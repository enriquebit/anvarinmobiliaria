<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\ClienteModel;

class ClienteEstadoCuentaController extends BaseController
{
    protected $ventaModel;
    protected $tablaModel;
    protected $pagoModel;
    protected $clienteModel;
    protected $db;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->pagoModel = new PagoVentaModel();
        $this->clienteModel = new ClienteModel();
        $this->db = \Config\Database::connect();
        
        // Cargar helpers necesarios
        helper(['estado_cuenta', 'format']);
    }

    /**
     * Dashboard principal del estado de cuenta del cliente
     */
    public function index()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Generar resumen completo del cliente
            $resumenCliente = generar_resumen_cliente($clienteId);
            
            if (!$resumenCliente['success']) {
                return view('cliente/estado-cuenta/index', [
                    'title' => 'Mi Estado de Cuenta',
                    'error' => $resumenCliente['error'],
                    'resumen_disponible' => false
                ]);
            }

            // Formatear datos para presentación
            $datosFormateados = formatear_estado_cuenta($resumenCliente);
            
            // Generar alertas específicas del cliente
            $alertasCliente = generar_alertas_vencimiento($clienteId);
            
            // Obtener datos adicionales para dashboard
            $proximasMensualidades = $this->ventaModel->getProximasMensualidadesCliente($clienteId, 7);
            $historialPagosRecientes = $this->pagoModel->buscarPagos([
                'cliente_id' => $clienteId,
                'estatus_pago' => 'aplicado',
                'orden_por' => 'pv.fecha_pago',
                'direccion' => 'DESC',
                'limite' => 5
            ]);

            $data = [
                'title' => 'Mi Estado de Cuenta',
                'cliente' => $datosFormateados['cliente'],
                'resumen_general' => $datosFormateados['resumen_general'],
                'indicadores' => $datosFormateados['indicadores'],
                'proximo_vencimiento' => $datosFormateados['proximo_vencimiento'],
                'mensualidades_vencidas' => $datosFormateados['mensualidades_vencidas'],
                'proximas_mensualidades' => $proximasMensualidades,
                'propiedades' => $datosFormateados['propiedades'],
                'alertas' => $alertasCliente,
                'historial_pagos' => $historialPagosRecientes,
                'metadata' => $datosFormateados['metadata'],
                'resumen_disponible' => true,
                'total_alertas' => $alertasCliente['total_alertas'] ?? 0
            ];

            return view('cliente/estado-cuenta/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en estado cuenta cliente: ' . $e->getMessage());
            return view('cliente/estado-cuenta/index', [
                'title' => 'Mi Estado de Cuenta',
                'error' => 'Error al cargar su estado de cuenta. Intente más tarde.',
                'resumen_disponible' => false
            ]);
        }
    }

    /**
     * Detalle de una propiedad específica del cliente
     */
    public function propiedad(int $ventaId = null)
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Si no se proporciona ventaId, buscar la primera venta del cliente
            if ($ventaId === null) {
                $primeraVenta = $this->ventaModel->where('cliente_id', $clienteId)
                                                 ->orderBy('created_at', 'ASC')
                                                 ->first();
                
                if (!$primeraVenta) {
                    return redirect()->to('/cliente/estado-cuenta')->with('error', 'No se encontraron propiedades asociadas a su cuenta');
                }
                
                // Redirigir a la URL correcta con el ID
                return redirect()->to("/cliente/estado-cuenta/propiedad/{$primeraVenta->id}");
            }

            // Validar que la venta pertenece al cliente autenticado
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta || $venta->cliente_id != $clienteId) {
                return redirect()->to('/cliente/estado-cuenta')->with('error', 'Propiedad no encontrada o sin acceso');
            }

            // Obtener información completa de la venta
            $resumenFinanciero = $venta->getResumenFinanciero();
            $estadoVisual = $venta->getEstadoVisualCompleto();
            
            // Obtener mensualidades de la venta
            $mensualidades = $this->tablaModel->getByVenta($ventaId);
            
            // Obtener historial de pagos de la venta
            $historialPagos = $this->pagoModel->getHistorialPagos($ventaId);
            
            // Generar proyección de liquidación
            $proyeccionLiquidacion = calcular_proyeccion_liquidacion($ventaId);

            // Obtener información del lote
            $loteInfo = $this->obtenerInformacionLote($venta->lote_id);

            $data = [
                'title' => 'Detalle de Propiedad - ' . $venta->folio_venta,
                'venta' => $venta,
                'lote_info' => $loteInfo,
                'resumen_financiero' => $resumenFinanciero,
                'estado_visual' => $estadoVisual,
                'mensualidades' => $mensualidades,
                'historial_pagos' => $historialPagos,
                'proyeccion_liquidacion' => $proyeccionLiquidacion,
                'montos_formateados' => $venta->formatearMontos()
            ];

            return view('cliente/estado-cuenta/propiedad', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en detalle propiedad cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/estado-cuenta')->with('error', 'Error al cargar detalle de propiedad');
        }
    }

    /**
     * Historial de pagos del cliente
     */
    public function historialPagos()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Obtener filtros de la request
            $filtros = $this->request->getGet();
            
            // Preparar filtros para búsqueda
            $filtrosBusqueda = [
                'cliente_id' => $clienteId,
                'fecha_desde' => $filtros['fecha_desde'] ?? null,
                'fecha_hasta' => $filtros['fecha_hasta'] ?? null,
                'forma_pago' => $filtros['forma_pago'] ?? null,
                'venta_id' => $filtros['venta_id'] ?? null,
                'estatus_pago' => $filtros['estatus_pago'] ?? 'aplicado', // Solo pagos aplicados por defecto
                'orden_por' => 'pv.fecha_pago',
                'direccion' => 'DESC',
                'limite' => 50
            ];

            // Buscar pagos
            $pagos = $this->pagoModel->buscarPagos($filtrosBusqueda);
            
            // Obtener estadísticas del historial
            $estadisticasHistorial = $this->calcularEstadisticasHistorial($pagos);
            
            // Obtener ventas del cliente para filtro
            $ventasCliente = $this->ventaModel->getVentasActivasCliente($clienteId);

            $data = [
                'title' => 'Mi Historial de Pagos',
                'pagos' => $pagos,
                'filtros_aplicados' => $filtros,
                'estadisticas' => $estadisticasHistorial,
                'ventas_cliente' => $ventasCliente,
                'formas_pago' => ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'deposito']
            ];

            return view('cliente/estado-cuenta/historialPagos', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en historial pagos cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/estado-cuenta')->with('error', 'Error al cargar historial de pagos');
        }
    }

    /**
     * Próximos vencimientos del cliente
     */
    public function proximosVencimientos()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            $diasAnticipacion = $this->request->getGet('dias') ?? 30;
            
            // Obtener próximas mensualidades
            $proximasMensualidades = $this->ventaModel->getProximasMensualidadesCliente($clienteId, $diasAnticipacion);
            
            // Categorizar por urgencia
            $categorizadas = [
                'vencidas' => [],
                'hoy' => [],
                'esta_semana' => [],
                'este_mes' => [],
                'futuras' => []
            ];

            $hoy = new \DateTime();
            $finSemana = (clone $hoy)->modify('+7 days');
            $finMes = (clone $hoy)->modify('+30 days');

            foreach ($proximasMensualidades as $proxima) {
                $mensualidad = $proxima['mensualidad'];
                $fechaVencimiento = new \DateTime($mensualidad->fecha_vencimiento);
                
                if ($proxima['esta_vencida']) {
                    $categorizadas['vencidas'][] = $proxima;
                } elseif ($fechaVencimiento->format('Y-m-d') === $hoy->format('Y-m-d')) {
                    $categorizadas['hoy'][] = $proxima;
                } elseif ($fechaVencimiento <= $finSemana) {
                    $categorizadas['esta_semana'][] = $proxima;
                } elseif ($fechaVencimiento <= $finMes) {
                    $categorizadas['este_mes'][] = $proxima;
                } else {
                    $categorizadas['futuras'][] = $proxima;
                }
            }

            // Calcular estadísticas
            $estadisticas = [
                'total_vencidas' => count($categorizadas['vencidas']),
                'total_hoy' => count($categorizadas['hoy']),
                'total_semana' => count($categorizadas['esta_semana']),
                'total_mes' => count($categorizadas['este_mes']),
                'monto_vencido' => array_sum(array_map(function($p) { 
                    return $p['mensualidad']->monto_total; 
                }, $categorizadas['vencidas'])),
                'monto_proximo' => array_sum(array_map(function($p) { 
                    return $p['mensualidad']->monto_total; 
                }, array_merge($categorizadas['hoy'], $categorizadas['esta_semana'])))
            ];

            $data = [
                'title' => 'Mis Próximos Vencimientos',
                'mensualidades_categorizadas' => $categorizadas,
                'estadisticas' => $estadisticas,
                'dias_anticipacion' => $diasAnticipacion,
                'fecha_consulta' => date('d/m/Y H:i:s')
            ];

            return view('cliente/estado-cuenta/proximos-vencimientos', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en próximos vencimientos cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/estado-cuenta')->with('error', 'Error al cargar próximos vencimientos');
        }
    }

    /**
     * Descargar estado de cuenta en PDF
     */
    public function descargarPDF()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Generar datos para PDF
            $datosPdf = generar_estado_cuenta_pdf($clienteId);
            
            if (!$datosPdf['success']) {
                return redirect()->back()->with('error', $datosPdf['error']);
            }

            // Generar PDF usando DOMPDF
            $pdf = new \Dompdf\Dompdf();
            $html = view('cliente/estado-cuenta/pdf_template', $datosPdf['datos_pdf']);
            
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            $nombreArchivo = 'mi_estado_cuenta_' . date('Ymd') . '.pdf';
            
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->setBody($pdf->output());

        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF cliente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar PDF. Intente más tarde.');
        }
    }

    /**
     * Solicitar información de pago (para clientes que quieren conocer dónde pagar)
     */
    public function informacionPago()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Obtener cuentas bancarias activas para pagos
            $cuentasBancarias = $this->obtenerCuentasBancariasActivas();
            
            // Obtener información de contacto de la empresa
            $empresaInfo = obtener_empresa_recibo();
            
            // Obtener mensualidades pendientes del cliente
            $mensualidadesPendientes = $this->tablaModel->getMensualidadesPendientes($clienteId);

            $data = [
                'title' => 'Información para Pagos',
                'cuentas_bancarias' => $cuentasBancarias,
                'empresa_info' => $empresaInfo,
                'mensualidades_pendientes' => count($mensualidadesPendientes),
                'formas_pago_disponibles' => [
                    'efectivo' => 'Pago en oficinas',
                    'transferencia' => 'Transferencia bancaria',
                    'deposito' => 'Depósito bancario',
                    'cheque' => 'Cheque a nombre de la empresa'
                ]
            ];

            return view('cliente/estado-cuenta/informacion-pago', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en información pago cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/estado-cuenta')->with('error', 'Error al cargar información de pago');
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    /**
     * Obtiene el ID del cliente autenticado
     */
    private function obtenerClienteIdAutenticado(): ?int
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        // Buscar cliente por user_id o email
        $cliente = $this->clienteModel
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        return $cliente ? $cliente->id : null;
    }

    /**
     * Calcula estadísticas del historial de pagos
     */
    private function calcularEstadisticasHistorial(array $pagos): array
    {
        $estadisticas = [
            'total_pagos' => count($pagos),
            'monto_total' => 0,
            'promedio_mensual' => 0,
            'por_forma_pago' => [],
            'ultimo_pago' => null
        ];

        if (empty($pagos)) {
            return $estadisticas;
        }

        foreach ($pagos as $pago) {
            $estadisticas['monto_total'] += $pago->monto_pago;
            
            $forma = $pago->forma_pago;
            if (!isset($estadisticas['por_forma_pago'][$forma])) {
                $estadisticas['por_forma_pago'][$forma] = 0;
            }
            $estadisticas['por_forma_pago'][$forma]++;
        }

        // Promedio (simplificado)
        $estadisticas['promedio_mensual'] = $estadisticas['monto_total'] / max(1, count($pagos));
        
        // Último pago
        $estadisticas['ultimo_pago'] = $pagos[0] ?? null; // Ya vienen ordenados DESC

        return $estadisticas;
    }

    /**
     * Obtiene información del lote para mostrar al cliente
     */
    private function obtenerInformacionLote(int $loteId): ?object
    {
        return $this->db->table('lotes l')
                       ->select('
                           l.clave,
                           l.numero,
                           l.area,
                           l.frente,
                           l.fondo,
                           p.nombre as proyecto_nombre,
                           m.nombre as manzana_nombre,
                           tl.nombre as tipo_nombre
                       ')
                       ->join('manzanas m', 'm.id = l.manzanas_id', 'left')
                       ->join('proyectos p', 'p.id = m.proyectos_id', 'left')
                       ->join('tipos_lotes tl', 'tl.id = l.tipos_lotes_id', 'left')
                       ->where('l.id', $loteId)
                       ->get()
                       ->getRow();
    }

    /**
     * Obtiene cuentas bancarias activas para mostrar información de pago
     */
    private function obtenerCuentasBancariasActivas(): array
    {
        return $this->db->table('cuentas_bancarias')
                       ->select('nombre_banco, numero_cuenta, tipo_cuenta, titular_cuenta')
                       ->where('activo', 1)
                       ->orderBy('nombre_banco', 'ASC')
                       ->get()
                       ->getResult();
    }
}