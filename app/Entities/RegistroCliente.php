<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use DateTime;

class RegistroCliente extends Entity
{
    protected $datamap = [];
    protected $dates   = ['fecha_registro', 'fecha_actualizacion', 'hubspot_last_sync'];
    protected $casts   = [
        'id' => 'integer',
        'acepta_terminos' => 'boolean',
        'hubspot_sync_attempts' => 'integer'
    ];

    // ===============================================
    // PROPIEDADES CALCULADAS
    // ===============================================

    /**
     * Obtener nombre completo del cliente
     */
    public function getNombreCompleto(): string
    {
        $firstname = trim($this->attributes['firstname'] ?? '');
        $lastname = trim($this->attributes['lastname'] ?? '');
        $apellidoMaterno = trim($this->attributes['apellido_materno'] ?? '');
        
        $nombreCompleto = $firstname . ' ' . $lastname;
        
        if (!empty($apellidoMaterno)) {
            $nombreCompleto .= ' ' . $apellidoMaterno;
        }
        
        return trim($nombreCompleto);
    }

    /**
     * Obtener nombre para carpeta basado en RFC/CURP
     */
    public function getNombreCarpeta(): string
    {
        $rfcCurp = trim($this->attributes['rfc_curp'] ?? '');
        
        if (!empty($rfcCurp)) {
            // Usar RFC/CURP como nombre de carpeta
            $nombreCarpeta = strtoupper($rfcCurp);
            $nombreCarpeta = $this->removerAcentos($nombreCarpeta);
            $nombreCarpeta = preg_replace('/[^A-Z0-9]/', '', $nombreCarpeta);
            return $nombreCarpeta;
        }
        
        // Fallback: usar nombre completo si no hay RFC/CURP
        $nombreCompleto = $this->getNombreCompleto();
        $nombreCarpeta = strtoupper($nombreCompleto);
        $nombreCarpeta = $this->removerAcentos($nombreCarpeta);
        $nombreCarpeta = preg_replace('/[^A-Z0-9]/', '', $nombreCarpeta);
        
        return $nombreCarpeta;
    }
    
    /**
     * Obtener RFC/CURP formateado
     */
    public function getRfcCurpFormateado(): string
    {
        $rfcCurp = trim($this->attributes['rfc_curp'] ?? '');
        
        if (empty($rfcCurp)) {
            return 'No proporcionado';
        }
        
        return strtoupper($rfcCurp);
    }
    
    /**
     * Verificar si tiene RFC/CURP válido
     */
    public function tieneRfcCurpValido(): bool
    {
        $rfcCurp = trim($this->attributes['rfc_curp'] ?? '');
        
        if (empty($rfcCurp)) {
            return false;
        }
        
        // Validar RFC (13 caracteres) o CURP (18 caracteres)
        $length = strlen($rfcCurp);
        return $length === 13 || $length === 18;
    }
    
    /**
     * Obtener tipo de documento (RFC o CURP)
     */
    public function getTipoDocumento(): string
    {
        $rfcCurp = trim($this->attributes['rfc_curp'] ?? '');
        
        if (empty($rfcCurp)) {
            return 'No proporcionado';
        }
        
        $length = strlen($rfcCurp);
        
        if ($length === 13) {
            return 'RFC';
        } elseif ($length === 18) {
            return 'CURP';
        } else {
            return 'Formato inválido';
        }
    }

    /**
     * Obtener texto del desarrollo
     */
    public function getDesarrolloTexto(): string
    {
        $desarrollos = [
            'valle_natura' => 'Valle Natura',
            'cordelia' => 'Cordelia'
        ];
        
        return $desarrollos[$this->attributes['desarrollo'] ?? ''] ?? 'Desarrollo no especificado';
    }

    /**
     * Obtener texto del medio de contacto
     */
    public function getMedioContactoTexto(): string
    {
        $medios = [
            'telefono' => 'Llamada Telefónica',
            'whatsapp' => 'WhatsApp'
        ];
        
        return $medios[$this->attributes['medio_contacto'] ?? ''] ?? 'No especificado';
    }

    /**
     * Verificar si tiene co-propietario
     */
    public function tieneCopropietario(): bool
    {
        return !empty(trim($this->attributes['nombre_copropietario'] ?? ''));
    }

    /**
     * Obtener información del co-propietario
     */
    public function getInfoCopropietario(): ?array
    {
        if (!$this->tieneCopropietario()) {
            return null;
        }
        
        return [
            'nombre' => trim($this->attributes['nombre_copropietario']),
            'parentesco' => $this->attributes['parentesco_copropietario'] ?? 'No especificado'
        ];
    }

    // ===============================================
    // ESTADOS DE SINCRONIZACIÓN
    // ===============================================

    /**
     * Verificar si está sincronizado con HubSpot
     */
    public function estaSincronizadoHubSpot(): bool
    {
        return ($this->attributes['hubspot_sync_status'] ?? '') === 'success';
    }

    /**
     * Verificar si falló la sincronización con HubSpot
     */
    public function falloSincronizacionHubSpot(): bool
    {
        return ($this->attributes['hubspot_sync_status'] ?? '') === 'failed';
    }

    /**
     * Verificar si está pendiente de sincronización con HubSpot
     */
    public function pendienteSincronizacionHubSpot(): bool
    {
        return ($this->attributes['hubspot_sync_status'] ?? '') === 'pending';
    }

    /**
     * Verificar si está sincronizado con Google Drive
     */
    public function estaSincronizadoGoogleDrive(): bool
    {
        return ($this->attributes['google_drive_sync_status'] ?? '') === 'success';
    }

    /**
     * Verificar si falló la sincronización con Google Drive
     */
    public function falloSincronizacionGoogleDrive(): bool
    {
        return ($this->attributes['google_drive_sync_status'] ?? '') === 'failed';
    }

    /**
     * Verificar si está pendiente de sincronización con Google Drive
     */
    public function pendienteSincronizacionGoogleDrive(): bool
    {
        return ($this->attributes['google_drive_sync_status'] ?? '') === 'pending';
    }

    /**
     * Obtener estado general de sincronización
     */
    public function getEstadoSincronizacion(): string
    {
        $hubspot = $this->attributes['hubspot_sync_status'] ?? 'pending';
        $googleDrive = $this->attributes['google_drive_sync_status'] ?? 'pending';
        
        if ($hubspot === 'success' && $googleDrive === 'success') {
            return 'completo';
        }
        
        if ($hubspot === 'failed' || $googleDrive === 'failed') {
            return 'con_errores';
        }
        
        return 'pendiente';
    }

    /**
     * Obtener badge HTML para el estado de sincronización
     */
    public function getBadgeEstadoSincronizacion(): string
    {
        $estado = $this->getEstadoSincronizacion();
        
        $badges = [
            'completo' => '<span class="badge badge-success">Completado</span>',
            'con_errores' => '<span class="badge badge-danger">Con Errores</span>',
            'pendiente' => '<span class="badge badge-warning">Pendiente</span>'
        ];
        
        return $badges[$estado] ?? '<span class="badge badge-secondary">Desconocido</span>';
    }

    // ===============================================
    // INFORMACIÓN DE CONTACTO
    // ===============================================

    /**
     * Obtener teléfono formateado
     */
    public function getTelefonoFormateado(): string
    {
        $telefono = $this->attributes['telefono'] ?? '';
        
        if (strlen($telefono) === 10) {
            // Formato: (555) 123-4567
            return sprintf('(%s) %s-%s', 
                substr($telefono, 0, 3),
                substr($telefono, 3, 3),
                substr($telefono, 6, 4)
            );
        }
        
        if (strlen($telefono) === 12 && substr($telefono, 0, 2) === '52') {
            // Formato mexicano con código de país: +52 555 123 4567
            return sprintf('+52 %s %s %s', 
                substr($telefono, 2, 3),
                substr($telefono, 5, 3),
                substr($telefono, 8, 4)
            );
        }
        
        return $telefono;
    }

    /**
     * Obtener enlace de WhatsApp
     */
    public function getEnlaceWhatsApp(): string
    {
        $telefono = preg_replace('/[^0-9]/', '', $this->attributes['telefono'] ?? '');
        
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono; // Agregar código de país de México
        }
        
        $mensaje = urlencode("Hola {$this->getNombreCompleto()}, nos ponemos en contacto desde ANVAR Inmobiliaria.");
        
        return "https://wa.me/{$telefono}?text={$mensaje}";
    }

    /**
     * Obtener enlace mailto
     */
    public function getEnlaceEmail(): string
    {
        $email = $this->attributes['email'] ?? '';
        $asunto = urlencode("ANVAR Inmobiliaria - Seguimiento de registro");
        $cuerpo = urlencode("Estimado(a) {$this->getNombreCompleto()},\n\nNos ponemos en contacto desde ANVAR Inmobiliaria...");
        
        return "mailto:{$email}?subject={$asunto}&body={$cuerpo}";
    }

    // ===============================================
    // INFORMACIÓN TEMPORAL
    // ===============================================

    /**
     * Obtener tiempo transcurrido desde el registro
     */
    public function getTiempoTranscurrido(): string
    {
        if (!isset($this->attributes['fecha_registro'])) {
            return 'Fecha no disponible';
        }
        
        $fechaRegistro = new DateTime($this->attributes['fecha_registro']);
        $ahora = new DateTime();
        $diferencia = $ahora->diff($fechaRegistro);
        
        if ($diferencia->days > 0) {
            return $diferencia->days === 1 ? 'Hace 1 día' : "Hace {$diferencia->days} días";
        }
        
        if ($diferencia->h > 0) {
            return $diferencia->h === 1 ? 'Hace 1 hora' : "Hace {$diferencia->h} horas";
        }
        
        if ($diferencia->i > 0) {
            return $diferencia->i === 1 ? 'Hace 1 minuto' : "Hace {$diferencia->i} minutos";
        }
        
        return 'Hace un momento';
    }

    /**
     * Obtener fecha de registro formateada
     */
    public function getFechaRegistroFormateada(string $formato = 'd/m/Y H:i'): string
    {
        if (!isset($this->attributes['fecha_registro'])) {
            return 'Fecha no disponible';
        }
        
        $fecha = new DateTime($this->attributes['fecha_registro']);
        return $fecha->format($formato);
    }

    // ===============================================
    // INFORMACIÓN TÉCNICA
    // ===============================================

    /**
     * Obtener información del navegador simplificada
     */
    public function getNavegadorSimplificado(): string
    {
        $userAgent = $this->attributes['user_agent'] ?? '';
        
        if (empty($userAgent)) {
            return 'No disponible';
        }
        
        // Detectar navegadores comunes
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        }
        
        if (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        }
        
        if (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            return 'Safari';
        }
        
        if (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        }
        
        return 'Otro navegador';
    }

    /**
     * Obtener sistema operativo simplificado
     */
    public function getSistemaOperativo(): string
    {
        $userAgent = $this->attributes['user_agent'] ?? '';
        
        if (empty($userAgent)) {
            return 'No disponible';
        }
        
        if (strpos($userAgent, 'Windows') !== false) {
            return 'Windows';
        }
        
        if (strpos($userAgent, 'Mac OS') !== false || strpos($userAgent, 'macOS') !== false) {
            return 'macOS';
        }
        
        if (strpos($userAgent, 'Linux') !== false) {
            return 'Linux';
        }
        
        if (strpos($userAgent, 'Android') !== false) {
            return 'Android';
        }
        
        if (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            return 'iOS';
        }
        
        return 'Otro SO';
    }

    // ===============================================
    // MÉTODOS DE VALIDACIÓN
    // ===============================================

    /**
     * Verificar si el registro está completo
     */
    public function estaCompleto(): bool
    {
        $camposRequeridos = [
            'firstname', 'lastname', 'email', 'mobilephone', 
            'desarrollo'
        ];
        
        foreach ($camposRequeridos as $campo) {
            if (empty(trim($this->attributes[$campo] ?? ''))) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Obtener lista de campos faltantes
     */
    public function getCamposFaltantes(): array
    {
        $camposRequeridos = [
            'firstname' => 'Nombre del titular',
            'lastname' => 'Apellido Paterno',
            'email' => 'Correo electrónico',
            'mobilephone' => 'WhatsApp',
            'desarrollo' => 'Desarrollo'
        ];
        
        $faltantes = [];
        
        foreach ($camposRequeridos as $campo => $etiqueta) {
            if (empty(trim($this->attributes[$campo] ?? ''))) {
                $faltantes[] = $etiqueta;
            }
        }
        
        return $faltantes;
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Remover acentos de una cadena
     */
    private function removerAcentos(string $cadena): string
    {
        $acentos = [
            'á' => 'A', 'à' => 'A', 'ä' => 'A', 'â' => 'A', 'ā' => 'A', 'ã' => 'A',
            'é' => 'E', 'è' => 'E', 'ë' => 'E', 'ê' => 'E', 'ē' => 'E',
            'í' => 'I', 'ì' => 'I', 'ï' => 'I', 'î' => 'I', 'ī' => 'I',
            'ó' => 'O', 'ò' => 'O', 'ö' => 'O', 'ô' => 'O', 'ō' => 'O', 'õ' => 'O',
            'ú' => 'U', 'ù' => 'U', 'ü' => 'U', 'û' => 'U', 'ū' => 'U',
            'ñ' => 'N',
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A', 'Ā' => 'A', 'Ã' => 'A',
            'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E', 'Ē' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I', 'Ī' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O', 'Ō' => 'O', 'Õ' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U', 'Ū' => 'U',
            'Ñ' => 'N'
        ];
        
        return strtr($cadena, $acentos);
    }

    /**
     * Convertir a array para API
     */
    public function toAPI(): array
    {
        return [
            'id' => $this->attributes['id'] ?? null,
            'folio' => $this->attributes['folio'] ?? null,
            'nombre_completo' => $this->getNombreCompleto(),
            'email' => $this->attributes['email'] ?? null,
            'telefono' => $this->getTelefonoFormateado(),
            'desarrollo' => $this->getDesarrolloTexto(),
            'medio_contacto' => $this->getMedioContactoTexto(),
            'tiene_copropietario' => $this->tieneCopropietario(),
            'info_copropietario' => $this->getInfoCopropietario(),
            'estado_sincronizacion' => $this->getEstadoSincronizacion(),
            'hubspot_sincronizado' => $this->estaSincronizadoHubSpot(),
            'google_drive_sincronizado' => $this->estaSincronizadoGoogleDrive(),
            'fecha_registro' => $this->getFechaRegistroFormateada(),
            'tiempo_transcurrido' => $this->getTiempoTranscurrido(),
            'agente_referido' => $this->attributes['agente_referido'] ?? null,
            'navegador' => $this->getNavegadorSimplificado(),
            'sistema_operativo' => $this->getSistemaOperativo()
        ];
    }

    /**
     * Obtener datos para HubSpot
     */
    public function toHubSpot(): array
    {
        return [
            'firstname' => $this->attributes['firstname'] ?? '',
            'lastname' => $this->attributes['lastname'] ?? '',
            'apellido_materno' => $this->attributes['apellido_materno'] ?? '',
            'rfc_curp' => $this->attributes['rfc_curp'] ?? '',
            'email' => $this->attributes['email'] ?? '',
            'mobilephone' => $this->attributes['mobilephone'] ?? '',
            'phone' => $this->attributes['phone'] ?? '',
            'medio_de_contacto' => $this->attributes['medio_de_contacto'] ?? '',
            'nombre_copropietario' => $this->attributes['nombre_copropietario'] ?? '',
            'parentesco_copropietario' => $this->attributes['parentesco_copropietario'] ?? '',
            'desarrollo' => $this->attributes['desarrollo'] ?? '',
            'manzana' => $this->attributes['manzana'] ?? '',
            'lote' => $this->attributes['lote'] ?? '',
            'numero_casa_depto' => $this->attributes['numero_casa_depto'] ?? '',
            'agente' => $this->attributes['agente_referido'] ?? '',
            'hubspot_owner_id' => '80110028', // Constante - Rodolfo Sandoval
            'lifecyclestage' => 'lead',
            'hs_lead_status' => 'NEW'
        ];
    }
}