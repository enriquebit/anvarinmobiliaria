#!/bin/bash

# Script para configurar permisos de uploads
# Uso: ./set_permissions.sh

echo "🔧 Configurando permisos de directorios de uploads..."

# Directorio base
BASE_DIR="/home/enriquebit/Documentos/nuevoanvar/public/uploads"

# Configurar permisos 777 recursivamente
chmod -R 777 "$BASE_DIR"

echo "✅ Permisos configurados:"
echo "   - Todos los directorios: 777 (drwxrwxrwx)"
echo "   - Todos los archivos: 777 (-rwxrwxrwx)"

echo ""
echo "📁 Estructura actual:"
ls -la "$BASE_DIR"

echo ""
echo "🎯 Configuración completada. Todos los usuarios pueden leer/escribir."