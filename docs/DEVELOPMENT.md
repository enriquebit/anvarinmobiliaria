# DEVELOPMENT.md - Workflow y Mejores Prácticas NuevoAnvar

Guía completa de desarrollo para el sistema inmobiliario basada en las prácticas implementadas y comprobadas del proyecto.

## 🎯 FILOSOFÍA DE DESARROLLO

### Principios Fundamentales
- **MVP-First**: Funcionalidad antes que perfección
- **Desarrollo Incremental**: Módulo por módulo hasta completar el ecosistema
- **DRY con Helpers**: Reutilizar código al máximo
- **Pragmatismo**: Que funcione primero, optimizar después
- **Metodología Entity-First**: Para módulos maduros

### Mantra del Proyecto
> **"CONSTRUYENDO EL SISTEMA INMOBILIARIO COMPLETO"**
> 
> **"QUE FUNCIONE + DRY + MVP = ÉXITO"**

---

## 🏗️ WORKFLOW DE DESARROLLO

### Proceso Completo para Nuevos Módulos
```
📋 1. PLANIFICACIÓN (15 min)
    ↓
🗄️ 2. BASE DE DATOS (30 min)
    ↓
📊 3. BACKEND (45 min)
    ↓
🎮 4. CONTROLLER (60 min)
    ↓
🖼️ 5. FRONTEND (90 min)
    ↓
🛣️ 6. CONFIGURACIÓN (15 min)
    ↓
🧪 7. TESTING MVP (30 min)
    ↓
🚀 8. DEPLOY & OPTIMIZACIÓN (variable)
```

**Tiempo total estimado: 4-6 horas por módulo MVP**

---

## 📋 FASE 1: PLANIFICACIÓN

### Checklist Obligatorio
```bash
✅ ¿Qué resuelve este módulo específicamente?
✅ ¿Qué tablas necesita en BD?
✅ ¿Se relaciona con módulos existentes?
✅ ¿Requiere carga de archivos?
✅ ¿Qué helpers puede reutilizar?
✅ ¿Cuál es el MVP mínimo funcional?
```

### Template de Análisis
```md
## MÓDULO: [Nombre]

### Objetivo MVP:
- [ ] Funcionalidad principal
- [ ] Funcionalidades secundarias

### Tablas Necesarias:
- [ ] Tabla principal: campos básicos
- [ ] Tabla relacionada: si aplica

### Relaciones:
- [ ] FK a tabla_existente
- [ ] Relación 1:N con módulo_x

### Helpers a Usar:
- [ ] formatPrecio(), formatFecha()
- [ ] badgeEstado(), iconoTipo()
- [ ] isAdmin(), userName()

### MVP Entregable:
- [ ] CRUD básico funcional
- [ ] Listado con DataTables
- [ ] Formularios validados
```

---

## 🗄️ FASE 2: BASE DE DATOS

### Workflow de Migraciones
```bash
# 1. Crear migración
php spark make:migration CreateModuloTable

# 2. Diseñar estructura (seguir convenciones)
# 3. Ejecutar migración
php spark migrate

# 4. Verificar en BD
php spark migrate:status

# 5. Actualizar schema de referencia
mysqldump nuevoanvar > app/Database/nuevoanvar.sql
```

### Convenciones de BD Obligatorias
```sql
-- ✅ Estructura estándar
id INT PRIMARY KEY AUTO_INCREMENT,
campo_principal VARCHAR(255) NOT NULL,
campo_opcional VARCHAR(100) NULL,
activo TINYINT(1) DEFAULT 1,
created_at DATETIME NULL,
updated_at DATETIME NULL

-- ✅ Foreign Keys siempre
FOREIGN KEY (tabla_id) REFERENCES tabla(id) ON DELETE CASCADE

-- ✅ Índices para performance
KEY idx_tabla_campo (campo_frecuente)
```

### Ejemplo Real Implementado
```sql
-- Migración de Proyectos (YA FUNCIONANDO)
proyectos (
    id, nombre, clave, empresa_id, descripcion, 
    direccion, longitud, latitud, color,
    created_at, updated_at
);

-- FK a empresas implementada
FOREIGN KEY (empresa_id) REFERENCES empresas(id) CASCADE
```

---

## 📊 FASE 3: BACKEND (Models + Entities)

### Estructura de Model MVP
```php
<?php
// app/Models/ModuloModel.php
namespace App\Models;

use CodeIgniter\Model;

class ModuloModel extends Model
{
    protected $table = 'modulos';
    protected $primaryKey = 'id';
    protected $returnType = 'App\Entities\Modulo';  // ✅ OBLIGATORIO
    protected $allowedFields = [
        'nombre', 'descripcion', 'campo_especifico'
    ];

    // ✅ Timestamps automáticos
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // ✅ Validaciones MVP básicas
    protected $validationRules = [
        'nombre' => 'required|max_length[255]'
    ];

    // ✅ Métodos de consulta con JOIN (si necesarios)
    public function getModulosConRelacion()
    {
        return $this->select('modulos.*, tabla_relacionada.nombre as relacion_nombre')
                    ->join('tabla_relacionada', 'tabla_relacionada.id = modulos.relacion_id')
                    ->findAll();
    }
}
```

### Estructura de Entity MVP
```php
<?php
// app/Entities/Modulo.php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Modulo extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ Métodos helper usando helpers del sistema
    public function getNombreFormateado(): string
    {
        return strtoupper($this->nombre ?? 'Sin nombre');
    }
    
    public function getFechaCreacion(): string
    {
        return formatFecha($this->created_at);
    }
    
    // ✅ Métodos de estado
    public function estaActivo(): bool
    {
        return $this->activo === true;
    }
}
```

---

## 🎮 FASE 4: CONTROLLER (CRUD RESTful)

### Estructura Estándar Implementada
```php
<?php
// app/Controllers/Admin/AdminModulosController.php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ModuloModel;

class AdminModulosController extends BaseController
{
    protected $moduloModel;

    public function __construct()
    {
        $this->moduloModel = new ModuloModel();
        // ✅ Cargar helpers automáticamente
        helper(['format', 'inmobiliario', 'auth']);
    }

    // ✅ GET /admin/modulos - Listado
    public function index()
    {
        $data = [
            'title' => 'Gestión de Módulos',
            'modulos' => $this->moduloModel->findAll()
        ];
        return view('admin/modulos/index', $data);
    }

    // ✅ GET /admin/modulos/create - Formulario crear
    public function create()
    {
        return view('admin/modulos/create', [
            'title' => 'Crear Módulo'
        ]);
    }

    // ✅ POST /admin/modulos - Procesar crear
    public function store()
    {
        // Validación MVP básica
        if (!$this->validate(['nombre' => 'required'])) {
            return redirect()->back()->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        if (!$this->moduloModel->save($data)) {
            return redirect()->back()->withInput()
                           ->with('error', 'Error al crear módulo');
        }

        return redirect()->to('/admin/modulos')
                       ->with('success', 'Módulo creado exitosamente');
    }

    // ✅ GET /admin/modulos/{id} - Ver detalles  
    public function show($id)
    {
        $modulo = $this->moduloModel->find($id);
        if (!$modulo) {
            return redirect()->to('/admin/modulos')
                           ->with('error', 'Módulo no encontrado');
        }

        return view('admin/modulos/show', [
            'title' => 'Ver Módulo',
            'modulo' => $modulo
        ]);
    }

    // ✅ GET /admin/modulos/{id}/edit - Formulario editar
    public function edit($id) { /* Implementación estándar */ }
    
    // ✅ PUT /admin/modulos/{id} - Procesar editar
    public function update($id) { /* Implementación estándar */ }
    
    // ✅ DELETE /admin/modulos/{id} - Eliminar
    public function delete($id) { /* Implementación estándar */ }
}
```

---

## 🖼️ FASE 5: FRONTEND (Vistas AdminLTE)

### Estructura de Vistas Estándar
```
app/Views/admin/modulos/
├── index.php     # Listado con DataTables
├── create.php    # Formulario crear
├── edit.php      # Formulario editar
└── show.php      # Vista detalles
```

### Template de Vista Index
```php
<?= $this->extend('layouts/admin') ?>
<?= $this->section('title') ?>Gestión de Módulos<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Lista de Módulos</h1>
                </div>
                <div class="col-sm-6">
                    <?= breadcrumb([
                        ['name' => 'Dashboard', 'url' => '/dashboard'],
                        ['name' => 'Módulos', 'url' => '']
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lista de Módulos</h3>
                    <div class="card-tools">
                        <?= botonAccion('/admin/modulos/create', 'Crear Módulo', 'primary', 'fas fa-plus') ?>
                    </div>
                </div>
                <div class="card-body">
                    <?= tablaResponsive('modulosTable') ?>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modulos as $modulo): ?>
                            <tr>
                                <td><?= $modulo->id ?></td>
                                <td><?= $modulo->nombre ?></td>
                                <td><?= $modulo->getFechaCreacion() ?></td>
                                <td><?= badgeEstado($modulo->activo ? 'activo' : 'inactivo') ?></td>
                                <td>
                                    <!-- Botones de acción usando helpers -->
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#modulosTable').DataTable({
        "responsive": true,
        "language": {"url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"}
    });
});
</script>
<?= $this->endSection() ?>
```

### Formularios Estándar MVP
```php
<!-- Formulario Create/Edit estándar -->
<form method="POST" action="<?= site_url($form_action) ?>">
    <?= csrf_field() ?>
    
    <div class="form-group">
        <label for="nombre">Nombre *</label>
        <input type="text" class="form-control" id="nombre" name="nombre" 
               value="<?= old('nombre', $modulo->nombre ?? '') ?>" required>
    </div>
    
    <div class="form-group">
        <label for="descripcion">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion">
            <?= old('descripcion', $modulo->descripcion ?? '') ?>
        </textarea>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="<?= site_url('/admin/modulos') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>
```

---

## 🛣️ FASE 6: CONFIGURACIÓN

### Rutas RESTful Estándar
```php
// app/Config/Routes.php

// ===== MÓDULO [NOMBRE] =====
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    $routes->group('modulos', function($routes) {
        $routes->get('/', 'Admin\AdminModulosController::index');
        $routes->get('create', 'Admin\AdminModulosController::create');
        $routes->post('/', 'Admin\AdminModulosController::store');
        $routes->get('(:num)', 'Admin\AdminModulosController::show/$1');
        $routes->get('(:num)/edit', 'Admin\AdminModulosController::edit/$1');
        $routes->put('(:num)', 'Admin\AdminModulosController::update/$1');
        $routes->delete('(:num)', 'Admin\AdminModulosController::delete/$1');
    });
});
```

### Sidebar Navigation
```php
// app/Views/layouts/partials/admin/sidebar.php

<!-- ===== GESTIÓN DE MÓDULOS ===== -->
<li class="nav-item <?= (strpos(current_url(), '/admin/modulos') !== false) ? 'menu-open' : '' ?>">
  <a href="#" class="nav-link <?= (strpos(current_url(), '/admin/modulos') !== false) ? 'active' : '' ?>">
    <i class="nav-icon fas fa-cubes"></i>
    <p>
      Gestión de Módulos
      <i class="right fas fa-angle-left"></i>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="<?= site_url('/admin/modulos') ?>" class="nav-link">
        <i class="far fa-circle nav-icon"></i>
        <p>Listar Módulos</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="<?= site_url('/admin/modulos/create') ?>" class="nav-link">
        <i class="far fa-circle nav-icon"></i>
        <p>Crear Módulo</p>
      </a>
    </li>
  </ul>
</li>
```

---

## 🧪 FASE 7: TESTING MVP

### Checklist de Testing Funcional
```bash
✅ Crear registro nuevo
    - Formulario carga correctamente
    - Validaciones funcionan
    - Se guarda en base de datos
    - Redirección a listado

✅ Listar registros
    - DataTable carga datos
    - Búsqueda funciona
    - Paginación opera
    - Helpers formatean correctamente

✅ Ver detalles
    - Muestra información completa
    - Enlaces funcionan
    - Helpers aplican formato

✅ Editar registro
    - Formulario pre-rellena datos
    - Actualización guarda cambios
    - Validaciones operan

✅ Eliminar registro
    - Confirmación aparece
    - Registro se elimina
    - Relaciones CASCADE funcionan
```

### Testing de Performance Básico
```bash
# Verificar consultas N+1
# En desarrollo, activar query debugging

# Verificar carga de página
# Tiempo < 2 segundos para listados

# Verificar memoria
# Uso < 50MB para operaciones normales
```

---

## 🚀 FASE 8: DEPLOY & OPTIMIZACIÓN

### Deploy MVP
```bash
# 1. Migrar producción
php spark migrate

# 2. Verificar funcionalidad básica
# 3. Backup de BD
mysqldump nuevoanvar > backup_pre_modulo.sql

# 4. Monitorear logs
tail -f writable/logs/log-*.php
```

### Optimizaciones Post-MVP
```bash
✅ Después del MVP exitoso:
    - Agregar validaciones robustas
    - Implementar CSRF
    - Optimizar consultas
    - Agregar índices específicos
    - Implementar cacheo
    - Testing automatizado
```

---

## 🔧 HERRAMIENTAS DE DESARROLLO

### Comandos Diarios
```bash
# Desarrollo local
php spark cache:clear          # Limpiar cache
php spark routes              # Ver rutas
php spark migrate:status      # Estado BD
composer dump-autoload       # Recargar autoload

# Debug
tail -f writable/logs/log-*.php    # Ver logs en tiempo real
php spark anvar:test-config        # Test configuración sistema
```

### URLs de Debug (Solo Development)
```bash
http://nuevoanvar.test/debug/conexion    # Test BD
http://nuevoanvar.test/debug/auth        # Test autenticación
```

### IDEs y Extensiones Recomendadas
```bash
✅ VS Code con:
    - PHP Intelephense
    - CodeIgniter 4 Snippets
    - Laravel Blade Syntax
    - GitLens
    - Database Client

✅ Configuración PHP:
    - PHP 8.1+
    - Composer actualizado
    - Xdebug para debugging
```

---

## 📋 CHECKLIST COMPLETO NUEVO MÓDULO

### ✅ Pre-desarrollo (15 min)
- [ ] Análisis de requerimientos completado
- [ ] Tablas y relaciones diseñadas
- [ ] Helpers identificados para reutilizar

### ✅ Backend (75 min)
- [ ] Migración creada y ejecutada
- [ ] Model con validaciones implementado
- [ ] Entity con helpers implementada
- [ ] Controller CRUD completo funcionando

### ✅ Frontend (90 min)
- [ ] Vista index con DataTables operativa
- [ ] Vista create con formulario validado
- [ ] Vista edit con pre-llenado
- [ ] Vista show con información detallada

### ✅ Configuración (15 min)
- [ ] Rutas RESTful agregadas
- [ ] Sidebar actualizado con navegación
- [ ] Filtros de autorización aplicados

### ✅ Testing (30 min)
- [ ] CRUD completo probado
- [ ] Validaciones verificadas
- [ ] Helpers funcionando correctamente
- [ ] Performance básico aceptable

### ✅ Documentación (15 min)
- [ ] CLAUDE.md actualizado si necesario
- [ ] DATABASE.md actualizado con nuevas tablas
- [ ] README del módulo (opcional)

---

## 🎯 MEJORES PRÁCTICAS IMPLEMENTADAS

### Código Limpio
```php
// ✅ Buenos nombres descriptivos
public function obtenerProyectosActivos(): array

// ✅ Métodos cortos y específicos
public function formatearPrecio(float $precio): string

// ✅ Validaciones claras
'nombre' => 'required|max_length[255]'

// ✅ Usar helpers para DRY
return formatPrecio($this->precio);
```

### Organización de Archivos
```bash
✅ Convenciones seguidas:
    - Models en app/Models/
    - Entities en app/Entities/
    - Controllers en app/Controllers/Admin/
    - Views en app/Views/admin/modulo/
    - Routes organizadas por módulo
    - Helpers agrupados por funcionalidad
```

### Gestión de Errores
```php
// ✅ Manejo de errores estándar
try {
    $result = $this->model->save($data);
} catch (\Exception $e) {
    log_message('error', 'Error módulo: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Error al guardar');
}
```

---

## 🔄 PROCESO DE MEJORA CONTINUA

### Review Post-MVP
```bash
1. ¿El módulo cumple su objetivo?
2. ¿La UX es intuitiva?
3. ¿Reutilizamos helpers apropiadamente?
4. ¿Hay código duplicado?
5. ¿Performance es aceptable?
```

### Refactoring Programado
```bash
✅ Cada 3 módulos implementados:
    - Revisar helpers para consolidar
    - Optimizar consultas comunes
    - Actualizar documentación
    - Evaluar patrones emergentes
```

### Métricas de Calidad
```bash
✅ Objetivos por módulo:
    - Tiempo desarrollo: < 6 horas
    - Líneas código: < 500 por controller
    - Helpers reutilizados: > 3 por módulo
    - Testing manual: 100% CRUD
```

---

## 🚀 COMANDO CLAUDE CODE PARA MÓDULO COMPLETO

```bash
claude "
Crea módulo completo [NOMBRE_MÓDULO] para sistema NuevoAnvar siguiendo DEVELOPMENT.md:

FASE 1 - BASE DE DATOS:
- Migración con campos básicos + FK apropiadas
- Ejecutar y verificar

FASE 2 - BACKEND:
- Model con returnType Entity + validaciones MVP
- Entity con helpers del sistema (formatPrecio, formatFecha, etc.)

FASE 3 - CONTROLLER:
- AdminController con CRUD RESTful completo
- Usar helpers (auth, format, inmobiliario)
- Validaciones básicas MVP

FASE 4 - FRONTEND:
- Vista index con DataTables responsive
- Vista create/edit con formularios AdminLTE
- Vista show con información detallada

FASE 5 - CONFIGURACIÓN:
- Rutas RESTful estándar del proyecto
- Sidebar con navegación activa

SEGUIR WORKFLOW EXACTO del documento DEVELOPMENT.md
TODO en español mexicano, MVP funcional
"
```

---

**¡Este workflow está comprobado y funcionando en el proyecto! 🚀**