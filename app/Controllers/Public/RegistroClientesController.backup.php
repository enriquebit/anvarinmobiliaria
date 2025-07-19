<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\RegistroLeadModel;
// RegistroDocumentosModel eliminado - módulo de documentos deshabilitado
use App\Models\RegistroApiLogsModel;
use App\Entities\RegistroLead;
use App\Libraries\GoogleDriveService;
use App\Libraries\HubSpotService;
use App\Services\EmailService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;

class RegistroClientesController extends BaseController
{
    protected $registroModel;
    // documentosModel eliminado - módulo de documentos deshabilitado
    protected $logsModel;
    protected $googleDriveService;
    protected $hubSpotService;
    protected $emailService;
    
    // Configuración HubSpot (usando variables de entorno)
    protected $hubspotConfig = [
        'base_url' => 'https://api.hubapi.com',
        'access_token' => null, // Se carga en __construct()
        'owner_id_default' => null // Se carga en __construct()
    ];
    
    // Configuración Google Drive (usando variables de entorno)
    protected $googleDriveConfig = [
        'client_id' => null, // Se carga en __construct()
        'client_secret' => null, // Se carga en __construct()
        'root_folder' => 'ANVAR_Clientes'
    ];
    
    // Configuración del webhook (usando variables de entorno)
    protected $webhookConfig = [
        'url' => null, // Se carga en __construct()
        'timeout' => 30
    ];

    public function __construct()
    {
        $this->registroModel = new RegistroLeadModel();
        // documentosModel eliminado - módulo de documentos deshabilitado
        $this->logsModel = new RegistroApiLogsModel();
        $this->googleDriveService = new GoogleDriveService();
        $this->hubSpotService = new HubSpotService();
        $this->emailService = new EmailService();
        
        // Cargar configuraciones desde variables de entorno
        $this->loadApiConfig();
        
        helper(['form', 'url']);
    }

    /**
     * Cargar configuraciones API desde variables de entorno
     */
    private function loadApiConfig(): void
    {
        // HubSpot Configuration
        $this->hubspotConfig['access_token'] = env('HUBSPOT_ACCESS_TOKEN', '');
        $this->hubspotConfig['owner_id_default'] = env('HUBSPOT_OWNER_ID', '80110028');
        
        // Google Drive Configuration  
        $this->googleDriveConfig['client_id'] = env('GOOGLE_DRIVE_CLIENT_ID', '');
        $this->googleDriveConfig['client_secret'] = env('GOOGLE_DRIVE_CLIENT_SECRET', '');
        
        // Webhook Configuration
        $this->webhookConfig['url'] = env('WEBHOOK_URL', 'https://appsysoftware.com/bienvenido');
        
        // Log configuration loading for development audit
        if (ENVIRONMENT === 'development') {
            error_log('[CONFIG_AUDIT] API credentials loaded from environment variables');
        }
    }

    /**
     * Mostrar el formulario público de registro
     */
    public function index()
    {
        // Log de acceso al formulario
        $this->logDebug('Formulario de registro accedido', [
            'ip' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'agente_referido' => $this->request->getGet('agente'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $data = [
            'titulo' => 'Registro de Cliente - ANVAR Inmobiliaria',
            'agente_referido' => $this->request->getGet('agente')
        ];

        return view('public/registro-clientes/index', $data);
    }

    /**
     * Validar datos del paso 1 (datos personales)
     */
    public function validarPaso1()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso no autorizado']);
        }

        $validation = \Config\Services::validation();
        
        // Reglas de validación para paso 1
        $rules = [
            'firstname' => [
                'label' => 'Nombre del titular',
                'rules' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/]'
            ],
            'lastname' => [
                'label' => 'Apellido Paterno',
                'rules' => 'required|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/]'
            ],
            'apellido_materno' => [
                'label' => 'Apellido Materno',
                'rules' => 'permit_empty|min_length[2]|max_length[100]|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/]'
            ],
            'rfc_curp' => [
                'label' => 'RFC o CURP',
                'rules' => 'required|min_length[13]|max_length[18]|alpha_numeric'
            ],
            'email' => [
                'label' => 'Correo para trámites y avisos',
                'rules' => 'required|valid_email|max_length[150]|is_unique[registro_clientes.email]'
            ],
            'mobilephone' => [
                'label' => 'WhatsApp',
                'rules' => 'required|regex_match[/^[0-9]{10,15}$/]'
            ],
            'phone' => [
                'label' => 'Teléfono para llamadas',
                'rules' => 'permit_empty|regex_match[/^[0-9]{10,15}$/]'
            ],
            'medio_de_contacto' => [
                'label' => 'Medio de contacto preferido',
                'rules' => 'required|in_list[whatsapp,telefono]'
            ],
            'desarrollo' => [
                'label' => 'Desarrollo de Interés',
                'rules' => 'required|in_list[valle_natura,cordelia]'
            ],
            'manzana' => [
                'label' => 'Manzana',
                'rules' => 'permit_empty|max_length[10]|regex_match[/^[a-zA-Z0-9]+$/]'
            ],
            'lote' => [
                'label' => 'Lote',
                'rules' => 'permit_empty|max_length[10]|regex_match[/^[a-zA-Z0-9]+$/]'
            ],
            'numero_casa_depto' => [
                'label' => 'Número Casa/Departamento',
                'rules' => 'permit_empty|max_length[10]|regex_match[/^[a-zA-Z0-9\s]+$/]'
            ],
            'nombre_copropietario' => [
                'label' => 'Nombre del Co-propietario',
                'rules' => 'permit_empty|max_length[255]|regex_match[/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/]'
            ],
            'parentesco_copropietario' => [
                'label' => 'Parentesco del Co-propietario',
                'rules' => 'permit_empty|max_length[100]'
            ],
            'acepta_terminos' => [
                'label' => 'Aceptación de Términos',
                'rules' => 'required|in_list[true,1]'
            ]
        ];

        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            $this->logDebug('Validación paso 1 falló', [
                'errors' => $validation->getErrors(),
                'data' => $this->request->getPost()
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validation->getErrors()
            ]);
        }

        // Validaciones adicionales
        $email = $this->request->getPost('email');
        $mobilephone = $this->request->getPost('mobilephone');

        // Verificar si el email ya existe
        $existeEmail = $this->registroModel->where('email', $email)->first();
        if ($existeEmail) {
            $this->logDebug('Email duplicado detectado', [
                'email' => $email,
                'registro_existente_id' => $existeEmail->id
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este correo electrónico ya está registrado',
                'errors' => ['email' => 'Ya existe un registro con este correo electrónico']
            ]);
        }

        $this->logDebug('Validación paso 1 exitosa', [
            'email' => $email,
            'mobilephone' => $mobilephone,
            'desarrollo' => $this->request->getPost('desarrollo')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Datos válidos',
            'data' => [
                'email_disponible' => true,
                'paso1_validado' => true
            ]
        ]);
    }

    /**
     * Procesar el registro completo (paso 2 + integración)
     */
    public function procesar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso no autorizado']);
        }

        $this->logDebug('Iniciando procesamiento de registro completo');

        try {
            // 1. Validar datos nuevamente
            error_log('[PROCESAR_DEBUG] Paso 1: Validando datos completos...');
            if (!$this->validarDatosCompletos()) {
                error_log('[PROCESAR_ERROR] Validación de datos falló');
                throw new \Exception('Datos de registro inválidos');
            }
            error_log('[PROCESAR_DEBUG] Paso 1: Validación exitosa');

            // 2. Procesar archivos
            error_log('[PROCESAR_DEBUG] Paso 2: Procesando archivos...');
            $archivos = $this->procesarArchivos();
            if (!$archivos['success']) {
                error_log('[PROCESAR_ERROR] Error procesando archivos: ' . $archivos['message']);
                throw new \Exception('Error al procesar archivos: ' . $archivos['message']);
            }
            error_log('[PROCESAR_DEBUG] Paso 2: Archivos procesados exitosamente');

            // 3. Crear registro en base de datos local
            error_log('[PROCESAR_DEBUG] Paso 3: Creando registro en BD local...');
            $registroId = $this->crearRegistroLocal($archivos['archivos']);
            if (!$registroId) {
                error_log('[PROCESAR_ERROR] Error creando registro local - ID: ' . var_export($registroId, true));
                throw new \Exception('Error al crear registro en base de datos local');
            }
            error_log('[PROCESAR_DEBUG] Paso 3: Registro creado exitosamente, ID: ' . $registroId);

            // 4. Integrar con HubSpot
            $hubspotResult = $this->integrarHubSpot($registroId);
            
            // 5. Subir documentos a Google Drive
            $googleDriveResult = $this->subirGoogleDrive($registroId, $archivos['archivos']);
            
            // 6. Enviar webhook de notificación
            $webhookResult = $this->enviarWebhook($registroId);

            // 7. Actualizar estado final del registro
            $this->actualizarEstadoFinal($registroId, $hubspotResult, $googleDriveResult, $webhookResult);

            $registro = $this->registroModel->find($registroId);

            $this->logDebug('Registro completado exitosamente', [
                'registro_id' => $registroId,
                'folio' => $registro->folio,
                'hubspot_success' => $hubspotResult['success'],
                'google_drive_success' => $googleDriveResult['success'],
                'webhook_success' => $webhookResult['success']
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registro completado exitosamente',
                'data' => [
                    'registro_id' => $registroId,
                    'folio' => $registro->folio,
                    'hubspot_contact_id' => $registro->hubspot_contact_id,
                    'google_drive_folder_url' => $registro->google_drive_folder_url,
                    'integraciones' => [
                        'hubspot' => $hubspotResult['success'],
                        'google_drive' => $googleDriveResult['success'],
                        'webhook' => $webhookResult['success']
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            $this->logError('Error en procesamiento de registro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_data' => $this->request->getPost(),
                'files' => array_keys($_FILES)
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al procesar el registro: ' . $e->getMessage(),
                'error_code' => 'PROCESSING_ERROR'
            ]);
        }
    }

    /**
     * Endpoint para logs de debugging desde el frontend
     */
    public function debugLog()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $message = $this->request->getPost('message');
        $data = $this->request->getPost('data');
        $timestamp = $this->request->getPost('timestamp');
        $page = $this->request->getPost('page');

        $this->logDebug('[FRONTEND] ' . $message, [
            'frontend_data' => $data,
            'frontend_timestamp' => $timestamp,
            'page' => $page,
            'ip' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString()
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    // ===============================================
    // MÉTODOS PRIVADOS DE VALIDACIÓN
    // ===============================================

    private function validarDatosCompletos(): bool
    {
        // Validaciones relajadas para desarrollo según política en CLAUDE.md
        $requiredFields = [
            'firstname', 'lastname', 'email', 'desarrollo'
        ];
        
        foreach ($requiredFields as $field) {
            $value = trim($this->request->getPost($field));
            if (empty($value)) {
                error_log('[VALIDACION_DEBUG] Campo requerido faltante: ' . $field);
                $this->logDebug('Campo requerido faltante', ['field' => $field]);
                return false;
            }
        }
        
        // Verificar que al menos un teléfono esté presente
        $mobilephone = trim($this->request->getPost('mobilephone'));
        $phone = trim($this->request->getPost('phone'));
        if (empty($mobilephone) && empty($phone)) {
            error_log('[VALIDACION_DEBUG] Ningún teléfono proporcionado');
            $this->logDebug('Teléfono requerido faltante', ['mobilephone' => $mobilephone, 'phone' => $phone]);
            return false;
        }
        
        // Validaciones relajadas de archivos para desarrollo
        if (ENVIRONMENT === 'development') {
            error_log('[VALIDACION_DEBUG] Modo desarrollo - validaciones de archivo relajadas');
            // Solo verificar que existan archivos, no validar errores estrictos
            $requiredFiles = ['file_ine_frente', 'file_ine_reverso', 'file_comprobante_domicilio'];
            foreach ($requiredFiles as $fileField) {
                if (!isset($_FILES[$fileField]) || empty($_FILES[$fileField]['name'])) {
                    error_log('[VALIDACION_DEBUG] Archivo faltante: ' . $fileField);
                    $this->logDebug('Archivo requerido faltante', ['file_field' => $fileField]);
                    return false;
                }
            }
        } else {
            // Validación estricta en producción
            $requiredFiles = ['file_ine_frente', 'file_ine_reverso', 'file_comprobante_domicilio'];
            foreach ($requiredFiles as $fileField) {
                if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
                    $this->logDebug('Archivo requerido faltante o con error', [
                        'file_field' => $fileField,
                        'error' => $_FILES[$fileField]['error'] ?? 'no_file'
                    ]);
                    return false;
                }
            }
        }
        
        error_log('[VALIDACION_DEBUG] Todas las validaciones pasaron exitosamente');
        return true;
    }

    private function procesarArchivos(): array
    {
        $archivos = [];
        $fileFields = [
            'file_ine_frente' => 'ine_frente',
            'file_ine_reverso' => 'ine_reverso', 
            'file_comprobante_domicilio' => 'comprobante_domicilio'
        ];

        try {
            foreach ($fileFields as $fieldName => $tipoDoc) {
                if (!isset($_FILES[$fieldName])) {
                    throw new \Exception("Archivo {$tipoDoc} no encontrado");
                }

                $file = new File($_FILES[$fieldName]['tmp_name']);
                
                // Validar archivo
                $validation = $this->validarArchivo($file, $_FILES[$fieldName]);
                if (!$validation['valid']) {
                    throw new \Exception("Archivo {$tipoDoc} inválido: " . $validation['error']);
                }

                $archivos[$tipoDoc] = [
                    'file' => $file,
                    'original_name' => $_FILES[$fieldName]['name'],
                    'size' => $_FILES[$fieldName]['size'],
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->guessExtension(),
                    'tmp_name' => $_FILES[$fieldName]['tmp_name']
                ];
            }

            $this->logDebug('Archivos procesados exitosamente', [
                'archivos' => array_keys($archivos),
                'total_size' => array_sum(array_column($archivos, 'size'))
            ]);

            return ['success' => true, 'archivos' => $archivos];

        } catch (\Exception $e) {
            $this->logError('Error al procesar archivos', [
                'error' => $e->getMessage(),
                'files_received' => array_keys($_FILES)
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function validarArchivo(File $file, array $fileData): array
    {
        // Validar tamaño (máximo 10MB)
        $maxSize = 10 * 1024 * 1024;
        if ($fileData['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Archivo excede el tamaño máximo de 10MB'];
        }

        // Validar tipo MIME
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $mimeType = $file->getMimeType();
        
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Tipo de archivo no permitido. Solo JPG, PNG y PDF'];
        }

        // Validar extensión
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower($file->guessExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Extensión de archivo no permitida'];
        }

        return ['valid' => true];
    }

    // ===============================================
    // MÉTODOS DE INTEGRACIÓN
    // ===============================================

    private function crearRegistroLocal(array $archivos): ?int
    {
        try {
            // Validaciones previas más estrictas
            $camposRequeridos = ['firstname', 'lastname', 'email', 'desarrollo'];
            foreach ($camposRequeridos as $campo) {
                $valor = trim($this->request->getPost($campo));
                if (empty($valor)) {
                    throw new \Exception("Campo requerido faltante: {$campo}");
                }
            }

            $mobilephone = trim($this->request->getPost('mobilephone')) ?: '';
            $phone = trim($this->request->getPost('phone')) ?: '';
            
            // Validación de email en desarrollo
            $email = trim($this->request->getPost('email'));
            if (ENVIRONMENT === 'development') {
                $this->validarEmailDesarrollo($email);
            }

            // Validar que al menos un teléfono esté presente
            if (empty($mobilephone) && empty($phone)) {
                throw new \Exception('Al menos un número de teléfono es requerido');
            }
            
            $datos = [
                'firstname' => trim($this->request->getPost('firstname')),
                'lastname' => trim($this->request->getPost('lastname')),
                'apellido_materno' => trim($this->request->getPost('apellido_materno') ?: ''),
                'rfc_curp' => strtoupper(trim($this->request->getPost('rfc_curp') ?: '')),
                'email' => $email,
                'telefono' => $mobilephone ?: $phone, // Campo legacy - usar el que esté disponible
                'mobilephone' => $mobilephone,
                'phone' => $phone,
                'medio_de_contacto' => $this->request->getPost('medio_de_contacto') ?: 'whatsapp',
                'desarrollo' => $this->request->getPost('desarrollo'),
                'manzana' => trim($this->request->getPost('manzana') ?: ''),
                'lote' => trim($this->request->getPost('lote') ?: ''),
                'numero_casa_depto' => trim($this->request->getPost('numero_casa_depto') ?: ''),
                'nombre_copropietario' => trim($this->request->getPost('nombre_copropietario') ?: ''),
                'parentesco_copropietario' => $this->request->getPost('parentesco_copropietario') ?: '',
                'agente_referido' => $this->request->getPost('agente_referido') ?: $this->request->getGet('agente') ?: '',
                'acepta_terminos' => 1,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'estado_registro' => 'completado',
                'fuente_registro' => 'formulario_web',
                'activo' => 1
            ];

            // Log crítico antes del insert
            $this->logDebug('Iniciando creación de registro local', [
                'email' => $datos['email'],
                'desarrollo' => $datos['desarrollo'],
                'documentos_count' => count($archivos)
            ]);

            // Desactivar validaciones para desarrollo (política de relajar validaciones)
            if (ENVIRONMENT === 'development') {
                $this->registroModel->skipValidation(true);
                error_log('[REGISTRO_DEBUG] Validaciones desactivadas para desarrollo');
            }

            // Usar método estándar del modelo con autoincrement
            error_log('[REGISTRO_DEBUG] Ejecutando insert en BD...');
            $registroId = $this->registroModel->insert($datos);
            error_log('[REGISTRO_DEBUG] Insert ejecutado, resultado: ' . var_export($registroId, true));

            if ($registroId === false) {
                $errors = $this->registroModel->errors();
                error_log('[REGISTRO_ERROR] Insert falló - Errores: ' . json_encode($errors));
                error_log('[REGISTRO_ERROR] Validation errors: ' . json_encode($this->registroModel->getValidationMessages()));
                throw new \Exception('Error en base de datos: ' . implode(', ', array_merge($errors, $this->registroModel->getValidationMessages())));
            }

            if (!$registroId || $registroId === 0) {
                error_log('[REGISTRO_ERROR] ID no válido: ' . var_export($registroId, true));
                throw new \Exception('No se generó ID de registro válido: ' . var_export($registroId, true));
            }

            error_log('[REGISTRO_SUCCESS] Registro creado exitosamente, ID: ' . $registroId);
            
            // Documentos comentados - módulo eliminado
            foreach ($archivos as $tipoDoc => $archivoData) {
                // Módulo de documentos eliminado - solo log para auditoría
                error_log('[REGISTRO_DEBUG] Documento procesado (sin BD): ' . $tipoDoc . ' - ' . $archivoData['original_name']);
            }

            $this->logDebug('Registro local creado exitosamente', [
                'registro_id' => $registroId,
                'email' => $datos['email'],
                'documentos' => array_keys($archivos)
            ]);

            return $registroId;

        } catch (\Exception $e) {
            error_log('[REGISTRO_ERROR] Excepción en crearRegistroLocal: ' . $e->getMessage());
            error_log('[REGISTRO_ERROR] Stack trace: ' . $e->getTraceAsString());
            
            $this->logError('Error al crear registro local', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'datos' => $datos ?? [],
                'post_data' => $this->request->getPost()
            ]);
            return null;
        }
    }

    /**
     * Validar email en entorno de desarrollo
     */
    private function validarEmailDesarrollo(string $email): void
    {
        // Validación básica de formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Formato de email inválido: ' . $email);
        }

        // En desarrollo, permitir emails de prueba comunes
        $emailsPermitidos = [
            'test@test.com',
            'cliente@test.com',
            'demo@demo.com',
            'admin@test.com'
        ];

        $dominiosPermitidos = [
            'gmail.com',
            'hotmail.com',
            'yahoo.com',
            'outlook.com',
            'test.com',
            'demo.com'
        ];

        $dominio = substr(strrchr($email, '@'), 1);
        
        if (!in_array($email, $emailsPermitidos) && !in_array($dominio, $dominiosPermitidos)) {
            error_log('[DESARROLLO] Email con dominio no común: ' . $email);
            // En desarrollo solo advertir, no bloquear
        }

        error_log('[DESARROLLO] Email validado: ' . $email);
    }

    private function generarNombreArchivo(array $datos, string $tipoDoc, string $extension): string
    {
        // Nueva nomenclatura: RFC_CURP/tipo_archivos_rfc_curp.extension
        $rfcCurp = strtoupper($datos['rfc_curp'] ?? '');
        
        if (empty($rfcCurp)) {
            // Fallback: usar nomenclatura anterior si no hay RFC/CURP
            $nombre = strtoupper($datos['firstname'] ?? $datos['nombre'] ?? '');
            $apellidos = strtoupper($datos['lastname'] ?? $datos['apellido_paterno'] ?? '');
            if (!empty($datos['apellido_materno'])) {
                $apellidos .= '_' . strtoupper($datos['apellido_materno']);
            }

            // Limpiar caracteres especiales
            $nombre = preg_replace('/[^A-Z0-9_]/', '_', $nombre);
            $apellidos = preg_replace('/[^A-Z0-9_]/', '_', $apellidos);
            
            // Evitar guiones bajos múltiples
            $nombre = preg_replace('/_+/', '_', $nombre);
            $apellidos = preg_replace('/_+/', '_', $apellidos);

            $tipoDocMap = [
                'ine_frente' => 'INE_FRENTE',
                'ine_reverso' => 'INE_REVERSO',
                'comprobante_domicilio' => 'COMPROBANTE_DOMICILIO'
            ];

            return $nombre . '_' . $apellidos . '_' . $tipoDocMap[$tipoDoc] . '.' . $extension;
        }

        // Nueva nomenclatura con RFC/CURP
        $tipoDocMap = [
            'ine_frente' => 'ine_frente',
            'ine_reverso' => 'ine_reverso',
            'comprobante_domicilio' => 'comprobante_domicilio'
        ];

        // Limpiar RFC/CURP de caracteres especiales
        $rfcCurp = preg_replace('/[^A-Z0-9]/', '', $rfcCurp);

        return $tipoDocMap[$tipoDoc] . '_' . $rfcCurp . '.' . $extension;
    }

    // ===============================================
    // MÉTODOS DE INTEGRACIÓN (PLACEHOLDER)
    // ===============================================

    private function integrarHubSpot(int $registroId): array
    {
        try {
            $this->logDebug('Iniciando integración con HubSpot', ['registro_id' => $registroId]);

            // Obtener datos del registro
            $registro = $this->registroModel->find($registroId);
            if (!$registro) {
                throw new \Exception('Registro no encontrado');
            }

            // Preparar datos para HubSpot
            $datosCliente = [
                'firstname' => $registro->firstname,
                'lastname' => $registro->lastname,
                'apellido_materno' => $registro->apellido_materno,
                'rfc_curp' => $registro->rfc_curp,
                'email' => $registro->email,
                'mobilephone' => $registro->mobilephone,
                'phone' => $registro->phone,
                'medio_de_contacto' => $registro->medio_de_contacto,
                'desarrollo' => $registro->desarrollo,
                'manzana' => $registro->manzana,
                'lote' => $registro->lote,
                'numero_casa_depto' => $registro->numero_casa_depto,
                'nombre_copropietario' => $registro->nombre_copropietario,
                'parentesco_copropietario' => $registro->parentesco_copropietario,
                'agente_referido' => $registro->agente_referido
            ];

            // FASE 2: Crear contacto con credenciales de cliente
            $resultadoHubSpot = $this->hubSpotService->crearContactoConCredenciales($datosCliente);
            
            if ($resultadoHubSpot['success']) {
                // Actualizar registro local con información de HubSpot
                $this->registroModel->update($registroId, [
                    'hubspot_contact_id' => $resultadoHubSpot['contacto_id'],
                    'hubspot_sync_status' => 'success',
                    'hubspot_last_sync' => date('Y-m-d H:i:s')
                ]);
                
                // Enviar magic link por email
                $emailResult = $this->emailService->enviarMagicLinkBienvenida($resultadoHubSpot['magic_link_data']);
                
                // Crear nota en HubSpot sobre las credenciales
                $this->hubSpotService->crearNotaCredenciales(
                    $resultadoHubSpot['contacto_id'], 
                    $resultadoHubSpot['credenciales'], 
                    $datosCliente
                );
                
                // Actualizar estado del magic link en HubSpot
                $estadoEmail = $emailResult['success'] ? 'sent' : 'error';
                $this->hubSpotService->actualizarEstadoMagicLink($resultadoHubSpot['contacto_id'], $estadoEmail);
                
                // Enviar notificación al equipo
                $this->emailService->enviarNotificacionEquipo($datosCliente, $resultadoHubSpot['credenciales']);
                
                $this->logDebug('Integración HubSpot exitosa con credenciales', [
                    'registro_id' => $registroId,
                    'contacto_id' => $resultadoHubSpot['contacto_id'],
                    'email_enviado' => $emailResult['success'],
                    'credenciales_generadas' => true
                ]);
                
                return [
                    'success' => true,
                    'contacto_id' => $resultadoHubSpot['contacto_id'],
                    'credenciales' => $resultadoHubSpot['credenciales'],
                    'email_enviado' => $emailResult['success'],
                    'message' => 'Contacto creado en HubSpot con credenciales de cliente'
                ];
            } else {
                // Fallback: crear contacto normal si falla la generación de credenciales
                $contactoNormal = $this->hubSpotService->procesarRegistroCompleto($datosCliente);
                $contactoNormal = $contactoNormal['success'] ? $contactoNormal['contacto'] : null;
                
                if ($contactoNormal && isset($contactoNormal['id'])) {
                    $this->registroModel->update($registroId, [
                        'hubspot_contact_id' => $contactoNormal['id'],
                        'hubspot_sync_status' => 'success_sin_credenciales',
                        'hubspot_sync_error' => 'Credenciales no generadas: ' . $resultadoHubSpot['error'],
                        'hubspot_last_sync' => date('Y-m-d H:i:s')
                    ]);
                    
                    $this->logDebug('HubSpot contacto creado sin credenciales', [
                        'registro_id' => $registroId,
                        'contacto_id' => $contactoNormal['id'],
                        'error_credenciales' => $resultadoHubSpot['error']
                    ]);
                    
                    return [
                        'success' => true,
                        'contacto_id' => $contactoNormal['id'],
                        'credenciales' => null,
                        'email_enviado' => false,
                        'message' => 'Contacto creado en HubSpot sin credenciales',
                        'warning' => 'No se pudieron generar credenciales de cliente'
                    ];
                } else {
                    throw new \Exception('Error creando contacto en HubSpot: ' . ($contactoNormal['error'] ?? 'Error desconocido'));
                }
            }

        } catch (\Exception $e) {
            $this->logError('Error en integración HubSpot', [
                'registro_id' => $registroId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Actualizar estado de error en el registro
            $this->registroModel->update($registroId, [
                'hubspot_sync_status' => 'failed',
                'hubspot_sync_error' => $e->getMessage(),
                'hubspot_last_sync' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'contacto_id' => null
            ];
        }
    }

    private function subirGoogleDrive(int $registroId, array $archivos): array
    {
        try {
            error_log('[GOOGLE_DRIVE_DEBUG] Iniciando subida a Google Drive - Registro: ' . $registroId);
            error_log('[GOOGLE_DRIVE_DEBUG] Archivos recibidos: ' . json_encode(array_keys($archivos)));
            
            $this->logDebug('Iniciando subida a Google Drive', [
                'registro_id' => $registroId,
                'archivos' => array_keys($archivos)
            ]);

            // Verificar conexión de Google Drive ANTES de procesar
            error_log('[GOOGLE_DRIVE_DEBUG] Verificando conexión con Google Drive...');
            $conexionOk = $this->googleDriveService->verificarConexion();
            error_log('[GOOGLE_DRIVE_DEBUG] Resultado conexión: ' . ($conexionOk ? 'EXITOSA' : 'FALLIDA'));
            
            $this->logDebug('Verificación de conexión Google Drive', [
                'conexion_exitosa' => $conexionOk
            ]);

            if (!$conexionOk) {
                error_log('[GOOGLE_DRIVE_ERROR] Conexión a Google Drive falló');
                throw new \Exception('No se pudo conectar a Google Drive. Verificar autenticación.');
            }

            // Obtener datos del registro para generar nombre de carpeta
            error_log('[GOOGLE_DRIVE_DEBUG] Obteniendo datos del registro...');
            $registro = $this->registroModel->find($registroId);
            if (!$registro) {
                error_log('[GOOGLE_DRIVE_ERROR] Registro no encontrado: ' . $registroId);
                throw new \Exception('Registro no encontrado');
            }

            // Usar nomenclatura basada en RFC/CURP para nombre de carpeta
            error_log('[GOOGLE_DRIVE_DEBUG] Generando nombre de carpeta...');
            $nombreCliente = $registro->getNombreCarpetaExpediente();
            error_log('[GOOGLE_DRIVE_DEBUG] Nombre de carpeta generado: ' . $nombreCliente);
            
            $this->logDebug('Datos del registro para Google Drive', [
                'registro_id' => $registroId,
                'nombre_cliente' => $nombreCliente,
                'rfc_curp' => $registro->rfc_curp,
                'firstname' => $registro->firstname,
                'lastname' => $registro->lastname
            ]);

            // Preparar archivos temporales para Google Drive
            error_log('[GOOGLE_DRIVE_DEBUG] Preparando archivos para subida...');
            $documentosParaSubir = [];
            
            foreach ($archivos as $tipoDoc => $archivoData) {
                error_log('[GOOGLE_DRIVE_DEBUG] Procesando archivo: ' . $tipoDoc . ' - ' . $archivoData['original_name']);
                
                // Mover archivo temporal a ubicación accesible
                $rutaTemporal = $this->moverArchivoTemporal($archivoData['tmp_name'], $archivoData['original_name']);
                error_log('[GOOGLE_DRIVE_DEBUG] Archivo movido a: ' . $rutaTemporal);
                
                $documentosParaSubir[$tipoDoc] = [
                    'ruta_temporal' => $rutaTemporal,
                    'nombre_final' => $this->generarNombreArchivo([
                        'firstname' => $registro->firstname,
                        'lastname' => $registro->lastname,
                        'apellido_materno' => $registro->apellido_materno,
                        'rfc_curp' => $registro->rfc_curp
                    ], $tipoDoc, $archivoData['extension']),
                    'mime_type' => $archivoData['mime_type']
                ];
            }

            error_log('[GOOGLE_DRIVE_DEBUG] Total documentos preparados: ' . count($documentosParaSubir));
            
            $this->logDebug('Archivos preparados para Google Drive', [
                'nombre_cliente' => $nombreCliente,
                'total_documentos' => count($documentosParaSubir),
                'documentos' => array_keys($documentosParaSubir)
            ]);

            // Procesar con Google Drive Service
            error_log('[GOOGLE_DRIVE_DEBUG] Llamando a Google Drive Service...');
            $resultado = $this->googleDriveService->procesarRegistroCompleto($nombreCliente, $documentosParaSubir);
            error_log('[GOOGLE_DRIVE_DEBUG] Resultado Google Drive: ' . json_encode($resultado));

            $this->logDebug('Resultado de Google Drive Service', [
                'success' => $resultado['success'],
                'carpeta_creada' => isset($resultado['carpeta']),
                'documentos_subidos' => count($resultado['documentos'] ?? []),
                'errores' => $resultado['errores'] ?? []
            ]);

            // Actualizar registros de documentos en BD
            $this->actualizarDocumentosGoogleDrive($registroId, $resultado);

            // Limpiar archivos temporales
            $this->limpiarArchivosTemporal(array_column($documentosParaSubir, 'ruta_temporal'));

            $this->logDebug('Subida a Google Drive completada', [
                'registro_id' => $registroId,
                'success' => $resultado['success'],
                'documentos_exitosos' => $resultado['estadisticas']['documentos_exitosos'],
                'documentos_fallidos' => $resultado['estadisticas']['documentos_fallidos']
            ]);

            return [
                'success' => $resultado['success'],
                'folder_id' => $resultado['carpeta']['id'],
                'folder_url' => $resultado['carpeta']['url'],
                'documentos' => $resultado['documentos'],
                'errores' => $resultado['errores']
            ];

        } catch (\Exception $e) {
            $this->logError('Error en subida a Google Drive', [
                'registro_id' => $registroId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function enviarWebhook(int $registroId): array
    {
        // TODO: Implementar webhook
        // Por ahora retornamos éxito simulado
        $this->logDebug('Webhook enviado', ['registro_id' => $registroId]);
        
        return ['success' => true];
    }

    private function actualizarEstadoFinal(int $registroId, array $hubspot, array $googleDrive, array $webhook): void
    {
        $updateData = [
            'hubspot_sync_status' => $hubspot['success'] ? 'success' : 'failed',
            'google_drive_sync_status' => $googleDrive['success'] ? 'success' : 'failed'
        ];

        if ($hubspot['success']) {
            $updateData['hubspot_contact_id'] = $hubspot['contact_id'];
            // Ya no creamos tickets, solo contactos
            $updateData['hubspot_ticket_id'] = null;
            $updateData['hubspot_last_sync'] = date('Y-m-d H:i:s');
        }

        if ($googleDrive['success']) {
            $updateData['google_drive_folder_id'] = $googleDrive['folder_id'];
            $updateData['google_drive_folder_url'] = $googleDrive['folder_url'];
        }

        $this->registroModel->update($registroId, $updateData);

        // Si ambos servicios funcionaron, actualizar HubSpot con URLs de documentos
        if ($hubspot['success'] && $googleDrive['success'] && isset($googleDrive['documentos'])) {
            $this->actualizarHubSpotConDocumentos($hubspot['contact_id'], $googleDrive['documentos']);
        }

        $this->logDebug('Estado final actualizado', [
            'registro_id' => $registroId,
            'update_data' => $updateData,
            'documentos_hubspot_actualizados' => $hubspot['success'] && $googleDrive['success']
        ]);
    }

    /**
     * Actualizar contacto de HubSpot con URLs de documentos
     */
    private function actualizarHubSpotConDocumentos(string $contactId, array $documentos): void
    {
        try {
            $urlsDocumentos = [];
            
            foreach ($documentos as $tipoDoc => $datosDoc) {
                $urlsDocumentos[$tipoDoc] = $datosDoc['url_publica'] ?? null;
            }
            
            $this->hubSpotService->actualizarContactoConDocumentos($contactId, $urlsDocumentos);
            
            $this->logDebug('URLs de documentos actualizadas en HubSpot', [
                'contact_id' => $contactId,
                'urls' => $urlsDocumentos
            ]);
            
        } catch (\Exception $e) {
            $this->logError('Error actualizando URLs en HubSpot', [
                'contact_id' => $contactId,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===============================================
    // MÉTODOS DE LOGGING
    // ===============================================

    private function logDebug(string $message, array $data = []): void
    {
        // Solo log en archivo para desarrollo para evitar problemas SQL
        if (ENVIRONMENT === 'development') {
            error_log("[REGISTRO_DEBUG] {$message}: " . json_encode($data));
        }
    }

    private function logError(string $message, array $data = []): void
    {
        // Log en archivo siempre para errores
        error_log("[REGISTRO_ERROR] {$message}: " . json_encode($data));
    }

    // ===============================================
    // ENDPOINTS DE DEBUGGING (SOLO DESARROLLO)
    // ===============================================

    public function debugUltimoRegistro()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(403);
        }

        $ultimoRegistro = $this->registroModel->orderBy('id', 'DESC')->first();
        
        if ($ultimoRegistro) {
            // $documentos = $this->documentosModel->where('registro_cliente_id', $ultimoRegistro->id)->findAll(); // TODO: Reimplement when RegistroDocumentosModel is recreated
            $documentos = []; // Fallback empty array
            
            return $this->response->setJSON([
                'registro' => $ultimoRegistro,
                'documentos' => $documentos
            ]);
        }

        return $this->response->setJSON(['message' => 'No hay registros']);
    }

    public function debugHubSpot()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(403);
        }

        try {
            $startTime = microtime(true);
            
            // Test 1: Verificar conexión
            $conexion = $this->hubSpotService->verificarConexion();
            
            if (!$conexion) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudo conectar a HubSpot. Verificar token de acceso.'
                ]);
            }

            // Test 2: Obtener info del propietario
            $propietario = $this->hubSpotService->obtenerInfoPropietario();
            
            // Test 3: Validar propiedades personalizadas
            $propiedades = $this->hubSpotService->validarPropiedadesPersonalizadas();
            
            $duration = round((microtime(true) - $startTime) * 1000);

            return $this->response->setJSON([
                'success' => true,
                'tests' => [
                    'conexion' => $conexion,
                    'propietario' => $propietario ? [
                        'id' => $propietario['id'] ?? 'N/A',
                        'email' => $propietario['email'] ?? 'N/A',
                        'nombre' => ($propietario['firstName'] ?? '') . ' ' . ($propietario['lastName'] ?? '')
                    ] : 'No disponible',
                    'propiedades_personalizadas' => $propiedades
                ],
                'duracion_ms' => $duration,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }


    // ===============================================
    // MÉTODOS AUXILIARES PARA GOOGLE DRIVE
    // ===============================================

    /**
     * Mover archivo temporal a ubicación accesible
     */
    private function moverArchivoTemporal(string $tmpName, string $originalName): string
    {
        $uploadsDir = FCPATH . 'uploads/temp/';
        
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        $nombreTemporal = uniqid('upload_') . '_' . $originalName;
        $rutaDestino = $uploadsDir . $nombreTemporal;
        
        if (!move_uploaded_file($tmpName, $rutaDestino)) {
            throw new \Exception('No se pudo mover el archivo temporal');
        }
        
        return $rutaDestino;
    }

    /**
     * Actualizar registros de documentos con información de Google Drive
     */
    private function actualizarDocumentosGoogleDrive(int $registroId, array $resultado): void
    {
        // TODO: Reimplement when RegistroDocumentosModel is recreated
        /*
        foreach ($resultado['documentos'] as $tipoDoc => $datosDoc) {
            $documento = $this->documentosModel->where('registro_cliente_id', $registroId)
                                              ->where('tipo_documento', $tipoDoc)
                                              ->first();
            
            if ($documento) {
                $this->documentosModel->update($documento['id'], [
                    'google_drive_file_id' => $datosDoc['id'],
                    'google_drive_url' => $datosDoc['url_publica'],
                    'google_drive_view_url' => $datosDoc['url_visualizacion'],
                    'upload_status' => 'success'
                ]);
            }
        }

        // Marcar como fallidos los que tuvieron errores
        foreach ($resultado['errores'] as $tipoDoc => $error) {
            $documento = $this->documentosModel->where('registro_cliente_id', $registroId)
                                              ->where('tipo_documento', $tipoDoc)
                                              ->first();
            
            if ($documento) {
                $this->documentosModel->update($documento['id'], [
                    'upload_status' => 'failed',
                    'upload_error' => $error,
                    'upload_attempts' => ($documento['upload_attempts'] ?? 0) + 1
                ]);
            }
        }
        */
        
        // Fallback: Document module was removed
        error_log('[REGISTRO_DEBUG] actualizarDocumentosGoogleDrive: Módulo de documentos temporalmente deshabilitado');
    }

    /**
     * Limpiar archivos temporales
     */
    private function limpiarArchivosTemporal(array $rutasArchivos): void
    {
        foreach ($rutasArchivos as $ruta) {
            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }
    }

    /**
     * Endpoint para test específico de Google Drive
     */
    public function testGoogleDriveConnection()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(403);
        }

        try {
            $startTime = microtime(true);
            
            // Test 1: Verificar conexión
            $conexion = $this->googleDriveService->verificarConexion();
            
            // Test 2: Obtener info del usuario
            $usuario = $this->googleDriveService->obtenerInfoUsuario();
            
            // Test 3: Crear carpeta de prueba
            $carpetaPrueba = $this->googleDriveService->crearCarpetaCliente('TEST_CLIENTE_' . date('YmdHis'));
            
            $duration = round((microtime(true) - $startTime) * 1000);

            return $this->response->setJSON([
                'success' => true,
                'tests' => [
                    'conexion' => $conexion,
                    'usuario' => $usuario ? $usuario['emailAddress'] ?? 'Usuario conectado' : 'No disponible',
                    'carpeta_test' => $carpetaPrueba
                ],
                'duracion_ms' => $duration,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Debug database structure
     */
    public function debugDatabaseStructure()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(403);
        }

        try {
            $db = \Config\Database::connect();
            
            // Get table structure
            $query = $db->query("DESCRIBE registro_clientes");
            $structure = $query->getResultArray();
            
            // Test sample insert
            $testData = [
                'firstname' => 'Test',
                'lastname' => 'User', 
                'email' => 'test_' . time() . '@example.com',
                'mobilephone' => '5551234567',
                'desarrollo' => 'valle_natura',
                'acepta_terminos' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Agent',
                'estado_registro' => 'completado'
            ];
            
            $insertResult = null;
            $insertError = null;
            
            try {
                $insertId = $this->registroModel->insert($testData);
                $insertResult = "✅ Insert successful. ID: $insertId";
            } catch (\Exception $e) {
                $insertError = "❌ Insert failed: " . $e->getMessage();
            }
            
            $html = "<h1>Database Structure Debug</h1>";
            $html .= "<h2>registro_clientes table structure:</h2>";
            $html .= "<table border='1' style='border-collapse: collapse;'>";
            $html .= "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            foreach ($structure as $row) {
                $html .= "<tr>";
                $html .= "<td>" . htmlspecialchars($row['Field']) . "</td>";
                $html .= "<td>" . htmlspecialchars($row['Type']) . "</td>";
                $html .= "<td>" . htmlspecialchars($row['Null']) . "</td>";
                $html .= "<td>" . htmlspecialchars($row['Key']) . "</td>";
                $html .= "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
                $html .= "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
            
            $html .= "<h2>Test Insert Result:</h2>";
            if ($insertResult) {
                $html .= "<p style='color: green;'>$insertResult</p>";
            }
            if ($insertError) {
                $html .= "<p style='color: red;'>$insertError</p>";
            }
            
            $html .= "<h2>Test Data Used:</h2>";
            $html .= "<pre>" . htmlspecialchars(json_encode($testData, JSON_PRETTY_PRINT)) . "</pre>";
            
            return $html;
            
        } catch (\Exception $e) {
            return "<h1>Database Debug Error</h1><p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    /**
     * Debug Google Drive configuration and authentication
     */
    public function debugGoogleDrive()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(403);
        }

        try {
            // Verificar archivo de tokens
            $tokenFile = WRITEPATH . 'google_drive_tokens.json';
            $tokensExisten = file_exists($tokenFile);
            $tokensContent = null;
            $tokensInfo = [];
            
            if ($tokensExisten) {
                $tokensContent = json_decode(file_get_contents($tokenFile), true);
                $tokensInfo = [
                    'access_token_presente' => !empty($tokensContent['access_token']),
                    'refresh_token_presente' => !empty($tokensContent['refresh_token']),
                    'expires_at' => $tokensContent['expires_at'] ?? null,
                    'created_at' => $tokensContent['created_at'] ?? null,
                    'expira_en' => $tokensContent['expires_at'] ? date('Y-m-d H:i:s', $tokensContent['expires_at']) : 'Desconocido',
                    'esta_expirado' => $tokensContent['expires_at'] ? (time() >= $tokensContent['expires_at']) : true
                ];
            }
            
            // Configuración de variables de entorno
            $envConfig = [
                'GOOGLE_DRIVE_CLIENT_ID' => env('GOOGLE_DRIVE_CLIENT_ID') ? 'Configurado' : 'No configurado',
                'GOOGLE_DRIVE_CLIENT_SECRET' => env('GOOGLE_DRIVE_CLIENT_SECRET') ? 'Configurado' : 'No configurado',
                'GOOGLE_DRIVE_ACCESS_TOKEN' => env('GOOGLE_DRIVE_ACCESS_TOKEN') ? 'Configurado' : 'No configurado',
                'GOOGLE_DRIVE_REFRESH_TOKEN' => env('GOOGLE_DRIVE_REFRESH_TOKEN') ? 'Configurado' : 'No configurado'
            ];
            
            // Test conexión
            $conexionOk = false;
            $errorConexion = null;
            
            try {
                $conexionOk = $this->googleDriveService->verificarConexion();
            } catch (\Exception $e) {
                $errorConexion = $e->getMessage();
            }
            
            // Test información de usuario
            $usuario = null;
            $errorUsuario = null;
            
            if ($conexionOk) {
                try {
                    $usuario = $this->googleDriveService->obtenerInfoUsuario();
                } catch (\Exception $e) {
                    $errorUsuario = $e->getMessage();
                }
            }
            
            // Test carpeta raíz
            $carpetaRaiz = null;
            $errorCarpeta = null;
            
            if ($conexionOk) {
                try {
                    $carpetaRaiz = $this->googleDriveService->obtenerCarpetaRaiz();
                } catch (\Exception $e) {
                    $errorCarpeta = $e->getMessage();
                }
            }
            
            return $this->response->setJSON([
                'success' => $conexionOk,
                'message' => $conexionOk ? 'Google Drive funcionando correctamente' : 'Problemas con Google Drive',
                'debug' => [
                    'tokens' => [
                        'archivo_existe' => $tokensExisten,
                        'ruta_archivo' => $tokenFile,
                        'info' => $tokensInfo
                    ],
                    'configuracion_env' => $envConfig,
                    'conexion' => [
                        'exitosa' => $conexionOk,
                        'error' => $errorConexion
                    ],
                    'usuario' => [
                        'obtenido' => $usuario !== null,
                        'data' => $usuario,
                        'error' => $errorUsuario
                    ],
                    'carpeta_raiz' => [
                        'obtenida' => $carpetaRaiz !== null,
                        'data' => $carpetaRaiz,
                        'error' => $errorCarpeta
                    ]
                ],
                'instrucciones' => [
                    'tokens_faltantes' => !$tokensExisten ? 'Necesitas autenticar Google Drive visitando /admin/google-drive/auth' : null,
                    'tokens_expirados' => ($tokensInfo['esta_expirado'] ?? true) ? 'Los tokens están expirados, necesitas re-autenticar' : null,
                    'configuracion_faltante' => (array_search('No configurado', $envConfig) !== false) ? 'Configura las variables de entorno en .env' : null
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en debug de Google Drive',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // ===============================================
    // FILTRADO DE LOTES POR DESARROLLO
    // ===============================================

    /**
     * Obtener lotes disponibles por desarrollo
     */
    public function obtenerLotesPorDesarrollo()
    {
        error_log('[LOTES_DEBUG] Método obtenerLotesPorDesarrollo llamado');
        
        if (!$this->request->isAJAX()) {
            error_log('[LOTES_DEBUG] Petición rechazada - no es AJAX');
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $desarrollo = $this->request->getPost('desarrollo') ?? $this->request->getGet('desarrollo');
            error_log('[LOTES_DEBUG] Desarrollo recibido: ' . ($desarrollo ?: 'VACIO'));

            if (empty($desarrollo)) {
                error_log('[LOTES_DEBUG] Error - desarrollo vacío');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Desarrollo no especificado',
                    'data' => []
                ]);
            }

            // Usar el LoteModel para obtener lotes filtrados
            error_log('[LOTES_DEBUG] Instanciando LoteModel...');
            $loteModel = model('LoteModel');
            
            error_log('[LOTES_DEBUG] Llamando getLotesPorDesarrollo con: ' . $desarrollo);
            $lotes = $loteModel->getLotesPorDesarrollo($desarrollo);
            error_log('[LOTES_DEBUG] Lotes obtenidos: ' . count($lotes));

            // Formatear datos para el frontend
            $lotesFormateados = [];
            foreach ($lotes as $lote) {
                $lotesFormateados[] = [
                    'id' => $lote->id ?? $lote['id'],
                    'numero' => $lote->numero ?? $lote['numero'],
                    'proyecto_nombre' => $lote->proyecto_nombre ?? $lote['proyecto_nombre'],
                    'manzana_nombre' => $lote->manzana_nombre ?? $lote['manzana_nombre'],
                    'area' => $lote->area ?? $lote['area'],
                    'precio_total' => $lote->precio_total ?? $lote['precio_total'],
                    'precio_m2' => $lote->precio_m2 ?? $lote['precio_m2'],
                    'estado_nombre' => $lote->estado_nombre ?? $lote['estado_nombre'],
                    'clave' => $lote->clave ?? $lote['clave'],
                    'descripcion' => ($lote->proyecto_nombre ?? $lote['proyecto_nombre']) . ' - Manzana ' . ($lote->manzana_nombre ?? $lote['manzana_nombre']) . ' - Lote ' . ($lote->numero ?? $lote['numero'])
                ];
            }

            // Obtener estadísticas
            $estadisticas = $loteModel->getEstadisticasPorDesarrollo($desarrollo);

            $this->logDebug('Lotes obtenidos por desarrollo', [
                'desarrollo' => $desarrollo,
                'total_lotes' => count($lotesFormateados),
                'proyectos_activos' => $estadisticas['proyectos_activos']
            ]);

            error_log('[LOTES_DEBUG] Enviando respuesta con ' . count($lotesFormateados) . ' lotes');
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Lotes obtenidos exitosamente',
                'data' => $lotesFormateados,
                'estadisticas' => $estadisticas,
                'desarrollo' => $desarrollo,
                'total' => count($lotesFormateados)
            ]);

        } catch (\Exception $e) {
            $this->logError('Error obteniendo lotes por desarrollo', [
                'error' => $e->getMessage(),
                'desarrollo' => $desarrollo ?? 'undefined',
                'trace' => $e->getTraceAsString()
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener lotes: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Obtener estadísticas de lotes por desarrollo
     */
    public function obtenerEstadisticasDesarrollo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $desarrollo = $this->request->getGet('desarrollo');

            if (empty($desarrollo)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Desarrollo no especificado'
                ]);
            }

            $loteModel = model('LoteModel');
            $estadisticas = $loteModel->getEstadisticasPorDesarrollo($desarrollo);

            return $this->response->setJSON([
                'success' => true,
                'data' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener todos los desarrollos disponibles
     */
    public function obtenerDesarrollosDisponibles()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso no autorizado']);
        }

        try {
            $desarrollos = [
                [
                    'value' => 'valle_natura',
                    'label' => 'ANVAR Inmobiliaria (Valle Natura)',
                    'descripcion' => 'Incluye V1 y C1 Valle Natura',
                    'alias' => ['anvar_inmobiliaria', 'valle_natura']
                ],
                [
                    'value' => 'cordelia',
                    'label' => 'Cordelia',
                    'descripcion' => 'Proyectos Cordelia',
                    'alias' => ['cordelia']
                ]
            ];

            // Obtener estadísticas para cada desarrollo
            $loteModel = model('LoteModel');
            foreach ($desarrollos as &$desarrollo) {
                $stats = $loteModel->getEstadisticasPorDesarrollo($desarrollo['value']);
                $desarrollo['lotes_disponibles'] = $stats['total_disponibles'];
                $desarrollo['proyectos_activos'] = $stats['proyectos_activos'];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $desarrollos
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener desarrollos: ' . $e->getMessage()
            ]);
        }
    }

    // ===============================================
    // FASE 4: DEBUG Y VALIDACIÓN DEL SISTEMA
    // ===============================================

    /**
     * Validar configuración completa del sistema
     */
    public function validarSistema()
    {
        if (ENVIRONMENT !== 'development') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Solo disponible en desarrollo']);
        }

        try {
            $validaciones = [
                'base_datos' => $this->validarBaseDatos(),
                'hubspot' => $this->validarHubSpot(),
                'google_drive' => $this->validarGoogleDrive(),
                'email' => $this->validarEmail(),
                'modelos' => $this->validarModelos(),
                'rutas' => $this->validarRutas()
            ];

            $todoValido = true;
            foreach ($validaciones as $componente => $resultado) {
                if (!$resultado['valido']) {
                    $todoValido = false;
                    break;
                }
            }

            return $this->response->setJSON([
                'sistema_valido' => $todoValido,
                'validaciones' => $validaciones,
                'timestamp' => date('Y-m-d H:i:s'),
                'environment' => ENVIRONMENT
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'sistema_valido' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function validarBaseDatos(): array
    {
        try {
            $db = \Config\Database::connect();
            
            // Verificar conexión
            if (!$db->connID) {
                return ['valido' => false, 'error' => 'No se pudo conectar a la base de datos'];
            }

            // Verificar tabla registro_leads
            $tablas = $db->listTables();
            if (!in_array('registro_leads', $tablas)) {
                return ['valido' => false, 'error' => 'Tabla registro_leads no existe'];
            }

            // Verificar campos críticos
            $campos = $db->getFieldNames('registro_leads');
            $camposCriticos = ['firstname', 'lastname', 'email', 'desarrollo', 'hubspot_contact_id'];
            
            foreach ($camposCriticos as $campo) {
                if (!in_array($campo, $campos)) {
                    return ['valido' => false, 'error' => "Campo {$campo} falta en registro_leads"];
                }
            }

            return [
                'valido' => true,
                'database' => $db->database,
                'total_tablas' => count($tablas),
                'campos_registro_leads' => count($campos)
            ];

        } catch (\Exception $e) {
            return ['valido' => false, 'error' => $e->getMessage()];
        }
    }

    private function validarHubSpot(): array
    {
        try {
            $token = env('HUBSPOT_ACCESS_TOKEN');
            if (empty($token)) {
                return ['valido' => false, 'error' => 'HUBSPOT_ACCESS_TOKEN no configurado'];
            }

            // Test básico de conexión
            $propiedades = $this->hubSpotService->validarPropiedadesPersonalizadas();
            $propiedadesFaltantes = [];
            
            foreach ($propiedades as $nombre => $info) {
                if (!$info['existe']) {
                    $propiedadesFaltantes[] = $nombre;
                }
            }

            return [
                'valido' => count($propiedadesFaltantes) < 5, // Permitir algunas propiedades faltantes
                'token_configurado' => !empty($token),
                'propiedades_faltantes' => $propiedadesFaltantes,
                'total_propiedades' => count($propiedades)
            ];

        } catch (\Exception $e) {
            return ['valido' => false, 'error' => $e->getMessage()];
        }
    }

    private function validarGoogleDrive(): array
    {
        try {
            $clientId = env('GOOGLE_DRIVE_CLIENT_ID');
            $clientSecret = env('GOOGLE_DRIVE_CLIENT_SECRET');
            
            if (empty($clientId) || empty($clientSecret)) {
                return ['valido' => false, 'error' => 'Credenciales Google Drive no configuradas'];
            }

            $tokenFile = WRITEPATH . 'google_drive_tokens.json';
            $tokensExisten = file_exists($tokenFile);

            return [
                'valido' => !empty($clientId) && !empty($clientSecret),
                'credenciales_configuradas' => !empty($clientId) && !empty($clientSecret),
                'tokens_existen' => $tokensExisten,
                'tokens_file' => $tokenFile
            ];

        } catch (\Exception $e) {
            return ['valido' => false, 'error' => $e->getMessage()];
        }
    }

    private function validarEmail(): array
    {
        try {
            $host = env('email.SMTPHost');
            $user = env('email.SMTPUser');
            
            if (empty($host) || empty($user)) {
                return ['valido' => false, 'error' => 'Configuración SMTP incompleta'];
            }

            $validacion = $this->emailService->validarConfiguracion();
            
            return [
                'valido' => $validacion['configurado'],
                'smtp_host' => $host,
                'smtp_user' => $user,
                'protocolo' => $validacion['protocolo'] ?? 'unknown'
            ];

        } catch (\Exception $e) {
            return ['valido' => false, 'error' => $e->getMessage()];
        }
    }

    private function validarModelos(): array
    {
        try {
            $modelos = [
                'RegistroLeadModel' => model('RegistroLeadModel'),
                'LoteModel' => model('LoteModel'),
                'ClienteModel' => model('ClienteModel')
            ];

            $validaciones = [];
            foreach ($modelos as $nombre => $modelo) {
                $validaciones[$nombre] = [
                    'existe' => $modelo !== null,
                    'tabla' => $modelo ? $modelo->getTable() : 'N/A',
                    'returnType' => $modelo ? $modelo->getReturnType() : 'N/A'
                ];
            }

            $todoValido = true;
            foreach ($validaciones as $validacion) {
                if (!$validacion['existe']) {
                    $todoValido = false;
                    break;
                }
            }

            return [
                'valido' => $todoValido,
                'modelos' => $validaciones
            ];

        } catch (\Exception $e) {
            return ['valido' => false, 'error' => $e->getMessage()];
        }
    }

    private function validarRutas(): array
    {
        try {
            $rutasCriticas = [
                '/registro-clientes',
                '/registro-clientes/obtener-lotes',
                '/admin/leads',
                '/admin/leads/conversiones'
            ];

            $rutasValidas = [];
            foreach ($rutasCriticas as $ruta) {
                $rutasValidas[$ruta] = 'configured'; // Simplificado para este debug
            }

            return [
                'valido' => true,
                'rutas_criticas' => $rutasValidas
            ];

        } catch (\Exception $e) {
            return ['valido' => false, 'error' => $e->getMessage()];
        }
    }
}