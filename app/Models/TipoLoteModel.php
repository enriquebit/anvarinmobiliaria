<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoLoteModel extends Model
{
    protected $table            = 'tipos_lotes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\TipoLote';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre', 'descripcion', 'activo'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombre' => 'required|max_length[100]|is_unique[tipos_lotes.nombre,id,{id}]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre del tipo es obligatorio',
            'max_length' => 'El nombre no debe exceder {param} caracteres',
            'is_unique' => 'Ya existe un tipo con este nombre'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Obtener tipos activos para select
     */
    public function getTiposPairaSelect(): array
    {
        $tipos = $this->where('activo', true)
                     ->orderBy('nombre', 'ASC')
                     ->findAll();

        $opciones = ['' => 'Seleccionar tipo...'];
        foreach ($tipos as $tipo) {
            $opciones[$tipo->id] = $tipo->nombre;
        }

        return $opciones;
    }

    /**
     * Obtener tipos con conteo de lotes
     */
    public function getTiposConConteo(): array
    {
        $builder = $this->db->table($this->table . ' t');
        return $builder->select('t.*, COUNT(CASE WHEN l.activo = 1 THEN l.id END) as lotes_count')
                      ->join('lotes l', 'l.tipos_lotes_id = t.id', 'left')
                      ->groupBy('t.id')
                      ->orderBy('t.activo', 'DESC')
                      ->orderBy('t.nombre', 'ASC')
                      ->get()
                      ->getResultArray();
    }
}