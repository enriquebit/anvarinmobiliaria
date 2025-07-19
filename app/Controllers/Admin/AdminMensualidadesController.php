<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\VentaModel;
use App\Models\ClienteModel;

class AdminMensualidadesController extends BaseController
{
    protected $tablaModel;
    protected $pagoModel;
    protected $ventaModel;
    protected $clienteModel;
    protected $db;

    public function __construct()
    {
        $this->tablaModel = new TablaAmortizacionModel();
        $this->pagoModel = new PagoVentaModel();
        $this->ventaModel = new VentaModel();
        $this->clienteModel = new ClienteModel();
        $this->db = \Config\Database::connect();
        
        // Cargar helpers necesarios
        helper(['estado_cuenta', 'amortizacion', 'recibo', 'format']);
    }

    /**
     * Lista global de mensualidades con filtros avanzados
     */
    public function index()
    {
        try {
            // Obtener filtros de la request
            $filtros = $this->request->getGet();
            
            // Configurar filtros por defecto
            $filtrosBusqueda = [
                'estatus' => $filtros['estatus'] ?? null,
                'cliente_id' => $filtros['cliente_id'] ?? null,
                'vendedor_id' => $filtros['vendedor_id'] ?? null,
                'fecha_desde' => $filtros['fecha_desde'] ?? null,
                'fecha_hasta' => $filtros['fecha_hasta'] ?? null,
                'dias_vencido_min' => $filtros['dias_vencido_min'] ?? null,
                'nombre_cliente' => $filtros['nombre_cliente'] ?? null,
                'orden_por' => $filtros['orden_por'] ?? 'ta.fecha_vencimiento',
                'direccion' => $filtros['direccion'] ?? 'ASC'
            ];

            // Paginación
            $paginaActual = (int)($filtros['page'] ?? 1);
            $elementosPorPagina = 50;
            $filtrosBusqueda['limite'] = $elementosPorPagina;
            $filtrosBusqueda['offset'] = ($paginaActual - 1) * $elementosPorPagina;

            // Buscar mensualidades
            $mensualidades = $this->tablaModel->buscarMensualidades($filtrosBusqueda);
            
            // Obtener estadísticas globales
            $estadisticasGenerales = $this->tablaModel->getEstadisticasGenerales();
            
            // Preparar datos para filtros
            $opcionesVendedores = $this->obtenerVendedoresConMensualidades();
            $estadosDisponibles = ['pendiente', 'vencida', 'pagada', 'parcial', 'cancelada'];

            $data = [
                'title' => 'Gestión de Mensualidades',
                'mensualidades' => $mensualidades,
                'filtros_aplicados' => $filtros,
                'estadisticas_generales' => $estadisticasGenerales,
                'opciones_vendedores' => $opcionesVendedores,
                'estados_disponibles' => $estadosDisponibles,
                'paginacion' => [
                    'pagina_actual' => $paginaActual,
                    'elementos_por_pagina' => $elementosPorPagina,
                    'total_elementos' => count($mensualidades)
                ]
            ];

            return view('admin/mensualidades/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en lista mensualidades: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar mensualidades: ' . $e->getMessage());
        }
    }

    /**
     * Vista específica para mensualidades pendientes y vencidas
     */
    public function pendientes()
    {
        try {
            // Obtener mensualidades pendientes y vencidas
            $mensualidadesPendientes = $this->tablaModel->buscarMensualidades([
                'estatus' => ['pendiente', 'vencida', 'parcial'],
                'orden_por' => 'ta.fecha_vencimiento',
                'direccion' => 'ASC'
            ]);

            // Categorizar por urgencia
            $categorizadas = [
                'criticas' => [], // Más de 30 días vencidas
                'urgentes' => [], // Vencidas hasta 30 días
                'proximasMañana' => [], // Vencen mañana
                'proximaSemana' => [], // Vencen en 7 días
                'futuras' => [] // Resto
            ];

            $hoy = new \DateTime();
            $mañana = (clone $hoy)->modify('+1 day');
            $proximaSemana = (clone $hoy)->modify('+7 days');

            foreach ($mensualidadesPendientes as $mensualidad) {
                $fechaVencimiento = new \DateTime($mensualidad->fecha_vencimiento);
                
                if ($mensualidad->estatus === 'vencida') {
                    if ($mensualidad->dias_atraso > 30) {
                        $categorizadas['criticas'][] = $mensualidad;
                    } else {
                        $categorizadas['urgentes'][] = $mensualidad;
                    }
                } elseif ($fechaVencimiento <= $mañana) {
                    $categorizadas['proximasMañana'][] = $mensualidad;
                } elseif ($fechaVencimiento <= $proximaSemana) {
                    $categorizadas['proximaSemana'][] = $mensualidad;
                } else {
                    $categorizadas['futuras'][] = $mensualidad;
                }
            }

            // Calcular estadísticas de pendientes
            $estadisticasPendientes = [
                'total_criticas' => count($categorizadas['criticas']),
                'total_urgentes' => count($categorizadas['urgentes']),
                'total_proximas' => count($categorizadas['proximasMañana']) + count($categorizadas['proximaSemana']),
                'monto_critico' => array_sum(array_column($categorizadas['criticas'], 'saldo_pendiente')),
                'monto_urgente' => array_sum(array_column($categorizadas['urgentes'], 'saldo_pendiente')),
                'monto_proximo' => array_sum(array_column($categorizadas['proximasMañana'], 'saldo_pendiente')) +
                                  array_sum(array_column($categorizadas['proximaSemana'], 'saldo_pendiente'))
            ];

            $data = [
                'title' => 'Mensualidades Pendientes - Gestión Prioritaria',
                'mensualidades_categorizadas' => $categorizadas,
                'estadisticas_pendientes' => $estadisticasPendientes,
                'fecha_actualizacion' => date('d/m/Y H:i:s')
            ];

            return view('admin/mensualidades/pendientes', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en mensualidades pendientes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar mensualidades pendientes: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para aplicar pago a mensualidad
     */
    public function aplicarPago(int $mensualidadId)
    {
        try {
            // Obtener la mensualidad
            $mensualidad = $this->tablaModel->find($mensualidadId);
            if (!$mensualidad) {
                return redirect()->back()->with('error', 'Mensualidad no encontrada');
            }

            // Obtener información de la venta y cliente
            $ventaInfo = $this->obtenerInfoVentaPorMensualidad($mensualidadId);
            if (!$ventaInfo) {
                return redirect()->back()->with('error', 'No se pudo obtener información de la venta');
            }

            // Calcular información del pago
            $infoPago = [
                'saldo_pendiente' => $mensualidad->getSaldoTotalPendiente(),
                'monto_capital' => $mensualidad->capital,
                'monto_interes' => $mensualidad->interes,
                'interes_moratorio' => $mensualidad->interes_moratorio,
                'dias_atraso' => $mensualidad->calcularDiasAtraso(),
                'esta_vencida' => $mensualidad->estaVencido()
            ];

            // Obtener formas de pago disponibles
            $formasPago = ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'deposito'];
            
            // Obtener cuentas bancarias para transferencias
            $cuentasBancarias = $this->obtenerCuentasBancarias();

            $data = [
                'title' => 'Aplicar Pago - Mensualidad #' . $mensualidad->numero_pago,
                'mensualidad' => $mensualidad,
                'venta_info' => $ventaInfo,
                'info_pago' => $infoPago,
                'formas_pago' => $formasPago,
                'cuentas_bancarias' => $cuentasBancarias
            ];

            return view('admin/mensualidades/aplicar-pago', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en formulario aplicar pago: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar formulario: ' . $e->getMessage());
        }
    }

    /**
     * Procesa el pago de una mensualidad
     */
    public function procesarPago()
    {
        try {
            // Validar datos de entrada
            $rules = [
                'mensualidad_id' => 'required|integer',
                'monto_pago' => 'required|decimal|greater_than[0]',
                'forma_pago' => 'required|in_list[efectivo,transferencia,cheque,tarjeta,deposito]',
                'concepto_pago' => 'required',
                'fecha_pago' => 'required|valid_date'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', $this->validator->getErrors());
            }

            $datosPago = $this->request->getPost();
            
            // Obtener la mensualidad
            $mensualidad = $this->tablaModel->find($datosPago['mensualidad_id']);
            if (!$mensualidad) {
                return redirect()->back()->with('error', 'Mensualidad no encontrada');
            }

            // Obtener información de la venta
            $ventaInfo = $this->obtenerInfoVentaPorMensualidad($datosPago['mensualidad_id']);
            
            // Preparar datos del pago
            $datosParaPago = [
                'venta_id' => $ventaInfo->venta_id,
                'tabla_amortizacion_id' => $datosPago['mensualidad_id'],
                'monto_pago' => (float)$datosPago['monto_pago'],
                'forma_pago' => $datosPago['forma_pago'],
                'concepto_pago' => $datosPago['concepto_pago'],
                'descripcion_concepto' => $datosPago['descripcion_concepto'] ?? null,
                'numero_mensualidad' => $mensualidad->numero_pago,
                'referencia_pago' => $datosPago['referencia_pago'] ?? null,
                'fecha_pago' => $datosPago['fecha_pago'],
                'registrado_por' => auth()->id(),
                'cuenta_bancaria_id' => $datosPago['cuenta_bancaria_id'] ?? null
            ];

            // Procesar el pago usando el model
            $resultadoPago = $this->pagoModel->procesarPago($datosParaPago);
            
            if (!$resultadoPago['success']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $resultadoPago['error']);
            }

            // Generar recibo si el pago fue exitoso
            $reciboData = generar_recibo_mensualidad($resultadoPago['pago_id']);
            
            $mensaje = 'Pago procesado exitosamente. Folio: ' . $resultadoPago['folio_generado'];
            
            return redirect()->route('admin.mensualidades.index')
                ->with('success', $mensaje)
                ->with('pago_id', $resultadoPago['pago_id'])
                ->with('mostrar_recibo', true);

        } catch (\Exception $e) {
            log_message('error', 'Error procesando pago: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar pago: ' . $e->getMessage());
        }
    }

    /**
     * Generar reporte mensual de mensualidades
     */
    public function reporteMensual()
    {
        try {
            $año = $this->request->getGet('año') ?? date('Y');
            $mes = $this->request->getGet('mes') ?? date('m');
            $vendedorId = $this->request->getGet('vendedor_id') ?? null;
            
            // Calcular rango de fechas del mes
            $fechaInicio = $año . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-01';
            $fechaFin = date('Y-m-t', strtotime($fechaInicio));
            
            // Filtros para el reporte
            $filtrosReporte = [
                'fecha_desde' => $fechaInicio,
                'fecha_hasta' => $fechaFin,
                'vendedor_id' => $vendedorId
            ];

            // Obtener mensualidades del período
            $mensualidades = $this->tablaModel->buscarMensualidades($filtrosReporte);
            
            // Calcular estadísticas del reporte
            $estadisticasReporte = $this->calcularEstadisticasReporte($mensualidades);
            
            // Obtener información adicional
            $vendedorInfo = null;
            if ($vendedorId) {
                $vendedorInfo = $this->obtenerInfoVendedor($vendedorId);
            }

            $datosReporte = [
                'titulo' => 'Reporte Mensual de Mensualidades',
                'periodo' => [
                    'año' => $año,
                    'mes' => $mes,
                    'mes_nombre' => $this->obtenerNombreMes($mes),
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ],
                'vendedor' => $vendedorInfo,
                'mensualidades' => $mensualidades,
                'estadisticas' => $estadisticasReporte,
                'fecha_generacion' => date('d/m/Y H:i:s'),
                'empresa' => obtener_empresa_recibo()
            ];

            // Si se solicita PDF, generar y descargar
            if ($this->request->getGet('formato') === 'pdf') {
                return $this->generarReportePDF($datosReporte);
            }

            // Calcular resumen ejecutivo basado en las estadísticas
            $resumenEjecutivo = [
                'total_cobrado' => $estadisticasReporte['monto_cobrado'] ?? 0,
                'mensualidades_cobradas' => $estadisticasReporte['pagadas'] ?? 0,
                'eficiencia_cobranza' => $estadisticasReporte['eficiencia_cobranza'] ?? 0,
                'clientes_morosos' => $estadisticasReporte['vencidas'] ?? 0
            ];

            // Mostrar vista del reporte
            $data = [
                'title' => 'Reporte Mensual - ' . $datosReporte['periodo']['mes_nombre'] . ' ' . $año,
                'reporte' => $datosReporte,
                'resumen_ejecutivo' => $resumenEjecutivo,
                'filtros' => [
                    'año' => $año,
                    'mes' => $mes,
                    'vendedor_id' => $vendedorId,
                    'tipo_reporte' => $this->request->getGet('tipo_reporte') ?? 'cobranza'
                ],
                'opciones_años' => range(date('Y') - 2, date('Y') + 1),
                'opciones_meses' => $this->obtenerMeses(),
                'opciones_vendedores' => $this->obtenerVendedoresConMensualidades()
            ];

            return view('admin/mensualidades/reporte-mensual', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en reporte mensual: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar reporte: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar un pago de mensualidad
     */
    public function cancelarPago(int $pagoId)
    {
        try {
            $motivo = $this->request->getPost('motivo_cancelacion');
            
            if (empty($motivo)) {
                return redirect()->back()->with('error', 'El motivo de cancelación es requerido');
            }

            // Cancelar pago usando el model
            $resultado = $this->pagoModel->cancelarPago($pagoId, $motivo, auth()->id());
            
            if (!$resultado['success']) {
                return redirect()->back()->with('error', $resultado['error']);
            }

            return redirect()->back()->with('success', 'Pago cancelado exitosamente');

        } catch (\Exception $e) {
            log_message('error', 'Error cancelando pago: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cancelar pago: ' . $e->getMessage());
        }
    }

    /**
     * Vista de detalle de una mensualidad específica
     */
    public function detalle(int $mensualidadId)
    {
        try {
            $mensualidad = $this->tablaModel->find($mensualidadId);
            if (!$mensualidad) {
                return redirect()->back()->with('error', 'Mensualidad no encontrada');
            }

            // Obtener información completa
            $ventaInfo = $this->obtenerInfoVentaPorMensualidad($mensualidadId);
            $pagosMensualidad = $this->obtenerPagosMensualidad($mensualidadId);
            
            // Calcular información detallada
            $infoDetallada = [
                'estado_visual' => $mensualidad->getEstadoVisualDetallado(),
                'info_vencimiento' => $mensualidad->getInfoVencimiento(),
                'montos_formateados' => $mensualidad->formatearMontos(),
                'descripcion_completa' => $mensualidad->getDescripcionCompleta()
            ];

            $data = [
                'title' => 'Detalle Mensualidad #' . $mensualidad->numero_pago,
                'mensualidad' => $mensualidad,
                'venta_info' => $ventaInfo,
                'pagos_mensualidad' => $pagosMensualidad,
                'info_detallada' => $infoDetallada
            ];

            return view('admin/mensualidades/detalle', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en detalle mensualidad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar detalle: ' . $e->getMessage());
        }
    }

    // ==========================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==========================================

    /**
     * Obtiene información de venta por mensualidad
     */
    private function obtenerInfoVentaPorMensualidad(int $mensualidadId): ?object
    {
        return $this->db->table('tabla_amortizacion ta')
                       ->select('
                           v.id as venta_id,
                           v.folio_venta,
                           v.precio_venta_final,
                           c.id as cliente_id,
                           CONCAT(c.nombres, " ", c.apellido_paterno, " ", c.apellido_materno) as nombre_cliente,
                           c.email,
                           c.telefono,
                           l.clave as lote_clave,
                           p.nombre as proyecto_nombre
                       ')
                       ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'inner')
                       ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                       ->join('clientes c', 'c.id = v.cliente_id', 'inner')
                       ->join('lotes l', 'l.id = v.lote_id', 'inner')
                       ->join('manzanas m', 'm.id = l.manzanas_id', 'inner')
                       ->join('proyectos p', 'p.id = m.proyectos_id', 'inner')
                       ->where('ta.id', $mensualidadId)
                       ->get()
                       ->getRow();
    }

    /**
     * Obtiene los pagos aplicados a una mensualidad
     */
    private function obtenerPagosMensualidad(int $mensualidadId): array
    {
        return $this->pagoModel->where('tabla_amortizacion_id', $mensualidadId)
                              ->orderBy('fecha_pago', 'DESC')
                              ->findAll();
    }

    /**
     * Obtiene vendedores que tienen mensualidades
     */
    private function obtenerVendedoresConMensualidades(): array
    {
        return $this->db->table('tabla_amortizacion ta')
                       ->select('
                           v.vendedor_id,
                           CONCAT(s.nombres, " ", s.apellido_paterno) as nombre_vendedor
                       ')
                       ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'inner')
                       ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                       ->join('staff s', 's.user_id = v.vendedor_id', 'inner')
                       ->groupBy('v.vendedor_id, s.nombres, s.apellido_paterno')
                       ->orderBy('s.nombres', 'ASC')
                       ->get()
                       ->getResult();
    }

    /**
     * Obtiene cuentas bancarias activas
     */
    private function obtenerCuentasBancarias(): array
    {
        return $this->db->table('cuentas_bancarias')
                       ->select('id, nombre_banco, numero_cuenta, tipo_cuenta')
                       ->where('activo', 1)
                       ->orderBy('nombre_banco', 'ASC')
                       ->get()
                       ->getResult();
    }

    /**
     * Calcula estadísticas para reporte
     */
    private function calcularEstadisticasReporte(array $mensualidades): array
    {
        $estadisticas = [
            'total_mensualidades' => count($mensualidades),
            'pagadas' => 0,
            'pendientes' => 0,
            'vencidas' => 0,
            'monto_total' => 0,
            'monto_cobrado' => 0,
            'monto_pendiente' => 0,
            'eficiencia_cobranza' => 0
        ];

        foreach ($mensualidades as $mensualidad) {
            $estadisticas['monto_total'] += $mensualidad->monto_total;
            $estadisticas['monto_cobrado'] += $mensualidad->monto_pagado;
            $estadisticas['monto_pendiente'] += $mensualidad->saldo_pendiente;

            switch ($mensualidad->estatus) {
                case 'pagada':
                    $estadisticas['pagadas']++;
                    break;
                case 'vencida':
                    $estadisticas['vencidas']++;
                    $estadisticas['pendientes']++;
                    break;
                default:
                    $estadisticas['pendientes']++;
                    break;
            }
        }

        // Calcular eficiencia de cobranza
        if ($estadisticas['monto_total'] > 0) {
            $estadisticas['eficiencia_cobranza'] = round(
                ($estadisticas['monto_cobrado'] / $estadisticas['monto_total']) * 100, 
                2
            );
        }

        return $estadisticas;
    }

    /**
     * Genera reporte en PDF
     */
    private function generarReportePDF(array $datosReporte)
    {
        try {
            $pdf = new \Dompdf\Dompdf();
            $html = view('admin/mensualidades/reporte-pdf', $datosReporte);
            
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'landscape');
            $pdf->render();

            $nombreArchivo = 'reporte_mensualidades_' . 
                           $datosReporte['periodo']['año'] . '_' . 
                           str_pad($datosReporte['periodo']['mes'], 2, '0', STR_PAD_LEFT) . '.pdf';
            
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->setBody($pdf->output());

        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF reporte: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene información de un vendedor
     */
    private function obtenerInfoVendedor(int $vendedorId): ?object
    {
        return $this->db->table('staff s')
                       ->select('s.*, CONCAT(s.nombres, " ", s.apellido_paterno) as nombre_completo')
                       ->where('s.user_id', $vendedorId)
                       ->get()
                       ->getRow();
    }

    /**
     * Obtiene nombre del mes
     */
    private function obtenerNombreMes(string $numeroMes): string
    {
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];
        
        return $meses[str_pad($numeroMes, 2, '0', STR_PAD_LEFT)] ?? 'Mes desconocido';
    }

    /**
     * Obtiene array de meses para select
     */
    private function obtenerMeses(): array
    {
        return [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];
    }
}