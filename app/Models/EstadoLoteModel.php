<?php

namespace App\Models;

use CodeIgniter\Model;

class EstadoLoteModel extends Model
{
    protected $table            = 'estados_lotes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre', 'codigo', 'color', 'descripcion', 'activo'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombre' => 'required|max_length[100]|is_unique[estados_lotes.nombre,id,{id}]',
        'codigo' => 'required|integer|is_unique[estados_lotes.id,id,{id}]',
        'color'  => 'permit_empty|regex_match[/^#[0-9a-fA-F]{6}$/]',
        'activo' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre del estado es obligatorio',
            'max_length' => 'El nombre no debe exceder {param} caracteres',
            'is_unique' => 'Ya existe un estado con este nombre'
        ],
        'codigo' => [
            'required' => 'El código del estado es obligatorio',
            'integer' => 'El código debe ser un número entero',
            'is_unique' => 'Ya existe un estado con este código'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Obtener estados activos para select
     */
    public function getEstadosPairaSelect(): array
    {
        $estados = $this->where('activo', true)
                       ->orderBy('nombre', 'ASC')
                       ->findAll();

        $opciones = ['' => 'Seleccionar estado...'];
        foreach ($estados as $estado) {
            $opciones[$estado->id] = $estado->nombre;
        }

        return $opciones;
    }

    /**
     * Obtener estados con conteo de lotes
     */
    public function getEstadosConConteo(): array
    {
        $builder = $this->db->table($this->table . ' e');
        return $builder->select('e.*, COUNT(l.id) as lotes_count')
                      ->join('lotes l', 'l.estados_lotes_id = e.id', 'left')
                      ->where('e.activo', true)
                      ->where('l.activo', true)
                      ->groupBy('e.id')
                      ->orderBy('e.nombre', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener estadísticas de estados para dashboard
     */
    public function getEstadisticasEstados(): array
    {
        $builder = $this->db->table($this->table . ' e');
        return $builder->select('e.nombre, e.id, COUNT(l.id) as total_lotes,
                               SUM(l.precio_total) as valor_total,
                               AVG(l.precio_total) as precio_promedio')
                      ->join('lotes l', 'l.estados_lotes_id = e.id', 'left')
                      ->where('e.activo', true)
                      ->where('l.activo', true)
                      ->groupBy('e.id')
                      ->orderBy('total_lotes', 'DESC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener estado por código
     */
    public function getEstadoPorCodigo(int $codigo): ?object
    {
        return $this->where('codigo', $codigo)
                   ->where('activo', true)
                   ->first();
    }

    /**
     * Buscar ID por código
     */
    public function getIdPorCodigo(int $codigo): ?int
    {
        $estado = $this->getEstadoPorCodigo($codigo);
        return $estado ? $estado->id : null;
    }
}