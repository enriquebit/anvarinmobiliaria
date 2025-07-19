# 🚀 MIGRACIÓN DEL SISTEMA ANVAR A HOSTING CPANEL

## 📋 INFORMACIÓN DEL SERVIDOR DE DESTINO

- **Dominio**: https://sistema.anvarinmobiliaria.com
- **Base de datos**: 
  - Usuario: `anvarinm_devcrm`
  - Contraseña: `QyhNwv^JItLr`
  - Nombre BD: `anvarinm_devcrm`
- **Método de transferencia**: FTP via FileZilla
- **Configuración**: Servidor ya configurado con .htaccess para CodeIgniter

## ⚙️ PRERREQUISITOS DEL SERVIDOR

### Verificar que el hosting tenga:
- **PHP 8.1 o superior**
- **MySQL 5.7 o superior**
- **Extensiones PHP requeridas**:
  - intl
  - mbstring
  - json
  - mysqlnd
  - xml
  - curl
  - zip
  - gd (para manejo de imágenes)

### Configuraciones mínimas recomendadas:
```
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

## 📁 PASO 1: PREPARAR ARCHIVOS LOCALES

### 1.1 Crear archivo comprimido del proyecto
```
- Crear ZIP/RAR de toda la carpeta /nuevoanvar
- Excluir carpetas innecesarias:
  * /vendor (se reinstalará)
  * /writable/cache/*
  * /writable/logs/*
  * /writable/session/*
  * /.git (si existe)
```

### 1.2 Archivos críticos a verificar antes de subir:
- ✅ `.env` (con configuración de producción)
- ✅ `app/Config/Database.php`
- ✅ `app/Config/App.php`
- ✅ `composer.json` y `composer.lock`
- ✅ Toda la carpeta `/app`
- ✅ Toda la carpeta `/public`
- ✅ Carpeta `/writable` (vacía las subcarpetas cache, logs, session)

## 🌐 PASO 2: CONFIGURAR ARCHIVOS PARA PRODUCCIÓN

### 2.1 Actualizar archivo .env
Crear/editar `.env` con configuración de producción:

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://sistema.anvarinmobiliaria.com/'
app.indexPage = ''
app.uriProtocol = 'REQUEST_URI'
app.defaultLocale = 'es'
app.negotiateLocale = false
app.supportedLocales = ['es']
app.appTimezone = 'America/Mexico_City'
app.charset = 'UTF-8'
app.forceGlobalSecureRequests = true

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = anvarinm_devcrm
database.default.username = anvarinm_devcrm
database.default.password = QyhNwv^JItLr
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port = 3306

#--------------------------------------------------------------------
# SECURITY
#--------------------------------------------------------------------
# GENERAR NUEVAS CLAVES DE SEGURIDAD
encryption.key = [GENERAR_NUEVA_CLAVE_32_CARACTERES]
security.csrfTokenName = 'csrf_token_name'
security.csrfHeaderName = 'X-CSRF-TOKEN'
security.csrfCookieName = 'csrf_cookie_name'
security.csrfExpire = 7200
security.csrfRegenerate = true

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------
session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.cookieName = 'anvar_session'
session.expiration = 7200
session.savePath = null
session.matchIP = false
session.timeToUpdate = 300
session.regenerateDestroy = false

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------
logger.threshold = 3

#--------------------------------------------------------------------
# GOOGLE DRIVE (Si se usa OAuth2)
#--------------------------------------------------------------------
GOOGLE_DRIVE_ACCESS_TOKEN = 
GOOGLE_DRIVE_REFRESH_TOKEN = 

#--------------------------------------------------------------------
# HUBSPOT
#--------------------------------------------------------------------
HUBSPOT_ACCESS_TOKEN = [TU_TOKEN_HUBSPOT]
```

### 2.2 Verificar app/Config/Database.php
```php
public array $default = [
    'DSN'          => '',
    'hostname'     => 'localhost',
    'username'     => 'anvarinm_devcrm',
    'password'     => 'QyhNwv^JItLr',
    'database'     => 'anvarinm_devcrm',
    'DBDriver'     => 'MySQLi',
    'DBPrefix'     => '',
    'pConnect'     => false,
    'DBDebug'      => false, // IMPORTANTE: false en producción
    'charset'      => 'utf8mb4',
    'DBCollat'     => 'utf8mb4_general_ci',
    'swapPre'      => '',
    'encrypt'      => false,
    'compress'     => false,
    'strictOn'     => false,
    'failover'     => [],
    'port'         => 3306,
    'numberNative' => false,
    'dateFormat'   => [
        'date'     => 'Y-m-d',
        'datetime' => 'Y-m-d H:i:s',
        'time'     => 'H:i:s',
    ],
];
```

### 2.3 Actualizar app/Config/App.php
```php
public string $baseURL = 'https://sistema.anvarinmobiliaria.com/';
public string $indexPage = '';
public bool $forceGlobalSecureRequests = true;
```

## 📊 PASO 3: MIGRAR BASE DE DATOS

### 3.1 Exportar base de datos local
1. Abrir phpMyAdmin local
2. Seleccionar base de datos `nuevoanvar_vacio`
3. Ir a "Exportar"
4. Seleccionar formato: **SQL**
5. Opciones avanzadas:
   - ✅ Estructura
   - ✅ Datos
   - ✅ CREATE DATABASE / USE statement (desmarcar)
   - ✅ Agregar DROP TABLE/VIEW/PROCEDURE/FUNCTION/EVENT
6. Descargar archivo `.sql`

### 3.2 Importar en servidor de producción
1. Acceder a cPanel del hosting
2. Ir a **phpMyAdmin**
3. Seleccionar base de datos `anvarinm_devcrm`
4. Ir a pestaña **"Importar"**
5. Seleccionar archivo `.sql` exportado
6. **Verificar configuración**:
   - Formato: SQL
   - Codificación: utf8
7. Ejecutar importación

### 3.3 Verificar datos importados
```sql
-- Verificar tablas principales
SHOW TABLES;

-- Verificar algunos registros
SELECT COUNT(*) FROM registro_clientes;
SELECT COUNT(*) FROM users;

-- Verificar estructura crítica
DESCRIBE registro_clientes;
DESCRIBE users;
```

## 📤 PASO 4: SUBIR ARCHIVOS VIA FTP

### 4.1 Conectar FileZilla
- **Host**: ftp.anvarinmobiliaria.com (o IP del servidor)
- **Usuario**: [tu_usuario_ftp]
- **Contraseña**: [tu_password_ftp]
- **Puerto**: 21 (o 22 para SFTP)

### 4.2 Estructura de archivos a subir
```
/public_html/ (o directorio raíz)
├── app/
├── public/
├── vendor/
├── writable/
├── .env
├── .htaccess
├── composer.json
├── composer.lock
├── spark
├── LICENSE
└── README.md
```

### 4.3 Orden de subida recomendado:
1. **Primero**: Carpeta `/app` completa
2. **Segundo**: Carpeta `/public` completa  
3. **Tercero**: Archivos raíz (`.env`, `.htaccess`, `composer.json`, etc.)
4. **Cuarto**: Carpeta `/writable` (crear estructura vacía)
5. **Último**: Carpeta `/vendor` (o reinstalar con Composer)

### 4.4 Permisos de carpetas (si es necesario)
```
writable/ = 755
writable/cache/ = 755
writable/logs/ = 755
writable/session/ = 755
writable/uploads/ = 755
```

## 🔧 PASO 5: CONFIGURACIÓN POST-MIGRACIÓN

### 5.1 Reinstalar dependencias (si el hosting lo permite)
Si el hosting tiene terminal o Composer:
```bash
composer install --no-dev --optimize-autoloader
```

Si no tiene Composer, subir carpeta `/vendor` completa vía FTP.

### 5.2 Verificar permisos de archivos
- **Archivos .php**: 644
- **Carpetas**: 755
- **writable/**: 755 y todas sus subcarpetas

### 5.3 Limpiar caché
Eliminar contenido de:
- `writable/cache/`
- `writable/logs/`
- `writable/session/`

### 5.4 Verificar .htaccess en raíz
El servidor ya está configurado, pero verificar que existe:
```apache
# Redirigir todo a public/
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/$1 [L]
```

## 🧪 PASO 6: PRUEBAS DE FUNCIONAMIENTO

### 6.1 Verificaciones básicas
1. **Acceso al sitio**: https://sistema.anvarinmobiliaria.com
2. **Página de login**: https://sistema.anvarinmobiliaria.com/auth/login
3. **Panel admin**: Hacer login y verificar dashboard
4. **Registro público**: https://sistema.anvarinmobiliaria.com/registro-clientes

### 6.2 Pruebas funcionales
- ✅ Login con usuario existente
- ✅ Acceso a módulos administrativos
- ✅ Registro de nuevo cliente desde formulario público
- ✅ Subida de documentos
- ✅ Integración HubSpot (si está configurado)
- ✅ Integración Google Drive (si está configurado)

### 6.3 Verificar logs de errores
- Revisar `writable/logs/` para errores
- Verificar logs de error de cPanel/hosting

## 🔒 PASO 7: CONFIGURACIONES DE SEGURIDAD

### 7.1 SSL/HTTPS
- Verificar que el certificado SSL esté activo
- Confirmar redirección HTTP → HTTPS

### 7.2 Configuraciones adicionales de seguridad
```php
// En .env
CI_ENVIRONMENT = production
logger.threshold = 3  // Solo errores críticos
```

### 7.3 Ocultar información sensible
- Verificar que `.env` no sea accesible públicamente
- Confirmar que `/vendor` y `/app` no sean accesibles

## 🚀 PASO 8: CONFIGURACIONES ESPECÍFICAS

### 8.1 Google Drive OAuth2
Si se usa OAuth2:
1. Ir a: https://sistema.anvarinmobiliaria.com/admin/google-drive/status
2. Completar autorización OAuth2
3. Verificar conexión

### 8.2 HubSpot
- Verificar token de acceso en configuración
- Probar integración con registro de prueba

### 8.3 Emails (si se usan)
- Configurar SMTP en CodeIgniter
- Probar envío de emails

## 📋 CHECKLIST FINAL

### Antes de declarar migración exitosa:
- [ ] Base de datos migrada y funcionando
- [ ] Todos los archivos subidos correctamente
- [ ] Login administrativo funciona
- [ ] Formulario público de registro funciona
- [ ] Subida de documentos funciona
- [ ] Integraciones externas funcionan (HubSpot, Google Drive)
- [ ] No hay errores en logs
- [ ] SSL/HTTPS funcionando
- [ ] Permisos de archivos correctos

## 🆘 SOLUCIÓN DE PROBLEMAS COMUNES

### Error 500 - Internal Server Error
1. Verificar permisos de `/writable`
2. Revisar `.env` y configuración de base de datos
3. Verificar logs de error del hosting
4. Confirmar versión de PHP compatible

### Error de base de datos
1. Verificar credenciales en `.env`
2. Confirmar que la BD fue importada correctamente
3. Verificar conexión desde phpMyAdmin

### Archivos no encontrados
1. Verificar estructura de carpetas
2. Confirmar que `.htaccess` está configurado
3. Revisar permisos de archivos

### Problema con composer/vendor
1. Subir carpeta `/vendor` completa vía FTP
2. Verificar que todas las dependencias están presentes

---

## 📞 SOPORTE POST-MIGRACIÓN

Una vez completada la migración, realizar pruebas exhaustivas y documentar cualquier configuración adicional específica del hosting utilizado.

**¡Migración completada! El sistema ANVAR estará funcionando en producción.**