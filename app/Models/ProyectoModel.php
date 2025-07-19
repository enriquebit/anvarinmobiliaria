<?php

namespace App\Models;

use CodeIgniter\Model;

class ProyectoModel extends Model
{
    protected $table            = 'proyectos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Proyecto';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'clave',
        'empresas_id',
        'descripcion',
        'direccion',
        'longitud',
        'latitud',
        'color',
        'fecha_inicio',
        'fecha_estimada_fin',
        'estatus'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [
        'nombre'     => 'permit_empty|max_length[255]',
        'clave'      => 'permit_empty|max_length[100]',
        'empresas_id' => 'permit_empty',
        'fecha_inicio' => 'permit_empty',
        'fecha_estimada_fin' => 'permit_empty',
        'estatus' => 'permit_empty'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getProyectosConEmpresa()
    {
        return $this->select('proyectos.*, empresas.nombre as nombre_empresa')
                    ->join('empresas', 'empresas.id = proyectos.empresas_id')
                    ->where('empresas.activo', 1) // Solo empresas activas
                    ->where('proyectos.estatus', 'activo') // Solo proyectos activos
                    ->orderBy('proyectos.created_at', 'DESC')
                    ->findAll();
    }

    public function getProyectoConEmpresa($id)
    {
        return $this->select('proyectos.*, empresas.nombre as nombre_empresa')
                    ->join('empresas', 'empresas.id = proyectos.empresas_id')
                    ->find($id);
    }

    /**
     * Obtener proyectos con estadísticas completas para tabla principal
     */
    public function getProyectosConEstadisticas()
    {
        // Temporalmente sin conteos de lotes hasta verificar estructura de tabla
        return $this->select('
                proyectos.*, 
                empresas.nombre as nombre_empresa,
                (SELECT COUNT(*) FROM manzanas WHERE proyectos_id = proyectos.id) as total_manzanas,
                0 as total_lotes,
                0 as lotes_disponibles,
                0 as lotes_vendidos,
                0 as total_documentos
            ')
            ->join('empresas', 'empresas.id = proyectos.empresas_id')
            ->orderBy('proyectos.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Obtener estadísticas detalladas de un proyecto específico
     */
    public function getEstadisticasProyecto($proyectoId): array
    {
        $db = \Config\Database::connect();
        
        // Estadísticas de manzanas
        $manzanas = $db->query("SELECT COUNT(*) as total FROM manzanas WHERE proyectos_id = ?", [$proyectoId])->getRow();
        
        // Estadísticas de lotes (temporalmente deshabilitado hasta verificar estructura)
        $lotes = (object)[
            'total' => 0,
            'disponibles' => 0,
            'vendidos' => 0,
            'apartados' => 0,
            'reservados' => 0
        ];
        
        // Documentos del proyecto
        $documentos = $db->query("SELECT COUNT(*) as total FROM documentos_proyecto WHERE proyectos_id = ?", [$proyectoId])->getRow();
        
        // Calcular porcentaje de avance
        $porcentajeAvance = 0;
        if ($lotes->total > 0) {
            $porcentajeAvance = round(($lotes->vendidos / $lotes->total) * 100, 2);
        }
        
        return [
            'manzanas' => $manzanas->total ?? 0,
            'lotes' => [
                'total' => $lotes->total ?? 0,
                'disponibles' => $lotes->disponibles ?? 0,
                'vendidos' => $lotes->vendidos ?? 0,
                'apartados' => $lotes->apartados ?? 0,
                'reservados' => $lotes->reservados ?? 0
            ],
            'documentos' => $documentos->total ?? 0,
            'avance_ventas' => $porcentajeAvance
        ];
    }

    /**
     * Obtener porcentaje de avance de ventas de un proyecto
     */
    public function getAvanceVentas($proyectoId): float
    {
        $db = \Config\Database::connect();
        
        // Temporalmente devolver 0 hasta verificar estructura de tabla lotes
        $resultado = (object)['total' => 0, 'vendidos' => 0];
        
        if ($resultado->total > 0) {
            return round(($resultado->vendidos / $resultado->total) * 100, 2);
        }
        
        return 0.0;
    }

    /**
     * Obtener opciones para select dropdown
     */
    public function obtenerOpcionesSelect(int $empresaId = null): array
    {
        $builder = $this->select('proyectos.id, proyectos.nombre, 
                                 COUNT(DISTINCT m.id) as total_manzanas,
                                 COUNT(DISTINCT l.id) as total_lotes')
                       ->join('empresas', 'empresas.id = proyectos.empresas_id')
                       ->join('manzanas m', 'm.proyectos_id = proyectos.id AND m.activo = 1', 'left')
                       ->join('lotes l', 'l.proyectos_id = proyectos.id AND l.activo = 1', 'left')
                       ->join('estados_lotes el', 'l.estados_lotes_id = el.id AND el.codigo = 0', 'left')
                       ->where('empresas.activo', 1) // Solo empresas activas
                       ->where('proyectos.estatus', 'activo') // Solo proyectos activos
                       ->groupBy('proyectos.id');
        
        if ($empresaId) {
            $builder->where('proyectos.empresas_id', $empresaId);
        }
        
        // Ordenar por proyectos con más lotes disponibles primero
        $proyectos = $builder->orderBy('total_lotes', 'DESC')
                           ->orderBy('total_manzanas', 'DESC')
                           ->orderBy('proyectos.nombre', 'ASC')
                           ->findAll();
        
        $opciones = [];
        
        foreach ($proyectos as $proyecto) {
            $sufijo = '';
            if ($proyecto->total_lotes > 0) {
                $sufijo = " ({$proyecto->total_lotes} lotes)";
            } else if ($proyecto->total_manzanas > 0) {
                $sufijo = " ({$proyecto->total_manzanas} manzanas)";
            } else {
                $sufijo = " (sin lotes)";
            }
            $opciones[$proyecto->id] = $proyecto->nombre . $sufijo;
        }
        
        return $opciones;
    }

    /**
     * Obtener proyectos huérfanos (sin empresa válida)
     */
    public function getProyectosHuerfanos(): array
    {
        return $this->select('proyectos.*')
                    ->join('empresas', 'empresas.id = proyectos.empresas_id', 'left')
                    ->where('empresas.id IS NULL OR empresas.activo = 0')
                    ->findAll();
    }

    /**
     * Buscar proyectos con filtros
     */
    public function buscarProyectos(array $filtros = []): array
    {
        $builder = $this->select('proyectos.*, empresas.nombre as nombre_empresa')
                        ->join('empresas', 'empresas.id = proyectos.empresas_id');
        
        if (!empty($filtros['empresa_id'])) {
            $builder->where('proyectos.empresas_id', $filtros['empresa_id']);
        }
        
        if (!empty($filtros['busqueda'])) {
            $builder->groupStart()
                   ->like('proyectos.nombre', $filtros['busqueda'])
                   ->orLike('proyectos.clave', $filtros['busqueda'])
                   ->orLike('proyectos.descripcion', $filtros['busqueda'])
                   ->groupEnd();
        }
        
        if (!empty($filtros['estatus'])) {
            $builder->where('proyectos.estatus', $filtros['estatus']);
        }
        
        return $builder->orderBy('proyectos.created_at', 'DESC')->findAll();
    }
}