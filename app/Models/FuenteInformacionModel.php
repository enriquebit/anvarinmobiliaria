<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\FuenteInformacion;

class FuenteInformacionModel extends Model
{
    protected $table = 'fuentes_informacion';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\FuenteInformacion::class;
    protected $useSoftDeletes = false;

    // Campos permitidos para inserción/actualización
    protected $allowedFields = [
        'nombre', 'valor', 'activo'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validaciones básicas
    protected $validationRules = [
        'nombre' => 'required|max_length[50]',
        'valor' => 'required|max_length[20]|is_unique[fuentes_informacion.valor,id,{id}]',
        'activo' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la fuente es obligatorio',
            'max_length' => 'El nombre no puede exceder 50 caracteres'
        ],
        'valor' => [
            'required' => 'El valor de la fuente es obligatorio',
            'max_length' => 'El valor no puede exceder 20 caracteres',
            'is_unique' => 'Este valor ya existe'
        ]
    ];

    // =====================================================================
    // MÉTODOS PÚBLICOS
    // =====================================================================

    /**
     * Obtener todas las fuentes de información activas
     */
    public function obtenerTodosActivos(): array
    {
        return $this->where('activo', 1)
                   ->orderBy('id', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener opciones para dropdown/select
     */
    public function obtenerOpcionesSelect(): array
    {
        $fuentes = $this->obtenerTodosActivos();
        $opciones = [];
        
        foreach ($fuentes as $fuente) {
            $opciones[$fuente->valor] = $fuente->nombre;
        }
        
        return $opciones;
    }

    /**
     * Buscar fuente de información por valor
     */
    public function obtenerPorValor(string $valor): ?FuenteInformacion
    {
        return $this->where('valor', $valor)
                   ->where('activo', 1)
                   ->first();
    }

    /**
     * Activar/Desactivar fuente de información
     */
    public function cambiarEstado(int $id, bool $activo): bool
    {
        return $this->update($id, ['activo' => $activo ? 1 : 0]);
    }

    /**
     * Obtener fuentes para administración
     */
    public function obtenerTodos(): array
    {
        return $this->orderBy('id', 'ASC')->findAll();
    }

    /**
     * Obtener estadísticas de uso
     */
    public function obtenerEstadisticasUso(): array
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                fi.nombre,
                fi.valor,
                COUNT(c.id) as total_clientes
            FROM fuentes_informacion fi
            LEFT JOIN clientes c ON c.fuente_informacion = fi.valor
            WHERE fi.activo = 1
            GROUP BY fi.id, fi.nombre, fi.valor
            ORDER BY total_clientes DESC, fi.nombre ASC
        ");
        
        return $query->getResultArray();
    }
}