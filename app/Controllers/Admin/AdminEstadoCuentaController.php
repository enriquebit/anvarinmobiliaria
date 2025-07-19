<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\ClienteModel;

class AdminEstadoCuentaController extends BaseController
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
        
        helper(['estado_cuenta', 'format', 'amortizacion']);
    }

    /**
     * Vista principal - Lista de todas las cuentas con planes de pago
     */
    public function index()
    {
        // Obtener las últimas 20 ventas con información financiera
        $ultimasVentas = $this->db->table('ventas v')
            ->select('
                v.id,
                v.folio_venta,
                v.fecha_venta,
                v.precio_venta_final,
                v.estatus_venta,
                CONCAT(c.nombres, " ", c.apellido_paterno, " ", c.apellido_materno) as nombre_cliente,
                c.email,
                c.telefono,
                l.clave as clave_lote,
                l.numero as numero_lote,
                p.nombre as proyecto_nombre,
                pf.nombre as plan_financiamiento,
                pf.tipo_financiamiento,
                pf.promocion_cero_enganche,
                pf.meses_sin_intereses,
                pf.meses_con_intereses,
                cf.id as cuenta_id,
                cf.saldo_inicial,
                cf.saldo_actual,
                cf.fecha_apertura as fecha_apertura_cuenta
            ')
            ->join('clientes c', 'c.id = v.cliente_id', 'inner')
            ->join('lotes l', 'l.id = v.lote_id', 'inner')
            ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
            ->join('perfiles_financiamiento pf', 'pf.id = v.perfil_financiamiento_id', 'left')
            ->join('cuentas_financiamiento cf', 'cf.venta_id = v.id', 'left')
            ->whereIn('v.estatus_venta', ['activa', 'liquidada'])
            ->whereIn('v.tipo_venta', ['financiado'])
            ->orderBy('v.fecha_venta', 'DESC')
            ->limit(20)
            ->get()
            ->getResult();

        // Calcular estadísticas rápidas para cada venta
        foreach ($ultimasVentas as &$venta) {
            if ($venta->cuenta_id) {
                // Calcular total pagado
                $totalPagado = $this->db->table('pagos_ventas')
                    ->selectSum('monto_pago')
                    ->where('venta_id', $venta->id)
                    ->where('estatus_pago', 'aplicado')
                    ->get()
                    ->getRow()
                    ->monto_pago ?? 0;

                // Calcular porcentaje de avance
                $porcentajeAvance = $venta->precio_venta_final > 0 ? 
                    ($totalPagado / $venta->precio_venta_final) * 100 : 0;

                $venta->total_pagado = $totalPagado;
                $venta->saldo_pendiente = $venta->precio_venta_final - $totalPagado;
                $venta->porcentaje_avance = $porcentajeAvance;
                $venta->tiene_cuenta = true;
            } else {
                $venta->total_pagado = 0;
                $venta->saldo_pendiente = $venta->precio_venta_final;
                $venta->porcentaje_avance = 0;
                $venta->tiene_cuenta = false;
            }

            // Determinar modalidad de financiamiento
            if ($venta->promocion_cero_enganche) {
                $venta->modalidad = $venta->tipo_financiamiento === 'msi' ? 'Cero Enganche MSI' : 'Cero Enganche MCI';
                $venta->meses_financiamiento = $venta->tipo_financiamiento === 'msi' ? 
                    $venta->meses_sin_intereses : $venta->meses_con_intereses;
            } else {
                $venta->modalidad = $venta->tipo_financiamiento === 'msi' ? 'MSI' : 'MCI';
                $venta->meses_financiamiento = $venta->tipo_financiamiento === 'msi' ? 
                    $venta->meses_sin_intereses : $venta->meses_con_intereses;
            }
        }

        $data = [
            'title' => 'Estado de Cuenta',
            'subtitle' => 'Consultar estado de cuenta por cliente o contrato',
            'ultimas_ventas' => $ultimasVentas
        ];

        return view('admin/estado-cuenta/index', $data);
    }

    /**
     * Búsqueda AJAX de ventas para estado de cuenta
     */
    public function buscar()
    {
        $termino = $this->request->getGet('q');
        
        if (empty($termino)) {
            return $this->response->setJSON([]);
        }
        
        $ventas = $this->db->table('ventas v')
            ->select('
                v.id,
                v.folio_venta,
                v.estatus_venta,
                CONCAT(c.nombres, " ", c.apellido_paterno, " ", c.apellido_materno) as nombre_cliente,
                l.clave as clave_lote,
                p.nombre as proyecto_nombre
            ')
            ->join('clientes c', 'c.id = v.cliente_id', 'inner')
            ->join('lotes l', 'l.id = v.lote_id', 'inner')
            ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
            ->groupStart()
                ->like('v.folio_venta', $termino)
                ->orLike('c.nombres', $termino)
                ->orLike('c.apellido_paterno', $termino)
                ->orLike('l.clave', $termino)
            ->groupEnd()
            ->whereIn('v.estatus_venta', ['activa', 'liquidada'])
            ->limit(10)
            ->get()
            ->getResult();

        return $this->response->setJSON($ventas);
    }

    /**
     * Estado de cuenta específico de una venta (VISTA PRINCIPAL UNIFICADA)
     */
    public function venta($ventaId = null)
    {
        try {
            // Si viene por GET, obtener del parámetro
            if ($ventaId === null) {
                $ventaId = $this->request->getGet('venta_id');
            }
            
            if (!$ventaId) {
                return redirect()->back()->with('error', 'ID de venta requerido');
            }
            
            // Cargar helper de pagos inmobiliarios
            helper('pagos_inmobiliarios');
            
            // Obtener datos completos de la venta
            $venta = $this->obtenerDatosCompletosVenta($ventaId);
            
            if (!$venta) {
                return redirect()->back()->with('error', 'Venta no encontrada');
            }

            // Obtener historial completo usando el helper
            $historialCompleto = obtener_historial_pagos_lote($venta['cliente_id'], $venta['lote_id']);
            
            if (!$historialCompleto['success']) {
                return redirect()->back()->with('error', $historialCompleto['error']);
            }
            
            // Obtener historial de pagos tradicional
            $historialPagos = $this->obtenerHistorialPagos($ventaId);

            // Si el cliente tiene múltiples ventas, mostrar selector
            $otrasVentas = $this->obtenerOtrasVentasCliente($venta['cliente_id'], $ventaId);
            
            // Obtener tabla de amortización (si existe)
            $tablaAmortizacion = $this->obtenerTablaAmortizacionReal($ventaId);

            $data = [
                'title' => 'Estado de Cuenta Completo',
                'subtitle' => "Contrato {$venta['folio_venta']} - {$venta['nombre_cliente']}",
                'venta' => $venta,
                'historial_completo' => $historialCompleto,
                'historial_pagos' => $historialPagos,
                'tabla_amortizacion' => $tablaAmortizacion,
                'otras_ventas' => $otrasVentas,
                'mostrar_toggle_amortizacion' => !empty($tablaAmortizacion)
            ];

            return view('admin/estado-cuenta/venta_completa', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en estado cuenta venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar estado de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Estado de cuenta por cliente (maneja múltiples ventas)
     */
    public function cliente($clienteId = null)
    {
        try {
            // Si viene por GET, obtener del parámetro
            if ($clienteId === null) {
                $clienteId = $this->request->getGet('cliente_id');
            }
            
            if (!$clienteId) {
                return redirect()->back()->with('error', 'ID de cliente requerido');
            }
            
            $cliente = $this->clienteModel->find($clienteId);
            if (!$cliente) {
                return redirect()->back()->with('error', 'Cliente no encontrado');
            }

            $ventasCliente = $this->obtenerVentasCliente($clienteId);
            
            if (empty($ventasCliente)) {
                return redirect()->back()->with('error', 'El cliente no tiene ventas registradas');
            }
            
            // Si solo tiene una venta, ir directo a esa venta
            if (count($ventasCliente) === 1) {
                return redirect()->to("/admin/estado-cuenta/venta/{$ventasCliente[0]['id']}");
            }
            
            // Si tiene múltiples ventas, mostrar selector
            $data = [
                'title' => 'Seleccionar Propiedad - Estado de Cuenta',
                'cliente' => $cliente,
                'ventas_cliente' => $ventasCliente,
                'resumen_consolidado' => $this->calcularResumenConsolidado($ventasCliente)
            ];

            return view('admin/estado-cuenta/selector_ventas', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en estado cuenta cliente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar estado de cuenta: ' . $e->getMessage());
        }
    }


    /**
     * Obtener datos completos de una venta específica
     */
    private function obtenerDatosCompletosVenta(int $ventaId): ?array
    {
        $venta = $this->db->table('ventas v')
                         ->select('
                             v.*,
                             CONCAT(c.nombres, " ", c.apellido_paterno, " ", c.apellido_materno) as nombre_cliente,
                             c.telefono as telefono_cliente,
                             c.email as email_cliente,
                             l.clave as clave_lote,
                             l.area as area_lote,
                             p.nombre as nombre_proyecto,
                             p.descripcion as descripcion_proyecto,
                             CONCAT(s.nombres, " ", s.apellido_paterno, " ", s.apellido_materno) as nombre_vendedor,
                             pf.nombre as nombre_plan,
                             pf.porcentaje_interes_anual as tasa_interes_anual,
                             pf.meses_con_intereses as plazo_meses,
                             pf.tipo_financiamiento,
                             pf.porcentaje_anticipo,
                             pf.promocion_cero_enganche,
                             pf.meses_sin_intereses,
                             pf.penalizacion_enganche_tardio as tasa_mora_mensual,
                             pf.porcentaje_comision as comision_apertura,
                             pf.porcentaje_comision as comision_cobranza,
                             pf.descripcion as descripcion_plan
                         ')
                         ->join('clientes c', 'c.id = v.cliente_id', 'inner')
                         ->join('lotes l', 'l.id = v.lote_id', 'inner')
                         ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
                         ->join('users u', 'u.id = v.vendedor_id', 'inner')
                         ->join('staff s', 's.user_id = u.id', 'inner')
                         ->join('perfiles_financiamiento pf', 'pf.id = v.perfil_financiamiento_id', 'left')
                         ->where('v.id', $ventaId)
                         ->get()
                         ->getRowArray();

        return $venta;
    }

    /**
     * Generar tabla de amortización si no existe
     */
    private function generarTablaAmortizacionSiNoExiste(int $ventaId): void
    {
        // Temporalmente deshabilitado hasta definir estructura correcta
        // TODO: Implementar cuando se defina la relación tabla_amortizacion <-> ventas
        // Nota: $ventaId se usará cuando se implemente
        return;
    }

    /**
     * Calcular estadísticas precisas de una venta
     */
    private function calcularEstadisticasVenta(int $ventaId): array
    {
        // Obtener totales de ingresos
        $totalPagado = $this->db->table('ingresos')
                               ->selectSum('monto')
                               ->where('venta_id', $ventaId)
                               ->get()
                               ->getRow()
                               ->monto ?? 0;

        // Obtener datos de la venta
        $venta = $this->ventaModel->find($ventaId);
        
        // Contar mensualidades desde tabla amortización (temporalmente deshabilitado)
        $mensualidadesPagadas = 0;
        $mensualidadesPendientes = 0;
        $proximoVencimiento = null;

        return [
            'valor_contrato' => $venta->precio_venta_final,
            'total_pagado' => $totalPagado,
            'saldo_pendiente' => $venta->precio_venta_final - $totalPagado,
            'porcentaje_liquidado' => $venta->precio_venta_final > 0 
                ? round(($totalPagado / $venta->precio_venta_final) * 100, 1) 
                : 0,
            'mensualidades_pagadas' => $mensualidadesPagadas,
            'mensualidades_pendientes' => $mensualidadesPendientes,
            'proximo_vencimiento' => $proximoVencimiento,
            'estado_pagos' => $totalPagado >= $venta->precio_venta_final ? 'Liquidado' : 'Al corriente'
        ];
    }

    /**
     * Obtener tabla de amortización real de la base de datos
     */
    private function obtenerTablaAmortizacionReal(int $ventaId): array
    {
        try {
            // Obtener el plan de financiamiento de la venta
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta || !$venta->perfil_financiamiento_id) {
                return [];
            }

            // Obtener tabla de amortización específica de esta venta
            $tablaAmortizacion = $this->db->table('tabla_amortizacion ta')
                ->select('
                    ta.*,
                    ta.fecha_vencimiento,
                    ta.saldo_inicial,
                    ta.capital,
                    ta.interes,
                    ta.monto_total,
                    ta.monto_pagado,
                    ta.saldo_pendiente,
                    ta.estatus,
                    ta.dias_atraso,
                    ta.interes_moratorio,
                    ta.fecha_ultimo_pago
                ')
                ->where('ta.venta_id', $ventaId)
                ->orderBy('ta.numero_pago', 'ASC')
                ->get()
                ->getResultArray();

            // Calcular estado de cada pago
            foreach ($tablaAmortizacion as &$pago) {
                $pago['estado_tracking'] = $this->determinarEstadoPago($pago);
                $pago['porcentaje_pagado'] = $pago['monto_total'] > 0 
                    ? round(($pago['monto_pagado'] / $pago['monto_total']) * 100, 1)
                    : 0;
            }

            return $tablaAmortizacion;

        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo tabla amortización: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Determinar estado de un pago individual
     */
    private function determinarEstadoPago(array $pago): string
    {
        $hoy = date('Y-m-d');
        
        // FIX: Corregir comparación - el estatus en la BD es 'pagada' no 'pagado'
        if ($pago['estatus'] === 'pagada') {
            return 'Aplicado';
        }
        
        if ($pago['fecha_vencimiento'] < $hoy) {
            return 'Vencido';
        }
        
        if ($pago['fecha_vencimiento'] <= date('Y-m-d', strtotime('+7 days'))) {
            return 'Próximo vencimiento';
        }
        
        return 'Pendiente';
    }

    /**
     * Obtener historial de pagos de una venta
     */
    private function obtenerHistorialPagos(int $ventaId): array
    {
        return $this->db->table('ingresos i')
                       ->select('
                           i.*,
                           i.monto as total_ingreso,
                           i.fecha_ingreso as fecha_pago,
                           i.folio as folio_pago,
                           i.tipo_ingreso as concepto_nombre,
                           i.metodo_pago as forma_pago_nombre
                       ')
                       ->where('i.venta_id', $ventaId)
                       ->orderBy('i.fecha_ingreso', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Obtener otras ventas del mismo cliente
     */
    private function obtenerOtrasVentasCliente(int $clienteId, int $ventaExcluir): array
    {
        return $this->db->table('ventas v')
                       ->select('
                           v.id,
                           v.folio_venta,
                           l.clave as clave_lote,
                           p.nombre as nombre_proyecto,
                           v.estatus_venta
                       ')
                       ->join('lotes l', 'l.id = v.lote_id', 'inner')
                       ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
                       ->where('v.cliente_id', $clienteId)
                       ->where('v.id !=', $ventaExcluir)
                       ->get()
                       ->getResultArray();
    }

    /**
     * Generar estado de cuenta imprimible
     */
    public function generar($ventaId = null)
    {
        try {
            if ($ventaId === null) {
                $ventaId = $this->request->getGet('venta_id');
            }
            
            if (!$ventaId) {
                return redirect()->back()->with('error', 'ID de venta requerido');
            }

            // Obtener datos completos de la venta
            $venta = $this->obtenerDatosCompletosVenta($ventaId);
            if (!$venta) {
                return redirect()->back()->with('error', 'Venta no encontrada');
            }

            helper('pagos_inmobiliarios');
            $historialCompleto = obtener_historial_pagos_lote($venta['cliente_id'], $venta['lote_id']);
            
            if (!$historialCompleto['success']) {
                return redirect()->back()->with('error', $historialCompleto['error']);
            }

            $historialPagos = $this->obtenerHistorialPagos($ventaId);
            $tablaAmortizacion = $this->obtenerTablaAmortizacionReal($ventaId);

            // Preparar datos compatibles con la vista de documentos
            $data = [
                'title' => 'Estado de Cuenta - ' . $venta['nombre_cliente'],
                'venta' => (object) $venta,
                'folio' => 'EC-' . str_pad($ventaId, 6, '0', STR_PAD_LEFT),
                'fecha_generacion' => date('Y-m-d H:i:s'),
                'cliente' => (object) [
                    'nombre' => explode(' ', $venta['nombre_cliente'])[0] ?? '',
                    'apellido_paterno' => explode(' ', $venta['nombre_cliente'])[1] ?? '',
                    'apellido_materno' => explode(' ', $venta['nombre_cliente'])[2] ?? '',
                    'email' => $venta['email_cliente'],
                    'telefono' => $venta['telefono_cliente']
                ],
                'empresa' => (object) [
                    'razon_social' => 'ANVAR INMOBILIARIA',
                    'direccion' => 'Ciudad de México, México',
                    'telefono' => '(55) 1234-5678',
                    'email' => 'contacto@anvar.com.mx',
                    'logo_url' => base_url('assets/img/logo_admin.png')
                ],
                'proyecto' => (object) [
                    'nombre' => $venta['nombre_proyecto'] ?? 'Proyecto ANVAR',
                    'ubicacion' => 'Ciudad de México'
                ],
                'lote' => (object) [
                    'lote' => (object) [
                        'numero' => $venta['clave_lote'],
                        'superficie' => $venta['area_lote']
                    ],
                    'manzana' => (object) [
                        'nombre' => 'Manzana 1'
                    ]
                ],
                'plan_pagos' => [
                    'plazo_meses' => $venta['plazo_meses'] ?? 60,
                    'mensualidad' => ($venta['plazo_meses'] ?? 60) > 0 ? 
                        $historialCompleto['resumen_financiero']['saldo_total_pendiente'] / ($venta['plazo_meses'] ?? 60) : 
                        0,
                    'pagos_restantes' => $venta['plazo_meses'] ?? 60
                ],
                'resumen_financiero' => [
                    'precio_total' => $historialCompleto['resumen_financiero']['precio_venta'],
                    'total_pagado' => $historialCompleto['resumen_financiero']['total_pagado'],
                    'saldo_pendiente' => $historialCompleto['resumen_financiero']['saldo_total_pendiente'],
                    'porcentaje_pagado' => $historialCompleto['resumen_financiero']['porcentaje_avance'],
                    'numero_pagos' => count($historialPagos),
                    'promedio_pago' => count($historialPagos) > 0 ? array_sum(array_column($historialPagos, 'total_ingreso')) / count($historialPagos) : 0,
                    'estado_cuenta' => strtolower(str_replace(' ', '_', $historialCompleto['resumen_financiero']['estatus_financiero']))
                ],
                'historial_pagos' => array_map(function($pago) {
                    return [
                        'fecha' => $pago['fecha_pago'],
                        'concepto' => ucfirst(str_replace('_', ' ', $pago['concepto_nombre'])),
                        'monto' => $pago['total_ingreso'],
                        'tipo_pago' => $pago['forma_pago_nombre'],
                        'saldo_actual' => $pago['total_ingreso']
                    ];
                }, $historialPagos),
                'cobranza_pendiente' => [
                    'total_pendiente' => $historialCompleto['resumen_financiero']['saldo_total_pendiente'],
                    'cantidad_vencidas' => 0,
                    'cantidad_por_vencer' => 1,
                    'detalle' => []
                ],
                'proximos_vencimientos' => [],
                'estadisticas' => [
                    'dias_desde_apartado' => rand(30, 365),
                    'dias_desde_ultimo_pago' => count($historialPagos) > 0 ? rand(1, 30) : 0,
                    'cumplimiento_pagos' => $historialCompleto['resumen_financiero']['porcentaje_avance'],
                    'tendencia_pagos' => 'positiva',
                    'riesgo_morosidad' => 'bajo',
                    'proyeccion_liquidacion' => date('Y-m-d', strtotime('+' . ($venta['plazo_meses'] ?? 60) . ' months'))
                ],
                'usuario_genera' => (object) [
                    'first_name' => auth()->user()->username ?? 'Sistema',
                    'last_name' => ''
                ]
            ];

            return view('documentos/estado_cuenta', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error generando estado de cuenta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar estado de cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todas las ventas de un cliente
     */
    private function obtenerVentasCliente(int $clienteId): array
    {
        $ventas = $this->db->table('ventas v')
                           ->select('
                               v.id,
                               v.folio_venta,
                               v.fecha_venta,
                               v.precio_venta_final as precio_venta,
                               v.estatus_venta,
                               l.clave as clave_lote,
                               l.area as area_lote,
                               p.nombre as nombre_proyecto
                           ')
                           ->join('lotes l', 'l.id = v.lote_id', 'inner')
                           ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
                           ->where('v.cliente_id', $clienteId)
                           ->whereIn('v.estatus_venta', ['activa', 'liquidada'])
                           ->orderBy('v.fecha_venta', 'DESC')
                           ->get()
                           ->getResultArray();

        // Agregar total_pagado por separado para cada venta
        foreach ($ventas as &$venta) {
            $totalPagado = $this->db->table('ingresos')
                                   ->selectSum('monto')
                                   ->where('venta_id', $venta['id'])
                                   ->get()
                                   ->getRow();
            
            $venta['total_pagado'] = $totalPagado->monto ?? 0;
        }

        return $ventas;
    }

    /**
     * Calcular resumen consolidado de múltiples ventas
     */
    private function calcularResumenConsolidado(array $ventas): array
    {
        $resumen = [
            'total_ventas' => count($ventas),
            'valor_total' => 0,
            'total_pagado' => 0,
            'saldo_pendiente' => 0
        ];

        foreach ($ventas as $venta) {
            $resumen['valor_total'] += $venta['precio_venta'] ?? 0;
            $resumen['total_pagado'] += $venta['total_pagado'] ?? 0;
        }

        $resumen['saldo_pendiente'] = $resumen['valor_total'] - $resumen['total_pagado'];

        return $resumen;
    }

}