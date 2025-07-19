<?php

namespace App\Models;

use CodeIgniter\Model;

class DivisionModel extends Model
{
    protected $table            = 'divisiones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Division';
    protected $useSoftDeletes   = false; // Usamos campo 'activo' en lugar de deleted_at
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'clave',
        'empresas_id',
        'proyectos_id',
        'descripcion',
        'orden',
        'color',
        'activo'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation (relajada para desarrollo)
    protected $validationRules = [
        'nombre'       => 'permit_empty|max_length[255]',
        'clave'        => 'permit_empty|max_length[10]',
        'empresas_id'  => 'permit_empty',
        'proyectos_id' => 'permit_empty',
        'descripcion'  => 'permit_empty|max_length[1000]',
        'orden'        => 'permit_empty',
        'color'        => 'permit_empty',
        'activo'       => 'permit_empty'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required'   => 'El nombre de la división es obligatorio',
            'max_length' => 'El nombre no puede exceder 255 caracteres',
        ],
        'clave' => [
            'required'     => 'La clave de la división es obligatoria',
            'max_length'   => 'La clave no puede exceder 10 caracteres',
            'alpha_numeric' => 'La clave solo puede contener letras y números',
        ],
        'empresas_id' => [
            'required'      => 'Debe seleccionar una empresa',
            'integer'       => 'La empresa debe ser un número válido',
            'is_not_unique' => 'La empresa seleccionada no existe',
        ],
        'proyectos_id' => [
            'required'      => 'Debe seleccionar un proyecto',
            'integer'       => 'El proyecto debe ser un número válido',
            'is_not_unique' => 'El proyecto seleccionado no existe',
        ],
        'descripcion' => [
            'max_length' => 'La descripción no puede exceder 1000 caracteres',
        ],
        'orden' => [
            'integer'      => 'El orden debe ser un número entero',
            'greater_than' => 'El orden debe ser mayor a 0',
        ],
        'color' => [
            'regex_match' => 'El color debe estar en formato hexadecimal (#000000)',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['validarClaveUnica'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['validarClaveUnica'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Valida que la clave sea única por empresa y proyecto antes de insertar/actualizar
     */
    protected function validarClaveUnica(array $data): array
    {
        if (isset($data['data']['clave']) && isset($data['data']['empresas_id']) && isset($data['data']['proyectos_id'])) {
            $builder = $this->builder();
            $builder->where('clave', $data['data']['clave'])
                    ->where('empresas_id', $data['data']['empresas_id'])
                    ->where('proyectos_id', $data['data']['proyectos_id'])
                    ->where('activo', true);

            // Si es actualización, excluir el registro actual
            if (isset($data['id'])) {
                $builder->where('id !=', $data['id']);
            }

            if ($builder->countAllResults() > 0) {
                throw new \InvalidArgumentException('Ya existe una división con esta clave en el proyecto seleccionado');
            }
        }

        return $data;
    }

    /**
     * Obtiene todas las divisiones activas
     */
    public function getActivas(): array
    {
        return $this->where('activo', true)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene divisiones por proyecto
     */
    public function getPorProyecto(int $proyectoId): array
    {
        return $this->where('proyectos_id', $proyectoId)
                    ->where('activo', true)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene divisiones por empresa
     */
    public function getPorEmpresa(int $empresaId): array
    {
        return $this->where('empresas_id', $empresaId)
                    ->where('activo', true)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Busca divisiones con filtros avanzados
     */
    public function buscarConFiltros(array $filtros = []): array
    {
        $builder = $this->builder();
        $builder->select('divisiones.*, empresas.nombre as nombre_empresa, proyectos.nombre as nombre_proyecto, proyectos.clave as clave_proyecto')
                ->join('empresas', 'divisiones.empresas_id = empresas.id', 'left')
                ->join('proyectos', 'divisiones.proyectos_id = proyectos.id', 'left')
                ->where('empresas.activo', 1) // Solo empresas activas
                ->where('proyectos.estatus', 'activo'); // Solo proyectos activos

        // Filtro por activo (por defecto solo activas)
        if (!isset($filtros['incluir_inactivas']) || !$filtros['incluir_inactivas']) {
            $builder->where('divisiones.activo', true);
        }

        // Filtro por empresa
        if (!empty($filtros['empresa_id'])) {
            $builder->where('divisiones.empresas_id', $filtros['empresa_id']);
        }

        // Filtro por proyecto
        if (!empty($filtros['proyecto_id'])) {
            $builder->where('divisiones.proyectos_id', $filtros['proyecto_id']);
        }

        // Filtro por búsqueda de texto
        if (!empty($filtros['busqueda'])) {
            $builder->groupStart()
                    ->like('divisiones.nombre', $filtros['busqueda'])
                    ->orLike('divisiones.clave', $filtros['busqueda'])
                    ->orLike('divisiones.descripcion', $filtros['busqueda'])
                    ->orLike('proyectos.nombre', $filtros['busqueda'])
                    ->groupEnd();
        }

        // Ordenamiento
        $orden = $filtros['orden'] ?? 'divisiones.orden';
        $direccion = $filtros['direccion'] ?? 'ASC';
        $builder->orderBy($orden, $direccion);
        $builder->orderBy('divisiones.nombre', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Verifica si existe una división con el mismo nombre en el proyecto
     */
    public function existeNombreEnProyecto(string $nombre, int $empresaId, int $proyectoId, ?int $excepto = null): bool
    {
        $builder = $this->builder();
        $builder->where('nombre', trim($nombre))
                ->where('empresas_id', $empresaId)
                ->where('proyectos_id', $proyectoId)
                ->where('activo', true);

        if ($excepto) {
            $builder->where('id !=', $excepto);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Verifica si existe una división con la misma clave en el proyecto
     */
    public function existeClaveEnProyecto(string $clave, int $empresaId, int $proyectoId, ?int $excepto = null): bool
    {
        $builder = $this->builder();
        $builder->where('clave', strtoupper(trim($clave)))
                ->where('empresas_id', $empresaId)
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
     * Restaurar división eliminada
     */
    public function restaurar(int $id): bool
    {
        return $this->update($id, ['activo' => true]);
    }

    /**
     * Obtiene el siguiente número de orden para una empresa/proyecto
     */
    public function getSiguienteOrden(int $empresaId, int $proyectoId): int
    {
        $maxOrden = $this->where('empresas_id', $empresaId)
                         ->where('proyectos_id', $proyectoId)
                         ->selectMax('orden')
                         ->first();

        return ($maxOrden['orden'] ?? 0) + 1;
    }

    /**
     * Obtiene estadísticas de divisiones
     */
    public function getEstadisticas(): array
    {
        $total = $this->countAll();
        $activas = $this->where('activo', true)->countAllResults();
        $inactivas = $total - $activas;
        
        // Conteo por empresa
        $porEmpresa = $this->select('empresas.nombre, COUNT(*) as total')
                          ->join('empresas', 'divisiones.empresas_id = empresas.id')
                          ->where('divisiones.activo', true)
                          ->groupBy('empresas.id')
                          ->findAll();

        return [
            'total'        => $total,
            'activas'      => $activas,
            'inactivas'    => $inactivas,
            'por_empresa'  => $porEmpresa,
        ];
    }

    /**
     * Obtiene divisiones para select (id => nombre)
     */
    public function getParaSelect(int $proyectoId = null): array
    {
        $builder = $this->builder();
        $builder->select('id, nombre, clave, orden')
                ->where('activo', true);

        if ($proyectoId) {
            $builder->where('proyectos_id', $proyectoId);
        }

        $divisiones = $builder->orderBy('orden', 'ASC')
                            ->orderBy('nombre', 'ASC')
                            ->get()
                            ->getResultArray();
        
        $opciones = [];
        foreach ($divisiones as $division) {
            $opciones[$division['id']] = $division['nombre'] . ' (' . $division['clave'] . ')';
        }

        return $opciones;
    }

    /**
     * Obtiene opciones para select simple (para AJAX)
     */
    public function obtenerOpcionesSelect(int $proyectoId = null): array
    {
        $builder = $this->builder();
        $builder->select('id, nombre, clave')
                ->where('activo', true);

        if ($proyectoId) {
            $builder->where('proyectos_id', $proyectoId);
        }

        $divisiones = $builder->orderBy('orden', 'ASC')
                            ->orderBy('nombre', 'ASC')
                            ->get()
                            ->getResultArray();
        
        $opciones = ['' => 'Seleccionar división...'];
        foreach ($divisiones as $division) {
            $opciones[$division['id']] = $division['nombre'] . ' (' . $division['clave'] . ')';
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
        if (!empty($datos['nombre']) && !empty($datos['empresas_id']) && !empty($datos['proyectos_id'])) {
            if ($this->existeNombreEnProyecto($datos['nombre'], $datos['empresas_id'], $datos['proyectos_id'], $exceptoId)) {
                $errores['nombre'] = 'Ya existe una división con este nombre en el proyecto seleccionado';
            }
        }

        // Validar clave única en proyecto
        if (!empty($datos['clave']) && !empty($datos['empresas_id']) && !empty($datos['proyectos_id'])) {
            if ($this->existeClaveEnProyecto($datos['clave'], $datos['empresas_id'], $datos['proyectos_id'], $exceptoId)) {
                $errores['clave'] = 'Ya existe una división con esta clave en el proyecto seleccionado';
            }
        }

        return $errores;
    }

    /**
     * Obtener divisiones huérfanas (con empresas inactivas o proyectos inactivos)
     */
    public function getDivisionesHuerfanas(): array
    {
        return $this->select('divisiones.*')
                    ->join('empresas', 'empresas.id = divisiones.empresas_id', 'left')
                    ->join('proyectos', 'proyectos.id = divisiones.proyectos_id', 'left')
                    ->where('(empresas.id IS NULL OR empresas.activo = 0 OR proyectos.id IS NULL OR proyectos.estatus != "activo")')
                    ->findAll();
    }
}