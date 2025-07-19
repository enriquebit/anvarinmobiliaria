# Métodos de Amortización en AdminVentasController

## Resumen de duplicidad resuelta

### ✅ Métodos actuales (sin duplicidad):

#### 1. `generarTablaAmortizacion()` - Línea 980
- **Propósito**: Vista previa de amortización con parámetros POST
- **Parámetros**: Sin parámetros de URL, recibe datos via POST
- **Uso**: Para simulaciones desde formularios
- **Ruta**: `POST /admin/ventas/generar-tabla-amortizacion`
- **Datos entrada**: precio_final, enganche, plazo_meses, tasa_anual, etc.

#### 2. `generarAmortizacionVenta($ventaId)` - Línea 1696
- **Propósito**: Generar amortización para venta específica existente
- **Parámetros**: $ventaId (ID de venta existente)
- **Uso**: Para ventas ya creadas en la base de datos
- **Ruta**: `GET /admin/ventas/generar-amortizacion/{ventaId}`
- **Datos entrada**: ID de venta, usa datos de la venta existente

### 🔄 Otros métodos relacionados:

#### 3. `simularAmortizacion()` - Línea 1730
- **Propósito**: Simular amortización con parámetros JSON personalizados
- **Parámetros**: Sin parámetros URL, recibe JSON
- **Uso**: Simulaciones avanzadas con ConfiguracionFinanciera
- **Ruta**: `POST /admin/ventas/simular-amortizacion`

#### 4. `exportarTablaAmortizacion($ventaId, $formato)` - Línea 1914
- **Propósito**: Exportar tabla de amortización en diferentes formatos
- **Parámetros**: $ventaId, $formato (json, csv, excel, pdf)
- **Uso**: Descargar tablas de amortización
- **Ruta**: `GET /admin/ventas/exportar-amortizacion/{ventaId}/{formato}`

### 📝 Diferencias clave:

| Método | Entrada | Venta existente | ConfigFinanciera | Propósito |
|--------|---------|-----------------|------------------|-----------|
| `generarTablaAmortizacion()` | POST form | ❌ No | ❌ Opcional | Vista previa |
| `generarAmortizacionVenta()` | URL param | ✅ Sí | ✅ Usa de venta | Venta real |
| `simularAmortizacion()` | JSON | ❌ No | ✅ Requerida | Simulación avanzada |
| `exportarTablaAmortizacion()` | URL param | ✅ Sí | ✅ Usa de venta | Exportación |

### 🚀 Uso recomendado:

- **Para formularios nuevos**: Usar `generarTablaAmortizacion()` con POST
- **Para ventas existentes**: Usar `generarAmortizacionVenta($ventaId)`
- **Para simulaciones avanzadas**: Usar `simularAmortizacion()` con JSON
- **Para exportar**: Usar `exportarTablaAmortizacion($ventaId, $formato)`