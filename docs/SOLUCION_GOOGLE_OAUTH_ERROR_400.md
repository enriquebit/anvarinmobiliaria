# 🚨 Solución Error 400: invalid_request - Google OAuth2

## 🔍 Problema
```
You can't sign in to this app because it doesn't comply with Google's OAuth 2.0 policy for keeping apps secure.
Error 400: invalid_request
```

## ✅ Soluciones Paso a Paso

### 1. **Verificar Configuración en Google Cloud Console**

#### A) **OAuth Consent Screen (Pantalla de Consentimiento)**
1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. APIs & Services > OAuth consent screen
3. Configurar:
   ```
   User Type: External (para desarrollo)
   Application name: ANVAR Inmobiliaria
   User support email: tu-email@gmail.com
   Developer contact information: tu-email@gmail.com
   ```
4. **Agregar Scopes requeridos**:
   ```
   https://www.googleapis.com/auth/drive.file
   ```
5. **Agregar Test Users** (obligatorio para apps no verificadas):
   - Agregar tu email de Google
   - Agregar emails de todos los que van a probar

#### B) **Credentials (Credenciales)**
1. APIs & Services > Credentials
2. Verificar OAuth 2.0 Client ID:
   ```
   Application type: Web application
   Name: ANVAR Inmobiliaria Web Client
   
   Authorized JavaScript origins:
   - http://localhost
   - http://nuevoanvar.test
   - https://tu-dominio-produccion.com
   
   Authorized redirect URIs:
   - http://nuevoanvar.test/admin/google-drive/callback
   - https://tu-dominio-produccion.com/admin/google-drive/callback
   ```

### 2. **Habilitar APIs Necesarias**
1. APIs & Services > Library
2. Buscar y habilitar:
   - **Google Drive API**
   - **Google+ API** (si es necesario)

### 3. **Actualizar Parámetros OAuth2**

Editar `/app/Libraries/GoogleDriveService.php`:

```php
public function getAuthorizationUrl(string $redirectUri): string
{
    $params = [
        'client_id' => $this->clientId,
        'redirect_uri' => $redirectUri,
        'scope' => 'https://www.googleapis.com/auth/drive.file',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent', // Cambiado de approval_prompt
        'include_granted_scopes' => 'true'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}
```

### 4. **Solución para Desarrollo: Usar Testing Mode**

#### A) **Configurar como App en Testing**
1. OAuth consent screen > Publishing status
2. Mantener en **"Testing"** durante desarrollo
3. Agregar usuarios de prueba en "Test users"

#### B) **Para Testing Interno (Recomendado)**
```php
// Actualizar scopes si es necesario
'scope' => implode(' ', [
    'https://www.googleapis.com/auth/drive.file',
    'openid',
    'email',
    'profile'
])
```

### 5. **Verificar URLs Exactas**

**❌ URLs que causan error:**
- `http://localhost:8080/callback`
- `http://127.0.0.1/callback`
- URLs con puertos no estándar

**✅ URLs correctas:**
- `http://nuevoanvar.test/admin/google-drive/callback`
- `https://tu-dominio.com/admin/google-drive/callback`

### 6. **Solución Alternativa: Service Account (Recomendado para Producción)**

Si el OAuth2 sigue dando problemas, usar Service Account:

#### A) **Crear Service Account**
1. Google Cloud Console > IAM & Admin > Service Accounts
2. Create Service Account:
   ```
   Name: anvar-drive-service
   Description: Service account for ANVAR Drive integration
   ```
3. Descargar JSON key file

#### B) **Actualizar GoogleDriveService para Service Account**
```php
// Nuevo constructor para Service Account
public function __construct()
{
    $this->useServiceAccount = true;
    $this->serviceAccountKeyFile = APPPATH . 'Config/google-service-account.json';
}

private function setAccessTokenFromServiceAccount()
{
    $keyFile = json_decode(file_get_contents($this->serviceAccountKeyFile), true);
    
    // Crear JWT token
    $jwt = $this->createJWT($keyFile);
    
    // Intercambiar por access token
    $this->accessToken = $this->getServiceAccountToken($jwt);
}
```

### 7. **Configuración de .env (Opcional)**

Agregar a `.env`:
```env
GOOGLE_OAUTH_CLIENT_ID="tu-client-id.apps.googleusercontent.com"
GOOGLE_OAUTH_CLIENT_SECRET="tu-client-secret"
GOOGLE_OAUTH_REDIRECT_URI="http://nuevoanvar.test/admin/google-drive/callback"
```

## 🔧 Script de Verificación

Crear endpoint de diagnóstico:

```php
// En GoogleDriveAuthController.php
public function diagnose()
{
    $data = [
        'client_id' => $this->googleDriveService->getClientId(),
        'redirect_uri' => base_url('admin/google-drive/callback'),
        'current_url' => current_url(),
        'base_url' => base_url(),
        'environment' => ENVIRONMENT
    ];
    
    return $this->response->setJSON($data);
}
```

Acceder a: `/admin/google-drive/diagnose`

## 🚀 Solución Rápida para Desarrollo

### Opción 1: OAuth2 con Testing Mode
1. ✅ Configurar OAuth consent screen en "Testing"
2. ✅ Agregar tu email como test user
3. ✅ Verificar redirect URIs exactas
4. ✅ Usar `prompt=consent` en lugar de `approval_prompt`

### Opción 2: Service Account (Más Confiable)
1. ✅ Crear service account
2. ✅ Descargar JSON key
3. ✅ Compartir carpeta Drive con service account email
4. ✅ No requiere OAuth2 interactivo

## ⚡ Implementación Service Account (Recomendada)

Si quieres que implemente la solución con Service Account que es más confiable para producción, solo dime y actualizo el código.

## 📞 Contacto Google Support

Si ninguna solución funciona:
1. Google Cloud Console > Support
2. Reportar problema con OAuth2 policy compliance
3. Proporcionar detalles de la aplicación y uso previsto