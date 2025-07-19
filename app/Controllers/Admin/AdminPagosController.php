<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ProcesadorPagosService;
use App\Services\RefactorizacionService;
use App\Services\EstadoCuentaService;
use App\Models\VentaModel;
use App\Models\ClienteModel;
use App\Models\LoteModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller AdminPagosController
 * 
 * Controlador principal para gestión de pagos inmobiliarios
 * Maneja apartados, liquidaciones, mensualidades y abonos a capital
 */
class AdminPagosController extends BaseController
{
    protected ProcesadorPagosService $procesadorService;
    protected RefactorizacionService $refactorizacionService;
    protected EstadoCuentaService $estadoCuentaService;
    protected VentaModel $ventaModel;
    protected ClienteModel $clienteModel;
    protected LoteModel $loteModel;

    public function __construct()
    {
        $this->procesadorService = new ProcesadorPagosService();
        $this->refactorizacionService = new RefactorizacionService();
        $this->estadoCuentaService = new EstadoCuentaService();
        $this->ventaModel = new VentaModel();
        $this->clienteModel = new ClienteModel();
        $this->loteModel = new LoteModel();
    }

    /**
     * Vista principal de pagos
     */
    public function index()
    {
        $data = [
            'title' => 'Gestión de Pagos Inmobiliarios',
            'ventas_activas' => $this->obtenerVentasActivas(),
            'estadisticas' => $this->obtenerEstadisticasPagos()
        ];

        return view('admin/pagos/index', $data);
    }

    /**
     * Vista detalle de cliente-lote
     */
    public function detalle(int $clienteId, int $loteId)
    {
        try {
            // Cargar helper de pagos inmobiliarios
            helper('pagos_inmobiliarios');
            
            // Obtener historial completo usando el helper
            $historial = obtener_historial_pagos_lote($clienteId, $loteId);
            
            if (!$historial['success']) {
                return redirect()->back()->with('error', $historial['error']);
            }

            $data = [
                'title' => 'Detalle Cliente-Lote',
                'historial' => $historial,
                'cliente_id' => $clienteId,
                'lote_id' => $loteId
            ];

            return view('admin/pagos/detalle', $data);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error cargando detalle: ' . $e->getMessage());
        }
    }

    /**
     * Vista para procesar apartado
     */
    public function procesarApartado(int $ventaId)
    {
        $venta = $this->ventaModel->find($ventaId);
        if (!$venta) {
            return redirect()->back()->with('error', 'Venta no encontrada');
        }

        // Obtener información del anticipo requerido (implementación temporal)
        $infoAnticipo = $this->calcularAnticipoRequerido($ventaId);
        
        $data = [
            'title' => 'Procesar Pago de Apartado',
            'venta' => $venta,
            'info_anticipo' => $infoAnticipo,
            'cliente' => $this->clienteModel->find($venta->cliente_id),
            'lote' => $this->loteModel->find($venta->lote_id)
        ];

        return view('admin/pagos/procesar_apartado', $data);
    }

    /**
     * Vista para procesar anticipo (alias de apartado)
     */
    public function procesarAnticipo(int $ventaId)
    {
        // Redirigir al método procesarApartado que ya existe
        return $this->procesarApartado($ventaId);
    }

    /**
     * Guardar pago de apartado
     */
    public function guardarApartado()
    {
        $datos = [
            'venta_id' => $this->request->getPost('venta_id'),
            'monto' => (float)$this->request->getPost('monto'),
            'fecha_aplicacion' => $this->request->getPost('fecha_aplicacion'),
            'forma_pago' => $this->request->getPost('forma_pago'),
            'referencia' => $this->request->getPost('referencia'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        $resultado = $this->procesadorService->procesarPagoApartado($datos);

        if ($resultado['success']) {
            $mensaje = 'Pago de apartado procesado exitosamente';
            if ($resultado['liquidacion_generada']) {
                $mensaje .= '. Liquidación de enganche generada automáticamente';
            }
            
            return redirect()->to("/admin/pagos/detalle/{$this->getClienteIdFromVenta($datos['venta_id'])}/{$this->getLoteIdFromVenta($datos['venta_id'])}")
                           ->with('success', $mensaje);
        } else {
            return redirect()->back()->with('error', $resultado['error'])->withInput();
        }
    }

    /**
     * Vista para liquidar enganche
     */
    public function liquidarEnganche(int $ventaId)
    {
        $venta = $this->ventaModel->find($ventaId);
        if (!$venta) {
            return redirect()->back()->with('error', 'Venta no encontrada');
        }

        // Obtener resumen de anticipos (implementación temporal)
        $resumenAnticipos = $this->obtenerResumenAnticipos($ventaId);
        
        $data = [
            'title' => 'Liquidar Enganche',
            'venta' => $venta,
            'resumen_anticipos' => $resumenAnticipos,
            'cliente' => $this->clienteModel->find($venta->cliente_id),
            'lote' => $this->loteModel->find($venta->lote_id)
        ];

        return view('admin/pagos/liquidar_enganche', $data);
    }

    /**
     * Guardar liquidación de enganche
     */
    public function guardarLiquidacion()
    {
        $datos = [
            'venta_id' => $this->request->getPost('venta_id'),
            'monto' => (float)$this->request->getPost('monto'),
            'monto_enganche_requerido' => (float)$this->request->getPost('monto_enganche_requerido'),
            'fecha_aplicacion' => $this->request->getPost('fecha_aplicacion'),
            'forma_pago' => $this->request->getPost('forma_pago'),
            'referencia' => $this->request->getPost('referencia'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        $resultado = $this->procesadorService->procesarLiquidacionEnganche($datos);

        if ($resultado['success']) {
            $mensaje = 'Liquidación procesada exitosamente';
            if ($resultado['cuenta_abierta']) {
                $mensaje .= '. Cuenta de financiamiento abierta automáticamente';
            }
            
            return redirect()->to("/admin/pagos/detalle/{$this->getClienteIdFromVenta($datos['venta_id'])}/{$this->getLoteIdFromVenta($datos['venta_id'])}")
                           ->with('success', $mensaje);
        } else {
            return redirect()->back()->with('error', $resultado['error'])->withInput();
        }
    }

    /**
     * Vista para procesar mensualidad
     */
    public function procesarMensualidad(int $ventaId)
    {
        $venta = $this->ventaModel->find($ventaId);
        if (!$venta) {
            return redirect()->back()->with('error', 'Venta no encontrada');
        }

        // Obtener información de la cuenta
        $resumen = $this->procesadorService->obtenerResumenCuenta($ventaId);
        
        $data = [
            'title' => 'Procesar Mensualidad',
            'venta' => $venta,
            'resumen' => $resumen,
            'cliente' => $this->clienteModel->find($venta->cliente_id),
            'lote' => $this->loteModel->find($venta->lote_id)
        ];

        return view('admin/pagos/procesar_mensualidad', $data);
    }

    /**
     * Guardar pago de mensualidad
     */
    public function guardarMensualidad()
    {
        $datos = [
            'venta_id' => $this->request->getPost('venta_id'),
            'monto' => (float)$this->request->getPost('monto'),
            'fecha_pago' => $this->request->getPost('fecha_aplicacion'),
            'forma_pago' => $this->request->getPost('forma_pago'),
            'referencia' => $this->request->getPost('referencia'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        $resultado = $this->procesadorService->procesarPagoMensualidad($datos);

        if ($resultado['success']) {
            $mensaje = 'Mensualidad procesada exitosamente';
            if ($resultado['cuenta_liquidada']) {
                $mensaje .= '. ¡Cuenta liquidada completamente!';
            }
            
            return redirect()->to("/admin/pagos/detalle/{$this->getClienteIdFromVenta($datos['venta_id'])}/{$this->getLoteIdFromVenta($datos['venta_id'])}")
                           ->with('success', $mensaje);
        } else {
            return redirect()->back()->with('error', $resultado['error'])->withInput();
        }
    }

    /**
     * Vista para abono a capital
     */
    public function abonoCapital(int $ventaId)
    {
        $venta = $this->ventaModel->find($ventaId);
        if (!$venta) {
            return redirect()->back()->with('error', 'Venta no encontrada');
        }

        // Obtener información de la cuenta
        $resumen = $this->procesadorService->obtenerResumenCuenta($ventaId);
        
        $data = [
            'title' => 'Abono a Capital',
            'venta' => $venta,
            'resumen' => $resumen,
            'cliente' => $this->clienteModel->find($venta->cliente_id),
            'lote' => $this->loteModel->find($venta->lote_id)
        ];

        return view('admin/pagos/abono_capital', $data);
    }

    /**
     * Simular abono a capital (AJAX)
     */
    public function simularAbono()
    {
        $ventaId = $this->request->getPost('venta_id');
        $monto = (float)$this->request->getPost('monto');

        $simulacion = $this->procesadorService->simularAbonoCapital($ventaId, $monto);

        return $this->response->setJSON($simulacion);
    }

    /**
     * Guardar abono a capital
     */
    public function guardarAbonoCapital()
    {
        $datos = [
            'venta_id' => $this->request->getPost('venta_id'),
            'monto' => (float)$this->request->getPost('monto'),
            'fecha_aplicacion' => $this->request->getPost('fecha_aplicacion'),
            'forma_pago' => $this->request->getPost('forma_pago'),
            'referencia' => $this->request->getPost('referencia'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        $resultado = $this->procesadorService->procesarAbonoCapital($datos);

        if ($resultado['success']) {
            $mensaje = sprintf(
                'Abono a capital procesado. Nueva mensualidad: $%s. Ahorro en intereses: $%s',
                number_format($resultado['nueva_mensualidad'], 2),
                number_format($resultado['ahorro_intereses'], 2)
            );
            
            return redirect()->to("/admin/pagos/detalle/{$this->getClienteIdFromVenta($datos['venta_id'])}/{$this->getLoteIdFromVenta($datos['venta_id'])}")
                           ->with('success', $mensaje);
        } else {
            return redirect()->back()->with('error', $resultado['error'])->withInput();
        }
    }

    /**
     * Vista de refactorizaciones
     */
    public function refactorizaciones(int $ventaId)
    {
        $venta = $this->ventaModel->find($ventaId);
        if (!$venta) {
            return redirect()->back()->with('error', 'Venta no encontrada');
        }

        // Obtener cuenta y historial
        $cuenta = $this->obtenerCuentaFinanciamiento($ventaId);
        $historial = $cuenta ? $this->refactorizacionService->obtenerHistorialRefactorizaciones($cuenta->id) : [];
        
        $data = [
            'title' => 'Historial de Refactorizaciones',
            'venta' => $venta,
            'cuenta' => $cuenta,
            'historial' => $historial,
            'cliente' => $this->clienteModel->find($venta->cliente_id),
            'lote' => $this->loteModel->find($venta->lote_id)
        ];

        return view('admin/pagos/refactorizaciones', $data);
    }

    /**
     * Generar estado de cuenta
     */
    public function estadoCuenta(int $ventaId)
    {
        $opciones = [
            'incluir_tabla_amortizacion' => true,
            'incluir_movimientos_detallados' => true,
            'incluir_refactorizaciones' => true,
            'incluir_analisis_cumplimiento' => true
        ];

        $estadoCuenta = $this->estadoCuentaService->generarEstadoCuenta($ventaId, $opciones);

        if ($estadoCuenta['success']) {
            $data = [
                'title' => 'Estado de Cuenta',
                'estado_cuenta' => $estadoCuenta['estado_cuenta']
            ];

            return view('admin/pagos/estado_cuenta', $data);
        } else {
            return redirect()->back()->with('error', $estadoCuenta['error']);
        }
    }

    /**
     * Exportar estado de cuenta a PDF
     */
    public function exportarEstadoCuenta(int $ventaId)
    {
        $opciones = [
            'incluir_tabla_amortizacion' => true,
            'incluir_movimientos_detallados' => false,
            'incluir_refactorizaciones' => true,
            'incluir_analisis_cumplimiento' => true
        ];

        $estadoCuenta = $this->estadoCuentaService->generarEstadoCuenta($ventaId, $opciones);

        if (!$estadoCuenta['success']) {
            return redirect()->back()->with('error', $estadoCuenta['error']);
        }

        // Generar PDF usando la librería de reportes
        $pdf = $this->generarPDFEstadoCuenta($estadoCuenta['estado_cuenta']);
        
        return $this->response->download($pdf, null, true);
    }

    /**
     * Búsqueda AJAX de ventas
     */
    public function buscarVentas()
    {
        $termino = $this->request->getGet('q');
        
        // Si no hay término de búsqueda, retornar array vacío
        if (empty($termino)) {
            return $this->response->setJSON([]);
        }
        
        $ventas = $this->ventaModel->select('
                ventas.id,
                ventas.folio_venta,
                ventas.estatus_venta,
                clientes.nombres,
                clientes.apellido_paterno,
                clientes.apellido_materno,
                lotes.clave as lote_clave,
                proyectos.nombre as proyecto_nombre
            ')
            ->join('clientes', 'clientes.id = ventas.cliente_id')
            ->join('lotes', 'lotes.id = ventas.lote_id')
            ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
            ->groupStart()
                ->like('ventas.folio_venta', $termino)
                ->orLike('clientes.nombres', $termino)
                ->orLike('clientes.apellido_paterno', $termino)
                ->orLike('lotes.clave', $termino)
            ->groupEnd()
            ->where('ventas.estatus_venta !=', 'cancelada')
            ->limit(10)
            ->get()
            ->getResult();

        return $this->response->setJSON($ventas);
    }

    /**
     * Dashboard de pagos (AJAX)
     */
    public function dashboardPagos()
    {
        $estadisticas = [
            'pagos_hoy' => $this->contarPagosHoy(),
            'pagos_mes' => $this->contarPagosMes(),
            'cuentas_vencidas' => $this->contarCuentasVencidas(),
            'monto_recaudado_mes' => $this->calcularMontoRecaudadoMes()
        ];

        return $this->response->setJSON($estadisticas);
    }

    // Métodos auxiliares

    private function obtenerVentasActivas(): array
    {
        return $this->ventaModel->select('
                ventas.*,
                clientes.nombres,
                clientes.apellido_paterno,
                clientes.apellido_materno,
                lotes.clave as lote_clave,
                proyectos.nombre as proyecto_nombre
            ')
            ->join('clientes', 'clientes.id = ventas.cliente_id')
            ->join('lotes', 'lotes.id = ventas.lote_id')
            ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
            ->where('ventas.estatus_venta', 'activa')
            ->orderBy('ventas.fecha_venta', 'DESC')
            ->limit(20)
            ->get()
            ->getResult();
    }

    private function obtenerEstadisticasPagos(): array
    {
        $db = \Config\Database::connect();
        
        // Verificar si las tablas existen antes de hacer consultas
        $tablas = $db->query("SHOW TABLES LIKE 'cuentas_financiamiento'")->getResult();
        if (empty($tablas)) {
            return [
                'total_ventas_activas' => 0,
                'total_cuentas_activas' => 0,
                'monto_pendiente_total' => 0,
                'pagos_mes_actual' => 0
            ];
        }
        
        return [
            'total_ventas_activas' => $db->query("SELECT COUNT(*) as total FROM ventas WHERE estatus_venta = 'activa'")->getRow()->total,
            'total_cuentas_activas' => $db->query("SELECT COUNT(*) as total FROM cuentas_financiamiento")->getRow()->total,
            'monto_pendiente_total' => $db->query("SELECT SUM(saldo_actual) as total FROM cuentas_financiamiento")->getRow()->total ?? 0,
            'pagos_mes_actual' => $db->query("SELECT COUNT(*) as total FROM conceptos_pago WHERE MONTH(fecha_aplicacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_aplicacion) = YEAR(CURRENT_DATE())")->getRow()->total
        ];
    }

    private function getClienteIdFromVenta(int $ventaId): int
    {
        $venta = $this->ventaModel->find($ventaId);
        return $venta ? $venta->cliente_id : 0;
    }

    private function getLoteIdFromVenta(int $ventaId): int
    {
        $venta = $this->ventaModel->find($ventaId);
        return $venta ? $venta->lote_id : 0;
    }

    private function obtenerCuentaFinanciamiento(int $ventaId)
    {
        $cuentaModel = new \App\Models\CuentaFinanciamientoModel();
        return $cuentaModel->where('venta_id', $ventaId)->first();
    }

    private function generarPDFEstadoCuenta(array $estadoCuenta): string
    {
        // Implementar generación de PDF
        // Por ahora retornamos un placeholder
        return 'estado_cuenta_' . date('Y-m-d_H-i-s') . '.pdf';
    }

    /**
     * Crear vista detalle lote (implementación temporal)
     */
    private function crearVistaDetalleLote(int $clienteId, int $loteId): array
    {
        return [
            'success' => true,
            'data' => [
                'cliente_id' => $clienteId,
                'lote_id' => $loteId,
                'historial' => []
            ]
        ];
    }

    /**
     * Calcular anticipo requerido (implementación temporal)
     */
    private function calcularAnticipoRequerido(int $ventaId): array
    {
        $db = \Config\Database::connect();
        
        // Obtener datos de la venta y perfil financiero
        $venta = $db->query("
            SELECT 
                v.precio_venta_final,
                v.estatus_venta,
                pf.apartado_minimo,
                pf.porcentaje_interes_anual,
                pf.enganche_minimo
            FROM ventas v 
            LEFT JOIN perfiles_financiamiento pf ON pf.id = v.perfil_financiamiento_id
            WHERE v.id = ?
        ", [$ventaId])->getRow();
        
        if (!$venta) {
            return ['error' => 'Venta no encontrada'];
        }
        
        // Calcular apartado requerido (mínimo o porcentaje del precio)
        $montoApartado = max($venta->apartado_minimo ?? 25000, $venta->precio_venta_final * 0.05);
        
        // Obtener pagos de apartado ya aplicados
        $pagosApartado = $db->query("
            SELECT COALESCE(SUM(monto), 0) as total_pagado
            FROM ingresos 
            WHERE venta_id = ? AND tipo_ingreso = 'apartado'
        ", [$ventaId])->getRow()->total_pagado ?? 0;
        
        $saldoPendiente = max(0, $montoApartado - $pagosApartado);
        
        return [
            'monto_requerido' => $montoApartado,
            'monto_aplicado' => $pagosApartado,
            'saldo_pendiente' => $saldoPendiente,
            'precio_venta' => $venta->precio_venta_final,
            'status_apartado' => $saldoPendiente > 0 ? 'pendiente' : 'liquidado',
            'porcentaje_completado' => $montoApartado > 0 ? round(($pagosApartado / $montoApartado) * 100, 1) : 0
        ];
    }

    /**
     * Obtener resumen de anticipos (implementación temporal)
     */
    private function obtenerResumenAnticipos(int $ventaId): array
    {
        $db = \Config\Database::connect();
        
        // Obtener datos de la venta y perfil financiero
        $venta = $db->query("
            SELECT 
                v.precio_venta_final,
                v.estatus_venta,
                pf.apartado_minimo,
                pf.enganche_minimo,
                pf.porcentaje_anticipo,
                pf.tipo_anticipo
            FROM ventas v 
            LEFT JOIN perfiles_financiamiento pf ON pf.id = v.perfil_financiamiento_id
            WHERE v.id = ?
        ", [$ventaId])->getRow();
        
        if (!$venta) {
            return ['error' => 'Venta no encontrada'];
        }
        
        // Calcular enganche requerido
        $engancheRequerido = ($venta->tipo_anticipo === 'porcentaje') 
            ? $venta->precio_venta_final * ($venta->porcentaje_anticipo / 100)
            : $venta->enganche_minimo;
            
        // Obtener pagos de apartado aplicados
        $pagosApartado = $db->query("
            SELECT COALESCE(SUM(monto), 0) as total
            FROM ingresos 
            WHERE venta_id = ? AND tipo_ingreso = 'apartado'
        ", [$ventaId])->getRow()->total ?? 0;
        
        // Obtener pagos de enganche aplicados
        $pagosEnganche = $db->query("
            SELECT COALESCE(SUM(monto), 0) as total
            FROM ingresos 
            WHERE venta_id = ? AND tipo_ingreso = 'enganche'
        ", [$ventaId])->getRow()->total ?? 0;
        
        $totalAnticipos = $pagosApartado + $pagosEnganche;
        $saldoEnganche = max(0, $engancheRequerido - $totalAnticipos);
        
        return [
            'total_anticipos' => $totalAnticipos,
            'pagos_apartado' => $pagosApartado,
            'pagos_enganche' => $pagosEnganche,
            'enganche_requerido' => $engancheRequerido,
            'anticipos_aplicados' => $totalAnticipos,
            'saldo_pendiente' => $saldoEnganche,
            'precio_venta' => $venta->precio_venta_final,
            'status_enganche' => $saldoEnganche > 0 ? 'pendiente' : 'liquidado',
            'porcentaje_completado' => $engancheRequerido > 0 ? round(($totalAnticipos / $engancheRequerido) * 100, 1) : 0,
            'monto_financiado' => $venta->precio_venta_final - $totalAnticipos,
            'detalle' => []
        ];
    }

    private function contarPagosHoy(): int
    {
        $db = \Config\Database::connect();
        return $db->query("SELECT COUNT(*) as total FROM conceptos_pago WHERE DATE(fecha_aplicacion) = CURRENT_DATE()")->getRow()->total;
    }

    private function contarPagosMes(): int
    {
        $db = \Config\Database::connect();
        return $db->query("SELECT COUNT(*) as total FROM conceptos_pago WHERE MONTH(fecha_aplicacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_aplicacion) = YEAR(CURRENT_DATE())")->getRow()->total;
    }

    private function contarCuentasVencidas(): int
    {
        $db = \Config\Database::connect();
        return $db->query("SELECT COUNT(DISTINCT plan_financiamiento_id) as total FROM tabla_amortizacion WHERE estatus = 'vencida'")->getRow()->total;
    }

    private function calcularMontoRecaudadoMes(): float
    {
        $db = \Config\Database::connect();
        return $db->query("SELECT SUM(monto) as total FROM conceptos_pago WHERE MONTH(fecha_aplicacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_aplicacion) = YEAR(CURRENT_DATE())")->getRow()->total ?? 0;
    }
}