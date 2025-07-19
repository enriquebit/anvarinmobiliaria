<?php

namespace App\Models;

use CodeIgniter\Model;

class FormaPagoModel extends Model
{
    protected $table            = 'formas_pago';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre', 'clave', 'descripcion', 'requiere_referencia',
        'requiere_comprobante', 'activo', 'orden'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombre' => 'required|max_length[100]',
        'clave'  => 'required|max_length[50]|is_unique[formas_pago.clave,id,{id}]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre es obligatorio'
        ],
        'clave' => [
            'required' => 'La clave es obligatoria',
            'is_unique' => 'Ya existe una forma de pago con esta clave'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Obtener formas de pago activas
     */
    public function getFormasPagoActivas(): array
    {
        return $this->where('activo', true)
                   ->orderBy('orden', 'ASC')
                   ->orderBy('nombre', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener forma de pago por clave
     */
    public function getPorClave(string $clave): ?array
    {
        return $this->where('clave', $clave)->first();
    }

    /**
     * Verificar si requiere referencia
     */
    public function requiereReferencia(int $formaPagoId): bool
    {
        $forma = $this->find($formaPagoId);
        return $forma ? (bool)$forma['requiere_referencia'] : false;
    }

    /**
     * Verificar si requiere comprobante
     */
    public function requiereComprobante(int $formaPagoId): bool
    {
        $forma = $this->find($formaPagoId);
        return $forma ? (bool)$forma['requiere_comprobante'] : false;
    }

    /**
     * Obtener opciones para select
     */
    public function getOpcionesSelect(): array
    {
        $formas = $this->getFormasPagoActivas();
        $opciones = [];
        
        foreach ($formas as $forma) {
            $opciones[$forma['id']] = $forma['nombre'];
        }
        
        return $opciones;
    }
}