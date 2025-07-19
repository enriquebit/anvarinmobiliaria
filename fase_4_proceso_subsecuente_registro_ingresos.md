# FASE 4: Registro de Ingresos/Flujos

## Objetivo
Implementar el sistema completo de registro de ingresos clasificados por tipo, siguiendo el flujo del sistema legacy donde cada pago genera un registro de ingreso con clasificación específica.

## Análisis del Sistema Legacy

### Tipos de Ingresos Legacy:
- **Enganche=1**: Pago del enganche inicial
- **Mensualidad=1**: Pago de mensualidad regular
- **AbonoCapital=1**: Abono adicional a capital
- **Comision=1**: Pago de comisión a vendedor

### Formas de Pago Legacy:
- `Efectivo`: Monto en efectivo
- `Transferencia`: Monto por transferencia
- `Cheque`: Monto por cheque
- `Tarjeta`: Monto por tarjeta

### Tabla Legacy: `tb_ingresos`
- Total de registros: 6,000
- Enganches: 360 registros
- Mensualidades: 3,950 registros
- Abonos capital: 40 registros
- Comisiones: 416 registros

## Estado Actual del Sistema

### Servicios Existentes:
- ✅ `PagosIngresoService.php` - Servicio consolidado (post-Fase 1)
- ✅ `VentasService.php` - Servicio principal de ventas
- ✅ `ComisionService.php` - Manejo de comisiones

### Entidades Existentes:
- ✅ `VentaIngreso.php` - Entity para ingresos
- ✅ `VentaPago.php` - Entity para pagos
- ✅ `FormaPago.php` - Entity para formas de pago

### Funcionalidad Faltante:
- ❌ Clasificación automática de ingresos por tipo
- ❌ Integración completa con cobranza (Fase 3)
- ❌ Manejo de ingresos mixtos (múltiples formas de pago)
- ❌ Conciliación automática con plan de pagos

## SUBTAREAS FASE 4

### 4.1 Extender Sistema de Clasificación de Ingresos
**Prioridad**: ALTA
**Tiempo estimado**: 2 horas

**Acciones**:
- Extender `VentaIngreso` entity con tipos de ingreso
- Implementar clasificación automática
- Agregar validaciones por tipo

**Código a implementar**:
```php
// En app/Entities/VentaIngreso.php
public const TIPO_ENGANCHE = 'enganche';
public const TIPO_MENSUALIDAD = 'mensualidad';
public const TIPO_ABONO_CAPITAL = 'abono_capital';
public const TIPO_COMISION = 'comision';
public const TIPO_APARTADO = 'apartado';

public function esEnganche(): bool
{
    return $this->tipo_ingreso === self::TIPO_ENGANCHE;
}

public function esMensualidad(): bool
{
    return $this->tipo_ingreso === self::TIPO_MENSUALIDAD;
}

public function esAbonoCapital(): bool
{
    return $this->tipo_ingreso === self::TIPO_ABONO_CAPITAL;
}

public function esComision(): bool
{
    return $this->tipo_ingreso === self::TIPO_COMISION;
}
```

### 4.2 Crear Servicio de Clasificación Automática
**Prioridad**: ALTA
**Tiempo estimado**: 2.5 horas

**Acciones**:
- Crear `IngresoClasificadorService.php`
- Implementar lógica de clasificación automática
- Integrar con `PagosIngresoService`

**Archivo a crear**: `app/Services/IngresoClasificadorService.php`

**Métodos principales**:
```php
class IngresoClasificadorService
{
    public function clasificarIngreso(Venta $venta, array $datosPago): string
    public function esEnganchez(Venta $venta, array $datosPago): bool
    public function esMensualidad(Venta $venta, array $datosPago): bool
    public function esAbonoCapital(Venta $venta, array $datosPago): bool
    public function validarClasificacion(string $tipo, Venta $venta): bool
}
```

**Lógica de clasificación**:
```php
public function clasificarIngreso(Venta $venta, array $datosPago): string
{
    // 1. Si venta está apartada = enganche
    if ($venta->estaApartado()) {
        return VentaIngreso::TIPO_ENGANCHE;
    }
    
    // 2. Si coincide con cuota mensual = mensualidad
    if ($this->coincideConCuotaMensual($venta, $datosPago['total'])) {
        return VentaIngreso::TIPO_MENSUALIDAD;
    }
    
    // 3. Si es mayor a cuota mensual = abono capital
    if ($datosPago['total'] > $venta->cuota_mensual) {
        return VentaIngreso::TIPO_ABONO_CAPITAL;
    }
    
    // 4. Por defecto = mensualidad
    return VentaIngreso::TIPO_MENSUALIDAD;
}
```

### 4.3 Implementar Registro de Formas de Pago Múltiples
**Prioridad**: ALTA
**Tiempo estimado**: 2 horas

**Acciones**:
- Extender `VentaIngreso` para múltiples formas de pago
- Implementar validación de suma de formas
- Crear registro detallado por forma

**Código a implementar**:
```php
// En VentaIngreso entity
public function setFormasPago(array $formas): static
{
    $total = 0;
    
    foreach ($formas as $forma => $monto) {
        if ($monto > 0) {
            $this->attributes[$forma] = $monto;
            $total += $monto;
        }
    }
    
    // Validar que suma coincida con total
    if (abs($total - $this->total) > 0.01) {
        throw new \Exception('La suma de formas de pago no coincide con el total');
    }
    
    return $this;
}

public function getFormasPago(): array
{
    return [
        'efectivo' => $this->efectivo ?? 0,
        'transferencia' => $this->transferencia ?? 0,
        'cheque' => $this->cheque ?? 0,
        'tarjeta' => $this->tarjeta ?? 0
    ];
}
```

### 4.4 Integrar con Sistema de Cobranza (Fase 3)
**Prioridad**: ALTA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Conectar `PagosIngresoService` con `CobranzaService`
- Implementar actualización automática de cobranza
- Crear conciliación de pagos vs plan

**Flujo de integración**:
```php
// En PagosIngresoService
public function procesarPago(Venta $venta, array $datosPago): array
{
    // 1. Clasificar ingreso
    $tipo = $this->clasificadorService->clasificarIngreso($venta, $datosPago);
    
    // 2. Crear registro de ingreso
    $ingreso = $this->crearRegistroIngreso($venta, $datosPago, $tipo);
    
    // 3. Actualizar cobranza
    $this->actualizarCobranza($venta, $ingreso);
    
    // 4. Actualizar venta
    $this->actualizarVenta($venta, $ingreso);
    
    return ['ingreso' => $ingreso, 'tipo' => $tipo];
}
```

### 4.5 Crear Sistema de Conciliación
**Prioridad**: MEDIA
**Tiempo estimado**: 2 horas

**Acciones**:
- Crear `ConciliacionService.php`
- Implementar conciliación automática
- Generar reportes de diferencias

**Archivo a crear**: `app/Services/ConciliacionService.php`

**Métodos principales**:
```php
class ConciliacionService
{
    public function conciliarPagosVsCobranza(Venta $venta): array
    public function identificarDiferencias(Venta $venta): array
    public function generarReporteConciliacion(Venta $venta): array
    public function corregirDiferencias(Venta $venta, array $ajustes): bool
}
```

### 4.6 Implementar Reportes de Ingresos
**Prioridad**: MEDIA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Crear `IngresoReporteService.php`
- Implementar reportes por tipo de ingreso
- Integrar con dashboard

**Métodos de reporte**:
```php
class IngresoReporteService
{
    public function reporteIngresosPorTipo(array $filtros): array
    public function reporteIngresosPorForma(array $filtros): array
    public function reporteIngresosPorVendedor(array $filtros): array
    public function reporteIngresosDiarios(string $fecha): array
}
```

## ARCHIVOS A CREAR

### Nuevos Servicios:
- ✅ `app/Services/IngresoClasificadorService.php`
- ✅ `app/Services/ConciliacionService.php`
- ✅ `app/Services/IngresoReporteService.php`

### Nuevos Models:
- ✅ `app/Models/IngresoDetalleModel.php` (para formas de pago)
- ✅ `app/Models/ConciliacionModel.php` (para diferencias)

### Nuevas Entidades:
- ✅ `app/Entities/IngresoDetalle.php`
- ✅ `app/Entities/Conciliacion.php`

## ARCHIVOS A ACTUALIZAR

### Servicios:
- 🔄 `app/Services/PagosIngresoService.php`
- 🔄 `app/Services/CobranzaService.php`
- 🔄 `app/Services/VentasService.php`

### Controllers:
- 🔄 `app/Controllers/Admin/AdminPagosController.php`
- 🔄 `app/Controllers/Admin/AdminVentasController.php`

### Entidades:
- 🔄 `app/Entities/VentaIngreso.php`
- 🔄 `app/Entities/Venta.php`

## FLUJO DE REGISTRO IMPLEMENTADO

### 1. Pago de Enganche:
```
Cliente realiza pago
→ PagosIngresoService::procesarPago()
→ IngresoClasificadorService::clasificarIngreso() → TIPO_ENGANCHE
→ VentaIngreso::create() con tipo enganche
→ Venta::procesarPagoAnticipo()
→ VentaEstadoService::procesarTransicionAutomatica()
```

### 2. Pago de Mensualidad:
```
Cliente realiza pago
→ PagosIngresoService::procesarPago()
→ IngresoClasificadorService::clasificarIngreso() → TIPO_MENSUALIDAD
→ VentaIngreso::create() con tipo mensualidad
→ CobranzaService::marcarCuotaPagada()
→ Venta::procesarPago()
```

### 3. Abono a Capital:
```
Cliente realiza pago extra
→ PagosIngresoService::procesarPago()
→ IngresoClasificadorService::clasificarIngreso() → TIPO_ABONO_CAPITAL
→ VentaIngreso::create() con tipo abono capital
→ VentaCalculoService::recalcularAmortizacion()
→ CobranzaService::actualizarPlan()
```

## COMPATIBILIDAD CON SISTEMA LEGACY

### Mapping de Campos:
- `Enganche` → `tipo_ingreso = 'enganche'`
- `Mensualidad` → `tipo_ingreso = 'mensualidad'`
- `AbonoCapital` → `tipo_ingreso = 'abono_capital'`
- `Comision` → `tipo_ingreso = 'comision'`

### Estructura de Datos Compatible:
```php
// Registro de ingreso legacy compatible
[
    'venta_id' => $venta->id,
    'total' => 5000.00,
    'tipo_ingreso' => 'mensualidad',
    'efectivo' => 3000.00,
    'transferencia' => 2000.00,
    'cheque' => 0.00,
    'tarjeta' => 0.00,
    'fecha_ingreso' => '2024-08-15',
    'referencia' => 'REF-001',
    'folio' => 'ING-000001'
]
```

## PRUEBAS REQUERIDAS

### 1. Pruebas de Clasificación:
- Pago de enganche se clasifica correctamente
- Pago de mensualidad se identifica automáticamente
- Abono capital se diferencia de mensualidad

### 2. Pruebas de Formas de Pago:
- Pago con múltiples formas suma correctamente
- Validación de suma de formas funciona
- Registro detallado por forma se guarda

### 3. Pruebas de Conciliación:
- Pagos se concilian con plan de cobranza
- Diferencias se identifican correctamente
- Reportes de conciliación son precisos

## BENEFICIOS ESPERADOS

### Técnicos:
- Clasificación automática de ingresos
- Conciliación automática con cobranza
- Reportes detallados por tipo

### Negocio:
- Mejor control de ingresos
- Clasificación automática reduce errores
- Conciliación en tiempo real

## CRITERIOS DE ACEPTACIÓN

### ✅ Funcionalidad:
- Ingresos se clasifican automáticamente
- Formas de pago múltiples funcionan
- Conciliación automática opera correctamente

### ✅ Reportes:
- Reportes por tipo de ingreso disponibles
- Reportes por forma de pago funcionan
- Dashboard muestra métricas correctas

### ✅ Integración:
- Integración con cobranza funciona
- Actualización de venta automática
- Transiciones de estado apropiadas

---

**DEPENDENCIAS**: Fase 3 completada
**TIEMPO TOTAL ESTIMADO**: 11.5 horas
**SIGUIENTES FASES**: Fase 5 - Cálculo de comisiones