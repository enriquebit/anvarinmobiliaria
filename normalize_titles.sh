#!/bin/bash

echo "=== NORMALIZANDO VARIABLES DE TÍTULO ==="

# 1. Cambiar todas las variables $title por $titulo en las vistas
echo "1. Cambiando \$title por \$titulo en vistas..."
find app/Views -name "*.php" -exec sed -i 's/\$title/\$titulo/g' {} \;

# 2. Cambiar todas las claves 'title' por 'titulo' en controladores  
echo "2. Cambiando 'title' por 'titulo' en controladores..."
find app/Controllers -name "*.php" -exec sed -i "s/'title'/'titulo'/g" {} \;

# 3. Cambiar breadcrumb titles por names para consistencia
echo "3. Cambiando breadcrumb 'title' por 'name'..."
find app/Views -name "*.php" -exec sed -i "s/\['title'\]/\['name'\]/g" {} \;
find app/Controllers -name "*.php" -exec sed -i "s/\['title'\]/\['name'\]/g" {} \;

echo "=== NORMALIZACIÓN COMPLETADA ==="
echo "Todas las variables ahora usan 'titulo' de forma consistente"