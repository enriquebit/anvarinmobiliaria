<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionVentaModel extends Model
{
    protected $table            = 'comisiones_ventas';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'object';
    protected $useSoftDeletes    = false;
    protected $protectFields     = true;
    protected $allowedFields     = [
        'venta_id', 'apartado_id', 'vendedor_id', 'configuracion_comision_id',
        'base_calculo', 'tipo_calculo', 'porcentaje_aplicado', 'monto_comision_total',
        'monto_pagado_apartado', 'monto_pagado_enganche', 'monto_por_cobranza',
        'monto_pagado_total', 'estatus', 'fecha_generacion', 'fecha_ultimo_pago',
        'fecha_cancelacion', 'motivo_cancelacion', 'cancelado_por', 'observaciones'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'vendedor_id' => 'required|integer',
        'base_calculo' => 'required|decimal|greater_than[0]',
        'tipo_calculo' => 'required|in_list[porcentaje,monto_fijo,escalonado]',
        'monto_comision_total' => 'required|decimal|greater_than[0]',
        'estatus' => 'in_list[devengada,pendiente_aceptacion,aceptada,pendiente,en_proceso,realizado,parcial,pagada,cancelada]',
        'fecha_generacion' => 'required|valid_date'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    /**
     * Obtener comisiones con sus relaciones
     */
    public function getComisionesConRelaciones(array $filtros = []): array
    {
        $builder = $this->builder();
        
        $builder->select('
                comisiones_ventas.*,
                v.folio_venta,
                a.folio_apartado,
                CONCAT(s.nombres, " ", s.apellido_paterno, " ", COALESCE(s.apellido_materno, "")) as vendedor_nombre,
                s.email as vendedor_email,
                CONCAT(c.nombres, " ", c.apellido_paterno, " ", COALESCE(c.apellido_materno, "")) as cliente_nombre,
                c.email as cliente_email,
                l.clave as lote_clave,
                l.numero as lote_numero,
                cc.nombre_config as config_nombre
            ')
            ->join('staff s', 's.user_id = comisiones_ventas.vendedor_id')
            ->join('users u', 'u.id = comisiones_ventas.vendedor_id', 'left')
            ->join('ventas v', 'v.id = comisiones_ventas.venta_id', 'left')
            ->join('apartados a', 'a.id = comisiones_ventas.apartado_id', 'left')
            ->join('clientes c', 'c.id = COALESCE(v.cliente_id, a.cliente_id)')
            ->join('lotes l', 'l.id = COALESCE(v.lote_id, a.lote_id)')
            ->join('configuracion_comisiones cc', 'cc.id = comisiones_ventas.configuracion_comision_id', 'left')
            ->orderBy('comisiones_ventas.created_at', 'DESC');

        // Aplicar filtros
        if (!empty($filtros['estatus'])) {
            $builder->where('comisiones_ventas.estatus', $filtros['estatus']);
        }

        if (!empty($filtros['vendedor_id'])) {
            $builder->where('comisiones_ventas.vendedor_id', $filtros['vendedor_id']);
        }

        if (!empty($filtros['fecha_inicio'])) {
            $builder->where('comisiones_ventas.fecha_generacion >=', $filtros['fecha_inicio']);
        }

        if (!empty($filtros['fecha_fin'])) {
            $builder->where('comisiones_ventas.fecha_generacion <=', $filtros['fecha_fin']);
        }

        return $builder->get()->getResult();
    }

    /**
     * Crear comisión devengada por apartado
     */
    public function crearComisionDevengada(int $apartadoId, int $vendedorId, float $baseCalculo, float $porcentaje, int $configuracionId = null): int
    {
        $montoComision = $baseCalculo * ($porcentaje / 100);
        
        $data = [
            'apartado_id' => $apartadoId,
            'venta_id' => null,
            'vendedor_id' => $vendedorId,
            'configuracion_comision_id' => $configuracionId,
            'base_calculo' => $baseCalculo,
            'tipo_calculo' => 'porcentaje',
            'porcentaje_aplicado' => $porcentaje,
            'monto_comision_total' => $montoComision,
            'estatus' => 'devengada',
            'fecha_generacion' => date('Y-m-d')
        ];

        return $this->insert($data);
    }

    /**
     * Actualizar comisión al liquidar enganche
     */
    public function actualizarComisionPorVenta(int $comisionId, int $ventaId): bool
    {
        return $this->update($comisionId, [
            'venta_id' => $ventaId,
            'estatus' => 'pendiente_aceptacion'
        ]);
    }

    /**
     * Cambiar estado de comisión
     */
    public function cambiarEstado(int $comisionId, string $nuevoEstado, string $observaciones = null): bool
    {
        $data = [
            'estatus' => $nuevoEstado
        ];

        if ($observaciones) {
            $data['observaciones'] = $observaciones;
        }

        // Si se está pagando, actualizar fecha
        if ($nuevoEstado === 'pagada' || $nuevoEstado === 'realizado') {
            $data['fecha_ultimo_pago'] = date('Y-m-d');
        }

        return $this->update($comisionId, $data);
    }

    /**
     * Obtener estadísticas de comisiones
     */
    public function getEstadisticas(): array
    {
        $builder = $this->builder();
        
        $stats = $builder->select('
                COUNT(*) as total_comisiones,
                SUM(CASE WHEN estatus IN ("pendiente", "aceptada", "en_proceso") THEN 1 ELSE 0 END) as comisiones_pendientes,
                SUM(CASE WHEN estatus = "pagada" THEN 1 ELSE 0 END) as comisiones_pagadas,
                SUM(monto_comision_total) as monto_total,
                SUM(CASE WHEN estatus = "pagada" THEN monto_comision_total ELSE 0 END) as monto_pagado,
                SUM(CASE WHEN estatus != "pagada" AND estatus != "cancelada" THEN monto_comision_total ELSE 0 END) as monto_pendiente
            ')
            ->get()
            ->getRowArray();

        return $stats ?: [
            'total_comisiones' => 0,
            'comisiones_pendientes' => 0,
            'comisiones_pagadas' => 0,
            'monto_total' => 0,
            'monto_pagado' => 0,
            'monto_pendiente' => 0
        ];
    }

    /**
     * Obtener comisiones por vendedor
     */
    public function getComisionesPorVendedor(int $vendedorId, array $filtros = []): array
    {
        $filtros['vendedor_id'] = $vendedorId;
        return $this->getComisionesConRelaciones($filtros);
    }

    /**
     * Registrar pago de comisión
     */
    public function registrarPago(int $comisionId, float $montoPago, string $concepto): bool
    {
        $comision = $this->find($comisionId);
        
        if (!$comision) {
            return false;
        }

        $nuevoMontoPagado = $comision->monto_pagado_total + $montoPago;
        $nuevoEstatus = ($nuevoMontoPagado >= $comision->monto_comision_total) ? 'pagada' : 'parcial';

        return $this->update($comisionId, [
            'monto_pagado_total' => $nuevoMontoPagado,
            'estatus' => $nuevoEstatus,
            'fecha_ultimo_pago' => date('Y-m-d')
        ]);
    }
}