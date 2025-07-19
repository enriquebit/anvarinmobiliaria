<?php

/**
 * Helper para generar avatares SVG con iniciales
 * Evita dependencias externas y errores de red
 */

if (!function_exists('generateAvatar')) {
    /**
     * Genera un avatar SVG con iniciales
     * 
     * @param string $text      Texto para las iniciales (nombre completo o email)
     * @param int    $size      Tamaño del avatar en píxeles
     * @param string $bgColor   Color de fondo en formato hex
     * @param string $textColor Color del texto en formato hex
     * @return string           Data URI del SVG
     */
    function generateAvatar(string $text = '', int $size = 32, string $bgColor = '#28a745', string $textColor = '#ffffff'): string
    {
        // Obtener iniciales
        $initials = getInitials($text);
        
        // Calcular tamaño de fuente proporcional
        $fontSize = round($size * 0.45);
        
        // Crear SVG
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
            <rect width="{$size}" height="{$size}" fill="{$bgColor}" rx="{$size}" ry="{$size}"/>
            <text x="50%" y="50%" fill="{$textColor}" font-family="Arial, sans-serif" font-size="{$fontSize}" font-weight="bold" text-anchor="middle" dy=".35em">
                {$initials}
            </text>
        </svg>
        SVG;
        
        // Convertir a data URI
        $svgEncoded = base64_encode($svg);
        return "data:image/svg+xml;base64,{$svgEncoded}";
    }
}

if (!function_exists('getInitials')) {
    /**
     * Extrae las iniciales de un texto
     * 
     * @param string $text Nombre completo o email
     * @return string      Iniciales en mayúsculas (máximo 2 caracteres)
     */
    function getInitials(string $text): string
    {
        if (empty($text)) {
            return 'U'; // Usuario por defecto
        }
        
        // Si es un email, usar la primera letra antes del @
        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            return strtoupper(substr($text, 0, 1));
        }
        
        // Limpiar y dividir el texto
        $text = trim($text);
        $words = explode(' ', $text);
        $initials = '';
        
        // Tomar la primera letra de las primeras dos palabras
        foreach ($words as $index => $word) {
            if ($index < 2 && !empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // Si solo hay una inicial, devolver esa
        // Si no hay ninguna, devolver 'U'
        return $initials ?: 'U';
    }
}

if (!function_exists('getUserAvatar')) {
    /**
     * Obtiene el avatar del usuario actual
     * Primero intenta cargar la foto de perfil real, si no existe genera un SVG
     * 
     * @param int    $size      Tamaño del avatar
     * @param string $class     Clases CSS adicionales
     * @return string           Tag HTML <img>
     */
    function getUserAvatar(int $size = 32, string $class = ''): string
    {
        $user = auth()->user();
        if (!$user) {
            return generateAvatarImg('Invitado', $size, $class);
        }
        
        // Intentar obtener staff y foto de perfil
        $staffModel = new \App\Models\StaffModel();
        $staff = $staffModel->where('user_id', $user->id)->first();
        
        if ($staff && !empty($staff->foto_perfil)) {
            // La foto_perfil ya contiene la ruta completa desde uploads/
            $fullPath = FCPATH . $staff->foto_perfil;
            
            log_message('debug', "Verificando avatar staff: foto_perfil={$staff->foto_perfil}, fullPath={$fullPath}");
            
            if (file_exists($fullPath)) {
                // Usar URL segura que ya funciona
                helper('secure_file');
                $fileName = basename($staff->foto_perfil);
                $avatarUrl = getSecureFileUrl('staff/' . $staff->rfc . '/' . $fileName, 'avatar');
                $fallbackAvatar = generateAvatar(userName(), $size);
                
                log_message('debug', "Avatar encontrado, usando URL segura: {$avatarUrl}");
                
                return sprintf(
                    '<img src="%s" alt="%s" class="%s" width="%d" height="%d" onerror="this.src=\'%s\'" style="border-radius: 50%%;">',
                    esc($avatarUrl),
                    esc(userName()),
                    esc($class),
                    $size,
                    $size,
                    $fallbackAvatar
                );
            } else {
                log_message('warning', "Archivo de avatar no encontrado: {$fullPath}");
            }
        } else {
            log_message('debug', "Staff sin foto_perfil: staff=" . json_encode($staff));
        }
        
        // Si no hay foto, generar avatar SVG
        return generateAvatarImg(userName(), $size, $class);
    }
}

if (!function_exists('generateAvatarImg')) {
    /**
     * Genera un tag <img> con avatar SVG
     * 
     * @param string $text  Texto para las iniciales
     * @param int    $size  Tamaño del avatar
     * @param string $class Clases CSS
     * @return string       Tag HTML <img>
     */
    function generateAvatarImg(string $text, int $size = 32, string $class = ''): string
    {
        $avatarSvg = generateAvatar($text, $size);
        
        return sprintf(
            '<img src="%s" alt="%s" class="%s" width="%d" height="%d" style="border-radius: 50%%;">',
            $avatarSvg,
            esc($text),
            esc($class),
            $size,
            $size
        );
    }
}

if (!function_exists('getUserAvatarUrl')) {
    /**
     * Obtiene solo la URL del avatar del usuario actual
     * 
     * @return string URL del avatar (segura o SVG)
     */
    function getUserAvatarUrl(): string
    {
        $user = auth()->user();
        if (!$user) {
            return generateAvatar('Invitado', 128);
        }
        
        // Intentar obtener staff y foto de perfil
        $staffModel = new \App\Models\StaffModel();
        $staff = $staffModel->where('user_id', $user->id)->first();
        
        if ($staff && !empty($staff->foto_perfil)) {
            $fullPath = FCPATH . $staff->foto_perfil;
            
            if (file_exists($fullPath)) {
                // Usar URL segura que ya funciona
                helper('secure_file');
                $fileName = basename($staff->foto_perfil);
                return getSecureFileUrl('staff/' . $staff->rfc . '/' . $fileName, 'avatar');
            }
        }
        
        // Si no hay foto, generar avatar SVG
        return generateAvatar(userName(), 128);
    }
}

if (!function_exists('getAvatarColors')) {
    /**
     * Obtiene colores para avatar basados en el rol del usuario
     * 
     * @return array [bgColor, textColor]
     */
    function getAvatarColors(): array
    {
        if (!auth()->loggedIn()) {
            return ['#6c757d', '#ffffff']; // Gris para invitados
        }
        
        $user = auth()->user();
        
        // Colores por rol
        if ($user->inGroup('superadmin')) {
            return ['#dc3545', '#ffffff']; // Rojo
        } elseif ($user->inGroup('admin')) {
            return ['#007bff', '#ffffff']; // Azul
        } elseif ($user->inGroup('cliente')) {
            return ['#28a745', '#ffffff']; // Verde
        } elseif ($user->inGroup('vendedor', 'supervendedor')) {
            return ['#ffc107', '#000000']; // Amarillo
        } else {
            return ['#6c757d', '#ffffff']; // Gris por defecto
        }
    }
}