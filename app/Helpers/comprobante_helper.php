<?php

/**
 * HELPER PARA MANEJO DE COMPROBANTES DE PAGO
 * Maneja upload de archivos organizados por RFC/CURP/ID
 */

if (!function_exists('obtenerDirectorioCliente')) {
    /**
     * Obtiene el directorio del cliente basado en RFC/CURP/ID
     * Prioridad: RFC > CURP > ID
     * 
     * @param array $cliente Datos del cliente con rfc, curp, id
     * @return string Nombre del directorio
     */
    function obtenerDirectorioCliente(array $cliente): string
    {
        // Prioridad 1: RFC
        if (!empty($cliente['rfc']) && $cliente['rfc'] !== 'Sin RFC' && strlen($cliente['rfc']) >= 10) {
            return strtoupper($cliente['rfc']);
        }
        
        // Prioridad 2: CURP  
        if (!empty($cliente['curp']) && strlen($cliente['curp']) >= 18) {
            return strtoupper($cliente['curp']);
        }
        
        // Prioridad 3: ID con prefijo
        return 'SIN_RFC_' . $cliente['id'];
    }
}

if (!function_exists('subirComprobanteVenta')) {
    /**
     * Sube comprobante de venta a la carpeta del cliente
     * 
     * @param array $archivo Archivo $_FILES
     * @param array $cliente Datos del cliente
     * @param int $ventaId ID de la venta
     * @param string $tipoComprobante Tipo de comprobante
     * @return array Resultado del upload
     */
    function subirComprobanteVenta(array $archivo, array $cliente, int $ventaId, string $tipoComprobante = 'pago'): array
    {
        try {
            // Validar archivo
            if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
                return [
                    'success' => false,
                    'message' => 'No se recibió archivo válido'
                ];
            }
            
            // Validar tamaño (máx 15MB)
            $maxSize = 15 * 1024 * 1024; // 15MB
            if ($archivo['size'] > $maxSize) {
                return [
                    'success' => false,
                    'message' => 'El archivo excede el tamaño máximo de 15MB'
                ];
            }
            
            // Validar tipo de archivo
            $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $tiposPermitidos)) {
                return [
                    'success' => false,
                    'message' => 'Tipo de archivo no permitido. Solo PDF, JPG, PNG'
                ];
            }
            
            // Obtener directorio del cliente
            $directorioCliente = obtenerDirectorioCliente($cliente);
            $rutaBase = FCPATH . 'uploads/clientes/' . $directorioCliente;
            
            // Crear directorio y configurar permisos universales
            configurarPermisosDirectorio($rutaBase);
            
            // Generar nombre del archivo
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArchivo = sprintf(
                '%s_comprobante_%s_venta_%d.%s',
                $directorioCliente,
                $tipoComprobante,
                $ventaId,
                $extension
            );
            
            $rutaCompleta = $rutaBase . '/' . $nombreArchivo;
            
            // Mover archivo
            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                return [
                    'success' => false,
                    'message' => 'Error al guardar el archivo'
                ];
            }
            
            // Log del upload
            log_message('info', "📄 Comprobante subido: {$nombreArchivo} para cliente {$cliente['id']}");
            
            return [
                'success' => true,
                'archivo' => $nombreArchivo,
                'ruta' => $rutaCompleta,
                'directorio' => $directorioCliente,
                'url' => base_url('uploads/clientes/' . $directorioCliente . '/' . $nombreArchivo),
                'message' => 'Comprobante subido exitosamente'
            ];
            
        } catch (\Exception $e) {
            log_message('error', "❌ Error subiendo comprobante: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error interno al procesar el archivo'
            ];
        }
    }
}

if (!function_exists('validarArchivoComprobante')) {
    /**
     * Valida archivo de comprobante antes del upload
     * 
     * @param array $archivo Archivo $_FILES
     * @return array Resultado de la validación
     */
    function validarArchivoComprobante(array $archivo): array
    {
        $errores = [];
        
        // Verificar que se subió archivo
        if (empty($archivo['tmp_name'])) {
            return [
                'valido' => true, // Es opcional
                'errores' => []
            ];
        }
        
        // Verificar errores de upload
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'Error al subir archivo: ' . $archivo['error'];
        }
        
        // Verificar tamaño
        $maxSize = 15 * 1024 * 1024; // 15MB
        if ($archivo['size'] > $maxSize) {
            $errores[] = 'El archivo excede 15MB';
        }
        
        // Verificar tipo
        if (!empty($archivo['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $archivo['tmp_name']);
            finfo_close($finfo);
            
            $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!in_array($mimeType, $tiposPermitidos)) {
                $errores[] = 'Solo se permiten archivos PDF, JPG, PNG';
            }
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}

if (!function_exists('configurarPermisosDirectorio')) {
    /**
     * Configura permisos 777 (lectura/escritura universal) para un directorio
     * 
     * @param string $ruta Ruta del directorio
     * @return bool True si se configuró correctamente
     */
    function configurarPermisosDirectorio(string $ruta): bool
    {
        try {
            // Crear directorio si no existe
            if (!is_dir($ruta)) {
                mkdir($ruta, 0777, true);
            }
            
            // Configurar permisos 777
            chmod($ruta, 0777);
            
            // Configurar permisos para archivos existentes
            $archivos = glob($ruta . '/*');
            foreach ($archivos as $archivo) {
                if (is_file($archivo)) {
                    chmod($archivo, 0777);
                } elseif (is_dir($archivo)) {
                    configurarPermisosDirectorio($archivo); // Recursivo
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            log_message('error', "❌ Error configurando permisos para {$ruta}: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('obtenerRutaComprobante')) {
    /**
     * Obtiene la ruta completa de un comprobante existente
     * 
     * @param array $cliente Datos del cliente
     * @param int $ventaId ID de la venta
     * @param string $tipoComprobante Tipo de comprobante
     * @return string|null Ruta del archivo o null si no existe
     */
    function obtenerRutaComprobante(array $cliente, int $ventaId, string $tipoComprobante = 'pago'): ?string
    {
        $directorioCliente = obtenerDirectorioCliente($cliente);
        $rutaBase = FCPATH . 'uploads/clientes/' . $directorioCliente;
        
        if (!is_dir($rutaBase)) {
            return null;
        }
        
        // Buscar archivo con patrón
        $patron = sprintf(
            '%s_comprobante_%s_venta_%d.*',
            $directorioCliente,
            $tipoComprobante,
            $ventaId
        );
        
        $archivos = glob($rutaBase . '/' . $patron);
        
        return !empty($archivos) ? $archivos[0] : null;
    }
}