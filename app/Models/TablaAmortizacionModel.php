<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\TablaAmortizacion;

class TablaAmortizacionModel extends Model
{
    protected $table            = 'tabla_amortizacion';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = TablaAmortizacion::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'plan_financiamiento_id',
        'venta_id',
        'numero_pago',
        'fecha_vencimiento',
        'saldo_inicial',
        'capital',
        'interes',
        'monto_total',
        'saldo_final',
        'monto_pagado',
        'saldo_pendiente',
        'numero_pagos_aplicados',
        'fecha_ultimo_pago',
        'estatus',
        'dias_atraso',
        'fecha_inicio_atraso',
        'interes_moratorio',
        'beneficiario',
        'concepto_especial'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'plan_financiamiento_id' => 'integer',
        'venta_id' => 'integer',
        'numero_pago' => 'integer',
        'saldo_inicial' => 'float',
        'capital' => 'float',
        'interes' => 'float',
        'monto_total' => 'float',
        'saldo_final' => 'float',
        'monto_pagado' => 'float',
        'saldo_pendiente' => 'float',
        'numero_pagos_aplicados' => 'integer',
        'dias_atraso' => 'integer',
        'interes_moratorio' => 'float'
    ];
    protected array $castHandlers = [];

    // Validation
    protected $validationRules = [
        'plan_financiamiento_id' => 'required|is_natural_no_zero',
        'numero_pago' => 'required|is_natural_no_zero',
        'fecha_vencimiento' => 'required|valid_date',
        'monto_total' => 'required|decimal|greater_than[0]',
        'capital' => 'required|decimal|greater_than_equal_to[0]',
        'interes' => 'required|decimal|greater_than_equal_to[0]'
    ];
    protected $validationMessages = [
        'plan_financiamiento_id' => [
            'required' => 'El ID del plan de financiamiento es requerido',
            'is_natural_no_zero' => 'Debe ser un ID válido'
        ],
        'numero_pago' => [
            'required' => 'El número de pago es requerido',
            'is_natural_no_zero' => 'Debe ser un número positivo'
        ],
        'fecha_vencimiento' => [
            'required' => 'La fecha de vencimiento es requerida',
            'valid_date' => 'Debe ser una fecha válida'
        ],
        'monto_total' => [
            'required' => 'El monto total es requerido',
            'decimal' => 'Debe ser un número decimal válido',
            'greater_than' => 'Debe ser mayor a cero'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // ==========================================
    // MÉTODOS BUSINESS LOGIC ESPECIALIZADOS
    // ==========================================

    /**
     * Obtiene el plan de pagos completo de una venta
     */
    public function getByVenta(int $ventaId): array
    {
        // Obtener directamente por venta_id
        $query = $this->db->table($this->table)
                         ->where('venta_id', $ventaId)
                         ->orderBy('numero_pago', 'ASC');
                         
        return $query->get()->getResult();
    }

    /**
     * Obtiene las mensualidades pendientes de una venta específica
     */
    public function getMensualidadesPendientes(int $ventaId): array
    {
        $query = $this->db->table($this->table)
                         ->where('venta_id', $ventaId)
                         ->whereNotIn('estatus', ['pagada', 'cancelada'])
                         ->orderBy('fecha_vencimiento', 'ASC');

        $resultados = $query->get()->getResult();
        
        // Convertir a Entities para usar métodos avanzados
        $mensualidades = [];
        foreach ($resultados as $resultado) {
            $entity = new TablaAmortizacion((array) $resultado);
            $entity->actualizarEstadoAutomatico();
            $entity->calcularInteresMoratorio();
            $mensualidades[] = $entity;
        }

        return $mensualidades;
    }

    /**
     * Obtiene todas las mensualidades vencidas de un cliente
     */
    public function getMensualidadesVencidas(int $clienteId): array
    {
        $hoy = date('Y-m-d');
        
        $query = $this->db->table($this->table . ' ta')
                         ->select('ta.*, pv.venta_id, v.cliente_id, v.folio_venta')
                         ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'left')
                         ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                         ->where('v.cliente_id', $clienteId)
                         ->where('ta.fecha_vencimiento <', $hoy)
                         ->whereNotIn('ta.estatus', ['pagada', 'cancelada'])
                         ->orderBy('ta.fecha_vencimiento', 'ASC');

        $resultados = $query->get()->getResult();
        
        // Convertir a Entities y actualizar estados
        $vencidas = [];
        foreach ($resultados as $resultado) {
            $entity = new TablaAmortizacion((array) $resultado);
            $entity->actualizarEstadoAutomatico();
            $entity->calcularInteresMoratorio();
            
            // Solo incluir si realmente está vencida después de actualización
            if ($entity->estaVencido()) {
                $vencidas[] = $entity;
            }
        }

        return $vencidas;
    }

    /**
     * Obtiene mensualidades próximas a vencer (siguiente semana)
     */
    public function getMensualidadesProximasVencer(int $diasAnticipacion = 7): array
    {
        $hoy = date('Y-m-d');
        $fechaLimite = date('Y-m-d', strtotime("+{$diasAnticipacion} days"));
        
        $query = $this->db->table($this->table . ' ta')
                         ->select('ta.*, pv.venta_id, v.cliente_id, v.folio_venta, CONCAT(c.nombres, " ", c.apellido_paterno, " ", c.apellido_materno) as nombre_cliente')
                         ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'left')
                         ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                         ->join('clientes c', 'c.id = v.cliente_id', 'inner')
                         ->where('ta.fecha_vencimiento >=', $hoy)
                         ->where('ta.fecha_vencimiento <=', $fechaLimite)
                         ->whereNotIn('ta.estatus', ['pagada', 'cancelada'])
                         ->orderBy('ta.fecha_vencimiento', 'ASC');

        return $query->get()->getResult();
    }

    /**
     * Job diario para actualizar todos los atrasos
     */
    public function actualizarAtrasos(): array
    {
        $mensualidadesPendientes = $this->whereNotIn('estatus', ['pagada', 'cancelada'])->findAll();
        $actualizadas = 0;
        $errores = [];

        foreach ($mensualidadesPendientes as $mensualidad) {
            try {
                $estadoAnterior = $mensualidad->estatus;
                
                // Actualizar estado y moratorios
                $mensualidad->actualizarEstadoAutomatico();
                $mensualidad->calcularInteresMoratorio();
                
                // Guardar solo si hubo cambios
                if ($mensualidad->estatus !== $estadoAnterior || $mensualidad->hasChanged()) {
                    $this->save($mensualidad);
                    $actualizadas++;
                }
            } catch (\Exception $e) {
                $errores[] = "Error mensualidad ID {$mensualidad->id}: " . $e->getMessage();
            }
        }

        return [
            'mensualidades_procesadas' => count($mensualidadesPendientes),
            'mensualidades_actualizadas' => $actualizadas,
            'errores' => $errores,
            'fecha_proceso' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Aplica un pago a una mensualidad específica
     */
    public function aplicarPago(array $pagoData): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Buscar la mensualidad
            $mensualidad = $this->find($pagoData['tabla_amortizacion_id']);
            
            if (!$mensualidad) {
                throw new \Exception('Mensualidad no encontrada');
            }

            // Aplicar el pago usando el método Entity
            $resultadoAplicacion = $mensualidad->aplicarPago(
                $pagoData['monto_pago'],
                $pagoData['metodo_pago'] ?? 'efectivo',
                $pagoData['referencia'] ?? null
            );

            if (!$resultadoAplicacion['success']) {
                throw new \Exception($resultadoAplicacion['error']);
            }

            // Guardar los cambios en la mensualidad
            $this->save($mensualidad);

            // Si hay sobrante, aplicar a siguiente mensualidad
            if ($resultadoAplicacion['sobrante'] > 0) {
                $siguienteMensualidad = $this->getSiguienteMensualidadPendiente($pagoData['tabla_amortizacion_id']);
                
                if ($siguienteMensualidad) {
                    $pagoSobrante = $pagoData;
                    $pagoSobrante['monto_pago'] = $resultadoAplicacion['sobrante'];
                    $pagoSobrante['tabla_amortizacion_id'] = $siguienteMensualidad->id;
                    
                    $resultadoSobrante = $this->aplicarPago($pagoSobrante);
                    $resultadoAplicacion['aplicacion_sobrante'] = $resultadoSobrante;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de pago');
            }

            return [
                'success' => true,
                'resultado_aplicacion' => $resultadoAplicacion,
                'mensualidad_actualizada' => $mensualidad->toApiArray()
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
     * Obtiene la siguiente mensualidad pendiente
     */
    private function getSiguienteMensualidadPendiente(int $mensualidadIdActual): ?TablaAmortizacion
    {
        $mensualidadActual = $this->find($mensualidadIdActual);
        
        if (!$mensualidadActual) {
            return null;
        }

        return $this->where('venta_id', $mensualidadActual->venta_id)
                   ->where('numero_pago >', $mensualidadActual->numero_pago)
                   ->whereNotIn('estatus', ['pagada', 'cancelada'])
                   ->orderBy('numero_pago', 'ASC')
                   ->first();
    }

    /**
     * Obtiene estadísticas generales de mensualidades
     */
    public function getEstadisticasGenerales(): array
    {
        $estadisticas = [
            'total_mensualidades' => $this->countAll(),
            'pendientes' => $this->where('estatus', 'pendiente')->countAllResults(),
            'vencidas' => $this->where('estatus', 'vencida')->countAllResults(),
            'pagadas' => $this->where('estatus', 'pagada')->countAllResults(),
            'parciales' => $this->where('estatus', 'parcial')->countAllResults()
        ];

        // Cálculos de montos
        $montos = $this->db->table($this->table)
                          ->select('
                              SUM(CASE WHEN estatus != "pagada" THEN saldo_pendiente ELSE 0 END) as saldo_total_pendiente,
                              SUM(CASE WHEN estatus != "pagada" THEN interes_moratorio ELSE 0 END) as moratorios_pendientes,
                              SUM(monto_pagado) as total_cobrado,
                              SUM(monto_total) as monto_total_sistema
                          ')
                          ->get()
                          ->getRow();

        return array_merge($estadisticas, (array) $montos);
    }

    /**
     * Busca mensualidades por múltiples criterios
     */
    public function buscarMensualidades(array $filtros = []): array
    {
        $query = $this->db->table($this->table . ' ta')
                         ->select('ta.*, pv.venta_id, v.folio_venta, CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente')
                         ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'left')
                         ->join('ventas v', 'v.id = pv.venta_id', 'left')
                         ->join('clientes c', 'c.id = v.cliente_id', 'left');

        // Aplicar filtros dinámicamente
        if (!empty($filtros['estatus'])) {
            if (is_array($filtros['estatus'])) {
                $query->whereIn('ta.estatus', $filtros['estatus']);
            } else {
                $query->where('ta.estatus', $filtros['estatus']);
            }
        }

        if (!empty($filtros['cliente_id'])) {
            $query->where('v.cliente_id', $filtros['cliente_id']);
        }

        if (!empty($filtros['venta_id'])) {
            $query->where('pv.venta_id', $filtros['venta_id']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('ta.fecha_vencimiento >=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('ta.fecha_vencimiento <=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['dias_vencido_min'])) {
            $query->where('ta.dias_atraso >=', $filtros['dias_vencido_min']);
        }

        // Ordenamiento
        $ordenPor = $filtros['orden_por'] ?? 'ta.fecha_vencimiento';
        $direccion = $filtros['direccion'] ?? 'ASC';
        $query->orderBy($ordenPor, $direccion);

        // Paginación
        if (!empty($filtros['limite'])) {
            $offset = $filtros['offset'] ?? 0;
            $query->limit($filtros['limite'], $offset);
        }

        return $query->get()->getResult();
    }

    /**
     * Genera resumen por vendedor
     */
    public function getResumenPorVendedor(): array
    {
        $query = $this->db->table($this->table . ' ta')
                         ->select('
                             v.vendedor_id,
                             CONCAT(s.nombres, " ", s.apellido_paterno) as nombre_vendedor,
                             COUNT(ta.id) as total_mensualidades,
                             SUM(CASE WHEN ta.estatus = "pendiente" THEN 1 ELSE 0 END) as pendientes,
                             SUM(CASE WHEN ta.estatus = "vencida" THEN 1 ELSE 0 END) as vencidas,
                             SUM(CASE WHEN ta.estatus = "pagada" THEN 1 ELSE 0 END) as pagadas,
                             SUM(CASE WHEN ta.estatus != "pagada" THEN ta.saldo_pendiente ELSE 0 END) as saldo_pendiente_total,
                             SUM(ta.interes_moratorio) as moratorios_total
                         ')
                         ->join('pagos_ventas pv', 'pv.tabla_amortizacion_id = ta.id', 'left')
                         ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                         ->join('users u', 'u.id = v.vendedor_id', 'inner')
                         ->join('staff s', 's.user_id = u.id', 'inner')
                         ->groupBy('v.vendedor_id, s.nombres, s.apellido_paterno')
                         ->orderBy('saldo_pendiente_total', 'DESC');

        return $query->get()->getResult();
    }
}