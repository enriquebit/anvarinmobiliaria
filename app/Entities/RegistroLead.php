<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use DateTime;

class RegistroLead extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'fecha_registro', 
        'fecha_actualizacion', 
        'hubspot_last_sync',
        'google_drive_last_sync',
        'fecha_conversion',
        'created_at',
        'updated_at'
    ];
    protected $casts   = [
        'id' => 'integer',
        'acepta_terminos' => 'boolean',
        'hubspot_sync_attempts' => 'integer',
        'google_drive_sync_attempts' => 'integer',
        'activo' => 'boolean',
        'convertido_a_cliente' => 'boolean',
        'cliente_id' => 'integer'
    ];

    // ===============================================
    // PROPIEDADES CALCULADAS
    // ===============================================

    /**
     * Obtener nombre completo del lead
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
     * Obtener identificador de expediente (RFC prioritario, CURP alternativo)
     */
    public function getIdentificadorExpediente(): string
    {
        // Prioridad: RFC > CURP > identificador_expediente > fallback
        $rfc = trim($this->attributes['rfc'] ?? '');
        $curp = trim($this->attributes['curp'] ?? '');
        $identificador = trim($this->attributes['identificador_expediente'] ?? '');
        
        if (!empty($rfc)) {
            return strtoupper($rfc);
        }
        
        if (!empty($curp)) {
            return strtoupper($curp);
        }
        
        if (!empty($identificador)) {
            return strtoupper($identificador);
        }
        
        // Fallback: generar ID basado en nombre
        $nombreCompleto = $this->getNombreCompleto();
        $fallback = 'LEAD_' . $this->attributes['id'] . '_' . strtoupper(substr($nombreCompleto, 0, 3));
        return $this->limpiarIdentificador($fallback);
    }

    /**
     * Obtener nombre para carpeta de expediente
     */
    public function getNombreCarpetaExpediente(): string
    {
        $identificador = $this->getIdentificadorExpediente();
        return $this->limpiarIdentificador($identificador);
    }

    /**
     * Verificar si tiene RFC válido
     */
    public function tieneRfcValido(): bool
    {
        $rfc = trim($this->attributes['rfc'] ?? '');
        return !empty($rfc) && strlen($rfc) === 13;
    }

    /**
     * Verificar si tiene CURP válido
     */
    public function tieneCurpValido(): bool
    {
        $curp = trim($this->attributes['curp'] ?? '');
        return !empty($curp) && strlen($curp) === 18;
    }

    /**
     * Obtener tipo de identificador principal
     */
    public function getTipoIdentificadorPrincipal(): string
    {
        if ($this->tieneRfcValido()) {
            return 'RFC';
        }
        
        if ($this->tieneCurpValido()) {
            return 'CURP';
        }
        
        return 'Identificador generado';
    }

    /**
     * Verificar si está convertido a cliente
     */
    public function estaConvertidoACliente(): bool
    {
        return ($this->attributes['convertido_a_cliente'] ?? 0) == 1;
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
            'whatsapp' => 'WhatsApp',
            'llamada_telefonica' => 'Llamada Telefónica'
        ];
        
        return $medios[$this->attributes['medio_de_contacto'] ?? ''] ?? 'No especificado';
    }

    /**
     * Obtener texto de la etapa del proceso
     */
    public function getEtapaProcesoTexto(): string
    {
        $etapas = [
            'pendiente' => 'Pendiente de revisión',
            'calificado' => 'Calificado como prospecto',
            'enviar_documento_para_firma' => 'Preparar envío de documento',
            'documento_enviado_para_firma' => 'Documento enviado para firma',
            'documento_recibido_firmado' => 'Documento recibido firmado'
        ];
        
        return $etapas[$this->attributes['etapa_proceso'] ?? 'pendiente'] ?? 'Estado desconocido';
    }

    /**
     * Obtener badge HTML para la etapa del proceso
     */
    public function getBadgeEtapaProceso(): string
    {
        $etapa = $this->attributes['etapa_proceso'] ?? 'pendiente';
        
        $badges = [
            'pendiente' => '<span class="badge badge-secondary">Pendiente</span>',
            'calificado' => '<span class="badge badge-info">Calificado</span>',
            'enviar_documento_para_firma' => '<span class="badge badge-warning">Por enviar documento</span>',
            'documento_enviado_para_firma' => '<span class="badge badge-primary">Documento enviado</span>',
            'documento_recibido_firmado' => '<span class="badge badge-success">Documento firmado</span>'
        ];
        
        return $badges[$etapa] ?? '<span class="badge badge-dark">Desconocido</span>';
    }

    /**
     * Verificar si puede convertirse a cliente
     */
    public function puedeConvertirseACliente(): bool
    {
        return !$this->estaConvertidoACliente() && 
               $this->attributes['etapa_proceso'] === 'documento_recibido_firmado' &&
               ($this->tieneRfcValido() || $this->tieneCurpValido());
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
            'completo' => '<span class="badge badge-success">Sincronizado</span>',
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
        $telefono = $this->attributes['telefono'] ?? $this->attributes['mobilephone'] ?? '';
        
        if (strlen($telefono) === 10) {
            return sprintf('(%s) %s-%s', 
                substr($telefono, 0, 3),
                substr($telefono, 3, 3),
                substr($telefono, 6, 4)
            );
        }
        
        if (strlen($telefono) === 12 && substr($telefono, 0, 2) === '52') {
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
        $telefono = preg_replace('/[^0-9]/', '', $this->attributes['mobilephone'] ?? $this->attributes['telefono'] ?? '');
        
        if (strlen($telefono) === 10) {
            $telefono = '52' . $telefono;
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
        $asunto = urlencode("ANVAR Inmobiliaria - Seguimiento de lead");
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

    /**
     * Obtener fecha de conversión formateada
     */
    public function getFechaConversionFormateada(string $formato = 'd/m/Y H:i'): ?string
    {
        if (!isset($this->attributes['fecha_conversion'])) {
            return null;
        }
        
        $fecha = new DateTime($this->attributes['fecha_conversion']);
        return $fecha->format($formato);
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
     * Verificar si tiene identificador válido para expediente
     */
    public function tieneIdentificadorValidoExpediente(): bool
    {
        return $this->tieneRfcValido() || $this->tieneCurpValido();
    }

    // ===============================================
    // RUTAS DE ARCHIVOS Y CARPETAS
    // ===============================================

    /**
     * Obtener ruta base para carpeta de lead
     */
    public function getRutaCarpetaLead(): string
    {
        $identificador = $this->getNombreCarpetaExpediente();
        return "leads/{$identificador}";
    }

    /**
     * Obtener ruta para un tipo específico de documento
     */
    public function getRutaDocumento(string $tipoDocumento, string $extension = 'pdf'): string
    {
        $identificador = $this->getNombreCarpetaExpediente();
        return "leads/{$identificador}/{$tipoDocumento}_{$identificador}.{$extension}";
    }

    /**
     * Obtener todas las rutas de documentos esperados
     */
    public function getRutasDocumentosEsperados(): array
    {
        $identificador = $this->getNombreCarpetaExpediente();
        $tiposDocumentos = [
            'ine_frontal',
            'ine_trasera', 
            'comprobante_ingresos',
            'comprobante_domicilio',
            'acta_nacimiento'
        ];
        
        $rutas = [];
        foreach ($tiposDocumentos as $tipo) {
            $rutas[$tipo] = "leads/{$identificador}/{$tipo}_{$identificador}.pdf";
        }
        
        return $rutas;
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Limpiar identificador para uso en nombres de archivos y carpetas
     */
    private function limpiarIdentificador(string $identificador): string
    {
        // Convertir a mayúsculas y remover acentos
        $identificador = strtoupper($this->removerAcentos($identificador));
        
        // Mantener solo letras, números y guiones bajos
        $identificador = preg_replace('/[^A-Z0-9_]/', '', $identificador);
        
        return $identificador;
    }

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

    // ===============================================
    // MÉTODOS DE EXPORTACIÓN
    // ===============================================

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
            'etapa_proceso' => $this->getEtapaProcesoTexto(),
            'identificador_expediente' => $this->getIdentificadorExpediente(),
            'tipo_identificador' => $this->getTipoIdentificadorPrincipal(),
            'convertido_a_cliente' => $this->estaConvertidoACliente(),
            'puede_convertirse' => $this->puedeConvertirseACliente(),
            'tiene_copropietario' => $this->tieneCopropietario(),
            'info_copropietario' => $this->getInfoCopropietario(),
            'estado_sincronizacion' => $this->getEstadoSincronizacion(),
            'hubspot_sincronizado' => $this->estaSincronizadoHubSpot(),
            'google_drive_sincronizado' => $this->estaSincronizadoGoogleDrive(),
            'fecha_registro' => $this->getFechaRegistroFormateada(),
            'fecha_conversion' => $this->getFechaConversionFormateada(),
            'tiempo_transcurrido' => $this->getTiempoTranscurrido(),
            'agente_referido' => $this->attributes['agente_referido'] ?? null,
            'activo' => ($this->attributes['activo'] ?? 1) == 1
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
            'rfc' => $this->attributes['rfc'] ?? '',
            'curp' => $this->attributes['curp'] ?? '',
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
            'etapa_proceso' => $this->attributes['etapa_proceso'] ?? 'pendiente',
            'hubspot_owner_id' => '80110028', // Constante - Rodolfo Sandoval
            'lifecyclestage' => 'lead',
            'hs_lead_status' => 'NEW'
        ];
    }

    /**
     * Obtener datos para conversión a cliente
     */
    public function toClienteData(): array
    {
        return [
            'nombres' => $this->attributes['firstname'] ?? '',
            'apellido_paterno' => $this->attributes['lastname'] ?? '',
            'apellido_materno' => $this->attributes['apellido_materno'] ?? '',
            'rfc' => $this->attributes['rfc'] ?? '',
            'curp' => $this->attributes['curp'] ?? '',
            'email' => $this->attributes['email'] ?? '',
            'telefono_movil' => $this->attributes['mobilephone'] ?? '',
            'telefono_fijo' => $this->attributes['phone'] ?? '',
            'medio_contacto_preferido' => $this->attributes['medio_de_contacto'] ?? 'whatsapp',
            'desarrollo_interes' => $this->attributes['desarrollo'] ?? '',
            'manzana_interes' => $this->attributes['manzana'] ?? '',
            'lote_interes' => $this->attributes['lote'] ?? '',
            'numero_casa_depto' => $this->attributes['numero_casa_depto'] ?? '',
            'nombre_copropietario' => $this->attributes['nombre_copropietario'] ?? '',
            'parentesco_copropietario' => $this->attributes['parentesco_copropietario'] ?? '',
            'agente_asignado' => $this->attributes['agente_referido'] ?? '',
            'origen_lead' => 'formulario_web',
            'fecha_origen_lead' => $this->attributes['fecha_registro'],
            'notas_conversion' => "Cliente convertido desde lead ID: {$this->attributes['id']}. Etapa previa: {$this->getEtapaProcesoTexto()}",
            'activo' => 1
        ];
    }
}