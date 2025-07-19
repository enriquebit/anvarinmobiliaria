# Procesos Subsecuentes a la Generación de Tabla de Amortización

## Análisis del Sistema Legacy - Módulo de Ventas

### Flujo Principal del Proceso de Ventas

Después de generar la tabla de amortización en el sistema legacy, se ejecutan los siguientes procesos subsecuentes:

## 1. REGISTRO DE VENTA (tb_ventas)
- **Estado inicial**: Apartado (Estado=1)
- **Transición**: Vendido (Estado=2) 
- **Campos críticos**:
  - `Total`: Monto total de la venta
  - `TotalPagado`: Monto pagado acumulado
  - `Anticipo`: Monto del enganche
  - `AnticipoPagado`: Enganche pagado
  - `Credito`: Indica si es venta a crédito
  - `CobrarInteres`: Si aplica intereses por mora
  - `Intereses`: Monto de intereses acumulados

## 2. GENERACIÓN DE TABLA DE COBRANZA (tb_cobranza)
Después de crear la tabla de amortización, se generan registros de cobranza:

### Tipos de Crédito:
- **TipoCredito=1**: Apartado/Enganche
- **TipoCredito=2**: Venta/Mensualidades

### Campos de Control:
- `Plazo`: Número de cuota o "A" para anualidades
- `FechaFinal`: Fecha límite de pago
- `Cobrado`: FALSE=Pendiente, TRUE=Pagado
- `Interes`: Monto de penalización por mora
- `TotalSI`: Total sin intereses

## 3. REGISTRO DE INGRESOS/FLUJOS (tb_ingresos)
Cada pago realizado genera un registro de ingreso con clasificación:

### Tipos de Ingresos:
- **Enganche=1**: Pago del enganche inicial
- **Mensualidad=1**: Pago de mensualidad regular
- **AbonoCapital=1**: Abono adicional a capital
- **Comision=1**: Pago de comisión a vendedor

### Formas de Pago:
- `Efectivo`: Monto en efectivo
- `Transferencia`: Monto por transferencia
- `Cheque`: Monto por cheque
- `Tarjeta`: Monto por tarjeta

## 4. CÁLCULO Y REGISTRO DE COMISIONES (tb_comisiones)
Posterior al registro de ventas, se calculan las comisiones:

### Proceso de Comisiones:
- **Comisión por Apartado**: `ComisionApartado` al momento de reservar
- **Comisión Total**: `ComisionTotal` al completar la venta
- **Estados**: Cobrada=0 (Pendiente), Cobrada=1 (Pagada)
- **SubComisiones**: Para vendedores con estructura jerárquica

## 5. FLUJO DE COBRANZA Y SEGUIMIENTO

### Estados de Cobranza:
1. **Mensualidad Pendiente**: `Cobrado=FALSE`
2. **Mensualidad Vencida**: `DATEDIFF(FechaFinal,NOW()) < -5`
3. **Mensualidad Pagada**: `Cobrado=TRUE`
4. **Con Intereses**: `Interes > 0` (penalización por mora)

### Proceso de Pago:
1. Cliente realiza pago
2. Se registra en `tb_ingresos` con clasificación
3. Se actualiza `tb_cobranza.Cobrado=TRUE`
4. Se actualiza `tb_cobranza.TotalPagado`
5. Se actualiza `tb_ventas.TotalPagado`

## 6. GENERACIÓN DE DOCUMENTOS

### Recibos Generados:
- **Recibo de Apartado**: Para pagos de enganche
- **Recibo de Venta**: Para el contrato final
- **Recibo de Capital**: Para abonos adicionales
- **Estado de Cuenta**: Resumen de pagos y pendientes

### Archivos Legacy Relacionados:
- `ventas_recibo_apartado.php`: Recibo de apartado
- `ventas_recibo_venta.php`: Recibo de venta
- `ventas_recibo_capital.php`: Recibo de abono
- `ventas_estado_cuenta.php`: Estado de cuenta

## 7. SEGUIMIENTO DE DEPÓSITOS BANCARIOS

### Conciliación Bancaria:
- Archivo: `ventas_depositos_bancarios.php`
- Relaciona pagos con depósitos bancarios
- Controla referencias bancarias
- Valida montos y fechas

## 8. REPORTES Y ANÁLISIS

### Reportes Disponibles:
- **Estado de Cuenta**: Pagos realizados y pendientes
- **Comisiones de Vendedor**: Tracking de comisiones
- **Flujo de Ingresos**: Análisis de ingresos por período
- **Cobranza Vencida**: Seguimiento de morosos

## TAREAS PARA MIGRACIÓN A CODEIGNITER 4

### Prioridad Alta:
1. **Migrar Entity de Venta**: Crear `app/Entities/Venta.php` completa
2. **Migrar Entity de Cobranza**: Crear `app/Entities/Cobranza.php`
3. **Migrar Entity de Ingreso**: Reemplazar "flujos" por "ingresos"
4. **Migrar Entity de Comisión**: Integrar cálculo de comisiones

### Prioridad Media:
5. **Controlador de Ventas**: `AdminVentasController.php`
6. **Servicio de Cobranza**: Lógica de pagos y fechas
7. **Servicio de Comisiones**: Cálculo automático
8. **Generador de Recibos**: PDF con dompdf

### Prioridad Baja:
9. **Dashboard de Cobranza**: Métricas y KPIs
10. **Reportes**: Migrar reportes legacy
11. **Notificaciones**: Email/SMS para pagos vencidos
12. **API REST**: Para integraciones externas

## CONSIDERACIONES TÉCNICAS

### Mejoras Propuestas:
- **Usar Entidades** en lugar de arrays asociativos
- **Servicios dedicados** para lógica de negocio
- **Validaciones robustas** con CodeIgniter 4
- **Transacciones de base de datos** para integridad
- **Logs detallados** para auditoría
- **Cache** para consultas frecuentes

### Campos Importantes a Mantener:
- Todos los campos de control de fechas
- Referencia de folios internos
- Tracking de usuarios y cambios
- Estados de cobrado/pendiente
- Cálculo de intereses automático

## CRONOGRAMA SUGERIDO

### Semana 1: Entities y Models
- Crear todas las entidades necesarias
- Migrar modelos con relaciones

### Semana 2: Controladores Core
- Ventas, Cobranza, Ingresos, Comisiones

### Semana 3: Servicios y Lógica
- Cálculos financieros
- Generación de documentos

### Semana 4: UI y Reportes
- Interfaces de usuario
- Reportes y dashboards

---

**Nota**: Este módulo de ventas es el CORE BUSINESS del sistema, por lo que debe migrarse con la máxima precisión y sin pérdida de funcionalidad.