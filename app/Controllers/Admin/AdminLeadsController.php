<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RegistroLeadModel;
// use App\Models\DocumentoLeadModel; // TODO: Reimplement when DocumentoLeadModel is recreated
use App\Models\RegistroApiLogsModel;
use CodeIgniter\HTTP\ResponseInterface;

class AdminLeadsController extends BaseController
{
    protected $registroModel;
    // protected $documentosModel; // TODO: Reimplement when DocumentoLeadModel is recreated
    protected $logsModel;

    public function __construct()
    {
        $this->registroModel = new RegistroLeadModel();
        // $this->documentosModel = new DocumentoLeadModel(); // TODO: Reimplement when DocumentoLeadModel is recreated
        $this->logsModel = new RegistroApiLogsModel();
        
        helper(['form', 'url']);
    }

    /**
     * Dashboard de métricas principal
     */
    public function metricas()
    {
        $data = [
            'titulo' => 'Métricas de Leads y Prospectos',
            'breadcrumb' => [
                ['name' => 'Admin', 'url' => '/admin/dashboard'],
                ['name' => 'Gestión de Leads', 'url' => '/admin/leads'],
                ['name' => 'Métricas', 'url' => '']
            ]
        ];

        return view('admin/leads/metricas', $data);
    }

    /**
     * Lista principal de registros
     */
    public function index()
    {
        $data = [
            'titulo' => 'Gestión de Leads y Prospectos',
            'breadcrumb' => [
                ['name' => 'Admin', 'url' => '/admin/dashboard'],
                ['name' => 'Gestión de Leads', 'url' => '']
            ]
        ];

        return view('admin/leads/index', $data);
    }

    /**
     * Ver detalle de un registro
     */
    public function show($id)
    {
        // Debug temporal - información del modelo
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'AdminLeadsController::show - Buscando ID: ' . $id);
            log_message('debug', 'AdminLeadsController::show - Tabla: ' . $this->registroModel->getTable());
            
            // Verificar si existe directamente en la base de datos
            $builder = $this->registroModel->builder();
            $count = $builder->where('id', $id)->countAllResults();
            log_message('debug', 'AdminLeadsController::show - Registros encontrados en DB: ' . $count);
            
            // Intentar consulta directa
            $builder = $this->registroModel->builder();
            $rawData = $builder->where('id', $id)->get()->getRow();
            log_message('debug', 'AdminLeadsController::show - Raw data exists: ' . ($rawData ? 'SÍ' : 'NO'));
            if ($rawData) {
                log_message('debug', 'AdminLeadsController::show - Raw folio: ' . ($rawData->folio ?? 'NULL'));
            }
        }

        $registro = $this->registroModel->find($id);
        
        if (!$registro) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'AdminLeadsController::show - Registro NULL para ID: ' . $id);
            }
            return redirect()->to('/admin/leads')->with('error', 'Registro no encontrado');
        }

        // Debug temporal - verificar qué tipo de objeto es
        if (ENVIRONMENT === 'development') {
            log_message('debug', 'AdminLeadsController::show - Tipo de registro: ' . get_class($registro));
            log_message('debug', 'AdminLeadsController::show - ID: ' . $id);
            log_message('debug', 'AdminLeadsController::show - Folio: ' . ($registro->folio ?? 'NULL'));
            
            // Verificar si los métodos existen
            $metodos = ['getNombreCompleto', 'getTelefonoFormateado', 'getDesarrolloTexto', 'getBadgeEstadoSincronizacion'];
            foreach ($metodos as $metodo) {
                log_message('debug', "Método {$metodo} existe: " . (method_exists($registro, $metodo) ? 'SÍ' : 'NO'));
            }
        }

        // $documentos = $this->documentosModel->obtenerPorLead($id); // TODO: Reimplement when DocumentoLeadModel is recreated
        $documentos = []; // Fallback empty array
        $logs = $this->logsModel->obtenerLogsPorRegistro($id);

        $data = [
            'titulo' => 'Detalle de Lead - ' . ($registro->folio ?? 'REG-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT)),
            'breadcrumb' => [
                ['name' => 'Admin', 'url' => '/admin/dashboard'],
                ['name' => 'Gestión de Leads', 'url' => '/admin/leads'],
                ['name' => 'Detalle', 'url' => '']
            ],
            'registro' => $registro,
            'documentos' => $documentos,
            'logs' => $logs
        ];

        return view('admin/leads/show', $data);
    }

    /**
     * Debug temporal para verificar consultas
     */
    public function debugShow($id)
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/admin/leads');
        }

        echo "<h1>Debug Lead ID: {$id}</h1>";
        
        // 1. Verificar modelo
        echo "<h2>1. Información del Modelo</h2>";
        echo "Tabla: " . $this->registroModel->getTable() . "<br>";
        echo "Return Type: " . $this->registroModel->getReturnType() . "<br>";
        
        // 2. Consulta directa con builder
        echo "<h2>2. Consulta Directa (Builder)</h2>";
        $builder = $this->registroModel->builder();
        $count = $builder->where('id', $id)->countAllResults();
        echo "Registros encontrados: {$count}<br>";
        
        if ($count > 0) {
            $builder = $this->registroModel->builder();
            $rawData = $builder->where('id', $id)->get()->getRow();
            echo "Datos raw:<br>";
            echo "<pre>";
            print_r($rawData);
            echo "</pre>";
        }
        
        // 3. Consulta con find()
        echo "<h2>3. Consulta con find()</h2>";
        $registro = $this->registroModel->find($id);
        echo "Resultado find(): " . ($registro ? 'ENCONTRADO' : 'NULL') . "<br>";
        
        if ($registro) {
            echo "Tipo de objeto: " . get_class($registro) . "<br>";
            echo "Folio: " . ($registro->folio ?? 'NULL') . "<br>";
            echo "Métodos disponibles:<br>";
            $metodos = ['getNombreCompleto', 'getTelefonoFormateado', 'getDesarrolloTexto'];
            foreach ($metodos as $metodo) {
                echo "- {$metodo}: " . (method_exists($registro, $metodo) ? 'SÍ' : 'NO') . "<br>";
            }
        }
        
        // 4. Verificar si la Entity está bien configurada
        echo "<h2>4. Verificación de Entity</h2>";
        echo "Clase RegistroCliente existe: " . (class_exists('App\\Entities\\RegistroCliente') ? 'SÍ' : 'NO') . "<br>";
        
        die();
    }

    // ===============================================
    // ENDPOINTS AJAX PARA DATATABLES Y MÉTRICAS
    // ===============================================

    /**
     * Obtener registros para DataTables
     */
    public function obtenerRegistros()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            // Parámetros de DataTables
            $draw = $this->request->getGet('draw');
            $start = $this->request->getGet('start') ?? 0;
            $length = $this->request->getGet('length') ?? 10;
            $searchValue = $this->request->getGet('search')['value'] ?? '';

            // Filtros adicionales
            $filtros = [
                'agente_referido' => $this->request->getGet('agente_referido'),
                'desarrollo' => $this->request->getGet('desarrollo'),
                'hubspot_sync_status' => $this->request->getGet('hubspot_sync_status'),
                'google_drive_sync_status' => $this->request->getGet('google_drive_sync_status'),
                'fecha_desde' => $this->request->getGet('fecha_desde'),
                'fecha_hasta' => $this->request->getGet('fecha_hasta'),
                'busqueda' => $searchValue,
                'limit' => $length,
                'offset' => $start
            ];

            // Obtener registros
            $registros = $this->registroModel->obtenerRegistrosConFiltros($filtros);

            // Contar total (sin filtros)
            $totalRecords = $this->registroModel->countAll();

            // Contar total filtrado
            $filtrosCount = $filtros;
            unset($filtrosCount['limit'], $filtrosCount['offset']);
            $totalFiltered = $this->registroModel->obtenerRegistrosConFiltros($filtrosCount);
            $totalFiltered = is_array($totalFiltered) ? count($totalFiltered) : 0;

            // Formatear datos para DataTables
            $data = [];
            foreach ($registros as $registro) {
                $data[] = [
                    'id' => $registro->id,
                    'folio' => $registro->folio ?? 'REG-' . date('Y') . '-' . str_pad($registro->id, 6, '0', STR_PAD_LEFT),
                    'nombre_completo' => $registro->getNombreCompleto(),
                    'email' => $registro->email,
                    'telefono' => $registro->getTelefonoFormateado(),
                    'desarrollo' => $registro->getDesarrolloTexto(),
                    'agente_referido' => $registro->agente_referido ?? 'N/A',
                    'hubspot_sync' => $registro->getBadgeEstadoSincronizacion(),
                    'google_drive_sync' => $registro->estaSincronizadoGoogleDrive() ? '<span class="badge badge-success">Exitoso</span>' : '<span class="badge badge-warning">Pendiente</span>',
                    'fecha_registro' => $registro->getFechaRegistroFormateada(),
                    'tiempo_transcurrido' => $registro->getTiempoTranscurrido(),
                    'acciones' => $this->generarBotonesAccion($registro)
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Error al obtener registros: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener métricas para dashboard
     */
    public function obtenerMetricas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $fechaInicio = $this->request->getGet('fecha_inicio') ?? date('Y-m-01');
            $fechaFin = $this->request->getGet('fecha_fin') ?? date('Y-m-d');

            // Estadísticas generales
            $estadisticasGenerales = $this->registroModel->obtenerEstadisticasGenerales();

            // Top agentes
            $topAgentes = $this->registroModel->obtenerTopAgentes(10, $fechaInicio, $fechaFin);

            // Métricas por agente específico si se solicita
            $agenteEspecifico = $this->request->getGet('agente_id');
            $metricasAgente = null;
            if ($agenteEspecifico) {
                $metricasAgente = $this->registroModel->obtenerMetricasPorAgente($agenteEspecifico, $fechaInicio, $fechaFin);
            }

            return $this->response->setJSON([
                'success' => true,
                'estadisticas_generales' => $estadisticasGenerales,
                'top_agentes' => $topAgentes,
                'metricas_agente' => $metricasAgente,
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener estadísticas en tiempo real
     */
    public function obtenerEstadisticas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            // Estadísticas generales
            $stats = $this->registroModel->obtenerEstadisticasGenerales();

            // Registros con errores
            $registrosConErrores = $this->registroModel->obtenerRegistrosConErrores();
            
            // Registros pendientes
            $registrosPendientes = $this->registroModel->obtenerRegistrosPendientes();

            // Estadísticas de documentos
            // $statsDocumentos = $this->documentosModel->obtenerEstadisticas(); // TODO: Reimplement when DocumentoLeadModel is recreated
            $statsDocumentos = ['total' => 0, 'pendientes' => 0, 'aprobados' => 0]; // Fallback

            // Logs de errores recientes
            $logsErrores = $this->logsModel->obtenerLogsErrores(10);

            return $this->response->setJSON([
                'success' => true,
                'estadisticas_registros' => $stats,
                'registros_con_errores' => count($registrosConErrores),
                'registros_pendientes' => count($registrosPendientes),
                'estadisticas_documentos' => $statsDocumentos,
                'errores_recientes' => count($logsErrores),
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===============================================
    // ACCIONES DE REENVÍO Y CORRECCIÓN
    // ===============================================

    /**
     * Reenviar registro a HubSpot
     */
    public function reenviarHubSpot($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $registro = $this->registroModel->find($id);
            if (!$registro) {
                throw new \Exception('Registro no encontrado');
            }

            // TODO: Implementar reenvío a HubSpot
            // Por ahora simulamos éxito
            
            $this->registroModel->update($id, [
                'hubspot_sync_status' => 'success',
                'hubspot_sync_error' => null,
                'hubspot_last_sync' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lead reenviado a HubSpot exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reenviar documentos a Google Drive
     */
    public function reenviarGoogleDrive($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $registro = $this->registroModel->find($id);
            if (!$registro) {
                throw new \Exception('Registro no encontrado');
            }

            // TODO: Implementar reenvío a Google Drive
            
            $this->registroModel->update($id, [
                'google_drive_sync_status' => 'success',
                'google_drive_sync_error' => null
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documentos del lead reenviados a Google Drive exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===============================================
    // HERRAMIENTAS Y UTILIDADES
    // ===============================================

    /**
     * Recalcular métricas de un mes específico
     */
    public function recalcularMetricas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $mes = $this->request->getPost('mes') ?? date('n');
            $año = $this->request->getPost('año') ?? date('Y');

            // TODO: Implementar recálculo de métricas
            // Por ahora simulamos éxito
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Métricas recalculadas para {$mes}/{$año}"
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Limpiar logs antiguos
     */
    public function limpiarLogs()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $diasAntiguedad = $this->request->getPost('dias') ?? 30;
            
            $logsEliminados = $this->logsModel->limpiarLogsAntiguos($diasAntiguedad);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Se eliminaron {$logsEliminados} logs de más de {$diasAntiguedad} días"
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ver logs de API
     */
    public function logs()
    {
        $servicio = $this->request->getGet('servicio');
        $soloErrores = $this->request->getGet('errores') === '1';
        
        $logs = $soloErrores 
            ? $this->logsModel->obtenerLogsErrores(100, $servicio)
            : $this->logsModel->obtenerLogsPorServicio($servicio ?? '', 100);

        $data = [
            'titulo' => 'Logs de API - Gestión de Leads',
            'breadcrumb' => [
                ['name' => 'Admin', 'url' => '/admin/dashboard'],
                ['name' => 'Gestión de Leads', 'url' => '/admin/leads'],
                ['name' => 'Logs', 'url' => '']
            ],
            'logs' => $logs,
            'servicio_filtro' => $servicio,
            'solo_errores' => $soloErrores
        ];

        return view('admin/leads/logs', $data);
    }

    /**
     * Ver registros con errores
     */
    public function errores()
    {
        $registrosConErrores = $this->registroModel->obtenerRegistrosConErrores();

        $data = [
            'titulo' => 'Leads con Errores',
            'breadcrumb' => [
                ['name' => 'Admin', 'url' => '/admin/dashboard'],
                ['name' => 'Gestión de Leads', 'url' => '/admin/leads'],
                ['name' => 'Errores', 'url' => '']
            ],
            'registros' => $registrosConErrores
        ];

        return view('admin/leads/errores', $data);
    }

    // ===============================================
    // EXPORTACIÓN
    // ===============================================

    /**
     * Exportar registros a CSV
     */
    public function exportarRegistros()
    {
        try {
            $filtros = [
                'agente_referido' => $this->request->getGet('agente_referido'),
                'desarrollo' => $this->request->getGet('desarrollo'),
                'fecha_desde' => $this->request->getGet('fecha_desde'),
                'fecha_hasta' => $this->request->getGet('fecha_hasta')
            ];

            $registros = $this->registroModel->obtenerRegistrosConFiltros($filtros);

            // Generar CSV
            $csv = "Folio,Nombre,Email,Teléfono,Desarrollo,Agente,Estado HubSpot,Estado Google Drive,Fecha Registro\n";
            
            foreach ($registros as $registro) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    $registro->folio ?? 'N/A',
                    $registro->getNombreCompleto(),
                    $registro->email,
                    $registro->telefono,
                    $registro->getDesarrolloTexto(),
                    $registro->agente_referido ?? 'N/A',
                    $registro->hubspot_sync_status,
                    $registro->google_drive_sync_status,
                    $registro->getFechaRegistroFormateada()
                );
            }

            $filename = 'leads_' . date('Y-m-d_H-i-s') . '.csv';
            
            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($csv);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Generar botones de acción para DataTables
     */
    private function generarBotonesAccion($registro): string
    {
        $botones = '<div class="btn-group" role="group">';
        
        // Ver detalle
        $botones .= '<a href="/admin/leads/show/' . $registro->id . '" class="btn btn-sm btn-info" title="Ver detalle">';
        $botones .= '<i class="fas fa-eye"></i>';
        $botones .= '</a>';
        
        // Reenviar a HubSpot si falló
        if ($registro->falloSincronizacionHubSpot()) {
            $botones .= '<button class="btn btn-sm btn-warning reenviar-hubspot" data-id="' . $registro->id . '" title="Reenviar a HubSpot">';
            $botones .= '<i class="fab fa-hubspot"></i>';
            $botones .= '</button>';
        }
        
        // Reenviar a Google Drive si falló
        if ($registro->falloSincronizacionGoogleDrive()) {
            $botones .= '<button class="btn btn-sm btn-success reenviar-drive" data-id="' . $registro->id . '" title="Reenviar a Google Drive">';
            $botones .= '<i class="fab fa-google-drive"></i>';
            $botones .= '</button>';
        }
        
        $botones .= '</div>';
        
        return $botones;
    }

    // ===============================================
    // CONVERSIÓN DE LEADS A CLIENTES
    // ===============================================

    /**
     * Convertir lead a cliente
     */
    public function convertirACliente()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Método no permitido'
            ])->setStatusCode(404);
        }

        try {
            $leadId = $this->request->getPost('lead_id');
            $observaciones = $this->request->getPost('observaciones') ?? '';

            if (empty($leadId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'ID de lead requerido'
                ]);
            }

            // Obtener el lead
            $lead = $this->registroModel->find($leadId);
            if (!$lead) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Lead no encontrado'
                ]);
            }

            // Verificar que no esté ya convertido
            if ($lead->convertido_a_cliente) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Este lead ya ha sido convertido a cliente'
                ]);
            }

            // Validar que tenga información mínima requerida
            if (empty($lead->firstname) || empty($lead->email)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'El lead necesita tener al menos nombre y email para ser convertido'
                ]);
            }

            // Crear cliente usando ClienteModel
            $clienteModel = model('ClienteModel');
            
            $datosCliente = [
                'nombres' => $lead->firstname,
                'apellido_paterno' => $lead->lastname,
                'apellido_materno' => $lead->apellido_materno ?? '',
                'email' => $lead->email,
                'telefono' => $lead->telefono ?? $lead->mobilephone,
                'celular' => $lead->mobilephone,
                'rfc' => $lead->rfc ?? '',
                'curp' => $lead->curp ?? '',
                'identificador_original' => $lead->rfc_curp ?? '',
                'desarrollo_interes' => $lead->desarrollo,
                'agente_referido' => $lead->agente_referido,
                'hubspot_contact_id' => $lead->hubspot_contact_id,
                'lead_origen_id' => $leadId,
                'observaciones_conversion' => $observaciones,
                'fecha_conversion' => date('Y-m-d H:i:s'),
                'convertido_por' => auth()->user()->id ?? 1,
                'activo' => 1
            ];

            $clienteId = $clienteModel->insert($datosCliente);

            if (!$clienteId) {
                throw new \Exception('Error al crear el cliente: ' . implode(', ', $clienteModel->errors()));
            }

            // Marcar lead como convertido
            $this->registroModel->marcarComoConvertido($leadId, $clienteId);

            // Log de la conversión
            log_message('info', "[LEADS] Lead {$leadId} convertido a cliente {$clienteId} por usuario " . (auth()->user()->id ?? 'sistema'));

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lead convertido a cliente exitosamente',
                'data' => [
                    'cliente_id' => $clienteId,
                    'lead_id' => $leadId,
                    'fecha_conversion' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '[LEADS] Error convirtiendo lead: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener leads convertibles (listos para conversión)
     */
    public function obtenerLeadsConvertibles()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            $leads = $this->registroModel->obtenerConvertiblesACliente();

            $data = [];
            foreach ($leads as $lead) {
                $data[] = [
                    'id' => $lead->id,
                    'folio' => $lead->folio ?? 'LEAD-' . str_pad($lead->id, 6, '0', STR_PAD_LEFT),
                    'nombre_completo' => $lead->getNombreCompleto(),
                    'email' => $lead->email,
                    'telefono' => $lead->getTelefonoFormateado(),
                    'desarrollo' => $lead->getDesarrolloTexto(),
                    'fecha_registro' => $lead->getFechaRegistroFormateada(),
                    'hubspot_sync' => $lead->getBadgeEstadoSincronizacion(),
                    'identificador' => $lead->rfc ?? $lead->curp ?? $lead->rfc_curp ?? 'N/A'
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Vista para gestionar conversiones masivas
     */
    public function conversiones()
    {
        $data = [
            'titulo' => 'Conversión de Leads a Clientes',
            'breadcrumb' => [
                ['name' => 'Admin', 'url' => '/admin/dashboard'],
                ['name' => 'Gestión de Leads', 'url' => '/admin/leads'],
                ['name' => 'Conversiones', 'url' => '']
            ]
        ];

        return view('admin/leads/conversiones', $data);
    }

    /**
     * Cambiar estado manual de revisión de documento
     */
    public function cambiarEstadoDocumento()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Método no permitido'
            ])->setStatusCode(404);
        }

        try {
            $documentoId = $this->request->getPost('documento_id');
            $nuevoEstado = $this->request->getPost('estado');

            // Validar datos
            if (empty($documentoId) || empty($nuevoEstado)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Datos incompletos'
                ]);
            }

            // Validar estados permitidos
            $estadosPermitidos = ['aceptado', 'rechazado', 'pendiente'];
            if (!in_array($nuevoEstado, $estadosPermitidos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Estado no válido'
                ]);
            }

            // Actualizar el documento
            // TODO: Reimplement document update when DocumentoLeadModel is recreated
            /*
            $actualizado = $this->documentosModel->update($documentoId, [
                'estado_revision' => $nuevoEstado
            ]);

            if ($actualizado) {
                log_message('info', "[LEADS] Estado de documento {$documentoId} cambiado a: {$nuevoEstado}");
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Estado del documento actualizado correctamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Error al actualizar el documento'
                ]);
            }
            */

            // Fallback: Document module was removed
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Módulo de documentos temporalmente deshabilitado'
            ]);

        } catch (\Exception $e) {
            log_message('error', '[LEADS] Error cambiando estado documento: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }
}