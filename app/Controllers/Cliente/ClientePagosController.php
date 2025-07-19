<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\ClienteModel;

class ClientePagosController extends BaseController
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
        helper(['estado_cuenta', 'recibo', 'format']);
    }

    /**
     * Dashboard de pagos del cliente
     */
    public function index()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Obtener mensualidades pendientes prioritarias
            $mensualidadesPendientes = $this->tablaModel->buscarMensualidades([
                'cliente_id' => $clienteId,
                'estatus' => ['pendiente', 'vencida', 'parcial'],
                'orden_por' => 'ta.fecha_vencimiento',
                'direccion' => 'ASC',
                'limite' => 10
            ]);

            // Categorizar mensualidades por urgencia
            $categorizadas = $this->categorizarMensualidadesPorUrgencia($mensualidadesPendientes);
            
            // Obtener últimos 5 pagos
            $pagosRecientes = $this->pagoModel->buscarPagos([
                'cliente_id' => $clienteId,
                'estatus_pago' => 'aplicado',
                'orden_por' => 'pv.fecha_pago',
                'direccion' => 'DESC',
                'limite' => 5
            ]);

            // Calcular estadísticas de pagos
            $estadisticasPagos = $this->calcularEstadisticasPagos($clienteId);

            $data = [
                'title' => 'Mis Pagos',
                'mensualidades_categorizadas' => $categorizadas,
                'pagos_recientes' => $pagosRecientes,
                'estadisticas' => $estadisticasPagos,
                'fecha_actualizacion' => date('d/m/Y H:i:s')
            ];

            return view('cliente/pagos/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en dashboard pagos cliente: ' . $e->getMessage());
            return view('cliente/pagos/index', [
                'title' => 'Mis Pagos',
                'error' => 'Error al cargar información de pagos',
                'mensualidades_categorizadas' => [],
                'pagos_recientes' => [],
                'estadisticas' => []
            ]);
        }
    }

    /**
     * Ver detalle de una mensualidad específica
     */
    public function mensualidad(int $mensualidadId)
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Obtener mensualidad y verificar que pertenece al cliente
            $mensualidad = $this->tablaModel->find($mensualidadId);
            if (!$mensualidad) {
                return redirect()->to('/cliente/pagos')->with('error', 'Mensualidad no encontrada');
            }

            // Verificar propiedad del cliente
            $ventaInfo = $this->obtenerInfoVentaPorMensualidad($mensualidadId);
            if (!$ventaInfo || $ventaInfo->cliente_id != $clienteId) {
                return redirect()->to('/cliente/pagos')->with('error', 'Sin acceso a esta mensualidad');
            }

            // Obtener información detallada
            $infoDetallada = [
                'estado_visual' => $mensualidad->getEstadoVisualDetallado(),
                'info_vencimiento' => $mensualidad->getInfoVencimiento(),
                'montos_formateados' => $mensualidad->formatearMontos(),
                'descripcion_completa' => $mensualidad->getDescripcionCompleta()
            ];

            // Obtener pagos aplicados a esta mensualidad
            $pagosAplicados = $this->pagoModel->where('tabla_amortizacion_id', $mensualidadId)
                                            ->where('estatus_pago', 'aplicado')
                                            ->orderBy('fecha_pago', 'DESC')
                                            ->findAll();

            // Calcular opciones de pago
            $opcionesPago = $this->calcularOpcionesPago($mensualidad);

            $data = [
                'title' => 'Detalle Mensualidad #' . $mensualidad->numero_pago,
                'mensualidad' => $mensualidad,
                'venta_info' => $ventaInfo,
                'info_detallada' => $infoDetallada,
                'pagos_aplicados' => $pagosAplicados,
                'opciones_pago' => $opcionesPago
            ];

            return view('cliente/pagos/mensualidad', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en detalle mensualidad cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/pagos')->with('error', 'Error al cargar detalle de mensualidad');
        }
    }

    /**
     * Comprobante de pago de una mensualidad
     */
    public function comprobante(int $pagoId)
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Obtener pago y verificar propiedad
            $pago = $this->pagoModel->find($pagoId);
            if (!$pago) {
                return redirect()->to('/cliente/pagos')->with('error', 'Comprobante no encontrado');
            }

            // Verificar que el pago pertenece al cliente
            $venta = $this->ventaModel->find($pago->venta_id);
            if (!$venta || $venta->cliente_id != $clienteId) {
                return redirect()->to('/cliente/pagos')->with('error', 'Sin acceso a este comprobante');
            }

            // Generar datos del recibo usando helper
            $datosRecibo = generar_recibo_mensualidad($pagoId);
            
            if (!$datosRecibo['success']) {
                return redirect()->to('/cliente/pagos')->with('error', 'Error generando comprobante: ' . $datosRecibo['error']);
            }

            return view('cliente/pagos/comprobante', $datosRecibo);

        } catch (\Exception $e) {
            log_message('error', 'Error en comprobante cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/pagos')->with('error', 'Error al generar comprobante');
        }
    }

    /**
     * Descargar comprobante en PDF
     */
    public function descargarComprobante(int $pagoId)
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Verificar propiedad del pago
            $pago = $this->pagoModel->find($pagoId);
            if (!$pago) {
                return redirect()->to('/cliente/pagos')->with('error', 'Comprobante no encontrado');
            }

            $venta = $this->ventaModel->find($pago->venta_id);
            if (!$venta || $venta->cliente_id != $clienteId) {
                return redirect()->to('/cliente/pagos')->with('error', 'Sin acceso a este comprobante');
            }

            // Generar datos del recibo
            $datosRecibo = generar_recibo_mensualidad($pagoId);
            
            if (!$datosRecibo['success']) {
                return redirect()->to('/cliente/pagos')->with('error', 'Error generando comprobante: ' . $datosRecibo['error']);
            }

            // Generar PDF
            $pdf = new \Dompdf\Dompdf();
            $html = view('cliente/pagos/comprobante_pdf', $datosRecibo);
            
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();

            $nombreArchivo = 'comprobante_pago_' . $pago->folio_pago . '.pdf';
            
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->setBody($pdf->output());

        } catch (\Exception $e) {
            log_message('error', 'Error descargando comprobante PDF cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/pagos')->with('error', 'Error al descargar comprobante');
        }
    }

    /**
     * Solicitar recordatorio de pago
     */
    public function solicitarRecordatorio()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return $this->response->setJSON(['success' => false, 'error' => 'Sesión expirada']);
            }

            $mensualidadId = $this->request->getPost('mensualidad_id');
            $tipoRecordatorio = $this->request->getPost('tipo'); // 'email' o 'sms'
            
            if (!$mensualidadId) {
                return $this->response->setJSON(['success' => false, 'error' => 'Mensualidad no especificada']);
            }

            // Verificar que la mensualidad pertenece al cliente
            $mensualidad = $this->tablaModel->find($mensualidadId);
            if (!$mensualidad) {
                return $this->response->setJSON(['success' => false, 'error' => 'Mensualidad no encontrada']);
            }

            $ventaInfo = $this->obtenerInfoVentaPorMensualidad($mensualidadId);
            if (!$ventaInfo || $ventaInfo->cliente_id != $clienteId) {
                return $this->response->setJSON(['success' => false, 'error' => 'Sin acceso a esta mensualidad']);
            }

            // Aquí se implementaría el envío de recordatorio
            // Por ahora simularemos el envío exitoso
            $resultado = $this->enviarRecordatorioPago($clienteId, $mensualidadId, $tipoRecordatorio);
            
            return $this->response->setJSON($resultado);

        } catch (\Exception $e) {
            log_message('error', 'Error enviando recordatorio cliente: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => 'Error al enviar recordatorio']);
        }
    }

    /**
     * Reportar un pago realizado (para que admin lo valide)
     */
    public function reportarPago()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            if ($this->request->getMethod() === 'post') {
                // Procesar reporte de pago
                $rules = [
                    'mensualidad_id' => 'required|integer',
                    'monto_pagado' => 'required|decimal|greater_than[0]',
                    'forma_pago' => 'required|in_list[transferencia,deposito,cheque,efectivo]',
                    'fecha_pago' => 'required|valid_date',
                    'referencia' => 'permit_empty|string'
                ];

                if (!$this->validate($rules)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('errors', $this->validator->getErrors());
                }

                $mensualidadId = $this->request->getPost('mensualidad_id');
                
                // Verificar propiedad de la mensualidad
                $ventaInfo = $this->obtenerInfoVentaPorMensualidad($mensualidadId);
                if (!$ventaInfo || $ventaInfo->cliente_id != $clienteId) {
                    return redirect()->back()->with('error', 'Sin acceso a esta mensualidad');
                }

                // Crear registro de pago pendiente de validación
                $datosReporte = [
                    'venta_id' => $ventaInfo->venta_id,
                    'tabla_amortizacion_id' => $mensualidadId,
                    'monto_pago' => $this->request->getPost('monto_pagado'),
                    'forma_pago' => $this->request->getPost('forma_pago'),
                    'concepto_pago' => 'Pago reportado por cliente',
                    'descripcion_concepto' => 'Pago reportado por cliente para validación',
                    'fecha_pago' => $this->request->getPost('fecha_pago'),
                    'estatus_pago' => 'reportado', // Estado especial para pagos reportados por clientes
                    'referencia_pago' => $this->request->getPost('referencia'),
                    'registrado_por' => auth()->id(),
                    'observaciones_cliente' => $this->request->getPost('observaciones')
                ];

                if ($this->pagoModel->insert($datosReporte)) {
                    return redirect()->to('/cliente/pagos')
                        ->with('success', 'Pago reportado exitosamente. Será validado por nuestro equipo en las próximas 24 horas.');
                } else {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Error al registrar reporte de pago');
                }
            }

            // Mostrar formulario
            $mensualidadId = $this->request->getGet('mensualidad_id');
            $mensualidad = null;
            $ventaInfo = null;

            if ($mensualidadId) {
                $mensualidad = $this->tablaModel->find($mensualidadId);
                if ($mensualidad) {
                    $ventaInfo = $this->obtenerInfoVentaPorMensualidad($mensualidadId);
                    // Verificar propiedad
                    if (!$ventaInfo || $ventaInfo->cliente_id != $clienteId) {
                        $mensualidad = null;
                        $ventaInfo = null;
                    }
                }
            }

            $data = [
                'title' => 'Reportar Pago Realizado',
                'mensualidad' => $mensualidad,
                'venta_info' => $ventaInfo,
                'formas_pago' => [
                    'transferencia' => 'Transferencia bancaria',
                    'deposito' => 'Depósito bancario',
                    'cheque' => 'Cheque',
                    'efectivo' => 'Efectivo en oficinas'
                ]
            ];

            return view('cliente/pagos/reportar-pago', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en reportar pago cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/pagos')->with('error', 'Error al procesar reporte de pago');
        }
    }

    /**
     * Historial completo de pagos con filtros
     */
    public function historial()
    {
        try {
            $clienteId = $this->obtenerClienteIdAutenticado();
            
            if (!$clienteId) {
                return redirect()->to('/cliente/login')->with('error', 'Debe iniciar sesión para acceder');
            }

            // Obtener filtros
            $filtros = $this->request->getGet();
            
            $filtrosBusqueda = [
                'cliente_id' => $clienteId,
                'fecha_desde' => $filtros['fecha_desde'] ?? null,
                'fecha_hasta' => $filtros['fecha_hasta'] ?? null,
                'forma_pago' => $filtros['forma_pago'] ?? null,
                'estatus_pago' => $filtros['estatus_pago'] ?? null,
                'venta_id' => $filtros['venta_id'] ?? null,
                'orden_por' => 'pv.fecha_pago',
                'direccion' => 'DESC',
                'limite' => 100
            ];

            $pagos = $this->pagoModel->buscarPagos($filtrosBusqueda);
            
            // Estadísticas por período
            $estadisticasDetalladas = $this->calcularEstadisticasDetalladas($pagos, $filtros);
            
            // Obtener ventas del cliente para filtro
            $ventasCliente = $this->ventaModel->getVentasActivasCliente($clienteId);

            $data = [
                'title' => 'Historial Completo de Pagos',
                'pagos' => $pagos,
                'filtros_aplicados' => $filtros,
                'estadisticas' => $estadisticasDetalladas,
                'ventas_cliente' => $ventasCliente,
                'opciones_filtros' => [
                    'formas_pago' => ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'deposito'],
                    'estados_pago' => ['aplicado', 'reportado', 'pendiente', 'cancelado']
                ]
            ];

            return view('cliente/pagos/historial', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en historial pagos cliente: ' . $e->getMessage());
            return redirect()->to('/cliente/pagos')->with('error', 'Error al cargar historial');
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

        $cliente = $this->clienteModel
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        return $cliente ? $cliente->id : null;
    }

    /**
     * Categoriza mensualidades por urgencia de pago
     */
    private function categorizarMensualidadesPorUrgencia(array $mensualidades): array
    {
        $categorizadas = [
            'criticas' => [], // Vencidas más de 30 días
            'urgentes' => [], // Vencidas hasta 30 días
            'proximasSemana' => [], // Vencen en 7 días
            'proximasMes' => [], // Vencen en 30 días
            'futuras' => [] // Resto
        ];

        $hoy = new \DateTime();
        $proximaSemana = (clone $hoy)->modify('+7 days');
        $proximoMes = (clone $hoy)->modify('+30 days');

        foreach ($mensualidades as $mensualidad) {
            $fechaVencimiento = new \DateTime($mensualidad->fecha_vencimiento);
            
            if ($mensualidad->estatus === 'vencida') {
                if ($mensualidad->dias_atraso > 30) {
                    $categorizadas['criticas'][] = $mensualidad;
                } else {
                    $categorizadas['urgentes'][] = $mensualidad;
                }
            } elseif ($fechaVencimiento <= $proximaSemana) {
                $categorizadas['proximasSemana'][] = $mensualidad;
            } elseif ($fechaVencimiento <= $proximoMes) {
                $categorizadas['proximasMes'][] = $mensualidad;
            } else {
                $categorizadas['futuras'][] = $mensualidad;
            }
        }

        return $categorizadas;
    }

    /**
     * Calcula estadísticas generales de pagos del cliente
     */
    private function calcularEstadisticasPagos(int $clienteId): array
    {
        // Pagos aplicados
        $pagosAplicados = $this->pagoModel->buscarPagos([
            'cliente_id' => $clienteId,
            'estatus_pago' => 'aplicado'
        ]);

        // Mensualidades pendientes
        $mensualidadesPendientes = $this->tablaModel->buscarMensualidades([
            'cliente_id' => $clienteId,
            'estatus' => ['pendiente', 'vencida', 'parcial']
        ]);

        $totalPagado = array_sum(array_column($pagosAplicados, 'monto_pago'));
        $totalPendiente = array_sum(array_map(function($m) { 
            return $m->getSaldoTotalPendiente(); 
        }, $mensualidadesPendientes));

        return [
            'total_pagos_realizados' => count($pagosAplicados),
            'monto_total_pagado' => $totalPagado,
            'mensualidades_pendientes' => count($mensualidadesPendientes),
            'monto_total_pendiente' => $totalPendiente,
            'ultima_fecha_pago' => !empty($pagosAplicados) ? $pagosAplicados[0]->fecha_pago : null,
            'promedio_pago' => count($pagosAplicados) > 0 ? $totalPagado / count($pagosAplicados) : 0
        ];
    }

    /**
     * Calcula opciones de pago para una mensualidad
     */
    private function calcularOpcionesPago(object $mensualidad): array
    {
        $opciones = [
            'pago_total' => $mensualidad->getSaldoTotalPendiente(),
            'pago_minimo' => min($mensualidad->getSaldoTotalPendiente(), $mensualidad->capital),
            'pago_capital_interes' => $mensualidad->capital + $mensualidad->interes,
            'solo_intereses' => $mensualidad->interes + $mensualidad->interes_moratorio
        ];

        return $opciones;
    }

    /**
     * Obtiene información de venta por mensualidad
     */
    private function obtenerInfoVentaPorMensualidad(int $mensualidadId): ?object
    {
        return $this->db->table('tabla_amortizacion ta')
                       ->select('
                           v.id as venta_id,
                           v.cliente_id,
                           v.folio_venta,
                           v.precio_venta_final,
                           c.nombres,
                           c.apellido_paterno,
                           c.email,
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
     * Simula envío de recordatorio de pago
     */
    private function enviarRecordatorioPago(int $clienteId, int $mensualidadId, string $tipo): array
    {
        // Implementación simulada - en producción se integraría con servicio de email/SMS
        log_message('info', "Recordatorio {$tipo} enviado a cliente {$clienteId} para mensualidad {$mensualidadId}");
        
        return [
            'success' => true,
            'message' => "Recordatorio por {$tipo} enviado exitosamente",
            'tipo' => $tipo,
            'fecha_envio' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calcula estadísticas detalladas para un período
     */
    private function calcularEstadisticasDetalladas(array $pagos, array $filtros): array
    {
        $estadisticas = [
            'total_pagos' => count($pagos),
            'monto_total' => 0,
            'por_forma_pago' => [],
            'por_mes' => [],
            'promedio_pago' => 0
        ];

        foreach ($pagos as $pago) {
            $estadisticas['monto_total'] += $pago->monto_pago;
            
            // Por forma de pago
            $forma = $pago->forma_pago;
            if (!isset($estadisticas['por_forma_pago'][$forma])) {
                $estadisticas['por_forma_pago'][$forma] = ['cantidad' => 0, 'monto' => 0];
            }
            $estadisticas['por_forma_pago'][$forma]['cantidad']++;
            $estadisticas['por_forma_pago'][$forma]['monto'] += $pago->monto_pago;
            
            // Por mes
            $mes = date('Y-m', strtotime($pago->fecha_pago));
            if (!isset($estadisticas['por_mes'][$mes])) {
                $estadisticas['por_mes'][$mes] = ['cantidad' => 0, 'monto' => 0];
            }
            $estadisticas['por_mes'][$mes]['cantidad']++;
            $estadisticas['por_mes'][$mes]['monto'] += $pago->monto_pago;
        }

        $estadisticas['promedio_pago'] = count($pagos) > 0 ? $estadisticas['monto_total'] / count($pagos) : 0;

        return $estadisticas;
    }
}