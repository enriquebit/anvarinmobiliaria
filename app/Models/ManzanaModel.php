<?php

namespace App\Models;

use CodeIgniter\Model;

class ManzanaModel extends Model
{
    protected $table            = 'manzanas';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'App\Entities\Manzana';
    protected $useSoftDeletes    = false; // Usamos campo 'activo' en lugar de deleted_at
    protected $protectFields     = true;
    protected $allowedFields     = [
        'nombre',
        'clave', 
        'descripcion',
        'proyectos_id',
        'longitud',
        'latitud',
        'color',
        'activo'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nombre'       => 'required|max_length[100]',
        'proyectos_id' => 'required|integer|is_not_unique[proyectos.id]',
        'descripcion'  => 'permit_empty|max_length[1000]',
        'longitud'     => 'permit_empty|decimal',
        'latitud'      => 'permit_empty|decimal',
        'color'        => 'permit_empty|regex_match[/^#[a-fA-F0-9]{6}$/]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required'   => 'El nombre de la manzana es obligatorio',
            'max_length' => 'El nombre no puede exceder 100 caracteres',
        ],
        'proyectos_id' => [
            'required'       => 'Debe seleccionar un proyecto',
            'integer'        => 'El proyecto debe ser un número válido',
            'is_not_unique'  => 'El proyecto seleccionado no existe',
        ],
        'descripcion' => [
            'max_length' => 'La descripción no puede exceder 1000 caracteres',
        ],
        'longitud' => [
            'decimal' => 'La longitud debe ser un número decimal válido',
        ],
        'latitud' => [
            'decimal' => 'La latitud debe ser un número decimal válido',
        ],
        'color' => [
            'regex_match' => 'El color debe estar en formato hexadecimal (#000000)',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateClave'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['generateClave'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Genera automáticamente la clave antes de insertar o actualizar
     */
    protected function generateClave(array $data): array
    {
        if (isset($data['data']['nombre']) && isset($data['data']['proyectos_id'])) {
            $proyectoModel = model('ProyectoModel');
            $proyecto = $proyectoModel->find($data['data']['proyectos_id']);
            
            if ($proyecto) {
                $data['data']['clave'] = $proyecto->clave . '-' . strtoupper($data['data']['nombre']);
            }
        }

        return $data;
    }

    /**
     * Obtiene todas las manzanas activas
     */
    public function getActivas(): array
    {
        return $this->where('activo', true)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene manzanas por proyecto
     */
    public function getPorProyecto(int $proyectoId): array
    {
        return $this->where('proyectos_id', $proyectoId)
                    ->where('activo', true)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Busca manzanas con filtros avanzados
     */
    public function buscarConFiltros(array $filtros = []): array
    {
        $builder = $this->builder();
        $builder->select('manzanas.*, proyectos.nombre as nombre_proyecto, proyectos.clave as clave_proyecto')
                ->join('proyectos', 'manzanas.proyectos_id = proyectos.id', 'left');

        // Filtro por activo (por defecto solo activas)
        if (!isset($filtros['incluir_inactivas']) || !$filtros['incluir_inactivas']) {
            $builder->where('manzanas.activo', true);
        }

        // Filtro por proyecto
        if (!empty($filtros['proyecto_id'])) {
            $builder->where('manzanas.proyectos_id', $filtros['proyecto_id']);
        }

        // Filtro por búsqueda de texto
        if (!empty($filtros['busqueda'])) {
            $builder->groupStart()
                    ->like('manzanas.nombre', $filtros['busqueda'])
                    ->orLike('manzanas.clave', $filtros['busqueda'])
                    ->orLike('manzanas.descripcion', $filtros['busqueda'])
                    ->orLike('proyectos.nombre', $filtros['busqueda'])
                    ->groupEnd();
        }

        // Ordenamiento
        $orden = $filtros['orden'] ?? 'manzanas.nombre';
        $direccion = $filtros['direccion'] ?? 'ASC';
        $builder->orderBy($orden, $direccion);

        return $builder->get()->getResultArray();
    }

    /**
     * Verifica si existe una manzana con el mismo nombre en el proyecto
     */
    public function existeNombreEnProyecto(string $nombre, int $proyectoId, ?int $excepto = null): bool
    {
        $builder = $this->builder();
        $builder->where('nombre', strtoupper($nombre))
                ->where('proyectos_id', $proyectoId)
                ->where('activo', true);

        if ($excepto) {
            $builder->where('id !=', $excepto);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Soft delete - marcar como inactiva
     */
    public function softDelete(int $id): bool
    {
        return $this->update($id, ['activo' => false]);
    }

    /**
     * Restaurar manzana eliminada
     */
    public function restaurar(int $id): bool
    {
        return $this->update($id, ['activo' => true]);
    }

    /**
     * Obtiene estadísticas de manzanas
     */
    public function getEstadisticas(): array
    {
        $total = $this->countAll();
        $activas = $this->where('activo', true)->countAllResults();
        $inactivas = $total - $activas;
        
        $conCoordenadas = $this->where('activo', true)
                               ->where('longitud IS NOT NULL')
                               ->where('latitud IS NOT NULL')
                               ->where('longitud !=', '')
                               ->where('latitud !=', '')
                               ->countAllResults();

        return [
            'total'           => $total,
            'activas'         => $activas,
            'inactivas'       => $inactivas,
            'con_coordenadas' => $conCoordenadas,
            'sin_coordenadas' => $activas - $conCoordenadas,
        ];
    }

    /**
     * Obtiene manzanas para select (id => nombre)
     */
    public function getParaSelect(int $proyectoId = null): array
    {
        $builder = $this->builder();
        $builder->select('id, nombre, clave')
                ->where('activo', true);

        if ($proyectoId) {
            $builder->where('proyectos_id', $proyectoId);
        }

        $manzanas = $builder->orderBy('nombre', 'ASC')->get()->getResultArray();
        
        $opciones = [];
        foreach ($manzanas as $manzana) {
            $opciones[$manzana['id']] = $manzana['nombre'] . ' (' . $manzana['clave'] . ')';
        }

        return $opciones;
    }

    /**
     * Valida datos antes de guardar
     */
    public function validarDatos(array $datos, ?int $exceptoId = null): array
    {
        $errores = [];

        // Validar nombre único en proyecto
        if (!empty($datos['nombre']) && !empty($datos['proyectos_id'])) {
            if ($this->existeNombreEnProyecto($datos['nombre'], $datos['proyectos_id'], $exceptoId)) {
                $errores['nombre'] = 'Ya existe una manzana con este nombre en el proyecto seleccionado';
            }
        }

        // Validar coordenadas
        if (!empty($datos['longitud'])) {
            $longitud = (float) $datos['longitud'];
            if ($longitud < -180 || $longitud > 180) {
                $errores['longitud'] = 'La longitud debe estar entre -180 y 180 grados';
            }
        }

        if (!empty($datos['latitud'])) {
            $latitud = (float) $datos['latitud'];
            if ($latitud < -90 || $latitud > 90) {
                $errores['latitud'] = 'La latitud debe estar entre -90 y 90 grados';
            }
        }

        return $errores;
    }
}