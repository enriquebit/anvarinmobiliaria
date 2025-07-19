<?php

/**
 * Helper para gestión segura de documentos
 * Sistema universal para visualizar documentos sin exponer URLs reales
 * Funciona para cualquier tipo de documento en toda la aplicación
 */

if (!function_exists('verDocumento')) {
    /**
     * Genera enlace para ver documento en nueva pestaña sin exponer URL real
     * 
     * @param int $documentoId ID del documento en la base de datos
     * @param string $tipo Tipo de documento (staff, cliente, venta, proyecto, expediente, etc.)
     * @param array $opciones Opciones del enlace (class, icon, text, title, etc.)
     * @return string HTML del enlace
     */
    function verDocumento(int $documentoId, string $tipo = 'staff', array $opciones = []): string
    {
        $url = base_url("documento/ver/{$tipo}/{$documentoId}");
        
        // Opciones por defecto
        $predeterminadas = [
            'target' => '_blank',
            'class' => 'btn btn-sm btn-info',
            'title' => 'Ver documento',
            'icon' => '<i class="fas fa-eye"></i>',
            'text' => ''
        ];
        
        $opciones = array_merge($predeterminadas, $opciones);
        
        // Construir atributos HTML
        $atributos = '';
        foreach (['target', 'class', 'title'] as $attr) {
            if (isset($opciones[$attr])) {
                $atributos .= " {$attr}=\"{$opciones[$attr]}\"";
            }
        }
        
        $contenido = $opciones['icon'] . ($opciones['text'] ? ' ' . $opciones['text'] : '');
        
        return "<a href=\"{$url}\"{$atributos}>{$contenido}</a>";
    }
}

if (!function_exists('descargarDocumento')) {
    /**
     * Genera enlace para descargar documento
     * 
     * @param int $documentoId ID del documento
     * @param string $tipo Tipo de documento
     * @param array $opciones Opciones del enlace
     * @return string HTML del enlace
     */
    function descargarDocumento(int $documentoId, string $tipo = 'staff', array $opciones = []): string
    {
        $url = base_url("documento/descargar/{$tipo}/{$documentoId}");
        
        // Opciones por defecto
        $predeterminadas = [
            'class' => 'btn btn-sm btn-success',
            'title' => 'Descargar documento',
            'icon' => '<i class="fas fa-download"></i>',
            'text' => ''
        ];
        
        $opciones = array_merge($predeterminadas, $opciones);
        
        // Construir atributos HTML
        $atributos = '';
        foreach (['class', 'title'] as $attr) {
            if (isset($opciones[$attr])) {
                $atributos .= " {$attr}=\"{$opciones[$attr]}\"";
            }
        }
        
        $contenido = $opciones['icon'] . ($opciones['text'] ? ' ' . $opciones['text'] : '');
        
        return "<a href=\"{$url}\"{$atributos}>{$contenido}</a>";
    }
}

if (!function_exists('botonesDocumento')) {
    /**
     * Genera grupo de botones para ver y descargar documento
     * 
     * @param int $documentoId ID del documento
     * @param string $tipo Tipo de documento
     * @param array $opciones Opciones de personalización
     * @return string HTML del grupo de botones
     */
    function botonesDocumento(int $documentoId, string $tipo = 'staff', array $opciones = []): string
    {
        $claseGrupo = $opciones['class_group'] ?? 'btn-group';
        
        $html = "<div class=\"{$claseGrupo}\" role=\"group\">";
        
        // Botón ver
        if (!isset($opciones['solo_descargar']) || !$opciones['solo_descargar']) {
            $opcionesVer = $opciones['ver'] ?? [];
            $html .= verDocumento($documentoId, $tipo, $opcionesVer);
        }
        
        // Botón descargar
        if (!isset($opciones['solo_ver']) || !$opciones['solo_ver']) {
            $opcionesDescargar = $opciones['descargar'] ?? [];
            $html .= descargarDocumento($documentoId, $tipo, $opcionesDescargar);
        }
        
        $html .= "</div>";
        
        return $html;
    }
}

if (!function_exists('servirDocumento')) {
    /**
     * Sirve un documento verificando permisos - Función principal del sistema
     * 
     * @param int $documentoId ID del documento
     * @param string $tipo Tipo de documento
     * @param bool $forzarDescarga True para descargar, false para visualizar
     * @return mixed Response
     */
    function servirDocumento(int $documentoId, string $tipo = 'staff', bool $forzarDescarga = false)
    {
        $response = \Config\Services::response();
        
        // Verificar autenticación
        if (!auth()->loggedIn()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Obtener documento según tipo
        $infoDocumento = obtenerInfoDocumento($documentoId, $tipo);
        
        if (!$infoDocumento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Verificar permisos
        if (!puedeAccederDocumento($documentoId, $tipo)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Obtener ruta del archivo
        $rutaArchivo = obtenerRutaArchivo($infoDocumento);
        
        if (!$rutaArchivo || !file_exists($rutaArchivo)) {
            log_message('error', "Archivo no encontrado: {$rutaArchivo}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Preparar respuesta
        $mimeType = $infoDocumento['mime_type'] ?? mime_content_type($rutaArchivo) ?? 'application/octet-stream';
        $nombreArchivo = $infoDocumento['nombre_archivo'] ?? basename($rutaArchivo);
        $disposition = $forzarDescarga ? 'attachment' : 'inline';
        
        // Headers de seguridad
        $response->setHeader('Content-Type', $mimeType);
        $response->setHeader('Content-Disposition', $disposition . '; filename="' . $nombreArchivo . '"');
        $response->setHeader('Content-Length', filesize($rutaArchivo));
        $response->setHeader('Cache-Control', 'private, max-age=3600');
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        
        // Servir archivo
        return $response->setBody(file_get_contents($rutaArchivo));
    }
}

if (!function_exists('obtenerInfoDocumento')) {
    /**
     * Obtiene información del documento según su tipo
     * Centraliza la lógica para diferentes tipos de documentos
     * 
     * @param int $documentoId ID del documento
     * @param string $tipo Tipo de documento
     * @return array|null Información del documento
     */
    function obtenerInfoDocumento(int $documentoId, string $tipo): ?array
    {
        $documento = null;
        
        // Mapa de tipos a modelos
        $modelosDocumentos = [
            'staff' => \App\Models\DocumentoStaffModel::class,
            'cliente' => \App\Models\DocumentoClienteModel::class,
            'venta' => \App\Models\VentaDocumentoModel::class,
            'proyecto' => \App\Models\DocumentoProyectoModel::class,
            'expediente' => \App\Models\ExpedienteDocumentoModel::class,
            'lead' => \App\Models\DocumentoLeadModel::class,
            'cobranza' => \App\Models\CobranzaDocumentoModel::class,
            'pago' => \App\Models\PagoDocumentoModel::class
        ];
        
        if (isset($modelosDocumentos[$tipo])) {
            $modelo = new $modelosDocumentos[$tipo]();
            $documento = $modelo->find($documentoId);
        }
        
        return $documento ? $documento->toArray() : null;
    }
}

if (!function_exists('puedeAccederDocumento')) {
    /**
     * Verifica si el usuario actual puede acceder a un documento
     * Centraliza toda la lógica de permisos
     * 
     * @param int $documentoId ID del documento
     * @param string $tipo Tipo de documento
     * @return bool
     */
    function puedeAccederDocumento(int $documentoId, string $tipo): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        $user = auth()->user();
        
        // Superadmin tiene acceso total
        if ($user->inGroup('superadmin')) {
            return true;
        }
        
        // Lógica específica por tipo
        switch ($tipo) {
            case 'staff':
                // Usuario puede ver sus propios documentos
                $staffModel = new \App\Models\StaffModel();
                $staff = $staffModel->where('user_id', $user->id)->first();
                
                if ($staff) {
                    $docModel = new \App\Models\DocumentoStaffModel();
                    $doc = $docModel->where('id', $documentoId)
                                   ->where('staff_id', $staff->id)
                                   ->first();
                    return !empty($doc);
                }
                
                // Admin puede ver todos los documentos de staff
                return $user->inGroup('admin');
                
            case 'cliente':
                // Solo admin puede ver documentos de clientes
                return $user->inGroup('admin');
                
            case 'venta':
                // Admin y vendedores pueden ver documentos de ventas
                return $user->inGroup('admin', 'vendedor', 'supervendedor');
                
            case 'proyecto':
            case 'expediente':
                // Admin y managers
                return $user->inGroup('admin', 'manager');
                
            case 'lead':
                // Admin, vendedores y managers
                return $user->inGroup('admin', 'vendedor', 'supervendedor', 'manager');
                
            case 'cobranza':
            case 'pago':
                // Admin y personal de cobranza
                return $user->inGroup('admin', 'cobranza');
                
            default:
                // Por defecto, solo admin
                return $user->inGroup('admin');
        }
    }
}

if (!function_exists('obtenerRutaArchivo')) {
    /**
     * Obtiene la ruta completa del archivo desde la información del documento
     * 
     * @param array $infoDocumento Información del documento
     * @return string|null Ruta completa del archivo
     */
    function obtenerRutaArchivo(array $infoDocumento): ?string
    {
        // Posibles campos que contienen la ruta
        $camposRuta = ['ruta_archivo', 'archivo', 'path', 'file_path', 'ruta'];
        
        $rutaRelativa = null;
        foreach ($camposRuta as $campo) {
            if (!empty($infoDocumento[$campo])) {
                $rutaRelativa = $infoDocumento[$campo];
                break;
            }
        }
        
        if (!$rutaRelativa) {
            return null;
        }
        
        // Si ya es ruta absoluta y existe
        if (file_exists($rutaRelativa)) {
            return $rutaRelativa;
        }
        
        // Intentar diferentes ubicaciones
        $ubicaciones = [
            WRITEPATH . $rutaRelativa,
            WRITEPATH . 'uploads/' . $rutaRelativa,
            FCPATH . $rutaRelativa,
            FCPATH . 'uploads/' . $rutaRelativa
        ];
        
        foreach ($ubicaciones as $ubicacion) {
            if (file_exists($ubicacion)) {
                return $ubicacion;
            }
        }
        
        return null;
    }
}

if (!function_exists('listaDocumentos')) {
    /**
     * Genera lista HTML de documentos con botones de acción
     * Útil para mostrar documentos en vistas
     * 
     * @param array $documentos Array de documentos
     * @param string $tipo Tipo de documentos
     * @param array $opciones Opciones de presentación
     * @return string HTML
     */
    function listaDocumentos(array $documentos, string $tipo = 'staff', array $opciones = []): string
    {
        if (empty($documentos)) {
            return '<p class="text-muted">No hay documentos disponibles</p>';
        }
        
        $claseContenedor = $opciones['class_container'] ?? 'list-group';
        $html = "<div class=\"{$claseContenedor}\">";
        
        foreach ($documentos as $doc) {
            $claseItem = $opciones['class_item'] ?? 'list-group-item d-flex justify-content-between align-items-center';
            $html .= "<div class=\"{$claseItem}\">";
            
            // Información del documento
            $nombre = $doc['nombre_archivo'] ?? $doc['tipo_documento'] ?? 'Documento';
            $fecha = isset($doc['created_at']) ? date('d/m/Y H:i', strtotime($doc['created_at'])) : '';
            
            $html .= "<div>";
            $html .= "<strong>{$nombre}</strong>";
            if ($fecha) {
                $html .= " <small class=\"text-muted\">({$fecha})</small>";
            }
            $html .= "</div>";
            
            // Botones de acción
            $html .= botonesDocumento($doc['id'], $tipo);
            
            $html .= "</div>";
        }
        
        $html .= "</div>";
        
        return $html;
    }
}

// Mantener compatibilidad con versiones anteriores
if (!function_exists('getSecureFileUrl')) {
    function getSecureFileUrl(string $filePath, string $type = 'staff'): string
    {
        $encodedPath = base64_encode($filePath);
        return base_url("secure/{$type}/{$encodedPath}");
    }
}

if (!function_exists('getSecureAvatarUrl')) {
    function getSecureAvatarUrl(string $rfc, string $filename): string
    {
        $filePath = "staff/{$rfc}/{$filename}";
        return getSecureFileUrl($filePath, 'avatar');
    }
}