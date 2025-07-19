# Flujo de Base de Datos - Sistema Legacy ANVAR

## Introducción

Este documento describe la estructura y flujo de datos del sistema legacy de ANVAR Real Estate, basado en el análisis del archivo `anvarinm_web.sql`. El sistema está diseñado para gestionar un negocio inmobiliario completo que incluye proyectos, lotes, ventas, cobranza, comisiones y administración de clientes.

## Estructura Jerárquica del Negocio

### 1. Empresas → Proyectos → Divisiones → Manzanas → Lotes

El sistema sigue una estructura jerárquica clara:

- **Empresa** (tb_empresas)
- **Proyecto** (tb_proyectos) 
- **División** (tb_divisiones)
- **Manzana** (tb_manzanas)
- **Lote** (tb_lotes)

## Tablas Principales y su Funcionalidad

### 1. **tb_empresas** - Entidad Corporativa Principal
```sql
Campos principales:
- IdEmpresa (PK)
- Nombre, RazonSocial, RFC
- Domicilio, Telefono, Email
- Representante, Logotipo
- Estatus (activo/inactivo)
```

**Propósito**: Gestiona las diferentes empresas o divisiones corporativas que pueden manejar múltiples proyectos inmobiliarios.

### 2. **tb_proyectos** - Desarrollos Inmobiliarios
```sql
Campos principales:
- IdProyecto (PK)
- Nombre, Clave, Descripcion
- Ubicacion, Longitud, Latitud
- Empresa (FK a tb_empresas)
- Color (para identificación visual)
- NComercial, Parcela, Ejido
- PenalizacionM (penalización por mora)
```

**Propósito**: Representa cada desarrollo inmobiliario. Contiene información geográfica, legal (ejido, parcela) y comercial del proyecto.

### 3. **tb_divisiones** - Etapas de Desarrollo Inmobiliario
```sql
Campos principales:
- IdDivision (PK)
- Nombre, Descripcion
- Empresa, Proyecto (FKs)
- Clave, Leyenda, Leyenda2
- Estatus (activo/disponible para venta)
```

**Propósito**: Gestiona las **etapas de desarrollo inmobiliario**. Cada división representa una etapa de construcción y comercialización (Etapa 1, Etapa 2, etc.). Los lotes de una etapa solo pueden venderse cuando la etapa anterior está completamente vendida.

**Lógica de Etapas**:
- **Etapa 1**: Disponible desde el inicio del proyecto
- **Etapas subsecuentes**: Se habilitan conforme se completan las ventas de la etapa anterior
- **Control de disponibilidad**: Solo una etapa activa por vez por proyecto

### 4. **tb_manzanas** - Agrupación de Lotes
```sql
Campos principales:
- IdManzana (PK)
- Nombre, Clave
- Proyecto (FK)
- Division (FK)
- Longitud, Latitud
- Color (identificación visual)
```

**Propósito**: Agrupa lotes en manzanas para mejor organización urbana y comercial.

### 5. **tb_lotes** - Unidad de Venta Principal
```sql
Campos principales:
- IdLote (PK)
- Clave, Numero
- Proyecto, Manzana (FKs)
- Tipo, Division (FKs)
- Area, Precio, Total
- Dimensiones: LatIzq, Fondo, Frente, LatDer
- Colindancias: Norte, Sur, Poniente, Oriente
- Construccion (área construida)
- Coordenadas, TipoCoordenadas
- Estatus (disponible, apartado, vendido)
```

**Propósito**: Unidad básica de venta. Contiene toda la información técnica, legal y comercial de cada lote.

**Estados de Lotes** (tb_estados_lotes):
- 0: Disponible (para venta en etapa activa)
- 1: Apartado (reservado temporalmente)
- 2: Vendido (venta confirmada)
- 3: Suspendido (no vendible - etapa no habilitada)

**Control por Etapas**:
- Los lotes solo están "Disponibles" si pertenecen a la etapa actualmente en venta
- Lotes de etapas futuras permanecen en estado "Suspendido" hasta que se habilite su etapa

### 6. **tb_clientes** - Gestión de Compradores
```sql
Campos principales:
- IdCliente (PK)
- Nombre, RazonSocial, RFC, CURP
- Domicilio completo (Numero, Colonia, CP, Ciudad, Estado)
- Contacto, Telefono, Email
- EstadoCivil (FK)
- Acceso, Clave (para portal web)
- Verificada (email verificado)
- TextoVendedor (notas del vendedor)
```

**Propósito**: Almacena información completa de clientes, tanto personas físicas como morales, con capacidad de acceso web.

### 7. **tb_usuarios** - Sistema de Usuarios y Permisos
```sql
Campos principales:
- IdUsuario (PK)
- Usuario, Clave, Email
- Nombre, Domicilio, Telefono
- Tipo (FK a tb_tipo_usuarios)
- Permisos específicos:
  - Ventas, Ingresos, Egresos
  - Cobranza, Clientes, Comisiones
  - Lotes, Reportes, Configuracion
```

**Propósito**: Control de acceso con permisos granulares por módulo del sistema.

## Flujo de Proceso de Ventas

### 1. **tb_ventas** - Registro de Ventas
```sql
Campos principales:
- IdVenta (PK)
- Folio (número de venta)
- Total, TotalPagado
- Proyecto, Manzana, Lote (FKs)
- Vendedor, Cliente (FKs)
- Forma, Tipo (formas y tipos de pago)
- Estado, Credito, Cobrado
- Anticipo, Descuento
- Fecha, Hora, Usuario
```

**Propósito**: Registra cada transacción de venta con referencias a lote, cliente y vendedor.

**Flujo de Venta**:
1. Cliente selecciona lote disponible
2. Se crea registro en tb_ventas
3. Lote cambia estado a "Apartado" o "Vendido"
4. Si es a crédito, se genera registro en tb_cobranza

### 2. **tb_cobranza** - Gestión de Financiamiento y Plan de Pagos
```sql
Campos principales:
- IdCobranza (PK)
- Total, TotalPagado, TotalSI (Total Sin Intereses)
- Venta (FK a tb_ventas)
- Cliente, Lote, Proyecto (FKs)
- TipoCredito, DiasCredito, Plazo
- FechaFinal, FechaPago
- Interes (monto de interés moratorio)
- Cobrado (boolean)
- Observaciones
```

**Propósito**: 
1. **Plan de Pagos**: Genera mensualidades automáticas al confirmar venta
2. **Seguimiento de Cobranza**: Control de pagos realizados vs pendientes
3. **Cálculo de Intereses**: Aplica penalizaciones por pagos vencidos
4. **Estado de Cuenta**: Historial completo de cada cliente/lote

**Tipos de Cobranza**:
- `TipoCredito = 1`: Resto del enganche (apartado)
- `TipoCredito = 2`: Mensualidades del financiamiento
- `Plazo = "A"`: Pagos de anticipo/enganche
- `Plazo = 1,2,3...`: Número de mensualidad

**Sistema de Intereses Moratorios**:
- Pagos vencidos > 5 días generan interés automático
- Campo `Interes` almacena monto de penalización
- Campo `TotalSI` mantiene monto original sin intereses
- Diferencia entre `Total` y `TotalSI` = intereses acumulados

**Tablas Relacionadas**:
- **tb_cobranza_pagos**: Historial de pagos realizados
- **tb_cobranza_vencimientos**: Control de vencimientos
- **tb_cobranza_intereses**: Cálculo detallado de intereses moratorios

### 3. **tb_ingresos** - Registro de Pagos
```sql
Campos principales:
- IdIngreso (PK)
- Folio, Total
- Forma (efectivo, cheque, transferencia)
- Proyecto, Lote, Cliente (FKs)
- Tipo, Cuenta
- FechaIngreso, HoraIngreso
- Referencia, Observaciones
```

**Propósito**: Registra todos los ingresos de dinero al sistema, vinculados a ventas o pagos de cobranza.

## Sistema de Comisiones

### **tb_comisiones** - Cálculo de Comisiones de Vendedores
```sql
Campos principales:
- IdComision (PK)
- Total, TotalPagado
- Vendedor (FK a tb_usuarios)
- Venta (FK a tb_ventas)
- Proyecto, Lote, Manzana (FKs)
- Cobrada, FechaPago
- Ingreso (FK cuando se paga)
```

**Propósito**: Calcula y rastrea las comisiones de vendedores basadas en ventas realizadas.

**Flujo de Comisiones**:
1. Se realiza una venta
2. Sistema calcula comisión automáticamente
3. Comisión se marca como "pendiente"
4. Al pagarse, se vincula con tb_ingresos

## Sistemas de Simulación y Reportes Financieros

### **Simulador de Plan de Pagos** (`simulador_capital.php`)
**Propósito**: Permite calcular diferentes escenarios de pago antes de generar la venta

**Funcionalidades**:
- Simulación de mensualidades con diferentes plazos
- Cálculo de enganches variables
- Proyección de intereses en caso de mora
- Comparación de escenarios (contado vs financiado)

### **Estado de Cuenta** (`ventas_estado_cuenta.php`)
**Propósito**: Reporte detallado del estado financiero de cada venta

**Información Desplegada**:
- Resumen del lote y cliente
- Total de la venta vs total pagado
- Desglose de mensualidades (pagadas/pendientes)
- Cálculo de intereses moratorios acumulados
- Proyección de pagos futuros
- Historial completo de pagos realizados

**Métricas Calculadas**:
```sql
-- Total de intereses pagados
SUM(IF(Interes<>0,IF(TotalPagado > TotalSI,(TotalPagado-TotalSI),0),0))

-- Total atrasado (vencido > 5 días)
SUM(Total-TotalPagado) WHERE DATEDIFF(FechaFinal,NOW()) < -5 AND Cobrado=FALSE

-- Total de penalizaciones pendientes
SUM(Interes) WHERE DATEDIFF(FechaFinal,NOW()) < -5
```

**Conceptos de Pago**:
- `APARTADO`: Pago inicial de enganche
- `MENSUALIDAD`: Pago regular de financiamiento  
- `CAPITAL`: Pago a capital (adelanto de mensualidades)
- `INTERES`: Pago de intereses moratorios
- `LIQUIDACION`: Pago final para liquidar deuda

**Formas de Pago Soportadas**:
- Efectivo, Cheque, Transferencia bancaria, Tarjeta de crédito
- Combinación de múltiples formas en un solo pago
- Referencia bancaria y fecha de operación para seguimiento

## Gestión Documental y Bitácora

### **tb_bitacora** - Auditoria del Sistema
```sql
Campos principales:
- IdBitacora (PK)
- Empresa, Proyecto (contexto)
- Usuario, TipoUsuario
- Categoria, Descripcion
- Fecha, Hora, IP
- Color (clasificación visual)
```

**Propósito**: Registra todas las acciones importantes del sistema para auditoría y seguimiento.

**Categorías de Bitácora**:
- ERROR CAPTURA
- CAMBIOS (de lote o ubicación)
- INCIDENTES (pagos, expedientes)

### **tb_bitacora_vendedor** - Seguimiento Comercial
```sql
Campos principales:
- Usuario, Cliente
- Proyecto, Lote
- Notas, Estado
- Fecha seguimiento
```

**Propósito**: Permite a vendedores llevar seguimiento de clientes potenciales y actividades comerciales.

## Catalogos de Soporte

### Catálogos Principales:
- **tb_estados_lotes**: Estados de lotes (Disponible, Apartado, Vendido, Suspendido)
- **tb_tipos_lotes**: Tipos de lotes (Residencial, Comercial, etc.)
- **tb_formas_pago**: Formas de pago (Efectivo, Cheque, Transferencia, Tarjeta)
- **tb_tipos_ingresos**: Tipos de ingresos (Enganche, Mensualidad, etc.)
- **tb_estado_civil**: Estados civiles de clientes
- **tb_tipo_usuarios**: Tipos de usuarios del sistema

## Patrones de Datos y Convenciones

### 1. **Convenciones de Nomenclatura**
- Todas las tablas inician con `tb_`
- Campos ID son `Id + NombreTabla`
- Campos de referencia incluyen el nombre: `NUsuario`, `NProyecto`
- Se almacena tanto ID como nombre denormalizado para performance

### 2. **Campos de Auditoría Estándar**
```sql
Fecha date
Hora time
Usuario int(11)
NUsuario varchar
IP varchar(100)
Estatus int(11) -- 0=inactivo, 1=activo, 2=eliminado
```

### 3. **Manejo de Estados**
- **Estatus**: Control de registros activos/inactivos
- **Estado**: Estados de proceso (en tb_ventas, tb_cobranza)
- **Cobrado/Pagado**: Flags booleanos para control de pagos

### 4. **Denormalización para Performance**
- Se almacenan nombres junto con IDs (NUsuario, NProyecto, etc.)
- Totales calculados se almacenan (Total, TotalPagado)
- Coordenadas geográficas duplicadas en múltiples niveles

## Relaciones Clave del Sistema

### **Flujo Principal de Datos**:
```
Empresa → Proyecto → División → Manzana → Lote
                                    ↓
Cliente → Venta ← Usuario(Vendedor)
           ↓
        Cobranza → Pagos(Ingresos)
           ↓
        Comisiones
```

### **Integridad Referencial**:
- **tb_lotes**: Referencia Proyecto, Manzana, División
- **tb_ventas**: Referencia Cliente, Lote, Vendedor
- **tb_cobranza**: Referencia Venta, Cliente, Lote
- **tb_ingresos**: Referencia Cliente, Lote, Proyecto
- **tb_comisiones**: Referencia Venta, Vendedor

## Consideraciones para Migración a CI4

### 1. **Optimizaciones Necesarias**:
- Normalizar campos denormalizados (N* campos)
- Implementar foreign keys reales
- Unificar charset (actualmente mixto latin1/utf8)
- Consolidar tablas de respaldo (_backup, _old)

### 2. **Entidades Principales para CI4**:
- Empresa, Proyecto, División, Manzana, Lote
- Cliente, Usuario, Venta, Cobranza, Ingreso, Comisión
- Bitácora, Configuración

### 3. **Relaciones Complejas**:
- Herencia: tb_usuarios con permisos modulares
- Aggregación: Totales calculados en ventas/cobranza
- Composición: Lotes pertenecen estrictamente a Manzanas

### 4. **Lógica de Negocio Crítica a Implementar**:

#### **Control de Etapas de Desarrollo**:
```php
class EtapaDesarrollo {
    public function puedeHabilitarseParaVenta(): bool
    public function habilitarSiguienteEtapa(): void
    public function getLotesDisponibles(): array
    public function porcentajeVentasCompletado(): float
}
```

#### **Simulador de Planes de Pago**:
```php
class SimuladorPagos {
    public function calcularMensualidades($monto, $plazo, $tasa): array
    public function simularEscenarios($precio, $enganche_opciones): array
    public function proyectarInteresesMora($dias_retraso): float
}
```

#### **Estado de Cuenta Dinámico**:
```php
class EstadoCuenta {
    public function generarReporte($venta_id): array
    public function calcularSaldoPendiente(): float
    public function getMensualidadesPendientes(): array
    public function getInteresesMoratorios(): float
    public function proyectarPagosVencimiento(): array
}
```

### 5. **Relaciones Complejas**:
- **Control de Etapas**: División → Lotes (solo activos si etapa habilitada)
- **Herencia**: tb_usuarios con permisos modulares
- **Aggregación**: Totales calculados en ventas/cobranza
- **Composición**: Lotes pertenecen estrictamente a Manzanas
- **Dependencia Temporal**: Etapas secuenciales basadas en % de ventas

### 6. **Puntos de Integración Críticos**:
- **Portal de Clientes**: Estado de cuenta en tiempo real
- **Sistema de Reportes**: Dashboards financieros ejecutivos
- **Simulador de Ventas**: Herramienta para vendedores
- **Alertas Automáticas**: Vencimientos y mora
- **Gestión Documental**: Contratos y comprobantes de pago
- **Geolocalización**: Mapas interactivos de etapas/disponibilidad

## Conclusión

El sistema legacy maneja un flujo completo de negocio inmobiliario con alta denormalización para performance. La estructura jerárquica clara permite escalabilidad, pero requiere refactoring para aprovechar las capacidades modernas de CI4, especialmente en términos de relaciones formales y optimización de consultas.

El flujo principal va desde la creación de proyectos hasta el cobro completo, pasando por venta, financiamiento y comisiones, con un robusto sistema de auditoría y seguimiento comercial.