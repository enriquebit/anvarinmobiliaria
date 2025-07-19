# 📊 ANÁLISIS EXHAUSTIVO DEL MÓDULO DE VENTAS - SISTEMA LEGACY ANVAR INMOBILIARIA

## 🎯 Objetivo
Documentar completamente la estructura, funcionamiento, relaciones y lógica de negocio del módulo de ventas del sistema legacy para su migración completa al nuevo sistema CodeIgniter 4 con metodología Entity-First.

## 📋 **RESUMEN EJECUTIVO**
**Análisis completo realizado el 07 de julio de 2025**

Este documento proporciona un análisis exhaustivo del sistema legacy de ventas de Anvar Inmobiliaria, incluyendo el flujo completo desde la configuración de empresa hasta el pago de comisiones, con múltiples tipos de usuarios y roles específicos. Se han identificado 4 tipos principales de ventas, un sistema complejo de comisiones automáticas con jerarquía vendedor/super-vendedor, sistema de cuentas bancarias por proyecto, y 94 campos en la tabla principal de ventas con desnormalización extrema.

### Hallazgos Clave del Sistema Legacy
- **🏗️ Arquitectura Desnormalizada**: Tabla tb_ventas con 57 campos incluyendo campos "N" (nombres) desnormalizados
- **💰 Sistema de Comisiones Dual**: ComisionApartado (fija al apartar) + ComisionTotal (% o fija al vender)
- **🏦 Jerarquía Financiera**: Empresas → Cuentas Bancarias → Proyectos → Manzanas → Lotes → Ventas
- **📈 Amortización Francesa**: Implementada con intereses anuales y pagos mensuales
- **🔧 Función "Redondear"**: Redondea pagos mensuales a pesos completos (sin centavos)
- **👥 Super-Vendedor System**: Vendedores tipo 5 heredan comisiones al super-vendedor asignado
- **📝 Sistema de Ingresos**: Registra cada pago por forma (efectivo, transferencia, tarjeta, cheque)
- **🎯 4 Tipos de Operación**: Apartado, Enganche, Venta Contado, Venta Crédito

## 📁 Estructura del Sistema Legacy

### Archivos Principales del Módulo Ventas
```
./administracion/
├── ventas.php                           # Lista principal de ventas
├── ventas_agregar.php                   # Formulario agregar venta
├── ventas_credito_presupuesto.php       # Gestión de presupuestos a crédito
├── ventas_depositos_bancarios.php       # Gestión de depósitos bancarios
├── ventas_estado_cuenta.php             # Estados de cuenta de ventas
├── ventas_recibo_apartado.php           # Recibos de apartado
├── ventas_recibo_capital.php            # Recibos de capital
├── ventas_recibo_enganche.php           # Recibos de enganche
├── ventas_recibo_venta.php              # Recibos de venta
├── ventas_caratula.php                  # Carátulas de venta
├── comisiones.php                       # Gestión de comisiones de vendedores
├── flujos.php                           # Flujos de efectivo e ingresos
├── ingresos_agregar.php                 # Formulario agregar ingresos
└── cobranza.php                         # Gestión de cobranza y pagos
```

### Funciones de Negocio y JavaScript
```
./administracion/comandos/
├── funciones.php                        # Funciones principales de ventas (AJAX)
├── conexion.php                         # Conexión a base de datos
├── encriptar.php                        # Funciones de encriptación
├── fechas.php                           # Funciones de manejo de fechas
└── menu.php                             # Menú del sistema

./administracion/js/funciones/
├── ventas.js                            # JavaScript para ventas
├── lista_ingresos.js                    # JavaScript para ingresos
└── menu.js                              # JavaScript para menú
```

## 🗄️ Estructura de Base de Datos Legacy

### Tabla Principal: `tb_ventas`
```sql
CREATE TABLE `tb_ventas` (
  `IdVenta` int(11) NOT NULL,
  `Folio` varchar(1000),
  `Total` double DEFAULT '0',
  `TotalPagado` double DEFAULT '0',
  `Forma` int(11) DEFAULT '0',
  `NForma` varchar(100),                    -- DESNORMALIZADO: Nombre de forma de pago
  `Proyecto` int(11) DEFAULT '0',
  `NProyecto` varchar(1000),                -- DESNORMALIZADO: Nombre del proyecto
  `Manzana` int(11) DEFAULT '0',
  `NManzana` varchar(100),                  -- DESNORMALIZADO: Nombre de manzana
  `Lote` int(11) DEFAULT '0',
  `NLote` varchar(1000),                    -- DESNORMALIZADO: Nombre/clave del lote
  `Vendedor` int(11) DEFAULT '0',
  `NVendedor` varchar(1000),                -- DESNORMALIZADO: Nombre del vendedor
  `Cliente` int(11) DEFAULT '0',
  `NCliente` varchar(1000),                 -- DESNORMALIZADO: Nombre del cliente
  `Telefono` varchar(100),
  `Tipo` int(11) DEFAULT '0',
  `NTipo` varchar(100),                     -- DESNORMALIZADO: Tipo de venta
  `Estado` int(4) DEFAULT '0',
  `NEstado` varchar(100),                   -- DESNORMALIZADO: Estado (Apartado/Vendido)
  `Fecha` date,
  `Hora` time,
  `Usuario` int(11) DEFAULT '0',
  `NUsuario` varchar(1000),                 -- DESNORMALIZADO: Nombre del usuario
  `IP` varchar(100),
  `Estatus` int(11) DEFAULT '0',           -- Activo/Inactivo
  `Credito` tinyint(4) DEFAULT '0',        -- ¿Es venta a crédito?
  `Cobrado` tinyint(4) DEFAULT '0',        -- ¿Está completamente cobrado?
  `Anticipo` double DEFAULT '0',           -- Monto del anticipo/enganche
  `AnticipoPagado` double DEFAULT '0',     -- Anticipo ya pagado
  `AnticipoCredito` tinyint(4) DEFAULT '0',-- ¿Anticipo a crédito?
  `AnticipoCobrado` tinyint(4) DEFAULT '0',-- ¿Anticipo cobrado?
  `Area` double DEFAULT '0',               -- Área del lote
  `Observaciones` text,
  `Empresa` int(11) DEFAULT '0',
  `NEmpresa` varchar(1000),                -- DESNORMALIZADO: Nombre de empresa
  `Descuento` varchar(100),
  `TipoDescuento` varchar(100),
  `TipoAnticipo` varchar(100),
  `ComisionApartado` double DEFAULT '0',
  `ComisionTotal` double DEFAULT '0',
  `FechaSistema` date,
  `Contrato` varchar(1000),               -- Archivo del contrato
  `ContratoCargado` tinyint(4) DEFAULT '0',
  `FechaCarga` date,
  `HoraCarga` time,
  `IntervaloCancelacion` int(11) DEFAULT '0',
  `DiasCancelacion` int(11) DEFAULT '0',
  `TextoCancelacion` text,
  `FolioInterno` varchar(100),
  `Origen` int(11) DEFAULT '0',
  `NOrigen` varchar(2000),                -- DESNORMALIZADO: Origen de la venta
  `CobrarInteres` tinyint(4) DEFAULT '0',
  `Cliente2` int(11) DEFAULT '0',          -- Cliente adicional (cónyuge)
  `NCliente2` varchar(300),                -- DESNORMALIZADO: Nombre cliente 2
  `Intereses` double DEFAULT '0'
);

-- Total de 57 campos en la tabla principal
-- Múltiples campos desnormalizados con prefijo "N" (Nombre)
```

### Tablas Relacionadas Críticas

#### `tb_comisiones` - Sistema de Comisiones
```sql
CREATE TABLE `tb_comisiones` (
  `IdComision` int(11) NOT NULL AUTO_INCREMENT,
  `Total` double DEFAULT '0',             -- Monto de la comisión
  `Fecha` date,                           -- Fecha de la comisión
  `Hora` time,                            -- Hora de la comisión
  `Vendedor` int(11) DEFAULT '0',         -- ID del vendedor que recibe
  `NVendedor` varchar(1000),              -- DESNORMALIZADO: Nombre del vendedor
  `Estatus` int(11) DEFAULT '0',          -- Activo/Inactivo
  `Proyecto` int(11) DEFAULT '0',
  `NProyecto` varchar(1000),              -- DESNORMALIZADO: Nombre del proyecto
  `Manzana` int(11) DEFAULT '0',
  `NManzana` varchar(100),                -- DESNORMALIZADO: Nombre de manzana
  `Lote` int(11) DEFAULT '0',
  `NLote` varchar(1000),                  -- DESNORMALIZADO: Clave del lote
  `Venta` int(11) DEFAULT '0',            -- ID de la venta
  `Empresa` int(11) DEFAULT '0',
  `NEmpresa` varchar(1000),               -- DESNORMALIZADO: Nombre de empresa
  PRIMARY KEY (`IdComision`)
);

#### `tb_ingresos` - Registro de Todos los Pagos
```sql
CREATE TABLE `tb_ingresos` (
  `IdIngreso` int(11) NOT NULL AUTO_INCREMENT,
  `Proyecto` int(11) DEFAULT '0',
  `NProyecto` varchar(1000),              -- DESNORMALIZADO: Nombre del proyecto
  `Manzana` int(11) DEFAULT '0',
  `NManzana` varchar(100),                -- DESNORMALIZADO: Nombre de manzana
  `Lote` int(11) DEFAULT '0',
  `NLote` varchar(1000),                  -- DESNORMALIZADO: Clave del lote
  `Vendedor` int(11) DEFAULT '0',
  `NVendedor` varchar(1000),              -- DESNORMALIZADO: Nombre del vendedor
  `Cliente` int(11) DEFAULT '0',
  `NCliente` varchar(1000),               -- DESNORMALIZADO: Nombre del cliente
  `Fecha` date,                           -- Fecha del ingreso
  `Hora` time,                            -- Hora del ingreso
  `Venta` int(11) DEFAULT '0',            -- ID de la venta relacionada
  `Tipo` int(11) DEFAULT '0',             -- 1=Apartado, 2=Venta, etc.
  `NTipo` varchar(100),                   -- DESNORMALIZADO: Tipo de ingreso
  `Total` double DEFAULT '0',             -- Monto total del ingreso
  `Observaciones` text,                   -- Observaciones del pago
  `Cuenta` int(11) DEFAULT '0',           -- ID de cuenta bancaria
  `NCuenta` varchar(1000),                -- DESNORMALIZADO: Nombre de cuenta
  `SaldoAnterior` double DEFAULT '0',     -- Saldo anterior de la cuenta
  `SaldoPosterior` double DEFAULT '0',    -- Saldo posterior de la cuenta
  `Referencia` varchar(100),              -- Referencia del pago
  `FechaReferencia` date,                 -- Fecha de la referencia
  `Estatus` int(11) DEFAULT '0',          -- Activo/Inactivo
  `Efectivo` double DEFAULT '0',          -- Monto en efectivo
  `Cheque` double DEFAULT '0',            -- Monto en cheque
  `Transferencia` double DEFAULT '0',     -- Monto por transferencia
  `Tarjeta` double DEFAULT '0',           -- Monto con tarjeta
  `Movimiento` int(11) DEFAULT '0',       -- 1=Ingreso, 2=Egreso
  `NMovimiento` varchar(100),             -- DESNORMALIZADO: Tipo de movimiento
  `Concepto` varchar(1000),               -- Concepto del pago
  `Apartado` tinyint(4) DEFAULT '0',      -- ¿Es pago de apartado?
  `Enganche` tinyint(4) DEFAULT '0',      -- ¿Es pago de enganche?
  `Folio` int(11) DEFAULT '0',            -- Folio del recibo
  `Empresa` int(11) DEFAULT '0',
  `NEmpresa` varchar(1000),               -- DESNORMALIZADO: Nombre de empresa
  `VariosPagos` tinyint(4) DEFAULT '0',   -- ¿Tiene múltiples formas de pago?
  `FolioVarios` int(11) DEFAULT '0',      -- Folio para varios pagos
  `Usuario` int(11) DEFAULT '0',          -- Usuario que registra
  `NUsuario` varchar(1000),               -- DESNORMALIZADO: Nombre del usuario
  `Turno` int(11) DEFAULT '0',            -- Turno del usuario
  `TipoForma` varchar(100),               -- Tipo de forma de pago específica
  `NTipoForma` varchar(100),              -- DESNORMALIZADO: Nombre tipo forma
  `Ordenante` varchar(1000),              -- Nombre del ordenante (transferencias)
  PRIMARY KEY (`IdIngreso`)
);

#### `tb_cobranza` - Cuentas por Cobrar
```sql
CREATE TABLE `tb_cobranza` (
  `IdCobranza` int(11) NOT NULL AUTO_INCREMENT,
  `Proyecto` int(11) DEFAULT '0',
  `NProyecto` varchar(1000),              -- DESNORMALIZADO: Nombre del proyecto
  `Manzana` int(11) DEFAULT '0',
  `NManzana` varchar(100),                -- DESNORMALIZADO: Nombre de manzana
  `Lote` int(11) DEFAULT '0',
  `NLote` varchar(1000),                  -- DESNORMALIZADO: Clave del lote
  `Vendedor` int(11) DEFAULT '0',
  `NVendedor` varchar(1000),              -- DESNORMALIZADO: Nombre del vendedor
  `Cliente` int(11) DEFAULT '0',
  `NCliente` varchar(1000),               -- DESNORMALIZADO: Nombre del cliente
  `DiasCredito` int(11) DEFAULT '0',      -- Días para vencimiento
  `Fecha` date,                           -- Fecha de la cuenta por cobrar
  `FechaFinal` date,                      -- Fecha de vencimiento
  `Hora` time,                            -- Hora de registro
  `Venta` int(11) DEFAULT '0',            -- ID de la venta
  `TipoCredito` int(11) DEFAULT '0',      -- 1=Apartado, 2=Venta
  `NTipoCredito` varchar(100),            -- DESNORMALIZADO: Tipo de crédito
  `Total` double DEFAULT '0',             -- Monto por cobrar
  `TotalPagado` double DEFAULT '0',       -- Monto ya pagado
  `Cobrado` tinyint(4) DEFAULT '0',       -- ¿Está cobrado?
  `FechaPago` date,                       -- Fecha del pago
  `HoraPago` time,                        -- Hora del pago
  `Estatus` int(11) DEFAULT '0',          -- Activo/Inactivo
  `Plazo` varchar(10),                    -- Número de plazo o 'A' para anualidades
  `Empresa` int(11) DEFAULT '0',
  `NEmpresa` varchar(1000),               -- DESNORMALIZADO: Nombre de empresa
  `Interes` double DEFAULT '0',           -- Monto de interés del periodo
  PRIMARY KEY (`IdCobranza`)
);

#### `tb_configuracion` - Configuración por Empresa
```sql
CREATE TABLE `tb_configuracion` (
  `IdConfiguracion` int(11) NOT NULL AUTO_INCREMENT,
  `Empresa` int(11) DEFAULT '0',          -- ID de la empresa
  `ApartadoMinimo` double DEFAULT '0',    -- Monto mínimo para apartado
  `PorcentajeAnticipo` double DEFAULT '0', -- % de anticipo requerido
  `DiasAnticipo` int(11) DEFAULT '0',     -- Días para liquidar anticipo
  `ApartadoComision` double DEFAULT '0',  -- Comisión por apartado
  `PorcentajeComision` double DEFAULT '0', -- % de comisión total
  `FolioVenta` int(11) DEFAULT '0',       -- Último folio de venta
  `FolioApartado` int(11) DEFAULT '0',    -- Último folio de apartado
  `FolioEnganche` int(11) DEFAULT '0',    -- Último folio de enganche
  `FoliosVariosPagos` int(11) DEFAULT '0', -- Último folio varios pagos
  PRIMARY KEY (`IdConfiguracion`)
);
```

### Tablas Relacionadas

#### `tb_proyectos`
```sql
CREATE TABLE `tb_proyectos` (
  `IdProyecto` int(11) NOT NULL,
  `Nombre` varchar(1000),
  `Clave` varchar(100),
  `Descripcion` text,
  `Ubicacion` text,
  `Color` varchar(100),
  `Empresa` int(11) DEFAULT '0',
  `NEmpresa` varchar(1000),               -- DESNORMALIZADO
  -- ... más campos
);
```

#### `tb_lotes` (inferido)
- Relacionado con `tb_ventas.Lote`
- Contiene información de lotes disponibles
- Estados: Disponible, Apartado, Vendido

#### `tb_manzanas` (inferido)  
- Relacionado con `tb_ventas.Manzana`
- Agrupación de lotes por manzanas

#### `tb_clientes`
- Relacionado con `tb_ventas.Cliente` y `tb_ventas.Cliente2`
- Información de compradores

#### `tb_cobranza`
- Relacionado con ventas a crédito
- Gestión de pagos mensuales

## 🔄 Flujo de Proceso de Ventas Completo

### 1. Estados de Lotes y Ventas
```
Disponible → Apartado → Vendido
    ↓           ↓         ↓
  Lote      Anticipo   Crédito/
 Libre     Parcial    Contado
```

**Estados de Lotes (tb_lotes.Estado)**:
- **0**: Disponible para venta
- **1**: Apartado (reservado temporalmente)
- **2**: Vendido (con contrato firmado)
- **3**: No disponible/Cancelado

### 2. Tipos de Venta Identificados

#### 2.1 Apartado Simple (Función 34)
- **Estado**: 1 (Apartado)
- **Propósito**: Reserva temporal del lote
- **Anticipo**: Monto mínimo configurado por empresa
- **Duración**: Días configurados para cancelación automática
- **Características**:
  - No genera tabla de cobranza
  - Solo actualiza estado del lote
  - Sistema de cancelación automática

#### 2.2 Apartado con Enganche (Función 36)
- **Estado**: 1 (Apartado)
- **Propósito**: Apartado + pago de enganche
- **Características**:
  - Genera registros en `tb_cobranza` para mensualidades
  - Crea plan de pagos automático
  - Pago del enganche parcial o completo
  - Puede financiar el enganche restante

#### 2.3 Venta a Crédito (Función 37)
- **Estado**: 2 (Vendido)
- **Propósito**: Venta completa con financiamiento
- **Características**:
  - Genera tabla de amortización completa
  - Crea todos los pagos mensuales en `tb_cobranza`
  - Sistema de intereses por mora automático
  - Controla totalmente el proceso de cobranza

#### 2.4 Venta de Contado (Función 38)
- **Estado**: 2 (Vendido)
- **Propósito**: Pago completo inmediato
- **Características**:
  - No genera registros de cobranza
  - Marca como `Cobrado = TRUE`
  - Proceso de venta terminado
  - No requiere seguimiento de pagos

### 3. Modalidades de Pago Múltiples
El sistema maneja **pagos combinados** en una sola transacción:
- **Efectivo**: Pago en efectivo
- **Transferencia**: Transferencia bancaria 
- **Tarjeta**: Pago con tarjeta de crédito/débito
- **Cheque**: Pago con cheque

**Configuración por Pago**:
- Cada forma requiere cuenta bancaria específica
- Tipo de pago especificado (`tb_tipos_formas`)
- Referencia y ordenante para transferencias

## 🧮 **SISTEMA DE CÁLCULOS FINANCIEROS LEGACY**

### 1. Amortización Francesa - Análisis del Código

El sistema legacy implementa la fórmula de amortización francesa con las siguientes características:

#### Código de Cálculo (funciones.php líneas 7000-7100)
```php
// Cálculo de cuota mensual con intereses
if($meses_intereses!='' & $meses_intereses!=0){
    $contador_mi=1;
    for($x=1;$x<=$meses_intereses;$x++){ 
        $fecha_inicial = endCycles(date("y-m-d",strtotime($fecha_inicial)), 1,$fecha_uno);
        if($contador_mi==1){ 
            $cuota_mensual_interes = number_format((($total*($interes_anual/100))/12),2,".",""); 
            $total = number_format(($total+($total*($interes_anual/100))),2,".",""); 
        }    
        
        $interes_acumulativo = number_format($interes_acumulativo+$cuota_mensual_interes,2,".","");
        
        if($x==$meses_intereses){ 
            // Último pago con saldo restante
            mysqli_query($link,"INSERT INTO tb_cobranza SET ...Total=$total...");
            $total=0;
        }else{
            // Pagos mensuales normales
            mysqli_query($link,"INSERT INTO tb_cobranza SET ...Total=$cuota_mensual+$cuota_mensual_interes...");
            $total = number_format(($total-($cuota_mensual_interes+$cuota_mensual)),2,".","");
        }
    }
}
```

#### Características del Sistema:
1. **Interés Anual**: Se divide entre 12 para obtener mensual
2. **Cuota Fija**: Cuota mensual + interés mensual
3. **Fecha Inteligente**: Función `endCycles()` maneja fechas de pago
4. **Anualidades**: Permite pagos extraordinarios en fechas específicas
5. **Saldo Final**: El último pago incluye cualquier saldo restante

### 2. Sistema de Comisiones Automáticas - Super-Vendedor

#### Lógica de Comisiones (funciones.php líneas 4620-4636)
```php
// Sistema de jerarquía de vendedores
$vvendedor=0;$nvvendedor='';
$resVE=mysqli_query($link,"SELECT Tipo,SuperVendedor FROM tb_usuarios WHERE IdUsuario=$vendedor");		
$registroVE=mysqli_fetch_row($resVE);

if($registroVE[0]==5){
    // Si el vendedor es tipo 5, la comisión va al super-vendedor
    $vvendedor=$registroVE[1];
    $resVEN=mysqli_query($link,"SELECT Nombre FROM tb_usuarios WHERE IdUsuario=$registroVE[1]");		
    $registroVEN=mysqli_fetch_row($resVEN);
    $nvvendedor=$registroVEN[0];
}else{
    // Si no es tipo 5, la comisión va al vendedor directo
    $vvendedor=$vendedor;
    $nvvendedor=$rowV["Nombre"];
}

// Insertar comisión automáticamente
mysqli_query($link,"INSERT INTO tb_comisiones SET Total=$comision_apartado,
    Fecha='$fecharef',Hora='$rowL[vHora]',Vendedor=$vvendedor,NVendedor='$nvvendedor',
    Estatus=1,Proyecto=$rowL[Proyecto],...");
```

#### Tipos de Usuario y Comisiones:
- **Tipo 1**: Administrador (sin comisiones)
- **Tipo 2**: Vendedor Senior (comisiones directas)
- **Tipo 3**: Supervisor (comisiones directas + supervisión)
- **Tipo 4**: Manager (comisiones directas)
- **Tipo 5**: Vendedor Junior (comisiones van al SuperVendedor asignado)
- **Tipo 6**: Solo lectura (sin comisiones)

### 3. Flujo de Ingresos y Actualización de Saldos

#### Actualización de Cuentas Bancarias (funciones.php líneas 4587-4614)
```php
// Para cada forma de pago, actualizar saldo de cuenta
if($transferencia!=0){
    // Registro en tb_ingresos
    mysqli_query($link,"INSERT INTO tb_ingresos SET 
        ...Total=$transferencia,
        SaldoAnterior=$rowN[Saldo],
        SaldoPosterior=".($rowN['Saldo']+$transferencia).",
        Transferencia=$transferencia...");
    
    // Actualización del saldo real
    mysqli_query($link,"UPDATE tb_cuentas_proyectos SET 
        Saldo=".($rowN["Saldo"]+$transferencia)." 
        WHERE IdCuenta=$rowN[IdCuenta]");
}
```

#### Características del Sistema de Ingresos:
1. **Trazabilidad Completa**: Cada pago registra saldo anterior y posterior
2. **Múltiples Formas**: Un ingreso puede combinar hasta 4 formas de pago
3. **Cuentas por Proyecto**: Cada proyecto tiene cuentas bancarias específicas
4. **Folios Automáticos**: Sistema de numeración automática por tipo
5. **Auditoría**: Registro completo de usuario, turno y fecha/hora

### 4. Función "Redondear" - Análisis Detallado

La función "Redondear" en el sistema legacy tiene un propósito específico:

#### Objetivo:
Convertir pagos mensuales con centavos a pesos completos para facilitar el pago de los clientes.

#### Ejemplo:
```
Pago calculado: $2,847.23 MXN
Con "Redondear": $2,847.00 MXN
Diferencia: $0.23 MXN (se ajusta en el último pago)
```

#### Implementación (inferida del contexto):
- Se redondea hacia abajo cada pago mensual
- La diferencia acumulada se suma al último pago
- Solo aplica para ventas a crédito con pagos mensuales

## 🏦 **JERARQUÍA FINANCIERA Y CONFIGURACIÓN**

### 1. Flujo de Configuración por Empresa

El sistema legacy maneja configuración específica por empresa:

#### Tabla `tb_configuracion` - Campos Críticos:
```sql
ApartadoMinimo      -- Monto mínimo para apartar (ej: $5,000)
PorcentajeAnticipo  -- % del total para enganche (ej: 20%)
DiasAnticipo        -- Días para liquidar enganche (ej: 30)
ApartadoComision    -- Comisión fija por apartado (ej: $500)
PorcentajeComision  -- % comisión sobre venta total (ej: 3%)
```

### 2. Jerarquía de Cuentas Bancarias

#### Estructura Detectada:
```
Empresa (tb_empresas)
    ↓
Cuentas Bancarias (tb_cuentas_proyectos)
    ↓
Proyectos (tb_proyectos)
    ↓
Manzanas (tb_manzanas)
    ↓
Lotes (tb_lotes)
    ↓
Ventas (tb_ventas)
```

#### Flujo de Efectivo:
- **Ingresos**: Se registran por cuenta bancaria específica
- **Saldos**: Se actualizan en tiempo real
- **Trazabilidad**: Cada movimiento tiene saldo anterior/posterior
- **Separación**: Cada proyecto mantiene cuentas independientes

### 3. Sistema de Folios y Numeración

#### Tipos de Folios (tb_configuracion):
- `FolioVenta`: Numeración consecutiva de ventas
- `FolioApartado`: Numeración consecutiva de apartados  
- `FolioEnganche`: Numeración consecutiva de enganches
- `FoliosVariosPagos`: Numeración para pagos con múltiples formas

#### Auto-incremento Automático:
```php
// Actualización automática de folios
mysqli_query($link,"UPDATE tb_configuracion SET 
    FolioApartado=$rowO[FolioApartado]+1 
    WHERE Empresa=$rowL[Empresa]");
```

## 🔍 **ANÁLISIS DE FUNCIONES JAVASCRIPT LEGACY**

### 1. Sistema de Búsqueda Dinámica (ventas.js)

#### Funcionalidad Principal:
- **Búsqueda AJAX**: Filtros múltiples para ventas
- **Cascada Dinámica**: Empresa → Proyecto → Manzana → Lote
- **DataTables**: Integración con tablas dinámicas
- **Colores**: Sistema de código de colores por estado

#### Código Clave (ventas.js líneas 8-36):
```javascript
$('#cb_empresa_busqueda').on('change', function() {
    if($("#cb_empresa_busqueda").val()!='' & $("#cb_empresa_busqueda").val()!=0){
        $("#cb_proyecto").prop('disabled', true);
        $.ajax({
            data: "funcion=226&empresa="+$("#cb_empresa_busqueda").val(), 
            type: "POST", 
            dataType: "json", 
            url: "comandos/funciones.php",
            success: function(data){
                // Poblar select de proyectos dinámicamente
                var html='<option value="0">Seleccionar...</option>';
                for (var i = 0; i < data.length; i++) {
                    html+='<option value="'+data[i].id+'">'+data[i].texto+'</option>';
                }
                $("#cb_proyecto").html(html);
                $("#cb_proyecto").prop('disabled', false);
            }
        });
    }
});
```

### 2. Sistema de Colores y Estados

#### Codificación Visual:
- **Verde (#DCFAF2)**: Ventas completamente cobradas (estado=2)
- **Naranja (#FFEBD7)**: Ventas apartadas/pendientes (estado=1)
- **Blanco**: Ventas en proceso normal

## 🏗️ **ARQUITECTURA DE MIGRACIÓN RECOMENDADA**

### 1. Mapeo de Tablas Legacy → CI4

#### Ventas y Transacciones:
```
tb_ventas           → ventas + ventas_pagos
tb_ingresos         → ventas_pagos (normalizado)
tb_cobranza         → plan_pagos + cobranza
tb_comisiones       → comisiones_vendedores
```

#### Configuración:
```
tb_configuracion    → configuracion_empresa (empresas table)
tb_cuentas_proyectos → cuentas_bancarias
tb_tipos_formas     → formas_pago
```

### 2. Entidades CI4 Recomendadas:

#### VentaEntity:
```php
class Venta extends Entity {
    public function calcularComision(): float
    public function generarTablaAmortizacion(): array
    public function aplicarRedondeo(): bool
    public function getEstadoTexto(): string
}
```

#### ComisionEntity:
```php  
class Comision extends Entity {
    public function asignarVendedor(): int  // Lógica super-vendedor
    public function calcularMonto(): float
    public function marcarPagada(): bool
}
```

### 3. Servicios CI4 Recomendados:

#### VentaCalculoService:
- Implementar amortización francesa
- Manejar función "redondear"
- Calcular intereses por mora
- Generar tablas de pago

#### ComisionService:
- Detectar jerarquía vendedor/super-vendedor
- Calcular comisiones automáticas
- Procesar pagos de comisiones

#### IngresoService:
- Manejar múltiples formas de pago
- Actualizar saldos bancarios
- Generar folios automáticos
- Cada forma tiene tipo de operación
- Sistema de múltiples formas en un solo pago

### 5. Configuración por Empresa

#### Tabla: `tb_configuracion`
**Parámetros identificados del análisis del código**:
```sql
-- Configuración de apartados
ApartadoMinimo DECIMAL(10,2)     -- Monto mínimo para apartar lote
PorcentajeAnticipo DECIMAL(5,2)  -- % del total para enganche (ej: 20%)
DiasAnticipo INT                 -- Días para completar enganche
DiasCancelacion INT              -- Días antes de cancelar apartado
TipoAnticipo INT                 -- 1=Porcentual, 2=Fijo
AnticipoFijo DECIMAL(10,2)       -- Monto fijo de anticipo

-- Configuración de mora
InteresMora DECIMAL(10,2)        -- Monto fijo por mora (ej: 150.00)
TipoInteresMora INT              -- 1=Fijo, 2=Porcentual

-- Configuración de comisiones  
ApartadoComision DECIMAL(5,2)    -- % comisión por apartado
VentaComision DECIMAL(5,2)       -- % comisión por venta

-- Configuración de folios
FolioApartado VARCHAR(50)        -- Prefijo para folios
FoliosVariosPagos BOOLEAN        -- ¿Permite múltiples formas de pago?
```

### 4. Funciones Principales Identificadas (funciones.php)

#### Función 34: Apartado Simple
```php
// Línea ~2822 en funciones.php
mysqli_query($link,"INSERT INTO tb_ventas SET 
    Total=$total_lote,
    Proyecto=$rowL[Proyecto],
    NProyecto='$rowL[NProyecto]',  -- PROBLEMA: Desnormalización
    Manzana=$rowL[Manzana],
    NManzana='$rowL[NManzana]',    -- PROBLEMA: Desnormalización
    Lote=$lote,
    NLote='$rowL[Clave]',          -- PROBLEMA: Desnormalización
    Estado=1,
    NEstado='Apartado',            -- PROBLEMA: Desnormalización
    Anticipo=$monto_enganche,
    DiasCancelacion=$rowO[DiasCancelacion],
    ComisionApartado=$comision_apartado,
    ComisionTotal=$comision,
    Estatus=1,
    Empresa=$rowL[Empresa],
    NEmpresa='$rowL[NEmpresa]'
");

// Actualizar estado del lote
mysqli_query($link,"UPDATE tb_lotes SET Estado=1,NEstado='Apartado' WHERE IdLote=$lote");
```

#### Función 36: Apartado con Enganche
```php
// Línea ~3116 en funciones.php - PROCESO COMPLEJO
mysqli_query($link,"INSERT INTO tb_ventas SET 
    Total=$total_lote,
    -- ... campos base ...
    Estado=1,
    NEstado='Apartado',
    AnticipoPagado=$totalrecibido,
    AnticipoCredito=TRUE,          -- Enganche financiado
    TotalPagado=$totalrecibido,
    FolioInterno='$referencia',
    Cliente2=$cliente2,            -- Soporte para cónyuge
    NCliente2='$ncliente2'
");

// GENERACIÓN AUTOMÁTICA DE COBRANZA
$fecha_inicial = new DateTime($fecharef);
for($i = 1; $i <= $meses_financiar; $i++) {
    $fecha_mensualidad = $fecha_inicial->add(new DateInterval('P1M'));
    mysqli_query($link,"INSERT INTO tb_cobranza SET 
        Venta=$nueva_venta_id,
        Cliente=$cliente,
        Total=$mensualidad,
        FechaFinal='".$fecha_mensualidad->format('Y-m-d')."',
        Plazo=$i,
        TipoCredito=2
    ");
}
```

#### Función 37: Venta a Crédito Completa
```php
// Línea ~3597 en funciones.php
mysqli_query($link,"INSERT INTO tb_ventas SET 
    Total=$total_lote,
    -- ... campos base ...
    Estado=2,                      -- VENDIDO
    NEstado='Vendido',
    Credito=TRUE,                  -- A CRÉDITO
    AnticipoPagado=$totalrecibido,
    AnticipoCobrado=TRUE,
    TotalPagado=$totalrecibido,
    Estatus=1
");

// Actualizar lote como vendido
mysqli_query($link,"UPDATE tb_lotes SET Estado=2,NEstado='Vendido' WHERE IdLote=$lote");
```

#### Función 38: Venta de Contado
```php
// Línea ~4053 en funciones.php
mysqli_query($link,"INSERT INTO tb_ventas SET 
    Total=$total_lote,
    -- ... campos base ...
    Estado=2,                      -- VENDIDO
    NEstado='Vendido',
    Credito=FALSE,                 -- DE CONTADO
    AnticipoPagado=$monto_enganche,
    AnticipoCobrado=TRUE,
    Cobrado=TRUE,                  -- TOTALMENTE COBRADO
    TotalPagado=$totalrecibido,    -- PAGO COMPLETO
    Estatus=1
");
```

## 🏭 SISTEMA DE GENERACIÓN AUTOMÁTICA DE CONTRATOS

### Archivo Principal: `documento.php`
**Ubicación**: `./administracion/documento.php`

#### Tecnología Utilizada
- **PhpWord**: Para generación de documentos DOCX
- **Plantillas Base**: `documentos/contrato_nuevo.docx`
- **Conversión a PDF**: Sistema automático de conversión

#### Proceso de Generación
```php
// Cargar plantilla base
$documento = new \PhpOffice\PhpWord\TemplateProcessor("documentos/contrato_nuevo.docx");

// Variables dinámicas principales
$documento->setValue('folio', $row["vFolio"]);
$documento->setValue('cliente', strtoupper($row["clNombre"]));
$documento->setValue('lote', strtoupper($row["loNumero"]));
$documento->setValue('manzana', strtoupper($row["NManzana"]));
$documento->setValue('area', $row["Area"]);
$documento->setValue('area_letras', convertirALetra2($row["Area"]));

// Datos financieros
$documento->setValue('total', formatearMoneda($row["Total"]));
$documento->setValue('anticipo', formatearMoneda($row["Anticipo"]));

// Datos del representante legal
$documento->setValue('gerente', strtoupper($rowGe["Nombre"]));
$documento->setValue('gerente_domicilio', $gerente_domicilio);

// Medidas y colindancias
$documento->setValue('norte', $row["Frente"]." METROS");
$documento->setValue('sur', $row["Fondo"]." METROS");
$documento->setValue('este', $row["LatIzq"]." METROS");
$documento->setValue('oeste', $row["LatDer"]." METROS");
```

#### Variables del Sistema (50+ variables)
**Datos del Cliente**:
- Nombre completo, nacionalidad, estado civil
- Domicilio completo con formato estructurado
- Información de identificación (INE, CURP, RFC)
- Datos de cónyuge (si aplica)
- Beneficiarios y referencias

**Datos del Lote**:
- Número de lote, manzana, proyecto
- Área total y construcción
- Medidas y colindancias detalladas
- Ubicación geográfica

**Datos Financieros**:
- Precio total y anticipo
- Montos convertidos a letras automáticamente
- Plan de pagos (si es a crédito)
- Fechas de vencimiento

### Sistema de Conversión Numérica
**Archivos**: `numeros_letras.php`, `fecha_letras.php`
```php
// Conversión automática de números a letras
$documento->setValue('area_letras', convertirALetra2($row["Area"]));
$documento->setValue('total_letras', convertirALetra($row["Total"]));
$documento->setValue('fecha_letras', obtenerFechaEnLetraCinco($fecha));
```

## 💰 SISTEMA DE COBRANZA Y MENSUALIDADES

### Tabla Principal: `tb_cobranza`
```sql
CREATE TABLE `tb_cobranza` (
  `IdCobranza` int(11) NOT NULL AUTO_INCREMENT,
  `Venta` int(11) DEFAULT '0',           -- FK a tb_ventas
  `Cliente` int(11) DEFAULT '0',         -- FK a tb_clientes  
  `NCliente` varchar(1000),              -- DESNORMALIZADO
  `Lote` int(11) DEFAULT '0',           -- FK a tb_lotes
  `NLote` varchar(1000),                -- DESNORMALIZADO
  `Proyecto` int(11) DEFAULT '0',       -- FK a tb_proyectos
  `NProyecto` varchar(1000),            -- DESNORMALIZADO
  `Empresa` int(11) DEFAULT '0',        -- FK a tb_empresas
  `NEmpresa` varchar(1000),             -- DESNORMALIZADO
  `Manzana` int(11) DEFAULT '0',        -- FK a tb_manzanas
  `NManzana` varchar(100),              -- DESNORMALIZADO
  `Total` double DEFAULT '0',           -- Monto del pago
  `TotalPagado` double DEFAULT '0',     -- Monto ya pagado
  `TotalSI` double DEFAULT '0',         -- Total sin intereses
  `FechaInicial` date,                  -- Fecha de creación
  `FechaFinal` date,                    -- Fecha de vencimiento
  `Plazo` varchar(100),                 -- Número de mensualidad
  `Cobrado` tinyint(4) DEFAULT '0',     -- ¿Ya fue cobrado?
  `Estatus` int(11) DEFAULT '0',        -- Activo/Inactivo
  `TipoCredito` int(11) DEFAULT '0',    -- 1=Anticipo, 2=Mensualidad
  `Interes` double DEFAULT '0',         -- Intereses acumulados
  `MesesInteres` int(11) DEFAULT '0',   -- Meses con interés
  `Fecha` date,                         -- Fecha de registro
  `Hora` time                           -- Hora de registro
);
```

### Tipos de Cobranza
1. **TipoCredito = 1**: Anticipo/Enganche
   - Pago inicial requerido
   - Una sola exhibición o parcialidades
   - Vencimiento específico

2. **TipoCredito = 2**: Mensualidades
   - Pagos recurrentes mensuales
   - Generados automáticamente al crear venta a crédito
   - Sistema de intereses por mora

### Algoritmo de Intereses por Mora
**Ubicación**: `funciones.php` líneas 32-164 y `cobranza.php` líneas 32-68

```php
// EJECUCIÓN AUTOMÁTICA EN CADA LOGIN
$resCo = mysqli_query($link, "SELECT IdCobranza, TotalPagado, Total, Empresa, 
    Venta, FechaFinal, Proyecto FROM tb_cobranza 
    WHERE (DATEDIFF(FechaFinal,NOW()) < -5) 
    AND Estatus=1 AND Cobrado=FALSE AND Interes=0 AND TipoCredito=2");

while($rowCo = mysqli_fetch_array($resCo)) {
    // Obtener configuración de interés de mora
    $resEm = mysqli_query($link, "SELECT InteresMora FROM tb_configuracion 
        WHERE Empresa=$rowCo[Empresa]");
    $rowEm = mysqli_fetch_array($resEm);
    
    if($rowEm["InteresMora"] != 0) {
        $totalInteres = number_format($rowEm["InteresMora"], 2);
        
        // Insertar registro de interés
        mysqli_query($link, "INSERT INTO tb_cobranza_intereses SET 
            Cobranza=$rowCo[IdCobranza],
            Interes=$rowEm[InteresMora],
            TotalInteres=$totalInteres,
            Fecha=CURRENT_DATE()");
        
        // Actualizar cobranza con interés
        mysqli_query($link, "UPDATE tb_cobranza SET 
            TotalSI=Total,
            Total=Total+$totalInteres,
            Interes=Interes+$totalInteres,
            MesesInteres=MesesInteres+1 
            WHERE IdCobranza=$rowCo[IdCobranza]");
        
        // Actualizar total de la venta
        mysqli_query($link, "UPDATE tb_ventas SET 
            Total=Total+$totalInteres,
            Intereses=Intereses+$totalInteres 
            WHERE IdVenta=$rowCo[Venta]");
    }
}
```

### Configuración de Mora por Empresa
**Tabla**: `tb_configuracion`
```sql
-- Parámetros por empresa
InteresMora DECIMAL(10,2)  -- Monto fijo de interés (ej: 150.00)
DiasGracia INT(11)         -- Días antes de aplicar mora (ej: 5)
TipoInteres INT(11)        -- 1=Fijo, 2=Porcentual
```

### Sistema de Cancelación Automática de Apartados
**Ubicación**: `funciones.php` líneas 47-62

```php
// EJECUCIÓN AUTOMÁTICA EN CADA LOGIN
$resA = mysqli_query($link, "SELECT IdVenta, Vendedor, Lote, Total, 
    Empresa, NEmpresa, Proyecto, NProyecto, Cliente, NCliente, Fecha, Hora 
    FROM tb_ventas 
    WHERE Estado=1 AND TotalPagado=0 AND Estatus=1 
    AND (TIMESTAMP(CURRENT_DATE(),CURRENT_TIME()) >= 
         TIMESTAMP(DATE_ADD(TIMESTAMP(Fecha,Hora), INTERVAL DiasCancelacion DAY)))");

while($rowA = mysqli_fetch_array($resA)) {
    // Cancelar la venta
    mysqli_query($link, "UPDATE tb_ventas SET Estatus=2 WHERE IdVenta=$rowA[IdVenta]");
    
    // Liberar el lote
    mysqli_query($link, "UPDATE tb_lotes SET 
        Estatus=1, Estado=0, NEstado='Disponible' 
        WHERE IdLote=$rowA[Lote]");
    
    // Penalizar al vendedor
    mysqli_query($link, "UPDATE tb_usuarios SET 
        ApartadosError=ApartadosError+1 
        WHERE IdUsuario=$rowA[Vendedor]");
    
    // Registrar en historial de apartados cancelados
    mysqli_query($link, "INSERT INTO tb_historial_apartados SET 
        Venta=$rowA[IdVenta],
        Vendedor=$rowA[Vendedor],
        Cliente=$rowA[Cliente],
        Fecha=CURRENT_DATE(),
        Movimiento=1");
}

// Suspender vendedores con más de 3 errores
mysqli_query($link, "UPDATE tb_usuarios SET 
    Estado=2, NEstado='Suspendido' 
    WHERE ApartadosError >= 3");
```

## ⚠️ Problemas Críticos Identificados

### 1. Desnormalización Extrema
- **50+ campos "N" redundantes**: `NProyecto`, `NManzana`, `NLote`, `NCliente`, etc.
- **Almacenamiento duplicado**: Nombres se guardan en cada registro
- **Sin integridad referencial**: Datos pueden volverse inconsistentes
- **Tamaño de base de datos**: Crecimiento exponencial innecesario

### 2. Arquitectura No Relacional
- **No hay foreign keys**: Relaciones "implícitas" por convención
- **Joins complejos**: Uso de IDs sin constraints
- **Riesgo de inconsistencia**: Datos huérfanos y referencias rotas
- **Dificultad de migración**: Require limpieza masiva de datos

### 3. Lógica de Negocio Centralizada
- **Archivo funciones.php**: 10,000+ líneas de código
- **Funciones gigantes**: Algunas con 500+ líneas
- **Mezcla de responsabilidades**: UI, lógica y datos juntos
- **Difícil mantenimiento**: Cambios requieren tocar código crítico

### 4. Problemas de Seguridad Graves
- **SQL Injection**: Concatenación directa de variables
- **Validaciones insuficientes**: Sanitización básica con `htmlspecialchars`
- **Sesiones inseguras**: Manejo manual de autenticación
- **Exposición de datos**: Información sensible en logs

### 5. Problemas de Escalabilidad
- **Queries N+1**: Consultas repetitivas en loops
- **Sin paginación**: Carga completa de datasets grandes
- **Sin cache**: Consultas repetidas sin optimización
- **Transacciones manuales**: Sin rollback automático

## 📊 SIMULADOR DE CAPITAL Y REESTRUCTURACIÓN

### Archivo: `simulador_capital.php`
**Propósito**: Calcular abonos a capital y reestructuración de pagos

#### Funcionalidades Principales
1. **Cálculo de Saldo Actual**:
   ```php
   $saldo_total = number_format(($rowV["Total"] - $rowV["TotalPagado"]) - $vrecibido, 2, '.', '');
   ```

2. **Recálculo de Mensualidades**:
   ```php
   $monto_financiar = number_format(((($rowV["Total"] - $rowV["TotalPagado"]) - $vrecibido) 
       / $rowCoP["vConteo"]), 0, '.', '');
   ```

3. **Tabla de Amortización Dinámica**:
   - Muestra pagos pendientes
   - Calcula nueva mensualidad con abono
   - Proyecta saldos futuros
   - Genera reporte imprimible

#### Variables del Simulador
- **Valor del Lote**: Total de la venta
- **Enganche Pagado**: Anticipo ya cubierto
- **Total Pagado**: Suma de todos los pagos
- **Saldo**: Deuda pendiente
- **Pagos Pendientes**: Mensualidades restantes
- **Monto Abono**: Abono propuesto a capital
- **Nueva Mensualidad**: Recalculada con el abono

## 🗃️ TABLAS Y RELACIONES IDENTIFICADAS

### Tablas Core del Sistema
```sql
-- Tabla principal de ventas
tb_ventas (94 campos) - Registro principal de transacciones

-- Tabla de cobranza y mensualidades  
tb_cobranza (25+ campos) - Pagos programados y realizados

-- Tabla de intereses por mora
tb_cobranza_intereses - Registro de penalizaciones

-- Tabla de ingresos/pagos
tb_ingresos - Pagos realizados por clientes

-- Tablas de inventario
tb_lotes - Inventario de propiedades
tb_manzanas - Agrupación de lotes
tb_proyectos - Desarrollos inmobiliarios

-- Tablas de configuración
tb_configuracion - Parámetros por empresa
tb_empresas - Empresas desarrolladoras

-- Tablas de clientes y personal
tb_clientes - Base de datos de compradores
tb_usuarios - Personal de ventas y administrativo
```

### Convención de Nomenclatura "N"
**Patrón identificado**: Prefijo "N" = Nombre/Número descriptivo

```php
// Ejemplos del código analizado:
$registro["NProyecto"] = "Valle Natura";
$registro["NManzana"] = "Manzana 1";
$registro["NLote"] = "Lote 25";
$registro["NCliente"] = "JUAN PÉREZ GARCÍA";
$registro["NEmpresa"] = "ANVAR INMOBILIARIA";
$registro["NVendedor"] = "MARÍA GONZÁLEZ";
$registro["NEstado"] = "Apartado"; // o "Vendido"
```

### Relaciones Implícitas Identificadas
```
tb_ventas.Proyecto → tb_proyectos.IdProyecto
tb_ventas.Manzana → tb_manzanas.IdManzana
tb_ventas.Lote → tb_lotes.IdLote
tb_ventas.Cliente → tb_clientes.IdCliente
tb_ventas.Vendedor → tb_usuarios.IdUsuario
tb_ventas.Empresa → tb_empresas.IdEmpresa

tb_cobranza.Venta → tb_ventas.IdVenta
tb_cobranza.Cliente → tb_clientes.IdCliente
tb_cobranza.Lote → tb_lotes.IdLote

tb_ingresos.Venta → tb_ventas.IdVenta
tb_ingresos.Cliente → tb_clientes.IdCliente
```

## 🎯 Propuesta de Migración a CodeIgniter 4

### 1. Modelo de Datos Normalizado

#### Nueva Estructura Propuesta (CI4)
```sql
-- Tabla principal de ventas (simplificada y normalizada)
CREATE TABLE ventas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio_interno VARCHAR(100),
    total DECIMAL(12,2) NOT NULL,
    total_pagado DECIMAL(12,2) DEFAULT 0,
    anticipo DECIMAL(12,2) DEFAULT 0,
    anticipo_pagado DECIMAL(12,2) DEFAULT 0,
    
    -- Referencias normalizadas (FK)
    proyecto_id INT NOT NULL,
    lote_id INT NOT NULL,
    cliente_id INT NOT NULL,
    cliente_conyuge_id INT NULL,
    vendedor_id INT NOT NULL,
    empresa_id INT NOT NULL,
    
    -- Estados y tipos
    estado_venta_id INT NOT NULL,      -- 1=Apartado, 2=Vendido
    tipo_credito_id INT NOT NULL,      -- 1=Contado, 2=Crédito
    
    -- Configuración de crédito
    es_credito BOOLEAN DEFAULT FALSE,
    anticipo_cobrado BOOLEAN DEFAULT FALSE,
    cobrado_completo BOOLEAN DEFAULT FALSE,
    
    -- Comisiones
    comision_apartado DECIMAL(10,2) DEFAULT 0,
    comision_total DECIMAL(10,2) DEFAULT 0,
    
    -- Control de apartados
    dias_cancelacion INT DEFAULT 0,
    fecha_limite_apartado DATETIME NULL,
    
    -- Descuentos
    descuento DECIMAL(10,2) DEFAULT 0,
    tipo_descuento ENUM('porcentaje', 'fijo') DEFAULT 'fijo',
    
    -- Intereses
    intereses_acumulados DECIMAL(10,2) DEFAULT 0,
    
    -- Auditoría
    fecha_venta DATE NOT NULL,
    usuario_creacion_id INT NOT NULL,
    observaciones TEXT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices y constraints
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    FOREIGN KEY (lote_id) REFERENCES lotes(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (cliente_conyuge_id) REFERENCES clientes(id),
    FOREIGN KEY (vendedor_id) REFERENCES staff(id),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    FOREIGN KEY (estado_venta_id) REFERENCES estados_venta(id),
    FOREIGN KEY (tipo_credito_id) REFERENCES tipos_credito(id),
    
    INDEX idx_proyecto_fecha (proyecto_id, fecha_venta),
    INDEX idx_cliente_fecha (cliente_id, fecha_venta),
    INDEX idx_vendedor_fecha (vendedor_id, fecha_venta),
    INDEX idx_estado_activo (estado_venta_id, activo)
);

-- Tabla de cobranza normalizada
CREATE TABLE cobranza (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT NOT NULL,
    cliente_id INT NOT NULL,
    
    -- Montos
    total DECIMAL(12,2) NOT NULL,
    total_pagado DECIMAL(12,2) DEFAULT 0,
    total_sin_intereses DECIMAL(12,2) NOT NULL,
    intereses DECIMAL(12,2) DEFAULT 0,
    
    -- Fechas
    fecha_inicial DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    
    -- Control de pago
    numero_pago INT NOT NULL,                    -- 1, 2, 3... (mensualidad)
    tipo_credito ENUM('anticipo', 'mensualidad') NOT NULL,
    cobrado BOOLEAN DEFAULT FALSE,
    meses_con_interes INT DEFAULT 0,
    
    -- Auditoría
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    
    INDEX idx_venta_fecha (venta_id, fecha_vencimiento),
    INDEX idx_cliente_vencimiento (cliente_id, fecha_vencimiento),
    INDEX idx_vencimientos_pendientes (fecha_vencimiento, cobrado)
);

-- Tabla de pagos realizados
CREATE TABLE pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT NOT NULL,
    cobranza_id INT NULL,                        -- NULL para pagos de contado
    cliente_id INT NOT NULL,
    
    -- Monto y formas de pago
    total_pago DECIMAL(12,2) NOT NULL,
    efectivo DECIMAL(12,2) DEFAULT 0,
    transferencia DECIMAL(12,2) DEFAULT 0,
    tarjeta DECIMAL(12,2) DEFAULT 0,
    cheque DECIMAL(12,2) DEFAULT 0,
    
    -- Referencias de pago
    referencia_transferencia VARCHAR(100) NULL,
    banco_cuenta_id INT NULL,
    fecha_referencia DATE NULL,
    
    -- Control
    es_enganche BOOLEAN DEFAULT FALSE,
    es_apartado BOOLEAN DEFAULT FALSE,
    numero_recibo VARCHAR(50) NULL,
    
    -- Auditoría
    fecha_pago DATE NOT NULL,
    usuario_registro_id INT NOT NULL,
    observaciones TEXT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (cobranza_id) REFERENCES cobranza(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_registro_id) REFERENCES staff(id),
    
    INDEX idx_venta_fecha (venta_id, fecha_pago),
    INDEX idx_cliente_fecha (cliente_id, fecha_pago)
);

-- Tablas de catálogos
CREATE TABLE estados_venta (
    id INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,                -- Apartado, Vendido
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE tipos_credito (
    id INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,                -- Contado, Crédito
    descripcion TEXT,
    requiere_cobranza BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de configuración por empresa
CREATE TABLE configuracion_ventas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    empresa_id INT NOT NULL,
    
    -- Apartados
    apartado_minimo DECIMAL(10,2) DEFAULT 0,
    porcentaje_anticipo DECIMAL(5,2) DEFAULT 20.00,
    anticipo_fijo DECIMAL(10,2) DEFAULT 0,
    tipo_anticipo ENUM('porcentaje', 'fijo') DEFAULT 'porcentaje',
    dias_cancelacion_apartado INT DEFAULT 15,
    
    -- Intereses
    interes_mora DECIMAL(10,2) DEFAULT 0,
    dias_gracia_mora INT DEFAULT 5,
    tipo_interes ENUM('fijo', 'porcentual') DEFAULT 'fijo',
    
    -- Comisiones
    comision_apartado_porcentaje DECIMAL(5,2) DEFAULT 5.00,
    comision_venta_porcentaje DECIMAL(5,2) DEFAULT 3.00,
    
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    UNIQUE KEY unique_empresa (empresa_id)
);
```

### 2. Entidades y Modelos Propuestos (CI4)

#### VentaEntity
```php
<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Models\{ProyectoModel, LoteModel, ClienteModel, StaffModel};

class Venta extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'total' => 'float',
        'total_pagado' => 'float',
        'anticipo' => 'float',
        'anticipo_pagado' => 'float',
        'es_credito' => 'boolean',
        'anticipo_cobrado' => 'boolean',
        'cobrado_completo' => 'boolean',
        'fecha_venta' => 'datetime',
        'fecha_limite_apartado' => 'datetime',
        'activo' => 'boolean'
    ];

    protected $dates = ['fecha_venta', 'fecha_limite_apartado', 'created_at', 'updated_at'];

    // Relaciones con otros modelos
    public function getProyecto()
    {
        return model(ProyectoModel::class)->find($this->proyecto_id);
    }

    public function getLote()
    {
        return model(LoteModel::class)->find($this->lote_id);
    }

    public function getCliente()
    {
        return model(ClienteModel::class)->find($this->cliente_id);
    }

    public function getVendedor()
    {
        return model(StaffModel::class)->find($this->vendedor_id);
    }

    // Métodos de negocio
    public function getSaldoPendiente(): float
    {
        return $this->total - $this->total_pagado;
    }

    public function getPorcentajePagado(): float
    {
        if ($this->total <= 0) return 0;
        return ($this->total_pagado / $this->total) * 100;
    }

    public function isVencido(): bool
    {
        if (!$this->fecha_limite_apartado) return false;
        return $this->fecha_limite_apartado < date('Y-m-d H:i:s');
    }

    public function puedeSerCancelado(): bool
    {
        return $this->estado_venta_id === 1 && // Apartado
               $this->total_pagado === 0 &&
               $this->isVencido();
    }

    public function calcularComisionTotal(): float
    {
        return $this->comision_apartado + $this->comision_total;
    }

    // Estados
    public function isApartado(): bool
    {
        return $this->estado_venta_id === 1;
    }

    public function isVendido(): bool
    {
        return $this->estado_venta_id === 2;
    }

    public function isCobradoCompleto(): bool
    {
        return $this->cobrado_completo || $this->total_pagado >= $this->total;
    }
}
```

#### CobranzaEntity
```php
<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Cobranza extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'cliente_id' => 'integer',
        'total' => 'float',
        'total_pagado' => 'float',
        'total_sin_intereses' => 'float',
        'intereses' => 'float',
        'numero_pago' => 'integer',
        'cobrado' => 'boolean',
        'meses_con_interes' => 'integer',
        'activo' => 'boolean'
    ];

    protected $dates = ['fecha_inicial', 'fecha_vencimiento', 'created_at', 'updated_at'];

    // Métodos de negocio
    public function getSaldoPendiente(): float
    {
        return $this->total - $this->total_pagado;
    }

    public function isVencido(): bool
    {
        return $this->fecha_vencimiento < date('Y-m-d') && !$this->cobrado;
    }

    public function getDiasVencido(): int
    {
        if (!$this->isVencido()) return 0;
        
        $fecha_vencimiento = new \DateTime($this->fecha_vencimiento);
        $hoy = new \DateTime();
        return $hoy->diff($fecha_vencimiento)->days;
    }

    public function requiereInteresMora(): bool
    {
        return $this->isVencido() && $this->getDiasVencido() > 5;
    }

    public function isAnticipo(): bool
    {
        return $this->tipo_credito === 'anticipo';
    }

    public function isMensualidad(): bool
    {
        return $this->tipo_credito === 'mensualidad';
    }
}
```

### 3. Servicios y Controladores Propuestos

#### VentasService
```php
<?php
namespace App\Services;

use App\Models\{VentaModel, CobranzaModel, LoteModel, PagoModel};
use App\Entities\{Venta, Cobranza};
use CodeIgniter\Database\BaseConnection;

class VentasService
{
    protected VentaModel $ventaModel;
    protected CobranzaModel $cobranzaModel;
    protected LoteModel $loteModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->ventaModel = model(VentaModel::class);
        $this->cobranzaModel = model(CobranzaModel::class);
        $this->loteModel = model(LoteModel::class);
        $this->db = \Config\Database::connect();
    }

    /**
     * Crear apartado simple
     */
    public function crearApartado(array $datos): array
    {
        $this->db->transStart();

        try {
            // Validar disponibilidad del lote
            $lote = $this->loteModel->find($datos['lote_id']);
            if (!$lote || $lote->estado !== 'disponible') {
                throw new \Exception('El lote no está disponible');
            }

            // Crear venta apartado
            $venta = new Venta([
                'folio_interno' => $this->generarFolio(),
                'total' => $datos['total'],
                'anticipo' => $datos['anticipo'],
                'proyecto_id' => $datos['proyecto_id'],
                'lote_id' => $datos['lote_id'],
                'cliente_id' => $datos['cliente_id'],
                'vendedor_id' => $datos['vendedor_id'],
                'empresa_id' => $datos['empresa_id'],
                'estado_venta_id' => 1, // Apartado
                'tipo_credito_id' => 2, // Crédito
                'es_credito' => true,
                'fecha_venta' => date('Y-m-d'),
                'fecha_limite_apartado' => date('Y-m-d H:i:s', strtotime('+15 days')),
                'usuario_creacion_id' => auth()->id()
            ]);

            $ventaId = $this->ventaModel->save($venta);

            // Actualizar estado del lote
            $this->loteModel->update($datos['lote_id'], ['estado' => 'apartado']);

            $this->db->transComplete();

            return ['success' => true, 'venta_id' => $ventaId];

        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Confirmar venta a crédito
     */
    public function confirmarVentaCredito(int $ventaId, array $datosPago): array
    {
        $this->db->transStart();

        try {
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                throw new \Exception('Venta no encontrada');
            }

            // Actualizar venta
            $venta->estado_venta_id = 2; // Vendido
            $venta->anticipo_pagado = $datosPago['monto_recibido'];
            $venta->total_pagado = $datosPago['monto_recibido'];
            $venta->anticipo_cobrado = $venta->anticipo_pagado >= $venta->anticipo;

            $this->ventaModel->save($venta);

            // Generar plan de cobranza
            $this->generarPlanCobranza($venta, $datosPago);

            // Actualizar lote como vendido
            $this->loteModel->update($venta->lote_id, ['estado' => 'vendido']);

            $this->db->transComplete();

            return ['success' => true];

        } catch (\Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generar plan de cobranza mensual
     */
    private function generarPlanCobranza(Venta $venta, array $config): void
    {
        $saldoFinanciar = $venta->total - $venta->total_pagado;
        $meses = $config['meses'] ?? 12;
        $mensualidad = $saldoFinanciar / $meses;
        
        $fechaInicial = new \DateTime($venta->fecha_venta);
        
        for ($i = 1; $i <= $meses; $i++) {
            $fechaVencimiento = clone $fechaInicial;
            $fechaVencimiento->modify("+{$i} month");
            
            $cobranza = new Cobranza([
                'venta_id' => $venta->id,
                'cliente_id' => $venta->cliente_id,
                'total' => $mensualidad,
                'total_sin_intereses' => $mensualidad,
                'fecha_inicial' => $venta->fecha_venta,
                'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                'numero_pago' => $i,
                'tipo_credito' => 'mensualidad'
            ]);
            
            $this->cobranzaModel->save($cobranza);
        }
    }

    /**
     * Cancelar apartados vencidos automáticamente
     */
    public function cancelarApartadosVencidos(): int
    {
        $apartadosVencidos = $this->ventaModel
            ->where('estado_venta_id', 1)
            ->where('total_pagado', 0)
            ->where('fecha_limite_apartado <', date('Y-m-d H:i:s'))
            ->where('activo', true)
            ->findAll();

        $cancelados = 0;
        
        foreach ($apartadosVencidos as $venta) {
            $this->db->transStart();
            
            // Cancelar venta
            $this->ventaModel->update($venta->id, ['activo' => false]);
            
            // Liberar lote
            $this->loteModel->update($venta->lote_id, ['estado' => 'disponible']);
            
            // Registrar penalización al vendedor (si aplica)
            
            $this->db->transComplete();
            $cancelados++;
        }
        
        return $cancelados;
    }

    private function generarFolio(): string
    {
        return 'V-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
```

#### AdminVentasController
```php
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\VentasService;
use App\Models\{VentaModel, ProyectoModel, ClienteModel};

class AdminVentasController extends BaseController
{
    protected VentasService $ventasService;
    protected VentaModel $ventaModel;

    public function __construct()
    {
        $this->ventasService = new VentasService();
        $this->ventaModel = model(VentaModel::class);
    }

    public function index()
    {
        $ventas = $this->ventaModel
            ->select('ventas.*, proyectos.nombre as proyecto_nombre, clientes.nombre as cliente_nombre')
            ->join('proyectos', 'proyectos.id = ventas.proyecto_id')
            ->join('clientes', 'clientes.id = ventas.cliente_id')
            ->where('ventas.activo', true)
            ->orderBy('ventas.created_at', 'DESC')
            ->paginate(20);

        return view('admin/ventas/index', [
            'ventas' => $ventas,
            'pager' => $this->ventaModel->pager
        ]);
    }

    public function crear()
    {
        if ($this->request->getMethod() === 'POST') {
            $datos = $this->request->getPost();
            
            $validacion = $this->validate([
                'proyecto_id' => 'required|integer',
                'lote_id' => 'required|integer', 
                'cliente_id' => 'required|integer',
                'total' => 'required|decimal',
                'anticipo' => 'required|decimal'
            ]);

            if (!$validacion) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $resultado = $this->ventasService->crearApartado($datos);

            if ($resultado['success']) {
                return redirect()->to('/admin/ventas')->with('success', 'Apartado creado exitosamente');
            } else {
                return redirect()->back()->withInput()->with('error', $resultado['message']);
            }
        }

        return view('admin/ventas/crear', [
            'proyectos' => model(ProyectoModel::class)->where('activo', true)->findAll(),
            'clientes' => model(ClienteModel::class)->where('activo', true)->findAll()
        ]);
    }

    public function confirmarVenta(int $id)
    {
        if ($this->request->getMethod() === 'POST') {
            $datosPago = $this->request->getPost();
            
            $resultado = $this->ventasService->confirmarVentaCredito($id, $datosPago);
            
            if ($resultado['success']) {
                return redirect()->to('/admin/ventas')->with('success', 'Venta confirmada exitosamente');
            } else {
                return redirect()->back()->with('error', $resultado['message']);
            }
        }

        $venta = $this->ventaModel->find($id);
        return view('admin/ventas/confirmar', ['venta' => $venta]);
    }
}
```

## 📋 Roadmap de Migración

### Fase 1: Estructuras Base ✅
- [x] Análisis completo del sistema legacy ✅
- [x] Identificación de 4 tipos de venta ✅
- [x] Mapeo de relaciones y desnormalización ✅
- [x] Documentación del flujo de cobranza ✅
- [x] Análisis del sistema de contratos ✅

### Fase 2: Diseño Normalizado 🚧
- [ ] Migración Create Table para `ventas`
- [ ] Migración Create Table para `cobranza`
- [ ] Migración Create Table para `pagos`
- [ ] Migración Create Table para catálogos (estados_venta, tipos_credito)
- [ ] Seeds para datos maestros

### Fase 3: Entidades y Modelos 📋
- [ ] VentaEntity con métodos de negocio
- [ ] CobranzaEntity con cálculos
- [ ] PagoEntity con validaciones
- [ ] VentaModel con relaciones
- [ ] CobranzaModel con scopes

### Fase 4: Servicios de Negocio 📋
- [ ] VentasService - Lógica principal
- [ ] CobranzaService - Gestión de pagos
- [ ] ContratosService - Generación automática
- [ ] InteresesService - Cálculo de mora
- [ ] CancelacionService - Apartados vencidos

### Fase 5: Controladores y Vistas 📋
- [ ] AdminVentasController (CRUD completo)
- [ ] AdminCobranzaController (gestión pagos)
- [ ] Vistas AdminLTE para ventas
- [ ] Formularios de apartado/venta
- [ ] Simulador de capital web

### Fase 6: Jobs y Automatización 📋
- [ ] Job: CancelarApartadosVencidos (diario)
- [ ] Job: CalcularInteresesMora (diario) 
- [ ] Job: GenerarReportesCobranza (semanal)
- [ ] Scheduler de CodeIgniter 4

### Fase 7: Migración de Datos 📋
- [ ] Script de migración `tb_ventas` → `ventas`
- [ ] Script de migración `tb_cobranza` → `cobranza`
- [ ] Validación de integridad de datos
- [ ] Limpieza de datos desnormalizados
- [ ] Establecimiento de foreign keys

### Fase 8: Funcionalidades Avanzadas 📋
- [ ] Sistema de comisiones automático
- [ ] Generación de contratos con PhpWord
- [ ] Estados de cuenta dinámicos
- [ ] Dashboard de ventas en tiempo real
- [ ] Reportes administrativos
- [ ] Integración con módulo contable

### Fase 9: Testing y Optimización 📋
- [ ] Tests unitarios para entities
- [ ] Tests de integración para services
- [ ] Tests funcionales para controllers
- [ ] Optimización de consultas
- [ ] Índices de base de datos
- [ ] Cache de reportes frecuentes

## 🎯 Próximos Pasos Inmediatos

### Prioridad 1: Base de Datos
1. **Crear migración `ventas`**: Tabla principal normalizada
2. **Crear migración `cobranza`**: Tabla de mensualidades
3. **Crear migración `pagos`**: Registro de transacciones
4. **Seeders de catálogos**: Estados y tipos básicos

### Prioridad 2: Entities y Models
1. **VentaEntity**: Con métodos `getSaldoPendiente()`, `isVencido()`
2. **VentaModel**: Con relaciones a Proyecto, Cliente, Lote
3. **CobranzaEntity**: Con cálculos de intereses
4. **CobranzaModel**: Con scopes para vencidos

### Prioridad 3: Servicios Core
1. **VentasService**: Métodos `crearApartado()`, `confirmarVenta()`
2. **CobranzaService**: Método `generarPlanPagos()`
3. **InteresesService**: Método `aplicarMora()`
4. **CancelacionService**: Método `cancelarVencidos()`

### Comando de Inicio Sugerido
```bash
# Para comenzar la migración
php spark make:migration CreateVentasTable
php spark make:migration CreateCobranzaTable  
php spark make:migration CreatePagosTable
php spark make:entity Venta
php spark make:model VentaModel
```

## 📊 Métricas del Análisis

### Archivos Analizados
- **ventas.php**: Interface principal de ventas
- **ventas_agregar.php**: Formulario de nueva venta
- **documento.php**: Generación automática de contratos (320+ líneas)
- **cobranza.php**: Sistema de mensualidades y mora
- **simulador_capital.php**: Calculadora de reestructuración
- **funciones.php**: Lógica principal (10,000+ líneas)

### Funciones Críticas Identificadas
- **Función 34**: Apartado simple (línea ~2822)
- **Función 36**: Apartado con enganche (línea ~3116)
- **Función 37**: Venta a crédito (línea ~3597) 
- **Función 38**: Venta de contado (línea ~4053)
- **Sistema de mora**: Automático en login (líneas 32-164)
- **Cancelación automática**: Apartados vencidos (líneas 47-62)

### Complejidad del Sistema Legacy
- **94 campos** en tabla `tb_ventas`
- **25+ campos** en tabla `tb_cobranza`
- **50+ variables** en generación de contratos
- **4 tipos diferentes** de transacciones de venta
- **Sistema de intereses** automático y configurable
- **Desnormalización extrema** con campos "N" duplicados

---
**Análisis completado**: 2025-07-05  
**Sistema Legacy**: `./administracion/` (PHP7 procedural)  
**Sistema Nuevo**: `./app/` (CodeIgniter 4 + Shield)  
**Próximo módulo**: Implementación de entities y migrations  
**Prioridad**: Alta - Módulo core del negocio inmobiliario