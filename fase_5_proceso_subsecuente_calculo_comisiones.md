# FASE 5: Cálculo y Registro de Comisiones

## Objetivo
Implementar el sistema completo de cálculo y registro de comisiones automático posterior al registro de ventas, siguiendo el flujo del sistema legacy.

## Análisis del Sistema Legacy

### Proceso de Comisiones Legacy:
- **Comisión por Apartado**: `ComisionApartado` al momento de reservar
- **Comisión Total**: `ComisionTotal` al completar la venta
- **Estados**: Cobrada=0 (Pendiente), Cobrada=1 (Pagada)
- **SubComisiones**: Para vendedores con estructura jerárquica

### Tabla Legacy: `tb_comisiones`
- Total de registros: 416 comisiones
- Campos críticos: `Total`, `TotalPagado`, `Cobrada`, `SubComision`
- Relación con: `Venta`, `Vendedor`, `Ingreso`

## Estado Actual del Sistema

### Servicios Existentes:
- ✅ `ComisionService.php` - Servicio completo y funcional
- ✅ `ComisionDinamicaService.php` - Cálculo dinámico
- ✅ `VentasService.php` - Integración básica

### Entidades Existentes:
- ✅ `ComisionVendedor.php` - Entity principal
- ✅ `Venta.php` - Con campos de comisión

### Funcionalidad Faltante:
- ❌ Generación automática de comisiones post-venta
- ❌ Integración completa con flujo de ingresos (Fase 4)
- ❌ Procesamiento automático de pagos de comisiones
- ❌ Dashboard completo de comisiones

## SUBTAREAS FASE 5

### 5.1 Crear Sistema de Generación Automática
**Prioridad**: ALTA
**Tiempo estimado**: 2 horas

**Acciones**:
- Crear `ComisionGeneradorService.php`
- Implementar generación automática en eventos de venta
- Integrar con `VentaEstadoService` de Fase 2

**Archivo a crear**: `app/Services/ComisionGeneradorService.php`

**Métodos principales**:
```php
class ComisionGeneradorService
{
    public function generarComisionApartado(Venta $venta): ?ComisionVendedor
    public function generarComisionTotal(Venta $venta): ?ComisionVendedor
    public function generarComisionCompleta(Venta $venta): array
    public function calcularMontoComision(Venta $venta, string $tipo): float
    public function validarGeneracionComision(Venta $venta, string $tipo): bool
}
```

**Lógica de generación**:
```php
public function generarComisionApartado(Venta $venta): ?ComisionVendedor
{
    if (!$venta->estaApartado() || $venta->comision_apartado <= 0) {
        return null;
    }
    
    $comision = new ComisionVendedor();
    $comision->venta_id = $venta->id;
    $comision->vendedor_id = $venta->vendedor_id;
    $comision->tipo_comision = 'apartado';
    $comision->total = $venta->comision_apartado;
    $comision->total_pagado = 0;
    $comision->cobrada = false;
    $comision->fecha_generacion = date('Y-m-d H:i:s');
    
    return $comision;
}
```

### 5.2 Integrar con Flujo de Ventas
**Prioridad**: ALTA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Modificar `VentasService` para incluir generación de comisiones
- Integrar eventos de venta con generación automática
- Actualizar método de confirmación de venta

**Código de integración**:
```php
// En VentasService
public function apartar(array $datosVenta): array
{
    // 1. Crear apartado (existente)
    $venta = $this->crearApartado($datosVenta);
    
    // 2. Generar comisión de apartado
    $comisionGenerador = new ComisionGeneradorService();
    $comisionApartado = $comisionGenerador->generarComisionApartado($venta);
    
    if ($comisionApartado) {
        $this->comisionService->guardarComision($comisionApartado);
    }
    
    return ['venta' => $venta, 'comision' => $comisionApartado];
}

public function confirmarVenta(int $ventaId): array
{
    // 1. Confirmar venta (existente)
    $venta = $this->confirmarVentaExistente($ventaId);
    
    // 2. Generar comisión total
    $comisionGenerador = new ComisionGeneradorService();
    $comisionTotal = $comisionGenerador->generarComisionTotal($venta);
    
    if ($comisionTotal) {
        $this->comisionService->guardarComision($comisionTotal);
    }
    
    return ['venta' => $venta, 'comision' => $comisionTotal];
}
```

### 5.3 Implementar Procesamiento de Pagos de Comisiones
**Prioridad**: ALTA
**Tiempo estimado**: 2 horas

**Acciones**:
- Crear `ComisionPagoService.php`
- Implementar procesamiento de pagos individuales y masivos
- Integrar con sistema de ingresos

**Archivo a crear**: `app/Services/ComisionPagoService.php`

**Métodos principales**:
```php
class ComisionPagoService
{
    public function procesarPagoComision(ComisionVendedor $comision, array $datosPago): array
    public function procesarPagoMasivo(array $comisionesIds, array $datosPago): array
    public function validarPagoComision(ComisionVendedor $comision, float $monto): bool
    public function registrarPagoComision(ComisionVendedor $comision, array $datosPago): VentaIngreso
}
```

**Flujo de pago**:
```php
public function procesarPagoComision(ComisionVendedor $comision, array $datosPago): array
{
    // 1. Validar pago
    $this->validarPagoComision($comision, $datosPago['total']);
    
    // 2. Registrar ingreso como comisión
    $ingreso = $this->registrarPagoComision($comision, $datosPago);
    
    // 3. Actualizar comisión
    $comision->total_pagado += $datosPago['total'];
    $comision->cobrada = ($comision->total_pagado >= $comision->total);
    $comision->fecha_pago = date('Y-m-d H:i:s');
    
    // 4. Guardar cambios
    $this->comisionModel->save($comision);
    
    return ['comision' => $comision, 'ingreso' => $ingreso];
}
```

### 5.4 Crear Dashboard de Comisiones
**Prioridad**: MEDIA
**Tiempo estimado**: 2.5 horas

**Acciones**:
- Crear `ComisionDashboardService.php`
- Implementar métricas y KPIs
- Integrar con AdminComisionesController

**Archivo a crear**: `app/Services/ComisionDashboardService.php`

**Métodos del dashboard**:
```php
class ComisionDashboardService
{
    public function getMetricasGenerales(): array
    public function getComisionesPendientes(): array
    public function getComisionesPorVendedor(): array
    public function getComisionesPorMes(): array
    public function getTopVendedores(): array
}
```

**Métricas implementadas**:
```php
public function getMetricasGenerales(): array
{
    return [
        'total_comisiones_generadas' => $this->calcularTotalGeneradas(),
        'total_comisiones_pagadas' => $this->calcularTotalPagadas(),
        'total_comisiones_pendientes' => $this->calcularTotalPendientes(),
        'porcentaje_cobrado' => $this->calcularPorcentajeCobrado(),
        'promedio_comision_por_venta' => $this->calcularPromedioComision(),
        'vendedores_activos' => $this->contarVendedoresActivos()
    ];
}
```

### 5.5 Implementar Reportes de Comisiones
**Prioridad**: MEDIA
**Tiempo estimado**: 1.5 horas

**Acciones**:
- Crear `ComisionReporteService.php`
- Implementar reportes detallados
- Integrar con sistema de exportación

**Archivo a crear**: `app/Services/ComisionReporteService.php`

**Reportes implementados**:
```php
class ComisionReporteService
{
    public function reporteComisionesPorVendedor(int $vendedorId): array
    public function reporteComisionesPorPeriodo(string $fechaInicio, string $fechaFin): array
    public function reporteComisionesPorProyecto(int $proyectoId): array
    public function reporteComisionesPendientes(): array
    public function exportarReporteComisiones(array $filtros): string
}
```

### 5.6 Crear Sistema de Alertas
**Prioridad**: BAJA
**Tiempo estimado**: 1 hora

**Acciones**:
- Crear `ComisionAlertaService.php`
- Implementar alertas para comisiones vencidas
- Integrar con sistema de notificaciones

**Archivo a crear**: `app/Services/ComisionAlertaService.php`

**Alertas implementadas**:
```php
class ComisionAlertaService
{
    public function alertasComisionesPendientes(): array
    public function alertasComisionesVencidas(): array
    public function alertasVendedoresSinComision(): array
    public function enviarAlertasPorEmail(): void
}
```

## ARCHIVOS A CREAR

### Nuevos Servicios:
- ✅ `app/Services/ComisionGeneradorService.php`
- ✅ `app/Services/ComisionPagoService.php`
- ✅ `app/Services/ComisionDashboardService.php`
- ✅ `app/Services/ComisionReporteService.php`
- ✅ `app/Services/ComisionAlertaService.php`

### Nuevos Models:
- ✅ `app/Models/ComisionHistorialModel.php` (para auditoría)
- ✅ `app/Models/ComisionPagoModel.php` (para pagos)

### Nuevas Entidades:
- ✅ `app/Entities/ComisionHistorial.php`
- ✅ `app/Entities/ComisionPago.php`

## ARCHIVOS A ACTUALIZAR

### Servicios:
- 🔄 `app/Services/VentasService.php`
- 🔄 `app/Services/ComisionService.php`
- 🔄 `app/Services/PagosIngresoService.php`

### Controllers:
- 🔄 `app/Controllers/Admin/AdminComisionesController.php`
- 🔄 `app/Controllers/Admin/AdminVentasController.php`

### Entidades:
- 🔄 `app/Entities/ComisionVendedor.php`
- 🔄 `app/Entities/VentaIngreso.php`

## FLUJO DE COMISIONES IMPLEMENTADO

### 1. Comisión por Apartado:
```
Venta::apartar()
→ ComisionGeneradorService::generarComisionApartado()
→ ComisionService::guardarComision()
→ ComisionDashboardService::actualizarMetricas()
```

### 2. Comisión Total:
```
Venta::confirmar()
→ ComisionGeneradorService::generarComisionTotal()
→ ComisionService::guardarComision()
→ ComisionAlertaService::verificarAlertas()
```

### 3. Pago de Comisión:
```
AdminComisionesController::procesarPago()
→ ComisionPagoService::procesarPagoComision()
→ PagosIngresoService::registrarIngreso() (tipo comisión)
→ ComisionService::marcarComoCobrada()
```

## COMPATIBILIDAD CON SISTEMA LEGACY

### Mapping de Campos:
- `ComisionApartado` → `comision_apartado`
- `ComisionTotal` → `comision_total`
- `Cobrada` → `cobrada`
- `SubComision` → `sub_comision`
- `SubVendedor` → `sub_vendedor_id`

### Estructura de Datos Compatible:
```php
// Registro de comisión legacy compatible
[
    'venta_id' => $venta->id,
    'vendedor_id' => $venta->vendedor_id,
    'tipo_comision' => 'apartado',
    'total' => 5000.00,
    'total_pagado' => 0.00,
    'cobrada' => false,
    'fecha_generacion' => '2024-08-15 10:30:00',
    'fecha_pago' => null,
    'sub_comision' => false,
    'sub_vendedor_id' => null
]
```

## PRUEBAS REQUERIDAS

### 1. Pruebas de Generación:
- Comisión de apartado se genera automáticamente
- Comisión total se genera al confirmar venta
- Validaciones de generación funcionan correctamente

### 2. Pruebas de Pago:
- Pago individual de comisiones funciona
- Pago masivo de comisiones opera correctamente
- Registro de ingresos por comisiones es preciso

### 3. Pruebas de Reportes:
- Dashboard muestra métricas correctas
- Reportes por vendedor son precisos
- Alertas se generan apropiadamente

## BENEFICIOS ESPERADOS

### Técnicos:
- Generación automática de comisiones
- Procesamiento eficiente de pagos
- Dashboard completo con métricas

### Negocio:
- Control total sobre comisiones
- Pagos automatizados a vendedores
- Alertas para comisiones pendientes

## CRITERIOS DE ACEPTACIÓN

### ✅ Funcionalidad:
- Comisiones se generan automáticamente
- Pagos de comisiones funcionan correctamente
- Dashboard muestra información precisa

### ✅ Reportes:
- Reportes por vendedor disponibles
- Métricas del dashboard son correctas
- Alertas funcionan apropiadamente

### ✅ Integración:
- Integración con ventas funciona
- Integración con ingresos opera correctamente
- Flujo completo está automatizado

---

**DEPENDENCIAS**: Fase 4 completada
**TIEMPO TOTAL ESTIMADO**: 10 horas
**SIGUIENTES FASES**: Fase 6 - Generación de documentos