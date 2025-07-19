<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaLoteModel extends Model
{
    protected $table            = 'categorias_lotes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\CategoriaLote';
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
        'nombre' => 'required|max_length[100]|is_unique[categorias_lotes.nombre,id,{id}]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la categoría es obligatorio',
            'max_length' => 'El nombre no debe exceder {param} caracteres',
            'is_unique' => 'Ya existe una categoría con este nombre'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Obtener categorías activas para select
     */
    public function getCategoriasPairaSelect(): array
    {
        $categorias = $this->where('activo', true)
                          ->orderBy('nombre', 'ASC')
                          ->findAll();

        $opciones = ['' => 'Seleccionar categoría...'];
        foreach ($categorias as $categoria) {
            $opciones[$categoria->id] = $categoria->nombre;
        }

        return $opciones;
    }

    /**
     * Obtener categorías con conteo de lotes
     */
    public function getCategoriasConConteo(): array
    {
        $builder = $this->db->table($this->table . ' c');
        return $builder->select('c.*, COUNT(l.id) as lotes_count')
                      ->join('lotes l', 'l.categorias_lotes_id = c.id', 'left')
                      ->groupBy('c.id, c.nombre, c.descripcion, c.activo, c.created_at, c.updated_at')
                      ->orderBy('c.nombre', 'ASC')
                      ->get()
                      ->getResultArray();
    }
}