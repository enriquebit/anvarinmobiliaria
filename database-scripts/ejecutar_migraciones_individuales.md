# Comandos para Ejecutar Migraciones Individuales

## PASO 1: Limpiar tabla migrations primero
```sql
-- Ejecuta este SQL primero en phpMyAdmin
DELETE FROM migrations WHERE id IN (22, 23, 24, 25, 26, 27);
ALTER TABLE migrations AUTO_INCREMENT = 22;
```

## PASO 2: Ejecutar cada migración por separado

### Migración 22: Categorías de Lotes
```bash
php spark migrate --to 2025-06-30-181000
```

### Migración 23: Tipos de Lotes  
```bash
php spark migrate --to 2025-06-30-181100
```

### Migración 24: Estados de Lotes
```bash
php spark migrate --to 2025-06-30-181200
```

### Migración 25: Amenidades
```bash
php spark migrate --to 2025-06-30-181300
```

### Migración 26: Lotes
```bash
php spark migrate --to 2025-06-30-181400
```

### Migración 27: Lotes-Amenidades
```bash
php spark migrate --to 2025-06-30-181500
```

## PASO 3: Verificar estado final
```bash
php spark migrate:status
```

## ALTERNATIVA: Ejecutar todas de una vez
```bash
php spark migrate
```

## ROLLBACK (Si necesitas deshacer)
```bash
# Deshacer la última migración
php spark migrate:rollback

# Deshacer hasta una migración específica
php spark migrate:rollback --to 2025-06-30-180000
```