# ANÁLISIS COMPLETO Y MIGRACIÓN DEL MÓDULO DE VENTAS LEGACY

## 📋 RESUMEN EJECUTIVO

Este documento analiza exhaustivamente el sistema de ventas legacy de ANVAR Inmobiliaria para su migración completa a CodeIgniter 4. El sistema actual maneja un complejo proceso que incluye apartados, ventas a crédito, contado, cálculos de amortización, intereses moratorios, comisiones y generación automática de documentos.

### Flujo de Negocio Identificado
```
Prospecto → Firma → Cliente → Expediente → Registro Venta → Ingreso $ → Bloqueo Lote → Generación Documentos Automática
```

---

## 🗄️ ANÁLISIS DE BASE DE DATOS LEGACY

### Tablas Principales del Sistema de Ventas

#### 1. **tb_ventas** (Tabla Central)
```sql
-- Estructura Legacy Identificada
IdVenta (int, PK, AUTO_INCREMENT)
Folio (varchar) - Folio interno personalizable
Total (double) - Precio final con descuentos/intereses
TotalPagado (double) - Monto acumulado pagado
Forma (int) - Tipo de pago (1=Contado, 2=Crédito)
Proyecto, Manzana, Lote (int + varchar) - Relaciones desnormalizadas
Vendedor, Cliente (int + varchar) - Relaciones con nombres
Tipo (int) - Tipo de transacción
Estado (int) - Estados: 0=Proceso, 1=Apartado, 2=Vendido, 3=Cancelado
Fecha, Hora - Timestamp de creación
Anticipo, AnticipoPagado - Montos de enganche
Credito (tinyint) - Bandera de crédito
Cobrado (tinyint) - Bandera de pago completo
AnticipoCredito, AnticipoCobrado - Control de enganches
Area (double) - Área del lote vendido
Descuento, TipoDescuento, TipoAnticipo - Configuraciones comerciales
ComisionApartado, ComisionTotal - Comisiones calculadas
IntervaloCancelacion, DiasCancelacion - Políticas de cancelación
FechaCarga, ContratoCargado - Control de documentos
```

#### 2. **tb_ventas_amortizaciones** (Cálculos de Financiamiento)
```sql
IdAmortizacion (int, PK)
Venta (text) - Venta encriptada (ID)
NVenta (int) - ID real de la venta
Cliente, Cliente2 - Clientes principales y secundarios
Lote (text) - Información encriptada del lote
Meses (double) - Plazo total del financiamiento
Recibido (double) - Monto de enganche recibido
Vendedor (int) - ID del vendedor
FechaI (varchar) - Fecha inicio de pagos
Financiar (double) - Monto a financiar
MesesI (double) - Meses sin intereses
Interes (double) - Tasa de interés anual
Enganche (double) - Monto de enganche requerido
Total (double) - Total antes de intereses
Anualidades (text) - Configuración de pagos especiales
TotalTotal (double) - Total final con intereses
Descuento (double) - Descuentos aplicados
```

#### 3. **tb_ventas_documentos** (Control de Documentos)
```sql
IdDetalle (int, PK)
Venta (int) - ID de la venta
Documento (varchar) - Nombre del documento generado
Fecha, Hora - Timestamp de generación
Usuario, NUsuario - Usuario que generó
IP (varchar) - Control de auditoría
```

#### 4. **tb_ventas_mensajes** (Bitácora de Ventas)
```sql
IdMensaje (int, PK)
Descripcion (text) - Mensaje o nota
Fecha, Hora - Timestamp
Usuario, NUsuario - Usuario que registra
Venta (int) - ID de la venta relacionada
Estatus (int) - Estado del mensaje
```

#### 5. **tb_ventas_estatus** (Estados Financieros)
```sql
IdVenta (int, PK)
Cliente, NCliente - Información del cliente
Venta (int) - ID de la venta
Total, TotalPagado, AnticipoPagado - Montos financieros
PagadoCobranza (double) - Pagos por cobranza
Diferencia (double) - Saldo pendiente
Estatus (int) - Estado financiero
```

### Tablas Relacionadas (Cobranza e Intereses)

#### **tb_cobranza** (Sistema de Mensualidades)
```sql
-- Campos críticos identificados:
TipoCredito (1=Apartado, 2=Mensualidad)
DiasCredito, FechaFinal - Control de vencimientos
Interes (double) - Intereses moratorios acumulados
TotalSI (double) - Total sin intereses (backup)
MesesInteres (int) - Contador de meses con mora
Liquidado (tinyint) - Bandera de liquidación
TotalSI (double) - Total original antes de intereses
```

#### **tb_cobranza_intereses** (Cálculo de Mora)
```sql
IdInteres (int, PK)
Cobranza (int) - ID de la cobranza
Interes (double) - Monto del interés
TotalInteres (double) - Interés total acumulado
Fecha (date) - Fecha de aplicación
```

---

## 🏗️ ARQUITECTURA DEL SISTEMA LEGACY

### Flujos de Proceso Identificados

#### 1. **Proceso de Apartado**
```php
// Lógica identificada en funciones.php líneas 3115-3180
1. Validar lote disponible (Estado 0 o 1)
2. Calcular enganche según configuración
3. Registrar venta con Estado=1 (Apartado)
4. Crear cobranza por saldo de enganche
5. Registrar ingresos por forma de pago
6. Actualizar saldos en cuentas
7. Bloquear lote (Estado=2)
8. Calcular comisiones
```

#### 2. **Proceso de Confirmación de Venta**
```php
// Flujo crítico de negocio
1. Verificar pago completo de enganche
2. Cambiar estado a 2 (Vendido)
3. Generar tabla de amortización
4. Crear cobranzas mensuales
5. Generar documentos automáticos
6. Activar comisiones totales
```

#### 3. **Sistema de Cálculo de Amortización**
```php
// ventas_credito_presupuesto.php - Lógicas identificadas:
- Cálculo de cuota mensual: (monto_financiar - anualidades) / meses
- Manejo de meses sin intereses
- Aplicación de interés compuesto
- Redondeo configurable de mensualidades
- Pagos especiales (anualidades)
- Cálculo de intereses moratorios
```

### Sistema de Intereses Moratorios
```php
// Funciones.php líneas 111-150 - Lógica automática:
1. Detectar cobranzas vencidas (>5 días)
2. Aplicar interés según configuración del proyecto
3. Actualizar totales en cobranza y venta
4. Registrar en tb_cobranza_intereses
5. Marcado especial por fechas (>= 2023-12-01)
```

---

## 📄 SISTEMA DE DOCUMENTOS AUTOMÁTICOS

### Documentos Identificados

#### 1. **Estado de Cuenta / Amortización** (`ver_amortizacion.php`)
- **Función**: Generar tabla de pagos programados
- **Datos**: Cronograma completo de mensualidades
- **Cálculos**: Intereses, saldos, fechas de vencimiento
- **Formato**: PDF multipágina con paginado automático

#### 2. **Carátula de Expediente** (`ventas_caratula.php`)
- **Función**: Documento de identificación del cliente
- **Datos**: Info personal, lote, proyecto, vendedor
- **Uso**: Portada del expediente físico
- **Personalización**: Por empresa (logos, datos)

#### 3. **Recibos Automáticos**
```php
// Tipos identificados:
- Recibo de apartado
- Recibo de enganche  
- Recibo de venta
- Recibo de mensualidad
- Recibo de abono
- Recibo de comisión
```

#### 4. **Contratos Automáticos**
- **Ubicación**: `./documentos/contrato_*.docx`
- **Generación**: Plantillas con reemplazo de variables
- **Personalización**: Por proyecto y tipo de cliente

#### 5. **Documentos Legales**
```php
// Archivos identificados:
- Aviso de privacidad personalizado
- Memorándum de operaciones
- Requerimientos de documentación
- Estados de cuenta consolidados
```

---

## 🧮 ANÁLISIS DE CÁLCULOS CRÍTICOS

### 1. **Cálculo de Cuotas Mensuales**
```php
// Lógica identificada en ventas_credito_presupuesto.php:
$cuota_mensual = ($monto_financiar - $total_anualidades) / $meses_total;

// Con interés compuesto:
$interes_mensual = ($interes_anual / 100) / 12;
$cuota_con_interes = $capital * $interes_mensual / (1 - pow(1 + $interes_mensual, -$meses));

// Redondeo configurable:
if ($redondear == 'true') {
    $cuota = number_format($cuota, 0);
} else {
    $cuota = number_format($cuota, 2);
}
```

### 2. **Sistema de Intereses Moratorios**
```php
// Lógica automática en funciones.php:
// Para proyectos específicos (1,2,3,4)
if (DATEDIFF(fecha_final, NOW()) < -5 && !cobrado && interes == 0) {
    $interes_mora = configuracion.InteresMora; // Cantidad fija
    // O alternativamente: (saldo_pendiente * tasa_mora) / 100
    
    // Actualizar cobranza
    UPDATE tb_cobranza SET 
        TotalSI = Total,
        Total = Total + interes_mora,
        Interes = Interes + interes_mora,
        MesesInteres = MesesInteres + 1;
}
```

### 3. **Cálculo de Comisiones**
```php
// Sistema identificado:
- ComisionApartado: % sobre apartado
- ComisionTotal: % sobre venta total
- Diferenciación por tipo de vendedor (vendedor/subvendedor)
- Configuración por proyecto
```

---

## 🚨 PROBLEMAS Y LIMITACIONES IDENTIFICADAS

### 1. **Arquitectura Legacy**
- ❌ Código procedural PHP mezclado con lógica de negocio
- ❌ Consultas SQL embebidas sin ORM
- ❌ Datos desnormalizados (NProyecto, NUsuario, etc.)
- ❌ Encriptación de IDs innecesaria y compleja
- ❌ Sesiones PHP sin framework de seguridad

### 2. **Base de Datos**
- ❌ Campos TEXT para datos numéricos
- ❌ Falta de claves foráneas explícitas
- ❌ Nombres de campos inconsistentes
- ❌ Tablas de respaldo sin estrategia clara

### 3. **Lógica de Negocio**
- ❌ Cálculos distribuidos en múltiples archivos
- ❌ Reglas de negocio hardcodeadas
- ❌ Falta de validaciones consistentes
- ❌ Estados de venta ambiguos

---

## 🎯 PROPUESTA DE MIGRACIÓN CI4

### 1. **Nueva Estructura de Entities**

#### **VentaEntity.php** (Ya existente - Mejorar)
```php
// Campos adicionales requeridos:
protected $casts = [
    'meses_financiamiento'     => 'integer',
    'tasa_interes_anual'       => 'float',
    'meses_sin_intereses'      => 'integer',
    'cuota_mensual'           => 'float',
    'fecha_inicio_pagos'      => 'date',
    'require_plan_pagos'      => 'boolean',
    'total_anualidades'       => 'float',
    'configuracion_redondeo'  => 'boolean',
];

// Métodos de negocio:
public function calcularCuotaMensual(): float
public function generarPlanPagos(): array
public function aplicarInteresMoratorio(): void
public function estaVencidoApartado(): bool
```

#### **VentaAmortizacionEntity.php** (Nueva)
```php
class VentaAmortizacion extends Entity
{
    protected $casts = [
        'ventas_id'               => 'integer',
        'numero_pago'             => 'integer', 
        'monto_capital'           => 'float',
        'monto_interes'           => 'float',
        'cuota_mensual'           => 'float',
        'saldo_pendiente'         => 'float',
        'fecha_vencimiento'       => 'date',
        'esta_pagado'            => 'boolean',
        'fecha_pago'             => 'datetime',
        'monto_pagado'           => 'float',
        'dias_mora'              => 'integer',
        'interes_moratorio'      => 'float',
    ];
}
```

#### **VentaDocumentoEntity.php** (Nueva)
```php
class VentaDocumento extends Entity
{
    protected $casts = [
        'ventas_id'          => 'integer',
        'tipo_documento'     => 'string',  // amortizacion, caratula, contrato, etc.
        'nombre_archivo'     => 'string',
        'ruta_archivo'       => 'string',
        'generado_auto'      => 'boolean',
        'fecha_generacion'   => 'datetime',
        'usuario_generacion' => 'integer',
        'parametros_json'    => 'json',     // Parámetros usados para generar
    ];
}
```

### 2. **Servicios Especializados**

#### **VentasService.php** (Mejorar existente)
```php
class VentasService 
{
    // Métodos principales del flujo de negocio
    public function crearApartado(array $datos): Venta
    public function confirmarVentaCredito(int $ventaId, array $parametros): bool
    public function confirmarVentaContado(int $ventaId): bool
    public function cancelarVenta(int $ventaId, string $motivo): bool
    
    // Cálculos financieros
    public function calcularEnganche(int $loteId, array $config): array
    public function calcularComisiones(int $ventaId): array
    public function aplicarDescuento(int $ventaId, float $descuento, string $tipo): bool
}
```

#### **AmortizacionService.php** (Nuevo)
```php
class AmortizacionService
{
    public function generarTablaAmortizacion(int $ventaId, array $parametros): array
    public function calcularCuotaMensual(float $capital, float $tasa, int $meses): float
    public function aplicarInteresesMoratorios(): void
    public function reestructurarCredito(int $ventaId, array $nuevosParametros): bool
    public function calcularSaldosPendientes(int $ventaId): array
}
```

#### **DocumentosVentaService.php** (Nuevo)
```php
class DocumentosVentaService
{
    public function generarEstadoCuenta(int $ventaId): string
    public function generarCaratula(int $ventaId, int $tipoCliente = 0): string
    public function generarContrato(int $ventaId): string
    public function generarAvisoPrivacidad(int $ventaId): string
    public function generarMemorandum(int $ventaId): string
    public function generarRequerimientos(int $ventaId): string
    
    // Control de documentos
    public function marcarDocumentoGenerado(int $ventaId, string $tipo, string $archivo): void
    public function obtenerDocumentosGenerados(int $ventaId): array
}
```

### 3. **Controladores Especializados**

#### **AdminVentasController.php** (Actualizar existente)
```php
// Endpoints principales:
POST   /admin/ventas                    // Crear apartado
PUT    /admin/ventas/{id}/confirmar     // Confirmar venta
PUT    /admin/ventas/{id}/cancelar      // Cancelar venta
GET    /admin/ventas/{id}/amortizacion  // Ver tabla de pagos
POST   /admin/ventas/{id}/documentos    // Generar documentos
PUT    /admin/ventas/{id}/reestructurar // Reestructurar crédito
```

#### **VentasDocumentosController.php** (Nuevo)
```php
// Endpoints especializados:
GET    /admin/ventas/{id}/documentos/estado-cuenta
GET    /admin/ventas/{id}/documentos/caratula/{tipo?}
GET    /admin/ventas/{id}/documentos/contrato
POST   /admin/ventas/{id}/documentos/generar-lote
```

### 4. **Modelos Especializados**

#### **VentaModel.php** (Mejorar existente)
```php
public function getVentasConEstadisticas(array $filtros = []): array
public function getVentasVencidas(): array
public function getVentasPorVendedor(int $vendedorId): array
public function getVentasPorProyecto(int $proyectoId): array
public function buscarPorFolio(string $folio): ?Venta
```

#### **VentaAmortizacionModel.php** (Nuevo)
```php
public function getPagosPendientes(int $ventaId): array
public function getPagosVencidos(): array
public function marcarPagado(int $amortizacionId, float $monto): bool
public function aplicarInteresMoratorio(int $amortizacionId, float $interes): bool
```

---

## 🔧 PLAN DE MIGRACIÓN DETALLADO

### **Fase 1: Preparación y Análisis** (1-2 semanas)
1. ✅ **Análisis completo del sistema legacy** (Completado)
2. **Mapeo de datos y validación de integridad**
3. **Identificación de casos edge y reglas especiales**
4. **Documentación de configuraciones por empresa/proyecto**

### **Fase 2: Infraestructura Base** (2-3 semanas)
1. **Actualizar Entities existentes**
   - Agregar campos faltantes a VentaEntity
   - Crear VentaAmortizacionEntity
   - Crear VentaDocumentoEntity

2. **Crear migraciones de BD**
   - Normalizar datos desnormalizados
   - Crear índices optimizados
   - Migrar datos históricos

3. **Implementar Services base**
   - VentasService (expandir)
   - AmortizacionService (nuevo)
   - DocumentosVentaService (nuevo)

### **Fase 3: Lógica de Negocio** (3-4 semanas)
1. **Sistema de apartados y confirmaciones**
2. **Cálculos de amortización y cuotas**
3. **Sistema de intereses moratorios automático**
4. **Manejo de comisiones**
5. **Validaciones y reglas de negocio**

### **Fase 4: Generación de Documentos** (2-3 semanas)
1. **Motor de templates (TCPDF/DomPDF)**
2. **Migración de plantillas existentes**
3. **Sistema de generación automática**
4. **Control y auditoría de documentos**

### **Fase 5: Interfaz y UX** (2-3 semanas)
1. **Vistas optimizadas con AdminLTE**
2. **Formularios reactivos con validación**
3. **Reportes y dashboards**
4. **Sistema de búsqueda avanzada**

### **Fase 6: Testing y Deploy** (2 semanas)
1. **Testing unitario de cálculos**
2. **Testing de integración**
3. **Migración de datos en paralelo**
4. **Capacitación de usuarios**

---

## ⚡ CONSIDERACIONES TÉCNICAS CRÍTICAS

### **1. Migración de Datos**
```sql
-- Mapeo crítico de estados:
Legacy Estado 0 → CI4 'proceso'
Legacy Estado 1 → CI4 'apartado' 
Legacy Estado 2 → CI4 'vendido'
Legacy Estado 3 → CI4 'cancelado'

-- Desnormalización a normalización:
tb_ventas.NProyecto → proyectos.nombre via proyectos_id
tb_ventas.NCliente → clientes.nombres via clientes_id
```

### **2. Cálculos Financieros**
- ⚠️ **Precisión decimal**: Usar `DECIMAL(15,2)` para montos
- ⚠️ **Redondeo**: Mantener configuración por proyecto
- ⚠️ **Fechas**: Considerar días hábiles vs naturales
- ⚠️ **Intereses**: Validar fórmulas de cálculo compuesto

### **3. Rendimiento**
- **Índices**: Crear índices en campos de búsqueda frecuente
- **Cache**: Implementar cache para cálculos complejos
- **Paginación**: Usar paginación para listas grandes
- **Jobs**: Procesar intereses moratorios en background

### **4. Seguridad**
- **Autorización**: Usar Shield para control de acceso
- **Validación**: Validar todos los inputs financieros
- **Auditoría**: Log de todas las operaciones críticas
- **Backup**: Estrategia de respaldo automático

---

## 📊 CASOS DE USO CRÍTICOS

### **Caso 1: Apartado de Lote**
```php
// Flujo completo:
1. Validar lote disponible
2. Validar cliente existente
3. Calcular enganche según configuración
4. Registrar apartado con fecha límite
5. Procesar pago inicial
6. Bloquear lote
7. Generar documentos iniciales
8. Calcular comisión de apartado
9. Enviar notificaciones
```

### **Caso 2: Confirmación de Venta a Crédito**
```php
// Flujo completo:
1. Verificar apartado válido
2. Validar pago de enganche completo
3. Generar tabla de amortización
4. Crear cobranzas mensuales
5. Cambiar estado a "venta_credito"
6. Generar contrato automático
7. Activar comisión total
8. Programar recordatorios de pago
```

### **Caso 3: Aplicación de Intereses Moratorios**
```php
// Proceso automático diario:
1. Identificar cobranzas vencidas (>5 días)
2. Calcular interés según configuración
3. Aplicar interés a cobranza y venta
4. Registrar en historial de intereses
5. Enviar notificaciones al cliente
6. Generar reporte para administración
```

---

## 🎯 ENTREGABLES FINALES

### **1. Código**
- ✅ Entities actualizadas y nuevas
- ✅ Services especializados
- ✅ Controllers optimizados
- ✅ Models con consultas eficientes
- ✅ Vistas responsive con AdminLTE

### **2. Base de Datos**
- ✅ Estructura normalizada
- ✅ Migraciones de datos legacy
- ✅ Índices optimizados
- ✅ Triggers para auditoría

### **3. Documentación**
- ✅ Manual de usuario
- ✅ Documentación técnica
- ✅ Casos de prueba
- ✅ Manual de configuración

### **4. Herramientas**
- ✅ Dashboard de ventas
- ✅ Reportes automáticos
- ✅ Sistema de notificaciones
- ✅ Herramientas de auditoría

---

## 🚀 BENEFICIOS ESPERADOS

### **Técnicos**
- 🔧 **Código mantenible** con arquitectura MVC
- 🔧 **Base de datos normalizada** con integridad referencial
- 🔧 **Testing automatizado** para cálculos críticos
- 🔧 **Escalabilidad** para crecimiento futuro

### **Funcionales**
- 📈 **Cálculos precisos** sin errores de redondeo
- 📈 **Documentos consistentes** con templates unificadas
- 📈 **Reportes en tiempo real** para toma de decisiones
- 📈 **Auditoría completa** de todas las operaciones

### **Operativos**
- ⚡ **Velocidad mejorada** en consultas y reportes
- ⚡ **Interfaz intuitiva** para reducir errores de captura
- ⚡ **Automatización** de procesos repetitivos
- ⚡ **Integración** con otros módulos del sistema

---

**📅 Estimación Total: 12-16 semanas**
**👥 Equipo Requerido: 2-3 desarrolladores + 1 QA**
**💼 Prioridad: ALTA - Módulo crítico del negocio**

---

*Documento generado por análisis exhaustivo del sistema legacy ANVAR Inmobiliaria*
*Fecha: 07 de Enero 2025*
*Versión: 1.0*