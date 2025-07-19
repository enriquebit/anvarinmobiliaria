# HELPERS.md - Sistema de Helpers NuevoAnvar

Sistema completo de helpers para reutilización de código (principio DRY) en el proyecto inmobiliario.

## 🔧 FILOSOFÍA DE HELPERS

Los helpers son **funciones globales reutilizables** que implementan el principio **DRY (Don't Repeat Yourself)**. En nuestro sistema inmobiliario, usamos helpers para:

- **Formateo de datos** (precios, fechas, teléfonos)
- **Validaciones comunes** (CURP, RFC, teléfonos mexicanos)
- **Utilidades de negocio** (cálculos inmobiliarios, comisiones)
- **Funciones de vista** (badges de estado, iconos por tipo)

## 📁 ESTRUCTURA DE HELPERS

```
app/Helpers/
├── auth_helper.php          # ✅ Implementado - Funciones de autenticación
├── format_helper.php        # ✅ Implementado - Formateo de datos
├── inmobiliario_helper.php  # 🚧 En desarrollo - Lógica específica del negocio
├── validation_helper.php    # 🚧 Pendiente - Validaciones personalizadas
└── ui_helper.php           # 🚧 Pendiente - Elementos de interfaz
```

## 🔄 CARGA DE HELPERS

### Carga Automática (BaseController)
```php
// app/Controllers/BaseController.php
class BaseController extends Controller
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // ✅ Cargar helpers automáticamente en todo el sistema
        helper(['auth', 'format', 'inmobiliario']);
    }
}
```

### Carga Manual
```php
// En cualquier controlador o vista
helper('auth');          // Cargar helper específico
helper(['auth', 'format']); // Cargar múltiples helpers
```

## 📋 HELPERS IMPLEMENTADOS

### 🔐 auth_helper.php (Autenticación)
```php
<?php
// ✅ YA IMPLEMENTADO

/**
 * Obtener nombre del usuario actual
 */
function userName(): string
{
    $user = auth()->user();
    return $user ? $user->getEmail() : 'Invitado';
}

/**
 * Obtener rol del usuario actual
 */
function userRole(): string
{
    $user = auth()->user();
    if (!$user) return 'guest';
    
    return $user->getGroups()[0] ?? 'cliente';
}

/**
 * Verificar si usuario tiene permiso específico
 */
function hasPermission(string $permission): bool
{
    return auth()->user()->can($permission);
}

/**
 * Verificar si usuario está en grupo específico
 */
function isAdmin(): bool
{
    return auth()->user()->inGroup('admin', 'superadmin');
}

function isCliente(): bool
{
    return auth()->user()->inGroup('cliente');
}
```

### 📊 format_helper.php (Formateo de Datos)
```php
<?php
// ✅ IMPLEMENTADO

/**
 * Formatear precio en pesos mexicanos
 */
function formatPrecio(float $precio): string
{
    return '$' . number_format($precio, 2, '.', ',');
}

/**
 * Formatear teléfono mexicano
 */
function formatTelefono(string $telefono): string
{
    $clean = preg_replace('/[^0-9]/', '', $telefono);
    
    if (strlen($clean) === 10) {
        return '(' . substr($clean, 0, 2) . ') ' . substr($clean, 2, 4) . '-' . substr($clean, 6);
    }
    
    return $telefono;
}

/**
 * Formatear fecha para mostrar en vistas
 */
function formatFecha($fecha, string $formato = 'd/m/Y'): string
{
    if (is_string($fecha)) {
        $fecha = new DateTime($fecha);
    }
    
    return $fecha instanceof DateTime ? $fecha->format($formato) : '';
}

/**
 * Formatear superficie en m²
 */
function formatSuperficie(float $superficie): string
{
    return number_format($superficie, 2) . ' m²';
}

/**
 * Generar color hexadecimal aleatorio
 */
function randomColor(): string
{
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}
```

### 🏠 inmobiliario_helper.php (Lógica de Negocio)
```php
<?php
// 🚧 EN DESARROLLO - Para el sistema inmobiliario

/**
 * Calcular comisión de asesor
 */
function calcularComision(float $precioVenta, float $porcentaje = 3.0): float
{
    return $precioVenta * ($porcentaje / 100);
}

/**
 * Generar clave única de propiedad
 */
function generarClavePropiedad(string $proyectoClave, int $lote, string $manzana = ''): string
{
    $manzanaPart = $manzana ? "-MZ{$manzana}" : '';
    return strtoupper($proyectoClave . $manzanaPart . "-L" . str_pad($lote, 3, '0', STR_PAD_LEFT));
}

/**
 * Obtener badge HTML para estado de propiedad
 */
function badgeEstadoPropiedad(string $estado): string
{
    $badges = [
        'disponible' => '<span class="badge badge-success">Disponible</span>',
        'apartado' => '<span class="badge badge-warning">Apartado</span>',
        'vendido' => '<span class="badge badge-danger">Vendido</span>',
        'reservado' => '<span class="badge badge-info">Reservado</span>'
    ];
    
    return $badges[$estado] ?? '<span class="badge badge-secondary">Desconocido</span>';
}

/**
 * Obtener icono para tipo de propiedad
 */
function iconoTipoPropiedad(string $tipo): string
{
    $iconos = [
        'lote' => '<i class="fas fa-map"></i>',
        'casa' => '<i class="fas fa-home"></i>',
        'departamento' => '<i class="fas fa-building"></i>',
        'local' => '<i class="fas fa-store"></i>'
    ];
    
    return $iconos[$tipo] ?? '<i class="fas fa-question"></i>';
}

/**
 * Calcular mensualidad de plan de pagos
 */
function calcularMensualidad(float $montoTotal, int $meses, float $interesAnual = 0): float
{
    if ($interesAnual == 0) {
        return $montoTotal / $meses;
    }
    
    $interesMensual = $interesAnual / 12 / 100;
    return $montoTotal * ($interesMensual * pow(1 + $interesMensual, $meses)) / (pow(1 + $interesMensual, $meses) - 1);
}

/**
 * Validar RFC mexicano
 */
function validarRFC(string $rfc): bool
{
    $pattern = '/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/';
    return preg_match($pattern, strtoupper($rfc));
}

/**
 * Validar CURP mexicana
 */
function validarCURP(string $curp): bool
{
    $pattern = '/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$/';
    return preg_match($pattern, strtoupper($curp));
}
```

## 🚧 HELPERS PENDIENTES POR IMPLEMENTAR

### 🔍 validation_helper.php (Validaciones Personalizadas)
```php
<?php
// 🚧 PENDIENTE - Validaciones específicas del negocio

/**
 * Validar teléfono mexicano (10 dígitos)
 */
function validarTelefonoMexicano(string $telefono): bool
{
    $clean = preg_replace('/[^0-9]/', '', $telefono);
    return strlen($clean) === 10 && preg_match('/^[0-9]{10}$/', $clean);
}

/**
 * Validar código postal mexicano
 */
function validarCodigoPostal(string $cp): bool
{
    return preg_match('/^[0-9]{5}$/', $cp);
}

/**
 * Validar coordenadas GPS
 */
function validarCoordenadas(string $latitud, string $longitud): bool
{
    return is_numeric($latitud) && is_numeric($longitud) 
           && $latitud >= -90 && $latitud <= 90
           && $longitud >= -180 && $longitud <= 180;
}

/**
 * Sanitizar número para base de datos
 */
function sanitizarNumero(string $input): float
{
    return (float) preg_replace('/[^0-9.]/', '', $input);
}
```

### 🎨 ui_helper.php (Elementos de Interfaz)
```php
<?php
// 🚧 PENDIENTE - Elementos reutilizables de UI

/**
 * Generar breadcrumb HTML
 */
function breadcrumb(array $items): string
{
    $html = '<ol class="breadcrumb float-sm-right">';
    
    foreach ($items as $item) {
        if (isset($item['url'])) {
            $html .= '<li class="breadcrumb-item"><a href="' . $item['url'] . '">' . $item['name'] . '</a></li>';
        } else {
            $html .= '<li class="breadcrumb-item active">' . $item['name'] . '</li>';
        }
    }
    
    $html .= '</ol>';
    return $html;
}

/**
 * Generar botón de acción estándar
 */
function botonAccion(string $url, string $texto, string $tipo = 'primary', string $icono = ''): string
{
    $iconoHtml = $icono ? "<i class='$icono'></i> " : '';
    return "<a href='$url' class='btn btn-$tipo'>$iconoHtml$texto</a>";
}

/**
 * Generar tabla responsive estándar
 */
function tablaResponsive(string $id = 'dataTable'): string
{
    return "<div class='table-responsive'><table id='$id' class='table table-bordered table-striped'>";
}

/**
 * Generar alert de notificación
 */
function alertNotificacion(string $mensaje, string $tipo = 'info'): string
{
    return "<div class='alert alert-$tipo alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                $mensaje
            </div>";
}
```

## 📚 CÓMO CREAR UN NUEVO HELPER

### 1. Crear el archivo helper
```bash
# Crear archivo en app/Helpers/
touch app/Helpers/mi_nuevo_helper.php
```

### 2. Estructura básica del helper
```php
<?php
// app/Helpers/mi_nuevo_helper.php

if (!function_exists('miFuncion')) {
    /**
     * Descripción de lo que hace la función
     * 
     * @param string $parametro Descripción del parámetro
     * @return string Descripción del retorno
     */
    function miFuncion(string $parametro): string
    {
        // Lógica de la función
        return "Resultado: " . $parametro;
    }
}

if (!function_exists('otraFuncion')) {
    function otraFuncion(array $datos): array
    {
        // Otra función del mismo helper
        return array_map('strtoupper', $datos);
    }
}
```

### 3. Cargar el helper
```php
// En BaseController (carga automática)
helper(['auth', 'format', 'mi_nuevo']);

// O carga manual cuando se necesite
helper('mi_nuevo');
```

### 4. Usar en controladores/vistas
```php
// En cualquier controlador
$resultado = miFuncion('datos de prueba');

// En cualquier vista
echo miFuncion($variable);
```

## 🎯 MEJORES PRÁCTICAS PARA HELPERS

### ✅ SÍ Hacer
- **Funciones puras**: Sin efectos secundarios
- **Validar parámetros**: Verificar tipos y valores
- **Documentar con PHPDoc**: Parámetros y retorno
- **Usar `if (!function_exists())`**: Evitar redefiniciones
- **Nombrar descriptivamente**: `formatPrecio()` mejor que `format()`
- **Agrupar por funcionalidad**: Un helper por dominio

### ❌ NO Hacer
- **Lógica compleja de negocio**: Eso va en entidades/modelos
- **Funciones que modifican estado**: Helpers deben ser funciones puras
- **Dependencias complejas**: Mantener helpers simples
- **Mezclar responsabilidades**: Un helper = una responsabilidad

## 📋 CHECKLIST PARA NUEVO HELPER

### ✅ Antes de crear
- [ ] ¿La función se usará en múltiples lugares?
- [ ] ¿Es lógica de presentación/formateo/validación?
- [ ] ¿Puede ser una función pura (sin efectos secundarios)?
- [ ] ¿No existe ya una función similar?

### ✅ Al implementar
- [ ] Usar `if (!function_exists())` wrapper
- [ ] Documentar con PHPDoc completo
- [ ] Validar parámetros de entrada
- [ ] Manejar casos edge y errores
- [ ] Escribir nombre descriptivo

### ✅ Después de crear
- [ ] Cargar en BaseController si es de uso general
- [ ] Actualizar documentación con ejemplos
- [ ] Probar en diferentes contextos
- [ ] Considerar escribir tests unitarios

## 💡 EJEMPLOS DE USO

### En Controladores
```php
<?php
class ProyectosController extends BaseController
{
    public function index()
    {
        helper(['format', 'inmobiliario']); // Carga manual si no está en BaseController
        
        $proyectos = model('ProyectoModel')->findAll();
        
        return view('admin/proyectos/index', ['proyectos' => $proyectos]);
    }
    
    public function store()
    {
        $data = $this->request->getPost();
        
        // ✅ Usar helper para generar clave
        $data['clave'] = generarClavePropiedad($data['nombre'], rand(1, 999));
        
        model('ProyectoModel')->save($data);
        return redirect()->to('/admin/proyectos');
    }
}
```

### En Vistas
```php
<!-- ✅ Vista usando helpers para formateo -->
<div class="card">
    <div class="card-header">
        <h3><?= $proyecto->nombre ?></h3>
        <?= badgeEstadoPropiedad($proyecto->estado) ?>
    </div>
    <div class="card-body">
        <p><strong>Precio:</strong> <?= formatPrecio($proyecto->precio) ?></p>
        <p><strong>Superficie:</strong> <?= formatSuperficie($proyecto->superficie) ?></p>
        <p><strong>Teléfono:</strong> <?= formatTelefono($proyecto->telefono) ?></p>
        <p><strong>Fecha:</strong> <?= formatFecha($proyecto->created_at) ?></p>
        
        <?php if (isAdmin()): ?>
            <a href="/admin/proyectos/<?= $proyecto->id ?>/edit" class="btn btn-primary">
                <?= iconoTipoPropiedad('proyecto') ?> Editar
            </a>
        <?php endif; ?>
    </div>
</div>
```

### En Entidades
```php
<?php
class Proyecto extends BaseEntity
{
    public function getPrecioFormateado(): string
    {
        // ✅ Usar helper en lugar de lógica duplicada
        return formatPrecio($this->precio ?? 0);
    }
    
    public function getTelefonoFormateado(): string
    {
        return formatTelefono($this->telefono ?? '');
    }
    
    public function getEstadoBadge(): string
    {
        return badgeEstadoPropiedad($this->estado ?? 'disponible');
    }
}
```

## 🚀 COMANDO CLAUDE CODE PARA HELPERS

```bash
claude "
Crea helper [nombre]_helper.php para proyecto NuevoAnvar:
- Funciones específicas para [dominio]
- Usar if (!function_exists()) wrapper
- PHPDoc completo en español
- Validación de parámetros
- Principio DRY para reutilización
- Ejemplos de uso en comentarios
- Funciones puras sin efectos secundarios
- Todo en español mexicano
"
```

---

**Los helpers son la clave para mantener código limpio y reutilizable en todo el sistema inmobiliario! 🔧**