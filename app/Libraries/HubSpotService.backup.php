<?php

namespace App\Libraries;

use Exception;

class HubSpotService
{
    private $baseUrl = 'https://api.hubapi.com';
    private $accessToken;
    private $ownerIdDefault;
    
    // Configuración desde el constructor
    public function __construct()
    {
        $this->accessToken = env('HUBSPOT_ACCESS_TOKEN', '');
        $this->ownerIdDefault = env('HUBSPOT_OWNER_ID', '80110028'); // Rodolfo Sandoval
    }

    /**
     * Buscar contacto por email
     */
    public function buscarContactoPorEmail(string $email): ?array
    {
        try {
            $url = $this->baseUrl . '/crm/v3/objects/contacts/search';
            
            $searchData = [
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'propertyName' => 'email',
                                'operator' => 'EQ',
                                'value' => $email
                            ]
                        ]
                    ]
                ],
                'properties' => [
                    'id', 'email', 'firstname', 'lastname', 'phone', 
                    'agente', 'hubspot_owner_id', 'createdate', 'lastmodifieddate'
                ]
            ];

            $response = $this->makeRequest('POST', $url, $searchData);

            if (isset($response['results']) && count($response['results']) > 0) {
                error_log('[HUBSPOT] Contacto encontrado: ' . $email . ' ID: ' . $response['results'][0]['id']);
                return $response['results'][0];
            }

            error_log('[HUBSPOT] Contacto no encontrado: ' . $email);
            return null;

        } catch (Exception $e) {
            error_log('[HUBSPOT] Error buscando contacto: ' . $e->getMessage());
            throw new Exception('Error al buscar contacto en HubSpot: ' . $e->getMessage());
        }
    }

    /**
     * Crear nuevo contacto
     */
    public function crearContacto(array $datosCliente): array
    {
        try {
            $url = $this->baseUrl . '/crm/v3/objects/contacts';
            
            // Preparar propiedades del contacto
            $propiedades = $this->prepararPropiedadesContacto($datosCliente);
            
            $contactData = [
                'properties' => $propiedades
            ];

            error_log('[HUBSPOT] Creando contacto: ' . $datosCliente['email']);
            
            $response = $this->makeRequest('POST', $url, $contactData);

            if (!isset($response['id'])) {
                throw new Exception('No se recibió ID del contacto creado');
            }

            error_log('[HUBSPOT] Contacto creado exitosamente: ' . $response['id']);
            
            return [
                'id' => $response['id'],
                'email' => $response['properties']['email'] ?? $datosCliente['email'],
                'created' => true
            ];

        } catch (Exception $e) {
            error_log('[HUBSPOT] Error creando contacto: ' . $e->getMessage());
            throw new Exception('Error al crear contacto en HubSpot: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar contacto existente
     */
    public function actualizarContacto(string $contactId, array $datosCliente): array
    {
        try {
            $url = $this->baseUrl . '/crm/v3/objects/contacts/' . $contactId;
            
            // Preparar propiedades del contacto (sin sobrescribir datos importantes)
            $propiedades = $this->prepararPropiedadesContacto($datosCliente, false);
            
            $contactData = [
                'properties' => $propiedades
            ];

            error_log('[HUBSPOT] Actualizando contacto: ' . $contactId);
            
            $response = $this->makeRequest('PATCH', $url, $contactData);

            error_log('[HUBSPOT] Contacto actualizado exitosamente: ' . $contactId);
            
            return [
                'id' => $response['id'],
                'email' => $response['properties']['email'] ?? $datosCliente['email'],
                'created' => false
            ];

        } catch (Exception $e) {
            error_log('[HUBSPOT] Error actualizando contacto: ' . $e->getMessage());
            throw new Exception('Error al actualizar contacto en HubSpot: ' . $e->getMessage());
        }
    }

    /**
     * Crear ticket asociado al contacto
     */
    public function crearTicket(string $contactId, array $datosCliente): array
    {
        try {
            $url = $this->baseUrl . '/crm/v3/objects/tickets';
            
            // Generar subject y contenido del ticket
            $nombreCompleto = trim($datosCliente['firstname'] . ' ' . $datosCliente['lastname']);
            $desarrollo = $datosCliente['desarrollo'] === 'valle_natura' ? 'Valle Natura' : 'Cordelia';
            
            $subject = "Nuevo registro - {$nombreCompleto} - {$desarrollo}";
            $content = $this->generarContenidoTicket($datosCliente);
            
            $ticketData = [
                'properties' => [
                    'subject' => $subject,
                    'content' => $content,
                    'hs_pipeline' => '0', // Pipeline por defecto
                    'hs_pipeline_stage' => '1', // Primera etapa
                    'hubspot_owner_id' => $this->ownerIdDefault,
                    'hs_ticket_priority' => 'MEDIUM'
                ],
                'associations' => [
                    [
                        'to' => ['id' => $contactId],
                        'types' => [
                            [
                                'associationCategory' => 'HUBSPOT_DEFINED',
                                'associationTypeId' => 16 // Ticket to Contact
                            ]
                        ]
                    ]
                ]
            ];

            error_log('[HUBSPOT] Creando ticket para contacto: ' . $contactId);
            
            $response = $this->makeRequest('POST', $url, $ticketData);

            if (!isset($response['id'])) {
                throw new Exception('No se recibió ID del ticket creado');
            }

            error_log('[HUBSPOT] Ticket creado exitosamente: ' . $response['id']);
            
            return [
                'id' => $response['id'],
                'subject' => $subject
            ];

        } catch (Exception $e) {
            error_log('[HUBSPOT] Error creando ticket: ' . $e->getMessage());
            throw new Exception('Error al crear ticket en HubSpot: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar contacto con URLs de Google Drive
     */
    public function actualizarContactoConDocumentos(string $contactId, array $urlsDocumentos): bool
    {
        try {
            $url = $this->baseUrl . '/crm/v3/objects/contacts/' . $contactId;
            
            $propiedades = [];
            
            // Mapear URLs de documentos a propiedades personalizadas
            if (isset($urlsDocumentos['ine_frente'])) {
                $propiedades['documento_ine_frente_url'] = $urlsDocumentos['ine_frente'];
            }
            
            if (isset($urlsDocumentos['ine_reverso'])) {
                $propiedades['documento_ine_reverso_url'] = $urlsDocumentos['ine_reverso'];
            }
            
            if (isset($urlsDocumentos['comprobante_domicilio'])) {
                $propiedades['documento_comprobante_url'] = $urlsDocumentos['comprobante_domicilio'];
            }

            $contactData = [
                'properties' => $propiedades
            ];

            error_log('[HUBSPOT] Actualizando contacto con URLs de documentos: ' . $contactId);
            
            $this->makeRequest('PATCH', $url, $contactData);

            error_log('[HUBSPOT] URLs de documentos actualizadas exitosamente');
            
            return true;

        } catch (Exception $e) {
            error_log('[HUBSPOT] Error actualizando URLs de documentos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Proceso completo: crear o actualizar contacto + crear ticket
     */
    public function procesarRegistroCompleto(array $datosCliente): array
    {
        try {
            $startTime = microtime(true);
            
            error_log('[HUBSPOT] Iniciando proceso completo para: ' . $datosCliente['email']);
            
            // 1. Buscar si el contacto ya existe
            $contactoExistente = $this->buscarContactoPorEmail($datosCliente['email']);
            
            // 2. Crear o actualizar contacto
            if ($contactoExistente) {
                $contacto = $this->actualizarContacto($contactoExistente['id'], $datosCliente);
                $contacto['action'] = 'updated';
            } else {
                $contacto = $this->crearContacto($datosCliente);
                $contacto['action'] = 'created';
            }
            
            $duration = round((microtime(true) - $startTime) * 1000);
            
            $resultado = [
                'success' => true,
                'contacto' => $contacto,
                'ticket' => null, // No crear tickets
                'estadisticas' => [
                    'contacto_existia' => $contactoExistente !== null,
                    'accion_contacto' => $contacto['action'],
                    'duracion_ms' => $duration
                ]
            ];
            
            error_log('[HUBSPOT] Proceso completo exitoso en ' . $duration . 'ms - Contacto: ' . $contacto['id']);
            
            return $resultado;

        } catch (Exception $e) {
            error_log('[HUBSPOT] Error en proceso completo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Preparar propiedades del contacto para HubSpot
     */
    private function prepararPropiedadesContacto(array $datosCliente, bool $esNuevo = true): array
    {
        // Solo incluir propiedades estándar de HubSpot
        // Las propiedades personalizadas deben crearse primero en HubSpot
        $propiedades = [
            'email' => $datosCliente['email'],
            'firstname' => $datosCliente['firstname'],
            'lastname' => $datosCliente['lastname'],
            'mobilephone' => $datosCliente['mobilephone'],
            'phone' => $datosCliente['phone'] ?? ''
        ];
        
        // PROPIEDADES PERSONALIZADAS - Habilitadas para envío
        $propiedades['apellido_materno'] = $datosCliente['apellido_materno'] ?? '';
        $propiedades['rfc_curp'] = $datosCliente['rfc_curp'] ?? '';
        $propiedades['medio_de_contacto'] = $datosCliente['medio_de_contacto'] ?? '';
        $propiedades['desarrollo'] = $datosCliente['desarrollo'] ?? '';
        $propiedades['manzana'] = $datosCliente['manzana'] ?? '';
        $propiedades['lote'] = $datosCliente['lote'] ?? '';
        $propiedades['numero_casa_departamento'] = $datosCliente['numero_casa_depto'] ?? '';
        $propiedades['nombre_copropietario'] = $datosCliente['nombre_copropietario'] ?? '';
        $propiedades['parentesco_copropietario'] = $datosCliente['parentesco_copropietario'] ?? '';

        // Solo agregar estos campos para contactos nuevos
        if ($esNuevo) {
            $propiedades['agente'] = $datosCliente['agente_referido'] ?? '';
            $propiedades['hubspot_owner_id'] = $this->ownerIdDefault;
            $propiedades['lifecyclestage'] = 'lead';
            $propiedades['hs_lead_status'] = 'NEW';
        }
        
        // Agregar toda la información adicional en las notas
        $notas = $this->generarNotasContacto($datosCliente);
        if (!empty($notas)) {
            $propiedades['hs_content_membership_notes'] = $notas;
        }

        // Limpiar valores vacíos
        return array_filter($propiedades, function($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Generar notas del contacto con toda la información adicional
     */
    private function generarNotasContacto(array $datosCliente): string
    {
        $notas = [];
        
        // Apellido materno
        if (!empty($datosCliente['apellido_materno'])) {
            $notas[] = "Apellido Materno: " . $datosCliente['apellido_materno'];
        }
        
        // Desarrollo de interés
        $desarrollo = $datosCliente['desarrollo'] === 'valle_natura' ? 'Valle Natura' : 'Cordelia';
        $notas[] = "Desarrollo de interés: " . $desarrollo;
        
        // Ubicación específica
        if ($datosCliente['desarrollo'] === 'valle_natura') {
            if (!empty($datosCliente['manzana']) || !empty($datosCliente['lote'])) {
                $notas[] = "Ubicación: Manzana " . ($datosCliente['manzana'] ?? 'N/A') . ", Lote " . ($datosCliente['lote'] ?? 'N/A');
            }
        } elseif ($datosCliente['desarrollo'] === 'cordelia') {
            if (!empty($datosCliente['numero_casa_depto'])) {
                $notas[] = "Número Casa/Departamento: " . $datosCliente['numero_casa_depto'];
            }
        }
        
        // Medio de contacto preferido
        $medioContacto = $datosCliente['medio_de_contacto'] ?? '';
        if ($medioContacto === 'telefono') {
            $notas[] = "Medio de contacto preferido: Llamada telefónica";
        } elseif ($medioContacto === 'whatsapp') {
            $notas[] = "Medio de contacto preferido: WhatsApp";
        }
        
        // Co-propietario
        if (!empty($datosCliente['nombre_copropietario'])) {
            $coprop = "Co-propietario: " . $datosCliente['nombre_copropietario'];
            if (!empty($datosCliente['parentesco_copropietario'])) {
                $coprop .= " (Parentesco: " . $datosCliente['parentesco_copropietario'] . ")";
            }
            $notas[] = $coprop;
        }
        
        // Agente referido
        if (!empty($datosCliente['agente_referido'])) {
            $notas[] = "Agente referido: " . $datosCliente['agente_referido'];
        }
        
        // Fecha de registro
        $notas[] = "Fecha de registro: " . date('d/m/Y H:i:s');
        $notas[] = "Fuente: Formulario web de registro";
        
        return implode("\n", $notas);
    }
    
    /**
     * Generar contenido del ticket
     */
    private function generarContenidoTicket(array $datosCliente): string
    {
        $nombreCompleto = trim($datosCliente['firstname'] . ' ' . $datosCliente['lastname']);
        $desarrollo = $datosCliente['desarrollo'] === 'valle_natura' ? 'Valle Natura' : 'Cordelia';
        $medioContacto = $datosCliente['medio_de_contacto'] ?? 'WhatsApp';
        $medioContactoTexto = $medioContacto === 'telefono' ? 'Llamada telefónica' : 'WhatsApp';
        
        $contenido = "Cliente registrado desde formulario web\n\n";
        $contenido .= "📋 INFORMACIÓN DEL CLIENTE:\n";
        $contenido .= "• Nombre: {$nombreCompleto}\n";
        $contenido .= "• Email: {$datosCliente['email']}\n";
        $contenido .= "• Teléfono: {$datosCliente['telefono']}\n";
        $contenido .= "• Medio de contacto preferido: {$medioContactoTexto}\n\n";
        
        $contenido .= "🏘️ INTERÉS EN DESARROLLO:\n";
        $contenido .= "• Desarrollo: {$desarrollo}\n";
        
        if (!empty($datosCliente['manzana'])) {
            $contenido .= "• Manzana: {$datosCliente['manzana']}\n";
        }
        
        if (!empty($datosCliente['lote'])) {
            $contenido .= "• Lote: {$datosCliente['lote']}\n";
        }
        
        if (!empty($datosCliente['nombre_copropietario'])) {
            $contenido .= "\n👥 CO-PROPIETARIO:\n";
            $contenido .= "• Nombre: {$datosCliente['nombre_copropietario']}\n";
            $contenido .= "• Parentesco: " . ($datosCliente['parentesco_copropietario'] ?? 'No especificado') . "\n";
        }
        
        if (!empty($datosCliente['agente_referido'])) {
            $contenido .= "\n📊 MÉTRICAS:\n";
            $contenido .= "• Agente referidor: {$datosCliente['agente_referido']}\n";
        }
        
        $contenido .= "\n📎 DOCUMENTOS:\n";
        $contenido .= "• Documentos cargados en Google Drive\n";
        $contenido .= "• INE frente y reverso\n";
        $contenido .= "• Comprobante de domicilio\n\n";
        
        $contenido .= "⏰ Registro realizado: " . date('d/m/Y H:i:s') . "\n";
        $contenido .= "🤖 Generado automáticamente por el sistema de registro web";
        
        return $contenido;
    }

    /**
     * Realizar petición HTTP a la API de HubSpot
     */
    private function makeRequest(string $method, string $url, array $data = null): array
    {
        $ch = curl_init();
        
        // Configuración básica de cURL
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method
        ]);

        // Headers
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'User-Agent: ANVAR-Inmobiliaria/1.0'
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Body para POST/PATCH/PUT
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Ejecutar petición
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $duration = round((microtime(true) - $startTime) * 1000);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);

        // Log de la petición
        error_log('[HUBSPOT] ' . $method . ' ' . $url . ' - HTTP ' . $httpCode . ' (' . $duration . 'ms)');

        if ($curlError) {
            throw new Exception('Error de cURL: ' . $curlError);
        }

        if ($httpCode >= 400) {
            error_log('[HUBSPOT] Error HTTP ' . $httpCode . ': ' . $response);
            
            // Intentar decodificar el error
            $errorData = json_decode($response, true);
            $errorMessage = 'Error de HubSpot API (HTTP ' . $httpCode . ')';
            
            if (isset($errorData['message'])) {
                $errorMessage .= ': ' . $errorData['message'];
            } elseif (isset($errorData['errors'][0]['message'])) {
                $errorMessage .= ': ' . $errorData['errors'][0]['message'];
            }
            
            throw new Exception($errorMessage);
        }

        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Respuesta JSON inválida de HubSpot API');
        }

        return $responseData;
    }

    /**
     * Verificar si el servicio está disponible (health check)
     */
    public function verificarConexion(): bool
    {
        try {
            $url = $this->baseUrl . '/crm/v3/objects/contacts/search';
            
            // Búsqueda que no debería devolver resultados (email inexistente)
            $searchData = [
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'propertyName' => 'email',
                                'operator' => 'EQ',
                                'value' => 'test_conexion_' . time() . '@nonexistent.com'
                            ]
                        ]
                    ]
                ],
                'limit' => 1
            ];
            
            $this->makeRequest('POST', $url, $searchData);
            
            return true;
            
        } catch (Exception $e) {
            error_log('[HUBSPOT] Error verificando conexión: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información del propietario por defecto
     */
    public function obtenerInfoPropietario(): ?array
    {
        try {
            $url = $this->baseUrl . '/crm/v3/owners/' . $this->ownerIdDefault;
            $response = $this->makeRequest('GET', $url);
            
            return $response;
            
        } catch (Exception $e) {
            error_log('[HUBSPOT] Error obteniendo info propietario: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validar que existan las propiedades personalizadas necesarias
     */
    public function validarPropiedadesPersonalizadas(): array
    {
        $propiedadesRequeridas = [
            // Propiedades básicas
            'agente' => 'number',
            'nombre_copropietario' => 'string',
            'parentesco_copropietario' => 'string',
            'medio_de_contacto' => 'enumeration',
            'desarrollo' => 'enumeration',
            'manzana' => 'string',
            'lote' => 'string',
            'documento_ine_frente_url' => 'string',
            'documento_ine_reverso_url' => 'string',
            'documento_comprobante_url' => 'string',
            
            // FASE 2: Propiedades de credenciales de cliente
            'cliente_generado' => 'enumeration',
            'fecha_generacion_cliente' => 'datetime',
            'estado_cliente' => 'enumeration',
            'email_cliente' => 'string',
            'magic_link_enviado' => 'enumeration',
            'magic_link_token' => 'string',
            'magic_link_expires' => 'datetime',
            'magic_link_accedido' => 'datetime',
            'magic_link_ultimo_intento' => 'datetime',
            'cliente_activo' => 'enumeration',
            'tipo_identificador' => 'enumeration'
        ];

        $resultados = [];

        foreach ($propiedadesRequeridas as $nombre => $tipo) {
            try {
                $url = $this->baseUrl . '/crm/v3/properties/contacts/' . $nombre;
                $response = $this->makeRequest('GET', $url);
                
                $resultados[$nombre] = [
                    'existe' => true,
                    'tipo' => $response['type'] ?? 'unknown',
                    'coincide_tipo' => ($response['type'] ?? '') === $tipo
                ];
                
            } catch (Exception $e) {
                $resultados[$nombre] = [
                    'existe' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $resultados;
    }

    // ===============================================
    // FASE 2: GENERACIÓN DE CREDENCIALES DE CLIENTE
    // ===============================================

    /**
     * Crear contacto con credenciales de cliente y envío de magic link
     */
    public function crearContactoConCredenciales(array $datosCliente): array
    {
        try {
            // 1. Crear o actualizar contacto normal
            $resultadoCompleto = $this->procesarRegistroCompleto($datosCliente);
            if (!$resultadoCompleto['success']) {
                throw new \Exception('Error creando contacto base: ' . ($resultadoCompleto['error'] ?? 'Error desconocido'));
            }
            $contacto = $resultadoCompleto['contacto'];
            
            // 2. Generar credenciales de cliente
            $credenciales = $this->generarCredencialesCliente($datosCliente);
            
            // 3. Actualizar contacto con información de credenciales
            $propiedadesCredenciales = [
                'cliente_generado' => 'true',
                'fecha_generacion_cliente' => date('Y-m-d H:i:s'),
                'estado_cliente' => 'activo',
                'email_cliente' => $credenciales['email_cliente'],
                'magic_link_token' => $credenciales['magic_token'],
                'magic_link_expires' => $credenciales['magic_link_expires'],
                'tipo_identificador' => $credenciales['tipo_identificador'],
                'magic_link_enviado' => 'pending'
            ];
            
            $this->actualizarContacto($contacto['id'], $propiedadesCredenciales);
            
            // 4. Programar envío de magic link (esto se manejará externamente)
            $magicLinkData = $this->prepararMagicLinkData($datosCliente, $credenciales);
            
            return [
                'success' => true,
                'contacto_id' => $contacto['id'],
                'credenciales' => $credenciales,
                'magic_link_data' => $magicLinkData,
                'message' => 'Contacto creado con credenciales de cliente'
            ];
            
        } catch (Exception $e) {
            error_log('[HUBSPOT] Error creando contacto con credenciales: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'contacto_id' => null
            ];
        }
    }

    /**
     * Generar credenciales de cliente basadas en RFC/CURP
     */
    private function generarCredencialesCliente(array $datosCliente): array
    {
        // Usar RFC/CURP como base para el email
        $identificador = $datosCliente['rfc'] ?? $datosCliente['curp'] ?? null;
        
        if (empty($identificador)) {
            // Fallback: generar basado en nombre y timestamp
            $nombre = strtolower(substr($datosCliente['firstname'], 0, 3));
            $apellido = strtolower(substr($datosCliente['lastname'], 0, 3));
            $timestamp = date('Ymd');
            $identificador = $nombre . $apellido . $timestamp;
        }
        
        // Limpiar identificador
        $identificador = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $identificador));
        
        // Generar email del cliente
        $emailCliente = $identificador . '@cliente.anvar.com.mx';
        
        // Generar token para magic link (válido por 7 días)
        $magicToken = $this->generarMagicToken($datosCliente, $identificador);
        
        return [
            'identificador' => $identificador,
            'email_cliente' => $emailCliente,
            'magic_token' => $magicToken,
            'magic_link_expires' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'tipo_identificador' => !empty($datosCliente['rfc']) ? 'RFC' : (!empty($datosCliente['curp']) ? 'CURP' : 'GENERADO')
        ];
    }

    /**
     * Generar token seguro para magic link
     */
    private function generarMagicToken(array $datosCliente, string $identificador): string
    {
        $seed = $identificador . $datosCliente['email'] . date('YmdH') . env('encryption.key', 'anvar2024');
        return hash('sha256', $seed) . bin2hex(random_bytes(16));
    }

    /**
     * Preparar datos para el magic link
     */
    private function prepararMagicLinkData(array $datosCliente, array $credenciales): array
    {
        $nombreCompleto = trim($datosCliente['firstname'] . ' ' . $datosCliente['lastname']);
        $desarrollo = $datosCliente['desarrollo'] === 'valle_natura' ? 'Valle Natura' : 'Cordelia';
        
        // URL del magic link (apunta al sistema de cliente)
        $magicLinkUrl = base_url('cliente/magic-login/' . $credenciales['magic_token']);
        
        return [
            'nombre_completo' => $nombreCompleto,
            'email_personal' => $datosCliente['email'],
            'email_cliente' => $credenciales['email_cliente'],
            'desarrollo_interes' => $desarrollo,
            'magic_link_url' => $magicLinkUrl,
            'magic_token' => $credenciales['magic_token'],
            'expira_en' => $credenciales['magic_link_expires'],
            'tipo_identificador' => $credenciales['tipo_identificador']
        ];
    }

    /**
     * Actualizar estado del magic link en HubSpot
     */
    public function actualizarEstadoMagicLink(string $contactoId, string $estado, ?string $fechaAcceso = null): bool
    {
        try {
            $propiedades = [
                'magic_link_enviado' => $estado, // sent, accessed, expired, error
                'magic_link_ultimo_intento' => date('Y-m-d H:i:s')
            ];
            
            if ($fechaAcceso) {
                $propiedades['magic_link_accedido'] = $fechaAcceso;
                $propiedades['cliente_activo'] = 'true';
            }
            
            $this->actualizarContacto($contactoId, $propiedades);
            
            error_log("[HUBSPOT] Estado magic link actualizado: {$contactoId} -> {$estado}");
            return true;
            
        } catch (Exception $e) {
            error_log("[HUBSPOT] Error actualizando estado magic link: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nota de seguimiento sobre generación de credenciales
     */
    public function crearNotaCredenciales(string $contactoId, array $credenciales, array $datosCliente): bool
    {
        try {
            $nombreCompleto = trim($datosCliente['firstname'] . ' ' . $datosCliente['lastname']);
            $desarrollo = $datosCliente['desarrollo'] === 'valle_natura' ? 'Valle Natura' : 'Cordelia';
            
            $contenidoNota = "🔐 CREDENCIALES DE CLIENTE GENERADAS\n\n";
            $contenidoNota .= "📧 Email del cliente: {$credenciales['email_cliente']}\n";
            $contenidoNota .= "🆔 Identificador: {$credenciales['identificador']} ({$credenciales['tipo_identificador']})\n";
            $contenidoNota .= "📱 Magic Link enviado a: {$datosCliente['email']}\n";
            $contenidoNota .= "⏰ Magic Link expira: {$credenciales['magic_link_expires']}\n";
            $contenidoNota .= "🏘️ Desarrollo de interés: {$desarrollo}\n\n";
            $contenidoNota .= "📋 El cliente podrá acceder a su portal usando el magic link enviado por email.\n";
            $contenidoNota .= "📅 Fecha de generación: " . date('d/m/Y H:i:s');
            
            $notaData = [
                'properties' => [
                    'hs_note_body' => $contenidoNota,
                    'hs_timestamp' => date('c')
                ],
                'associations' => [
                    [
                        'to' => ['id' => $contactoId],
                        'types' => [
                            [
                                'associationCategory' => 'HUBSPOT_DEFINED',
                                'associationTypeId' => 202 // Note to Contact
                            ]
                        ]
                    ]
                ]
            ];
            
            $url = $this->baseUrl . '/crm/v3/objects/notes';
            $this->makeRequest('POST', $url, $notaData);
            
            error_log("[HUBSPOT] Nota de credenciales creada para contacto: {$contactoId}");
            return true;
            
        } catch (Exception $e) {
            error_log("[HUBSPOT] Error creando nota de credenciales: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información del cliente por token de magic link
     */
    public function obtenerClientePorMagicToken(string $token): ?array
    {
        try {
            $url = $this->baseUrl . '/crm/v3/objects/contacts/search';
            
            $searchData = [
                'filterGroups' => [
                    [
                        'filters' => [
                            [
                                'propertyName' => 'magic_link_token',
                                'operator' => 'EQ',
                                'value' => $token
                            ]
                        ]
                    ]
                ],
                'properties' => [
                    'id', 'email', 'firstname', 'lastname', 'phone',
                    'email_cliente', 'magic_link_expires', 'cliente_activo'
                ]
            ];

            $response = $this->makeRequest('POST', $url, $searchData);

            if (isset($response['results']) && count($response['results']) > 0) {
                return $response['results'][0];
            }

            return null;

        } catch (Exception $e) {
            error_log('[HUBSPOT] Error buscando cliente por magic token: ' . $e->getMessage());
            return null;
        }
    }
}