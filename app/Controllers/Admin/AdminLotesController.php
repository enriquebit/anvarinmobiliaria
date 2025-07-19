<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LoteModel;
use App\Models\CategoriaLoteModel;
use App\Models\TipoLoteModel;
use App\Models\EstadoLoteModel;
use App\Models\AmenidadModel;
use App\Models\LoteAmenidadModel;
use App\Models\EmpresaModel;
use App\Models\ProyectoModel;
use App\Models\DivisionModel;
use App\Models\ManzanaModel;
use App\Entities\Lote;

class AdminLotesController extends BaseController
{
    protected $loteModel;
    protected $categoriaLoteModel;
    protected $tipoLoteModel;
    protected $estadoLoteModel;
    protected $amenidadModel;
    protected $loteAmenidadModel;
    protected $empresaModel;
    protected $proyectoModel;
    protected $divisionModel;
    protected $manzanaModel;

    public function __construct()
    {
        $this->loteModel = new LoteModel();
        $this->categoriaLoteModel = new CategoriaLoteModel();
        $this->tipoLoteModel = new TipoLoteModel();
        $this->estadoLoteModel = new EstadoLoteModel();
        $this->amenidadModel = new AmenidadModel();
        $this->loteAmenidadModel = new LoteAmenidadModel();
        $this->empresaModel = new EmpresaModel();
        $this->proyectoModel = new ProyectoModel();
        $this->divisionModel = new DivisionModel();
        $this->manzanaModel = new ManzanaModel();
    }

    /**
     * Página principal del módulo de lotes
     */
    public function index()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Gestión de Lotes'
        ];

        return view('admin/lotes/index', $data);
    }

    /**
     * Obtener lotes via AJAX con filtros
     */
    public function obtenerLotes()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $filtros = [
                'empresas_id' => $this->request->getPost('empresas_id'),
                'proyectos_id' => $this->request->getPost('proyectos_id'),
                'manzanas_id' => $this->request->getPost('manzanas_id'),
                'estados_lotes_id' => $this->request->getPost('estado_codigo'), // Mapear estado_codigo a estados_lotes_id
                'categorias_lotes_id' => $this->request->getPost('categorias_lotes_id'),
                'tipos_lotes_id' => $this->request->getPost('tipos_lotes_id'),
                'area_min' => $this->request->getPost('area_min'),
                'area_max' => $this->request->getPost('area_max'),
                'precio_min' => $this->request->getPost('precio_min'),
                'precio_max' => $this->request->getPost('precio_max'),
                'buscar' => $this->request->getPost('buscar'),
                'activo' => $this->request->getPost('activo'), // Sin valor por defecto, mostrar todos los lotes
                'ordenar' => $this->request->getPost('ordenar') ?? 'lotes.created_at',
                'direccion' => $this->request->getPost('direccion') ?? 'DESC'
            ];

            $lotes = $this->loteModel->getLotesConFiltros($filtros);

            // Formatear datos para DataTables
            $data = [];
            foreach ($lotes as $lote) {
                $amenidades = $this->loteAmenidadModel->getAmenidadesPorLote($lote['id']);
                $badgesAmenidades = '';
                foreach ($amenidades as $amenidad) {
                    $badgesAmenidades .= '<span class="badge badge-info mr-1"><i class="' . $amenidad['icono'] . '"></i> ' . $amenidad['nombre'] . '</span>';
                }

                $data[] = [
                    'id' => $lote['id'],
                    'numero' => $lote['numero'],
                    'clave' => $lote['clave'],
                    'empresa' => $lote['empresa_nombre'],
                    'proyecto' => $lote['proyecto_clave'] ?? $lote['proyecto_nombre'], // Usar clave si existe
                    'tipo' => $lote['tipo_lote_nombre'],
                    'division' => $lote['division_clave'] ?? $lote['division_nombre'], // Solo clave (E1 en lugar de ETAPA (E1))
                    'categoria' => $lote['categoria_nombre'],
                    'frente' => $lote['frente'] ? number_format($lote['frente'], 1) : '--', // 1 decimal, sin unidad
                    'fondo' => $lote['fondo'] ? number_format($lote['fondo'], 1) : '--', // 1 decimal, sin unidad
                    'lateral_izq' => $lote['lateral_izquierdo'] ? number_format($lote['lateral_izquierdo'], 1) : '--', // 1 decimal, sin unidad
                    'lateral_der' => $lote['lateral_derecho'] ? number_format($lote['lateral_derecho'], 1) : '--', // 1 decimal, sin unidad
                    'area' => number_format($lote['area'], 0), // Sin decimales ni unidad (header ya dice m²)
                    'construccion' => $lote['construccion'] ? number_format($lote['construccion'], 0) : '--', // Sin decimales ni unidad
                    'precio_m2' => formatPrecio($lote['precio_m2']),
                    'precio_total' => formatPrecio($lote['precio_total']),
                    'estado' => '<span class="badge badge-' . $this->getColorEstado($lote['estados_lotes_id']) . ' badge-pill">' . ($lote['estado_nombre'] ?: $this->getNombreEstadoPorId($lote['estados_lotes_id'])) . '</span>',
                    'acciones' => $this->generarBotonesAccion($lote)
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener lotes: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mostrar formulario para crear nuevo lote
     */
    public function create()
    {
        
        if (!isAdmin()) {
            return redirect()->to('/admin/lotes')->with('error', 'Acceso denegado');
        }

        $data = [
            'titulo' => 'Crear Nuevo Lote',
            'lote' => null,
            'empresas' => ['' => 'Seleccionar empresa...'] + $this->empresaModel->obtenerOpcionesSelect(),
            'proyectos' => ['' => 'Seleccionar proyecto...'],
            'divisiones' => ['' => 'Seleccionar división...'],
            'manzanas' => ['' => 'Seleccionar manzana...'],
            'categorias' => $this->categoriaLoteModel->getCategoriasPairaSelect(),
            'tipos' => $this->tipoLoteModel->getTiposPairaSelect(),
            'estados' => $this->estadoLoteModel->getEstadosPairaSelect(),
            'amenidades' => $this->amenidadModel->getAmenidadesPairaSelect(),
            'amenidades_seleccionadas' => []
        ];


        return view('admin/lotes/create', $data);
    }

    /**
     * Mostrar formulario para editar lote
     */
    public function edit($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/lotes')->with('error', 'Acceso denegado');
        }

        $lote = $this->loteModel->find($id);
        if (!$lote) {
            return redirect()->to('/admin/lotes')->with('error', 'Lote no encontrado');
        }

        // Obtener proyectos de la empresa del lote
        $proyectos = $this->proyectoModel->select('proyectos.*')
                                        ->join('empresas', 'empresas.id = proyectos.empresas_id')
                                        ->where('proyectos.empresas_id', $lote->empresas_id)
                                        ->where('empresas.activo', 1)
                                        ->where('proyectos.estatus', 'activo')
                                        ->orderBy('proyectos.nombre', 'ASC')
                                        ->findAll();
        $proyectosSelect = ['' => 'Seleccionar proyecto...'];
        foreach ($proyectos as $proyecto) {
            $proyectosSelect[$proyecto->id] = $proyecto->nombre . ' (' . $proyecto->clave . ')';
        }

        // Obtener manzanas del proyecto del lote
        $manzanas = $this->manzanaModel->where('proyectos_id', $lote->proyectos_id)
                                      ->where('activo', true)
                                      ->findAll();
        $manzanasSelect = ['' => 'Seleccionar manzana...'];
        foreach ($manzanas as $manzana) {
            $manzanasSelect[$manzana->id] = $manzana->nombre;
        }

        // Obtener divisiones del proyecto del lote
        $divisiones = $this->divisionModel->where('proyectos_id', $lote->proyectos_id)
                                         ->where('activo', true)
                                         ->findAll();
        $divisionesSelect = ['' => 'Seleccionar división...'];
        foreach ($divisiones as $division) {
            $divisionesSelect[$division->id] = $division->nombre . ' (' . $division->clave . ')';
        }

        // Obtener amenidades del lote
        $amenidadesLote = $this->loteAmenidadModel->getAmenidadesPorLote($id);
        $amenidadesSeleccionadas = array_column($amenidadesLote, 'id');
        log_message('debug', "Lote ID: $id, Amenidades encontradas: " . json_encode($amenidadesLote));
        log_message('debug', "Amenidades seleccionadas: " . json_encode($amenidadesSeleccionadas));

        $data = [
            'titulo' => 'Editar Lote: ' . $lote->numero,
            'lote' => $lote,
            'empresas' => ['' => 'Seleccionar empresa...'] + $this->empresaModel->obtenerOpcionesSelect(),
            'proyectos' => $proyectosSelect,
            'divisiones' => $divisionesSelect,
            'manzanas' => $manzanasSelect,
            'categorias' => $this->categoriaLoteModel->getCategoriasPairaSelect(),
            'tipos' => $this->tipoLoteModel->getTiposPairaSelect(),
            'estados' => $this->estadoLoteModel->getEstadosPairaSelect(),
            'amenidades' => $this->amenidadModel->getAmenidadesPairaSelect(),
            'amenidades_seleccionadas' => $amenidadesSeleccionadas
        ];

        return view('admin/lotes/edit', $data);
    }

    /**
     * Guardar nuevo lote
     */
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/lotes');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $datos = [
                'numero' => $this->request->getPost('numero'),
                'empresas_id' => $this->request->getPost('empresas_id'),
                'proyectos_id' => $this->request->getPost('proyectos_id'),
                'divisiones_id' => $this->request->getPost('divisiones_id'),
                'manzanas_id' => $this->request->getPost('manzanas_id'),
                'categorias_lotes_id' => $this->request->getPost('categorias_lotes_id'),
                'tipos_lotes_id' => $this->request->getPost('tipos_lotes_id'),
                'estados_lotes_id' => $this->request->getPost('estados_lotes_id'),
                'area' => $this->request->getPost('area'),
                'frente' => $this->request->getPost('frente') ?: null,
                'fondo' => $this->request->getPost('fondo') ?: null,
                'lateral_izquierdo' => $this->request->getPost('lateral_izquierdo') ?: null,
                'lateral_derecho' => $this->request->getPost('lateral_derecho') ?: null,
                'construccion' => $this->request->getPost('construccion') ?: 0,
                'precio_m2' => $this->request->getPost('precio_m2'),
                'descripcion' => $this->request->getPost('descripcion'),
                'coordenadas_poligono' => $this->request->getPost('coordenadas_poligono'),
                'longitud' => $this->request->getPost('longitud'),
                'latitud' => $this->request->getPost('latitud'),
                'color' => $this->request->getPost('color') ?: '#3498db'
            ];

            // Validar coordenadas GPS solo si se proporcionan
            if (($datos['longitud'] || $datos['latitud']) && !$this->loteModel->validarCoordenadas($datos['longitud'], $datos['latitud'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Las coordenadas GPS no son válidas'
                ]);
            }

            // Calcular precio total
            $area = floatval($datos['area']);
            $precioM2 = floatval($datos['precio_m2']);
            $datos['precio_total'] = $area * $precioM2;

            // Generar clave automática solo si tenemos todos los datos necesarios
            if ($datos['empresas_id'] && $datos['proyectos_id'] && $datos['divisiones_id'] && $datos['manzanas_id'] && $datos['numero']) {
                $clave = $this->loteModel->generarClave(
                    intval($datos['empresas_id']),
                    intval($datos['proyectos_id']),
                    intval($datos['divisiones_id']),
                    intval($datos['manzanas_id']),
                    $datos['numero']
                );
                $datos['clave'] = $clave;
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Faltan datos requeridos para generar la clave del lote (empresa, proyecto, división, manzana, número)'
                ]);
            }

            $this->loteModel->transBegin();

            // Insertar lote
            if (!$this->loteModel->insert($datos)) {
                $errors = $this->loteModel->errors();
                if (!empty($errors)) {
                    throw new \Exception('Error al crear el lote: ' . implode(', ', $errors));
                } else {
                    throw new \Exception('Error al crear el lote. Verifique que no exista otro lote con el mismo número en esta manzana.');
                }
            }

            $loteId = $this->loteModel->getInsertID();

            // Procesar amenidades
            $amenidades = $this->request->getPost('amenidades') ?: [];
            log_message('debug', 'Amenidades recibidas (store): ' . json_encode($amenidades));
            if (!empty($amenidades)) {
                $this->loteAmenidadModel->asignarAmenidadesALote($loteId, $amenidades);
            }

            $this->loteModel->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lote creado exitosamente',
                'id' => $loteId
            ]);

        } catch (\Exception $e) {
            $this->loteModel->transRollback();
            
            // Manejar errores específicos de clave duplicada
            $message = $e->getMessage();
            if (strpos($message, 'Duplicate entry') !== false && strpos($message, 'unique_numero_manzana') !== false) {
                $message = 'Ya existe un lote con este número en la manzana seleccionada. Por favor use un número diferente.';
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => $message
            ]);
        }
    }

    /**
     * Actualizar lote existente
     */
    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/lotes');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $lote = $this->loteModel->find($id);
            if (!$lote) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lote no encontrado']);
            }

            $datos = [
                'numero' => $this->request->getPost('numero'),
                'empresas_id' => $this->request->getPost('empresas_id') ?: $lote->empresas_id,
                'proyectos_id' => $this->request->getPost('proyectos_id') ?: $lote->proyectos_id,
                'divisiones_id' => $this->request->getPost('divisiones_id') ?: $lote->divisiones_id,
                'manzanas_id' => $this->request->getPost('manzanas_id') ?: $lote->manzanas_id,
                'categorias_lotes_id' => $this->request->getPost('categorias_lotes_id'),
                'tipos_lotes_id' => $this->request->getPost('tipos_lotes_id'),
                'estados_lotes_id' => $this->request->getPost('estados_lotes_id'),
                'area' => $this->request->getPost('area'),
                'frente' => $this->request->getPost('frente') ?: null,
                'fondo' => $this->request->getPost('fondo') ?: null,
                'lateral_izquierdo' => $this->request->getPost('lateral_izquierdo') ?: null,
                'lateral_derecho' => $this->request->getPost('lateral_derecho') ?: null,
                'construccion' => $this->request->getPost('construccion') ?: 0,
                'precio_m2' => $this->request->getPost('precio_m2'),
                'descripcion' => $this->request->getPost('descripcion'),
                'coordenadas_poligono' => $this->request->getPost('coordenadas_poligono'),
                'longitud' => $this->request->getPost('longitud'),
                'latitud' => $this->request->getPost('latitud'),
                'color' => $this->request->getPost('color') ?: '#3498db'
            ];

            // Validar coordenadas GPS solo si se proporcionan
            if (($datos['longitud'] || $datos['latitud']) && !$this->loteModel->validarCoordenadas($datos['longitud'], $datos['latitud'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Las coordenadas GPS no son válidas'
                ]);
            }

            // Recalcular precio total
            $area = floatval($datos['area']);
            $precioM2 = floatval($datos['precio_m2']);
            $datos['precio_total'] = $area * $precioM2;

            // Regenerar clave automática solo si tenemos todos los datos necesarios
            if ($datos['empresas_id'] && $datos['proyectos_id'] && $datos['divisiones_id'] && $datos['manzanas_id'] && $datos['numero']) {
                $clave = $this->loteModel->generarClave(
                    intval($datos['empresas_id']),
                    intval($datos['proyectos_id']),
                    intval($datos['divisiones_id']),
                    intval($datos['manzanas_id']),
                    $datos['numero']
                );
                $datos['clave'] = $clave;
            }

            $this->loteModel->transBegin();

            // Actualizar lote
            if (!$this->loteModel->update($id, $datos)) {
                throw new \Exception('Error al actualizar el lote: ' . implode(', ', $this->loteModel->errors()));
            }

            // Procesar amenidades
            $amenidades = $this->request->getPost('amenidades') ?: [];
            log_message('debug', 'Amenidades recibidas: ' . json_encode($amenidades));
            $this->loteAmenidadModel->asignarAmenidadesALote($id, $amenidades);

            $this->loteModel->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lote actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            $this->loteModel->transRollback();
            
            // Manejar errores específicos de clave duplicada
            $message = $e->getMessage();
            if (strpos($message, 'Duplicate entry') !== false && strpos($message, 'unique_numero_manzana') !== false) {
                $message = 'Ya existe un lote con este número en la manzana seleccionada. Por favor use un número diferente.';
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => $message
            ]);
        }
    }

    /**
     * Eliminar lote (soft delete)
     */
    public function destroy($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/lotes');
        }

        if (!isSuperAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo superadministradores pueden eliminar lotes']);
        }

        try {
            $lote = $this->loteModel->find($id);
            if (!$lote) {
                return $this->response->setJSON(['success' => false, 'message' => 'Lote no encontrado']);
            }

            // VALIDACIÓN: Verificar si el lote tiene transacciones activas
            $ventasActivas = $this->loteModel->db->table('ventas')
                ->where('lote_id', $id)
                ->where('estatus_venta !=', 'cancelada')
                ->countAllResults();
            
            $apartadosVigentes = $this->loteModel->db->table('apartados')
                ->where('lote_id', $id)
                ->where('estatus_apartado', 'vigente')
                ->countAllResults();

            $amenidades = $this->loteModel->db->table('lotes_amenidades')
                ->where('lotes_id', $id)
                ->countAllResults();

            $bloqueos = $this->loteModel->db->table('lotes_bloqueados')
                ->where('lote_id', $id)
                ->where('estatus', 'activo')
                ->countAllResults();

            // Generar mensaje detallado de restricciones
            $restricciones = [];
            if ($ventasActivas > 0) {
                $restricciones[] = "{$ventasActivas} venta(s) activa(s)";
            }
            if ($apartadosVigentes > 0) {
                $restricciones[] = "{$apartadosVigentes} apartado(s) vigente(s)";
            }
            if ($amenidades > 0) {
                $restricciones[] = "{$amenidades} amenidad(es) asociada(s)";
            }
            if ($bloqueos > 0) {
                $restricciones[] = "{$bloqueos} bloqueo(s) activo(s)";
            }

            if (!empty($restricciones)) {
                $mensaje = "❌ NO SE PUEDE ELIMINAR EL LOTE\n\n";
                $mensaje .= "🔒 RESTRICCIONES DE INTEGRIDAD:\n";
                $mensaje .= "• " . implode("\n• ", $restricciones) . "\n\n";
                $mensaje .= "📋 PASOS PARA ELIMINAR:\n";
                $mensaje .= "1. Cancelar todas las ventas activas\n";
                $mensaje .= "2. Cancelar todos los apartados vigentes\n";
                $mensaje .= "3. Remover amenidades asociadas\n";
                $mensaje .= "4. Liberar bloqueos activos\n\n";
                $mensaje .= "⚠️ IMPORTANTE: La eliminación de lotes con transacciones puede afectar la integridad contable y fiscal del sistema.";

                return $this->response->setJSON([
                    'success' => false,
                    'message' => $mensaje,
                    'tipo' => 'restricciones_integridad',
                    'detalles' => [
                        'ventas_activas' => $ventasActivas,
                        'apartados_vigentes' => $apartadosVigentes,
                        'amenidades' => $amenidades,
                        'bloqueos' => $bloqueos
                    ]
                ]);
            }

            $this->loteModel->delete($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lote eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            // Detectar si es un error de foreign key constraint
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $mensaje = "❌ ERROR DE INTEGRIDAD REFERENCIAL\n\n";
                $mensaje .= "🔗 El lote tiene dependencias en otras tablas del sistema.\n\n";
                $mensaje .= "📋 POSIBLES CAUSAS:\n";
                $mensaje .= "• Ventas registradas con este lote\n";
                $mensaje .= "• Apartados asociados al lote\n";
                $mensaje .= "• Amenidades configuradas\n";
                $mensaje .= "• Bloqueos administrativos\n";
                $mensaje .= "• Registros en tabla de amortización\n\n";
                $mensaje .= "💡 SOLUCIÓN: Elimine primero todos los registros dependientes o contacte al administrador del sistema.";

                return $this->response->setJSON([
                    'success' => false,
                    'message' => $mensaje,
                    'tipo' => 'foreign_key_error'
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar lote: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Cambiar estado de un lote
     */
    public function cambiarEstado($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/lotes');
        }

        if (!isAdmin()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        try {
            $nuevoEstado = $this->request->getPost('estado');
            
            if (!$this->loteModel->cambiarEstado($id, $nuevoEstado)) {
                throw new \Exception('Error al cambiar estado del lote');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Estado del lote actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Método temporal para verificar datos
     */
    public function debugBaseDatos()
    {
        $empresas = $this->empresaModel->findAll();
        $proyectos = $this->proyectoModel->getProyectosConEmpresa();
        
        echo "<h3>Empresas:</h3>";
        foreach($empresas as $empresa) {
            echo "ID: {$empresa->id} - Nombre: {$empresa->nombre}<br>";
        }
        
        echo "<h3>Proyectos:</h3>";
        foreach($proyectos as $proyecto) {
            echo "ID: {$proyecto->id} - Nombre: {$proyecto->nombre} - Empresa ID: " . (property_exists($proyecto, 'empresas_id') ? $proyecto->empresas_id : 'NO EXISTE') . "<br>";
        }
        
        echo "<h3>Estructura tabla proyectos:</h3>";
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('proyectos');
        foreach($fields as $field) {
            echo "Campo: {$field->name} - Tipo: {$field->type}<br>";
        }
    }

    /**
     * Obtener proyectos por empresa (AJAX)
     */
    public function obtenerProyectosPorEmpresa($empresaId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            // Debug: Log para verificar que se está llamando el método
            log_message('info', "Buscando proyectos para empresa ID: {$empresaId}");
            
            $proyectos = $this->proyectoModel->select('proyectos.*')
                                            ->join('empresas', 'empresas.id = proyectos.empresas_id')
                                            ->where('proyectos.empresas_id', $empresaId)
                                            ->where('empresas.activo', 1) // Solo empresas activas
                                            ->where('proyectos.estatus', 'activo') // Solo proyectos activos
                                            ->orderBy('proyectos.nombre', 'ASC')
                                            ->findAll();
            
            // Debug: Log cantidad encontrada
            log_message('info', "Proyectos encontrados: " . count($proyectos));

            $opciones = [];
            foreach ($proyectos as $proyecto) {
                // Incluir la clave entre paréntesis para que el JavaScript la pueda extraer
                $opciones[$proyecto->id] = $proyecto->nombre . ' (' . $proyecto->clave . ')';
                log_message('info', "Proyecto ID: {$proyecto->id}, Nombre: {$proyecto->nombre}, Clave: {$proyecto->clave}");
            }

            return $this->response->setJSON([
                'success' => true,
                'proyectos' => $opciones,
                'debug' => [
                    'empresa_id' => $empresaId,
                    'total_encontrados' => count($proyectos)
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', "Error al obtener proyectos: " . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener proyectos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener manzanas por proyecto (AJAX)
     */
    public function obtenerManzanasPorProyecto($proyectoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $manzanas = $this->manzanaModel->where('proyectos_id', $proyectoId)
                                          ->where('activo', true)
                                          ->orderBy('nombre', 'ASC')
                                          ->findAll();

            $opciones = [];
            foreach ($manzanas as $manzana) {
                $opciones[$manzana->id] = $manzana->nombre;
            }

            return $this->response->setJSON([
                'success' => true,
                'manzanas' => $opciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener manzanas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener estadísticas de lotes por proyecto
     */
    public function estadisticasProyecto($proyectoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $estadisticas = $this->loteModel->getEstadisticasPorProyecto($proyectoId);

            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener divisiones por proyecto (AJAX)
     */
    public function obtenerDivisionesPorProyecto($proyectoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $divisiones = $this->divisionModel->select('divisiones.*')
                                             ->join('proyectos', 'proyectos.id = divisiones.proyectos_id')
                                             ->join('empresas', 'empresas.id = proyectos.empresas_id')
                                             ->where('divisiones.proyectos_id', $proyectoId)
                                             ->where('divisiones.activo', true)
                                             ->where('proyectos.estatus', 'activo')
                                             ->where('empresas.activo', 1)
                                             ->orderBy('divisiones.orden', 'ASC')
                                             ->orderBy('divisiones.nombre', 'ASC')
                                             ->findAll();

            $opciones = [];
            foreach ($divisiones as $division) {
                $opciones[$division->id] = $division->nombre . ' (' . $division->clave . ')';
            }

            return $this->response->setJSON([
                'success' => true,
                'divisiones' => $opciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener divisiones: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ver detalles completos de un lote
     */
    public function show($id)
    {
        if (!isAdmin()) {
            return redirect()->to('/admin/lotes')->with('error', 'Acceso denegado');
        }

        $lote = $this->loteModel->find($id);
        if (!$lote) {
            return redirect()->to('/admin/lotes')->with('error', 'Lote no encontrado');
        }

        $data = [
            'titulo' => 'Detalles del Lote: ' . $lote->numero,
            'lote' => $lote,
            'amenidades' => $this->loteAmenidadModel->getAmenidadesPorLote($id),
            'lotes_similares' => $this->loteModel->getLotesSimilares($id)
        ];

        return view('admin/lotes/show', $data);
    }

    /**
     * Obtener empresas para filtros (AJAX)
     */
    public function obtenerEmpresas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $empresas = $this->empresaModel->where('activo', 1)
                                          ->orderBy('nombre', 'ASC')
                                          ->findAll();

            $opciones = [];
            foreach ($empresas as $empresa) {
                $opciones[] = [
                    'id' => $empresa->id,
                    'nombre' => $empresa->nombre
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'empresas' => $opciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener empresas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener categorías de lotes para filtros (AJAX)
     */
    public function obtenerCategorias()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $categorias = $this->categoriaLoteModel->where('activo', 1)
                                                  ->orderBy('nombre', 'ASC')
                                                  ->findAll();

            $opciones = [];
            foreach ($categorias as $categoria) {
                $opciones[] = [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'categorias' => $opciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ]);
        }
    }

    // Métodos privados de utilidad

    /**
     * Obtener color del badge según el estado por ID
     */
    private function getColorEstado($estadoId): string
    {
        return match($estadoId) {
            0 => 'success',    // Disponible - Verde
            1 => 'warning',    // Apartado - Amarillo
            2 => 'danger',     // Vendido - Rojo
            3 => 'secondary',  // Suspendido - Gris
            default => 'secondary'
        };
    }

    /**
     * Obtener color del badge según el nombre del estado
     */
    private function getColorEstadoNombre($estadoNombre): string
    {
        return match(strtolower(trim($estadoNombre))) {
            'disponible' => 'success',    // Verde
            'apartado' => 'warning',      // Amarillo
            'vendido' => 'danger',        // Rojo
            'suspendido' => 'secondary',  // Gris
            default => 'secondary'
        };
    }

    /**
     * Obtener nombre del estado por ID cuando el LEFT JOIN no encuentra registro
     */
    private function getNombreEstadoPorId($estadoId): string
    {
        return match($estadoId) {
            0 => 'Disponible',
            1 => 'Apartado', 
            2 => 'Vendido',
            3 => 'Suspendido',
            default => 'Sin Estado'
        };
    }

    /**
     * Generar botones de acción para la tabla
     */
    private function generarBotonesAccion($lote): string
    {
        $botones = '<div class="btn-group" role="group">';
        
        // Ver detalles
        $botones .= '<a href="' . base_url('admin/lotes/show/' . $lote['id']) . '" class="btn btn-sm btn-info" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                     </a>';
        
        // Editar
        $botones .= '<a href="' . base_url('admin/lotes/edit/' . $lote['id']) . '" class="btn btn-sm btn-warning" title="Editar">
                        <i class="fas fa-edit"></i>
                     </a>';
        
        // Eliminar (solo superadmin)
        if (isSuperAdmin()) {
            $botones .= '<button class="btn btn-sm btn-danger btn-eliminar" data-id="' . $lote['id'] . '" title="Eliminar permanentemente">
                            <i class="fas fa-trash"></i>
                         </button>';
        }
        
        $botones .= '</div>';
        
        return $botones;
    }

    /**
     * Regenerar claves de lotes existentes con nueva nomenclatura
     * PROYECTO-DIVISION-MANZANA-NUMERO
     */
    public function regenerarClaves()
    {
        if (!isSuperAdmin()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Solo superadministradores pueden regenerar claves'
                ]);
            }
            return redirect()->to('/admin/lotes')->with('error', 'Acceso denegado');
        }

        try {
            $resultados = $this->loteModel->regenerarClavesExistentes();

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => sprintf(
                        'Regeneración completada. Total: %d, Actualizados: %d, Errores: %d',
                        $resultados['total'],
                        $resultados['actualizados'], 
                        $resultados['errores']
                    ),
                    'data' => $resultados
                ]);
            }

            $mensaje = sprintf(
                'Regeneración de claves completada: %d lotes procesados, %d actualizados, %d errores',
                $resultados['total'],
                $resultados['actualizados'],
                $resultados['errores']
            );

            return redirect()->to('/admin/lotes')->with('success', $mensaje);

        } catch (\Exception $e) {
            log_message('error', 'Error en regenerarClaves: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al regenerar claves: ' . $e->getMessage()
                ]);
            }

            return redirect()->to('/admin/lotes')->with('error', 'Error al regenerar claves');
        }
    }


    /**
     * Cambiar estado de un lote AJAX (para la vista de tabla)
     */
    public function cambiarEstadoAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $id = $this->request->getPost('id');
        $nuevoEstado = $this->request->getPost('estado');
        $motivo = $this->request->getPost('motivo');

        try {
            $this->loteModel->cambiarEstado($id, $nuevoEstado);
            
            // Log del cambio si hay motivo
            if (!empty($motivo)) {
                log_message('info', "Cambio de estado lote {$id}: {$motivo}");
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Estado del lote actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ]);
        }
    }
}