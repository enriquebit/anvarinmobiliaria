# ANVAR Inmobiliaria - Sistema de Manzanas (Legacy Analysis)

## 📊 Análisis Completo del Sistema Legacy de Manzanas

### 1. **ESTRUCTURA DE ARCHIVOS LEGACY**

#### Archivos Principales
- `administracion/manzanas.php` - Vista principal (listado)
- `administracion/manzanas_agregar.php` - Formulario de creación
- `administracion/manzanas_modificar.php` - Formulario de edición
- `administracion/js/funciones/manzanas.js` - Lógica JavaScript/AJAX

#### Archivos de Funciones
- `administracion/comandos/funciones.php` - Funciones AJAX principales

---

### 2. **ESTRUCTURA DE BASE DE DATOS LEGACY**

#### Tabla: `tb_manzanas`
```sql
CREATE TABLE `tb_manzanas` (
  `IdManzana` int(11) NOT NULL,
  `Nombre` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Clave` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Descripcion` text COLLATE utf8_unicode_ci,
  `Proyecto` int(11) DEFAULT '0',
  `NProyecto` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Longitud` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Latitud` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Fecha` date DEFAULT NULL,
  `Hora` time DEFAULT NULL,
  `Usuario` int(11) DEFAULT NULL,
  `NUsuario` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IP` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Estatus` int(11) DEFAULT '0',
  `Empresa` int(11) DEFAULT '0',
  `NEmpresa` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Color` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Division` int(11) DEFAULT '0',
  `NDivision` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

#### Campos Principales
- **IdManzana**: Primary Key
- **Nombre**: Nombre de la manzana (ej: "1", "2", "3")
- **Clave**: Clave compuesta (ej: "1-1", "1-2") = {Proyecto.Clave}-{Manzana.Nombre}
- **Descripcion**: Descripción opcional
- **Proyecto**: FK a tb_proyectos
- **NProyecto**: Nombre del proyecto (desnormalizado)
- **Longitud/Latitud**: Coordenadas geográficas
- **Empresa**: FK a tb_empresas
- **NEmpresa**: Nombre empresa (desnormalizado)
- **Color**: Color heredado del proyecto
- **Estatus**: 1=Activo, 2=Eliminado

---

### 3. **RELACIONES CON OTRAS TABLAS**

#### Con Proyectos (`tb_proyectos`)
- Una manzana pertenece a un proyecto
- Hereda la clave del proyecto para formar su propia clave
- Hereda el color del proyecto

#### Con Lotes (`tb_lotes`)
- Una manzana puede tener múltiples lotes
- Los lotes referencian la manzana mediante `Manzana` (IdManzana)
- Campo `NManzana` en lotes se actualiza cuando cambia nombre de manzana

#### Con Empresas (`tb_empresas`)
- Una manzana pertenece a una empresa
- Validación de permisos por empresa según usuario

---

### 4. **OPERACIONES CRUD**

#### 4.1 CREATE (Función 13)
**Archivo**: `funciones.php` - función 13
**JavaScript**: `btn_guardar_manzana`

**Validaciones**:
- Campo `nombre` requerido
- Campo `proyecto` requerido  
- Campo `empresa` requerido
- Campo `fecha` requerido
- Validación de permisos por empresa
- Validación de nombre único por proyecto

**Proceso**:
1. Validar sesión y permisos
2. Verificar que no existe manzana con mismo nombre en el proyecto
3. Obtener datos del proyecto (nombre, clave, color)
4. Obtener nombre de empresa
5. Insertar registro con clave compuesta: `{proyecto.clave}-{manzana.nombre}`
6. Registrar en historial (Movimiento 78)

#### 4.2 READ (Lista principal)
**Consulta Principal**:
```sql
SELECT IdManzana,Clave,Nombre,Descripcion,DATE_FORMAT(Fecha,'%d-%m-%Y') AS vFecha,
       NProyecto,Proyecto,NEmpresa,Color 
FROM tb_manzanas 
WHERE Estatus=1 {filtros_empresa}
```

**Métricas de Lotes** (por cada manzana):
```sql
SELECT 
  (SELECT COUNT(IdLote) FROM tb_lotes WHERE Estatus=1 AND Proyecto=? AND Manzana=?) AS tConteo,
  (SELECT COUNT(IdLote) FROM tb_lotes WHERE Estatus=1 AND Estado=0 AND Proyecto=? AND Manzana=?) AS dConteo,
  (SELECT COUNT(IdLote) FROM tb_lotes WHERE Estatus=1 AND Estado=2 AND Proyecto=? AND Manzana=?) AS vConteo
```

#### 4.3 UPDATE (Función 14)
**Archivo**: `funciones.php` - función 14
**JavaScript**: `btn_modificar_manzana`

**Validaciones**:
- Mismas validaciones que CREATE
- Validación de nombre único excluyendo registro actual
- Verificar permisos por empresa

**Proceso**:
1. Desencriptar ID de manzana
2. Validar datos y permisos
3. Verificar nombre único (excluyendo registro actual)
4. Actualizar registro con nueva clave compuesta
5. Actualizar campo `NManzana` en tabla `tb_lotes`
6. Registrar en historial (Movimiento 79)

#### 4.4 DELETE (Función 15)
**Archivo**: `funciones.php` - función 15
**JavaScript**: `btn_eliminar_manzana`

**Validaciones**:
- Verificar que no existan lotes asociados
- Validar permisos

**Proceso**:
1. Desencriptar ID
2. Verificar que no hay lotes: `SELECT Manzana FROM tb_lotes WHERE Manzana=? AND Estatus=1`
3. Si hay lotes, error "nox"
4. Actualizar estatus a 2 (eliminado lógico)
5. Registrar en historial (Movimiento 80)

---

### 5. **FUNCIONES AJAX DE BÚSQUEDA**

#### 5.1 Búsqueda General (Función 225)
**Parámetros**:
- `empresa`: Filtro por empresa
- `proyecto`: Filtro por proyecto
- `todo`: Búsqueda en nombre o clave

**Lógica**:
- Validar permisos por empresa
- Construir filtros SQL dinámicos
- Calcular métricas de lotes por manzana
- Registrar búsqueda en historial (Movimiento 111)

#### 5.2 Cargar Proyectos por Empresa (Función 226)
**Propósito**: Llenar dropdown de proyectos al seleccionar empresa
**Consulta**: `SELECT IdProyecto,Nombre FROM tb_proyectos WHERE Estatus=1 AND Empresa=?`

#### 5.3 Cargar Proyectos para Formularios (Función 227)
**Propósito**: Similar a 226 pero para formularios de agregar/modificar

---

### 6. **SISTEMA DE PERMISOS**

#### Validaciones de Acceso
1. **Sesión válida**: `$_SESSION["ANV_U_LOG_L"] == "SI"`
2. **Permisos de manzanas**: `$_SESSION["ANV_U_LOG_P_MANZANAS"] == 1`
3. **Tipos de usuario bloqueados**: Tipo 6 no puede realizar operaciones
4. **Permisos por empresa**: Usuarios tipo != 3 deben tener empresa asignada

#### Filtros de Empresa
```php
// Para administrador general (tipo 3)
$_SESSION["ANV_U_LOG_P_EMPRESAS"] = "" // Ve todas

// Para otros usuarios
$_SESSION["ANV_U_LOG_P_EMPRESAS"] = " AND Empresa IN (1,2,3)" // Solo empresas asignadas
```

---

### 7. **INTERFACE DE USUARIO**

#### Vista Principal (manzanas.php)
**Filtros**:
- Dropdown Empresa (con Select2)
- Dropdown Proyecto (se llena dinámicamente)
- Campo de búsqueda general

**Tabla de Resultados**:
- Nombre, Clave, Descripción, Fecha
- Empresa, Proyecto
- Lotes (Total, Vendidos, Disponibles)
- Barra de progreso de ventas
- Acciones: Editar, Eliminar

#### Formularios (agregar/modificar)
**Campos**:
- Nombre (requerido)
- Empresa (dropdown, requerido)
- Proyecto (dropdown dependiente, requerido) 
- Fecha (datepicker, requerido)
- Descripción (opcional)
- Longitud/Latitud (opcional)

---

### 8. **CARACTERÍSTICAS TÉCNICAS**

#### JavaScript
- jQuery para AJAX
- Select2 para dropdowns
- SweetAlert para confirmaciones
- DataTables para la tabla
- Validación en frontend y backend

#### Seguridad
- `htmlspecialchars()` en todos los inputs
- Validación de sesión en cada operación
- Encriptación de IDs en URLs
- Validaciones de permisos por empresa

#### Auditoria
- Registro en `tb_historial_movimientos`
- Captura de IP, usuario, fecha/hora
- Movimientos: 77=Ver, 78=Crear, 79=Modificar, 80=Eliminar, 111=Buscar

---

### 9. **PROBLEMAS IDENTIFICADOS**

#### Arquitectura
1. **Desnormalización excesiva**: Campos como `NProyecto`, `NEmpresa`
2. **Lógica en PHP plano**: No usa MVC
3. **SQL injection potential**: Aunque usa `htmlspecialchars()`
4. **Mixing HTML/PHP**: Dificulta mantenimiento

#### Base de Datos
1. **Campos de auditoria redundantes**: `Usuario`, `NUsuario`, `IP`, `Fecha`, `Hora`
2. **Tipos inconsistentes**: `Longitud`/`Latitud` como VARCHAR
3. **Sin foreign keys**: Relaciones no enforced
4. **Soft delete**: Campo `Estatus` para eliminación lógica

#### UI/UX
1. **No responsive**: Diseño fijo
2. **Validaciones básicas**: Falta validación avanzada
3. **UX inconsistente**: Patrones no estandarizados

---

### 10. **RECOMENDACIONES PARA MIGRACIÓN A CI4**

#### 10.1 Arquitectura Propuesta
```
app/Controllers/Admin/ManzanasController.php
app/Models/ManzanaModel.php
app/Entities/Manzana.php
app/Views/admin/manzanas/
├── index.php
├── create.php
└── edit.php
```

#### 10.2 Estructura de Base de Datos CI4
```sql
CREATE TABLE manzanas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    clave VARCHAR(255) NOT NULL,
    descripcion TEXT,
    proyecto_id INT NOT NULL,
    empresa_id INT NOT NULL,
    longitud DECIMAL(10, 8),
    latitud DECIMAL(11, 8),
    fecha DATE NOT NULL,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    UNIQUE KEY unique_nombre_proyecto (nombre, proyecto_id)
);
```

#### 10.3 Entidad Manzana
```php
<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Manzana extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'fecha'];
    protected $casts = [
        'longitud' => 'decimal',
        'latitud' => 'decimal',
        'empresa_id' => 'integer',
        'proyecto_id' => 'integer'
    ];
    
    // Auto-generate clave based on proyecto
    public function setClave()
    {
        if ($this->proyecto_id && $this->nombre) {
            $proyectoModel = new \App\Models\ProyectoModel();
            $proyecto = $proyectoModel->find($this->proyecto_id);
            $this->attributes['clave'] = $proyecto->clave . '-' . $this->nombre;
        }
    }
}
```

#### 10.4 Controlador con Filtros
```php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ManzanaModel;

class ManzanasController extends BaseController
{
    protected $manzanaModel;
    
    public function __construct()
    {
        $this->manzanaModel = new ManzanaModel();
    }
    
    public function index()
    {
        // Aplicar filtros de empresa según permisos
        $empresas = $this->getEmpresasPermitidas();
        
        $data = [
            'manzanas' => $this->manzanaModel->getManzanasWithMetrics($empresas),
            'empresas' => $empresas
        ];
        
        return view('admin/manzanas/index', $data);
    }
}
```

#### 10.5 Validaciones
```php
// app/Config/Validation.php
public $manzana = [
    'nombre' => 'required|max_length[255]|is_unique_combined[manzanas.nombre.proyecto_id,{proyecto_id}]',
    'proyecto_id' => 'required|integer|is_not_unique[proyectos.id]',
    'empresa_id' => 'required|integer|is_not_unique[empresas.id]',
    'fecha' => 'required|valid_date',
    'longitud' => 'permit_empty|decimal',
    'latitud' => 'permit_empty|decimal'
];
```

---

### 11. **ESTRATEGIA DE MIGRACIÓN**

#### Fase 1: Preparación
1. Crear migrations para nueva estructura
2. Implementar seeders con datos legacy
3. Crear entidades y modelos base

#### Fase 2: Backend
1. Implementar controladores CRUD
2. Migrar lógica de validaciones
3. Implementar sistema de permisos

#### Fase 3: Frontend  
1. Crear vistas con framework moderno
2. Implementar búsqueda y filtros
3. AJAX para interacciones dinámicas

#### Fase 4: Testing
1. Unit tests para modelos
2. Feature tests para controladores
3. Testing de permisos y validaciones

#### Fase 5: Deployment
1. Migración de datos legacy
2. Actualización de referencias en lotes
3. Testing en producción

---

### 12. **ARCHIVOS DE MIGRACIÓN PROPUESTOS**

#### Migration: CreateManzanasTable
```php
<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManzanasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'clave' => [
                'type' => 'VARCHAR', 
                'constraint' => 255,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'proyecto_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'empresa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'longitud' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
            ],
            'latitud' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8', 
                'null' => true,
            ],
            'fecha' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['nombre', 'proyecto_id'], false, true); // unique
        $this->forge->addForeignKey('proyecto_id', 'proyectos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('manzanas');
    }
    
    public function down()
    {
        $this->forge->dropTable('manzanas');
    }
}
```

---

### 13. **CONCLUSIONES**

El sistema legacy de manzanas es funcional pero presenta múltiples oportunidades de mejora:

1. **Arquitectura**: Migrar a patrón MVC con CI4
2. **Base de datos**: Normalizar y agregar constraints
3. **Seguridad**: Implementar filters y validation robusta  
4. **UI/UX**: Modernizar con responsive design
5. **Mantenibilidad**: Separar lógica de presentación

La migración debe ser incremental, manteniendo compatibilidad con el sistema de lotes existente mientras se mejora la arquitectura general.

**Prioridad**: Alta - Las manzanas son entidad central del sistema inmobiliario.
**Complejidad**: Media - Relaciones simples pero lógica de negocio específica.
**Impacto**: Alto - Afecta lotes, ventas y reportes.