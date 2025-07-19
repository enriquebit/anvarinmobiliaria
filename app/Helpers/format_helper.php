<?php

/**
 * Helper de Formateo General para el Sistema ANVAR
 * Funciones auxiliares para formatear datos
 */

if (!function_exists('formatPrecio')) {
    /**
     * Formatear precio en formato moneda mexicana - FUNCIÓN ÚNICA DEL PROYECTO
     * 
     * @param float $precio
     * @return string
     */
    function formatPrecio($precio): string
    {
        if (!$precio && $precio !== 0) return '$0.00';
        $num = floatval($precio);
        return '$' . number_format($num, 2, '.', ',');
    }
}

// Alias para mantener compatibilidad
if (!function_exists('formatear_moneda_mexicana')) {
    function formatear_moneda_mexicana(float $monto): string
    {
        return formatPrecio($monto);
    }
}

if (!function_exists('formatPrecioJs')) {
    /**
     * Formatear precio para JavaScript con formato mexicano completo
     * Genera función JavaScript reutilizable
     * 
     * @return string
     */
    function formatPrecioJs(): string
    {
        return '
        function formatearMoneda(numero) {
            if (!numero && numero !== 0) return "$0.00";
            const num = parseFloat(numero);
            return num.toLocaleString("es-MX", {
                style: "currency",
                currency: "MXN",
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }';
    }
}

if (!function_exists('formatFecha')) {
    /**
     * Formatear fecha en formato legible
     * 
     * @param mixed $fecha
     * @param string $formato
     * @return string
     */
    function formatFecha($fecha, string $formato = 'd/m/Y'): string
    {
        if (empty($fecha)) {
            return '';
        }

        if ($fecha instanceof \DateTime) {
            return $fecha->format($formato);
        }

        if (is_string($fecha)) {
            try {
                $dateTime = new \DateTime($fecha);
                return $dateTime->format($formato);
            } catch (\Exception $e) {
                return '';
            }
        }

        return '';
    }
}

if (!function_exists('formatFechaHora')) {
    /**
     * Formatear fecha y hora en formato legible
     * 
     * @param mixed $fecha
     * @return string
     */
    function formatFechaHora($fecha): string
    {
        return formatFecha($fecha, 'd/m/Y H:i');
    }
}

if (!function_exists('formatNumero')) {
    /**
     * Formatear número con separadores de miles
     * 
     * @param float $numero
     * @param int $decimales
     * @return string
     */
    function formatNumero($numero, int $decimales = 2): string
    {
        return number_format($numero, $decimales, '.', ',');
    }
}

if (!function_exists('formatPorcentaje')) {
    /**
     * Formatear porcentaje
     * 
     * @param float $porcentaje
     * @param int $decimales
     * @return string
     */
    function formatPorcentaje($porcentaje, int $decimales = 2): string
    {
        return number_format($porcentaje, $decimales, '.', ',') . '%';
    }
}

if (!function_exists('formatTelefono')) {
    /**
     * Formatear número de teléfono mexicano
     * 
     * @param string $telefono
     * @return string
     */
    function formatTelefono(string $telefono): string
    {
        // Eliminar caracteres no numéricos
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        
        // Si tiene 10 dígitos, formatear como (XXX) XXX-XXXX
        if (strlen($telefono) == 10) {
            return sprintf('(%s) %s-%s', 
                substr($telefono, 0, 3),
                substr($telefono, 3, 3),
                substr($telefono, 6, 4)
            );
        }
        
        return $telefono;
    }
}

if (!function_exists('formatMetrosCuadrados')) {
    /**
     * Formatear metros cuadrados
     * 
     * @param float $metros
     * @return string
     */
    function formatMetrosCuadrados($metros): string
    {
        return formatNumero($metros, 2) . ' m²';
    }
}

if (!function_exists('formatFolio')) {
    /**
     * Formatear folio con ceros a la izquierda
     * 
     * @param int $numero
     * @param int $longitud
     * @param string $prefijo
     * @return string
     */
    function formatFolio(int $numero, int $longitud = 6, string $prefijo = ''): string
    {
        return $prefijo . str_pad($numero, $longitud, '0', STR_PAD_LEFT);
    }
}