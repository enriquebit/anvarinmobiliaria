<?php
/**
 * Script para corregir todas las referencias a Spanish.json en las vistas
 * Cambia las rutas locales por CDN de DataTables
 */

$viewsPath = __DIR__ . '/app/Views/admin';
$files = [];

// Función recursiva para encontrar archivos PHP
function findPhpFiles($dir, &$files) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            findPhpFiles($path, $files);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $files[] = $path;
        }
    }
}

// Encontrar todos los archivos PHP
findPhpFiles($viewsPath, $files);

$updatedFiles = 0;
$patterns = [
    // Patrón para rutas con base_url y comillas dobles
    '/"url":\s*"<\?=\s*base_url\([\'"]assets\/plugins\/datatables\/Spanish\.json[\'"]\)\s*\?>"/i' => '"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"',
    '/"url":\s*"<\?=\s*base_url\([\'"]assets\/plugins\/datatables\/i18n\/Spanish\.json[\'"]\)\s*\?>"/i' => '"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"',
    
    // Patrón para rutas con base_url y comillas simples
    '/url:\s*\'<\?=\s*base_url\([\'"]assets\/plugins\/datatables\/Spanish\.json[\'"]\)\s*\?>\'/i' => 'url: \'//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json\'',
    '/url:\s*\'<\?=\s*base_url\([\'"]assets\/plugins\/datatables\/i18n\/Spanish\.json[\'"]\)\s*\?>\'/i' => 'url: \'//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json\'',
    
    // Patrón para rutas directas con comillas dobles
    '/"url":\s*"assets\/plugins\/datatables\/Spanish\.json"/i' => '"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"',
    '/"url":\s*"\/assets\/plugins\/datatables\/Spanish\.json"/i' => '"url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"',
    
    // Patrón para rutas directas con comillas simples
    '/url:\s*\'\/assets\/plugins\/datatables\/Spanish\.json\'/i' => 'url: \'//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json\'',
    '/url:\s*\'assets\/plugins\/datatables\/Spanish\.json\'/i' => 'url: \'//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json\'',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $updatedFiles++;
        echo "✅ Actualizado: " . str_replace(__DIR__, '.', $file) . "\n";
    }
}

echo "\n📊 Resumen:\n";
echo "Total de archivos procesados: " . count($files) . "\n";
echo "Archivos actualizados: " . $updatedFiles . "\n";
echo "\n✅ ¡Todas las referencias a Spanish.json han sido actualizadas al CDN!\n";