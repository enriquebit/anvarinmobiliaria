<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PerfilFinanciamientoModel;
use App\Models\EmpresaModel;
use App\Models\ProyectoModel;
use App\Models\VentaModel;
use App\Models\ApartadoModel;

class AdminFinanciamientoController extends BaseController
{
    protected $perfilModel;
    protected $empresaModel;
    protected $proyectoModel;
    protected $ventaModel;
    protected $apartadoModel;

    public function __construct()
    {
        $this->perfilModel = new PerfilFinanciamientoModel();
        $this->empresaModel = new EmpresaModel();
        $this->proyectoModel = new ProyectoModel();
        $this->ventaModel = new VentaModel();
        $this->apartadoModel = new ApartadoModel();
    }

    /**
     * Página principal - listado de configuraciones
     */
    public function index()
    {
        $data = [
            'titulo' => 'Financiamientos',
            'empresas' => $this->empresaModel->where('activo', 1)->findAll(),
            'estadisticas' => $this->perfilModel->getEstadisticasUso()
        ];

        return view('admin/financiamiento/index', $data);
    }

    /**
     * Formulario para crear nueva configuración
     */
    public function create()
    {
        $data = [
            'titulo' => 'Nuevo Financiamiento',
            'financiamiento' => null,
            'empresas' => $this->empresaModel->where('activo', 1)->findAll(),
            'proyectos' => $this->proyectoModel->getProyectosConEmpresa()
        ];

        return view('admin/financiamiento/form', $data);
    }

    /**
     * Procesar creación de nueva configuración
     */
    public function store()
    {
        $data = $this->request->getPost();
        
        // Limpiar campos vacíos o nulos ANTES de la validación
        $data = $this->cleanData($data);
        
        if (!$this->validate($this->perfilModel->getValidationRules(), $data)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }
        
        // Validación adicional para meses totales
        $mesesSinIntereses = (int)($data['meses_sin_intereses'] ?? 0);
        $mesesConIntereses = (int)($data['meses_con_intereses'] ?? 0);
        $totalMeses = $mesesSinIntereses + $mesesConIntereses;
        
        if ($totalMeses <= 0) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Debe especificar al menos un mes de financiamiento (con o sin intereses)');
        }
        
        // Validar que solo haya una configuración default por empresa
        if (isset($data['es_default']) && $data['es_default'] == 1) {
            $this->perfilModel->where('empresa_id', $data['empresa_id'])
                             ->set(['es_default' => 0])
                             ->update();
        }

        // Los datos ya fueron limpiados antes de la validación

        try {
            $result = $this->perfilModel->insert($data);
            log_message('debug', 'Financiamiento::store() - Resultado del insert: ' . ($result ? 'true' : 'false'));
            
            if ($result) {
                log_message('info', 'Financiamiento::store() - Inserción exitosa');
                return redirect()->to('admin/financiamiento')
                               ->with('success', 'Financiamiento creado exitosamente');
            } else {
                $modelErrors = $this->perfilModel->errors();
                log_message('error', 'Financiamiento::store() - Error en modelo: ' . json_encode($modelErrors));
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Error al crear el financiamiento. Errores: ' . json_encode($modelErrors));
            }
        } catch (\Exception $e) {
            log_message('error', 'Financiamiento::store() - Excepción: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalles de una configuración
     */
    public function show($id)
    {
        $financiamiento = $this->perfilModel->find($id);
        
        if (!$financiamiento) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financiamiento no encontrado');
        }

        $data = [
            'titulo' => 'Detalles de Financiamiento',
            'financiamiento' => $financiamiento,
            'simulacion' => $financiamiento->simularVenta(500000) // Simulación ejemplo con $500k
        ];

        return view('admin/financiamiento/show', $data);
    }

    /**
     * Formulario para editar configuración
     */
    public function edit($id)
    {
        $financiamiento = $this->perfilModel->find($id);
        
        if (!$financiamiento) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financiamiento no encontrado');
        }

        $data = [
            'titulo' => 'Editar Financiamiento',
            'financiamiento' => $financiamiento,
            'empresas' => $this->empresaModel->where('activo', 1)->findAll(),
            'proyectos' => $this->proyectoModel->getProyectosConEmpresa()
        ];

        return view('admin/financiamiento/form', $data);
    }

    /**
     * Procesar actualización de configuración
     */
    public function update($id)
    {
        // DEBUG: Log inicio
        log_message('debug', 'Financiamiento::update() - Iniciando actualización para ID: ' . $id);
        
        $financiamiento = $this->perfilModel->find($id);
        
        if (!$financiamiento) {
            log_message('error', 'Financiamiento::update() - Financiamiento no encontrado para ID: ' . $id);
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Financiamiento no encontrado');
        }

        // DEBUG: Log datos POST
        $data = $this->request->getPost();
        log_message('debug', 'Financiamiento::update() - Datos POST recibidos: ' . json_encode($data));
        
        // DEBUG específico para tipo_financiamiento
        log_message('debug', 'Financiamiento::update() - tipo_financiamiento recibido: ' . ($data['tipo_financiamiento'] ?? 'NO RECIBIDO'));

        // Limpiar campos vacíos o nulos ANTES de la validación
        $data = $this->cleanData($data);
        log_message('debug', 'Financiamiento::update() - Datos después de cleanData (pre-validación): ' . json_encode($data));

        if (!$this->validate($this->perfilModel->getValidationRules(), $data)) {
            $errors = $this->validator->getErrors();
            log_message('error', 'Financiamiento::update() - Errores de validación: ' . json_encode($errors));
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $errors);
        }

        // Validación adicional para meses totales
        $mesesSinIntereses = (int)($data['meses_sin_intereses'] ?? 0);
        $mesesConIntereses = (int)($data['meses_con_intereses'] ?? 0);
        $totalMeses = $mesesSinIntereses + $mesesConIntereses;
        
        log_message('debug', 'Financiamiento::update() - Validación meses: S/I=' . $mesesSinIntereses . ', C/I=' . $mesesConIntereses . ', Total=' . $totalMeses);
        
        if ($totalMeses <= 0) {
            log_message('error', 'Financiamiento::update() - Error: Total meses <= 0');
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Debe especificar al menos un mes de financiamiento (con o sin intereses)');
        }
        
        // Validar que solo haya una configuración default por empresa
        if (isset($data['es_default']) && $data['es_default'] == 1) {
            log_message('debug', 'Financiamiento::update() - Actualizando financiamiento default para empresa: ' . $data['empresa_id']);
            $this->perfilModel->where('empresa_id', $data['empresa_id'])
                             ->where('id !=', $id)
                             ->set(['es_default' => 0])
                             ->update();
        }

        // Los datos ya fueron limpiados antes de la validación

        try {
            // DEBUG: Log los datos finales que se van a guardar
            log_message('debug', 'Financiamiento::update() - Datos finales a guardar: ' . json_encode($data));
            
            // Verificar si hay cambios comparando con el registro actual
            $currentRecord = $this->perfilModel->find($id);
            log_message('debug', 'Financiamiento::update() - Registro actual: ' . json_encode($currentRecord));
            
            $result = $this->perfilModel->update($id, $data);
            log_message('debug', 'Financiamiento::update() - Resultado del update: ' . ($result ? 'true' : 'false'));
            
            if ($result) {
                // Verificar que realmente se guardó
                $updatedRecord = $this->perfilModel->find($id);
                log_message('debug', 'Financiamiento::update() - Registro después del update: ' . json_encode($updatedRecord));
                
                log_message('info', 'Financiamiento::update() - Actualización exitosa para ID: ' . $id);
                return redirect()->to('admin/financiamiento')
                               ->with('success', 'Financiamiento actualizado exitosamente');
            } else {
                $modelErrors = $this->perfilModel->errors();
                log_message('error', 'Financiamiento::update() - Error en modelo: ' . json_encode($modelErrors));
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Error al actualizar el financiamiento. Errores: ' . json_encode($modelErrors));
            }
        } catch (\Exception $e) {
            log_message('error', 'Financiamiento::update() - Excepción: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error inesperado: ' . $e->getMessage());
        }
    }

    /**
     * Limpia los datos para evitar errores en el modelo
     */
    private function cleanData(array $data): array
    {
        // Convertir checkboxes a valores booleanos apropiados
        $booleanFields = [
            'es_default', 'activo', 'permite_apartado', 
            'aplica_terreno_habitacional', 'aplica_terreno_comercial', 
            'aplica_casa', 'aplica_departamento',
            'promocion_cero_enganche'
        ];
        
        foreach ($booleanFields as $field) {
            if (!isset($data[$field]) || $data[$field] === null || $data[$field] === '') {
                $data[$field] = 0;
            } else {
                $data[$field] = (int)$data[$field];
            }
        }
        
        // Campos que deben ser integers (para validaciones is_natural)
        $integerFields = [
            'meses_sin_intereses' => 0,
            'meses_con_intereses' => 0,
            'dias_anticipo' => 30,
            'plazo_liquidar_enganche' => 10,
            'prioridad' => 0,
            'mensualidades_comision' => 2
        ];
        
        foreach ($integerFields as $field => $default) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $data[$field] = $default;
            } else {
                $data[$field] = (int)$data[$field];
            }
        }
        
        // Campos que deben ser floats (para porcentajes y montos)
        $floatFields = [
            'porcentaje_anticipo' => 0.0,
            'anticipo_fijo' => 0.0,
            'enganche_minimo' => null,
            'apartado_minimo' => 0.0,
            'porcentaje_comision' => 0.0,
            'comision_fija' => 0.0,
            'porcentaje_interes_anual' => 0.0,
            'penalizacion_apartado' => 0.0,
            'penalizacion_enganche_tardio' => 0.0,
            'porcentaje_cancelacion' => 100.0,
            'metros_cuadrados_max' => null,
            'superficie_minima_m2' => null,
            'monto_minimo' => null,
            'monto_maximo' => null
        ];
        
        foreach ($floatFields as $field => $default) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $data[$field] = $default;
            } else {
                // Para campos de superficie y montos, si viene 0 o vacío, establecer NULL para no aplicar filtro
                if (in_array($field, ['superficie_minima_m2', 'metros_cuadrados_max', 'monto_minimo', 'monto_maximo', 'enganche_minimo'])) {
                    $value = (float)$data[$field];
                    $data[$field] = $value > 0 ? $value : null;
                    log_message('debug', "cleanData() - {$field} convertido: " . ($data[$field] ?: 'NULL'));
                } else {
                    $data[$field] = is_null($default) ? (float)$data[$field] : (float)$data[$field];
                }
            }
        }
        
        // Campos de texto
        $textFields = ['nombre', 'descripcion', 'tipo_anticipo', 'tipo_comision', 'tipo_financiamiento', 'accion_anticipo_incompleto'];
        foreach ($textFields as $field) {
            if (!isset($data[$field])) {
                // Valores por defecto para campos críticos
                if ($field === 'tipo_financiamiento') {
                    $data[$field] = 'msi';
                } else {
                    $data[$field] = '';
                }
            }
        }
        
        // VALIDACIÓN SUAVE: Solo aplicar correcciones críticas
        $tipoFinanciamiento = $data['tipo_financiamiento'] ?? 'msi';
        $mesesSinInteres = (int)($data['meses_sin_intereses'] ?? 0);
        $mesesConInteres = (int)($data['meses_con_intereses'] ?? 0);
        
        // Solo aplicar correcciones mínimas necesarias
        if ($tipoFinanciamiento === 'msi') {
            // MSI: Solo forzar tasa 0% - NO mover meses automáticamente
            $data['porcentaje_interes_anual'] = 0.0;
            log_message('debug', "Financiamiento::cleanData() - MSI: Forzando tasa 0%, manteniendo meses como están");
        } else {
            // MCI: Permitir cualquier configuración de meses y tasa
            // No hacer auto-correcciones que confundan al usuario
            log_message('debug', "Financiamiento::cleanData() - MCI: Respetando valores del usuario sin correcciones");
        }
        
        // Solo validar que la configuración tenga sentido para cálculos
        $totalMesesConfig = $mesesSinInteres + $mesesConInteres;
        if ($totalMesesConfig <= 0) {
            log_message('warning', "Financiamiento::cleanData() - Financiamiento sin meses válidos, estableciendo default según tipo");
            if ($tipoFinanciamiento === 'msi') {
                $data['meses_sin_intereses'] = 1; // Mínimo 1 mes
            } else {
                $data['meses_con_intereses'] = 1; // Mínimo 1 mes
            }
        }
        
        log_message('debug', 'Financiamiento::cleanData() - Configuración final respetada: ' . json_encode([
            'tipo_financiamiento' => $data['tipo_financiamiento'],
            'tasa_anual' => $data['porcentaje_interes_anual'],
            'meses_sin_intereses' => $data['meses_sin_intereses'], 
            'meses_con_intereses' => $data['meses_con_intereses']
        ]));
        
        // Campos especiales que pueden ser NULL
        $nullableFields = [
            'proyecto_id', 'enganche_minimo', 'metros_cuadrados_max',
            'superficie_minima_m2', 'monto_minimo', 'monto_maximo',
            'fecha_vigencia_inicio', 'fecha_vigencia_fin'
        ];
        foreach ($nullableFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $data[$field] = null;
            } else {
                // Para proyecto_id, manejar conversiones especiales
                if ($field === 'proyecto_id') {
                    $original = $data[$field];
                    $value = (int)$data[$field];
                    // Si es 0 o menor, establecer como NULL (significa "Global")
                    $data[$field] = $value > 0 ? $value : null;
                    log_message('debug', "cleanData() - proyecto_id original: {$original}, convertido: " . ($data[$field] ?: 'NULL'));
                }
                // Para fechas, validar formato
                else if (in_array($field, ['fecha_vigencia_inicio', 'fecha_vigencia_fin'])) {
                    if ($data[$field] === '0000-00-00' || $data[$field] === '') {
                        $data[$field] = null;
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * Desactivar financiamiento (soft delete personalizado)
     */
    public function delete($id)
    {
        $financiamiento = $this->perfilModel->find($id);
        
        if (!$financiamiento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Financiamiento no encontrado'
            ]);
        }

        // No permitir eliminar financiamiento default
        if ($financiamiento->es_default) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar el financiamiento por defecto'
            ]);
        }

        // Verificar si el financiamiento está siendo usado en ventas activas
        $ventasAsociadas = $this->ventaModel->where('perfil_financiamiento_id', $id)
                                          ->where('estatus_venta !=', 'cancelada')
                                          ->countAllResults();

        if ($ventasAsociadas > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No se puede eliminar el financiamiento porque tiene {$ventasAsociadas} venta(s) asociada(s). Primero debe desactivar el financiamiento."
            ]);
        }

        // Verificar si el financiamiento está siendo usado en apartados vigentes
        $apartadosAsociados = $this->apartadoModel->where('perfil_financiamiento_id', $id)
                                                ->where('estatus_apartado', 'vigente')
                                                ->countAllResults();

        if ($apartadosAsociados > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No se puede eliminar el financiamiento porque tiene {$apartadosAsociados} apartado(s) vigente(s). Primero debe cancelar los apartados o desactivar el financiamiento."
            ]);
        }

        // Verificar si el financiamiento tiene comisiones asociadas
        $db = \Config\Database::connect();
        $comisionesAsociadas = $db->table('configuracion_comisiones')
                                 ->where('tipo_plan_financiamiento_id', $id)
                                 ->countAllResults();

        if ($comisionesAsociadas > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No se puede eliminar el financiamiento porque tiene {$comisionesAsociadas} configuración(es) de comisión asociada(s). Primero debe eliminar las configuraciones de comisión."
            ]);
        }

        // Verificar que el financiamiento ya esté desactivado
        if ($financiamiento->activo == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Para eliminar el financiamiento, primero debe desactivarlo'
            ]);
        }

        // Eliminar definitivamente solo si está desactivada y sin ventas asociadas
        if ($this->perfilModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Financiamiento eliminado definitivamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el financiamiento'
            ]);
        }
    }

    /**
     * Desactivar financiamiento (nuevo método)
     */
    public function desactivar($id)
    {
        $financiamiento = $this->perfilModel->find($id);
        
        if (!$financiamiento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Financiamiento no encontrado'
            ]);
        }

        // No permitir desactivar financiamiento default
        if ($financiamiento->es_default) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede desactivar el financiamiento por defecto'
            ]);
        }

        if ($this->perfilModel->update($id, ['activo' => 0])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Financiamiento desactivado exitosamente'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al desactivar el financiamiento'
            ]);
        }
    }

    /**
     * AJAX: Obtener financiamientos para DataTables
     */
    public function obtenerFinanciamientos()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        try {
            $empresaId = $this->request->getGet('empresa_id');
            // Convertir string vacío a null para que coincida con el tipo esperado
            $empresaId = (!empty($empresaId) && is_numeric($empresaId)) ? (int)$empresaId : null;
            $financiamientos = $this->perfilModel->getDatatableData($empresaId);

            $data = [];
            foreach ($financiamientos as $config) {
                $acciones = '<div class="btn-group">';
                $acciones .= '<a href="' . site_url('admin/financiamiento/show/' . $config->id) . '" class="btn btn-info btn-sm" title="Ver">';
                $acciones .= '<i class="fas fa-eye"></i></a>';
                $acciones .= '<a href="' . site_url('admin/financiamiento/edit/' . $config->id) . '" class="btn btn-warning btn-sm" title="Editar">';
                $acciones .= '<i class="fas fa-edit"></i></a>';
                
                if (!$config->es_default) {
                    $acciones .= '<button class="btn btn-success btn-sm btn-set-default" data-id="' . $config->id . '" title="Establecer como predeterminado">';
                    $acciones .= '<i class="fas fa-star"></i></button>';
                    $acciones .= '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $config->id . '" title="Eliminar">';
                    $acciones .= '<i class="fas fa-trash"></i></button>';
                }
                
                $acciones .= '</div>';

                // DEBUG temporal para configuraciones problemáticas
                $totalMeses = $config->getTotalMeses();
                if ($config->id == 5) {
                    log_message('debug', "Financiamiento ID 5 DEBUG: tipo_financiamiento={$config->tipo_financiamiento}, msi={$config->meses_sin_intereses}, mci={$config->meses_con_intereses}, total={$totalMeses}");
                }
                
                $data[] = [
                    'id' => $config->id,
                    'empresa' => $config->empresa_nombre ?? 'N/A',
                    'proyecto' => $config->proyecto_nombre ?? 'Global',
                    'nombre' => $config->nombre,
                    'anticipo' => $config->getAnticipoFormateado(),
                    'comision' => $config->getComisionFormateada(),
                    'plazo' => $totalMeses . ' meses',
                    'estado' => $config->getBadgeEstado(),
                    'acciones' => $acciones
                ];
            }

            return $this->response->setJSON(['data' => $data]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en obtenerFinanciamientos: ' . $e->getMessage());
            return $this->response->setJSON([
                'data' => [],
                'error' => 'Error al cargar financiamientos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Simular financiamiento
     */
    public function simular()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $financiamientoId = $this->request->getPost('financiamiento_id');
        $precioTotal = $this->request->getPost('precio_total');

        if (!$financiamientoId || !$precioTotal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos para la simulación'
            ]);
        }

        $financiamiento = $this->perfilModel->find($financiamientoId);
        
        if (!$financiamiento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Financiamiento no encontrado'
            ]);
        }

        $simulacion = $financiamiento->simularVenta(floatval($precioTotal));

        return $this->response->setJSON([
            'success' => true,
            'simulacion' => $simulacion,
            'financiamiento' => $financiamiento->getResumen()
        ]);
    }

    /**
     * AJAX: Establecer financiamiento como predeterminado
     */
    public function setDefault()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = $this->request->getPost('id');
        
        if ($this->perfilModel->setAsDefault($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Financiamiento establecido como predeterminado'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al establecer financiamiento predeterminado'
            ]);
        }
    }

    /**
     * AJAX: Duplicar financiamiento
     */
    public function duplicate()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = $this->request->getPost('id');
        
        $newId = $this->perfilModel->duplicate($id);
        
        if ($newId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Financiamiento duplicado exitosamente',
                'new_id' => $newId
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al duplicar el financiamiento'
            ]);
        }
    }

    /**
     * AJAX: Obtener financiamientos por empresa
     */
    public function getByEmpresa($empresaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $financiamientos = $this->perfilModel->getByEmpresa($empresaId);
        
        $options = [];
        foreach ($financiamientos as $config) {
            $options[] = [
                'id' => $config->id,
                'nombre' => $config->nombre,
                'es_default' => $config->es_default,
                'resumen' => $config->getResumen()
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'financiamientos' => $options
        ]);
    }

    /**
     * AJAX: Obtener proyectos por empresa
     */
    public function getProyectosByEmpresa($empresaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $proyectos = $this->proyectoModel->where('empresas_id', $empresaId)
                                       ->where('estatus', 'activo')
                                       ->findAll();
        
        $options = [['id' => '', 'nombre' => 'Global (Toda la empresa)']];
        foreach ($proyectos as $proyecto) {
            $options[] = [
                'id' => $proyecto->id,
                'nombre' => $proyecto->nombre
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'proyectos' => $options
        ]);
    }
}