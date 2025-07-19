# 📊 MÓDULO DE ESTADO DE CUENTA E2E - PLAN DE DESARROLLO

## 🎯 **OBJETIVO GENERAL**
Desarrollar un módulo completo de estado de cuenta que permita gestionar mensualidades, tracking de pagos y múltiples contratos por cliente usando metodología Entity-First y arquitectura de helpers.

---

## 📋 **FASE 1: FUNDAMENTOS ENTITY-FIRST**

### **Subtarea 1.1: Crear Entity TablaAmortizacion**
```php
// app/Entities/TablaAmortizacion.php
- Propiedades calculadas para días de atraso
- Métodos para determinar estado automáticamente
- Cálculo de intereses moratorios
- Validaciones de business logic
```
**Tiempo estimado:** 2 horas  
**Dependencias:** Ninguna  
**Test E2E:** Crear amortización y verificar cálculos automáticos

### **Subtarea 1.2: Crear Entity PagoVenta**
```php
// app/Entities/PagoVenta.php
- Generación automática de folio
- Validaciones de monto vs saldo pendiente
- Métodos para aplicar pagos parciales
- Estados de pago automáticos
```
**Tiempo estimado:** 2 horas  
**Dependencias:** TablaAmortizacion Entity  
**Test E2E:** Aplicar pago y verificar actualización de estados

### **Subtarea 1.3: Extender Entity Venta**
```php
// app/Entities/Venta.php (actualizar)
- Método generarTablaAmortizacion()
- Cálculo de saldo total pendiente
- Estado de liquidación automático
- Relación con múltiples contratos
```
**Tiempo estimado:** 1.5 horas  
**Dependencias:** Entities anteriores  
**Test E2E:** Crear venta y verificar generación de tabla

---

## 📋 **FASE 2: MODELS CON BUSINESS LOGIC**

### **Subtarea 2.1: Crear TablaAmortizacionModel**
```php
// app/Models/TablaAmortizacionModel.php
- getByVenta($ventaId) - obtener plan de pagos
- getMensualidadesPendientes($ventaId) 
- getMensualidadesVencidas($clienteId)
- actualizarAtrasos() - job diario
- aplicarPago($pagoData) - actualizar estado
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Entity TablaAmortizacion  
**Test E2E:** Consultar mensualidades y verificar estados

### **Subtarea 2.2: Crear PagoVentaModel**
```php
// app/Models/PagoVentaModel.php
- getHistorialPagos($ventaId)
- getPagosPendientes($clienteId)
- procesarPago($pagoData) - lógica completa
- cancelarPago($pagoId, $motivo)
- getEstadisticasPagos($clienteId)
```
**Tiempo estimado:** 3 horas  
**Dependencias:** TablaAmortizacionModel  
**Test E2E:** Procesar pago completo y verificar actualización

### **Subtarea 2.3: Extender VentaModel**
```php
// app/Models/VentaModel.php (actualizar)
- getVentasActivasCliente($clienteId)
- generarAmortizacion($ventaId) - crear plan pagos
- getSaldoTotal($ventaId)
- getResumenFinanciero($clienteId)
```
**Tiempo estimado:** 2 horas  
**Dependencias:** Models anteriores  
**Test E2E:** Obtener resumen completo del cliente

---

## 📋 **FASE 3: HELPERS ESPECIALIZADOS**

### **Subtarea 3.1: Crear Helper estado_cuenta_helper.php**
```php
// app/Helpers/estado_cuenta_helper.php
- generar_resumen_cliente($clienteId)
- calcular_dias_atraso($fechaVencimiento)
- calcular_interes_moratorio($monto, $dias, $tasa)
- formatear_estado_cuenta($datos)
- generar_alertas_vencimiento($clienteId)
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Models de Fase 2  
**Test E2E:** Generar estado completo y verificar cálculos

### **Subtarea 3.2: Crear Helper amortizacion_helper.php**
```php
// app/Helpers/amortizacion_helper.php
- generar_tabla_amortizacion($ventaData, $configFinanciera)
- calcular_pago_mensual($capital, $tasa, $plazo)
- distribuir_pago($monto, $saldoPendiente, $intereses)
- actualizar_saldos_tabla($ventaId, $pagoAplicado)
```
**Tiempo estimado:** 4 horas  
**Dependencias:** Helper estado_cuenta  
**Test E2E:** Crear tabla completa y aplicar múltiples pagos

### **Subtarea 3.3: Extender recibo_helper.php**
```php
// app/Helpers/recibo_helper.php (actualizar)
- generar_recibo_mensualidad($pagoId)
- generar_estado_cuenta_pdf($clienteId)
- template para mensualidades con mora
- template para liquidación parcial
```
**Tiempo estimado:** 2 horas  
**Dependencias:** Helpers anteriores  
**Test E2E:** Generar recibos y PDFs, verificar datos

---

## 📋 **FASE 4: CONTROLLERS ADMIN**

### **Subtarea 4.1: Crear AdminEstadoCuentaController**
```php
// app/Controllers/Admin/AdminEstadoCuentaController.php
- index() - dashboard general
- cliente($clienteId) - estado específico
- venta($ventaId) - detalle de propiedad
- mensualidades() - gestión global
- alertas() - vencimientos próximos
```
**Tiempo estimado:** 4 horas  
**Dependencias:** Todas las anteriores  
**Test E2E:** Navegar por todas las vistas admin

### **Subtarea 4.2: Crear AdminMensualidadesController**
```php
// app/Controllers/Admin/AdminMensualidadesController.php
- index() - lista global de mensualidades
- pendientes() - solo pendientes/vencidas
- aplicarPago() - formulario aplicación
- procesarPago() - lógica backend
- reporteMensual() - PDF mensualidades
```
**Tiempo estimado:** 4 horas  
**Dependencias:** AdminEstadoCuentaController  
**Test E2E:** Proceso completo de aplicar pago

### **Subtarea 4.3: Integrar con AdminVentasController**
```php
// app/Controllers/Admin/AdminVentasController.php (actualizar)
- Generar tabla amortización al crear venta
- Botón "Ver Estado Cuenta" en vista show
- Hook post-venta para crear mensualidades
- Integración con recibos existentes
```
**Tiempo estimado:** 2 horas  
**Dependencias:** Controllers anteriores  
**Test E2E:** Crear venta → Ver estado → Aplicar pago

---

## 📋 **FASE 5: CONTROLLERS CLIENTE**

### **Subtarea 5.1: Crear Cliente/EstadoCuentaController**
```php
// app/Controllers/Cliente/EstadoCuentaController.php
- index() - resumen todas las propiedades
- propiedad($ventaId) - detalle específico
- historialPagos($ventaId) - pagos realizados
- proximosVencimientos() - calendar view
- descargarEstado($ventaId) - PDF
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Helpers y Models  
**Test E2E:** Cliente navega por su portal completo

### **Subtarea 5.2: Crear Cliente/PagosController**
```php
// app/Controllers/Cliente/PagosController.php
- index() - próximos pagos
- realizar($mensualidadId) - formulario pago
- comprobante($pagoId) - subir evidencia
- historial() - todos los pagos
```
**Tiempo estimado:** 3 horas  
**Dependencias:** EstadoCuentaController cliente  
**Test E2E:** Cliente realiza pago completo con comprobante

---

## 📋 **FASE 6: VISTAS ADMIN**

### **Subtarea 6.1: Dashboard Estado de Cuenta**
```html
<!-- app/Views/admin/estado-cuenta/index.php -->
- Cards de estadísticas generales
- Tabla de clientes con saldos
- Gráficos de mensualidades por estado
- Alertas de vencimientos críticos
```
**Tiempo estimado:** 4 horas  
**Dependencias:** AdminEstadoCuentaController  
**Test E2E:** Verificar datos en tiempo real

### **Subtarea 6.2: Vista Detalle Cliente**
```html
<!-- app/Views/admin/estado-cuenta/cliente.php -->
- Resumen financiero del cliente
- Lista de todas sus propiedades
- Timeline de pagos recientes
- Botones de acción rápida
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Dashboard  
**Test E2E:** Navegación entre propiedades del cliente

### **Subtarea 6.3: Vista Gestión Mensualidades**
```html
<!-- app/Views/admin/mensualidades/index.php -->
- DataTable con filtros avanzados
- Botones masivos (aplicar pagos)
- Modal para aplicar pago individual
- Exportación a Excel/PDF
```
**Tiempo estimado:** 4 horas  
**Dependencias:** AdminMensualidadesController  
**Test E2E:** Filtrar, aplicar pago, exportar

### **Subtarea 6.4: Formulario Aplicar Pago**
```html
<!-- app/Views/admin/mensualidades/aplicar-pago.php -->
- Cálculo automático de montos
- Selección de mensualidades a aplicar
- Validación en tiempo real
- Integración con recibos
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Vista gestión  
**Test E2E:** Aplicar pago completo con validaciones

---

## 📋 **FASE 7: VISTAS CLIENTE**

### **Subtarea 7.1: Portal Cliente - Estado de Cuenta**
```html
<!-- app/Views/cliente/estado-cuenta/index.php -->
- Dashboard con sus propiedades
- Indicadores de saldos pendientes
- Próximos vencimientos destacados
- Accesos rápidos a pagos
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Cliente/EstadoCuentaController  
**Test E2E:** Cliente ve su información completa

### **Subtarea 7.2: Detalle de Propiedad Cliente**
```html
<!-- app/Views/cliente/estado-cuenta/propiedad.php -->
- Información del inmueble
- Tabla de amortización completa
- Historial de pagos realizados
- Botón para realizar pago
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Portal cliente  
**Test E2E:** Ver detalle completo de propiedad

### **Subtarea 7.3: Sistema de Pagos Cliente**
```html
<!-- app/Views/cliente/pagos/ -->
- realizar.php - formulario de pago
- comprobante.php - subir evidencia
- confirmacion.php - recibo digital
```
**Tiempo estimado:** 4 horas  
**Dependencias:** Cliente/PagosController  
**Test E2E:** Proceso completo de pago por cliente

---

## 📋 **FASE 8: INTEGRACIONES**

### **Subtarea 8.1: Integrar con Sistema de Recibos**
```php
// Actualizar templates de recibos existentes
- Nuevo template para mensualidades
- Recibo de liquidación parcial
- Estado de cuenta en PDF
- Integración con email automático
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Sistema de recibos actual  
**Test E2E:** Generar todos los tipos de recibos

### **Subtarea 8.2: Sistema de Notificaciones**
```php
// app/Libraries/NotificacionesService.php
- Alertas de vencimiento (email/SMS)
- Notificaciones de pago aplicado
- Recordatorios automáticos
- Dashboard de notificaciones admin
```
**Tiempo estimado:** 4 horas  
**Dependencias:** Helpers y Controllers  
**Test E2E:** Envío automático de notificaciones

### **Subtarea 8.3: Hooks Post-Venta**
```php
// Integrar con flujo de ventas existente
- Auto-generar tabla amortización
- Crear primera mensualidad
- Notificar al cliente
- Actualizar dashboard
```
**Tiempo estimado:** 2 horas  
**Dependencias:** Sistema de ventas actual  
**Test E2E:** Venta completa → Estado automático

---

## 📋 **FASE 9: JOBS Y AUTOMATIZACIÓN**

### **Subtarea 9.1: Job Actualización Diaria**
```php
// app/Commands/ActualizarEstadosCommand.php
- Marcar mensualidades vencidas
- Calcular días de atraso
- Aplicar intereses moratorios
- Generar alertas automáticas
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Models y Helpers  
**Test E2E:** Ejecutar job y verificar cambios

### **Subtarea 9.2: Sistema de Reportes**
```php
// app/Services/ReportesEstadoCuentaService.php
- Reporte mensual de cobranza
- Estado de cartera por vendedor
- Proyección de ingresos
- Alertas de cartera vencida
```
**Tiempo estimado:** 4 horas  
**Dependencias:** Job anterior  
**Test E2E:** Generar reportes con datos reales

---

## 📋 **FASE 10: TESTING E2E**

### **Subtarea 10.1: Tests de Flujo Completo**
```php
// tests/integration/EstadoCuentaTest.php
- Crear venta → Generar amortización
- Aplicar pagos → Verificar estados
- Cliente consulta → Admin gestiona
- Vencimientos → Alertas automáticas
```
**Tiempo estimado:** 4 horas  
**Dependencias:** Todo el módulo  
**Test E2E:** Flujo completo automatizado

### **Subtarea 10.2: Tests de Edge Cases**
```php
// tests/integration/PagosEdgeCasesTest.php
- Pagos parciales múltiples
- Cancelación de pagos
- Liquidación anticipada
- Múltiples propiedades mismo cliente
```
**Tiempo estimado:** 3 horas  
**Dependencias:** Tests principales  
**Test E2E:** Casos límite del sistema

---

## 🎯 **CRONOGRAMA ESTIMADO**

| Fase | Tiempo | Prioridad | Entregable |
|------|--------|-----------|------------|
| **Fase 1-2** | 16 horas | Alta | Entities y Models funcionales |
| **Fase 3** | 9 horas | Alta | Helpers de cálculo |
| **Fase 4** | 10 horas | Alta | Controllers admin |
| **Fase 5** | 6 horas | Media | Controllers cliente |
| **Fase 6** | 14 horas | Alta | Vistas admin completas |
| **Fase 7** | 10 horas | Media | Portal cliente |
| **Fase 8** | 9 horas | Alta | Integraciones |
| **Fase 9** | 7 horas | Media | Automatización |
| **Fase 10** | 7 horas | Alta | Testing E2E |

**TOTAL:** ~88 horas de desarrollo

---

## ✅ **CRITERIOS DE ÉXITO E2E**

1. **Flujo Venta Completa:** Crear venta → Auto-generar mensualidades → Cliente ve estado
2. **Proceso de Pago:** Cliente/Admin aplica pago → Actualiza estados → Genera recibo
3. **Gestión Múltiples Propiedades:** Cliente con 3+ propiedades → Estados independientes
4. **Alertas Automáticas:** Vencimientos → Notificaciones → Dashboard actualizado
5. **Reportes Gerenciales:** Datos en tiempo real → Exportación → Toma de decisiones

---

## 🚀 **TECNOLOGÍAS Y METODOLOGÍAS**

- **Entity-First:** Business logic en Entities
- **Helper-Driven:** Funciones reutilizables
- **Test-Driven:** E2E desde inicio
- **API-Ready:** Preparado para frontend moderno
- **Mobile-First:** Responsive design
- **Security-First:** Validaciones y permisos

**¡Módulo completo y robusto para gestión de estado de cuenta empresarial!**