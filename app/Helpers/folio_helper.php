<?php

/**
 * HELPER PARA GENERACIÓN DE FOLIOS
 * Reemplaza la funcionalidad de los triggers MySQL
 * que requerían privilegios SUPER no disponibles en hosting
 */

if (!function_exists('generarFolio')) {
    /**
     * Genera un folio único para registro de clientes
     * Formato: REG-YYYY-XXXXXX
     * 
     * @param string $tabla Nombre de la tabla (registro_clientes)
     * @param string $prefijo Prefijo del folio (REG por defecto)
     * @return string Folio generado
     */
    function generarFolio(string $tabla = 'registro_clientes', string $prefijo = 'REG'): string
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table($tabla);
            
            $year = date('Y');
            
            // Contar registros del año actual para generar secuencia
            // Determinar el campo de fecha según la tabla
            $campoFecha = match($tabla) {
                'ventas' => 'created_at',
                'registro_clientes' => 'fecha_registro',
                default => 'created_at'
            };
            
            $count = $builder->where("YEAR({$campoFecha})", $year)
                           ->countAllResults();
            
            // Incrementar para el siguiente folio
            $nextNumber = $count + 1;
            
            // Generar folio con formato: REG-2025-000001
            $folio = sprintf('%s-%s-%06d', $prefijo, $year, $nextNumber);
            
            log_message('info', "📄 Folio generado: {$folio} para tabla {$tabla}");
            
            return $folio;
            
        } catch (\Exception $e) {
            log_message('error', "❌ Error generando folio para {$tabla}: " . $e->getMessage());
            
            // Fallback: folio con timestamp si hay error
            $fallback = sprintf('%s-%s-%s', $prefijo, date('Y'), substr(time(), -6));
            log_message('warning', "⚠️ Usando folio fallback: {$fallback}");
            
            return $fallback;
        }
    }
}

if (!function_exists('generarFolioConId')) {
    /**
     * Genera un folio usando el ID del registro ya insertado
     * Útil cuando se necesita el ID real de la base de datos
     * 
     * @param int $id ID del registro insertado
     * @param string $prefijo Prefijo del folio
     * @return string Folio generado
     */
    function generarFolioConId(int $id, string $prefijo = 'REG'): string
    {
        $year = date('Y');
        $folio = sprintf('%s-%s-%06d', $prefijo, $year, $id);
        
        log_message('info', "📄 Folio generado con ID real: {$folio}");
        
        return $folio;
    }
}

if (!function_exists('validarFormatoFolio')) {
    /**
     * Valida que un folio tenga el formato correcto
     * 
     * @param string $folio Folio a validar
     * @return bool True si es válido, false si no
     */
    function validarFormatoFolio(string $folio): bool
    {
        // Patrón: REG-YYYY-NNNNNN
        $patron = '/^[A-Z]{2,5}-\d{4}-\d{6}$/';
        
        return preg_match($patron, $folio) === 1;
    }
}

if (!function_exists('extraerAnoDelFolio')) {
    /**
     * Extrae el año de un folio
     * 
     * @param string $folio Folio del cual extraer el año
     * @return int|null Año extraído o null si no es válido
     */
    function extraerAnoDelFolio(string $folio): ?int
    {
        if (!validarFormatoFolio($folio)) {
            return null;
        }
        
        $partes = explode('-', $folio);
        return isset($partes[1]) ? (int)$partes[1] : null;
    }
}

if (!function_exists('extraerNumeroDelFolio')) {
    /**
     * Extrae el número secuencial de un folio
     * 
     * @param string $folio Folio del cual extraer el número
     * @return int|null Número extraído o null si no es válido
     */
    function extraerNumeroDelFolio(string $folio): ?int
    {
        if (!validarFormatoFolio($folio)) {
            return null;
        }
        
        $partes = explode('-', $folio);
        return isset($partes[2]) ? (int)$partes[2] : null;
    }
}

if (!function_exists('generar_folio_venta')) {
    /**
     * Genera un folio único para ventas
     * Formato: VTA-YYYY-XXXXXX
     * 
     * @return string Folio generado
     */
    function generar_folio_venta(): string
    {
        return generarFolio('ventas', 'VTA');
    }
}

if (!function_exists('generar_folio_pago')) {
    /**
     * Genera un folio único para pagos
     * Formato: PAG-YYYY-XXXXXX
     * 
     * @return string Folio generado
     */
    function generar_folio_pago(): string
    {
        return generarFolio('pagos_ventas', 'PAG');
    }
}