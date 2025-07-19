<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\PerfilFinanciamiento;

class PerfilFinanciamientoModel extends Model
{
    protected $table            = 'perfiles_financiamiento';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\PerfilFinanciamiento';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'empresa_id',
        'proyecto_id',
        'nombre',
        'descripcion',
        'tipo_anticipo',
        'porcentaje_anticipo',
        'anticipo_fijo',
        'enganche_minimo',
        'apartado_minimo',
        'tipo_comision',
        'porcentaje_comision',
        'comision_fija',
        'tipo_financiamiento',
        'meses_sin_intereses',
        'meses_con_intereses',
        'porcentaje_interes_anual',
        'dias_anticipo',
        'plazo_liquidar_enganche',
        'accion_anticipo_incompleto',
        'penalizacion_apartado',
        'porcentaje_cancelacion',
        'es_default',
        'permite_apartado',
        'aplica_terreno_habitacional',
        'aplica_terreno_comercial',
        'aplica_casa',
        'aplica_departamento',
        'metros_cuadrados_max',
        'superficie_minima_m2',
        'monto_minimo',
        'monto_maximo',
        'fecha_vigencia_inicio',
        'fecha_vigencia_fin',
        'prioridad',
        'promocion_cero_enganche',
        'mensualidades_comision',
        'penalizacion_enganche_tardio',
        'activo',
        'created_by',
        'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'empresa_id' => 'required|is_natural_no_zero',
        'proyecto_id' => 'permit_empty|is_natural',
        'nombre' => 'required|max_length[100]',
        'tipo_anticipo' => 'required|in_list[porcentaje,fijo]',
        'porcentaje_anticipo' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'anticipo_fijo' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'enganche_minimo' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'apartado_minimo' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'tipo_comision' => 'required|in_list[porcentaje,fijo]',
        'porcentaje_comision' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'comision_fija' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'tipo_financiamiento' => 'permit_empty|in_list[msi,mci]',
        'meses_sin_intereses' => 'permit_empty|is_natural',
        'meses_con_intereses' => 'permit_empty|is_natural',
        'porcentaje_interes_anual' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'dias_anticipo' => 'permit_empty|is_natural',
        'plazo_liquidar_enganche' => 'permit_empty|is_natural',
        'accion_anticipo_incompleto' => 'permit_empty|in_list[liberar_lote,mantener_apartado,aplicar_penalizacion]',
        'penalizacion_apartado' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'porcentaje_cancelacion' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'metros_cuadrados_max' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'superficie_minima_m2' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'monto_minimo' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'monto_maximo' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'fecha_vigencia_inicio' => 'permit_empty|valid_date',
        'fecha_vigencia_fin' => 'permit_empty|valid_date',
        'prioridad' => 'permit_empty|is_natural',
        'mensualidades_comision' => 'permit_empty|is_natural|greater_than_equal_to[1]|less_than_equal_to[12]',
        'penalizacion_enganche_tardio' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
    ];

    protected $validationMessages = [
        'empresa_id' => [
            'required' => 'La empresa es requerida',
            'is_natural_no_zero' => 'La empresa debe ser válida'
        ],
        'proyecto_id' => [
            'is_natural_no_zero' => 'El proyecto debe ser válido'
        ],
        'nombre' => [
            'required' => 'El nombre es requerido',
            'max_length' => 'El nombre no puede exceder 100 caracteres'
        ],
        'tipo_anticipo' => [
            'required' => 'El tipo de anticipo es requerido',
            'in_list' => 'El tipo de anticipo debe ser porcentaje o fijo'
        ],
        'porcentaje_anticipo' => [
            'decimal' => 'El porcentaje de anticipo debe ser un número decimal',
            'greater_than_equal_to' => 'El porcentaje de anticipo no puede ser negativo',
            'less_than_equal_to' => 'El porcentaje de anticipo no puede ser mayor a 100'
        ],
        'meses_con_intereses' => [
            'required' => 'Los meses con intereses son requeridos',
            'is_natural' => 'Los meses con intereses deben ser un número natural (puede ser 0)'
        ],
        'superficie_minima_m2' => [
            'decimal' => 'La superficie mínima debe ser un número decimal',
            'greater_than_equal_to' => 'La superficie mínima no puede ser negativa'
        ],
        'monto_minimo' => [
            'decimal' => 'El monto mínimo debe ser un número decimal',
            'greater_than_equal_to' => 'El monto mínimo no puede ser negativo'
        ],
        'monto_maximo' => [
            'decimal' => 'El monto máximo debe ser un número decimal',
            'greater_than_equal_to' => 'El monto máximo no puede ser negativo'
        ],
        'fecha_vigencia_inicio' => [
            'valid_date' => 'La fecha de inicio de vigencia debe ser una fecha válida'
        ],
        'fecha_vigencia_fin' => [
            'valid_date' => 'La fecha de fin de vigencia debe ser una fecha válida'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setCreatedBy'];
    protected $beforeUpdate   = ['setUpdatedBy'];

    /**
     * Establece el usuario que crea el registro
     */
    protected function setCreatedBy(array $data)
    {
        if (auth()->loggedIn()) {
            $data['data']['created_by'] = auth()->id();
        }
        return $data;
    }

    /**
     * Establece el usuario que actualiza el registro
     */
    protected function setUpdatedBy(array $data)
    {
        if (auth()->loggedIn()) {
            $data['data']['updated_by'] = auth()->id();
        }
        return $data;
    }


    /**
     * Obtiene configuraciones por empresa
     */
    public function getByEmpresa(int $empresaId, bool $soloActivas = true): array
    {
        $builder = $this->select('perfiles_financiamiento.*, empresas.nombre as empresa_nombre')
                        ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id')
                        ->where('perfiles_financiamiento.empresa_id', $empresaId);

        if ($soloActivas) {
            $builder->where('perfiles_financiamiento.activo', 1);
        }

        return $builder->orderBy('perfiles_financiamiento.es_default', 'DESC')
                       ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                       ->findAll();
    }

    /**
     * Obtiene la configuración por defecto de una empresa
     */
    public function getDefaultByEmpresa(int $empresaId)
    {
        return $this->where('empresa_id', $empresaId)
                    ->where('es_default', 1)
                    ->where('activo', 1)
                    ->first();
    }

    /**
     * Obtiene configuraciones para un proyecto específico
     */
    public function getByProyecto(int $proyectoId): array
    {
        return $this->select('perfiles_financiamiento.*, empresas.nombre as empresa_nombre')
                    ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id')
                    ->where('perfiles_financiamiento.proyecto_id', $proyectoId)
                    ->where('perfiles_financiamiento.activo', 1)
                    ->orderBy('perfiles_financiamiento.es_default', 'DESC')
                    ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener configuraciones financieras para módulo de ventas
     * Agrupadas por empresa con información completa
     */
    public function getConfiguracionesParaVentas(): array
    {
        $builder = $this->select('
            perfiles_financiamiento.*,
            empresas.nombre as empresa_nombre,
            proyectos.nombre as proyecto_nombre
        ')
        ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id')
        ->join('proyectos', 'proyectos.id = perfiles_financiamiento.proyecto_id', 'left')
        ->where('perfiles_financiamiento.activo', 1)
        ->where('empresas.activo', 1)
        ->orderBy('empresas.nombre', 'ASC')
        ->orderBy('perfiles_financiamiento.nombre', 'ASC');

        $results = $builder->get()->getResult();
        
        // Procesar resultados para agregar información útil
        foreach ($results as $result) {
            if (empty($result->proyecto_nombre)) {
                $result->proyecto_nombre = 'Global';
                $result->es_global = true;
            } else {
                $result->es_global = false;
            }
            
            // Agregar descripción resumida
            $result->descripcion_resumida = $this->generarDescripcionResumida($result);
        }

        return $results;
    }

    /**
     * Generar descripción resumida de la configuración
     */
    private function generarDescripcionResumida($config): string
    {
        $descripcion = [];
        
        // Anticipo
        if ($config->tipo_anticipo === 'fijo') {
            $descripcion[] = "Anticipo: $" . number_format($config->anticipo_fijo, 0);
        } else {
            $descripcion[] = "Anticipo: {$config->porcentaje_anticipo}%";
        }
        
        // Comisión
        if ($config->tipo_comision === 'fijo') {
            $descripcion[] = "Comisión: $" . number_format($config->comision_fija, 0);
        } else {
            $descripcion[] = "Comisión: {$config->porcentaje_comision}%";
        }
        
        // Financiamiento
        if ($config->meses_con_intereses > 0) {
            $descripcion[] = "Hasta {$config->meses_con_intereses} meses";
            if ($config->porcentaje_interes_anual > 0) {
                $descripcion[] = "{$config->porcentaje_interes_anual}% anual";
            }
        }
        
        return implode(' | ', $descripcion);
    }

    /**
     * Filtrar planes de financiamiento según características del lote
     */
    public function getPlanesParaLote($loteData, $tipoTerreno = 'habitacional')
    {
        log_message('debug', "FILTRADO PLANES - Lote área: {$loteData->area} m², Tipo: {$tipoTerreno}");
        
        $builder = $this->select('perfiles_financiamiento.*, empresas.nombre as empresa_nombre')
                    ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id', 'left')
                    ->where('perfiles_financiamiento.activo', 1);

        // Filtro por superficie máxima
        if (!empty($loteData->area)) {
            $builder->groupStart()
                   ->where('perfiles_financiamiento.metros_cuadrados_max IS NULL')
                   ->orWhere('perfiles_financiamiento.metros_cuadrados_max >=', $loteData->area)
                   ->groupEnd();
        }

        // Filtro por superficie mínima
        if (!empty($loteData->area)) {
            $builder->groupStart()
                   ->where('perfiles_financiamiento.superficie_minima_m2 IS NULL')
                   ->orWhere('perfiles_financiamiento.superficie_minima_m2 <=', $loteData->area)
                   ->groupEnd();
        }

        // Filtro por monto mínimo y máximo
        if (!empty($loteData->precio_total)) {
            $builder->groupStart()
                   ->where('perfiles_financiamiento.monto_minimo IS NULL')
                   ->orWhere('perfiles_financiamiento.monto_minimo <=', $loteData->precio_total)
                   ->groupEnd();

            $builder->groupStart()
                   ->where('perfiles_financiamiento.monto_maximo IS NULL')
                   ->orWhere('perfiles_financiamiento.monto_maximo >=', $loteData->precio_total)
                   ->groupEnd();
        }

        // Filtro por tipo de terreno
        if ($tipoTerreno === 'comercial') {
            $builder->where('perfiles_financiamiento.aplica_terreno_comercial', 1);
        } else {
            $builder->where('perfiles_financiamiento.aplica_terreno_habitacional', 1);
        }

        // Filtro por fechas de vigencia
        $hoy = date('Y-m-d');
        $builder->groupStart()
               ->where('perfiles_financiamiento.fecha_vigencia_inicio IS NULL')
               ->orWhere('perfiles_financiamiento.fecha_vigencia_inicio <=', $hoy)
               ->groupEnd();

        $builder->groupStart()
               ->where('perfiles_financiamiento.fecha_vigencia_fin IS NULL')
               ->orWhere('perfiles_financiamiento.fecha_vigencia_fin >=', $hoy)
               ->groupEnd();

        $result = $builder->orderBy('perfiles_financiamiento.prioridad', 'DESC')
                         ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                         ->findAll();
        
        log_message('debug', "PLANES FILTRADOS - Total encontrados: " . count($result));
        foreach($result as $plan) {
            log_message('debug', "Plan {$plan->id}: {$plan->nombre}, Max: {$plan->metros_cuadrados_max} m²");
        }
        
        return $result;
    }

    /**
     * Obtiene todas las configuraciones para DataTables
     */
    public function getDatatableData(?int $empresaId = null): array
    {
        $builder = $this->select('
            perfiles_financiamiento.*,
            empresas.nombre as empresa_nombre,
            proyectos.nombre as proyecto_nombre
        ')
        ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id')
        ->join('proyectos', 'proyectos.id = perfiles_financiamiento.proyecto_id', 'left');

        if ($empresaId) {
            $builder->where('perfiles_financiamiento.empresa_id', $empresaId);
        }

        $results = $builder->orderBy('empresas.nombre', 'ASC')
                          ->orderBy('perfiles_financiamiento.prioridad', 'DESC')
                          ->orderBy('perfiles_financiamiento.es_default', 'DESC')
                          ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                          ->findAll();

        // Procesar los resultados para manejar el proyecto_nombre
        foreach ($results as $result) {
            if (empty($result->proyecto_nombre)) {
                $result->proyecto_nombre = 'Global';
            }
        }

        return $results;
    }

    /**
     * Establece una configuración como predeterminada
     */
    public function setAsDefault(int $id): bool
    {
        $config = $this->find($id);
        if (!$config) {
            return false;
        }

        $this->db->transStart();

        // Quitar el default de todas las configuraciones de la empresa
        $this->where('empresa_id', $config->empresa_id)
             ->set(['es_default' => 0])
             ->update();

        // Establecer como default la configuración seleccionada
        $this->where('id', $id)
             ->set(['es_default' => 1])
             ->update();

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Duplica una configuración existente
     */
    public function duplicate(int $id): ?int
    {
        $original = $this->find($id);
        if (!$original) {
            return null;
        }

        $data = $original->toArray();
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        
        // Modificar el nombre para indicar que es una copia
        $data['nombre'] = 'Copia de ' . $data['nombre'];
        $data['es_default'] = 0; // Una copia nunca puede ser default

        return $this->insert($data) ? $this->insertID() : null;
    }

    /**
     * Obtiene estadísticas de uso de configuraciones
     */
    public function getEstadisticasUso(): array
    {
        // TODO: Implementar cuando exista la tabla de ventas
        // Por ahora retornamos datos simulados
        return [
            'total_financiamientos' => $this->countAll(),
            'financiamientos_activos' => $this->where('activo', 1)->countAllResults(false),
            'financiamientos_default' => $this->where('es_default', 1)->countAllResults(false),
            'financiamientos_cero_enganche' => $this->where('tipo_anticipo', 'fijo')
                                                   ->where('anticipo_fijo', 0)
                                                   ->countAllResults(false)
        ];
    }

    /**
     * Busca configuraciones por término
     */
    public function search(string $termino, ?int $empresaId = null): array
    {
        $builder = $this->select('perfiles_financiamiento.*, empresas.nombre as empresa_nombre')
                        ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id')
                        ->groupStart()
                            ->like('perfiles_financiamiento.nombre', $termino)
                            ->orLike('perfiles_financiamiento.descripcion', $termino)
                        ->groupEnd();

        if ($empresaId) {
            $builder->where('perfiles_financiamiento.empresa_id', $empresaId);
        }

        return $builder->orderBy('perfiles_financiamiento.es_default', 'DESC')
                       ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                       ->findAll();
    }

    /**
     * Valida que no haya conflictos al establecer como default
     */
    public function validateDefault(int $id, int $empresaId): bool
    {
        $current = $this->find($id);
        if (!$current || $current->empresa_id != $empresaId) {
            return false;
        }

        return true;
    }

    /**
     * Obtener todas las configuraciones financieras activas
     */
    public function getConfiguracionesActivas()
    {
        $result = $this->select('perfiles_financiamiento.*, empresas.nombre as empresa_nombre')
                    ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id', 'left')
                    ->where('perfiles_financiamiento.activo', 1)
                    ->orderBy('perfiles_financiamiento.prioridad', 'DESC')
                    ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                    ->findAll();
                    
        // DEBUG: Verificar datos desde BD
        echo "<!-- DEBUG MODEL getConfiguracionesActivas -->\n";
        echo "<!-- Total registros encontrados: " . count($result) . " -->\n";
        foreach($result as $i => $config) {
            echo "<!-- Config[$i]: ID={$config->id}, Nombre={$config->nombre}, Activo={$config->activo}, Empresa={$config->empresa_id} -->\n";
        }
        echo "<!-- FIN DEBUG MODEL -->\n";
        
        return $result;
    }

    /**
     * Obtener configuraciones financieras para apartados (excluye planes cero enganche)
     */
    public function getConfiguracionesParaApartados()
    {
        $results = $this->select('perfiles_financiamiento.*, empresas.nombre as empresa_nombre')
                    ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id', 'left')
                    ->where('perfiles_financiamiento.activo', 1)
                    ->where('perfiles_financiamiento.permite_apartado', 1) // FILTRO FALTANTE: Solo planes que permiten apartados
                    ->groupStart()
                        // Excluir planes cero enganche - tipo fijo con anticipo = 0
                        ->where('NOT (perfiles_financiamiento.tipo_anticipo = "fijo" AND perfiles_financiamiento.anticipo_fijo = 0)')
                        // Excluir planes cero enganche - tipo porcentaje con anticipo = 0
                        ->where('NOT (perfiles_financiamiento.tipo_anticipo = "porcentaje" AND perfiles_financiamiento.porcentaje_anticipo = 0)')
                        // Excluir promociones cero enganche
                        ->where('perfiles_financiamiento.promocion_cero_enganche !=', 1)
                    ->groupEnd()
                    ->orderBy('perfiles_financiamiento.prioridad', 'DESC')
                    ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                    ->findAll();
        
        return $results;
    }
    
    /**
     * Obtener configuraciones financieras filtradas para apartados según el lote
     */
    public function getConfiguracionesParaApartadosPorLote($loteId)
    {
        // Primero obtener la información del lote
        $loteModel = new \App\Models\LoteModel();
        $lote = $loteModel->select('lotes.*, tipos_lotes.nombre as tipo_nombre, proyectos.empresas_id')
                          ->join('tipos_lotes', 'tipos_lotes.id = lotes.tipos_lotes_id', 'left')
                          ->join('manzanas', 'manzanas.id = lotes.manzanas_id')
                          ->join('proyectos', 'proyectos.id = manzanas.proyectos_id')
                          ->where('lotes.id', $loteId)
                          ->first();
                          
        if (!$lote) {
            return [];
        }
        
        $builder = $this->select('perfiles_financiamiento.*, empresas.nombre as empresa_nombre')
                        ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id', 'left')
                        ->where('perfiles_financiamiento.activo', 1)
                        ->where('perfiles_financiamiento.empresa_id', $lote->empresas_id);
        
        // Excluir planes cero enganche para apartados
        $builder->groupStart()
                ->where('NOT (perfiles_financiamiento.tipo_anticipo = "fijo" AND perfiles_financiamiento.anticipo_fijo = 0)')
                ->where('NOT (perfiles_financiamiento.tipo_anticipo = "porcentaje" AND perfiles_financiamiento.porcentaje_anticipo = 0)')
                ->where('perfiles_financiamiento.promocion_cero_enganche !=', 1)
                ->groupEnd();
        
        // Filtrar por tipo de terreno
        $tipoLote = strtolower($lote->tipo_nombre ?? '');
        if (strpos($tipoLote, 'comercial') !== false) {
            $builder->where('perfiles_financiamiento.aplica_terreno_comercial', 1);
        } else {
            // Por defecto, los lotes sin especificar son habitacionales
            $builder->where('perfiles_financiamiento.aplica_terreno_habitacional', 1);
        }
        
        // Filtrar por superficie
        if ($lote->area > 0) {
            $builder->groupStart()
                    // Configuraciones con límites definidos deben cumplir el rango
                    ->groupStart()
                        ->groupStart()
                            ->where('perfiles_financiamiento.superficie_minima_m2 IS NOT NULL')
                            ->where('perfiles_financiamiento.superficie_minima_m2 <=', $lote->area)
                        ->groupEnd()
                        ->orWhere('perfiles_financiamiento.superficie_minima_m2 IS NULL')
                    ->groupEnd()
                    ->groupStart()
                        ->groupStart()
                            ->where('perfiles_financiamiento.metros_cuadrados_max IS NOT NULL')
                            ->where('perfiles_financiamiento.metros_cuadrados_max >=', $lote->area)
                        ->groupEnd()
                        ->orWhere('perfiles_financiamiento.metros_cuadrados_max IS NULL')
                    ->groupEnd()
                    ->groupEnd();
        }
        
        // Filtrar por precio
        if ($lote->precio_total > 0) {
            $builder->groupStart()
                    ->groupStart()
                        ->groupStart()
                            ->where('perfiles_financiamiento.monto_minimo IS NOT NULL')
                            ->where('perfiles_financiamiento.monto_minimo <=', $lote->precio_total)
                        ->groupEnd()
                        ->orWhere('perfiles_financiamiento.monto_minimo IS NULL')
                    ->groupEnd()
                    ->groupStart()
                        ->groupStart()
                            ->where('perfiles_financiamiento.monto_maximo IS NOT NULL')
                            ->where('perfiles_financiamiento.monto_maximo >=', $lote->precio_total)
                        ->groupEnd()
                        ->orWhere('perfiles_financiamiento.monto_maximo IS NULL')
                    ->groupEnd()
                    ->groupEnd();
        }
        
        return $builder->orderBy('perfiles_financiamiento.prioridad', 'DESC')
                       ->orderBy('perfiles_financiamiento.nombre', 'ASC')
                       ->findAll();
    }

    /**
     * Obtiene la configuración más apropiada para un lote específico
     */
    public function getConfiguracionParaLote(int $empresaId, ?int $proyectoId, string $tipoTerreno, float $metrosCuadrados): ?PerfilFinanciamiento
    {
        $builder = $this->where('empresa_id', $empresaId)
                        ->where('activo', 1);

        // Filtrar por tipo de terreno
        if ($tipoTerreno === 'habitacional') {
            $builder->where('aplica_terreno_habitacional', 1);
        } elseif ($tipoTerreno === 'comercial') {
            $builder->where('aplica_terreno_comercial', 1);
        }

        // Considerar proyecto específico o global
        if ($proyectoId) {
            $builder->groupStart()
                    ->where('proyecto_id', $proyectoId)
                    ->orWhere('proyecto_id', null)
                    ->groupEnd();
        } else {
            $builder->where('proyecto_id', null);
        }

        // Obtener todas las configuraciones aplicables
        $configuraciones = $builder->orderBy('prioridad', 'DESC')
                                   ->orderBy('es_default', 'DESC')
                                   ->findAll();

        // Filtrar por metros cuadrados y retornar la primera que aplique
        foreach ($configuraciones as $config) {
            if ($config->aplicaParaMetrosCuadrados($metrosCuadrados)) {
                return $config;
            }
        }

        // Si no hay ninguna que aplique, retornar la default de la empresa
        return $this->getDefaultByEmpresa($empresaId);
    }

    /**
     * Obtener configuraciones filtradas por criterios de lote
     * FILTRO INTELIGENTE - NÚCLEO DEL SISTEMA DE VENTAS
     */
    public function getConfiguracionesFiltradas(array $criterios = [], bool $debug = false): array
    {
        if ($debug) {
            log_message('debug', '[FILTRO_CONFIG] Iniciando filtro con criterios: ' . json_encode($criterios));
        }

        $builder = $this->select('
            perfiles_financiamiento.*,
            empresas.nombre as empresa_nombre,
            proyectos.nombre as proyecto_nombre
        ')
        ->join('empresas', 'empresas.id = perfiles_financiamiento.empresa_id')
        ->join('proyectos', 'proyectos.id = perfiles_financiamiento.proyecto_id', 'left')
        ->where('perfiles_financiamiento.activo', 1)
        ->where('empresas.activo', 1);

        // FILTRO 1: TIPO DE TERRENO Y INMUEBLE
        if (!empty($criterios['tipo_terreno'])) {
            $tipoTerreno = $criterios['tipo_terreno'];
            
            if ($debug) {
                log_message('debug', "[FILTRO_CONFIG] Aplicando filtro tipo_terreno: {$tipoTerreno}");
            }
            
            if ($tipoTerreno === 'habitacional') {
                // Para terrenos habitacionales, acepta configuraciones que apliquen a:
                // - Terreno habitacional O Casa O Departamento
                $builder->groupStart()
                    ->where('aplica_terreno_habitacional', 1)
                    ->orWhere('aplica_casa', 1)
                    ->orWhere('aplica_departamento', 1)
                ->groupEnd();
            } elseif ($tipoTerreno === 'comercial') {
                $builder->where('aplica_terreno_comercial', 1);
            }
            // Si es 'todas' no agregamos filtro
        }

        // FILTRO 2: ÁREA EN M2
        if (!empty($criterios['area_m2'])) {
            $area = (float) $criterios['area_m2'];
            
            if ($debug) {
                log_message('debug', "[FILTRO_CONFIG] Aplicando filtro área: {$area} m2");
            }
            
            // Área debe estar entre superficie_minima_m2 y metros_cuadrados_max
            $builder->groupStart()
                ->where('superficie_minima_m2 IS NULL')
                ->orWhere('superficie_minima_m2 <=', $area)
            ->groupEnd()
            ->groupStart()
                ->where('metros_cuadrados_max IS NULL')
                ->orWhere('metros_cuadrados_max >=', $area)
            ->groupEnd();
        }

        // FILTRO 3: RANGO DE PRECIO
        if (!empty($criterios['precio_lote'])) {
            $precio = (float) $criterios['precio_lote'];
            
            if ($debug) {
                log_message('debug', "[FILTRO_CONFIG] Aplicando filtro precio: $" . number_format($precio, 2));
            }
            
            $builder->groupStart()
                ->where('monto_minimo IS NULL')
                ->orWhere('monto_minimo <=', $precio)
            ->groupEnd()
            ->groupStart()
                ->where('monto_maximo IS NULL')
                ->orWhere('monto_maximo >=', $precio)
            ->groupEnd();
        }

        // FILTRO 4: VIGENCIA
        $fechaHoy = date('Y-m-d');
        $builder->groupStart()
            ->where('fecha_vigencia_inicio IS NULL')
            ->orWhere('fecha_vigencia_inicio <=', $fechaHoy)
        ->groupEnd()
        ->groupStart()
            ->where('fecha_vigencia_fin IS NULL')
            ->orWhere('fecha_vigencia_fin >=', $fechaHoy)
        ->groupEnd();

        // FILTRO 5: EMPRESA/PROYECTO ESPECÍFICO
        if (!empty($criterios['empresa_id'])) {
            $builder->where('perfiles_financiamiento.empresa_id', $criterios['empresa_id']);
        }

        if (!empty($criterios['proyecto_id'])) {
            $builder->groupStart()
                ->where('perfiles_financiamiento.proyecto_id', $criterios['proyecto_id'])
                ->orWhere('perfiles_financiamiento.proyecto_id IS NULL') // Incluir configuraciones globales
            ->groupEnd();
        }

        // ORDENAR POR PRIORIDAD Y DEFAULT
        $builder->orderBy('perfiles_financiamiento.es_default', 'DESC')
                ->orderBy('perfiles_financiamiento.prioridad', 'DESC')
                ->orderBy('empresas.nombre', 'ASC')
                ->orderBy('perfiles_financiamiento.nombre', 'ASC');

        $results = $builder->get()->getResult();

        if ($debug) {
            log_message('debug', "[FILTRO_CONFIG] Encontradas " . count($results) . " configuraciones");
            foreach ($results as $config) {
                log_message('debug', "[FILTRO_CONFIG] - {$config->nombre} (ID: {$config->id})");
            }
        }

        // Procesar resultados para agregar información útil
        foreach ($results as $result) {
            if (empty($result->proyecto_nombre)) {
                $result->proyecto_nombre = 'Global';
                $result->es_global = true;
            } else {
                $result->es_global = false;
            }
            
            // Agregar descripción resumida
            $result->descripcion_resumida = $this->generarDescripcionResumida($result);
            
            // Agregar información de compatibilidad
            $result->motivo_compatibilidad = $this->generarMotivoCompatibilidad($result, $criterios);
        }

        return $results;
    }

    /**
     * Obtener configuraciones compatibles con un lote específico
     */
    public function getConfiguracionesParaLote(int $loteId, bool $debug = false): array
    {
        // Obtener información completa del lote
        $loteModel = new \App\Models\LoteModel();
        $lote = $loteModel->select('
            lotes.*,
            tipos_lotes.nombre as tipo_lote_nombre,
            categorias_lotes.nombre as categoria_lote_nombre,
            proyectos.id as proyecto_id,
            proyectos.empresas_id as empresa_id
        ')
        ->join('tipos_lotes', 'lotes.tipos_lotes_id = tipos_lotes.id', 'left')
        ->join('categorias_lotes', 'lotes.categorias_lotes_id = categorias_lotes.id', 'left')
        ->join('manzanas', 'lotes.manzanas_id = manzanas.id', 'left')
        ->join('proyectos', 'manzanas.proyectos_id = proyectos.id', 'left')
        ->where('lotes.id', $loteId)
        ->first();

        if (!$lote) {
            if ($debug) {
                log_message('error', "[FILTRO_CONFIG] Lote ID {$loteId} no encontrado");
            }
            return [];
        }

        // Determinar tipo de terreno
        $tipoTerreno = 'habitacional'; // Default
        if (!empty($lote->tipo_lote_nombre)) {
            $tipoLoteNombre = strtolower($lote->tipo_lote_nombre);
            if (strpos($tipoLoteNombre, 'comercial') !== false) {
                $tipoTerreno = 'comercial';
            }
        }
        if (!empty($lote->categoria_lote_nombre) && strtolower($lote->categoria_lote_nombre) === 'comercial') {
            $tipoTerreno = 'comercial';
        }

        $criterios = [
            'tipo_terreno' => $tipoTerreno,
            'area_m2' => $lote->area,
            'precio_lote' => $lote->precio_total,
            'empresa_id' => $lote->empresa_id,
            'proyecto_id' => $lote->proyecto_id
        ];

        if ($debug) {
            log_message('debug', "[FILTRO_CONFIG] Lote {$loteId} - Criterios extraídos: " . json_encode($criterios));
            log_message('debug', "[FILTRO_CONFIG] Tipo: {$lote->tipo_lote_nombre}, Categoría: {$lote->categoria_lote_nombre}");
        }
        
        $configuraciones = $this->getConfiguracionesFiltradas($criterios, $debug);
        
        if ($debug) {
            log_message('debug', "[FILTRO_CONFIG] Total configuraciones encontradas para lote {$loteId}: " . count($configuraciones));
            foreach ($configuraciones as $config) {
                log_message('debug', "[FILTRO_CONFIG] - Config ID: {$config->id}, Nombre: {$config->nombre}");
            }
        }

        return $configuraciones;
    }

    /**
     * Generar motivo de compatibilidad para debug
     */
    private function generarMotivoCompatibilidad($config, $criterios): string
    {
        $motivos = [];
        
        if (!empty($criterios['tipo_terreno'])) {
            if ($criterios['tipo_terreno'] === 'habitacional') {
                if ($config->aplica_terreno_habitacional) {
                    $motivos[] = "✓ Aplica terreno habitacional";
                } elseif ($config->aplica_casa) {
                    $motivos[] = "✓ Aplica para casas";
                } elseif ($config->aplica_departamento) {
                    $motivos[] = "✓ Aplica para departamentos";
                }
            } elseif ($criterios['tipo_terreno'] === 'comercial' && $config->aplica_terreno_comercial) {
                $motivos[] = "✓ Aplica terreno comercial";
            }
        }
        
        if (!empty($criterios['area_m2'])) {
            $area = $criterios['area_m2'];
            $minOk = is_null($config->superficie_minima_m2) || $config->superficie_minima_m2 <= $area;
            $maxOk = is_null($config->metros_cuadrados_max) || $config->metros_cuadrados_max >= $area;
            if ($minOk && $maxOk) {
                $motivos[] = "✓ Área compatible ({$area} m²)";
            }
        }
        
        if ($config->es_default) {
            $motivos[] = "⭐ Configuración por defecto";
        }
        
        return implode(', ', $motivos);
    }
}