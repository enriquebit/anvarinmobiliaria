<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model para FormasPago
 * Gestiona el catálogo de formas de pago disponibles
 */
class FormasPagoModel extends Model
{
    protected $table = 'formas_pago';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'nombre',
        'clave',
        'descripcion',
        'metodo_pago',
        'requiere_referencia',
        'requiere_comprobante',
        'activo',
        'orden'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'nombre' => 'required|min_length[3]|max_length[100]',
        'clave' => 'required|min_length[2]|max_length[50]|is_unique[formas_pago.clave,id,{id}]',
        'metodo_pago' => 'required|in_list[transferencia,efectivo,tarjeta,cheque]',
        'orden' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la forma de pago es requerido',
            'min_length' => 'El nombre debe tener al menos 3 caracteres',
            'max_length' => 'El nombre no puede exceder 100 caracteres'
        ],
        'clave' => [
            'required' => 'La clave es requerida',
            'is_unique' => 'La clave ya existe, debe ser única'
        ],
        'metodo_pago' => [
            'required' => 'El método de pago es requerido',
            'in_list' => 'Método de pago no válido'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Eventos del modelo
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Obtener formas de pago activas ordenadas
     */
    public function getFormasActivas(): array
    {
        return $this->where('activo', true)
                   ->orderBy('orden', 'ASC')
                   ->orderBy('nombre', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener formas de pago por método específico
     */
    public function getFormasPorMetodo(string $metodo): array
    {
        return $this->where('activo', true)
                   ->where('metodo_pago', $metodo)
                   ->orderBy('orden', 'ASC')
                   ->orderBy('nombre', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener estadísticas de uso de formas de pago
     */
    public function getEstadisticasUso(): array
    {
        $builder = $this->db->table('ventas_pagos vp');
        $stats = $builder->select('
                fp.nombre,
                fp.metodo_pago,
                COUNT(vp.id) as total_usos,
                SUM(vp.monto) as monto_total,
                AVG(vp.monto) as monto_promedio
            ')
            ->join('formas_pago fp', 'fp.id = vp.forma_pago_id', 'left')
            ->where('vp.estado', 'aplicado')
            ->groupBy('vp.forma_pago_id, fp.nombre, fp.metodo_pago')
            ->orderBy('total_usos', 'DESC')
            ->get()
            ->getResult();

        return $stats;
    }

    /**
     * Obtener configuración completa por método de pago
     */
    public function getConfiguracionCompleta(): array
    {
        $formas = $this->getFormasActivas();
        $configuracion = [];

        foreach ($formas as $forma) {
            if (!isset($configuracion[$forma->metodo_pago])) {
                $configuracion[$forma->metodo_pago] = [
                    'metodo' => $forma->metodo_pago,
                    'formas' => []
                ];
            }
            
            $configuracion[$forma->metodo_pago]['formas'][] = [
                'id' => $forma->id,
                'nombre' => $forma->nombre,
                'clave' => $forma->clave,
                'descripcion' => $forma->descripcion,
                'requiere_referencia' => $forma->requiere_referencia,
                'requiere_comprobante' => $forma->requiere_comprobante,
                'orden' => $forma->orden
            ];
        }

        return array_values($configuracion);
    }

    /**
     * Validar si una forma de pago requiere comprobante
     */
    public function requiereComprobante(int $formaId): bool
    {
        $forma = $this->find($formaId);
        return $forma ? (bool) $forma->requiere_comprobante : false;
    }

    /**
     * Validar si una forma de pago requiere referencia
     */
    public function requiereReferencia(int $formaId): bool
    {
        $forma = $this->find($formaId);
        return $forma ? (bool) $forma->requiere_referencia : false;
    }

    /**
     * Activar/desactivar forma de pago
     */
    public function toggleEstado(int $id): bool
    {
        $forma = $this->find($id);
        if (!$forma) {
            return false;
        }

        return $this->update($id, ['activo' => !$forma->activo]);
    }

    /**
     * Obtener formas de pago para select dropdown
     */
    public function getForSelect(string $metodo = null): array
    {
        $builder = $this->where('activo', true);
        
        if ($metodo) {
            $builder->where('metodo_pago', $metodo);
        }
        
        $formas = $builder->orderBy('orden', 'ASC')
                         ->orderBy('nombre', 'ASC')
                         ->findAll();

        $options = [];
        foreach ($formas as $forma) {
            $options[$forma->id] = $forma->nombre;
        }

        return $options;
    }

    /**
     * Actualizar orden de visualización
     */
    public function actualizarOrden(array $ordenIds): bool
    {
        $this->db->transStart();

        foreach ($ordenIds as $orden => $id) {
            $this->update($id, ['orden' => $orden + 1]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Obtener formas de pago activas
     */
    public function getFormasPagoActivas()
    {
        return $this->where('activo', 1)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }
}