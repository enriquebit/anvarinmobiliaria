# 🏗️ ARQUITECTURA INGRESOS vs PAGOS

## 📋 Resumen de la Refactorización

### ✅ **INGRESOS** (Dinero que ENTRA)
- **Tabla**: `ingresos` (renombrada de `pagos`)
- **Uso**: Registrar dinero que entra de clientes
- **Conceptos**: apartado, enganche, mensualidad, capital, liquidacion
- **Modelo**: `IngresoModel` + `VentaIngresoModel`
- **Entidad**: `Ingreso` + `VentaIngreso`

### ✅ **PAGOS** (Dinero que SALE) 
- **Tabla**: `ventas_pagos` (existente)
- **Uso**: Registrar pagos diversos (puede incluir pagos a proveedores)
- **Modelo**: `VentaPagoModel` (existente)

### ✅ **COMISIONES** (Dinero que SALE a vendedores)
- **Tabla**: `comisiones_vendedores` (existente)
- **Uso**: Registrar comisiones a vendedores
- **Modelo**: `ComisionVendedorModel` (existente)

---

## 🔄 Cambios Realizados

### 1. **Base de Datos**
- ✅ Renombrado: `pagos` → `ingresos`
- ✅ Agregado campos: `folio_recibo`, `ordenante`, `fecha_referencia`, `saldo_anterior`, `saldo_posterior`, `tipo_ingreso`
- ✅ Renombrado campo: `total` → `total_ingreso`
- ✅ Eliminado tablas duplicadas: `pagos_comisiones`, `pagos_proveedores`

### 2. **Modelos**
- ✅ Creado: `IngresoModel.php`
- ✅ Actualizado: `VentaIngresoModel.php` (usa tabla `ingresos`)
- ✅ Usar existente: `ComisionVendedorModel.php`
- ✅ Usar existente: `VentaPagoModel.php`

### 3. **Entidades**
- ✅ Creada: `Ingreso.php`
- ✅ Usar existente: `VentaIngreso.php`
- ✅ Usar existente: `ComisionVendedor.php`

### 4. **Servicios**
- ✅ Actualizado: `VentasService.php` (usa `ingresosService`)
- ✅ Actualizado: `PagosIngresoService.php` (usa `IngresoModel`)

---

## 🔧 Arquitectura Final

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUJO DE DINERO                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  💰 INGRESOS (Dinero que ENTRA)                            │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Tabla: ingresos                                     │    │
│  │ - Apartados de clientes                             │    │
│  │ - Enganches de clientes                             │    │
│  │ - Mensualidades de clientes                         │    │
│  │ - Liquidaciones de clientes                         │    │
│  │ - Otros ingresos                                    │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
│  💸 PAGOS (Dinero que SALE)                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Tabla: comisiones_vendedores                        │    │
│  │ - Comisiones a vendedores                           │    │
│  │ - Bonos y comisiones especiales                     │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Tabla: ventas_pagos                                 │    │
│  │ - Pagos a proveedores                               │    │
│  │ - Gastos operativos                                 │    │
│  │ - Otros pagos                                       │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Mapeo de Conceptos

### **INGRESOS** (tabla: `ingresos`)
| Concepto | Descripción | Tipo Ingreso |
|----------|-------------|--------------|
| `apartado` | Dinero inicial para reservar lote | `apartado` |
| `enganche` | Pago inicial de la venta | `enganche` |
| `mensualidad` | Pago mensual de crédito | `mensualidad` |
| `capital` | Abono directo a capital | `abono_capital` |
| `liquidacion` | Pago final de la venta | `liquidacion` |

### **COMISIONES** (tabla: `comisiones_vendedores`)
| Tipo Comision | Descripción | Estado |
|---------------|-------------|---------|
| `captacion` | Comisión por generar lead | `pagada`/`pendiente` |
| `venta` | Comisión por cerrar venta | `pagada`/`pendiente` |
| `apartado` | Comisión por apartado | `pagada`/`pendiente` |
| `bono` | Bonificación especial | `pagada`/`pendiente` |

### **PAGOS DIVERSOS** (tabla: `ventas_pagos`)
| Concepto | Descripción | Método Pago |
|----------|-------------|-------------|
| `apartado` | Pago de apartado | `efectivo`/`transferencia` |
| `capital` | Pago de capital | `efectivo`/`transferencia` |
| `interes` | Pago de intereses | `efectivo`/`transferencia` |
| `penalizacion` | Pago de penalización | `efectivo`/`transferencia` |

---

## 🎯 Próximos Pasos

### Pendientes:
1. **Actualizar vistas y controladores** para nueva lógica de INGRESOS
2. **Probar funcionalidad** de creación de ventas con nueva arquitectura
3. **Arreglar sidebar** (últimos 2 elementos treeview)

### Completado:
- ✅ Base de datos refactorizada
- ✅ Modelos y entidades actualizados
- ✅ Servicios principales actualizados
- ✅ Eliminadas tablas duplicadas

---

## 📝 Notas Técnicas

- **Compatibilidad**: Se mantiene `VentaIngresoModel` para compatibilidad temporal
- **Migración**: Datos existentes se preservan en tabla `ingresos`
- **Rendimiento**: Índices optimizados para consultas frecuentes
- **Auditoría**: Campos de trazabilidad completos (IP, usuario, timestamps)
