<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ConceptoPago;

/**
 * Model ConceptoPagoModel
 * 
 * Gestiona los conceptos de pago inmobiliario
 * con lógica de negocio integrada
 */
class ConceptoPagoModel extends Model
{
    protected $table            = 'conceptos_pago';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = ConceptoPago::class;
    protected $useSoftDeletes    = false;
    protected $protectFields     = true;
    protected $allowedFields     = [
        'venta_id',
        'cuenta_financiamiento_id',
        'anticipo_id',
        'liquidacion_id',
        'concepto',
        'descripcion',
        'monto',
        'monto_aplicado',
        'saldo_pendiente',
        'estado',
        'fecha_pago',
        'fecha_vencimiento',
        'forma_pago',
        'referencia',
        'aplicado_a_capital',
        'aplicado_a_interes',
        'aplicado_a_moratorio',
        'refactoriza_tabla',
        'observaciones'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'venta_id' => 'required|integer',
        'concepto' => 'required|in_list[apartado,liquidacion_enganche,mensualidad,abono_capital,interes_moratorio,liquidacion_total]',
        'monto' => 'required|decimal|greater_than[0]',
        'estado' => 'in_list[pendiente,aplicado,vencido,cancelado]'
    ];

    protected $validationMessages = [
        'venta_id' => [
            'required' => 'La venta es requerida',
            'integer' => 'ID de venta inválido'
        ],
        'concepto' => [
            'required' => 'El concepto es requerido',
            'in_list' => 'Concepto de pago inválido'
        ],
        'monto' => [
            'required' => 'El monto es requerido',
            'decimal' => 'El monto debe ser un número decimal',
            'greater_than' => 'El monto debe ser mayor a 0'
        ]
    ];

    /**
     * Obtener pagos por venta
     */
    public function getPagosPorVenta(int $ventaId): array
    {
        return $this->where('venta_id', $ventaId)
                   ->orderBy('fecha_pago', 'DESC')
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener pagos pendientes por venta
     */
    public function getPagosPendientes(int $ventaId): array
    {
        return $this->where('venta_id', $ventaId)
                   ->where('estado', ConceptoPago::ESTADO_PENDIENTE)
                   ->orderBy('fecha_vencimiento', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener pagos vencidos
     */
    public function getPagosVencidos(int $ventaId = null): array
    {
        $builder = $this->where('estado', ConceptoPago::ESTADO_PENDIENTE)
                       ->where('fecha_vencimiento <', date('Y-m-d'));

        if ($ventaId) {
            $builder->where('venta_id', $ventaId);
        }

        return $builder->orderBy('fecha_vencimiento', 'ASC')
                      ->findAll();
    }

    /**
     * Obtener estadísticas de pagos por concepto
     */
    public function getEstadisticasPorConcepto(int $ventaId = null): array
    {
        $builder = $this->select('
            concepto,
            COUNT(*) as total_pagos,
            SUM(monto) as monto_total,
            SUM(monto_aplicado) as monto_aplicado,
            SUM(saldo_pendiente) as saldo_pendiente
        ');

        if ($ventaId) {
            $builder->where('venta_id', $ventaId);
        }

        return $builder->groupBy('concepto')
                      ->orderBy('concepto', 'ASC')
                      ->findAll();
    }

    /**
     * Obtener resumen financiero por cliente
     */
    public function getResumenFinanciero(int $clienteId): array
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                cp.concepto,
                COUNT(*) as total_pagos,
                SUM(cp.monto) as monto_total,
                SUM(cp.monto_aplicado) as monto_aplicado,
                SUM(cp.saldo_pendiente) as saldo_pendiente,
                AVG(cp.monto) as promedio_pago
            FROM conceptos_pago cp
            JOIN ventas v ON v.id = cp.venta_id
            WHERE v.cliente_id = ?
            GROUP BY cp.concepto
            ORDER BY cp.concepto ASC
        ", [$clienteId]);

        return $query->getResult();
    }

    /**
     * Crear concepto de apartado
     */
    public function crearConceptoApartado(array $datos): ConceptoPago
    {
        $concepto = new ConceptoPago([
            'venta_id' => $datos['venta_id'],
            'concepto' => ConceptoPago::APARTADO,
            'descripcion' => 'Apartado - ' . ($datos['descripcion'] ?? 'Anticipo para reserva'),
            'monto' => $datos['monto'],
            'monto_aplicado' => 0,
            'saldo_pendiente' => $datos['monto'],
            'estado' => ConceptoPago::ESTADO_PENDIENTE,
            'fecha_pago' => $datos['fecha_pago'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days')),
            'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
            'referencia' => $datos['referencia'] ?? '',
            'observaciones' => $datos['observaciones'] ?? ''
        ]);

        return $concepto;
    }

    /**
     * Crear concepto de liquidación de enganche
     */
    public function crearConceptoLiquidacionEnganche(array $datos): ConceptoPago
    {
        $concepto = new ConceptoPago([
            'venta_id' => $datos['venta_id'],
            'liquidacion_id' => $datos['liquidacion_id'] ?? null,
            'concepto' => ConceptoPago::LIQUIDACION_ENGANCHE,
            'descripcion' => 'Liquidación de Enganche - ' . ($datos['descripcion'] ?? 'Pago de enganche'),
            'monto' => $datos['monto'],
            'monto_aplicado' => 0,
            'saldo_pendiente' => $datos['monto'],
            'estado' => ConceptoPago::ESTADO_PENDIENTE,
            'fecha_pago' => $datos['fecha_pago'],
            'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
            'referencia' => $datos['referencia'] ?? '',
            'observaciones' => $datos['observaciones'] ?? ''
        ]);

        return $concepto;
    }

    /**
     * Crear concepto de mensualidad
     */
    public function crearConceptoMensualidad(array $datos): ConceptoPago
    {
        $concepto = new ConceptoPago([
            'venta_id' => $datos['venta_id'],
            'cuenta_financiamiento_id' => $datos['cuenta_id'],
            'concepto' => ConceptoPago::MENSUALIDAD,
            'descripcion' => 'Mensualidad - ' . ($datos['descripcion'] ?? 'Pago mensual'),
            'monto' => $datos['monto'],
            'monto_aplicado' => 0,
            'saldo_pendiente' => $datos['monto'],
            'estado' => ConceptoPago::ESTADO_PENDIENTE,
            'fecha_pago' => $datos['fecha_pago'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
            'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
            'referencia' => $datos['referencia'] ?? '',
            'observaciones' => $datos['observaciones'] ?? ''
        ]);

        return $concepto;
    }

    /**
     * Crear concepto de abono a capital
     */
    public function crearConceptoAbonoCapital(array $datos): ConceptoPago
    {
        $concepto = new ConceptoPago([
            'venta_id' => $datos['venta_id'],
            'cuenta_financiamiento_id' => $datos['cuenta_id'],
            'concepto' => ConceptoPago::ABONO_CAPITAL,
            'descripcion' => 'Abono a Capital - ' . ($datos['descripcion'] ?? 'Pago anticipado'),
            'monto' => $datos['monto'],
            'monto_aplicado' => 0,
            'saldo_pendiente' => $datos['monto'],
            'estado' => ConceptoPago::ESTADO_PENDIENTE,
            'fecha_pago' => $datos['fecha_pago'],
            'forma_pago' => $datos['forma_pago'] ?? 'efectivo',
            'referencia' => $datos['referencia'] ?? '',
            'refactoriza_tabla' => true,
            'observaciones' => $datos['observaciones'] ?? ''
        ]);

        return $concepto;
    }

    /**
     * Marcar como aplicado
     */
    public function aplicarPago(int $conceptoId, array $distribucion): bool
    {
        $concepto = $this->find($conceptoId);
        if (!$concepto) {
            return false;
        }

        $concepto->aplicarPago($distribucion);
        return $this->save($concepto);
    }

    /**
     * Marcar pagos como vencidos
     */
    public function marcarPagosVencidos(): int
    {
        return $this->where('estado', ConceptoPago::ESTADO_PENDIENTE)
                   ->where('fecha_vencimiento <', date('Y-m-d'))
                   ->set('estado', ConceptoPago::ESTADO_VENCIDO)
                   ->update();
    }

    /**
     * Obtener pagos que requieren refactorización
     */
    public function getPagosParaRefactorizar(): array
    {
        return $this->where('refactoriza_tabla', true)
                   ->where('estado', ConceptoPago::ESTADO_APLICADO)
                   ->findAll();
    }

    /**
     * Obtener total pagado por concepto
     */
    public function getTotalPagadoPorConcepto(int $ventaId, string $concepto): float
    {
        $resultado = $this->selectSum('monto_aplicado')
                         ->where('venta_id', $ventaId)
                         ->where('concepto', $concepto)
                         ->where('estado', ConceptoPago::ESTADO_APLICADO)
                         ->first();

        return $resultado ? (float)$resultado->monto_aplicado : 0;
    }

    /**
     * Obtener siguiente número de pago
     */
    public function getSiguienteNumeroPago(int $ventaId, string $concepto): int
    {
        $resultado = $this->selectMax('id')
                         ->where('venta_id', $ventaId)
                         ->where('concepto', $concepto)
                         ->first();

        return $resultado ? $resultado->id + 1 : 1;
    }

    /**
     * Hooks antes de insertar
     */
    protected function beforeInsert(array $data): array
    {
        $data = $this->validarMontosMinimos($data);
        $data = $this->generarDescripcionAutomatica($data);
        return $data;
    }

    /**
     * Hooks antes de actualizar
     */
    protected function beforeUpdate(array $data): array
    {
        $data = $this->validarMontosMinimos($data);
        return $data;
    }

    /**
     * Validar montos mínimos
     */
    private function validarMontosMinimos(array $data): array
    {
        if (isset($data['data']['concepto']) && isset($data['data']['monto'])) {
            $concepto = new ConceptoPago($data['data']);
            $validacion = $concepto->validarMontoMinimo();
            
            if (!$validacion['valido']) {
                throw new \RuntimeException($validacion['mensaje']);
            }
        }

        return $data;
    }

    /**
     * Generar descripción automática
     */
    private function generarDescripcionAutomatica(array $data): array
    {
        if (isset($data['data']['concepto']) && empty($data['data']['descripcion'])) {
            $concepto = new ConceptoPago($data['data']);
            $data['data']['descripcion'] = $concepto->getDescripcionConcepto();
        }

        return $data;
    }
}