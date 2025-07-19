🔍 ANÁLISIS COMPLETO DEL SISTEMA LEGACY DE PAGOS Y COBRANZA

  📊 TABLAS PRINCIPALES DEL SISTEMA

  1. tb_ingresos - Registro de todos los pagos/ingresos
    - Campos: IdIngreso, Folio, Total, Efectivo, Transferencia, Cheque, Tarjeta
    - Estados: Estatus (1=Activo, 2=Cancelado)
    - Relaciones: Cliente, Lote, Venta, Proyecto, Empresa
  2. tb_cobranza - Plan de pagos/cuentas por cobrar
    - Campos: IdCobranza, Total, TotalPagado, FechaFinal, Interes, Cobrado
    - Tipos: TipoCredito (1=Enganche, 2=Mensualidad)
    - Estados: Cobrado (TRUE/FALSE)
  3. tb_cobranza_pagos - Relación entre pagos y cobranza
    - Campos: Cobranza, Ingreso, Total, Estado
    - Estados: Estado (0=Activo, 1=Cancelado)
  4. tb_cobranza_intereses - Registro de intereses moratorios
    - Campos: Cobranza, Interes, TotalInteres, Fecha
  5. tb_configuracion - Configuración por empresa
    - Campo: InteresMora (monto fijo de interés moratorio)

  💰 FLUJO DE REGISTRO DE PAGOS

  1. Agregar Ingreso (ingresos_agregar.php):
    - Selección de: Empresa → Proyecto → Cuenta → Cliente → Lote
    - Formas de pago: Efectivo, Transferencia, Cheque, Tarjeta
    - Tipos de ingreso desde tb_lista_ingresos
    - Genera folio único por ingreso
  2. Proceso de Pago:
  -- 1. Insertar en tb_ingresos
  INSERT INTO tb_ingresos (Folio, Total, Efectivo, Transferencia, ...)

  -- 2. Registrar en tb_cobranza_pagos
  INSERT INTO tb_cobranza_pagos (Cobranza, Ingreso, Total, Estado=0)

  -- 3. Actualizar saldo en tb_cobranza
  UPDATE tb_cobranza SET TotalPagado = TotalPagado + [monto]

  🔄 GESTIÓN DE INTERESES MORATORIOS

  1. Cálculo Automático (en cobranza.php):
    - Se ejecuta al cargar la página de cobranza
    - Busca pagos vencidos con más de 5 días de atraso
    - Aplica interés fijo desde tb_configuracion.InteresMora
    - Actualiza montos en tb_cobranza y tb_ventas
  2. Proceso:
  // Si FechaFinal < NOW() - 5 días Y no tiene interés aplicado
  $totalInteres = $rowEm["InteresMora"]; // Monto fijo, no porcentaje

  // Registrar interés
  INSERT INTO tb_cobranza_intereses

  // Actualizar cobranza
  UPDATE tb_cobranza SET Total = Total + $totalInteres

  ❌ CANCELACIÓN DE PAGOS

  1. Función 356 - Editar/Reemplazar Pago:
  -- Cancelar pago original
  UPDATE tb_ingresos SET Estatus=2 WHERE IdIngreso=$id
  UPDATE tb_cobranza_pagos SET Estado=1 WHERE Ingreso=$id
  -- Crear nuevo pago con datos actualizados
  2. Función 362 - Cancelar Venta Completa:
  UPDATE tb_ventas SET Estatus=2 WHERE IdVenta=$id
  UPDATE tb_ingresos SET Estatus=2 WHERE Venta=$id
  3. Estados de Cancelación:
    - Estatus=2 en tb_ingresos = Pago cancelado
    - Estado=1 en tb_cobranza_pagos = Relación cancelada
    - NO se eliminan registros, solo se marcan como cancelados

  📑 TIPOS DE COMPROBANTES

  1. Recibos de Pago:
    - recibo_pago_mensualidad.php - Pago de mensualidad
    - recibo_pago_mensualidades.php - Múltiples mensualidades
    - recibo_abono_mensualidad.php - Abono parcial
    - recibo_ingreso.php - Ingreso general
  2. Recibos de Venta:
    - ventas_recibo_apartado.php - Apartado
    - ventas_recibo_enganche.php - Enganche
    - ventas_recibo_venta.php - Venta
    - ventas_recibo_capital.php - Pago a capital
  3. Información en Recibos:
    - Folio único del pago
    - Datos del cliente y lote
    - Formas de pago utilizadas
    - Concepto detallado
    - Referencias bancarias

  💼 COMISIONES

  1. Archivos de Comisiones:
    - comisiones.php - Gestión general
    - vendedor_comisiones.php - Por vendedor
    - super_vendedor_comisiones.php - Supervisores
    - reporte_comisiones.php - Reportes
  2. Cálculo de Comisiones:
    - Se calculan sobre pagos efectivos recibidos
    - Diferentes porcentajes por tipo de venta
    - Registro en tb_comisiones_pagos

  🔐 VALIDACIONES Y SEGURIDAD

  1. Permisos por Rol:
    - $_SESSION["ANV_U_LOG_P_FLUJOS"] - Gestión de ingresos
    - $_SESSION["ANV_U_LOG_P_COBRANZA"] - Gestión de cobranza
    - Filtros por empresa según permisos del usuario
  2. Auditoría:
    - Registro en tb_historial_movimientos
    - Usuario, fecha, hora, IP de cada operación
    - Tipos: Visualización, Agregar, Modificar, Cancelar

  📈 FLUJO DE COBRANZA COMPLETO

  1. Venta → Plan de Pagos:
    - Al confirmar venta se genera plan en tb_cobranza
    - Enganche + Mensualidades según plazo
  2. Registro de Pagos:
    - Cliente realiza pago → Se registra en tb_ingresos
    - Se aplica a cobranza específica → tb_cobranza_pagos
    - Se actualiza saldo → tb_cobranza.TotalPagado
  3. Control de Morosidad:
    - Sistema automático revisa vencimientos diarios
    - Aplica intereses después de días de gracia
    - Actualiza estados y montos totales
  4. Cancelaciones:
    - Soft delete con Estatus=2
    - Mantiene historial completo
    - Ajusta saldos automáticamente

  Este sistema maneja de forma integral todo el ciclo de cobranza, desde la generación del plan de pagos hasta el control de
  morosidad y cancelaciones, manteniendo trazabilidad completa de todas las operaciones.