# FASE 3: Generación de Tabla de Cobranza

## Objetivo
Implementar la generación automática de registros de cobranza después de crear la tabla de amortización, siguiendo el flujo del sistema legacy.

## Análisis del Sistema Legacy

### Tipos de Crédito Legacy:
- **TipoCredito=1**: Apartado/Enganche
- **TipoCredito=2**: Venta/Mensualidades

### Campos de Control Legacy:
- `Plazo`: Número de cuota o "A" para anualidades
- `FechaFinal`: Fecha límite de pago
- `Cobrado`: FALSE=Pendiente, TRUE=Pagado
- `Interes`: Monto de penalización por mora
- `TotalSI`: Total sin intereses

### Tabla Legacy: `tb_cobranza`
- Total de registros: 26,491
- Apartados: 285 registros
- Mensualidades: 26,206 registros

## Estado Actual del Sistema

### Servicios Existentes:
- ✅ `CobranzaService.php` - Gestión de planes de pago
- ✅ `VentaCalculoService.php` - Cálculos de amortización (post-Fase 1)
- ✅ `InteresesService.php` - Cálculo de intereses moratorios

### Entidades Existentes:
- ✅ `Cobranza.php` - Entity principal
- ✅ `CobranzaPlanPago.php` - Planes de pago
- ✅ `VentaAmortizacion.php` - Tabla de amortización

### Funcionalidad Faltante:
- ❌ Generación automática de registros de cobranza post-amortización
- ❌ Integración completa con tabla de amortización
- ❌ Manejo de anualidades especiales
- ❌ Sincronización con cambios en venta

## SUBTAREAS FASE 3

### 3.1 Crear Servicio de Generación de Cobranza
**Prioridad**: ALTA
**Tiempo estimado**: 2.5 horas

**Acciones**:
- Crear `CobranzaGeneradorService.php`
- Implementar generación automática desde tabla de amortización
- Integrar con `VentaEstadoService` de Fase 2

**Archivo a crear**: `app/Services/CobranzaGeneradorService.php`

**Métodos principales**:
```php
class CobranzaGeneradorService
{
    public function generarCobranzaDesdeAmortizacion(Venta $venta): array
    public function generarCobranzaApartado(Venta $venta): Cobranza
    public function generarCobranzaMensualidades(Venta $venta): array
    public function generarCobranzaAnualidades(Venta $venta, array $anualidades): array
    public function sincronizarCobranzaConVenta(Venta $venta): void
}
```

### 3.2 Implementar Tipos de Crédito
**Prioridad**: ALTA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Extender `Cobranza` entity con tipos de crédito
- Implementar lógica de apartado vs mensualidades
- Agregar validaciones por tipo

**Código a implementar**:
```php
// En app/Entities/Cobranza.php
public const TIPO_CREDITO_APARTADO = 1;
public const TIPO_CREDITO_MENSUALIDAD = 2;

public function esApartado(): bool
{
    return $this->tipo_credito === self::TIPO_CREDITO_APARTADO;
}

public function esMensualidad(): bool
{
    return $this->tipo_credito === self::TIPO_CREDITO_MENSUALIDAD;
}
```

### 3.3 Integrar con Tabla de Amortización
**Prioridad**: ALTA
**Tiempo estimado**: 2 horas

**Acciones**:
- Modificar `VentaCalculoService` para incluir generación de cobranza
- Crear relación entre `VentaAmortizacion` y `Cobranza`
- Implementar sincronización automática

**Flujo de integración**:
```php
// En VentaCalculoService
public function generarAmortizacionConCobranza(Venta $venta): array
{
    // 1. Generar tabla de amortización
    $tablaAmortizacion = $this->generarTablaAmortizacion($venta);
    
    // 2. Generar registros de cobranza
    $cobranzaGenerator = new CobranzaGeneradorService();
    $cobranzas = $cobranzaGenerator->generarCobranzaDesdeAmortizacion($venta);
    
    // 3. Sincronizar ambas
    $this->sincronizarAmortizacionCobranza($tablaAmortizacion, $cobranzas);
    
    return [
        'amortizacion' => $tablaAmortizacion,
        'cobranzas' => $cobranzas
    ];
}
```

### 3.4 Implementar Manejo de Anualidades
**Prioridad**: MEDIA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Extender `CobranzaGeneradorService` para anualidades
- Implementar lógica de plazo "A" para anualidades
- Agregar validaciones específicas

**Código a implementar**:
```php
// En CobranzaGeneradorService
public function generarCobranzaAnualidades(Venta $venta, array $anualidades): array
{
    $cobranzas = [];
    
    foreach ($anualidades as $index => $anualidad) {
        $cobranza = new Cobranza();
        $cobranza->venta_id = $venta->id;
        $cobranza->tipo_credito = Cobranza::TIPO_CREDITO_MENSUALIDAD;
        $cobranza->plazo = 'A'; // Anualidad
        $cobranza->total = $anualidad['monto'];
        $cobranza->fecha_final = $anualidad['fecha_vencimiento'];
        $cobranza->cobrado = false;
        
        $cobranzas[] = $cobranza;
    }
    
    return $cobranzas;
}
```

### 3.5 Crear Sistema de Sincronización
**Prioridad**: MEDIA
**Tiempo estimado**: 2 horas

**Acciones**:
- Implementar `CobranzaSincronizadorService`
- Crear eventos para sincronización automática
- Manejar cambios en venta que afecten cobranza

**Archivo a crear**: `app/Services/CobranzaSincronizadorService.php`

**Eventos a manejar**:
```php
class CobranzaSincronizadorService
{
    public function onVentaConfirmada(Venta $venta): void
    public function onVentaModificada(Venta $venta): void
    public function onVentaCancelada(Venta $venta): void
    public function onPagoRealizado(Venta $venta, array $pago): void
}
```

### 3.6 Actualizar VentasService
**Prioridad**: ALTA
**Tiempo estimado**: 1 hora

**Acciones**:
- Integrar `CobranzaGeneradorService` en flujo de ventas
- Modificar confirmación de venta para generar cobranza
- Actualizar método de apartado

**Métodos a actualizar**:
```php
// En VentasService
public function confirmarVenta(int $ventaId): array
{
    // 1. Confirmar venta (existente)
    $venta = $this->confirmarVentaExistente($ventaId);
    
    // 2. Generar tabla de amortización
    $resultado = $this->calculoService->generarAmortizacionConCobranza($venta);
    
    // 3. Actualizar estado (integración con Fase 2)
    $this->estadoService->procesarTransicionAutomatica($venta);
    
    return $resultado;
}
```

## ARCHIVOS A CREAR

### Nuevos Servicios:
- ✅ `app/Services/CobranzaGeneradorService.php`
- ✅ `app/Services/CobranzaSincronizadorService.php`

### Nuevos Models:
- ✅ `app/Models/CobranzaHistorialModel.php` (para auditoría)

### Nuevas Entidades:
- ✅ `app/Entities/CobranzaHistorial.php`

## ARCHIVOS A ACTUALIZAR

### Servicios:
- 🔄 `app/Services/VentasService.php`
- 🔄 `app/Services/VentaCalculoService.php`
- 🔄 `app/Services/CobranzaService.php`

### Controllers:
- 🔄 `app/Controllers/Admin/AdminVentasController.php`
- 🔄 `app/Controllers/Admin/AdminCobranzaController.php`

### Entidades:
- 🔄 `app/Entities/Cobranza.php`
- 🔄 `app/Entities/Venta.php`

## FLUJO DE GENERACIÓN IMPLEMENTADO

### 1. Venta Confirmada:
```
Venta::confirmar() 
→ VentaCalculoService::generarAmortizacionConCobranza()
→ CobranzaGeneradorService::generarCobranzaDesdeAmortizacion()
→ CobranzaModel::insertBatch()
→ CobranzaSincronizadorService::onVentaConfirmada()
```

### 2. Apartado Creado:
```
Venta::apartar()
→ CobranzaGeneradorService::generarCobranzaApartado()
→ CobranzaModel::save()
```

### 3. Anualidades Especiales:
```
VentasService::configurarAnualidades()
→ CobranzaGeneradorService::generarCobranzaAnualidades()
→ CobranzaModel::insertBatch()
```

## COMPATIBILIDAD CON SISTEMA LEGACY

### Mapping de Campos:
- `TipoCredito` → `tipo_credito`
- `Plazo` → `plazo`
- `FechaFinal` → `fecha_final`
- `Cobrado` → `cobrado`
- `Interes` → `interes`
- `TotalSI` → `total_sin_intereses`

### Estructura de Datos Compatible:
```php
// Registro de cobranza legacy compatible
[
    'venta_id' => $venta->id,
    'tipo_credito' => 2, // Mensualidad
    'plazo' => 1, // Primera cuota
    'total' => 5000.00,
    'total_pagado' => 0.00,
    'fecha_final' => '2024-08-15',
    'cobrado' => false,
    'interes' => 0.00,
    'total_sin_intereses' => 5000.00
]
```

## PRUEBAS REQUERIDAS

### 1. Pruebas de Generación:
- Generar cobranza desde tabla de amortización
- Validar creación de apartado
- Verificar anualidades especiales

### 2. Pruebas de Sincronización:
- Modificar venta y verificar actualización de cobranza
- Cancelar venta y verificar eliminación de cobranza
- Procesar pago y verificar actualización

### 3. Pruebas de Compatibilidad:
- Verificar campos compatibles con legacy
- Validar tipos de crédito correctos
- Confirmar estructura de datos

## BENEFICIOS ESPERADOS

### Técnicos:
- Generación automática de cobranza
- Sincronización en tiempo real
- Estructura compatible con legacy

### Negocio:
- Proceso automatizado de cobranza
- Mejor control de cuentas por cobrar
- Reducción de errores manuales

## CRITERIOS DE ACEPTACIÓN

### ✅ Funcionalidad:
- Cobranza se genera automáticamente al confirmar venta
- Tipos de crédito funcionan correctamente
- Anualidades se manejan adecuadamente

### ✅ Sincronización:
- Cambios en venta actualizan cobranza
- Pagos actualizan estado de cobranza
- Cancelaciones eliminan cobranza

### ✅ Compatibilidad:
- Estructura compatible con sistema legacy
- Campos mapeados correctamente
- Flujo de datos consistente

---

**DEPENDENCIAS**: Fase 2 completada
**TIEMPO TOTAL ESTIMADO**: 10.5 horas
**SIGUIENTES FASES**: Fase 4 - Registro de ingresos