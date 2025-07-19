<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\PagoVenta;

class PagoVentaModel extends Model
{
    protected $table            = 'pagos_ventas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = PagoVenta::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'venta_id',
        'plan_financiamiento_id',
        'cuenta_bancaria_id',
        'tabla_amortizacion_id',
        'folio_pago',
        'fecha_pago',
        'monto_pago',
        'forma_pago',
        'concepto_pago',
        'descripcion_concepto',
        'numero_mensualidad',
        'referencia_pago',
        'comprobante_url',
        'estatus_pago',
        'fecha_cancelacion',
        'motivo_cancelacion',
        'usuario_cancela_id',
        'registrado_por',
        'es_pago_adelantado'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'plan_financiamiento_id' => '?integer',
        'cuenta_bancaria_id' => '?integer',
        'tabla_amortizacion_id' => '?integer',
        'numero_mensualidad' => '?integer',
        'usuario_cancela_id' => '?integer',
        'registrado_por' => 'integer',
        'monto_pago' => 'float',
        'es_pago_adelantado' => 'boolean'
    ];
    protected array $castHandlers = [];

    // Validation
    protected $validationRules = [
        'venta_id' => 'required|is_natural_no_zero',
        'monto_pago' => 'required|decimal|greater_than_equal_to[0]',
        'forma_pago' => 'required|in_list[efectivo,transferencia,cheque,tarjeta,deposito]',
        'concepto_pago' => 'required|in_list[apartado,enganche,mensualidad,moratorio,penalizacion,liquidacion,otro]',
        'registrado_por' => 'required|is_natural_no_zero'
    ];
    protected $validationMessages = [
        'venta_id' => [
            'required' => 'El ID de venta es requerido',
            'is_natural_no_zero' => 'Debe ser un ID de venta válido'
        ],
        'monto_pago' => [
            'required' => 'El monto del pago es requerido',
            'decimal' => 'Debe ser un número decimal válido',
            'greater_than' => 'El monto debe ser mayor a cero'
        ],
        'forma_pago' => [
            'required' => 'La forma de pago es requerida',
            'in_list' => 'Forma de pago no válida'
        ],
        'concepto_pago' => [
            'required' => 'El concepto de pago es requerido',
            'in_list' => 'Concepto de pago no válido'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['prepararInsercion'];
    protected $afterInsert    = ['procesarPostInsercion'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['procesarPostActualizacion'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // ==========================================
    // MÉTODOS BUSINESS LOGIC ESPECIALIZADOS
    // ==========================================

    /**
     * Obtiene el historial completo de pagos de una venta
     */
    public function getHistorialPagos(int $ventaId): array
    {
        $query = $this->db->table($this->table . ' pv')
                         ->select('
                             pv.*,
                             ta.numero_pago,
                             ta.fecha_vencimiento,
                             CONCAT(s.nombres, " ", s.apellido_paterno) as registrado_por_nombre,
                             cb.banco as nombre_banco,
                             cb.numero_cuenta
                         ')
                         ->join('tabla_amortizacion ta', 'ta.id = pv.tabla_amortizacion_id', 'left')
                         ->join('users u', 'u.id = pv.registrado_por', 'left')
                         ->join('staff s', 's.user_id = u.id', 'left')
                         ->join('cuentas_bancarias cb', 'cb.id = pv.cuenta_bancaria_id', 'left')
                         ->where('pv.venta_id', $ventaId)
                         ->orderBy('pv.fecha_pago', 'DESC');

        return $query->get()->getResult();
    }

    /**
     * Obtiene todos los pagos pendientes de un cliente
     */
    public function getPagosPendientes(int $clienteId): array
    {
        $query = $this->db->table($this->table . ' pv')
                         ->select('
                             pv.*,
                             v.folio_venta,
                             ta.numero_pago,
                             ta.fecha_vencimiento,
                             ta.monto_total as monto_mensualidad
                         ')
                         ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                         ->join('tabla_amortizacion ta', 'ta.id = pv.tabla_amortizacion_id', 'left')
                         ->where('v.cliente_id', $clienteId)
                         ->where('pv.estatus_pago', 'pendiente')
                         ->orderBy('ta.fecha_vencimiento', 'ASC');

        return $query->get()->getResult();
    }

    /**
     * Procesa un pago completo con todas las validaciones y actualizaciones
     */
    public function procesarPago(array $pagoData): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Crear Entity del pago
            $pago = new PagoVenta($pagoData);
            $pago->prepararParaInsercion($pagoData['registrado_por']);

            // Validar Entity
            $validacionEntity = $pago->validarEntity();
            if (!$validacionEntity['valido']) {
                throw new \Exception('Validación fallida: ' . implode(', ', $validacionEntity['errores']));
            }

            // Validar el pago específico
            $validacionPago = $pago->validarPago(
                $pago->monto_pago,
                $pago->tabla_amortizacion_id
            );
            
            if (!$validacionPago['valido']) {
                throw new \Exception('Validación de pago fallida: ' . implode(', ', $validacionPago['errores']));
            }

            // Insertar el pago
            $pagoId = $this->insert($pago);
            if (!$pagoId) {
                throw new \Exception('Error al insertar el pago: ' . implode(', ', $this->errors()));
            }

            // Aplicar el pago
            $resultadoAplicacion = $pago->aplicarPago($pagoData);
            if (!$resultadoAplicacion['success']) {
                throw new \Exception($resultadoAplicacion['error']);
            }

            // Actualizar el pago con el estado aplicado
            $this->update($pagoId, [
                'estatus_pago' => 'aplicado',
                'fecha_pago' => $resultadoAplicacion['fecha_aplicacion']
            ]);

            // Si hay tabla de amortización, actualizarla
            if (!empty($pago->tabla_amortizacion_id)) {
                $tablaModel = new \App\Models\TablaAmortizacionModel();
                $resultadoTabla = $tablaModel->aplicarPago([
                    'tabla_amortizacion_id' => $pago->tabla_amortizacion_id,
                    'monto_pago' => $pago->monto_pago,
                    'metodo_pago' => $pago->forma_pago,
                    'referencia' => $pago->referencia_pago
                ]);

                if (!$resultadoTabla['success']) {
                    throw new \Exception('Error al actualizar tabla amortización: ' . $resultadoTabla['error']);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción del pago');
            }

            // Obtener el pago completo insertado
            $pagoCompleto = $this->find($pagoId);

            return [
                'success' => true,
                'pago_id' => $pagoId,
                'folio_generado' => $resultadoAplicacion['folio_generado'],
                'pago_completo' => $pagoCompleto->toApiArray(),
                'resultado_aplicacion' => $resultadoAplicacion
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancela un pago con auditoría completa
     */
    public function cancelarPago(int $pagoId, string $motivo, int $usuarioCancelaId): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $pago = $this->find($pagoId);
            if (!$pago) {
                throw new \Exception('Pago no encontrado');
            }

            // Usar método Entity para cancelar
            $resultadoCancelacion = $pago->cancelarPago($motivo, $usuarioCancelaId);
            if (!$resultadoCancelacion['success']) {
                throw new \Exception($resultadoCancelacion['error']);
            }

            // Actualizar en base de datos
            $this->save($pago);

            // Revertir cambios en tabla de amortización si existe
            if (!empty($pago->tabla_amortizacion_id)) {
                $tablaModel = new \App\Models\TablaAmortizacionModel();
                $mensualidad = $tablaModel->find($pago->tabla_amortizacion_id);
                
                if ($mensualidad) {
                    // Restar el monto pagado
                    $mensualidad->monto_pagado = max(0, $mensualidad->monto_pagado - $pago->monto_pago);
                    $mensualidad->numero_pagos_aplicados = max(0, $mensualidad->numero_pagos_aplicados - 1);
                    $mensualidad->actualizarEstadoAutomatico();
                    $tablaModel->save($mensualidad);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de cancelación');
            }

            return [
                'success' => true,
                'pago_cancelado' => $pago->toApiArray(),
                'resultado_cancelacion' => $resultadoCancelacion
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene estadísticas de pagos por cliente
     */
    public function getEstadisticasPagos(int $clienteId): array
    {
        $estadisticas = $this->db->table($this->table . ' pv')
                                ->select('
                                    COUNT(pv.id) as total_pagos,
                                    SUM(CASE WHEN pv.estatus_pago = "aplicado" THEN 1 ELSE 0 END) as pagos_aplicados,
                                    SUM(CASE WHEN pv.estatus_pago = "pendiente" THEN 1 ELSE 0 END) as pagos_pendientes,
                                    SUM(CASE WHEN pv.estatus_pago = "cancelado" THEN 1 ELSE 0 END) as pagos_cancelados,
                                    SUM(CASE WHEN pv.estatus_pago = "aplicado" THEN pv.monto_pago ELSE 0 END) as monto_total_pagado,
                                    SUM(CASE WHEN pv.estatus_pago = "pendiente" THEN pv.monto_pago ELSE 0 END) as monto_pendiente,
                                    AVG(CASE WHEN pv.estatus_pago = "aplicado" THEN pv.monto_pago ELSE NULL END) as promedio_pago,
                                    MAX(pv.fecha_pago) as ultimo_pago_fecha,
                                    MIN(pv.fecha_pago) as primer_pago_fecha
                                ')
                                ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                                ->where('v.cliente_id', $clienteId)
                                ->get()
                                ->getRow();

        // Estadísticas por concepto
        $porConcepto = $this->db->table($this->table . ' pv')
                               ->select('
                                   pv.concepto_pago,
                                   COUNT(pv.id) as cantidad,
                                   SUM(pv.monto_pago) as monto_total
                               ')
                               ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                               ->where('v.cliente_id', $clienteId)
                               ->where('pv.estatus_pago', 'aplicado')
                               ->groupBy('pv.concepto_pago')
                               ->get()
                               ->getResult();

        return [
            'resumen_general' => $estadisticas,
            'por_concepto' => $porConcepto
        ];
    }

    /**
     * Busca pagos con filtros múltiples
     */
    public function buscarPagos(array $filtros = []): array
    {
        $query = $this->db->table($this->table . ' pv')
                         ->select('
                             pv.*,
                             v.folio_venta,
                             CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente,
                             ta.numero_pago,
                             ta.fecha_vencimiento
                         ')
                         ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                         ->join('clientes c', 'c.id = v.cliente_id', 'inner')
                         ->join('tabla_amortizacion ta', 'ta.id = pv.tabla_amortizacion_id', 'left');

        // Aplicar filtros dinámicamente
        if (!empty($filtros['cliente_id'])) {
            $query->where('v.cliente_id', $filtros['cliente_id']);
        }

        if (!empty($filtros['venta_id'])) {
            $query->where('pv.venta_id', $filtros['venta_id']);
        }

        if (!empty($filtros['estatus_pago'])) {
            if (is_array($filtros['estatus_pago'])) {
                $query->whereIn('pv.estatus_pago', $filtros['estatus_pago']);
            } else {
                $query->where('pv.estatus_pago', $filtros['estatus_pago']);
            }
        }

        if (!empty($filtros['concepto_pago'])) {
            $query->where('pv.concepto_pago', $filtros['concepto_pago']);
        }

        if (!empty($filtros['forma_pago'])) {
            $query->where('pv.forma_pago', $filtros['forma_pago']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('pv.fecha_pago >=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('pv.fecha_pago <=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['monto_min'])) {
            $query->where('pv.monto_pago >=', $filtros['monto_min']);
        }

        if (!empty($filtros['monto_max'])) {
            $query->where('pv.monto_pago <=', $filtros['monto_max']);
        }

        if (!empty($filtros['folio_pago'])) {
            $query->like('pv.folio_pago', $filtros['folio_pago']);
        }

        // Búsqueda por nombre de cliente
        if (!empty($filtros['nombre_cliente'])) {
            $query->groupStart();
            $query->like('c.nombres', $filtros['nombre_cliente']);
            $query->orLike('c.apellido_paterno', $filtros['nombre_cliente']);
            $query->orLike('c.apellido_materno', $filtros['nombre_cliente']);
            $query->groupEnd();
        }

        // Ordenamiento
        $ordenPor = $filtros['orden_por'] ?? 'pv.fecha_pago';
        $direccion = $filtros['direccion'] ?? 'DESC';
        $query->orderBy($ordenPor, $direccion);

        // Paginación
        if (!empty($filtros['limite'])) {
            $offset = $filtros['offset'] ?? 0;
            $query->limit($filtros['limite'], $offset);
        }

        return $query->get()->getResult();
    }

    /**
     * Obtiene el resumen de pagos por período
     */
    public function getResumenPorPeriodo(string $fechaInicio, string $fechaFin): array
    {
        $query = $this->db->table($this->table)
                         ->select('
                             DATE(fecha_pago) as fecha,
                             concepto_pago,
                             COUNT(id) as cantidad_pagos,
                             SUM(monto_pago) as monto_total,
                             forma_pago
                         ')
                         ->where('fecha_pago >=', $fechaInicio)
                         ->where('fecha_pago <=', $fechaFin)
                         ->where('estatus_pago', 'aplicado')
                         ->groupBy('DATE(fecha_pago), concepto_pago, forma_pago')
                         ->orderBy('fecha_pago', 'DESC');

        return $query->get()->getResult();
    }

    /**
     * Obtiene pagos que requieren comprobante
     */
    public function getPagosSinComprobante(): array
    {
        $query = $this->db->table($this->table . ' pv')
                         ->select('
                             pv.*,
                             v.folio_venta,
                             CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente
                         ')
                         ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                         ->join('clientes c', 'c.id = v.cliente_id', 'inner')
                         ->where('pv.estatus_pago', 'aplicado')
                         ->where('(pv.comprobante_url IS NULL OR pv.comprobante_url = "")')
                         ->groupStart()
                         ->whereIn('pv.forma_pago', ['transferencia', 'cheque', 'deposito'])
                         ->orWhere('pv.monto_pago >=', 5000)
                         ->groupEnd()
                         ->orderBy('pv.fecha_pago', 'DESC');

        return $query->get()->getResult();
    }

    // ==========================================
    // CALLBACKS DEL MODEL
    // ==========================================

    /**
     * Prepara datos antes de inserción
     */
    protected function prepararInsercion(array $data): array
    {
        if (isset($data['data']) && $data['data'] instanceof PagoVenta) {
            $entity = $data['data'];
            $entity->prepararParaInsercion($data['data']->registrado_por ?? 1);
        }
        
        return $data;
    }

    /**
     * Procesa acciones después de inserción
     */
    protected function procesarPostInsercion(array $data): array
    {
        if (!empty($data['id'])) {
            // Aquí se pueden agregar hooks post-inserción
            // Como envío de notificaciones, logs, etc.
        }
        
        return $data;
    }

    /**
     * Procesa acciones después de actualización
     */
    protected function procesarPostActualizacion(array $data): array
    {
        if (!empty($data['id'])) {
            // Hooks post-actualización
            // Como notificaciones de cambio de estado
        }
        
        return $data;
    }
}