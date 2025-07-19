# FASE 2: Registro de Venta y Transiciones de Estados

## Objetivo
Implementar el flujo completo de estados de venta según el sistema legacy: Apartado → Vendido → Completado, con toda la lógica de negocio correspondiente.

## Análisis del Sistema Legacy

### Estados Identificados:
- **Estado=1**: Apartado (inicial)
- **Estado=2**: Vendido (confirmado)
- **Estado=3**: Completado (totalmente pagado)
- **Estado=0**: Cancelado

### Campos Críticos Legacy:
- `Total`: Monto total de la venta
- `TotalPagado`: Monto pagado acumulado
- `Anticipo`: Monto del enganche
- `AnticipoPagado`: Enganche pagado
- `Credito`: Indica si es venta a crédito
- `CobrarInteres`: Si aplica intereses por mora
- `Intereses`: Monto de intereses acumulados

## Estado Actual del Sistema

### Entity Venta Existente:
- ✅ Estados definidos: `ESTADO_APARTADO`, `ESTADO_VENTA_CREDITO`, `ESTADO_VENTA_CONTADO`, `ESTADO_CANCELADO`
- ✅ Métodos de transición: `apartar()`, `confirmar()`, `cancelar()`
- ✅ Validaciones de estado: `estaApartado()`, `estaVendido()`, `estaCancelado()`
- ✅ Cálculos financieros: `getSaldoPendiente()`, `getPorcentajePagado()`

### Funcionalidad Faltante:
- ❌ Estado "Completado" cuando venta está 100% pagada
- ❌ Transición automática de estados por pagos
- ❌ Validación de reglas de negocio por estado
- ❌ Log de cambios de estado para auditoría

## SUBTAREAS FASE 2

### 2.1 Extender Estados de Venta
**Prioridad**: ALTA
**Tiempo estimado**: 1 hora

**Acciones**:
- Agregar `ESTADO_COMPLETADO` a Entity Venta
- Implementar método `estaCompletado()`
- Implementar método `completar()`
- Agregar validaciones de transición

**Código a implementar**:
```php
// En app/Entities/Venta.php
public const ESTADO_COMPLETADO = 'completado';

public function estaCompletado(): bool
{
    return $this->estado === self::ESTADO_COMPLETADO;
}

public function completar(): static
{
    if (!$this->estaVendido()) {
        throw new \Exception('Solo se pueden completar ventas vendidas');
    }
    
    if ($this->getSaldoPendiente() > 0.01) {
        throw new \Exception('No se puede completar venta con saldo pendiente');
    }
    
    $this->attributes['estado'] = self::ESTADO_COMPLETADO;
    return $this;
}
```

### 2.2 Implementar Transiciones Automáticas
**Prioridad**: ALTA
**Tiempo estimado**: 2 horas

**Acciones**:
- Crear `VentaEstadoService` para manejar transiciones
- Implementar lógica de transición automática en pagos
- Agregar validaciones de reglas de negocio

**Archivo a crear**: `app/Services/VentaEstadoService.php`

**Métodos requeridos**:
```php
class VentaEstadoService
{
    public function procesarTransicionAutomatica(Venta $venta): Venta
    public function validarTransicion(Venta $venta, string $nuevoEstado): bool
    public function obtenerEstadosSiguientes(Venta $venta): array
    public function registrarCambioEstado(Venta $venta, string $estadoAnterior): void
}
```

### 2.3 Integrar con Procesamiento de Pagos
**Prioridad**: ALTA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Modificar `PagosIngresoService` para incluir transiciones
- Actualizar `VentasService` para usar `VentaEstadoService`
- Implementar validaciones por estado

**Flujo de integración**:
```php
// En PagosIngresoService
public function procesarPago(Venta $venta, array $datosPago): array
{
    // 1. Procesar pago normal
    $resultado = $this->procesarPagoExistente($venta, $datosPago);
    
    // 2. Verificar transición automática
    $ventaEstadoService = new VentaEstadoService();
    $venta = $ventaEstadoService->procesarTransicionAutomatica($venta);
    
    // 3. Guardar cambios
    $this->ventaModel->save($venta);
    
    return $resultado;
}
```

### 2.4 Crear Logs de Auditoría de Estados
**Prioridad**: MEDIA
**Tiempo estimado**: 1 hora

**Acciones**:
- Crear tabla `venta_estado_historial` (si no existe)
- Implementar `VentaHistorialEstadoModel`
- Integrar logging en cambios de estado

**Estructura de tabla**:
```sql
CREATE TABLE venta_estado_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT,
    FOREIGN KEY (venta_id) REFERENCES ventas(id)
);
```

### 2.5 Implementar Validaciones de Reglas de Negocio
**Prioridad**: MEDIA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Crear `VentaValidacionService`
- Implementar validaciones por estado
- Integrar con controllers

**Validaciones requeridas**:
```php
class VentaValidacionService
{
    public function validarApartado(Venta $venta): array
    public function validarVenta(Venta $venta): array
    public function validarCompletado(Venta $venta): array
    public function validarCancelacion(Venta $venta): array
}
```

### 2.6 Actualizar AdminVentasController
**Prioridad**: ALTA
**Tiempo estimado**: 2 horas

**Acciones**:
- Integrar `VentaEstadoService` en controller
- Actualizar método `confirmar()` para nuevos estados
- Agregar endpoints para transiciones manuales
- Implementar validaciones en endpoints

**Métodos a actualizar**:
```php
// En AdminVentasController
public function confirmar($ventaId)
{
    // Usar VentaEstadoService para validar y procesar
}

public function completar($ventaId)
{
    // Nuevo método para completar venta manualmente
}

public function obtenerHistorialEstados($ventaId)
{
    // Nuevo método para obtener historial de cambios
}
```

## ARCHIVOS A CREAR

### Nuevos Servicios:
- ✅ `app/Services/VentaEstadoService.php`
- ✅ `app/Services/VentaValidacionService.php`

### Nuevos Models:
- ✅ `app/Models/VentaHistorialEstadoModel.php`

### Nuevas Entidades:
- ✅ `app/Entities/VentaHistorialEstado.php`

## ARCHIVOS A ACTUALIZAR

### Servicios:
- 🔄 `app/Services/VentasService.php`
- 🔄 `app/Services/PagosIngresoService.php` (consolidado en Fase 1)

### Controllers:
- 🔄 `app/Controllers/Admin/AdminVentasController.php`

### Entidades:
- 🔄 `app/Entities/Venta.php`

## FLUJO DE ESTADOS IMPLEMENTADO

### 1. Apartado → Vendido:
- Validar que tenga anticipo pagado (si requerido)
- Validar que lote esté disponible
- Generar plan de pagos automáticamente
- Registrar cambio de estado

### 2. Vendido → Completado:
- Validar que saldo pendiente = 0
- Validar que no tenga pagos pendientes
- Actualizar estado de lote a "vendido"
- Registrar cambio de estado

### 3. Cualquier Estado → Cancelado:
- Validar reglas de cancelación
- Liberar lote para nueva venta
- Cancelar plan de pagos
- Registrar cambio de estado

## COMPATIBILIDAD CON SISTEMA LEGACY

### Mapping de Estados:
- `ESTADO_APARTADO` → Legacy Estado=1
- `ESTADO_VENTA_CREDITO` → Legacy Estado=2
- `ESTADO_VENTA_CONTADO` → Legacy Estado=2
- `ESTADO_COMPLETADO` → Legacy Estado=3 (nuevo)
- `ESTADO_CANCELADO` → Legacy Estado=0

### Campos Compatibles:
- `Total` → `total`
- `TotalPagado` → `total_pagado`
- `Anticipo` → `anticipo`
- `AnticipoPagado` → `anticipo_pagado`
- `Credito` → `es_credito`
- `CobrarInteres` → `cobrar_interes`
- `Intereses` → `intereses_acumulados`

## PRUEBAS REQUERIDAS

### 1. Pruebas de Transición:
- Apartado → Vendido con anticipo completo
- Vendido → Completado con saldo = 0
- Validar que transiciones inválidas fallen

### 2. Pruebas de Integración:
- Procesar pago y verificar transición automática
- Validar que plan de pagos se genere correctamente
- Verificar que logs de auditoría se guarden

### 3. Pruebas de Reglas de Negocio:
- No permitir apartar lote ya vendido
- No permitir completar venta con saldo pendiente
- Validar permisos por usuario

## BENEFICIOS ESPERADOS

### Técnicos:
- Estados bien definidos y transiciones controladas
- Auditoría completa de cambios
- Validaciones robustas de reglas de negocio

### Negocio:
- Flujo de ventas más confiable
- Mejor control de estados
- Historial completo para auditorías

## CRITERIOS DE ACEPTACIÓN

### ✅ Funcionalidad:
- Todos los estados funcionan correctamente
- Transiciones automáticas operan según reglas
- Validaciones previenen estados inválidos

### ✅ Auditoría:
- Todos los cambios se registran
- Historial completo disponible
- Trazabilidad de usuarios y fechas

### ✅ Integración:
- Compatible con sistema legacy
- Servicios integrados funcionan correctamente
- Controllers actualizados

---

**DEPENDENCIAS**: Fase 1 completada
**TIEMPO TOTAL ESTIMADO**: 9 horas
**SIGUIENTES FASES**: Fase 3 - Generación de cobranza