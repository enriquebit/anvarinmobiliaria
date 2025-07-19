<?php

namespace App\Services;

use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\ConceptoPagoModel;
use App\Models\CuentaFinanciamientoModel;
use App\Controllers\Admin\AdminEstadoCuentaController;
use CodeIgniter\Database\BaseConnection;
use Exception;

/**
 * Service IntegracionEstadoCuentaService
 * 
 * Integra el módulo de pagos inmobiliarios con el módulo de estado de cuenta existente
 * Proporciona compatibilidad bidireccional entre ambos sistemas
 */
class IntegracionEstadoCuentaService
{
    protected VentaModel $ventaModel;
    protected TablaAmortizacionModel $tablaModel;
    protected PagoVentaModel $pagoVentaModel;
    protected ConceptoPagoModel $conceptoModel;
    protected CuentaFinanciamientoModel $cuentaModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->tablaModel = new TablaAmortizacionModel();
        $this->pagoVentaModel = new PagoVentaModel();
        $this->conceptoModel = new ConceptoPagoModel();
        $this->cuentaModel = new CuentaFinanciamientoModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Sincronizar datos entre módulos
     */
    public function sincronizarDatos(int $ventaId): array
    {
        try {
            // Obtener datos del módulo de pagos inmobiliarios
            $datosPagos = $this->obtenerDatosPagos($ventaId);
            
            // Obtener datos del módulo de estado de cuenta
            $datosEstadoCuenta = $this->obtenerDatosEstadoCuenta($ventaId);
            
            // Sincronizar conceptos de pago con pagos de venta
            $this->sincronizarConceptos($ventaId, $datosPagos, $datosEstadoCuenta);
            
            // Sincronizar tabla de amortización
            $this->sincronizarTablaAmortizacion($ventaId, $datosPagos);
            
            return [
                'success' => true,
                'mensaje' => 'Datos sincronizados correctamente',
                'datos_pagos' => $datosPagos,
                'datos_estado_cuenta' => $datosEstadoCuenta
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error sincronizando datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar estado de cuenta unificado
     */
    public function generarEstadoCuentaUnificado(int $ventaId): array
    {
        try {
            // Obtener datos del módulo de pagos inmobiliarios
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                throw new Exception('Venta no encontrada');
            }

            // Obtener historial de pagos inmobiliarios
            $historialPagos = obtener_historial_pagos_lote($venta->cliente_id, $venta->lote_id);
            
            // Obtener datos del estado de cuenta tradicional
            $estadoCuentaTradicional = $this->obtenerEstadoCuentaTradicional($ventaId);
            
            // Unificar ambos sistemas
            $estadoUnificado = $this->unificarEstados($historialPagos, $estadoCuentaTradicional);
            
            return [
                'success' => true,
                'estado_unificado' => $estadoUnificado,
                'venta' => $venta
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error generando estado unificado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear concepto de pago desde pago de venta
     */
    public function crearConceptoDesdePagoVenta(int $pagoVentaId): array
    {
        try {
            $pagoVenta = $this->pagoVentaModel->find($pagoVentaId);
            if (!$pagoVenta) {
                throw new Exception('Pago de venta no encontrado');
            }

            // Determinar tipo de concepto
            $tipoConcepto = $this->determinarTipoConcepto($pagoVenta);
            
            // Crear concepto de pago
            $conceptoPago = $this->conceptoModel->crearConceptoMensualidad([
                'venta_id' => $pagoVenta->venta_id,
                'monto' => $pagoVenta->monto,
                'fecha_pago' => $pagoVenta->fecha_pago,
                'forma_pago' => $pagoVenta->forma_pago ?? 'efectivo',
                'referencia' => $pagoVenta->referencia ?? '',
                'descripcion' => 'Migrado desde pago de venta: ' . $pagoVenta->id
            ]);

            $this->conceptoModel->save($conceptoPago);

            return [
                'success' => true,
                'concepto_id' => $conceptoPago->id,
                'pago_venta_id' => $pagoVentaId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error creando concepto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Migrar datos existentes al nuevo sistema
     */
    public function migrarDatosExistentes(int $ventaId = null): array
    {
        try {
            $builder = $this->ventaModel->builder();
            
            if ($ventaId) {
                $builder->where('id', $ventaId);
            }
            
            $ventas = $builder->get()->getResult();
            $ventasMigradas = 0;
            $errores = [];

            foreach ($ventas as $venta) {
                try {
                    // Migrar pagos de venta a conceptos de pago
                    $this->migrarPagosVenta($venta->id);
                    
                    // Crear cuenta de financiamiento si no existe
                    $this->crearCuentaFinanciamientoSiNoExiste($venta->id);
                    
                    // Sincronizar tabla de amortización
                    $this->sincronizarTablaAmortizacion($venta->id, []);
                    
                    $ventasMigradas++;
                    
                } catch (Exception $e) {
                    $errores[] = "Venta {$venta->id}: " . $e->getMessage();
                }
            }

            return [
                'success' => true,
                'ventas_migradas' => $ventasMigradas,
                'errores' => $errores,
                'total_ventas' => count($ventas)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error migrando datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar compatibilidad entre sistemas
     */
    public function verificarCompatibilidad(): array
    {
        try {
            $problemas = [];
            
            // Verificar tablas necesarias
            if (!$this->db->tableExists('conceptos_pago')) {
                $problemas[] = 'Tabla conceptos_pago no existe';
            }
            
            if (!$this->db->tableExists('cuentas_financiamiento')) {
                $problemas[] = 'Tabla cuentas_financiamiento no existe';
            }
            
            if (!$this->db->tableExists('movimientos_cuenta')) {
                $problemas[] = 'Tabla movimientos_cuenta no existe';
            }

            // Verificar helpers necesarios
            if (!function_exists('obtener_historial_pagos_lote')) {
                $problemas[] = 'Helper pagos_inmobiliarios_helper no cargado';
            }

            if (!function_exists('generar_resumen_cliente')) {
                $problemas[] = 'Helper estado_cuenta_helper no cargado';
            }

            // Verificar conflictos de datos
            $conflictos = $this->detectarConflictosDatos();
            $problemas = array_merge($problemas, $conflictos);

            return [
                'success' => empty($problemas),
                'problemas' => $problemas,
                'compatible' => empty($problemas)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error verificando compatibilidad: ' . $e->getMessage()
            ];
        }
    }

    // Métodos privados de apoyo

    private function obtenerDatosPagos(int $ventaId): array
    {
        return [
            'conceptos_pago' => $this->conceptoModel->getPagosPorVenta($ventaId),
            'cuenta_financiamiento' => $this->cuentaModel->where('venta_id', $ventaId)->first(),
            'estadisticas' => $this->conceptoModel->getEstadisticasPorConcepto($ventaId)
        ];
    }

    private function obtenerDatosEstadoCuenta(int $ventaId): array
    {
        return [
            'pagos_venta' => $this->pagoVentaModel->where('venta_id', $ventaId)->findAll(),
            'tabla_amortizacion' => $this->tablaModel->where('venta_id', $ventaId)->findAll(),
            'mensualidades_pendientes' => $this->tablaModel->where('venta_id', $ventaId)
                                                           ->where('estatus', 'pendiente')
                                                           ->findAll()
        ];
    }

    private function sincronizarConceptos(int $ventaId, array $datosPagos, array $datosEstadoCuenta): void
    {
        foreach ($datosEstadoCuenta['pagos_venta'] as $pagoVenta) {
            // Verificar si ya existe un concepto para este pago
            $conceptoExistente = $this->conceptoModel->where('venta_id', $ventaId)
                                                   ->where('referencia', 'PV-' . $pagoVenta->id)
                                                   ->first();
            
            if (!$conceptoExistente) {
                $this->crearConceptoDesdePagoVenta($pagoVenta->id);
            }
        }
    }

    private function sincronizarTablaAmortizacion(int $ventaId, array $datosPagos): void
    {
        // Obtener tabla de amortización existente
        $tablaExistente = $this->tablaModel->where('venta_id', $ventaId)->findAll();
        
        // Obtener cuenta de financiamiento
        $cuenta = $this->cuentaModel->where('venta_id', $ventaId)->first();
        
        if ($cuenta && !empty($tablaExistente)) {
            // Actualizar información de la cuenta basada en la tabla
            $this->actualizarCuentaDesdTabla($cuenta, $tablaExistente);
        }
    }

    private function obtenerEstadoCuentaTradicional(int $ventaId): array
    {
        // Usar el controller existente para obtener datos
        $estadoCuentaController = new AdminEstadoCuentaController();
        
        // Simular request para obtener datos
        $request = \Config\Services::request();
        $request->setGlobal('get', ['venta_id' => $ventaId]);
        
        return [
            'existe' => true,
            'datos' => [] // Aquí se integrarían los datos del sistema existente
        ];
    }

    private function unificarEstados(array $historialPagos, array $estadoCuentaTradicional): array
    {
        return [
            'resumen_unificado' => [
                'fuente_pagos' => $historialPagos['success'] ? 'pagos_inmobiliarios' : 'estado_cuenta',
                'info_base' => $historialPagos['info_base'] ?? [],
                'resumen_financiero' => $historialPagos['resumen_financiero'] ?? [],
                'compatibilidad' => 'alta'
            ],
            'datos_pagos_inmobiliarios' => $historialPagos,
            'datos_estado_cuenta' => $estadoCuentaTradicional
        ];
    }

    private function determinarTipoConcepto($pagoVenta): string
    {
        // Lógica para determinar el tipo de concepto basado en el pago de venta
        if (strpos(strtolower($pagoVenta->concepto ?? ''), 'apartado') !== false) {
            return 'apartado';
        }
        
        if (strpos(strtolower($pagoVenta->concepto ?? ''), 'enganche') !== false) {
            return 'liquidacion_enganche';
        }
        
        if (strpos(strtolower($pagoVenta->concepto ?? ''), 'mensualidad') !== false) {
            return 'mensualidad';
        }
        
        return 'mensualidad'; // Por defecto
    }

    private function migrarPagosVenta(int $ventaId): void
    {
        $pagosVenta = $this->pagoVentaModel->where('venta_id', $ventaId)->findAll();
        
        foreach ($pagosVenta as $pagoVenta) {
            $this->crearConceptoDesdePagoVenta($pagoVenta->id);
        }
    }

    private function crearCuentaFinanciamientoSiNoExiste(int $ventaId): void
    {
        $cuentaExistente = $this->cuentaModel->where('venta_id', $ventaId)->first();
        
        if (!$cuentaExistente) {
            $venta = $this->ventaModel->find($ventaId);
            if ($venta) {
                $cuenta = new \App\Entities\CuentaFinanciamiento();
                $cuenta->venta_id = $ventaId;
                $cuenta->capital_inicial = $venta->precio_venta_final * 0.8; // 80% si el enganche es 20%
                $cuenta->saldo_capital = $cuenta->capital_inicial;
                $cuenta->saldo_total = $cuenta->capital_inicial;
                $cuenta->monto_mensualidad = $cuenta->capital_inicial / 60; // 60 meses por defecto
                $cuenta->estado = 'activa';
                $cuenta->fecha_apertura = date('Y-m-d');
                $cuenta->generarNumeroCuenta();
                
                $this->cuentaModel->save($cuenta);
            }
        }
    }

    private function actualizarCuentaDesdTabla($cuenta, array $tablaAmortizacion): void
    {
        $pagosPendientes = array_filter($tablaAmortizacion, fn($pago) => $pago->estatus === 'pendiente');
        $pagosVencidos = array_filter($tablaAmortizacion, fn($pago) => $pago->estatus === 'vencida');
        
        $cuenta->pagos_vencidos = count($pagosVencidos);
        $cuenta->meses_transcurridos = count($tablaAmortizacion) - count($pagosPendientes);
        $cuenta->meses_restantes = count($pagosPendientes);
        
        $this->cuentaModel->save($cuenta);
    }

    private function detectarConflictosDatos(): array
    {
        $conflictos = [];
        
        // Verificar duplicados en conceptos de pago
        $duplicados = $this->db->query("
            SELECT venta_id, COUNT(*) as total 
            FROM conceptos_pago 
            GROUP BY venta_id, concepto, fecha_pago, monto 
            HAVING COUNT(*) > 1
        ")->getResult();
        
        if (!empty($duplicados)) {
            $conflictos[] = 'Encontrados conceptos de pago duplicados';
        }
        
        return $conflictos;
    }
}