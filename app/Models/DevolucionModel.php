<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Devolucion;

class DevolucionModel extends Model
{
    protected $table            = 'devoluciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Devolucion';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ventas_id', 'motivo', 'monto_devolucion', 'metodo_devolucion',
        'referencia_devolucion', 'observaciones', 'procesado_por_user_id',
        'estado_anterior_venta', 'estado_anterior_lote'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'ventas_id'               => 'required|integer|is_not_unique[ventas.id]',
        'motivo'                  => 'required|in_list[solicitud_cliente,incumplimiento_contrato,error_administrativo,problema_legal,otro]',
        'monto_devolucion'        => 'required|numeric|greater_than[0]',
        'observaciones'           => 'required|min_length[10]|max_length[1000]',
        'procesado_por_user_id'   => 'required|integer|is_not_unique[users.id]',
        'estado_anterior_venta'   => 'required|max_length[50]',
        'estado_anterior_lote'    => 'required|integer'
    ];

    protected $validationMessages = [
        'ventas_id' => [
            'required' => 'La venta es obligatoria',
            'is_not_unique' => 'La venta especificada no existe'
        ],
        'motivo' => [
            'required' => 'El motivo de devolución es obligatorio',
            'in_list' => 'Motivo de devolución inválido'
        ],
        'monto_devolucion' => [
            'required' => 'El monto de devolución es obligatorio',
            'greater_than' => 'El monto debe ser mayor a 0'
        ],
        'observaciones' => [
            'required' => 'Las observaciones son obligatorias',
            'min_length' => 'Las observaciones deben tener al menos 10 caracteres'
        ]
    ];

    protected $skipValidation = false;

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

    /**
     * Registrar nueva devolución
     */
    public function registrarDevolucion(int $ventaId, array $datosDevolucion, int $usuarioId): ?Devolucion
    {
        try {
            // Obtener información de la venta antes de cancelar
            $ventaModel = model('VentaModel');
            $venta = $ventaModel->find($ventaId);
            
            if (!$venta) {
                throw new \Exception('Venta no encontrada');
            }

            // Obtener información del lote antes de liberar
            $loteModel = model('LoteModel');
            $lote = $loteModel->find($venta->lotes_id);
            
            if (!$lote) {
                throw new \Exception('Lote asociado no encontrado');
            }

            $devolucionData = array_merge($datosDevolucion, [
                'ventas_id' => $ventaId,
                'procesado_por_user_id' => $usuarioId,
                'estado_anterior_venta' => $venta->estado,
                'estado_anterior_lote' => $lote->estados_lotes_id
            ]);

            $devolucionId = $this->insert($devolucionData, true);
            
            if (!$devolucionId) {
                throw new \Exception('Error al registrar la devolución');
            }

            return $this->find($devolucionId);

        } catch (\Exception $e) {
            log_message('error', 'Error en registrarDevolucion(): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener devoluciones por venta
     */
    public function getDevolucionesPorVenta(int $ventaId): array
    {
        return $this->where('ventas_id', $ventaId)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener devoluciones con información completa
     */
    public function getDevolucionesConInfo(array $filtros = []): array
    {
        $builder = $this->builder();
        
        $builder->select('
            devoluciones.*,
            ventas.folio as venta_folio,
            ventas.total as venta_total,
            clientes.nombres as cliente_nombres,
            clientes.apellido_paterno as cliente_apellido_paterno,
            CONCAT(clientes.nombres, " ", clientes.apellido_paterno) as cliente_nombre,
            lotes.clave as lote_clave,
            proyectos.nombre as proyecto_nombre,
            auth_identities.secret as procesado_por_email
        ');
        
        $builder->join('ventas', 'ventas.id = devoluciones.ventas_id', 'left');
        $builder->join('clientes', 'clientes.id = ventas.clientes_id', 'left');
        $builder->join('lotes', 'lotes.id = ventas.lotes_id', 'left');
        $builder->join('proyectos', 'proyectos.id = lotes.proyectos_id', 'left');
        $builder->join('users', 'users.id = devoluciones.procesado_por_user_id', 'left');
        $builder->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left');

        // Aplicar filtros
        if (!empty($filtros['motivo'])) {
            $builder->where('devoluciones.motivo', $filtros['motivo']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $builder->where('DATE(devoluciones.created_at) >=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $builder->where('DATE(devoluciones.created_at) <=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['monto_min'])) {
            $builder->where('devoluciones.monto_devolucion >=', $filtros['monto_min']);
        }

        if (!empty($filtros['monto_max'])) {
            $builder->where('devoluciones.monto_devolucion <=', $filtros['monto_max']);
        }

        $builder->orderBy('devoluciones.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Obtener estadísticas de devoluciones
     */
    public function getEstadisticasDevolucion(): array
    {
        $builder = $this->builder();
        
        $estadisticas = $builder->select('
                COUNT(*) as total_devoluciones,
                SUM(monto_devolucion) as monto_total_devuelto,
                AVG(monto_devolucion) as promedio_devolucion,
                COUNT(CASE WHEN motivo = "solicitud_cliente" THEN 1 END) as por_solicitud_cliente,
                COUNT(CASE WHEN motivo = "incumplimiento_contrato" THEN 1 END) as por_incumplimiento,
                COUNT(CASE WHEN motivo = "error_administrativo" THEN 1 END) as por_error_admin,
                COUNT(CASE WHEN motivo = "problema_legal" THEN 1 END) as por_problema_legal,
                COUNT(CASE WHEN motivo = "otro" THEN 1 END) as por_otro_motivo
            ')
            ->get()
            ->getRowArray();

        return $estadisticas ?: [
            'total_devoluciones' => 0,
            'monto_total_devuelto' => 0,
            'promedio_devolucion' => 0,
            'por_solicitud_cliente' => 0,
            'por_incumplimiento' => 0,
            'por_error_admin' => 0,
            'por_problema_legal' => 0,
            'por_otro_motivo' => 0
        ];
    }

    /**
     * Obtener devoluciones por período
     */
    public function getDevolucionesPorPeriodo(string $fechaInicio, string $fechaFin): array
    {
        return $this->where('DATE(created_at) >=', $fechaInicio)
                   ->where('DATE(created_at) <=', $fechaFin)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }
}