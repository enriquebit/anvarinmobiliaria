<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Lote;

class LoteModel extends Model
{
    // Constantes de estados de lotes (corresponden a IDs de la tabla estados_lotes)
    const ESTADO_DISPONIBLE = 1;
    const ESTADO_APARTADO = 2;
    const ESTADO_VENDIDO = 3;
    const ESTADO_SUSPENDIDO = 4;

    protected $table            = 'lotes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\Lote';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'numero', 'clave', 'empresas_id', 'proyectos_id', 'divisiones_id', 'manzanas_id',
        'categorias_lotes_id', 'tipos_lotes_id', 'estados_lotes_id',
        'area', 'frente', 'fondo', 'lateral_izquierdo', 'lateral_derecho',
        'construccion', 'precio_m2', 'precio_total', 'descripcion',
        'coordenadas_poligono', 'longitud', 'latitud', 'color', 'activo'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'numero'                => 'required|max_length[50]',
        'empresas_id'           => 'required|integer|is_not_unique[empresas.id]',
        'proyectos_id'          => 'required|integer|is_not_unique[proyectos.id]',
        'divisiones_id'         => 'required|integer|is_not_unique[divisiones.id]',
        'manzanas_id'           => 'required|integer|is_not_unique[manzanas.id]',
        'categorias_lotes_id'   => 'required|integer|is_not_unique[categorias_lotes.id]',
        'tipos_lotes_id'        => 'required|integer|is_not_unique[tipos_lotes.id]',
        'estados_lotes_id'      => 'required|integer|is_not_unique[estados_lotes.id]',
        'area'                  => 'required|numeric|greater_than[0]',
        'precio_m2'             => 'required|numeric|greater_than_equal_to[0]',
        'frente'                => 'permit_empty|numeric|greater_than[0]',
        'fondo'                 => 'permit_empty|numeric|greater_than[0]',
        'lateral_izquierdo'     => 'permit_empty|numeric|greater_than[0]',
        'lateral_derecho'       => 'permit_empty|numeric|greater_than[0]',
        'construccion'          => 'permit_empty|numeric|greater_than_equal_to[0]',
        'longitud'              => 'permit_empty|numeric',
        'latitud'               => 'permit_empty|numeric',
        'coordenadas'           => 'permit_empty|string',
        'color'                 => 'permit_empty|regex_match[/^#[0-9a-fA-F]{6}$/]',
        'activo'                => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'numero' => [
            'required' => 'El número del lote es obligatorio',
            'max_length' => 'El número del lote no debe exceder {param} caracteres'
        ],
        'empresas_id' => [
            'required' => 'Debe seleccionar una empresa',
            'integer' => 'La empresa debe ser un número válido',
            'is_not_unique' => 'La empresa seleccionada no existe'
        ],
        'proyectos_id' => [
            'required' => 'Debe seleccionar un proyecto',
            'integer' => 'El proyecto debe ser un número válido',
            'is_not_unique' => 'El proyecto seleccionado no existe'
        ],
        'manzanas_id' => [
            'required' => 'Debe seleccionar una manzana',
            'integer' => 'La manzana debe ser un número válido',
            'is_not_unique' => 'La manzana seleccionada no existe'
        ],
        'area' => [
            'required' => 'El área es obligatoria',
            'numeric' => 'El área debe ser un número válido',
            'greater_than' => 'El área debe ser mayor a 0'
        ],
        'precio_m2' => [
            'required' => 'El precio por m² es obligatorio',
            'numeric' => 'El precio por m² debe ser un número válido',
            'greater_than_equal_to' => 'El precio por m² debe ser mayor o igual a 0'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['validarEdicionPermitida'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = ['validarEliminacionPermitida'];
    protected $afterDelete    = [];

    /**
     * Obtener lotes con filtros avanzados
     */
    public function getLotesConFiltros(array $filtros = []): array
    {
        $builder = $this->builder();
        
        // Joins para obtener información relacionada con aliases legacy
        $builder->select('lotes.*, 
                         lotes.numero as lote,
                         lotes.area as area_total,
                         lotes.precio_m2 as precio_metro,
                         lotes.lateral_izquierdo as lateral_izquierda,
                         lotes.lateral_derecho as lateral_derecha,
                         lotes.precio_total as anticipo,
                         empresas.id as proyecto_empresa_id,
                         empresas.nombre as empresa_nombre,
                         proyectos.nombre as proyecto_nombre,
                         proyectos.clave as proyecto_clave,
                         divisiones.nombre as division_nombre,
                         divisiones.clave as division_clave,
                         manzanas.nombre as manzana_nombre,
                         categorias_lotes.nombre as categoria_nombre,
                         tipos_lotes.nombre as tipo_lote_nombre,
                         estados_lotes.nombre as estado_nombre')
                ->join('empresas', 'empresas.id = lotes.empresas_id')
                ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
                ->join('divisiones', 'divisiones.id = lotes.divisiones_id')
                ->join('manzanas', 'manzanas.id = lotes.manzanas_id')
                ->join('categorias_lotes', 'categorias_lotes.id = lotes.categorias_lotes_id', 'left')
                ->join('tipos_lotes', 'tipos_lotes.id = lotes.tipos_lotes_id', 'left')
                ->join('estados_lotes', 'estados_lotes.id = lotes.estados_lotes_id', 'left');

        // Aplicar filtros
        if (!empty($filtros['empresas_id'])) {
            $builder->where('lotes.empresas_id', $filtros['empresas_id']);
        }

        if (!empty($filtros['proyectos_id'])) {
            $builder->where('lotes.proyectos_id', $filtros['proyectos_id']);
        }

        if (!empty($filtros['manzanas_id'])) {
            $builder->where('lotes.manzanas_id', $filtros['manzanas_id']);
        }

        if (!empty($filtros['estados_lotes_id'])) {
            // Si viene como nombre del estado, filtrar por nombre, si no por código
            if (is_numeric($filtros['estados_lotes_id'])) {
                $builder->where('estados_lotes.id', $filtros['estados_lotes_id']);
            } else {
                $builder->where('estados_lotes.nombre', $filtros['estados_lotes_id']);
            }
        }

        if (!empty($filtros['categorias_lotes_id'])) {
            $builder->where('lotes.categorias_lotes_id', $filtros['categorias_lotes_id']);
        }

        if (!empty($filtros['tipos_lotes_id'])) {
            $builder->where('lotes.tipos_lotes_id', $filtros['tipos_lotes_id']);
        }

        if (!empty($filtros['area_min'])) {
            $builder->where('lotes.area >=', $filtros['area_min']);
        }

        if (!empty($filtros['area_max'])) {
            $builder->where('lotes.area <=', $filtros['area_max']);
        }

        if (!empty($filtros['precio_min'])) {
            $builder->where('lotes.precio_total >=', $filtros['precio_min']);
        }

        if (!empty($filtros['precio_max'])) {
            $builder->where('lotes.precio_total <=', $filtros['precio_max']);
        }

        if (!empty($filtros['buscar'])) {
            $builder->groupStart()
                    ->like('lotes.numero', $filtros['buscar'])
                    ->orLike('lotes.clave', $filtros['buscar'])
                    ->orLike('lotes.descripcion', $filtros['buscar'])
                    ->groupEnd();
        }

        // Solo aplicar filtro de activo si se proporciona explícitamente
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $builder->where('lotes.activo', $filtros['activo']);
        }
        // Si no se especifica filtro, mostrar todos los lotes (activos e inactivos)

        // NOTA: Filtro de ventas eliminado - módulo de ventas fue removido del sistema
        // Todos los lotes están disponibles independientemente de ventas previas

        // Ordenamiento
        $ordenamiento = $filtros['ordenar'] ?? 'lotes.created_at';
        $direccion = $filtros['direccion'] ?? 'DESC';
        $builder->orderBy($ordenamiento, $direccion);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener estadísticas de lotes por proyecto
     */
    public function getEstadisticasPorProyecto(int $proyectoId): array
    {
        $builder = $this->builder();
        
        $estadisticas = $builder->select('
                COUNT(*) as total_lotes,
                SUM(CASE WHEN el.codigo = 0 THEN 1 ELSE 0 END) as disponibles,
                SUM(CASE WHEN el.codigo = 1 THEN 1 ELSE 0 END) as apartados,
                SUM(CASE WHEN el.codigo = 2 THEN 1 ELSE 0 END) as vendidos,
                SUM(CASE WHEN el.codigo = 3 THEN 1 ELSE 0 END) as suspendidos,
                SUM(lotes.area) as area_total,
                AVG(lotes.area) as area_promedio,
                MIN(lotes.precio_total) as precio_min,
                MAX(lotes.precio_total) as precio_max,
                AVG(lotes.precio_total) as precio_promedio,
                SUM(lotes.precio_total) as valor_total_inventario
            ')
            ->join('estados_lotes el', 'lotes.estados_lotes_id = el.id', 'left')
            ->where('proyectos_id', $proyectoId)
            ->where('activo', true)
            ->get()
            ->getRowArray();

        return $estadisticas ?: [
            'total_lotes' => 0,
            'disponibles' => 0,
            'apartados' => 0,
            'vendidos' => 0,
            'suspendidos' => 0,
            'area_total' => 0,
            'area_promedio' => 0,
            'precio_min' => 0,
            'precio_max' => 0,
            'precio_promedio' => 0,
            'valor_total_inventario' => 0
        ];
    }

    /**
     * Obtener lotes disponibles para venta
     */
    public function getLotesDisponibles(?int $proyectoId = null): array
    {
        $builder = $this->select('lotes.*, 
                                 empresas.nombre as empresa_nombre,
                                 proyectos.nombre as proyecto_nombre, 
                                 tipos_lotes.nombre as tipo_nombre,
                                 divisiones.nombre as division_nombre,
                                 manzanas.nombre as manzana_nombre, 
                                 manzanas.clave as manzana_clave,
                                 categorias_lotes.nombre as categoria_nombre,
                                 estados_lotes.nombre as estado_nombre')
                      ->join('manzanas', 'lotes.manzanas_id = manzanas.id', 'left')
                      ->join('proyectos', 'manzanas.proyectos_id = proyectos.id', 'left')
                      ->join('empresas', 'proyectos.empresas_id = empresas.id', 'left')
                      ->join('divisiones', 'lotes.divisiones_id = divisiones.id', 'left')
                      ->join('tipos_lotes', 'lotes.tipos_lotes_id = tipos_lotes.id', 'left')
                      ->join('categorias_lotes', 'lotes.categorias_lotes_id = categorias_lotes.id', 'left')
                      ->join('estados_lotes', 'lotes.estados_lotes_id = estados_lotes.id', 'left')
                      ->where('estados_lotes.id', self::ESTADO_DISPONIBLE)
                      ->where('lotes.activo', true)
                      ->orderBy('proyectos.nombre, lotes.clave');

        if ($proyectoId) {
            $builder->where('lotes.proyectos_id', $proyectoId);
        }

        return $builder->findAll();
    }

    /**
     * Cambiar estado de un lote
     */
    public function cambiarEstado(int $loteId, int $nuevoEstado): bool
    {
        $lote = $this->find($loteId);
        
        if (!$lote) {
            throw new \Exception('Lote no encontrado');
        }

        // Validar que el cambio de estado es permitido
        if (!$this->esCambioEstadoValido($lote->estados_lotes_id, $nuevoEstado)) {
            throw new \Exception('Cambio de estado no permitido');
        }

        return $this->update($loteId, ['estados_lotes_id' => $nuevoEstado]);
    }

    /**
     * Validar si un cambio de estado es válido
     */
    private function esCambioEstadoValido(int $estadoActual, int $nuevoEstado): bool
    {
        // Reglas de negocio más permisivas para administradores
        $transicionesPermitidas = [
            self::ESTADO_DISPONIBLE => [self::ESTADO_APARTADO, self::ESTADO_VENDIDO, self::ESTADO_SUSPENDIDO],
            self::ESTADO_APARTADO   => [self::ESTADO_VENDIDO, self::ESTADO_DISPONIBLE, self::ESTADO_SUSPENDIDO],
            self::ESTADO_VENDIDO    => [self::ESTADO_APARTADO, self::ESTADO_DISPONIBLE, self::ESTADO_SUSPENDIDO],
            self::ESTADO_SUSPENDIDO => [self::ESTADO_DISPONIBLE, self::ESTADO_APARTADO, self::ESTADO_VENDIDO]
        ];

        return isset($transicionesPermitidas[$estadoActual]) && 
               in_array($nuevoEstado, $transicionesPermitidas[$estadoActual]);
    }

    /**
     * Obtener lotes por manzana
     */
    public function getLotesPorManzana(int $manzanaId): array
    {
        return $this->where('manzanas_id', $manzanaId)
                   ->where('activo', true)
                   ->orderBy('numero', 'ASC')
                   ->findAll();
    }

    /**
     * Calcular precio total automáticamente
     */
    public function calcularPrecioTotal(float $area, float $precioM2): float
    {
        return $area * $precioM2;
    }

    /**
     * Obtener lotes con amenidades
     */
    public function getLotesConAmenidades(int $proyectoId = null): array
    {
        $builder = $this->db->table('lotes l');
        $builder->select('l.*, 
                         GROUP_CONCAT(a.nombre) as amenidades_nombres,
                         GROUP_CONCAT(a.clase) as amenidades_clases')
                ->join('lotes_amenidades la', 'la.lotes_id = l.id', 'left')
                ->join('amenidades a', 'a.id = la.amenidades_id', 'left')
                ->where('l.activo', true)
                ->groupBy('l.id');

        if ($proyectoId) {
            $builder->where('l.proyectos_id', $proyectoId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Validar coordenadas GPS
     */
    public function validarCoordenadas(?string $longitud, ?string $latitud): bool
    {
        if (!$longitud || !$latitud) {
            return true; // Coordenadas opcionales
        }

        return is_numeric($longitud) && $longitud >= -180 && $longitud <= 180 &&
               is_numeric($latitud) && $latitud >= -90 && $latitud <= 90;
    }

    /**
     * Buscar lotes similares (para recomendaciones)
     */
    public function getLotesSimilares(int $loteId, int $limite = 5): array
    {
        $lote = $this->find($loteId);
        
        if (!$lote) {
            return [];
        }

        $builder = $this->builder();
        $builder->where('id !=', $loteId)
                ->where('proyectos_id', $lote->proyectos_id)
                ->where('categorias_lotes_id', $lote->categorias_lotes_id)
                ->where('activo', true)
                ->where('area >=', $lote->area * 0.8)
                ->where('area <=', $lote->area * 1.2) // ±20% área
                ->orderBy('ABS(area - ' . $lote->area . ')', 'ASC')
                ->limit($limite);

        return $builder->get()->getResultArray();
    }

    /**
     * Generar clave automática para el lote siguiendo el patrón: PROYECTO-DIVISION-MANZANA-NUMERO
     * Estructura completa: proyectos.clave + divisiones.clave + manzanas.nombre + lotes.numero
     */
    public function generarClave(int $empresaId, int $proyectoId, int $divisionId, int $manzanaId, string $numero): string
    {
        try {
            // Obtener clave del proyecto
            $proyectoModel = model('ProyectoModel');
            $proyecto = $proyectoModel->find($proyectoId);
            $proyectoClave = $proyecto && !empty($proyecto->clave) ? $proyecto->clave : 'PRY' . $proyectoId;

            // Obtener clave de la división
            $divisionModel = model('DivisionModel');
            $division = $divisionModel->find($divisionId);
            $divisionClave = $division && !empty($division->clave) ? $division->clave : 'DIV' . $divisionId;

            // Obtener nombre de la manzana 
            $manzanaModel = model('ManzanaModel');
            $manzana = $manzanaModel->find($manzanaId);
            $manzanaNombre = $manzana && !empty($manzana->nombre) ? $manzana->nombre : 'MZ' . $manzanaId;

            // Generar clave completa: PROYECTO-DIVISION-MANZANA-NUMERO
            $clave = $proyectoClave . '-' . $divisionClave . '-' . $manzanaNombre . '-' . $numero;
            
            log_message('debug', "Clave generada: {$clave} (Proyecto: {$proyectoClave}, División: {$divisionClave}, Manzana: {$manzanaNombre}, Número: {$numero})");
            
            return strtoupper($clave);
            
        } catch (\Exception $e) {
            // Fallback si hay error
            log_message('error', 'Error generando clave de lote: ' . $e->getMessage());
            return 'L' . $proyectoId . '-D' . $divisionId . '-M' . $manzanaId . '-' . $numero;
        }
    }

    /**
     * Validar que la clave generada sea única
     */
    public function validarClaveUnica(string $clave, ?int $exceptoId = null): bool
    {
        $builder = $this->builder();
        $builder->where('clave', $clave)
                ->where('activo', true);

        if ($exceptoId) {
            $builder->where('id !=', $exceptoId);
        }

        return $builder->countAllResults() === 0;
    }

    /**
     * Regenerar claves existentes con la nueva nomenclatura
     * PROYECTO-DIVISION-MANZANA-NUMERO
     */
    public function regenerarClavesExistentes(): array
    {
        $resultados = [
            'total' => 0,
            'actualizados' => 0,
            'errores' => 0,
            'detalles' => []
        ];

        try {
            // Obtener todos los lotes activos con sus relaciones
            $lotes = $this->select('lotes.*, p.clave as proyecto_clave, d.clave as division_clave, m.nombre as manzana_nombre')
                          ->join('proyectos p', 'p.id = lotes.proyectos_id', 'left')
                          ->join('divisiones d', 'd.id = lotes.divisiones_id', 'left')
                          ->join('manzanas m', 'm.id = lotes.manzanas_id', 'left')
                          ->where('lotes.activo', true)
                          ->findAll();

            $resultados['total'] = count($lotes);

            foreach ($lotes as $lote) {
                try {
                    // Validar que tenemos todos los datos necesarios
                    if (!$lote->empresas_id || !$lote->proyectos_id || !$lote->divisiones_id || !$lote->manzanas_id || !$lote->numero) {
                        $resultados['errores']++;
                        $resultados['detalles'][] = "Lote ID {$lote->id}: Faltan datos requeridos";
                        continue;
                    }

                    // Generar nueva clave
                    $nuevaClave = $this->generarClave(
                        $lote->empresas_id,
                        $lote->proyectos_id, 
                        $lote->divisiones_id,
                        $lote->manzanas_id,
                        $lote->numero
                    );

                    // Solo actualizar si la clave cambió
                    if ($lote->clave !== $nuevaClave) {
                        $actualizado = $this->update($lote->id, ['clave' => $nuevaClave]);
                        
                        if ($actualizado) {
                            $resultados['actualizados']++;
                            $resultados['detalles'][] = "Lote ID {$lote->id}: {$lote->clave} → {$nuevaClave}";
                            log_message('info', "Clave actualizada - Lote ID {$lote->id}: {$lote->clave} → {$nuevaClave}");
                        } else {
                            $resultados['errores']++;
                            $resultados['detalles'][] = "Lote ID {$lote->id}: Error al actualizar en BD";
                        }
                    }

                } catch (\Exception $e) {
                    $resultados['errores']++;
                    $resultados['detalles'][] = "Lote ID {$lote->id}: " . $e->getMessage();
                    log_message('error', "Error regenerando clave lote {$lote->id}: " . $e->getMessage());
                }
            }

            log_message('info', "Regeneración de claves completada. Total: {$resultados['total']}, Actualizados: {$resultados['actualizados']}, Errores: {$resultados['errores']}");

        } catch (\Exception $e) {
            $resultados['errores']++;
            $resultados['detalles'][] = "Error general: " . $e->getMessage();
            log_message('error', "Error en regenerarClavesExistentes: " . $e->getMessage());
        }

        return $resultados;
    }

    /**
     * Obtener el siguiente número disponible para una manzana específica
     */
    public function getSiguienteNumero(int $manzanaId): int
    {
        $ultimoNumero = $this->where('manzanas_id', $manzanaId)
                            ->where('activo', true)
                            ->selectMax('numero')
                            ->first();

        return intval($ultimoNumero['numero'] ?? 0) + 1;
    }
    
    /**
     * ================================================================
     * MÉTODOS DE PROTECCIÓN CONTRA SOBREVENTA
     * ================================================================
     */
    
    /**
     * Validar que el lote puede ser editado
     * NOTA: Restricciones de ventas eliminadas - módulo deshabilitado
     */
    protected function validarEdicionPermitida(array $data): array
    {
        if (!isset($data['id']) || empty($data['id'])) {
            return $data; // Es una inserción, permitir
        }
        
        $loteId = is_array($data['id']) ? $data['id'][0] : $data['id'];
        $lote = $this->find($loteId);
        
        if (!$lote) {
            return $data; // Lote no encontrado, dejar que el sistema maneje el error
        }
        
        // Si solo se está cambiando el estado (campos específicos del sistema), permitir
        $camposPermitidosParaCambioEstado = ['estados_lotes_id', 'updated_at'];
        if (isset($data['data']) && count(array_diff_key($data['data'], array_flip($camposPermitidosParaCambioEstado))) === 0) {
            log_message('debug', "🔄 Permitiendo cambio de estado para lote {$loteId}");
            return $data; // Solo cambio de estado, permitir
        }
        
        // Para ediciones de otros campos, aplicar validaciones más estrictas
        $codigoEstadoActual = $this->getCodigoEstadoPorId($lote->estados_lotes_id);
        $estadosQueBloqueanEdicion = [
            self::ESTADO_APARTADO,  // 1
            self::ESTADO_VENDIDO    // 2
        ];
        
        // Si el lote está en un estado que bloquea la edición de campos generales
        if (in_array($codigoEstadoActual, $estadosQueBloqueanEdicion)) {
            throw new \RuntimeException(
                "⚠️ PROTECCIÓN CONTRA SOBREVENTA: No se puede editar el lote ID {$loteId} " .
                "porque está en estado " . $this->getNombreEstado($lote->estados_lotes_id) . ". " .
                "Solo se permiten cambios de estado a través del administrador."
            );
        }
        
        // NOTA: Verificación de inconsistencia de ventas eliminada - módulo de ventas deshabilitado
        
        return $data;
    }
    
    /**
     * Obtener nombre del estado por ID
     */
    private function getNombreEstado(int $estadoId): string
    {
        static $estadosCache = [];
        
        if (!isset($estadosCache[$estadoId])) {
            $estadoModel = model('EstadoLoteModel');
            $estado = $estadoModel->find($estadoId);
            $estadosCache[$estadoId] = $estado ? strtoupper($estado->nombre) : 'DESCONOCIDO';
        }
        
        return $estadosCache[$estadoId];
    }
    
    /**
     * Obtener código del estado por ID
     */
    private function getCodigoEstadoPorId(int $estadoId): int
    {
        static $codigosCache = [];
        
        if (!isset($codigosCache[$estadoId])) {
            $estadoModel = model('EstadoLoteModel');
            $estado = $estadoModel->find($estadoId);
            $codigosCache[$estadoId] = $estado ? $estado->codigo : -1;
        }
        
        return $codigosCache[$estadoId];
    }
    
    /**
     * Validar que el lote puede ser eliminado
     * NOTA: Restricciones de ventas eliminadas - módulo deshabilitado
     */
    protected function validarEliminacionPermitida(array $data): array
    {
        if (!isset($data['id']) || empty($data['id'])) {
            return $data;
        }
        
        $loteId = is_array($data['id']) ? $data['id'][0] : $data['id'];
        
        // NOTA: Verificación de ventas activas eliminada - módulo de ventas deshabilitado
        // Los lotes pueden eliminarse sin restricciones de ventas
        
        return $data;
    }
    
    /**
     * Verificar si el lote tiene ventas activas (no canceladas)
     * NOTA: Módulo de ventas eliminado - siempre retorna false
     */
    public function tieneVentasActivas(int $loteId): bool
    {
        // Módulo de ventas eliminado - no hay ventas activas
        return false;
    }
    
    /**
     * Obtener ventas activas de un lote
     * NOTA: Módulo de ventas eliminado - siempre retorna array vacío
     */
    public function getVentasActivas(int $loteId): array
    {
        // Módulo de ventas eliminado - no hay ventas activas
        return [];
    }
    
    /**
     * Verificar disponibilidad del lote para venta
     */
    public function puedeVenderse(int $loteId): array
    {
        $lote = $this->find($loteId);
        
        if (!$lote) {
            return [
                'puede_venderse' => false,
                'razon' => 'Lote no encontrado'
            ];
        }
        
        // Estado del lote debe ser DISPONIBLE
        if ($lote->getCodigoEstado() !== self::ESTADO_DISPONIBLE) {
            return [
                'puede_venderse' => false,
                'razon' => 'Lote no está disponible (Código estado: ' . $lote->getCodigoEstado() . ')'
            ];
        }
        
        // NOTA: Verificación de ventas activas eliminada - módulo de ventas deshabilitado
        // Todos los lotes están disponibles para venta (solo validando estado)
        
        return [
            'puede_venderse' => true,
            'razon' => 'Lote disponible para venta'
        ];
    }

    /**
     * Obtener información completa de un lote para venta
     */
    public function getLoteCompleto($loteId): ?array
    {
        $builder = $this->builder();
        
        $lote = $builder->select('lotes.*, 
                                 lotes.numero as lote,
                                 lotes.area as area_total,
                                 lotes.precio_m2 as precio_metro,
                                 lotes.lateral_izquierdo as lateral_izquierda,
                                 lotes.lateral_derecho as lateral_derecha,
                                 empresas.id as proyecto_empresa_id,
                                 empresas.nombre as empresa_nombre,
                                 proyectos.nombre as proyecto_nombre,
                                 proyectos.clave as proyecto_clave,
                                 divisiones.nombre as division_nombre,
                                 divisiones.clave as division_clave,
                                 manzanas.nombre as manzana_nombre,
                                 categorias_lotes.nombre as categoria_nombre,
                                 tipos_lotes.nombre as tipo_lote_nombre,
                                 estados_lotes.nombre as estado_nombre')
                        ->join('empresas', 'empresas.id = lotes.empresas_id')
                        ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
                        ->join('divisiones', 'divisiones.id = lotes.divisiones_id')
                        ->join('manzanas', 'manzanas.id = lotes.manzanas_id')
                        ->join('categorias_lotes', 'categorias_lotes.id = lotes.categorias_lotes_id')
                        ->join('tipos_lotes', 'tipos_lotes.id = lotes.tipos_lotes_id')
                        ->join('estados_lotes', 'estados_lotes.id = lotes.estados_lotes_id')
                        ->where('lotes.id', $loteId)
                        ->where('lotes.activo', 1)
                        ->get()
                        ->getRowArray();

        return $lote ?: null;
    }

    // ===============================================
    // MÉTODOS PARA FILTRADO POR PROYECTO (REGISTRO LEADS)
    // ===============================================

    /**
     * Obtener lotes por nombre de proyecto específico
     */
    public function getLotesPorProyecto(string $proyectoNombre): array
    {
        return $this->select('lotes.*, 
                             proyectos.nombre as proyecto_nombre,
                             proyectos.clave as proyecto_clave,
                             empresas.nombre as empresa_nombre,
                             manzanas.nombre as manzana_nombre,
                             estados_lotes.nombre as estado_nombre')
                    ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
                    ->join('empresas', 'empresas.id = lotes.empresas_id')
                    ->join('manzanas', 'manzanas.id = lotes.manzanas_id')
                    ->join('estados_lotes', 'estados_lotes.id = lotes.estados_lotes_id')
                    ->where('proyectos.nombre', $proyectoNombre)
                    ->where('estados_lotes.id', self::ESTADO_DISPONIBLE)
                    ->where('lotes.activo', 1)
                    ->where('proyectos.estatus', 'activo')
                    ->orderBy('proyectos.nombre, manzanas.nombre, lotes.numero')
                    ->findAll();
    }

    /**
     * Obtener lotes disponibles por empresa específica
     */
    public function getLotesDisponiblesPorEmpresa(int $empresaId): array
    {
        return $this->select('lotes.*, 
                             proyectos.nombre as proyecto_nombre,
                             proyectos.clave as proyecto_clave,
                             empresas.nombre as empresa_nombre,
                             manzanas.nombre as manzana_nombre,
                             estados_lotes.nombre as estado_nombre')
                    ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
                    ->join('empresas', 'empresas.id = lotes.empresas_id')
                    ->join('manzanas', 'manzanas.id = lotes.manzanas_id')
                    ->join('estados_lotes', 'estados_lotes.id = lotes.estados_lotes_id')
                    ->where('lotes.empresas_id', $empresaId)
                    ->where('estados_lotes.id', self::ESTADO_DISPONIBLE)
                    ->where('lotes.activo', 1)
                    ->where('proyectos.estatus', 'activo')
                    ->orderBy('proyectos.nombre, manzanas.nombre, lotes.numero')
                    ->findAll();
    }

    /**
     * Obtener lotes de ANVAR INMOBILIARIA (V1 + C1 Valle Natura)
     */
    public function getLotesAnvarInmobiliaria(): array
    {
        return $this->select('lotes.*, 
                             proyectos.nombre as proyecto_nombre,
                             proyectos.clave as proyecto_clave,
                             empresas.nombre as empresa_nombre,
                             manzanas.nombre as manzana_nombre,
                             estados_lotes.nombre as estado_nombre')
                    ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
                    ->join('empresas', 'empresas.id = lotes.empresas_id')
                    ->join('manzanas', 'manzanas.id = lotes.manzanas_id')
                    ->join('estados_lotes', 'estados_lotes.id = lotes.estados_lotes_id')
                    ->where('estados_lotes.id', self::ESTADO_DISPONIBLE)
                    ->where('lotes.activo', 1)
                    ->where('proyectos.estatus', 'activo')
                    ->where('proyectos.clave', 'V1')
                    ->orderBy('proyectos.nombre, manzanas.nombre, lotes.numero')
                    ->findAll();
    }

    /**
     * Obtener lotes de CORDELIA
     */
    public function getLotesCordelia(): array
    {
        return $this->select('lotes.*, 
                             proyectos.nombre as proyecto_nombre,
                             proyectos.clave as proyecto_clave,
                             empresas.nombre as empresa_nombre,
                             manzanas.nombre as manzana_nombre,
                             estados_lotes.nombre as estado_nombre')
                    ->join('proyectos', 'proyectos.id = lotes.proyectos_id')
                    ->join('empresas', 'empresas.id = lotes.empresas_id')
                    ->join('manzanas', 'manzanas.id = lotes.manzanas_id')
                    ->join('estados_lotes', 'estados_lotes.id = lotes.estados_lotes_id')
                    ->where('UPPER(proyectos.nombre)', 'CORDELIA')
                    ->where('estados_lotes.id', self::ESTADO_DISPONIBLE)
                    ->where('lotes.activo', 1)
                    ->where('proyectos.estatus', 'activo')
                    ->orderBy('proyectos.nombre, manzanas.nombre, lotes.numero')
                    ->findAll();
    }

    /**
     * Obtener lotes filtrados por desarrollo para formulario público
     */
    public function getLotesPorDesarrollo(string $desarrollo): array
    {
        switch (strtolower($desarrollo)) {
            case 'valle_natura':
            case 'anvar_inmobiliaria':
                return $this->getLotesAnvarInmobiliaria();
                
            case 'cordelia':
                return $this->getLotesCordelia();
                
            default:
                log_message('warning', "Desarrollo no reconocido: {$desarrollo}");
                return [];
        }
    }

    /**
     * Obtener estadísticas de lotes por desarrollo
     */
    public function getEstadisticasPorDesarrollo(string $desarrollo): array
    {
        $lotes = $this->getLotesPorDesarrollo($desarrollo);
        
        $stats = [
            'total_disponibles' => count($lotes),
            'proyectos_activos' => 0,
            'precio_min' => null,
            'precio_max' => null,
            'precio_promedio' => 0,
            'area_min' => null,
            'area_max' => null,
            'area_promedio' => 0
        ];
        
        if (!empty($lotes)) {
            $proyectos = array_unique(array_column($lotes, 'proyecto_nombre'));
            $stats['proyectos_activos'] = count($proyectos);
            
            $precios = array_column($lotes, 'precio_total');
            $areas = array_column($lotes, 'area');
            
            $stats['precio_min'] = min($precios);
            $stats['precio_max'] = max($precios);
            $stats['precio_promedio'] = array_sum($precios) / count($precios);
            
            $stats['area_min'] = min($areas);
            $stats['area_max'] = max($areas);
            $stats['area_promedio'] = array_sum($areas) / count($areas);
        }
        
        return $stats;
    }

    /**
     * Obtener lotes para modal de selección con información completa
     * Usado por el módulo de apartados para seleccionar lotes
     */
    public function getLotesParaModal(array $filtros = []): array
    {
        $builder = $this->builder();
        
        $builder->select('lotes.*, 
                         proyectos.nombre as proyecto_nombre,
                         proyectos.id as proyecto_id,
                         manzanas.nombre as manzana_nombre,
                         manzanas.id as manzana_id,
                         tipos_lotes.nombre as tipo_nombre,
                         categorias_lotes.nombre as categoria_nombre,
                         estados_lotes.nombre as estado_nombre')
                ->join('proyectos', 'proyectos.id = lotes.proyectos_id', 'left')
                ->join('manzanas', 'manzanas.id = lotes.manzanas_id', 'left')
                ->join('tipos_lotes', 'tipos_lotes.id = lotes.tipos_lotes_id', 'left')
                ->join('categorias_lotes', 'categorias_lotes.id = lotes.categorias_lotes_id', 'left')
                ->join('estados_lotes', 'estados_lotes.id = lotes.estados_lotes_id', 'left')
                ->where('lotes.activo', 1);

        // Aplicar filtros
        if (!empty($filtros['proyecto_id'])) {
            $builder->where('lotes.proyectos_id', $filtros['proyecto_id']);
        }

        if (!empty($filtros['estado_id'])) {
            $builder->where('estados_lotes.id', $filtros['estado_id']);
        } else {
            // Por defecto, solo lotes disponibles
            $builder->where('estados_lotes.id', self::ESTADO_DISPONIBLE);
        }

        if (!empty($filtros['search'])) {
            $builder->groupStart()
                    ->like('lotes.clave', $filtros['search'])
                    ->orLike('lotes.numero', $filtros['search'])
                    ->orLike('proyectos.nombre', $filtros['search'])
                    ->orLike('manzanas.nombre', $filtros['search'])
                    ->groupEnd();
        }

        // Ordenamiento
        $builder->orderBy('proyectos.nombre, manzanas.nombre, lotes.numero');

        return $builder->get()->getResult();
    }
}