# 🏗️ ARQUITECTURA DE USUARIOS Y GESTIÓN DE DOCUMENTOS

## 📋 RESUMEN EJECUTIVO

Este documento describe la arquitectura actual del sistema de usuarios de NuevoAnvar, basado en CodeIgniter 4 + Shield v1.1 con metodología Entity-First.

## 🗃️ ESTRUCTURA DE BASE DE DATOS

### Tablas Core de Shield
```sql
users                   -- Usuario base (Shield)
auth_identities        -- Credenciales email/password
auth_groups_users      -- Relación usuario-rol
auth_logins            -- Log de accesos
auth_remember_tokens   -- Tokens de "recordarme"
```

### Tablas de Negocio
```sql
clientes               -- Información extendida de clientes
staff                  -- Información extendida de personal
documentos_clientes    -- Referencias a documentos de clientes
-- documentos_staff    -- [PENDIENTE] Referencias a documentos de staff
```

### Relaciones Principales
```
users (1) ←→ (1) clientes [user_id]
users (1) ←→ (1) staff [user_id]
clientes (1) ←→ (N) documentos_clientes [cliente_id]
```

## 🔄 FLUJOS DE CREACIÓN DE USUARIOS

### 1. Registro Público de Clientes (`/register`)
```
Cliente llena formulario
    ↓
RegisterController::attemptRegister()
    ↓
UserModel::createClienteUser()
    ├─ Crea registro en 'users' (INACTIVO)
    ├─ Crea 'auth_identity' (email/password)
    ├─ Asigna grupo 'cliente'
    └─ Crea registro en 'clientes'
    ↓
Cliente queda PENDIENTE de aprobación administrativa
```

### 2. Creación Administrativa de Clientes (`/admin/clientes/create`)
```
Admin llena formulario extendido
    ↓
AdminClientesController::store()
    ├─ Valida permisos (solo admin/superadmin)
    ├─ Crea usuario + cliente
    ├─ Crea información relacionada:
    │   ├─ Dirección (direcciones_clientes)
    │   ├─ Info laboral (informacion_laboral)
    │   ├─ Info cónyuge (informacion_conyuge)
    │   └─ Referencias (referencias_clientes)
    └─ Puede activar inmediatamente
```

### 3. Creación de Staff (`/admin/usuarios/create`)
```
Admin/Superadmin crea personal
    ↓
AdminUsuariosController::store()
    ├─ Crea registro en 'users'
    ├─ Crea 'auth_identity'
    ├─ Asigna grupo (admin, vendedor, etc.)
    └─ Crea registro en 'staff'
```

## 📁 SISTEMA DE ARCHIVOS

### Estructura de Carpetas
```
/public/uploads/
├── clientes/
│   └── {RFC}/
│       ├── identificacion_oficial_{RFC}.jpg
│       ├── comprobante_domicilio_{RFC}.pdf
│       ├── rfc_{RFC}.pdf
│       ├── curp_{RFC}.jpg
│       ├── carta_domicilio_{RFC}.pdf
│       ├── hoja_requerimientos_{RFC}.pdf
│       └── ofac_{RFC}.pdf
└── staff/              # [PENDIENTE]
    └── {RFC}/
        ├── identificacion_oficial_{RFC}.jpg
        └── comprobante_domicilio_{RFC}.pdf
```

### Tipos de Documentos Soportados
```
- identificacion_oficial: INE, Pasaporte
- comprobante_domicilio: Recibo de servicios
- rfc: Constancia de Situación Fiscal
- curp: Constancia CURP
- carta_domicilio: Carta de domicilio
- hoja_requerimientos: Hoja de requerimientos
- ofac: Documentos OFAC
```

### Nomenclatura de Archivos
```
Patrón: {RFC}_{tipo_documento}.{extensión}
Ejemplo: RFC123456789_identificacion_oficial.jpg
```

## 🏗️ ARQUITECTURA ENTITY-FIRST

### Cliente Entity (`app/Entities/Cliente.php`)
```php
// Propiedades virtuales
getNombreCompleto()     // Formatea nombres + apellidos
getTelefonoFormateado() // Formato (xxx) xxx-xxxx
getRfcFormateado()      // Formato RFC-000000-XX0

// Lazy loading de relaciones  
getDireccion()          // Carga dirección bajo demanda
getInformacionLaboral() // Carga info laboral
getInformacionConyuge() // Carga info cónyuge
getReferencias()        // Carga referencias

// Métodos de negocio
isActivo()              // Verifica si está activo
hasInfoCompleta()       // Verifica completitud
puedeAvanzarEtapa()     // Lógica de etapas
```

### Staff Entity (`app/Entities/Staff.php`)
```php
// Propiedades virtuales para vistas
getNombreFormateado()   // Nombre para UI
getTipoBadge()          // Badge según tipo de staff
getPermisosBadge()      // Permisos como badges

// Verificación de permisos
isSuperAdmin()          // Es superadmin?
puedeSerEditado()       // Puede ser modificado?
```

### User Entity (extiende Shield)
```php
// Delegación según tipo de usuario
getClienteInfo()        // Si es cliente, devuelve entity Cliente
getStaffInfo()          // Si es staff, devuelve entity Staff
getUserType()           // Detecta automáticamente el tipo
```

## 🔐 SISTEMA DE AUTENTICACIÓN

### Roles y Grupos (Config/AuthGroups.php)
```php
'superadmin' => [
    'title' => 'Super Administrador',
    'description' => 'Control total del sistema'
],
'admin' => [
    'title' => 'Administrador', 
    'description' => 'Administración general'
],
'vendedor' => [
    'title' => 'Vendedor',
    'description' => 'Gestión de ventas'
],
'cliente' => [
    'title' => 'Cliente',
    'description' => 'Acceso a panel de cliente'
]
```

### Helpers de Autenticación (`auth_helper.php`)
```php
// Verificación de roles
isAdmin()               // ¿Es admin o superadmin?
isSuperAdmin()          // ¿Es superadmin?
isVendedor()            // ¿Es vendedor?
isCliente()             // ¿Es cliente?

// Verificación de permisos
can('ventas.create')    // ¿Puede crear ventas?
canAny(['ventas.read', 'ventas.update']) // ¿Algún permiso?

// Información del usuario
userName()              // Nombre formateado
userRole()              // Rol principal
userPhone()             // Teléfono formateado
userCompleteness()      // % de información completa
```

## 📄 GESTIÓN DE DOCUMENTOS

### Proceso de Subida de Documentos
```
1. Validación de archivo (tipo MIME, tamaño)
2. Generación de nombre único: {RFC}_{tipo}.{ext}
3. Almacenamiento en /public/uploads/clientes/{RFC}/
4. Registro en documentos_clientes
5. Desactivación de versiones anteriores del mismo tipo
```

### DocumentoClienteModel::subirDocumento()
```php
public function subirDocumento(array $data): bool
{
    // 1. Validar archivo
    // 2. Generar nombre único  
    // 3. Mover archivo a carpeta del cliente
    // 4. Desactivar versiones anteriores
    // 5. Registrar en BD
    // 6. Log de actividad
}
```

## 🚨 PENDIENTES IDENTIFICADOS

### 1. Tabla documentos_staff
```sql
-- CREAR TABLA FALTANTE
CREATE TABLE documentos_staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT UNSIGNED NOT NULL,
    tipo_documento VARCHAR(50) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(id)
);
```

### 2. DocumentoStaffModel
- Crear modelo similar a DocumentoClienteModel
- Implementar métodos de subida/gestión

### 3. Módulo Mi-Perfil
- Controladores: AdminMiPerfilController, ClienteMiPerfilController  
- Vistas: admin/mi-perfil/, cliente/mi-perfil/
- Upload de documentos para staff
- Cambio de contraseña con validación mínima

## 🔍 CONSIDERACIONES DE SEGURIDAD

### Separación de Responsabilidades
- **Shield**: Maneja autenticación, sesiones, grupos
- **Entities**: Lógica de negocio y formateo
- **Models**: Operaciones de BD y validaciones
- **Controllers**: Flujo HTTP y autorización

### Validaciones
- Email único en auth_identities (Shield)
- RFC único en clientes/staff
- Tipos MIME permitidos: jpg, png, pdf
- Tamaño máximo: 10MB para documentos, 2MB para fotos

### Estados
- Usuarios creados por registro público → INACTIVOS por defecto
- Activación manual por administradores
- Documentos versionados (solo el último activo)

## 🎯 PRÓXIMOS PASOS

1. **Crear tabla documentos_staff**
2. **Implementar DocumentoStaffModel**  
3. **Desarrollar módulo Mi-Perfil respetando arquitectura actual**
4. **Mantener principios Entity-First y DRY**