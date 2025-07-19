#!/bin/bash
echo "🔥 CONFIGURACIÓN DE DESARROLLO - PERMISOS RELAJADOS"

# Limpiar archivos problemáticos
echo "🧹 Limpiando archivos de sesión y debug..."
rm -rf writable/debugbar/debugbar_*.json 2>/dev/null
rm -rf writable/session/ci_session* 2>/dev/null

# Crear directorios si no existen
echo "📁 Creando directorios necesarios..."
mkdir -p writable/{session,cache,logs,debugbar,uploads}

# Permisos súper permisivos para desarrollo
echo "🔓 Configurando permisos permisivos..."
chmod -R 777 writable/
chmod -R 755 public/

# Crear archivos index de seguridad
echo "🛡️ Creando archivos index de seguridad..."
echo '<html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden.</h1></body></html>' > writable/session/index.html
echo '<html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden.</h1></body></html>' > writable/debugbar/index.html
echo '<html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden.</h1></body></html>' > writable/cache/index.html
echo '<html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden.</h1></body></html>' > writable/logs/index.html

# Verificar permisos
echo "📋 Verificando permisos..."
ls -la writable/

echo "✅ Configuración completada - Listo para desarrollo sin restricciones"
echo "🌐 Puedes acceder a: http://localhost"