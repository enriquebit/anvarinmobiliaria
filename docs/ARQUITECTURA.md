# ARQUITECTURA.md - Arquitectura del Sistema NuevoAnvar

Documentación completa de la arquitectura del sistema inmobiliario basada en la implementación real y comprobada.

## 🏗️ ARQUITECTURA GENERAL

### Visión de Alto Nivel
```
┌─────────────────────────────────────────────────────────────┐
│                    SISTEMA INMOBILIARIO                     │
│                        NuevoAnvar                           │
└─────────────────────────────────────────────────────────────┘
                                │
                   ┌─────────────┴─────────────┐
                   │                           │
            ┌──────▼──────┐              ┌────▼────┐
            │   Frontend  │              │ Backend │
            │  AdminLTE   │              │  CI4    │
            │  + jQuery   │              │ + Shield│
            └─────────────┘              └─────────┘
                   │                           │
                   └─────────────┬─────────────┘
                                 │
                         ┌───────▼───────┐
                         │   Base Datos  │
                         │    MySQL      │
                         │  (19 tablas)  │
                         └───────────────┘
```

### Stack Tecnológico Implementado
```bash
✅ Backend Framework: CodeIgniter 4.x
✅ Autenticación: CodeIgniter Shield
✅ Frontend Framework: AdminLTE 3.x
✅ JavaScript: jQuery + Bootstrap 4
✅ Base de Datos: MySQL/MariaDB
✅ Servidor Web: Apache/Nginx (NO php spark serve)
✅ Plugins: DataTables, SweetAlert2, Toastr, Chart.js
```

---

## 📁 ARQUITECTURA DE DIRECTORIOS

### Estructura Real Implementada
```
nuevoanvar/
├── app/
│   ├── Controllers/
│   │   ├── Admin/              # 🎯 Controladores administrativos
│   │   │   ├── AdminClientesController.php
│   │   │   ├── AdminEmpresasController.php
│   │   │   ├── AdminProyectosController.php
│   │   │   └── [otros módulos]
│   │   ├── Cliente/            # 👥 Controladores para clientes
│   │   ├── Auth/               # 🔐 Autenticación personalizada
│   │   └── Debug/              # 🐛 Solo desarrollo
│   │
│   ├── Models/                 # 📊 Capa de datos
│   │   ├── ClienteModel.php
│   │   ├── EmpresaModel.php
│   │   ├── ProyectoModel.php
│   │   └── [otros modelos]
│   │
│   ├── Entities/               # 🎯 Lógica de dominio
│   │   ├── Cliente.php
│   │   ├── Empresa.php
│   │   ├── Proyecto.php
│   │   └── [otras entidades]
│   │
│   ├── Helpers/                # 🔧 Funciones reutilizables
│   │   ├── auth_helper.php     # ✅ Autenticación
│   │   ├── format_helper.php   # ✅ Formateo
│   │   ├── inmobiliario_helper.php # 🚧 Lógica negocio
│   │   ├── validation_helper.php   # 🚧 Validaciones
│   │   └── ui_helper.php           # 🚧 Elementos UI
│   │
│   ├── Views/
│   │   ├── layouts/            # 🎨 Layouts por rol
│   │   │   ├── admin.php       # Layout administrativo
│   │   │   ├── cliente.php     # Layout cliente
│   │   │   ├── auth.php        # Layout autenticación
│   │   │   └── partials/       # Componentes reutilizables
│   │   ├── admin/              # 👨‍💼 Vistas admin
│   │   │   ├── clientes/
│   │   │   ├── empresas/
│   │   │   ├── proyectos/
│   │   │   └── [otros módulos]/
│   │   └── cliente/            # 👤 Vistas cliente
│   │
│   ├── Database/
│   │   ├── Migrations/         # 📈 Evolución incremental
│   │   ├── Seeds/              # 🌱 Datos iniciales
│   │   └── nuevoanvar.sql      # 📋 Schema completo
│   │
│   ├── Filters/                # 🛡️ Middleware de seguridad
│   │   ├── AdminFilter.php
│   │   ├── ClienteFilter.php
│   │   └── LoginFilter.php
│   │
│   └── Config/                 # ⚙️ Configuraciones
│       ├── Routes.php          # 🛣️ Rutas del sistema
│       ├── Database.php        # 🗄️ Conexión BD
│       └── Validation.php      # ✅ Reglas validación
│
├── public/
│   ├── assets/                 # 🎨 Recursos estáticos
│   │   ├── adminlte/          # AdminLTE framework
│   │   ├── plugins/           # jQuery plugins
│   │   └── css/               # CSS personalizado
│   └── uploads/               # 📁 Archivos subidos
│       └── proyectos/         # Por módulo específico
│
└── writable/
    └── logs/                  # 📝 Logs del sistema
```

---

## 🎯 PATRONES ARQUITECTÓNICOS

### 1. MVC (Model-View-Controller)
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│      MODEL      │    │   CONTROLLER    │    │      VIEW       │
│                 │    │                 │    │                 │
│ • Entities      │◄──►│ • Validación    │◄──►│ • AdminLTE      │
│ • Models        │    │ • Lógica flujo  │    │ • Formularios   │
│ • Validaciones  │    │ • Helpers       │    │ • DataTables    │
│ • Persistencia  │    │ • Redirección   │    │ • JavaScript    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         ▲                       ▲                       ▲
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                        BASE DE DATOS                            │
│     MySQL con 19 tablas relacionadas + integridad referencial   │
└─────────────────────────────────────────────────────────────────┘
```

### 2. Repository Pattern (via CodeIgniter Models)
```php
// Abstracción de acceso a datos
class ProyectoModel extends Model
{
    // ✅ Configuración estándar
    protected $table = 'proyectos';
    protected $returnType = 'App\Entities\Proyecto';
    
    // ✅ Métodos de consulta específicos
    public function getProyectosConEmpresa() { /*...*/ }
    public function getProyectosPorEstado($estado) { /*...*/ }
}
```

### 3. Entity Pattern (Domain Objects)
```php
// Objetos de dominio con comportamiento
class Proyecto extends Entity
{
    // ✅ Transformación automática de datos
    protected $casts = ['created_at' => 'datetime'];
    
    // ✅ Lógica de negocio encapsulada
    public function hasCoordinates(): bool { /*...*/ }
    public function getColorHex(): string { /*...*/ }
}
```

### 4. Helper Pattern (DRY Implementation)
```php
// Funciones globales reutilizables
formatPrecio($precio)           // $1,234.56
formatTelefono($telefono)       // (55) 1234-5678
badgeEstadoPropiedad($estado)   // HTML badge
isAdmin()                       // true/false
```

### 5. Filter Pattern (Middleware)
```php
// Filtros de autorización por ruta
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    // Solo usuarios admin/superadmin
});
```

---

## 🔐 ARQUITECTURA DE SEGURIDAD

### Sistema de Autenticación
```
┌─────────────────────────────────────────────────────────────┐
│                    CODEIGNITER SHIELD                       │
└─────────────────────────────────────────────────────────────┘
                                │
                   ┌─────────────┴─────────────┐
                   │                           │
            ┌──────▼──────┐              ┌────▼────┐
            │    Users    │              │  Groups │
            │   (tabla)   │              │ (roles) │
            └─────────────┘              └─────────┘
                   │                           │
                   └─────────────┬─────────────┘
                                 │
                    ┌────────────▼────────────┐
                    │     auth_identities     │
                    │   (credenciales)        │
                    └─────────────────────────┘
```

### Roles y Permisos Implementados
```php
// ✅ Roles definidos en el sistema
'superadmin' => [
    'admin.clientes.*',
    'admin.empresas.*', 
    'admin.proyectos.*',
    'admin.staff.*',
    'admin.settings.*'
],

'admin' => [
    'admin.clientes.*',
    'admin.proyectos.*',
    'admin.ventas.*'
],

'cliente' => [
    'cliente.dashboard',
    'cliente.perfil.*',
    'cliente.pagos.view'
],

'vendedor' => [
    'admin.clientes.*',
    'admin.ventas.*'
]
```

### Flujo de Autorización
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Request   │───►│   Filter    │───►│  Controller │───►│   Response  │
│             │    │             │    │             │    │             │
│ /admin/     │    │ AdminFilter │    │ AdminXxx    │    │ View/JSON   │
│ clientes    │    │ ✅ isAdmin()│    │ Controller  │    │             │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                           │
                    ┌──────▼──────┐
                    │ Unauthorized│
                    │ → Redirect  │
                    │ to /login   │
                    └─────────────┘
```

---

## 📊 ARQUITECTURA DE DATOS

### Diseño de Base de Datos
```
┌─────────────────────────────────────────────────────────────────┐
│                       DISEÑO RELACIONAL                         │
└─────────────────────────────────────────────────────────────────┘

    ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
    │    USERS    │──────│  CLIENTES   │──────│ DIRECCIONES │
    │  (Shield)   │ 1:1  │             │ 1:N  │   CLIENTE   │
    └─────────────┘      └─────────────┘      └─────────────┘
                                │
                                │ 1:N
                                ▼
                         ┌─────────────┐
                         │ REFERENCIAS │
                         │   CLIENTE   │
                         └─────────────┘

    ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
    │  EMPRESAS   │──────│  PROYECTOS  │──────│ DOCUMENTOS  │
    │             │ 1:N  │             │ 1:N  │  PROYECTO   │
    └─────────────┘      └─────────────┘      └─────────────┘
           │                     │
           │ 1:N                 │ 1:N (futuro)
           ▼                     ▼
    ┌─────────────┐      ┌─────────────┐
    │    STAFF    │      │ PROPIEDADES │
    │             │      │  (pendiente)│
    └─────────────┘      └─────────────┘
```

### Integridad Referencial
```sql
-- ✅ Foreign Keys implementadas
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE

-- ✅ Índices para performance
KEY idx_clientes_etapa (etapa_proceso)
KEY idx_clientes_asesor (asesor_asignado)
KEY idx_proyecto_empresa (empresa_id)
```

---

## 🌐 ARQUITECTURA DE PRESENTACIÓN

### Sistema de Layouts
```
┌─────────────────────────────────────────────────────────────────┐
│                      LAYOUTS JERÁRQUICOS                        │
└─────────────────────────────────────────────────────────────────┘

app/Views/layouts/
├── admin.php           # 👨‍💼 Layout administrativo
│   ├── Header
│   ├── Sidebar        # 📋 Navegación por módulos
│   ├── Content        # 🎯 Contenido específico
│   └── Footer         # 📊 Scripts AdminLTE
│
├── cliente.php         # 👤 Layout para clientes
│   ├── Header
│   ├── Navigation     # 🧭 Menú simplificado
│   ├── Content
│   └── Footer
│
└── auth.php            # 🔐 Layout autenticación
    ├── Header
    ├── Login Form     # 📝 Formularios login/registro
    └── Footer
```

### Componentes Reutilizables
```php
// ✅ Partials implementados
app/Views/layouts/partials/
├── admin/
│   ├── sidebar.php           # Navegación lateral
│   ├── header.php            # Barra superior
│   └── footer.php            # Scripts y cerrado
│
├── cliente/
│   ├── header.php            # Header cliente
│   └── navigation.php        # Menú cliente
│
└── common/
    ├── breadcrumbs.php       # Navegación miga de pan
    ├── alerts.php            # Notificaciones
    └── modals.php            # Ventanas modales
```

### Integración AdminLTE
```html
<!-- ✅ Estructura AdminLTE implementada -->
<div class="wrapper">
    <!-- Header -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
    
    <!-- Content -->
    <div class="content-wrapper">
        <div class="content-header">           <!-- Breadcrumbs -->
        <section class="content">              <!-- Contenido principal -->
    
    <!-- Footer -->
    <footer class="main-footer">
</div>
```

---

## 🔧 ARQUITECTURA DE HELPERS

### Sistema DRY Implementado
```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEMA DE HELPERS                           │
└─────────────────────────────────────────────────────────────────┘

app/Helpers/
├── auth_helper.php          # ✅ Implementado
│   ├── userName()           # Usuario actual
│   ├── userRole()           # Rol del usuario  
│   ├── isAdmin()            # Verificar permisos
│   └── hasPermission()      # Permisos específicos
│
├── format_helper.php        # ✅ Implementado
│   ├── formatPrecio()       # $1,234.56
│   ├── formatTelefono()     # (55) 1234-5678
│   ├── formatFecha()        # 01/12/2024
│   └── formatSuperficie()   # 150.50 m²
│
├── inmobiliario_helper.php  # 🚧 En desarrollo
│   ├── calcularComision()   # Comisiones asesores
│   ├── badgeEstado()        # Badges HTML
│   ├── iconoTipo()          # Iconos por tipo
│   └── generarClave()       # Claves únicas
│
├── validation_helper.php    # 🚧 Pendiente
│   ├── validarRFC()         # RFC mexicano
│   ├── validarCURP()        # CURP mexicana
│   └── validarTelefono()    # Teléfono MX
│
└── ui_helper.php           # 🚧 Pendiente
    ├── breadcrumb()         # Navegación
    ├── botonAccion()        # Botones estándar
    └── alertNotification()  # Notificaciones
```

### Carga de Helpers
```php
// ✅ BaseController - Carga automática
class BaseController extends Controller
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Helpers cargados automáticamente
        helper(['auth', 'format', 'inmobiliario']);
    }
}
```

---

## 📱 ARQUITECTURA RESPONSIVE

### Diseño Responsive Implementado
```css
/* ✅ Bootstrap 4 + AdminLTE responsive */
.content-wrapper {
    @media (max-width: 768px) {
        margin-left: 0;          /* Sidebar colapsado */
    }
}

.card {
    @media (max-width: 576px) {
        margin: 0.5rem;          /* Espaciado móvil */
    }
}

/* ✅ DataTables responsive */
table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control {
    position: relative;
    padding-left: 30px;
}
```

### Componentes Móviles
```html
<!-- ✅ Cards responsive -->
<div class="row">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card">
            <!-- Contenido adaptable -->
        </div>
    </div>
</div>

<!-- ✅ Tablas responsive -->
<div class="table-responsive">
    <table class="table table-bordered table-striped" id="dataTable">
        <!-- DataTables con responsive extension -->
    </table>
</div>
```

---

## 🚀 ARQUITECTURA DE PERFORMANCE

### Optimizaciones Implementadas
```php
// ✅ Entity caching
protected $casts = [
    'created_at' => 'datetime',    // Cast automático
    'precio' => 'float',           // Conversión directa
    'activo' => 'boolean'          // Boolean nativo
];

// ✅ Query optimization
public function getProyectosConEmpresa()
{
    return $this->select('proyectos.*, empresas.nombre as nombre_empresa')
                ->join('empresas', 'empresas.id = proyectos.empresa_id')
                ->findAll();  // JOIN en lugar de N+1 queries
}

// ✅ Helper optimization
function formatPrecio(float $precio): string
{
    static $formatter;
    if (!$formatter) {
        $formatter = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
    }
    return $formatter->formatCurrency($precio, 'MXN');
}
```

### Estrategias de Cacheo (Futuro)
```php
// 🚧 Para implementar post-MVP
- Query caching para consultas frecuentes
- View caching para páginas estáticas  
- Helper result caching
- File-based cache para configuraciones
```

---

## 🔄 ARQUITECTURA DE FLUJO DE DATOS

### Flujo Request-Response
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Browser   │───►│   Routes    │───►│   Filter    │───►│ Controller  │
│             │    │             │    │             │    │             │
│ GET /admin/ │    │ Routes.php  │    │AdminFilter  │    │AdminXxx     │
│ proyectos   │    │             │    │             │    │Controller   │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
       ▲                                                          │
       │                                                          ▼
       │                                                  ┌─────────────┐
       │                                                  │    Model    │
       │                                                  │             │
       │                                                  │ ProyectoModel│
       │                                                  │             │
       │                                                  └─────────────┘
       │                                                          │
       │                                                          ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Response  │◄───│    View     │◄───│   Entity    │◄───│  Database   │
│   (HTML)    │    │             │    │             │    │             │
│AdminLTE +   │    │admin/       │    │ Proyecto    │    │   MySQL     │
│DataTables   │    │proyectos/   │    │ Entity      │    │             │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
```

### Flujo de Datos CRUD
```
CREATE:
Browser → Routes → Filter → Controller → Validation → Model → DB
                                          ↓
Browser ← Redirect ← Flash Message ← Controller ← Result ← DB

READ:
Browser → Routes → Filter → Controller → Model → Entity → View → Browser

UPDATE:
Browser → Routes → Filter → Controller → Validation → Model → DB
                                          ↓
Browser ← Redirect ← Flash Message ← Controller ← Result ← DB

DELETE:
Browser → Routes → Filter → Controller → Model → DB
                                          ↓
Browser ← Redirect ← Flash Message ← Controller ← Result ← DB
```

---

## 🧩 ARQUITECTURA MODULAR

### Organización por Módulos
```
MÓDULOS IMPLEMENTADOS:
├── 🔐 Autenticación (Shield)
│   ├── Login/Logout
│   ├── Registro clientes
│   ├── Gestión usuarios
│   └── Roles y permisos
│
├── 👥 Clientes (Completo)
│   ├── CRUD clientes
│   ├── Direcciones múltiples
│   ├── Referencias familiares
│   ├── Información laboral
│   └── Datos cónyuge
│
├── 🏢 Empresas (Configurado)
│   ├── Datos básicos
│   ├── Configuración financiera
│   ├── Parámetros comisiones
│   └── Términos de pago
│
├── 👨‍💼 Staff (Implementado)
│   ├── Gestión empleados
│   ├── Roles internos
│   ├── Asignación empresas
│   └── Perfiles usuario
│
└── 🏗️ Proyectos (Completo) ⭐
    ├── CRUD proyectos
    ├── Geolocalización
    ├── Documentos adjuntos
    └── Relación empresas

MÓDULOS PENDIENTES:
├── 🏠 Propiedades (Próximo)
├── 💰 Ventas
├── 💳 Pagos
├── 👨‍💼 Asesores
├── 🎫 Tickets
└── 📊 Reportes
```

### Interdependencias de Módulos
```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  EMPRESAS   │───►│  PROYECTOS  │───►│ PROPIEDADES │
│             │1:N │             │1:N │  (futuro)   │
└─────────────┘    └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │ DOCUMENTOS  │    │   VENTAS    │
                   │  PROYECTO   │    │  (futuro)   │
                   └─────────────┘    └─────────────┘

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    USERS    │───►│  CLIENTES   │───►│   VENTAS    │
│  (Shield)   │1:1 │             │1:N │  (futuro)   │
└─────────────┘    └─────────────┘    └─────────────┘
                           │
                           ▼
                   ┌─────────────┐
                   │ DIRECCIONES │
                   │ REFERENCIAS │
                   │INFO LABORAL │
                   └─────────────┘
```

---

## 🛡️ ARQUITECTURA DE TESTING

### Estrategia de Testing Implementada
```bash
✅ Testing Manual (Actual):
    - CRUD completo por módulo
    - Validaciones de formularios
    - Autorización por roles
    - Flujos de usuario end-to-end

🚧 Testing Automatizado (Futuro):
    - Unit tests para Models/Entities
    - Integration tests para Controllers  
    - Feature tests para flujos completos
    - Database testing con fixtures
```

### Estructura de Testing
```php
// 🚧 Para implementar post-MVP
tests/
├── Unit/
│   ├── Models/
│   ├── Entities/
│   └── Helpers/
├── Feature/
│   ├── Admin/
│   ├── Cliente/
│   └── Auth/
└── Database/
    ├── Migrations/
    └── Seeders/
```

---

## 📈 ARQUITECTURA ESCALABLE

### Preparación para Crecimiento
```
ESCALABILIDAD HORIZONTAL:
├── 🗄️ Database Sharding (por empresa)
├── 📊 Read Replicas (consultas)
├── 🌐 CDN para archivos estáticos
└── ⚖️ Load Balancer múltiples servidores

ESCALABILIDAD VERTICAL:
├── 💾 Cache layers (Redis/Memcached)
├── 🔄 Queue system (jobs pesados)
├── 📁 File storage (S3/CloudFlare)
└── 🏗️ Microservices (módulos independientes)
```

### Patrones de Crecimiento
```php
// ✅ Preparado para múltiples empresas
class BaseController extends Controller
{
    protected function getCurrentEmpresa()
    {
        // Detectar empresa por subdominio o parámetro
        return session('empresa_id') ?? 1;
    }
}

// ✅ Preparado para multi-tenancy
protected function scopeToEmpresa($query)
{
    return $query->where('empresa_id', $this->getCurrentEmpresa());
}
```

---

## 🎯 PRINCIPIOS ARQUITECTÓNICOS

### SOLID Principles Aplicados
```php
// ✅ Single Responsibility
class ProyectoModel extends Model          // Solo persistencia
class Proyecto extends Entity              // Solo lógica dominio
class AdminProyectosController extends Controller  // Solo flujo

// ✅ Open/Closed
interface FormatterInterface {
    public function format($data): string;
}
class PrecioFormatter implements FormatterInterface { /*...*/ }

// ✅ Dependency Inversion
class ProyectoService {
    public function __construct(ProyectoModelInterface $model) { /*...*/ }
}
```

### DRY (Don't Repeat Yourself)
```php
// ✅ Helpers para código común
formatPrecio($precio)          // En lugar de repetir number_format
badgeEstado($estado)           // En lugar de repetir HTML
isAdmin()                      // En lugar de verificaciones manuales

// ✅ Layouts para UI común
$this->extend('layouts/admin') // En lugar de repetir HTML
```

### KISS (Keep It Simple, Stupid)
```php
// ✅ MVP funcional antes que arquitectura compleja
public function index()
{
    $proyectos = $this->proyectoModel->findAll();
    return view('admin/proyectos/index', compact('proyectos'));
}
```

---

## 🔮 ARQUITECTURA FUTURA

### Roadmap Técnico
```bash
FASE ACTUAL (MVP):
✅ Funcionalidad básica
✅ CRUD completo
✅ Helpers DRY

FASE 2 (Optimización):
🚧 Cacheo inteligente
🚧 Testing automatizado
🚧 API REST endpoints

FASE 3 (Escalabilidad):
🚧 Microservices
🚧 Event-driven architecture
🚧 Real-time notifications

FASE 4 (Inteligencia):
🚧 Analytics avanzados
🚧 Machine learning
🚧 Automatización procesos
```

### Tecnologías Emergentes
```bash
🚧 Para considerar futuro:
    - Vue.js/React frontend
    - GraphQL API
    - Docker containerization
    - Kubernetes orchestration
    - ElasticSearch full-text
    - WebSocket real-time
```

---

**¡Esta arquitectura está probada, funcionando y lista para escalar! 🏗️**