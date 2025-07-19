# 🔧 Configuración Google Drive OAuth2

## 📋 Pasos para Configurar OAuth2

### 1. **Configurar Google Cloud Console**
1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto o crea uno nuevo
3. Habilita la **Google Drive API**:
   - Ir a "APIs & Services" > "Library"
   - Buscar "Google Drive API"
   - Hacer clic en "Enable"

### 2. **Crear Credenciales OAuth2**
1. Ve a "APIs & Services" > "Credentials"
2. Clic en "Create Credentials" > "OAuth 2.0 Client IDs"
3. Configurar:
   - **Application type**: Web application
   - **Name**: ANVAR Inmobiliaria - Google Drive
   - **Authorized redirect URIs**: 
     - Desarrollo: `http://nuevoanvar.test/admin/google-drive/callback`
     - Producción: `https://tudominio.com/admin/google-drive/callback`

### 3. **Actualizar Credenciales en el Código**
Editar `/app/Libraries/GoogleDriveService.php`:
```php
// Líneas 19-20, reemplazar con tus credenciales reales:
$this->clientId = 'TU_CLIENT_ID_AQUI.apps.googleusercontent.com';
$this->clientSecret = 'TU_CLIENT_SECRET_AQUI';
```

### 4. **Autorizar la Aplicación**
1. Como administrador, ir a: `http://nuevoanvar.test/admin/google-drive/status`
2. Hacer clic en "Autorizar Google Drive"
3. Completar el flujo OAuth2 en Google
4. Verificar que aparezca "Autorización Activa"

## 🔗 URLs del Sistema

- **Estado OAuth2**: `/admin/google-drive/status`
- **Iniciar autorización**: `/admin/google-drive/authorize`
- **Callback**: `/admin/google-drive/callback`
- **Probar conexión**: `/admin/google-drive/test`
- **Revocar tokens**: `/admin/google-drive/revoke`

## 📂 Archivos Modificados

### ✅ **Nuevos Archivos**
- `app/Controllers/Admin/GoogleDriveAuthController.php`
- `app/Views/admin/google-drive/status.php`

### ✅ **Archivos Actualizados**
- `app/Libraries/GoogleDriveService.php` - Agregado soporte OAuth2
- `app/Config/Routes.php` - Agregadas rutas OAuth2

## 🔄 Flujo de Autenticación

1. **Administrador** va a `/admin/google-drive/status`
2. **Sistema** muestra si hay tokens válidos
3. **Si no hay tokens** → Botón "Autorizar Google Drive"
4. **Redirección** a Google OAuth2
5. **Usuario autoriza** la aplicación
6. **Google redirige** a `/admin/google-drive/callback` con código
7. **Sistema intercambia** código por tokens
8. **Tokens se guardan** en `writable/google_drive_tokens.json`
9. **Renovación automática** cuando expiran

## 🗂️ Almacenamiento de Tokens

Los tokens se guardan en:
```
writable/google_drive_tokens.json
```

Estructura:
```json
{
    "access_token": "ya29.a0AfH6...",
    "refresh_token": "1//04...",
    "expires_at": 1625097600,
    "created_at": 1625094000
}
```

## ⚡ Características

- ✅ **Renovación automática** de tokens
- ✅ **Detección de expiración** (5 min antes)
- ✅ **Almacenamiento persistente** en archivo JSON
- ✅ **Logs detallados** para debugging
- ✅ **Interfaz administrativa** para gestión
- ✅ **Pruebas de conexión** integradas

## 🚨 Problemas Comunes

### Error 401 - Invalid Credentials
- Verificar que `client_id` y `client_secret` sean correctos
- Confirmar que la redirect URI esté configurada en Google Console
- Revisar que los tokens no hayan expirado

### Error de Redirect URI Mismatch
- La URL en Google Console debe coincidir exactamente
- Incluir tanto HTTP (desarrollo) como HTTPS (producción)

### Tokens No Se Renuevan
- Verificar que `refresh_token` esté presente
- El refresh token solo se obtiene en la primera autorización
- Si se pierde, hay que reautorizar completamente

## 📱 Para Producción

1. **Actualizar redirect URI** en Google Console
2. **Usar HTTPS** obligatorio
3. **Proteger archivo de tokens** con permisos adecuados
4. **Configurar variables de entorno** para credenciales
5. **Monitorear logs** para detectar problemas

## 🔍 Debug y Monitoreo

Ver logs en tiempo real:
```bash
tail -f writable/logs/log-*.php | grep GOOGLE_DRIVE
```

Verificar estado actual:
```
GET /admin/google-drive/test
```