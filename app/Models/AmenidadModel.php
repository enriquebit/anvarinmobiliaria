<?php

namespace App\Models;

use CodeIgniter\Model;

class AmenidadModel extends Model
{
    protected $table            = 'amenidades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Amenidad';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre', 'descripcion', 'icono', 'activo'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombre' => 'required|max_length[100]|is_unique[amenidades.nombre,id,{id}]',
        'icono'  => 'permit_empty|max_length[100]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la amenidad es obligatorio',
            'max_length' => 'El nombre no debe exceder {param} caracteres',
            'is_unique' => 'Ya existe una amenidad con este nombre'
        ],
        'icono' => [
            'max_length' => 'La clase del icono no debe exceder {param} caracteres'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Obtener amenidades activas para select multiple
     */
    public function getAmenidadesPairaSelect(): array
    {
        $amenidades = $this->where('activo', true)
                          ->orderBy('nombre', 'ASC')
                          ->findAll();

        $opciones = [];
        foreach ($amenidades as $amenidad) {
            $opciones[$amenidad->id] = $amenidad->nombre;
        }

        return $opciones;
    }

    /**
     * Obtener amenidades con conteo de lotes
     */
    public function getAmenidadesConConteo(): array
    {
        $builder = $this->db->table($this->table . ' a');
        return $builder->select('a.*, COUNT(la.lotes_id) as lotes_count')
                      ->join('lotes_amenidades la', 'la.amenidades_id = a.id', 'left')
                      ->join('lotes l', 'l.id = la.lotes_id AND l.activo = 1', 'left')
                      ->where('a.activo', true)
                      ->groupBy('a.id')
                      ->orderBy('a.nombre', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener amenidades más populares
     */
    public function getAmenidadesPopulares(int $limite = 10): array
    {
        $builder = $this->db->table($this->table . ' a');
        return $builder->select('a.*, COUNT(la.lotes_id) as uso_count')
                      ->join('lotes_amenidades la', 'la.amenidades_id = a.id')
                      ->join('lotes l', 'l.id = la.lotes_id AND l.activo = 1')
                      ->where('a.activo', true)
                      ->groupBy('a.id')
                      ->orderBy('uso_count', 'DESC')
                      ->limit($limite)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Buscar amenidades por texto
     */
    public function buscarAmenidades(string $termino): array
    {
        return $this->like('nombre', $termino)
                   ->orLike('descripcion', $termino)
                   ->where('activo', true)
                   ->orderBy('nombre', 'ASC')
                   ->findAll();
    }
}