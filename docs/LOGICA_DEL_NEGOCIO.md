# LÓGICA DEL NEGOCIO - SISTEMA INMOBILIARIO ANVAR
### Análisis de Ingeniería Inversa del Sistema Legacy para Migración a CodeIgniter 4

---

## 📋 **INFORMACIÓN DEL DOCUMENTO**

- **Proyecto**: Sistema Inmobiliario ANVAR - Migración Legacy a CI4
- **Enfoque**: Análisis técnico de base de datos y flujos de negocio
- **Objetivo**: Documentar la lógica de ventas de lotes inmobiliarios para replicar en CI4
- **Fecha**: Junio 2025
- **Desarrollador**: Migración individual

---

## 🎯 **ENTIDADES PRINCIPALES DEL NEGOCIO**

### **Estructura Jerárquica Completa del Negocio Inmobiliario**
```
EMPRESA (tb_empresas)
├── Nombre, RazonSocial, RFC
├── Configuración Financiera
├── Logotipo y Representante Legal
│
├── PROYECTOS INMOBILIARIOS (tb_proyectos)
│   ├── Información General: Nombre, Clave, Ubicación
│   ├── Datos Legales: Ejido, Parcela, Escrituración
│   ├── Configuración Comercial: PenalizacionM, PenalizacionA
│   ├── Coordenadas Geográficas: Longitud, Latitud
│   │
│   ├── ETAPAS DE DESARROLLO (tb_divisiones) - CONTROL SECUENCIAL
│   │   ├── ETAPA 1 "Primera Sección" (Activa desde inicio)
│   │   │   ├── Estatus: ACTIVA (disponible para venta)
│   │   │   ├── Leyenda: Promocional de lanzamiento
│   │   │   ├── % Ventas: 0-100% (monitoreado)
│   │   │   │
│   │   │   ├── MANZANAS (tb_manzanas)
│   │   │   │   ├── Identificación: Nombre, Clave, Color
│   │   │   │   ├── Coordenadas específicas por manzana
│   │   │   │   │
│   │   │   │   ├── LOTES (tb_lotes) - UNIDAD VENDIBLE
│   │   │   │   │   ├── Datos Técnicos:
│   │   │   │   │   │   ├── Dimensiones: Frente, Fondo, LatIzq, LatDer
│   │   │   │   │   │   ├── Superficie: Area total, Construcción
│   │   │   │   │   │   ├── Colindancias: Norte, Sur, Oriente, Poniente
│   │   │   │   │   │   └── Coordenadas GPS específicas
│   │   │   │   │   │
│   │   │   │   │   ├── Información Comercial:
│   │   │   │   │   │   ├── Precio por m², Total
│   │   │   │   │   │   ├── TipoLote: Residencial, Comercial, Esquina
│   │   │   │   │   │   ├── Estado: Disponible(0), Apartado(1), Vendido(2), Suspendido(3)
│   │   │   │   │   │   └── Clave única del lote
│   │   │   │   │   │
│   │   │   │   │   └── PROCESO DE VENTA
│   │   │   │   │       │
│   │   │   │   │       ├── CLIENTES (tb_clientes)
│   │   │   │   │       │   ├── Persona Física: Nombre, RFC, CURP
│   │   │   │   │       │   ├── Persona Moral: RazonSocial, RFC
│   │   │   │   │       │   ├── Domicilio Completo
│   │   │   │   │       │   ├── Contacto: Telefono, Email
│   │   │   │   │       │   ├── Referencias: Personal y Laboral
│   │   │   │   │       │   ├── Estado Civil y Cónyuge
│   │   │   │   │       │   └── Acceso Portal Web
│   │   │   │   │       │
│   │   │   │   │       ├── VENDEDORES (tb_usuarios)
│   │   │   │   │       │   ├── Datos Personales
│   │   │   │   │       │   ├── Permisos Modulares Granulares
│   │   │   │   │       │   ├── Control de Apartados Fallidos
│   │   │   │   │       │   └── Estado: Activo, Suspendido
│   │   │   │   │       │
│   │   │   │   │       └── APARTADOS/VENTAS (tb_ventas)
│   │   │   │   │           ├── Información de Venta:
│   │   │   │   │           │   ├── Folio, FolioInterno
│   │   │   │   │           │   ├── Total, TotalPagado
│   │   │   │   │           │   ├── Anticipo, Descuento
│   │   │   │   │           │   ├── Estado: Proceso(0), Apartado(1), Vendido(2), Cancelado(3)
│   │   │   │   │           │   ├── DiasCancelacion (timer automático)
│   │   │   │   │           │   └── Fecha, Hora, Usuario
│   │   │   │   │           │
│   │   │   │   │           ├── FINANCIAMIENTO Y PLAN DE PAGOS (tb_cobranza)
│   │   │   │   │           │   ├── Estructura de Pagos:
│   │   │   │   │           │   │   ├── TipoCredito: Apartado(1), Mensualidad(2)
│   │   │   │   │           │   │   ├── Plazo: "A"(anticipo), 1,2,3...(mensualidad)
│   │   │   │   │           │   │   ├── Total, TotalPagado, TotalSI(sin intereses)
│   │   │   │   │           │   │   ├── FechaFinal, DiasCredito
│   │   │   │   │           │   │   └── Cobrado (boolean)
│   │   │   │   │           │   │
│   │   │   │   │           │   ├── SISTEMA DE INTERESES MORATORIOS:
│   │   │   │   │           │   │   ├── Interes (monto penalización)
│   │   │   │   │           │   │   ├── Auto-aplicación > 5 días vencido
│   │   │   │   │           │   │   ├── TotalSI preserva monto original
│   │   │   │   │           │   │   └── Diferencia Total-TotalSI = intereses
│   │   │   │   │           │   │
│   │   │   │   │           │   └── ESTADO DE CUENTA DINÁMICO:
│   │   │   │   │           │       ├── Pagos realizados vs pendientes
│   │   │   │   │           │       ├── Intereses moratorios acumulados
│   │   │   │   │           │       ├── Proyección vencimientos futuros
│   │   │   │   │           │       └── Simulación liquidación anticipada
│   │   │   │   │           │
│   │   │   │   │           ├── REGISTRO DE PAGOS (tb_ingresos)
│   │   │   │   │           │   ├── Formas de Pago:
│   │   │   │   │           │   │   ├── Efectivo, Cheque, Transferencia, Tarjeta
│   │   │   │   │           │   │   ├── Combinación múltiple en un pago
│   │   │   │   │           │   │   └── Referencias bancarias
│   │   │   │   │           │   │
│   │   │   │   │           │   ├── Conceptos:
│   │   │   │   │           │   │   ├── APARTADO: Pago inicial
│   │   │   │   │           │   │   ├── MENSUALIDAD: Pago regular
│   │   │   │   │           │   │   ├── CAPITAL: Adelanto mensualidades
│   │   │   │   │           │   │   ├── INTERES: Pago moratorios
│   │   │   │   │           │   │   └── LIQUIDACION: Pago final
│   │   │   │   │           │   │
│   │   │   │   │           │   └── Control Contable:
│   │   │   │   │           │       ├── Cuenta destino
│   │   │   │   │           │       ├── Fecha y hora operación
│   │   │   │   │           │       └── Usuario responsable
│   │   │   │   │           │
│   │   │   │   │           └── COMISIONES VENDEDORES (tb_comisiones)
│   │   │   │   │               ├── ComisionApartado: % sobre apartado
│   │   │   │   │               ├── ComisionTotal: % sobre venta total
│   │   │   │   │               ├── Estado: Pendiente, Pagada
│   │   │   │   │               ├── FechaPago, Ingreso vinculado
│   │   │   │   │               └── Cálculo automático por venta
│   │   │   │
│   │   ├── ETAPA 2 "Segunda Sección" (Se habilita al 90-100% Etapa 1)
│   │   │   ├── Estatus: SUSPENDIDA → ACTIVA (automático)
│   │   │   ├── Trigger: Porcentaje ventas Etapa 1
│   │   │   ├── Cambio masivo: Lotes Estado 3→0
│   │   │   └── [Misma estructura que Etapa 1]
│   │   │
│   │   └── ETAPA N "Sección Final"
│   │       └── [Secuencia hasta completar proyecto]
│   │
│   ├── SISTEMAS DE SOPORTE
│   │   │
│   │   ├── SIMULADOR DE PLAN DE PAGOS (simulador_capital.php)
│   │   │   ├── Cálculo mensualidades variables
│   │   │   ├── Simulación enganches múltiples
│   │   │   ├── Proyección intereses mora
│   │   │   ├── Comparación contado vs financiado
│   │   │   └── Liquidación anticipada
│   │   │
│   │   ├── GESTIÓN DOCUMENTAL
│   │   │   ├── Contratos de venta
│   │   │   ├── Comprobantes de pago
│   │   │   ├── Expedientes clientes
│   │   │   └── Documentos proyecto
│   │   │
│   │   ├── BITÁCORA Y AUDITORÍA (tb_bitacora)
│   │   │   ├── Movimientos del sistema
│   │   │   ├── Cambios críticos
│   │   │   ├── Errores y incidentes
│   │   │   └── Trazabilidad completa
│   │   │
│   │   └── REPORTES Y DASHBOARDS
│   │       ├── Ventas por etapa/proyecto
│   │       ├── Estado cobranza
│   │       ├── Comisiones vendedores
│   │       ├── Inventario disponible
│   │       └── Métricas financieras
│   │
│   └── PROCESOS AUTOMATIZADOS (Cron Jobs)
│       ├── Cancelación apartados vencidos
│       ├── Aplicación intereses moratorios
│       ├── Habilitación automática etapas
│       ├── Suspensión vendedores (3+ errores)
│       ├── Notificaciones vencimientos
│       └── Generación estados cuenta
```

---

## 📊 **ANÁLISIS DE TABLAS PRINCIPALES**

### **1. TABLA PROYECTOS (`tb_proyectos`)**
**Función**: Contenedor principal de desarrollo inmobiliario
- Relaciona: Empresa → Proyectos → Manzanas → Lotes
- Campos críticos: PenalizacionM, PenalizacionA (para intereses)

### **2. TABLA MANZANAS (`tb_manzanas`)**
**Función**: Agrupación de lotes dentro de una etapa de desarrollo
```sql
IdManzana, Nombre, Clave, Proyecto, Division, Color, Coordenadas
```
- **Relación**: Proyecto → Etapa/División (1:N) → Manzanas
- **División**: Etapas secuenciales del proyecto
- **Control de Disponibilidad**: Solo manzanas de etapas activas permiten ventas

**Lógica de Etapas de Desarrollo**:
- **Etapa 1**: Disponible desde el lanzamiento del proyecto
- **Etapas Subsecuentes**: Se habilitan automáticamente cuando la etapa anterior alcanza 90-100% de ventas
- **Una Etapa Activa**: Solo una etapa puede estar disponible para ventas simultáneamente

### **3. TABLA LOTES (`tb_lotes`)**
**Función**: Unidad vendible principal del negocio
```sql
IdLote, Clave, Proyecto, Manzana, Numero, Area, Precio, Total,
Frente, Fondo, LatIzq, LatDer, Norte, Sur, Poniente, Oriente,
Estado, NEstado, TipoLote, Construccion, Coordenadas
```

**Estados de Lote**:
- `Estado = 0`: **Disponible** (vendible - solo si etapa activa)
- `Estado = 1`: **Apartado** (reservado temporalmente) 
- `Estado = 2`: **Vendido** (venta confirmada)
- `Estado = 3`: **No disponible** (etapa no habilitada para venta)

**Control por Etapas**:
- Lotes en etapas no activas automáticamente tienen `Estado = 3`
- Al habilitar nueva etapa, lotes cambian a `Estado = 0` (disponibles)
- Sistema valida etapa activa antes de permitir apartados

**Tipos de Lote**:
- Residencial, Comercial, Preferencial/Esquina

### **4. TABLA CLIENTES (`tb_clientes`)**
**Función**: Datos del comprador
```sql
IdCliente, Nombre, RFC, CURP, Domicilio, Telefono, Email,
EstadoCivil, Conyuge, NombreReferenciaUno, TelefonoReferenciaUno,
Profesion, Empresa, Acceso, Verificada
```

### **5. TABLA VENTAS (`tb_ventas`)**
**Función**: Núcleo del proceso de venta
```sql
IdVenta, Folio, Total, TotalPagado, Proyecto, Manzana, Lote,
Vendedor, Cliente, Estado, Fecha, Credito, Anticipo, Area,
DiasCancelacion, ComisionApartado, ComisionTotal, Intereses
```

**Estados de Venta**:
- `Estado = 0`: **Borrador/Proceso**
- `Estado = 1`: **Apartado** (reserva temporal)
- `Estado = 2`: **Venta Confirmada**
- `Estado = 3`: **Cancelada**

### **6. TABLA COBRANZA (`tb_cobranza`)**
**Función**: Plan de pagos, gestión financiera y estado de cuenta
```sql
IdCobranza, Venta, Cliente, Total, TotalPagado, TotalSI,
FechaFinal, TipoCredito, Plazo, Interes, MesesInteres, 
Cobrado, Empresa, DiasCredito
```

**Nuevas Funcionalidades Identificadas**:
- **Plan de Pagos**: Generación automática de mensualidades
- **Estado de Cuenta**: Reporte detallado por cliente/venta
- **Simulador**: Cálculo de diferentes escenarios de pago
- **Intereses Moratorios**: Aplicación automática por mora
- **TotalSI**: Monto original sin intereses para cálculos

---

## 🔄 **FLUJO DE NEGOCIO PRINCIPAL**

### **FASE 1: APARTADO DE LOTE**
```
1. Cliente selecciona lote disponible (Estado = 0)
2. Sistema genera pre-venta con Estado = 1 (Apartado)
3. Lote cambia a Estado = 1 (Apartado)
4. Inicia timer de cancelación automática (DiasCancelacion)
5. Se genera comisión de apartado para vendedor
```

### **FASE 2: CONFIRMACIÓN DE VENTA**
```
1. Cliente paga anticipo/enganche
2. Venta cambia a Estado = 2 (Confirmada)
3. Lote cambia a Estado = 2 (Vendido)
4. Se genera plan de financiamiento en tb_cobranza
5. Se calcula comisión total del vendedor
```

### **FASE 3: PLAN DE PAGOS Y ESTADO DE CUENTA**
```
1. Sistema genera plan de pagos completo en tb_cobranza
2. Cada mensualidad tiene fecha límite y monto específico
3. Control automático de pagos vencidos (> 5 días)
4. Aplicación de intereses moratorios a cobranzas vencidas
5. Estado de cuenta dinámico con:
   - Pagos realizados vs pendientes
   - Intereses moratorios acumulados
   - Proyección de vencimientos futuros
   - Simulación de liquidación anticipada
```

### **FASE 4: CONTROL DE ETAPAS DE DESARROLLO**
```
1. Monitoreo automático del % de ventas por etapa
2. Al alcanzar umbral (90-100%), habilitar siguiente etapa
3. Cambio masivo de estado de lotes: Estado=3 → Estado=0
4. Notificación a vendedores de nueva etapa disponible
5. Actualización de mapas y materiales de venta
```

---

## ⚠️ **CASOS ESPECIALES IDENTIFICADOS**

### **1. CANCELACIÓN AUTOMÁTICA DE APARTADOS**
**Lógica en `funciones.php` líneas 47-59**:
```php
// Detecta apartados vencidos
$resA = mysqli_query($link, "SELECT IdVenta,Vendedor,Lote,Total... 
    FROM tb_ventas WHERE Estado=1 AND TotalPagado=0 AND Estatus=1 
    AND (TIMESTAMP(CURRENT_DATE(),CURRENT_TIME()) >= 
    TIMESTAMP(DATE_ADD(TIMESTAMP(Fecha,Hora),INTERVAL DiasCancelacion DAY)))");

// Cancela venta y libera lote
mysqli_query($link,"UPDATE tb_ventas SET Estatus=2 WHERE IdVenta=$rowA[IdVenta]");
mysqli_query($link,"UPDATE tb_lotes SET Estatus=1,Estado=0 WHERE IdLote=$rowA[Lote]");
```

### **2. SISTEMA DE INTERESES MORATORIOS Y ESTADO DE CUENTA**
**Lógica de Penalizaciones**:
- Cobranzas vencidas > 5 días generan interés automático
- Penalización mensual/anual definida por proyecto (`PenalizacionM`)
- Campo `TotalSI` preserva monto original sin intereses
- Campo `Interes` acumula penalizaciones
- Diferencia `Total - TotalSI` = intereses totales acumulados

**Estado de Cuenta Dinámico**:
```sql
-- Total de intereses pagados
SUM(IF(Interes<>0,IF(TotalPagado > TotalSI,(TotalPagado-TotalSI),0),0))

-- Monto atrasado (vencido > 5 días)
SUM(Total-TotalPagado) WHERE DATEDIFF(FechaFinal,NOW()) < -5 AND Cobrado=FALSE

-- Penalizaciones pendientes
SUM(Interes) WHERE DATEDIFF(FechaFinal,NOW()) < -5
```

### **3. SIMULADOR DE PLAN DE PAGOS**
**Funcionalidades del Simulador** (`simulador_capital.php`):
- Cálculo de mensualidades con diferentes plazos
- Simulación de enganches variables (%, monto fijo)
- Comparación contado vs financiado
- Proyección de intereses en caso de mora
- Cálculo de liquidación anticipada

### **4. BLOQUEO DE VENDEDORES**
```php
// Vendedores con 3+ apartados vencidos se suspenden
mysqli_query($link,"UPDATE tb_usuarios SET Estado=2 WHERE ApartadosError>=3");
```

---

## 🏗️ **ESTRUCTURA PROPUESTA PARA CI4**

### **MODELOS PRINCIPALES**
```php
ProyectoModel    → tb_proyectos
ManzanaModel     → tb_manzanas  
LoteModel        → tb_lotes
VentaModel       → tb_ventas
CobranzaModel    → tb_cobranza
ComisionModel    → tb_comisiones
```

### **ENTIDADES BUSINESS LOGIC**
```php
Lote::class {
    - isDisponible()
    - apartarPor($vendedor, $cliente, $dias)
    - confirmarVenta($anticipo)
    - cancelarApartado()
    - calcularPrecioTotal()
}

Venta::class {
    - generarPlanPagos($meses, $tasa)
    - procesarPago($monto)
    - aplicarInteresMoratorio()
    - calcularComisiones()
}
```

### **CONTROLADORES**
```php
Admin\LotesController     → CRUD + Mapa interactivo
Admin\VentasController    → Proceso venta + Apartados
Admin\CobranzaController  → Pagos + Financiamiento  
Admin\ComisionesController → Cálculo vendedores
```

### **HELPERS ESPECIALIZADOS**
```php
inmobiliario_helper.php {
    - formatearClaveLote($proyecto, $etapa, $manzana, $lote)
    - calcularSuperficie($frente, $fondo, $latIzq, $latDer)
    - validarEtapaDisponible($etapa_id, $proyecto_id)
    - habilitarSiguienteEtapa($proyecto_id)
    - porcentajeVentasEtapa($etapa_id)
}

financiero_helper.php {
    - generarPlanPagos($total, $anticipo, $meses, $tasa)
    - simularEscenariosPago($precio, $enganches_opciones)
    - calcularInteresMoratorio($saldo, $dias_vencido, $tasa)
    - generarEstadoCuenta($venta_id)
    - calcularLiquidacionAnticipada($venta_id, $fecha_pago)
}

cobranza_helper.php {
    - aplicarPenalizacion($cobranza_id, $diasVencido)
    - procesarPagoMensualidad($cobranza_id, $monto, $formas_pago)
    - generarReciboCobranza($pago_id)
    - notificarVencimientoProximo($cliente_id, $dias_aviso)
}
```

---

## ⚡ **PROCESOS AUTOMATIZADOS**

### **1. TASK SCHEDULER (Cron Jobs)**
```php
// Ejecutar diario a las 00:00
Commands\CancelarApartadosCommand::class
Commands\AplicarInteresesCommand::class  
Commands\SuspenderVendedoresCommand::class
Commands\HabilitarEtapasCommand::class
Commands\GenerarEstadosCuentaCommand::class
Commands\NotificarVencimientosCommand::class
```

### **2. FILTROS MIDDLEWARE**
```php
VentaFilter::class → Validar permisos de venta
LoteFilter::class  → Validar disponibilidad
```

---

## 🔍 **FUNCIONES AJAX IDENTIFICADAS (funciones.php)**

### **FUNCIÓN 317 - APARTAR LOTE**
**Ubicación**: `comandos/funciones.php` línea ~4350-4650
**Proceso completo de apartado**:

```php
// 1. VALIDACIÓN Y CÁLCULOS
$monto_enganche = $_POST['enganche'];  // Monto del anticipo
$totalrecibido = $efectivo + $transferencia + $tarjeta + $cheque; // Total pagado

// 2. ACTUALIZAR VENTA
UPDATE tb_ventas SET 
    Fecha='$fecharef', AnticipoPagado=$totalrecibido, 
    AnticipoCredito=TRUE, TotalPagado=$totalrecibido,
    Total=$total_lote, Anticipo=$monto_enganche
WHERE IdVenta=$venta

// 3. CREAR COBRANZA PARA RESTANTE DEL ENGANCHE
INSERT INTO tb_cobranza SET 
    Proyecto=$proyecto, Manzana=$manzana, Lote=$lote,
    Vendedor=$vendedor, Cliente=$cliente,
    TipoCredito=1, NTipoCredito='Apartado',
    Total=$enganche_financiar, // (enganche - totalrecibido)
    FechaFinal=$fecha_final    // fecha + DiasAnticipo

// 4. REGISTRAR INGRESOS POR CADA FORMA DE PAGO
INSERT INTO tb_ingresos SET 
    Tipo=1, NTipo='Apartado', Concepto='APARTADO',
    Total=$monto, Cuenta=$cuenta, // Por cada: efectivo, transferencia, tarjeta, cheque
    Movimiento=1, Apartado=1
```

### **FUNCIÓN DE CANCELACIÓN AUTOMÁTICA**
**Ubicación**: Login automático en `funciones.php` líneas 47-59
```php
// Detecta apartados vencidos automáticamente
SELECT IdVenta FROM tb_ventas 
WHERE Estado=1 AND TotalPagado=0 AND Estatus=1 
AND (CURRENT_TIMESTAMP >= DATE_ADD(TIMESTAMP(Fecha,Hora), INTERVAL DiasCancelacion DAY))

// Auto-cancela y penaliza vendedor
UPDATE tb_ventas SET Estatus=2 WHERE IdVenta=$venta
UPDATE tb_lotes SET Estado=0,NEstado='Disponible' WHERE IdLote=$lote  
UPDATE tb_usuarios SET ApartadosError=ApartadosError+1 WHERE IdUsuario=$vendedor
```

---

## 💰 **SISTEMA FINANCIERO DETALLADO**

### **TABLA tb_cobranza - Gestión de Financiamiento**
```sql
IdCobranza, Venta, Cliente, Proyecto, Manzana, Lote,
Total, TotalPagado, TipoCredito, FechaFinal, Plazo,
Interes, MesesInteres, DiasCredito, Cobrado
```

**Tipos de Crédito**:
1. **TipoCredito = 1**: Apartado (resto del enganche)
2. **TipoCredito = 2**: Financiamiento mensual (post-enganche)

### **TABLA tb_ingresos - Control de Pagos**
```sql
IdIngreso, Venta, Cliente, Proyecto, Manzana, Lote,
Total, Efectivo, Cheque, Transferencia, Tarjeta,
Cuenta, Referencia, FechaReferencia, Concepto, Apartado
```

**Conceptos de Pago**:
- `APARTADO`: Pago inicial de enganche
- `MENSUALIDAD`: Pago regular de financiamiento  
- `CAPITAL`: Pago a capital
- `INTERES`: Pago de intereses moratorios

---

## 🏗️ **ARQUITECTURA CI4 REFINADA**

### **MODELOS CON BUSINESS LOGIC**
```php
VentaModel extends Model {
    protected $returnType = 'App\Entities\Venta';
    
    public function apartarLote($loteId, $clienteId, $vendedorId, $enganche, $formasPago)
    public function confirmarVenta($ventaId, $planPagos)
    public function cancelarApartado($ventaId, $motivo)
    public function aplicarPenalizacion($ventaId, $diasVencido)
    public function getVentasVencidas()
}

CobranzaModel extends Model {
    protected $returnType = 'App\Entities\Cobranza';
    
    public function crearPlanPagos($ventaId, $monto, $meses, $tasaInteres)
    public function procesarPago($cobranzaId, $monto, $formasPago)
    public function aplicarInteresMoratorio($cobranzaId)
    public function getCobranzasVencidas($diasVencimiento = 5)
}

IngresoModel extends Model {
    public function registrarPago($ventaId, $concepto, $formasPago, $cuentas)
    public function getReporteIngresos($fechaInicio, $fechaFin, $proyecto = null)
}
```

### **ENTIDADES CON LÓGICA DE NEGOCIO**
```php
class Venta extends Entity {
    public function calcularComisionApartado(): float
    public function calcularComisionTotal(): float  
    public function puedeSerCancelada(): bool
    public function diasParaVencimiento(): int
    public function montoPendiente(): float
    public function generarFolio(): string
}

class Lote extends Entity {
    public function apartarPor(User $vendedor, Cliente $cliente, int $diasLimite): Venta
    public function marcarComoVendido(): void
    public function liberarApartado(): void
    public function calcularPrecioConDescuento(float $descuento, string $tipo): float
}

class Cobranza extends Entity {
    public function estaVencida(): bool
    public function calcularInteresMoratorio(): float
    public function aplicarPago(float $monto): void
    public function generarAvisoVencimiento(): string
}
```

### **HELPERS FINANCIEROS**
```php
// app/Helpers/financiero_helper.php
function calcularEnganche($precioTotal, $porcentaje, $minimo = 0)
function generarPlanPagos($monto, $meses, $tasaAnual, $fechaInicio)
function aplicarInteresMoratorio($saldoPendiente, $diasVencido, $tasaPenalizacion)
function calcularComisionVendedor($montoVenta, $porcentajeComision, $tipo = 'total')
function formatearFormaPago($efectivo, $transferencia, $tarjeta, $cheque)
function validarMontoMinimo($montoRecibido, $montoMinimo)

// app/Helpers/cobranza_helper.php  
function generarReciboCobranza($cobranzaId, $montoPagado)
function calcularDiasVencimiento($fechaLimite)
function notificarVencimiento($clienteId, $ventaId, $montoVencido)
function suspenderVendedor($vendedorId, $motivoSuspension)
```

### **COMMANDS PARA AUTOMATIZACIÓN**
```php
// app/Commands/ProcesarVencimientosCommand.php
class ProcesarVencimientosCommand extends BaseCommand {
    public function run(array $params) {
        // 1. Cancelar apartados vencidos
        // 2. Aplicar intereses moratorios  
        // 3. Suspender vendedores con 3+ errores
        // 4. Notificar vencimientos próximos
    }
}

// app/Commands/GenerarReportesCommand.php  
class GenerarReportesCommand extends BaseCommand {
    public function run(array $params) {
        // 1. Reporte diario de ventas
        // 2. Reporte de cobranza
        // 3. Reporte de comisiones
        // 4. Dashboard de métricas
    }
}
```

---

## 📋 **FLUJO TÉCNICO COMPLETO**

### **1. APARTADO DE LOTE (Función 317)**
```
[FRONTEND] Selección lote + cliente + enganche + formas pago
    ↓
[CONTROLLER] VentasController::apartar()
    ↓
[VALIDATION] Validar disponibilidad + montos + permisos
    ↓
[TRANSACTION START]
    ↓
[UPDATE] tb_lotes: Estado=1 (Apartado)
    ↓
[INSERT] tb_ventas: Estado=1, Anticipo, DiasCancelacion
    ↓  
[INSERT] tb_cobranza: TipoCredito=1 (resto enganche)
    ↓
[INSERT] tb_ingresos: Concepto='APARTADO' (por cada forma pago)
    ↓
[UPDATE] tb_cuentas_proyectos: Saldos
    ↓
[INSERT] tb_comisiones: ComisionApartado
    ↓
[TRANSACTION COMMIT]
    ↓
[RESPONSE] Folio apartado + datos confirmación
```

### **2. CONFIRMACIÓN DE VENTA**
```
[TRIGGER] Pago completo de enganche
    ↓
[UPDATE] tb_ventas: Estado=2, TotalPagado
    ↓
[UPDATE] tb_lotes: Estado=2 (Vendido)
    ↓
[INSERT] tb_cobranza: TipoCredito=2 (mensualidades)
    ↓
[UPDATE] tb_comisiones: ComisionTotal
```

### **3. CANCELACIÓN AUTOMÁTICA**
```
[CRON DIARIO] ProcesarVencimientosCommand
    ↓
[SELECT] Apartados vencidos
    ↓
[FOREACH] Apartado vencido:
    [UPDATE] tb_ventas: Estatus=2 (Cancelado)
    [UPDATE] tb_lotes: Estado=0 (Disponible)  
    [UPDATE] tb_usuarios: ApartadosError++
    [INSERT] tb_historial_apartados: Registro
    ↓
[CHECK] Vendedores con ApartadosError >= 3
    ↓
[UPDATE] tb_usuarios: Estado=2 (Suspendido)
```

---

## 📋 **PENDIENTES DE INVESTIGACIÓN**

### **Funciones AJAX por mapear**:
- [ ] Función de venta completa (post-apartado)
- [ ] Función de pagos de mensualidades  
- [ ] Función de cancelación manual
- [ ] Función de traspaso de lotes
- [ ] Función de cambio de lote
- [ ] Función de reestructuración
- [ ] Función de habilitación de etapas
- [ ] Función de simulación de plan de pagos

### **Archivos por analizar**:
- [ ] `cobranza.php` - Interface de pagos
- [ ] `comisiones.php` - Cálculo vendedores  
- [ ] `lotes_modificar.php` - Cambios de lote
- [x] `simulador_capital.php` - Simulador de pagos ✅
- [x] `ventas_estado_cuenta.php` - Estado de cuenta ✅
- [ ] Reportes financieros
- [ ] Sistema de control de etapas

---

## 🎯 **SIGUIENTE FASE**

**Prioridad 1**: Implementar control de etapas de desarrollo secuencial
**Prioridad 2**: Crear simulador de plan de pagos en CI4
**Prioridad 3**: Desarrollar estado de cuenta dinámico con intereses moratorios
**Prioridad 4**: Mapear funciones de cobranza y pagos mensuales completas
**Prioridad 5**: Analizar sistema de comisiones y documentar casos especiales

---

*Documento actualizado - Incluye control de etapas, simulador de pagos, estado de cuenta e intereses moratorios*