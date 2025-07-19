# Proyecto: Sistema de Autenticación - Anvar Inmobiliaria

## 📋 **CONTEXTO DEL PROYECTO**

Estoy desarrollando un sistema de autenticación completo para **Anvar Inmobiliaria** usando **CodeIgniter 4.6.1** con **Shield v1.1** para la autenticación. El proyecto está enfocado en **simplicidad** y **metodología MVP** (Minimum Viable Product).

## 🎯 **METODOLOGÍA DE PROGRAMACIÓN ADOPTADA**

### **Características del enfoque:**
- **Filters específicos** en lugar de validaciones repetidas
- **Entities de Shield** para lógica de negocio
- **UserModel extendido** para campos personalizados
- **Helpers simples** para utilidades comunes
- **Controladores RESTful** con métodos específicos
- **Vistas organizadas** por módulos
- **Validación en métodos privados** para DRY

### **Estructura de archivos adoptada:**
```
app/
├── Controllers/
│   ├── BaseController.php
│   ├── Auth/ (Login, Register)
│   ├── Cliente/ (DashboardController)
│   ├── Staff/ (DashboardController)
│   └── Admin/ (UsuariosController, ClientesController)
├── Filters/
│   ├── Login.php (equivalente a AuthFilter)
│   ├── Client.php
│   └── Staff.php
├── Models/
│   ├── UserModel.php (extendido de Shield)
│   ├── ClientesModel.php
│   └── StaffModel.php
├── Entities/
│   ├── User.php (extendido de Shield)
│   ├── Cliente.php
│   └── Staff.php
├── Helpers/
│   ├── auth_helper.php
│   └── admin_helper.php
└── Views/
    ├── layouts/ (default.php, auth.php, admin.php)
    ├── auth/ (login.php, register.php)
    ├── cliente/ (dashboard.php)
    ├── staff/ (dashboard.php)
    └── admin/usuarios/ (index.php, show.php, groups.php, permissions.php)
```

## 📄 **SISTEMA DE DOCUMENTOS PLANIFICADO**

### **Tipos de documentos por rol:**

**Para Staff:**
- Contrato de trabajo
- Identificación oficial
- Comprobante de domicilio  
- RFC
- CURP

**Para Clientes:**
- Identificación oficial
- Contrato firmado
- RFC
- CURP
- Comprobantes de pago (numeración incremental automática)
- Comprobante de domicilio
- Aviso de privacidad firmado

### **Estados de documentos:**
- `pendiente` - Recién subido, esperando revisión
- `aprobado` - Aprobado por administrador
- `rechazado` - Rechazado con observaciones

### **Helpers para archivos:**
```php
// Helper personalizado para documentos (por crear)
function allowedFileTypes(string $role): array
function maxFileSize(): int
function uploadPath(string $role, string $tipo): string
function validateDocument(array $file, string $tipo): bool
```

### **Configuración de archivos:**
```php
// En controlador de documentos
$config = [
    'max_size' => 2048,  // 2MB
    'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
    'upload_path' => FCPATH . 'uploads/',
    'encrypt_name' => true
];
```

## 🗄️ **BASE DE DATOS IMPLEMENTADA**

### **Tablas Shield (automáticas):**
- `users` - Usuarios base de Shield
- `auth_identities` - Credenciales de acceso
- `auth_groups` - Roles/grupos
- `auth_groups_users` - Relación usuarios-roles
- `auth_permissions` - Permisos granulares

### **Tablas personalizadas creadas:**
1. **`clientes`** - Información de clientes externos
2. **`staff`** - Información de empleados internos  
3. **`direcciones`** - Domicilios de usuarios

### **Tablas de documentos (por implementar):**
4. **`documentos_staff`** - Documentos del personal interno
5. **`documentos_clientes`** - Documentos de clientes

### **Estructura de documentos:**
```sql
-- Campos comunes en ambas tablas de documentos
id, [staff_id|cliente_id], tipo_documento, nombre_archivo, 
ruta_archivo, tamano_archivo, extension, estado, observaciones,
aprobado_por, fecha_aprobacion, activo, created_at, updated_at

-- Campo adicional en documentos_clientes
numero_comprobante (para comprobantes de pago consecutivos)
```

### **Campos principales:**
```sql
-- Tabla clientes
user_id, nombres, apellido_paterno, apellido_materno, telefono, 
fecha_nacimiento, rfc, curp, tiempo_radicando, tipo_residencia, 
estado_civil, nacionalidad, lugar_nacimiento, ocupacion, 
activo (false por defecto), fecha_activacion, activado_por

-- Tabla staff  
user_id, nombres, apellido_paterno, apellido_materno, telefono,
fecha_nacimiento, activo (true por defecto)
```

## ⚙️ **CONFIGURACIÓN IMPLEMENTADA**

### **1. Roles y Permisos (app/Config/AuthGroups.php):**
```php
'groups' => [
    'superadmin' => ['title' => 'Super Administrador'],
    'admin' => ['title' => 'Administrador'],
    'gerente' => ['title' => 'Gerente'],
    'ventas' => ['title' => 'Ventas'],
    'auxiliar' => ['title' => 'Auxiliar'],
    'clientes' => ['title' => 'Cliente'],
]

'permissions' => [
    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.activate',
    'profile.view', 'profile.edit'
]
```

### **2. Helpers Automáticos (app/Config/Autoload.php):**
```php
public $helpers = [
    'auth',        // Helper personalizado de autenticación
    'file',        // Helper personalizado de archivos  
    'form',        // Helper de formularios de CI4 (form_open, form_close, etc.)
    'url',         // Helper de URLs de CI4 (base_url, site_url, etc.)
    'html',        // Helper de HTML de CI4
    'text',        // Helper de texto de CI4
    'security',    // Helper de seguridad de CI4
    'filesystem',  // Helper de sistema de archivos de CI4
];
```

### **3. UserModel Extendido (app/Models/UserModel.php):**
```php
<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();
        
        $this->allowedFields = [
            ...$this->allowedFields,
            'first_name', 'telephone'  // Campos adicionales
        ];
    }
}
```

### **4. Helper Personalizado (app/Helpers/auth_helper.php):**
```php
function can(string $permission): bool          // Verificar permisos
function is(string $role): bool                 // Verificar rol específico  
function isAny(array $roles): bool              // Verificar múltiples roles
function currentRole(): string                  // Rol actual del usuario
function userName(): string                     // Nombre completo del usuario
```

### **5. Helper Administrativo (app/Helpers/admin_helper.php):**
```php
function yesno(bool $value): string             // Convertir bool a "yes"/"no"
function formatFileSize(int $bytes): string     // Formatear tamaño de archivo
function getStatusBadge(string $status): string // Badge HTML según estado
```

## 🛡️ **FILTERS IMPLEMENTADOS**

### **1. Login Filter (app/Filters/Login.php):**
```php
public function before(RequestInterface $request, $arguments = null)
{
    if (!auth()->loggedIn()) {
        return redirect()->to("login")->with("message", "Debes iniciar sesión primero.");
    }
}
```

### **2. Client Filter** - Específico para rutas de clientes
### **3. Staff Filter** - Específico para rutas de staff  
### **4. Admin Filter** - Para rutas administrativas

### **Configuración en Filters.php:**
```php
public array $aliases = [
    'login' => \App\Filters\Login::class,
    'client' => \App\Filters\Client::class,
    'staff' => \App\Filters\Staff::class,
    'admin' => \App\Filters\Admin::class,
];
```

## 🛣️ **RUTAS ORGANIZADAS**

```php
// Públicas
/login (GET/POST), /register (GET/POST)

// Con redirección automática
/dashboard -> redirige según rol

// Clientes (filter: client)
/cliente/dashboard (GET), /cliente/perfil (GET/POST), /cliente/documentos (GET/POST)

// Staff (filter: staff) 
/staff/dashboard (GET), /staff/perfil (GET/POST)

// Admin (filter: staff + permisos) - con method spoofing
/admin/usuarios/ (GET) - Listar usuarios
/admin/usuarios/crear (GET/POST) - Crear usuario
/admin/usuarios/{id} (GET) - Ver usuario
/admin/usuarios/{id}/editar (GET/POST) - Editar usuario  
/admin/usuarios/{id} (PUT) - Actualizar usuario (method spoofing)
/admin/usuarios/{id} (DELETE) - Eliminar usuario (method spoofing)
/admin/usuarios/{id}/activar (PATCH) - Activar/desactivar (method spoofing)
```

**Configuración de rutas con métodos HTTP:**
```php
// En Routes.php - Soporte para method spoofing
$routes->post('admin/usuarios/(:num)', 'Admin\UsuariosController::update/$1', ['filter' => 'permission:users.edit']);
$routes->delete('admin/usuarios/(:num)', 'Admin\UsuariosController::delete/$1', ['filter' => 'permission:users.delete']);
$routes->patch('admin/usuarios/(:num)/activar', 'Admin\UsuariosController::toggleStatus/$1', ['filter' => 'permission:users.activate']);
```

## 📁 **ARCHIVOS CREADOS**

## 📁 **ARCHIVOS SIGUIENDO LA METODOLOGÍA**

### **Controladores con metodología adoptada:**

**Admin/UsuariosController.php - Ejemplo de implementación:**
```php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Exceptions\PageNotFoundException;

class UsuariosController extends BaseController
{
    private UserModel $model;
    
    public function __construct() {
        $this->model = new UserModel;
    }
    
    public function index() {
        $users = $this->model->paginate(10);
        return view("admin/usuarios/index", [
            "users" => $users,
            "pager" => $this->model->pager
        ]);
    }
    
    public function show($id) {
        $user = $this->getUserOr404($id);
        return view("admin/usuarios/show", ["user" => $user]);
    }
    
    public function groups($id) {
        $user = $this->getUserOr404($id);
        
        if ($this->request->is('post')) {
            $groups = $this->request->getPost("groups") ?? [];
            $user->syncGroups(...$groups);
            return redirect()->to("admin/usuarios/$id")
                           ->with("message", "Grupos actualizados correctamente.");
        }
        
        return view("admin/usuarios/groups", ["user" => $user]);
    }
    
    public function permissions($id) {
        $user = $this->getUserOr404($id);
        
        if ($this->request->is('post')) {
            $permissions = $this->request->getPost("permissions") ?? [];
            $user->syncPermissions(...$permissions);
            return redirect()->to("admin/usuarios/$id")
                           ->with("message", "Permisos actualizados correctamente.");
        }
        
        return view("admin/usuarios/permissions", ["user" => $user]);
    }
    
    public function toggleBan($id) {
        $user = $this->getUserOr404($id);
        
        if ($user->isBanned()) {
            $user->unBan();
        } else {
            $user->ban();
        }
        
        return redirect()->back()->with("message", "Estado actualizado.");
    }
    
    private function getUserOr404($id): User {
        $user = $this->model->find($id);
        if ($user === null) {
            throw new PageNotFoundException("Usuario con id: $id no encontrado");
        }
        return $user;
    }
}
```

### **Vistas con metodología adoptada:**

**admin/usuarios/index.php:**
```php
<?= $this->extend("layouts/admin") ?>
<?= $this->section("title") ?>Usuarios<?= $this->endSection(); ?>
<?= $this->section("content") ?>

<h1>Usuarios</h1>
<table class="table">
    <thead>
        <tr>
            <th>Email</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Activo</th>
            <th>Bloqueado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($users as $user): ?>
        <tr>
            <td><a href="<?= site_url("admin/usuarios/{$user->id}") ?>"><?= esc($user->email) ?></a></td>
            <td><?= esc($user->first_name) ?></td>
            <td><?= esc($user->telephone) ?></td>
            <td><?= yesno($user->active) ?></td>
            <td><?= yesno($user->isBanned()) ?></td>
            <td>
                <?php if (can('users.edit')): ?>
                    <a href="<?= site_url("admin/usuarios/{$user->id}/groups") ?>">Grupos</a>
                    <a href="<?= site_url("admin/usuarios/{$user->id}/permissions") ?>">Permisos</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $pager->links() ?>
<?= $this->endSection() ?>
```

**admin/usuarios/show.php:**
```php
<?= $this->extend("layouts/admin") ?>
<?= $this->section("title") ?>Usuario: <?= esc($user->first_name) ?><?= $this->endSection(); ?>
<?= $this->section("content") ?>

<h1>Detalles del Usuario</h1>

<dl class="row">
    <dt class="col-sm-3">Email</dt>
    <dd class="col-sm-9"><?= esc($user->email) ?></dd>
    
    <dt class="col-sm-3">Nombre</dt>
    <dd class="col-sm-9"><?= esc($user->first_name) ?></dd>
    
    <dt class="col-sm-3">Teléfono</dt>
    <dd class="col-sm-9"><?= esc($user->telephone) ?></dd>
    
    <dt class="col-sm-3">Estado</dt>
    <dd class="col-sm-9"><?= $user->active ? 'Activo' : 'Inactivo' ?></dd>
    
    <dt class="col-sm-3">Grupos</dt>
    <dd class="col-sm-9">
        <?= implode(", ", $user->getGroups()) ?>
        <?php if (can('users.edit')): ?>
            <a href="<?= site_url("admin/usuarios/{$user->id}/groups") ?>" class="btn btn-sm btn-primary">Cambiar</a>
        <?php endif; ?>
    </dd>
    
    <dt class="col-sm-3">Permisos</dt>
    <dd class="col-sm-9">
        <?= implode(", ", $user->getPermissions()) ?>
        <?php if (can('users.edit')): ?>
            <a href="<?= site_url("admin/usuarios/{$user->id}/permissions") ?>" class="btn btn-sm btn-primary">Cambiar</a>
        <?php endif; ?>
    </dd>
    
    <dt class="col-sm-3">Bloqueado</dt>
    <dd class="col-sm-9">
        <?= yesno($user->isBanned()) ?>
        <?php if (can('users.activate')): ?>
            <?= form_open("admin/usuarios/{$user->id}/toggle-ban", ['style' => 'display:inline']) ?>
                <button type="submit" class="btn btn-sm btn-warning">
                    <?= $user->isBanned() ? "Desbloquear" : "Bloquear" ?>
                </button>
            <?= form_close() ?>
        <?php endif; ?>
    </dd>
</dl>

<div class="mt-3">
    <a href="<?= site_url('admin/usuarios') ?>" class="btn btn-secondary">← Volver a usuarios</a>
</div>

<?= $this->endSection() ?>
```

### **Configuración:**
- `app/Config/Auth.php` - Configuración Shield personalizada
- `app/Config/AuthGroups.php` - Roles y permisos
- `app/Config/Routes.php` - Rutas con filters y method spoofing
- `app/Config/Filters.php` - Registro de filters
- `app/Config/Autoload.php` - Helpers automáticos
- `app/Config/App.php` - Habilitar method spoofing

**Method Spoofing en App.php:**
```php
// En app/Config/App.php
public bool $allowMethodSpoofing = true;  // Habilitar PUT, PATCH, DELETE
```

### **Utilidades:**
- `app/Commands/CreateSuperAdmin.php` - Comando para crear superadmin
- `app/Database/Seeds/SuperAdminSeeder.php` - Seeder de superadmin

## 🎨 **CONVENCIONES DE CÓDIGO ADOPTADAS**

### **Estructura de controladores:**
- **Constructor** con inyección de dependencias (`private UserModel $model`)
- **Métodos públicos** para cada acción (index, show, groups, permissions)
- **Métodos privados** para validaciones comunes (`getUserOr404()`)
- **Manejo de POST** en el mismo método que GET
- **Redirecciones** con mensajes de confirmación

### **Formularios:**
- Usar `form_open()` y `form_close()` únicamente para apertura/cierre
- HTML puro con PHP para todos los inputs
- **Method spoofing** con `<input type="hidden" name="_method" value="PUT">`
- **Checkboxes** para permisos y grupos múltiples

### **Vistas organizadas:**
- **Layouts** específicos: `layouts/default`, `layouts/admin`, `layouts/auth`
- **Estructura modular**: `admin/usuarios/index.php`, `admin/usuarios/show.php`
- **Extensión de layouts**: `<?= $this->extend("layouts/admin") ?>`
- **Secciones definidas**: `<?= $this->section("title") ?>`, `<?= $this->section("content") ?>`

### **Permisos en vistas:**
```php
<?php if (can('users.edit')): ?>
    <a href="<?= site_url("admin/usuarios/{$user->id}/groups") ?>">Cambiar Grupos</a>
<?php endif; ?>

<?php if (can('users.delete')): ?>
    <?= form_open("admin/usuarios/{$user->id}/delete") ?>
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" onclick="return confirm('¿Seguro?')">Eliminar</button>
    <?= form_close() ?>
<?php endif; ?>
```

### **Uso de Entities:**
```php
// En lugar de métodos del modelo, usar la entidad
$user->isBanned()           // Verificar si está bloqueado
$user->ban()                // Bloquear usuario
$user->unBan()              // Desbloquear usuario
$user->syncGroups(...$groups)      // Sincronizar grupos
$user->syncPermissions(...$perms)  // Sincronizar permisos
$user->inGroup('admin')     // Verificar grupo
$user->hasPermission('users.edit') // Verificar permiso
```

### **Helpers administrativos:**
```php
<?= yesno($user->active) ?>                    // "yes" o "no"
<?= yesno($user->isBanned()) ?>               // "yes" o "no"
<?= esc($user->email) ?>                      // Escapar HTML
<?= $user->created_at->humanize() ?>          // "hace 2 días"
```

## 🚀 **ESTADO ACTUAL DEL PROYECTO**

### **✅ COMPLETADO:**
- Instalación y configuración de Shield
- Sistema de roles y permisos granulares
- Autenticación con redirección automática por rol
- Registro de clientes con activación manual por admin
- Dashboards diferenciados (cliente vs staff)
- Filters para seguridad por rutas
- Helper personalizado para permisos
- Seeders para datos iniciales
- Layout responsive con AdminLTE

### **🔄 EN PROCESO:**
- Refactorización completa del código existente
- Implementación de principios DRY
- Optimización de controladores y vistas
- Gestión de documentos por rol

### **📋 PENDIENTE SIGUIENDO LA METODOLOGÍA:**
- **Módulo de gestión de usuarios** - CRUD completo con grupos y permisos (como ejemplo base)
- **Sistema de activación de clientes** pendientes con toggle similar al ban/unban
- **Gestión de documentos** (subida y aprobación) usando Entities para lógica
- **Sistema de perfiles editables** para clientes y staff
- **Módulos específicos del negocio inmobiliario** (ventas, lotes, etc.)
- **Entities personalizadas** para Cliente y Staff con métodos de negocio
- **Layout admin** completo con AdminLTE integrado

## 🧪 **DATOS DE PRUEBA**

### **SuperAdmin creado:**
- Email: `superadmin@anvarinmobiliaria.test`
- Password: `scret1234`

### **Comando para crear superadmin:**
```bash
php spark anvar:create-superadmin
```

### **Seeder:**
```bash
php spark db:seed SuperAdminSeeder
```

## 🎯 **PRÓXIMOS PASOS SUGERIDOS**

1. **Refactorizar controladores existentes** con nueva arquitectura
2. **Implementar módulo de gestión de usuarios** con permisos granulares
3. **Crear sistema de activación de clientes** pendientes
4. **Desarrollar gestión de documentos** diferenciada por rol
5. **Optimizar vistas** con helpers de permisos

## 💡 **INSTRUCCIONES PARA CONTINUAR**

Cuando continúes el proyecto, recuerda:
- Seguir principios **DRY** y **MVP**
- Usar **filters específicos** en lugar de validaciones repetidas
- Implementar **permisos granulares** con Shield
- Mantener **HTML con PHP** en formularios (no helpers de CI para inputs)
- Usar **helpers personalizados** (`can()`, `is()`, `isAny()`) para permisos
- Seguir **convenciones establecidas** en rutas y nomenclatura
- **Validar siempre** tanto en vista como en controlador para seguridad

¿En qué parte específica del proyecto quieres que continue el desarrollo?