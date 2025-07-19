# FLUJO DE VENTAS COMPLETO - SISTEMA LEGACY ANVAR INMOBILIARIA

## RESUMEN EJECUTIVO

Este documento detalla el flujo completo de ventas del sistema legacy de ANVAR Inmobiliaria, desde la selección de lotes hasta la liquidación total, incluyendo todos los procesos de cálculo, generación de documentos, y manejo de comisiones.

## ESTRUCTURA DE DATOS PRINCIPALES

### Tablas Clave del Sistema Legacy

- **tb_ventas**: Registro principal de ventas
- **tb_cobranza**: Tabla de amortización y mensualidades
- **tb_ingresos**: Registro de pagos y movimientos
- **tb_comisiones**: Cálculo y seguimiento de comisiones
- **tb_clientes**: Información de clientes
- **tb_lotes**: Inventario de lotes
- **tb_proyectos**: Información de proyectos inmobiliarios
- **tb_configuracion**: Configuración financiera por empresa

## FLUJO COMPLETO DE VENTAS

### 1. SELECCIÓN Y APARTADO DE LOTES

#### Proceso de Búsqueda de Lotes
```javascript
// Archivo: ventas_agregar.php + js/funciones/ventas.js
1. Selección de Empresa → Proyecto → Manzana → Lote
2. Filtros: Tipo, División, Área, Precio
3. Validación de disponibilidad (Estado = 0 "Disponible")
4. Cálculo automático de precios y anticipos
```

#### Datos del Lote
```php
// Información extraída de tb_lotes
- IdLote, Numero (clave), Area, Precio
- Proyecto, Manzana, Tipo, División
- Estado: 0=Disponible, 1=Apartado, 2=Vendido
- Construcción, Frente, Fondo, Laterales
```

### 2. CONFIGURACIÓN FINANCIERA

#### Esquemas de Financiamiento
```php
// Tabla: tb_financiamientos
- Tipos de esquemas predefinidos
- Configuración por empresa en tb_configuracion:
  - PorcentajeAnticipo / AnticipoFijo
  - MesesCreditoLote / MesesCreditoIntereses
  - InteresAnual / InteresMora
  - PorcentajeComision / ComisionFija
  - DiasCancelacion
```

#### Cálculos Automáticos
```javascript
// Lógica en ventas.js
1. Total del lote = Area × Precio por m²
2. Anticipo = Total × PorcentajeAnticipo (o fijo)
3. Mínimo anticipo = ApartadoMinimo de configuración
4. Monto a financiar = Total - Anticipo + Descuentos
```

### 3. PROCESO DE APARTADO

#### Datos Requeridos
```php
// Modal de apartado (ventas_agregar.php)
- Cliente principal + Cliente 2 (opcional)
- Vendedor asignado
- Esquema financiamiento
- Formas de pago: Transferencia, Efectivo, Tarjeta, Cheque
- Cuentas bancarias por forma de pago
- Referencia y ordenante
- Comprobantes de pago (upload)
```

#### Inserción en tb_ventas
```php
// Estado = 1 (Apartado)
INSERT INTO tb_ventas SET
  Folio = auto_increment,
  FolioInterno = opcional,
  Cliente = IdCliente,
  Cliente2 = IdCliente2 (opcional),
  Lote = IdLote,
  Vendedor = IdVendedor,
  Total = total_calculado,
  Anticipo = anticipo_calculado,
  AnticipoPagado = monto_recibido,
  Credito = 1/0,
  Estado = 1,
  DiasCancelacion = config,
  Fecha = CURRENT_DATE(),
  Hora = CURRENT_TIME()
```

### 4. GENERACIÓN DE TABLA DE AMORTIZACIÓN

#### Algoritmo de Cálculo (Función 38 en funciones.php)
```php
// Parámetros de entrada
$meses = meses_sin_interes;
$meses_intereses = meses_con_interes;
$interes_anual = porcentaje_anual;
$monto_financiar = total - anticipo;
$fecha_inicial = fecha_inicio_pagos;
$anualidades = []; // Pagos adicionales

// Cálculo de cuota mensual
if ($meses_intereses > 0) {
    $cuota_mensual = ($monto_financiar - $total_anualidades) / ($meses + $meses_intereses);
} else {
    $cuota_mensual = ($monto_financiar - $total_anualidades) / $meses;
}

// Redondeo opcional
if ($redondear == 1) {
    $cuota_mensual = round($cuota_mensual, 0);
} else {
    $cuota_mensual = round($cuota_mensual, 2);
}
```

#### Generación de Mensualidades Sin Interés
```php
$contador = 1;
for ($x = $mes; $x <= $meses; $x++) {
    // Cálculo de fecha de vencimiento
    $fecha_inicial = date("Y-m-d", strtotime($fecha_inicial . "+ 1 month"));
    
    // Insertar mensualidad
    INSERT INTO tb_cobranza SET
        Proyecto = $proyecto,
        Manzana = $manzana,
        Lote = $lote,
        Cliente = $cliente,
        Venta = $venta_id,
        FechaFinal = $fecha_inicial,
        Total = $cuota_mensual,
        TipoCredito = 2, // Venta
        Plazo = $contador,
        Interes = 0,
        Estatus = 1
    
    $contador++;
}
```

#### Generación de Mensualidades Con Interés
```php
$interes_acumulativo = 0;
if ($meses_intereses > 0) {
    for ($x = 1; $x <= $meses_intereses; $x++) {
        // Cálculo de interés mensual
        if ($contador_mi == 1) {
            $cuota_mensual_interes = ($total * ($interes_anual / 100)) / 12;
            $total = $total + ($total * ($interes_anual / 100));
        }
        
        $interes_acumulativo += $cuota_mensual_interes;
        
        // Insertar mensualidad con interés
        INSERT INTO tb_cobranza SET
            // ... campos básicos ...
            Total = $cuota_mensual + $cuota_mensual_interes,
            Interes = $cuota_mensual_interes,
            Plazo = $contador
        
        $contador++;
    }
}
```

#### Manejo de Anualidades (Pagos Adicionales)
```php
// Inserción de pagos adicionales en fechas específicas
foreach ($anualidades as $anualidad) {
    INSERT INTO tb_cobranza SET
        // ... campos básicos ...
        Total = $anualidad['monto'],
        FechaFinal = $anualidad['fecha'],
        Plazo = 'A', // Anualidad
        Interes = 0
}
```

### 5. REGISTRO DE INGRESOS

#### Generación de Recibos por Forma de Pago
```php
// Para cada forma de pago utilizada
if ($transferencia > 0) {
    INSERT INTO tb_ingresos SET
        // ... datos básicos ...
        Total = $transferencia,
        Transferencia = $transferencia,
        Cuenta = $cuenta_transferencia,
        TipoForma = $tipo_forma_transferencia,
        Folio = $folio_enganche + 1,
        Enganche = 1
}

// Similar para efectivo, tarjeta, cheque
```

#### Actualización de Saldos de Cuentas
```php
// Actualización automática de saldos
UPDATE tb_cuentas SET
    Saldo = Saldo + $monto_ingreso
WHERE IdCuenta = $cuenta_id
```

### 6. CÁLCULO DE COMISIONES

#### Comisión por Apartado
```php
// Configuración: PorcentajeComision o ComisionFija
if ($tipo_comision == 1) { // Porcentaje
    $comision_apartado = $anticipo_pagado * ($porcentaje_comision / 100);
} else { // Fija
    $comision_apartado = $apartado_comision;
}

INSERT INTO tb_comisiones SET
    Vendedor = $vendedor,
    Venta = $venta_id,
    Tipo = 1, // Apartado
    Monto = $comision_apartado,
    Pagado = 0
```

#### Comisión por Venta Completa
```php
// Se genera al liquidar completamente la venta
if ($tipo_comision == 1) { // Porcentaje
    $comision_venta = $total_venta * ($porcentaje_comision / 100);
} else { // Fija
    $comision_venta = $comision_fija;
}
```

### 7. GESTIÓN DE COBRANZA

#### Cálculo de Intereses por Mora
```php
// Proceso automático en cada login
$cobranzas_vencidas = SELECT * FROM tb_cobranza 
    WHERE DATEDIFF(FechaFinal, NOW()) < -5 
    AND Cobrado = FALSE 
    AND Interes = 0;

foreach ($cobranzas_vencidas as $cobranza) {
    $interes_mora = $configuracion['InteresMora'];
    
    INSERT INTO tb_cobranza_intereses SET
        Cobranza = $cobranza_id,
        Interes = $interes_mora,
        Fecha = CURRENT_DATE()
    
    UPDATE tb_cobranza SET
        Total = Total + $interes_mora,
        Interes = Interes + $interes_mora
    WHERE IdCobranza = $cobranza_id
}
```

#### Aplicación de Pagos
```php
// Cuando se recibe un pago
UPDATE tb_cobranza SET
    TotalPagado = TotalPagado + $monto_pago,
    Cobrado = IF(TotalPagado >= Total, 1, 0)
WHERE IdCobranza = $cobranza_id

// Registro del ingreso
INSERT INTO tb_ingresos SET
    // ... datos del pago ...
    Tipo = 3, // Mensualidad
    Cobranza = $cobranza_id
```

### 8. GENERACIÓN DE DOCUMENTOS

#### Tipos de Recibos
1. **Recibo de Apartado**: `ventas_recibo_apartado.php`
2. **Recibo de Enganche**: `ventas_recibo_enganche.php`
3. **Recibo de Mensualidad**: `recibo_pago_mensualidades.php`
4. **Estado de Cuenta**: `ventas_estado_cuenta.php`
5. **Tabla de Amortización**: `ver_amortizacion.php`

#### Plantillas de Documentos
```php
// Estructura común de recibos
- Logotipo del proyecto
- Datos de la empresa
- Información del cliente
- Detalles del lote
- Monto y forma de pago
- Firma del vendedor
- Folio único
```

### 9. ESTADOS DE VENTA

#### Estados Posibles
- **Estado 0**: Disponible (tb_lotes)
- **Estado 1**: Apartado (tb_ventas)
- **Estado 2**: Vendido/Liquidado (tb_ventas)
- **Estado 3**: Cancelado (tb_ventas)

#### Transiciones de Estado
```php
// Apartado → Vendido
UPDATE tb_ventas SET Estado = 2 WHERE IdVenta = $venta_id;
UPDATE tb_lotes SET Estado = 2 WHERE IdLote = $lote_id;

// Cancelación automática por vencimiento
if (DATEDIFF(NOW(), Fecha) > DiasCancelacion AND TotalPagado = 0) {
    UPDATE tb_ventas SET Estado = 3, Estatus = 2;
    UPDATE tb_lotes SET Estado = 0; // Disponible nuevamente
}
```

### 10. PROCESOS DE LIQUIDACIÓN

#### Verificación de Liquidación
```php
// Una venta se considera liquidada cuando:
1. AnticipoPagado >= Anticipo
2. SUM(tb_cobranza.TotalPagado) >= SUM(tb_cobranza.Total)
3. Todos los pagos adicionales están completos

// Actualización automática
UPDATE tb_ventas SET
    Cobrado = 1,
    Estado = 2,
    FechaLiquidacion = CURRENT_DATE()
WHERE IdVenta = $venta_id
```

#### Generación de Comisión Final
```php
// Al liquidar completamente
INSERT INTO tb_comisiones SET
    Vendedor = $vendedor,
    Venta = $venta_id,
    Tipo = 2, // Liquidación
    Monto = $comision_liquidacion,
    Pagado = 0
```

## PROCESOS AUTOMATIZADOS

### 1. Validación de Apartados Vencidos
```php
// Ejecutado en cada login (funciones.php líneas 47-59)
- Buscar apartados vencidos sin pago
- Liberar lotes automáticamente
- Marcar vendedor con error
- Registrar en historial
```

### 2. Cálculo de Intereses por Mora
```php
// Ejecutado diariamente
- Identificar cobranzas vencidas (+5 días)
- Aplicar interés por mora
- Actualizar totales de cobranza y venta
```

### 3. Actualización de Saldos
```php
// En cada transacción
- Actualizar saldos de cuentas bancarias
- Recalcular totales de ventas
- Actualizar estados de cobranza
```

## CONFIGURACIÓN FINANCIERA

### Parámetros por Empresa
```php
// tb_configuracion
- PorcentajeAnticipo: % del total para anticipo
- ApartadoMinimo: Monto mínimo para apartar
- MesesCreditoLote: Meses sin interés
- MesesCreditoIntereses: Meses con interés
- InteresAnual: % anual para cálculo de intereses
- InteresMora: Monto fijo por mora
- PorcentajeComision: % comisión o ComisionFija
- DiasCancelacion: Días para cancelar apartado
```

### Formas de Pago
```php
// tb_tipos_formas
- Transferencia (Forma = 1)
- Efectivo (Forma = 2)  
- Tarjeta (Forma = 3)
- Cheque (Forma = 4)
```

## REPORTES Y CONSULTAS

### Estados de Cuenta
- Tabla de amortización completa
- Pagos realizados vs. pendientes
- Intereses acumulados
- Próximos vencimientos

### Reportes de Ventas
- Ventas por período
- Ventas por vendedor
- Ventas por proyecto
- Estados de cobranza

### Reportes de Comisiones
- Comisiones por vendedor
- Comisiones por apartado/liquidación
- Comisiones pagadas vs. pendientes

## CONSIDERACIONES TÉCNICAS

### Campos Importantes
```php
// Campos con prefijo "N" = Nombre/Descripción
- NProyecto, NManzana, NLote, NCliente
- Utilizados para evitar JOINs en reportes

// Campos de auditoría
- Usuario, NUsuario, Fecha, Hora, IP
- Presentes en la mayoría de tablas
```

### Lógica de Negocio Crítica
1. **Cálculo de Cuotas**: Distribución equitativa del monto
2. **Manejo de Fechas**: Cálculo mensual progresivo
3. **Aplicación de Intereses**: Compound interest para meses con interés
4. **Redondeo**: Opcional para cuotas mensuales
5. **Anualidades**: Pagos adicionales en fechas específicas

## FLUJO DE DATOS RESUMIDO

```
1. Selección Lote → 2. Configuración Financiera → 3. Apartado
                                 ↓
4. Generación Tabla Amortización → 5. Registro Ingresos → 6. Cálculo Comisiones
                                 ↓
7. Cobranza Mensual → 8. Aplicación Pagos → 9. Verificación Liquidación
                                 ↓
10. Generación Documentos → 11. Actualización Estados → 12. Reportes
```

## RECOMENDACIONES PARA CI4

1. **Entities**: Crear entities para cada tabla principal
2. **Services**: Separar lógica de cálculo en services
3. **Helpers**: Funciones de cálculo y formateo
4. **Events**: Para procesos automatizados
5. **Validation**: Reglas de negocio específicas
6. **Libraries**: Para generación de documentos
7. **Commands**: Para procesos batch (intereses, vencimientos)

Este documento sirve como base para la implementación exacta del mismo flujo de negocio en el sistema CodeIgniter 4, asegurando continuidad operacional y manteniendo la lógica de negocio probada del sistema legacy.