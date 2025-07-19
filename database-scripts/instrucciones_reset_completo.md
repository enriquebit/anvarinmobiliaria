# 🚀 INSTRUCCIONES PARA RESET COMPLETO DE BASE DE DATOS

## ⚠️ IMPORTANTE: LEE COMPLETAMENTE ANTES DE EJECUTAR

Este proceso eliminará completamente tu base de datos actual y la recreará desde cero con la estructura sincronizada.

## 📋 PASOS A SEGUIR (EN ORDEN ESTRICTO)

### PASO 1: Crear Backup de Seguridad (OBLIGATORIO)
```bash
# Opción A: Usar el script automático
./database-scripts/backup_before_reset.bat

# Opción B: Manual
mysqldump -u root -p nuevoanvar_vacio > backup_antes_reset_completo.sql
```

### PASO 2: Ejecutar Reset de Base de Datos
1. Abre **phpMyAdmin** o **MySQL Workbench**
2. Ejecuta el archivo: `./database-scripts/reset_database_complete.sql`
3. Confirma que aparezca el mensaje: "Base de datos reseteada exitosamente"

### PASO 3: Ejecutar Migración Unificada
```bash
# En terminal desde la raíz del proyecto
php spark migrate
```

### PASO 4: Verificar Usuario Superadmin
```bash
# Prueba de login en el navegador
# URL: http://localhost/login
# Email: superadmin@nuevoanvar.test
# Password: secret1234
```

## ✅ VERIFICACIONES POST-RESET

### Verificar Estructura de Tablas
```sql
-- Ejecutar en phpMyAdmin para verificar
SHOW TABLES;

-- Debe mostrar todas las tablas incluyendo:
-- amenidades, categorias_lotes, tipos_lotes, estados_lotes
-- manzanas, divisiones, lotes, lotes_amenidades
-- auth_*, users, clientes, empresas, proyectos, etc.
```

### Verificar Datos de Catálogos
```sql
-- Verificar que se insertaron datos por defecto
SELECT * FROM amenidades;
SELECT * FROM categorias_lotes;
SELECT * FROM tipos_lotes;
SELECT * FROM estados_lotes;
```

### Verificar Usuario Superadmin
```sql
-- Verificar usuario creado
SELECT u.id, u.active, ai.secret as email, agu.group
FROM users u
JOIN auth_identities ai ON ai.user_id = u.id  
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE ai.secret = 'superadmin@nuevoanvar.test';
```

### Verificar Campo etapa_proceso
```sql
-- Verificar que se agregó el campo
DESCRIBE registro_clientes;
-- Debe mostrar el campo 'etapa_proceso' tipo ENUM
```

## 🔧 SOLUCIÓN DE PROBLEMAS

### Error: "Table doesn't exist"
- Verifica que ejecutaste el reset_database_complete.sql correctamente
- Asegúrate que la base de datos se recreó completamente

### Error en Migración
- Ejecuta: `php spark migrate:rollback`
- Luego: `php spark migrate`

### Usuario no puede hacer login
- Verifica que el hash de password se generó correctamente
- Password debe ser exactamente: `secret1234`

### Faltan Datos en Catálogos
- Las tablas de catálogos deben tener datos automáticamente
- Si están vacías, hay un problema en la migración

## 📊 ESTADO FINAL ESPERADO

### Tabla migrations
```
id | version           | class
---|-------------------|---------------------------------------
1  | 2025-07-06-120000 | CreateCompleteDatabaseStructure
```

### Usuario Superadmin
- ✅ Email: `superadmin@nuevoanvar.test`
- ✅ Password: `secret1234`
- ✅ Grupo: `superadmin`
- ✅ Activo: `1`

### Catálogos Poblados
- ✅ 10 amenidades
- ✅ 7 categorías de lotes  
- ✅ 5 tipos de lotes
- ✅ 4 estados de lotes

## 🆘 EN CASO DE EMERGENCIA

Si algo sale mal, restaura tu backup:
```bash
mysql -u root -p nuevoanvar_vacio < backup_antes_reset_completo.sql
```