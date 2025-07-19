Genera una base de datos relacional para ventas con financiamiento a plazos con y sin intereses con registro de ingresos por las ventas, registro de pago de comisiones a vendedores (egresos) y tabla de registro de mensualidades (abonos) o partidas financieras
Es para llenar una administración de control de pago de inmuebles de terrenos de varios tamaños

Editar
Voy a diseñar una base de datos relacional completa para el control de ventas de terrenos con financiamiento. Te explicaré cada componente paso a paso para que comprendas cómo funcionan todas las piezas juntas.


Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código 
He creado una base de datos completa para tu sistema de administración de ventas de terrenos con financiamiento. Te explicaré cómo está estructurada y cómo funciona cada componente:

Estructura General del Sistema
La base de datos está organizada en cuatro secciones principales que trabajan juntas para gestionar todo el proceso de venta y administración financiera:

1. Tablas de Catálogos Principales
Estas tablas almacenan la información básica que necesitas para operar:

Clientes: Guarda todos los datos de las personas que compran terrenos, incluyendo información fiscal como RFC y CURP
Vendedores: Registra a tu equipo de ventas con sus porcentajes de comisión personalizados
Desarrollos: Los fraccionamientos o desarrollos donde se encuentran los terrenos
Terrenos: Cada lote individual con su superficie, precio y ubicación específica
2. Tablas de Operaciones
Aquí se registran las transacciones principales:

Ventas: El registro maestro de cada operación de venta
Planes de Financiamiento: Define cómo se pagará cada venta (enganche, plazos, tasas de interés)
Mensualidades: El calendario detallado de pagos programados para cada venta financiada
3. Control Financiero
Para llevar un control preciso del dinero:

Pagos: Registra cada abono o pago que realizan los clientes
Ingresos: Control contable de las entradas de dinero
Comisiones: Calcula y registra las comisiones ganadas por cada vendedor
Egresos: Controla las salidas de dinero, principalmente pagos de comisiones
Características Especiales Implementadas
Financiamiento Flexible
El sistema maneja tanto ventas de contado como financiadas, y para las financiadas puede manejar:

Planes con intereses (especificando la tasa anual)
Planes sin intereses (tasa = 0)
Diferentes plazos de pago
Enganches variables
Automatización con Triggers
Incluí automatizaciones que te ahorrarán trabajo:

Cuando registras una venta, el terreno automáticamente cambia su estatus a "vendido"
Cuando registras un pago completo de una mensualidad, esta se marca como "pagada"
Procedimientos Almacenados
Creé dos procedimientos especiales que simplifican operaciones complejas:

generar_plan_pagos: Cuando vendas un terreno a plazos, este procedimiento genera automáticamente todas las mensualidades con sus fechas de vencimiento, calculando correctamente los intereses (si aplican) y los saldos
calcular_comision_venta: Calcula automáticamente la comisión del vendedor basándose en el precio de venta y su porcentaje asignado
Vistas para Reportes
Incluí tres vistas prediseñadas que te darán información valiosa:

vista_estado_cuenta_cliente: Muestra el estado actual de cada cliente, cuánto ha pagado y cuánto debe
vista_comisiones_vendedor: Resume las comisiones pendientes y pagadas de cada vendedor
vista_flujo_efectivo_mensual: Te muestra mes a mes cuánto dinero entra y sale
Cómo Usar el Sistema
Para registrar una venta nueva con financiamiento, el flujo sería:

Registrar al cliente (si es nuevo)
Crear la venta vinculando cliente, vendedor y terreno
Ejecutar el procedimiento generar_plan_pagos para crear automáticamente el plan de financiamiento y todas las mensualidades
El sistema calculará automáticamente las comisiones
Conforme lleguen los pagos, los registras en la tabla de pagos
Los ingresos y egresos se registran para el control contable
El diseño permite consultas complejas como "¿Quiénes tienen pagos vencidos?" o "¿Cuál es mi flujo de efectivo del último trimestre?" de manera eficiente gracias a los índices estratégicamente colocados.

¿Te gustaría que profundice en algún aspecto específico del sistema o que agregue alguna funcionalidad adicional?




Tenemos un plan que es cero enganche dónde las primeras dos mensualidades van directo al cliente y no se paga anticipo enganche porque para otros planes de financiamiento puede varias entonces tenemos que ajustar la respuesta anterior para que nos genere un bloque dónde creamos nuestras configuraciones financieras ahí se asustan los parámetros 
Tas, intereses , días de pago, comisión del vendedor si es por porcentaje o fijo si aplica para terrenos mayores o menores a ciertas superficie , si Aplica promoción cero enganche, días de pago de la mensualidad, etc. Todo eso de carga y entonces a partir de esos valores se genera una tabla de amortización y como quedan programados sus pagos

Editar
Entiendo perfectamente tu necesidad. Vamos a rediseñar el sistema para que sea mucho más flexible y pueda manejar diferentes tipos de planes de financiamiento, incluyendo ese plan especial de cero enganche donde las primeras mensualidades van al vendedor. Te voy a explicar cómo estructurar esto de manera profesional.


Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código ∙ Versión 2 
He rediseñado completamente el sistema para que sea mucho más flexible y potente. Te voy a explicar los cambios principales y cómo este nuevo diseño resuelve tus necesidades específicas, especialmente el plan de cero enganche donde las primeras mensualidades van al vendedor.

Arquitectura del Nuevo Sistema
El cambio fundamental está en cómo manejamos la configuración de los planes financieros. En lugar de tener valores fijos en el código, ahora todo es configurable a través de tablas maestras que te permiten crear diferentes tipos de planes según tus necesidades comerciales.

1. Configuración Maestra de Planes Financieros
La tabla tipos_plan_financiamiento es el corazón del sistema. Aquí defines todos los parámetros de cada plan que ofreces:

Configuración de enganche: Puedes especificar si requiere enganche, el porcentaje mínimo y sugerido, y si permite cero enganche
Configuración de plazos: Define los plazos mínimos, máximos y sugeridos para cada plan
Configuración de intereses: Especifica si aplica intereses, la tasa anual, y el tipo (fijo, variable o sin intereses)
Configuración especial: El campo mensualidades_para_vendedor es clave para tu plan cero enganche - aquí indicas cuántas mensualidades iniciales van directamente al vendedor
2. Sistema de Comisiones Flexible
La tabla configuracion_comisiones te permite definir cómo se calculan las comisiones para cada tipo de plan:

Puedes elegir entre porcentaje, monto fijo o escalas
Definir si la comisión se calcula sobre el precio de venta, el enganche o las mensualidades
Especificar cuándo se paga la comisión (al momento del enganche o por cobranza)
3. Tabla de Amortización Mejorada
La tabla tabla_amortizacion ahora incluye campos especiales:

beneficiario: Indica si el pago va a la empresa o al vendedor
concepto_especial: Para identificar claramente los pagos que corresponden al vendedor en planes cero enganche
Cómo Funciona el Plan Cero Enganche
Cuando configuras un plan con mensualidades_para_vendedor = 2, el sistema automáticamente:

No requiere enganche inicial del cliente
Las primeras 2 mensualidades se marcan con beneficiario = 'vendedor'
El concepto indica claramente que es una "Comisión vendedor plan cero enganche"
Los pagos de estas mensualidades se registran identificando al vendedor beneficiario
El procedimiento generar_tabla_amortizacion maneja esta lógica automáticamente. Cuando se ejecuta, verifica cuántas mensualidades deben ir al vendedor y las marca apropiadamente en la tabla de amortización.

Ejemplos de Configuración Incluidos
Ya incluí cuatro tipos de planes como ejemplo:

CONTADO: Pago total inmediato
TRAD2024: Plan tradicional con 20% de enganche a 24 meses
CERO2024: Tu plan especial sin enganche donde las primeras 2 mensualidades van al vendedor
MSI18: 18 meses sin intereses con 10% de enganche
Ventajas del Nuevo Diseño
Flexibilidad Total
Puedes crear nuevos planes sin modificar la estructura de la base de datos. Solo agregas un nuevo registro en tipos_plan_financiamiento con las características que necesites.

Control de Promociones
La tabla de promociones te permite crear ofertas temporales que se aplican automáticamente según las fechas de vigencia y las condiciones que establezcas.

Trazabilidad Completa
Cada pago identifica claramente su beneficiario, lo que facilita la conciliación contable y el pago de comisiones a vendedores.

Escalabilidad
El sistema puede manejar cualquier combinación de:

Plazos (desde contado hasta 60 meses o más)
Tasas de interés (fijas, variables o sin intereses)
Esquemas de comisiones (porcentajes, montos fijos o escalas)
Configuraciones especiales (como tu plan cero enganche)
Flujo de Trabajo
Para crear una venta con el plan cero enganche:

El vendedor registra la venta seleccionando el plan "CERO2024"
El sistema crea el registro en planes_financiamiento con enganche = 0 y mensualidades_vendedor = 2
Se ejecuta el procedimiento generar_tabla_amortizacion que crea toda la tabla de pagos, marcando las primeras 2 como beneficiario vendedor
Cuando el cliente paga esas primeras mensualidades, el sistema registra que el pago es para el vendedor
A partir de la mensualidad 3, los pagos van a la empresa
Este diseño te da la flexibilidad para crear cualquier tipo de plan financiero que tu negocio necesite, manteniendo un control preciso de todos los aspectos financieros y contables.

¿Te gustaría que profundice en algún aspecto específico o que agregue alguna funcionalidad adicional?




En esta tabla también se tiene que contemplar un valor a en configuración financiera si por ejemplo puede apartar con un mínimo de 5000 y tiene 10 dias para liquidar su enganchede 18000 si expira cae en penalización fija o porcentual por no cubrir el enganche total del terreno
Lo importante es la tabla de amortización, ajuste de configuración financiera

Editar
Excelente observación. Necesitamos agregar la funcionalidad de apartados con penalizaciones para manejar ese escenario comercial tan común en la venta de terrenos. Voy a explicarte cómo integrar esto al sistema de manera que todo fluya correctamente.


Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código ∙ Versión 3 

Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código ∙ Versión 4 

Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código ∙ Versión 5 

Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código ∙ Versión 6 

Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código ∙ Versión 7 
Perfecto, he completado las modificaciones para incluir el sistema de apartados con penalizaciones. Déjame explicarte cómo funciona esta nueva funcionalidad y por qué es fundamental para tu negocio de venta de terrenos.

El Sistema de Apartados: Una Solución Completa
El apartado es una práctica comercial muy común en bienes raíces que ahora está totalmente integrada en tu sistema. Funciona como una reservación del terreno donde el cliente da un anticipo para "apartar" la propiedad mientras completa el enganche total.

Configuración Flexible por Plan
En la tabla tipos_plan_financiamiento, agregué campos específicos para controlar los apartados:

Configuración básica del apartado:

permite_apartado: Define si ese plan acepta apartados
monto_minimo_apartado: El mínimo que debe dar el cliente (por ejemplo, $5,000)
dias_para_completar_enganche: Cuántos días tiene para completar el enganche total
Sistema de penalizaciones:

aplica_penalizacion: Si habrá penalización por no completar a tiempo
tipo_penalizacion: Puede ser 'fija' (monto específico) o 'porcentual' (% del apartado)
monto_penalizacion_fija: Si eligiste penalización fija, cuánto será
porcentaje_penalizacion: Si es porcentual, qué porcentaje del apartado se queda la empresa
destino_apartado_vencido: Qué pasa con el dinero - tres opciones:
devolucion_parcial: Se devuelve el apartado menos la penalización
perdida_total: El cliente pierde todo el apartado
abono_enganche: El apartado se convierte en parte del enganche (sin penalización)
La Nueva Tabla de Apartados
Creé una tabla específica apartados que registra:

Todos los datos del apartado (cliente, terreno, vendedor, monto)
La fecha límite para completar el enganche
El estatus del apartado (vigente, completado, vencido, cancelado, penalizado)
Si se aplicó penalización y cuánto fue
La referencia a la venta cuando el apartado se convierte exitosamente
Flujo de Trabajo del Apartado
El proceso completo funciona así:

Creación del apartado: Cuando un cliente quiere apartar un terreno, se ejecuta el procedimiento crear_apartado. Este procedimiento:
Verifica que el terreno esté disponible
Valida que el monto cumpla con el mínimo del plan
Calcula automáticamente la fecha límite
Cambia el estatus del terreno a "apartado"
Seguimiento automático: Incluí un evento programado que corre diariamente (procesar_vencimientos_diarios) para revisar apartados vencidos y aplicar las penalizaciones correspondientes según la configuración del plan.
Conversión a venta: Cuando el cliente completa el enganche, se ejecuta convertir_apartado_venta que:
Verifica que el total (apartado + complemento) cubra el enganche requerido
Crea la venta formal
Actualiza el apartado como "completado"
Cambia el terreno a estatus "vendido"
Integración con la Tabla de Amortización
En la tabla planes_financiamiento agregué campos para rastrear el historial del apartado:

tuvo_apartado: Indica si la venta vino de un apartado
monto_apartado_aplicado: Cuánto del apartado se aplicó al enganche
penalizacion_apartado: Si hubo penalización, cuánto fue
Esto es importante para la contabilidad y para entender el historial completo de cada venta.

Ejemplos Prácticos de Configuración
Ya incluí cuatro planes con diferentes configuraciones de apartado:

Plan TRAD2024 (Tradicional):

Permite apartar con mínimo $5,000
10 días para completar el enganche del 20%
Penalización del 10% del apartado si no completa
El resto se devuelve al cliente
Plan MSI18 (Meses sin intereses):

Permite apartar con mínimo $3,000
15 días para completar el enganche del 10%
Penalización fija de $1,000 si no completa
El apartado se convierte en parte del enganche (no se pierde)
Plan CERO2024 (Cero enganche):

No permite apartados (porque no requiere enganche)
Las primeras 2 mensualidades van al vendedor como acordamos
Ventajas de Este Diseño
Control total del riesgo: Puedes ajustar las penalizaciones según tu experiencia con cada tipo de cliente o plan
Automatización: El sistema procesa automáticamente los vencimientos sin intervención manual
Flexibilidad comercial: Cada plan puede tener sus propias reglas de apartado
Trazabilidad completa: Toda la historia queda registrada para auditorías o análisis
Este sistema te permite manejar situaciones comerciales reales como cuando un cliente quiere reservar un terreno pero necesita tiempo para juntar el enganche completo, mientras proteges tu negocio con penalizaciones justas si no cumple el compromiso.

¿Te gustaría que ajuste algún parámetro de la configuración o que agregue alguna funcionalidad adicional al sistema de apartados?




Nadamas aquí hay un problema que no tenemos permisos para crear vistas no podemos hacer trigger o procedimientos dentro de las tablas para ello utulizaremos  codeigniter que es quien se va a encargar de la programación por el momento enfoquemonos en optimizar la base de datos

Editar


Tienes toda la razón. Vamos a optimizar la base de datos enfocándonos solo en la estructura de tablas e índices, sin vistas, triggers ni procedimientos almacenados. Todo el procesamiento lo manejarás desde CodeIgniter.


Base de Datos - Sistema de Ventas de Terrenos con Financiamiento
Código ∙ Versión 8 
He optimizado completamente la base de datos para trabajar con CodeIgniter. Aquí están las mejoras principales que implementé:

Optimizaciones Estructurales
1. Motor de Base de Datos y Charset
Todas las tablas ahora usan:

ENGINE=InnoDB: Para garantizar transacciones ACID y mejor rendimiento
CHARSET=utf8mb4 con COLLATE=utf8mb4_unicode_ci: Para soportar todos los caracteres incluyendo emojis
2. Índices Estratégicos
Agregué índices en todos los campos que serán consultados frecuentemente:

Búsquedas por estatus (idx_*_estatus)
Búsquedas por fechas (idx_*_fecha)
Búsquedas por llaves foráneas
Índices compuestos para consultas complejas
3. Campos Calculados y Almacenados
Utilicé columnas generadas para cálculos automáticos:

sql
precio_m2 DECIMAL(10,2) GENERATED ALWAYS AS (precio_lista / superficie_m2) STORED
saldo_pendiente DECIMAL(10,2) GENERATED ALWAYS AS (monto_total - monto_pagado) STORED
dias_plazo INT GENERATED ALWAYS AS (DATEDIFF(fecha_limite_enganche, DATE(fecha_apartado))) STORED
4. Timestamps Automáticos
Todas las tablas incluyen:

sql
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
Mejoras para CodeIgniter
1. Tabla de Relación Pagos-Mensualidades
Creé la tabla pagos_mensualidades para manejar la aplicación de pagos parciales a múltiples mensualidades, algo muy común en sistemas de financiamiento.

2. Campos Adicionales para Control
Agregué campos que facilitarán el desarrollo en CodeIgniter:

usuario_registro: Para auditoría
cancelado con fecha_cancelacion y motivo_cancelacion
Campos de control como contrato_generado, facturado, etc.
3. Enums Mejorados
Expandí los enums para cubrir más casos de uso:

estatus_venta ahora incluye 'juridico'
tipo_egreso incluye más categorías
tipo_pago es más específico
4. Tabla de Bitácora
Agregué bitacora_cambios para que puedas implementar auditoría desde CodeIgniter:

php
// En tu modelo de CodeIgniter
$this->db->insert('bitacora_cambios', [
    'tabla_afectada' => 'ventas',
    'registro_id' => $venta_id,
    'tipo_cambio' => 'update',
    'campo_modificado' => 'estatus_venta',
    'valor_anterior' => 'activa',
    'valor_nuevo' => 'cancelada',
    'usuario' => $this->session->userdata('usuario')
]);
Optimizaciones de Rendimiento
1. Índices Compuestos
Por ejemplo, en tabla_amortizacion:

sql
INDEX idx_amort_vencidas (estatus, fecha_vencimiento)
Esto acelera consultas como "obtener todas las mensualidades vencidas".

2. Campos Desnormalizados para Rendimiento
En planes_financiamiento agregué:

numero_pagos_realizados: Evita contar cada vez
proximo_vencimiento: Acceso rápido sin joins
dias_atraso_maximo: Para reportes rápidos
3. Claves Únicas Compuestas
sql
UNIQUE KEY uk_plan_numero (plan_id, numero_pago)
Garantiza integridad y mejora búsquedas.

Sugerencias para CodeIgniter
1. Modelos Base
Crea un modelo base que maneje automáticamente:

php
class MY_Model extends CI_Model {
    protected function log_change($table, $id, $type, $data) {
        // Registrar en bitacora_cambios
    }
}
2. Uso de Transacciones
Para operaciones críticas como crear una venta:

php
$this->db->trans_start();
// Insertar venta
// Actualizar terreno
// Generar plan de financiamiento
// Crear tabla de amortización
$this->db->trans_complete();
3. Consultas Optimizadas
Aprovecha los índices creados:

php
// Obtener mensualidades vencidas
$this->db->where('estatus', 'vencida')
         ->where('fecha_vencimiento <=', date('Y-m-d'))
         ->order_by('fecha_vencimiento', 'ASC')
         ->get('tabla_amortizacion');
         


-- =================================================================
-- SISTEMA DE GESTIÓN DE VENTAS DE TERRENOS CON FINANCIAMIENTO
-- =================================================================
-- Optimizado para CodeIgniter sin vistas, triggers ni procedimientos
-- Todos los procesos se manejarán desde la aplicación

-- =================================================================
-- TABLAS DE CATÁLOGOS PRINCIPALES
-- =================================================================

-- Tabla de clientes
CREATE TABLE clientes (
    cliente_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50),
    rfc VARCHAR(13),
    curp VARCHAR(18),
    telefono VARCHAR(15),
    telefono_alternativo VARCHAR(15),
    email VARCHAR(100),
    direccion_calle VARCHAR(150),
    direccion_colonia VARCHAR(100),
    direccion_ciudad VARCHAR(50),
    direccion_estado VARCHAR(50),
    direccion_cp VARCHAR(10),
    fecha_nacimiento DATE,
    ocupacion VARCHAR(100),
    empresa_trabaja VARCHAR(100),
    ingreso_mensual DECIMAL(10,2),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cliente_rfc (rfc),
    INDEX idx_cliente_email (email),
    INDEX idx_cliente_activo (activo),
    INDEX idx_cliente_nombre (apellido_paterno, apellido_materno, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de vendedores
CREATE TABLE vendedores (
    vendedor_id INT PRIMARY KEY AUTO_INCREMENT,
    codigo_vendedor VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50),
    rfc VARCHAR(13),
    telefono VARCHAR(15),
    email VARCHAR(100),
    direccion TEXT,
    fecha_ingreso DATE NOT NULL,
    fecha_baja DATE,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vendedor_codigo (codigo_vendedor),
    INDEX idx_vendedor_activo (activo),
    INDEX idx_vendedor_rfc (rfc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de desarrollos
CREATE TABLE desarrollos (
    desarrollo_id INT PRIMARY KEY AUTO_INCREMENT,
    codigo_desarrollo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    direccion_completa TEXT,
    ciudad VARCHAR(50),
    estado VARCHAR(50),
    codigo_postal VARCHAR(10),
    total_lotes INT DEFAULT 0,
    lotes_disponibles INT DEFAULT 0,
    fecha_inicio DATE,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    amenidades TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_desarrollo_codigo (codigo_desarrollo),
    INDEX idx_desarrollo_activo (activo),
    INDEX idx_desarrollo_ciudad_estado (ciudad, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de terrenos
CREATE TABLE terrenos (
    terreno_id INT PRIMARY KEY AUTO_INCREMENT,
    desarrollo_id INT NOT NULL,
    codigo_terreno VARCHAR(30) UNIQUE NOT NULL,
    manzana VARCHAR(10),
    lote VARCHAR(10),
    calle VARCHAR(100),
    superficie_m2 DECIMAL(10,2) NOT NULL,
    superficie_construccion DECIMAL(10,2) DEFAULT 0,
    frente_metros DECIMAL(8,2),
    fondo_metros DECIMAL(8,2),
    forma VARCHAR(50), -- regular, irregular, esquina
    precio_lista DECIMAL(12,2) NOT NULL,
    precio_m2 DECIMAL(10,2) GENERATED ALWAYS AS (precio_lista / superficie_m2) STORED,
    uso_suelo VARCHAR(50), -- habitacional, comercial, mixto
    servicios_disponibles VARCHAR(200), -- agua,luz,drenaje
    ubicacion_especifica TEXT,
    observaciones TEXT,
    estatus ENUM('disponible', 'apartado', 'vendido', 'bloqueado') DEFAULT 'disponible',
    fecha_alta DATE DEFAULT (CURRENT_DATE),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (desarrollo_id) REFERENCES desarrollos(desarrollo_id),
    INDEX idx_terreno_desarrollo (desarrollo_id),
    INDEX idx_terreno_estatus (estatus),
    INDEX idx_terreno_manzana_lote (manzana, lote),
    INDEX idx_terreno_precio (precio_lista),
    INDEX idx_terreno_superficie (superficie_m2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- CONFIGURACIÓN DE PLANES FINANCIEROS
-- =================================================================

-- Tabla de configuración de planes de financiamiento
CREATE TABLE tipos_plan_financiamiento (
    tipo_plan_id INT PRIMARY KEY AUTO_INCREMENT,
    codigo_plan VARCHAR(20) UNIQUE NOT NULL,
    nombre_plan VARCHAR(100) NOT NULL,
    descripcion TEXT,
    -- Configuración de apartado
    permite_apartado BOOLEAN DEFAULT TRUE,
    monto_minimo_apartado DECIMAL(10,2) DEFAULT 5000.00,
    dias_para_completar_enganche INT DEFAULT 10,
    -- Configuración de penalizaciones
    aplica_penalizacion BOOLEAN DEFAULT TRUE,
    tipo_penalizacion ENUM('fija', 'porcentual', 'sin_penalizacion') DEFAULT 'porcentual',
    monto_penalizacion_fija DECIMAL(10,2) DEFAULT 0.00,
    porcentaje_penalizacion DECIMAL(5,2) DEFAULT 10.00,
    destino_apartado_vencido ENUM('devolucion_parcial', 'perdida_total', 'abono_enganche') DEFAULT 'devolucion_parcial',
    -- Configuración de enganche
    requiere_enganche BOOLEAN DEFAULT TRUE,
    porcentaje_enganche_min DECIMAL(5,2) DEFAULT 10.00,
    porcentaje_enganche_max DECIMAL(5,2) DEFAULT 50.00,
    porcentaje_enganche_sugerido DECIMAL(5,2) DEFAULT 20.00,
    permite_cero_enganche BOOLEAN DEFAULT FALSE,
    -- Configuración de plazos
    plazo_minimo_meses INT DEFAULT 1,
    plazo_maximo_meses INT DEFAULT 60,
    plazo_sugerido_meses INT DEFAULT 24,
    -- Configuración de intereses
    aplica_intereses BOOLEAN DEFAULT TRUE,
    tasa_interes_anual DECIMAL(5,2) DEFAULT 12.00,
    tipo_interes ENUM('fijo', 'variable', 'sin_intereses') DEFAULT 'fijo',
    -- Configuración de pagos
    dias_pago_permitidos VARCHAR(100), -- "1,5,10,15,20,25,30"
    dia_pago_default INT DEFAULT 5,
    dias_gracia INT DEFAULT 5,
    -- Configuración especial cero enganche
    mensualidades_para_vendedor INT DEFAULT 0,
    -- Restricciones
    superficie_minima_m2 DECIMAL(10,2),
    superficie_maxima_m2 DECIMAL(10,2),
    monto_minimo DECIMAL(12,2),
    monto_maximo DECIMAL(12,2),
    -- Control
    prioridad INT DEFAULT 100,
    activo BOOLEAN DEFAULT TRUE,
    fecha_vigencia_inicio DATE,
    fecha_vigencia_fin DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_plan_codigo (codigo_plan),
    INDEX idx_tipo_plan_activo (activo),
    INDEX idx_tipo_plan_vigencia (fecha_vigencia_inicio, fecha_vigencia_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de configuración de comisiones
CREATE TABLE configuracion_comisiones (
    config_comision_id INT PRIMARY KEY AUTO_INCREMENT,
    tipo_plan_id INT NOT NULL,
    nombre_config VARCHAR(100),
    -- Tipo de cálculo
    tipo_calculo ENUM('porcentaje', 'monto_fijo', 'escalonado') NOT NULL,
    porcentaje_sobre ENUM('precio_venta', 'enganche', 'mensualidades') DEFAULT 'precio_venta',
    porcentaje_comision DECIMAL(5,2),
    monto_fijo DECIMAL(10,2),
    -- Condiciones
    superficie_minima_m2 DECIMAL(10,2),
    superficie_maxima_m2 DECIMAL(10,2),
    monto_venta_minimo DECIMAL(12,2),
    monto_venta_maximo DECIMAL(12,2),
    -- Momento de pago
    paga_al_apartar BOOLEAN DEFAULT FALSE,
    paga_al_enganchar BOOLEAN DEFAULT TRUE,
    paga_por_cobranza BOOLEAN DEFAULT FALSE,
    porcentaje_al_apartar DECIMAL(5,2) DEFAULT 0,
    porcentaje_al_enganchar DECIMAL(5,2) DEFAULT 100,
    porcentaje_por_cobranza DECIMAL(5,2) DEFAULT 0,
    -- Control
    prioridad INT DEFAULT 100,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_plan_id) REFERENCES tipos_plan_financiamiento(tipo_plan_id),
    INDEX idx_config_comision_plan (tipo_plan_id),
    INDEX idx_config_comision_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLAS DE OPERACIONES
-- =================================================================

-- Tabla de apartados
CREATE TABLE apartados (
    apartado_id INT PRIMARY KEY AUTO_INCREMENT,
    folio_apartado VARCHAR(20) UNIQUE NOT NULL,
    terreno_id INT NOT NULL,
    cliente_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    tipo_plan_id INT NOT NULL,
    fecha_apartado DATETIME NOT NULL,
    monto_apartado DECIMAL(10,2) NOT NULL,
    monto_enganche_requerido DECIMAL(12,2) NOT NULL,
    fecha_limite_enganche DATE NOT NULL,
    dias_plazo INT GENERATED ALWAYS AS (DATEDIFF(fecha_limite_enganche, DATE(fecha_apartado))) STORED,
    -- Formas de pago del apartado
    forma_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta', 'deposito') NOT NULL,
    referencia_pago VARCHAR(50),
    comprobante_url VARCHAR(255),
    -- Control de estatus
    estatus_apartado ENUM('vigente', 'completado', 'vencido', 'cancelado', 'penalizado') DEFAULT 'vigente',
    fecha_cambio_estatus DATETIME,
    motivo_cancelacion TEXT,
    -- Penalizaciones
    fecha_vencimiento DATETIME,
    aplico_penalizacion BOOLEAN DEFAULT FALSE,
    tipo_penalizacion_aplicada ENUM('fija', 'porcentual'),
    monto_penalizacion DECIMAL(10,2) DEFAULT 0.00,
    monto_devuelto DECIMAL(10,2) DEFAULT 0.00,
    fecha_devolucion DATE,
    -- Referencias
    venta_id INT,
    usuario_registro VARCHAR(50),
    observaciones TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (terreno_id) REFERENCES terrenos(terreno_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(cliente_id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(vendedor_id),
    FOREIGN KEY (tipo_plan_id) REFERENCES tipos_plan_financiamiento(tipo_plan_id),
    INDEX idx_apartado_fecha_limite (fecha_limite_enganche),
    INDEX idx_apartado_estatus (estatus_apartado),
    INDEX idx_apartado_terreno (terreno_id),
    INDEX idx_apartado_cliente (cliente_id),
    INDEX idx_apartado_vendedor (vendedor_id),
    INDEX idx_apartado_fecha (fecha_apartado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de ventas
CREATE TABLE ventas (
    venta_id INT PRIMARY KEY AUTO_INCREMENT,
    folio_venta VARCHAR(20) UNIQUE NOT NULL,
    terreno_id INT NOT NULL,
    cliente_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    tipo_plan_id INT NOT NULL,
    apartado_id INT,
    -- Datos de la venta
    fecha_venta DATE NOT NULL,
    precio_lista DECIMAL(12,2) NOT NULL,
    descuento_aplicado DECIMAL(10,2) DEFAULT 0.00,
    motivo_descuento VARCHAR(200),
    precio_venta_final DECIMAL(12,2) NOT NULL,
    -- Tipo y estatus
    tipo_venta ENUM('contado', 'financiado') NOT NULL,
    estatus_venta ENUM('activa', 'cancelada', 'liquidada', 'juridico') DEFAULT 'activa',
    fecha_liquidacion DATE,
    fecha_cancelacion DATE,
    motivo_cancelacion TEXT,
    -- Control
    contrato_generado BOOLEAN DEFAULT FALSE,
    fecha_contrato DATE,
    numero_contrato VARCHAR(50),
    usuario_registro VARCHAR(50),
    observaciones TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (terreno_id) REFERENCES terrenos(terreno_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(cliente_id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(vendedor_id),
    FOREIGN KEY (tipo_plan_id) REFERENCES tipos_plan_financiamiento(tipo_plan_id),
    FOREIGN KEY (apartado_id) REFERENCES apartados(apartado_id),
    INDEX idx_venta_fecha (fecha_venta),
    INDEX idx_venta_estatus (estatus_venta),
    INDEX idx_venta_cliente (cliente_id),
    INDEX idx_venta_vendedor (vendedor_id),
    INDEX idx_venta_terreno (terreno_id),
    INDEX idx_venta_tipo (tipo_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de planes de financiamiento
CREATE TABLE planes_financiamiento (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT NOT NULL,
    -- Información del apartado aplicado
    tuvo_apartado BOOLEAN DEFAULT FALSE,
    monto_apartado_aplicado DECIMAL(10,2) DEFAULT 0.00,
    penalizacion_apartado DECIMAL(10,2) DEFAULT 0.00,
    -- Configuración del enganche
    monto_enganche DECIMAL(12,2) DEFAULT 0.00,
    porcentaje_enganche DECIMAL(5,2),
    fecha_pago_enganche DATE,
    enganche_pagado BOOLEAN DEFAULT FALSE,
    -- Configuración del financiamiento
    monto_financiar DECIMAL(12,2) NOT NULL,
    plazo_meses INT NOT NULL,
    tasa_interes_anual DECIMAL(5,2) DEFAULT 0.00,
    tipo_calculo_interes ENUM('global', 'saldos_insolutos') DEFAULT 'saldos_insolutos',
    -- Montos calculados
    monto_mensualidad DECIMAL(10,2) NOT NULL,
    total_intereses DECIMAL(12,2) DEFAULT 0.00,
    total_a_pagar DECIMAL(12,2) NOT NULL,
    -- Configuración de pagos
    dia_pago_mensual INT NOT NULL CHECK (dia_pago_mensual BETWEEN 1 AND 28),
    fecha_primer_pago DATE NOT NULL,
    fecha_ultimo_pago DATE NOT NULL,
    dias_gracia INT DEFAULT 5,
    -- Para planes especiales
    mensualidades_para_vendedor INT DEFAULT 0,
    -- Control de pagos
    numero_pagos_realizados INT DEFAULT 0,
    ultimo_pago_fecha DATE,
    proximo_vencimiento DATE,
    dias_atraso_maximo INT DEFAULT 0,
    -- Estatus
    estatus ENUM('vigente', 'liquidado', 'vencido', 'cancelado', 'juridico') DEFAULT 'vigente',
    fecha_liquidacion DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(venta_id),
    INDEX idx_plan_venta (venta_id),
    INDEX idx_plan_estatus (estatus),
    INDEX idx_plan_proximo_venc (proximo_vencimiento),
    INDEX idx_plan_dia_pago (dia_pago_mensual)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de amortización
CREATE TABLE tabla_amortizacion (
    amortizacion_id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    numero_pago INT NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    -- Desglose del pago
    saldo_inicial DECIMAL(12,2) NOT NULL,
    capital DECIMAL(10,2) NOT NULL,
    interes DECIMAL(10,2) DEFAULT 0.00,
    monto_total DECIMAL(10,2) NOT NULL,
    saldo_final DECIMAL(12,2) NOT NULL,
    -- Para planes especiales
    beneficiario ENUM('empresa', 'vendedor') DEFAULT 'empresa',
    concepto_especial VARCHAR(100),
    -- Control de pagos
    estatus ENUM('pendiente', 'pagada', 'parcial', 'vencida', 'cancelada') DEFAULT 'pendiente',
    monto_pagado DECIMAL(10,2) DEFAULT 0.00,
    saldo_pendiente DECIMAL(10,2) GENERATED ALWAYS AS (monto_total - monto_pagado) STORED,
    fecha_ultimo_pago DATE,
    numero_pagos_aplicados INT DEFAULT 0,
    -- Atrasos
    dias_atraso INT DEFAULT 0,
    fecha_inicio_atraso DATE,
    interes_moratorio DECIMAL(10,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES planes_financiamiento(plan_id),
    UNIQUE KEY uk_plan_numero (plan_id, numero_pago),
    INDEX idx_amort_fecha_venc (fecha_vencimiento),
    INDEX idx_amort_estatus (estatus),
    INDEX idx_amort_beneficiario (beneficiario),
    INDEX idx_amort_plan (plan_id),
    INDEX idx_amort_vencidas (estatus, fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLAS DE CONTROL FINANCIERO
-- =================================================================

-- Tabla de pagos recibidos
CREATE TABLE pagos (
    pago_id INT PRIMARY KEY AUTO_INCREMENT,
    folio_pago VARCHAR(20) UNIQUE NOT NULL,
    venta_id INT NOT NULL,
    -- Aplicación del pago
    tipo_pago ENUM('apartado', 'enganche', 'mensualidad', 'abono_capital', 'interes_moratorio', 'pago_anticipado', 'liquidacion') NOT NULL,
    concepto_pago VARCHAR(200) NOT NULL,
    -- Montos
    monto_pago DECIMAL(10,2) NOT NULL,
    monto_aplicado_capital DECIMAL(10,2) DEFAULT 0.00,
    monto_aplicado_interes DECIMAL(10,2) DEFAULT 0.00,
    monto_aplicado_moratorio DECIMAL(10,2) DEFAULT 0.00,
    -- Forma de pago
    forma_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta', 'deposito') NOT NULL,
    banco_origen VARCHAR(50),
    referencia_pago VARCHAR(50),
    fecha_pago DATE NOT NULL,
    hora_pago TIME,
    -- Beneficiario del pago (para planes especiales)
    beneficiario_pago ENUM('empresa', 'vendedor') DEFAULT 'empresa',
    vendedor_beneficiario_id INT,
    -- Control
    facturado BOOLEAN DEFAULT FALSE,
    numero_factura VARCHAR(50),
    cancelado BOOLEAN DEFAULT FALSE,
    fecha_cancelacion DATETIME,
    motivo_cancelacion TEXT,
    usuario_registro VARCHAR(50),
    observaciones TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(venta_id),
    FOREIGN KEY (vendedor_beneficiario_id) REFERENCES vendedores(vendedor_id),
    INDEX idx_pago_fecha (fecha_pago),
    INDEX idx_pago_venta (venta_id),
    INDEX idx_pago_tipo (tipo_pago),
    INDEX idx_pago_beneficiario (beneficiario_pago),
    INDEX idx_pago_cancelado (cancelado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de aplicación de pagos a mensualidades
CREATE TABLE pagos_mensualidades (
    pago_mensualidad_id INT PRIMARY KEY AUTO_INCREMENT,
    pago_id INT NOT NULL,
    amortizacion_id INT NOT NULL,
    monto_aplicado DECIMAL(10,2) NOT NULL,
    tipo_aplicacion ENUM('capital', 'interes', 'moratorio') NOT NULL,
    fecha_aplicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pago_id) REFERENCES pagos(pago_id),
    FOREIGN KEY (amortizacion_id) REFERENCES tabla_amortizacion(amortizacion_id),
    INDEX idx_pago_mens_pago (pago_id),
    INDEX idx_pago_mens_amort (amortizacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de ingresos (contabilidad)
CREATE TABLE ingresos (
    ingreso_id INT PRIMARY KEY AUTO_INCREMENT,
    pago_id INT NOT NULL,
    fecha_ingreso DATE NOT NULL,
    monto_ingreso DECIMAL(12,2) NOT NULL,
    tipo_ingreso ENUM('venta_terreno', 'interes', 'moratorio', 'penalizacion', 'otro') DEFAULT 'venta_terreno',
    concepto_contable VARCHAR(200),
    cuenta_bancaria VARCHAR(50),
    numero_deposito VARCHAR(30),
    comprobante_url VARCHAR(255),
    -- Para reportes
    periodo_mes INT NOT NULL,
    periodo_año INT NOT NULL,
    registrado_contabilidad BOOLEAN DEFAULT FALSE,
    fecha_registro_contable DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pago_id) REFERENCES pagos(pago_id),
    INDEX idx_ingreso_periodo (periodo_año, periodo_mes),
    INDEX idx_ingreso_fecha (fecha_ingreso),
    INDEX idx_ingreso_tipo (tipo_ingreso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de comisiones
CREATE TABLE comisiones (
    comision_id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    config_comision_id INT NOT NULL,
    -- Cálculo
    base_calculo DECIMAL(12,2) NOT NULL,
    tipo_calculo_aplicado ENUM('porcentaje', 'monto_fijo', 'escalonado') NOT NULL,
    porcentaje_aplicado DECIMAL(5,2),
    monto_comision DECIMAL(10,2) NOT NULL,
    -- Tipo y origen
    tipo_comision ENUM('apartado', 'venta', 'enganche', 'cobranza', 'bono') DEFAULT 'venta',
    numero_pago_origen INT, -- Si es por cobranza, qué pago la generó
    pago_id INT, -- Referencia al pago que generó la comisión
    -- Control de pago
    estatus ENUM('pendiente', 'aprobada', 'pagada', 'cancelada') DEFAULT 'pendiente',
    fecha_generacion DATE NOT NULL,
    fecha_aprobacion DATE,
    usuario_aprueba VARCHAR(50),
    fecha_programada_pago DATE,
    fecha_pago DATE,
    -- Referencia al egreso
    egreso_id INT,
    observaciones TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(venta_id),
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(vendedor_id),
    FOREIGN KEY (config_comision_id) REFERENCES configuracion_comisiones(config_comision_id),
    FOREIGN KEY (pago_id) REFERENCES pagos(pago_id),
    INDEX idx_comision_vendedor (vendedor_id),
    INDEX idx_comision_estatus (estatus),
    INDEX idx_comision_fecha_gen (fecha_generacion),
    INDEX idx_comision_tipo (tipo_comision)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de egresos
CREATE TABLE egresos (
    egreso_id INT PRIMARY KEY AUTO_INCREMENT,
    folio_egreso VARCHAR(20) UNIQUE NOT NULL,
    tipo_egreso ENUM('comision', 'devolucion_apartado', 'gasto_operativo', 'pago_proveedor', 'nomina', 'otro') NOT NULL,
    -- Referencias según el tipo
    comision_id INT,
    apartado_id INT,
    -- Datos del egreso
    beneficiario VARCHAR(200) NOT NULL,
    rfc_beneficiario VARCHAR(13),
    concepto VARCHAR(200) NOT NULL,
    fecha_egreso DATE NOT NULL,
    monto_egreso DECIMAL(12,2) NOT NULL,
    -- Forma de pago
    forma_pago ENUM('efectivo', 'transferencia', 'cheque') NOT NULL,
    banco_destino VARCHAR(50),
    numero_cuenta_destino VARCHAR(20),
    referencia_pago VARCHAR(50),
    -- Control
    comprobante_url VARCHAR(255),
    factura_recibida BOOLEAN DEFAULT FALSE,
    numero_factura VARCHAR(50),
    -- Para reportes
    periodo_mes INT NOT NULL,
    periodo_año INT NOT NULL,
    categoria_gasto VARCHAR(50),
    centro_costo VARCHAR(50),
    -- Autorización
    requiere_autorizacion BOOLEAN DEFAULT TRUE,
    autorizado BOOLEAN DEFAULT FALSE,
    usuario_autoriza VARCHAR(50),
    fecha_autorizacion DATETIME,
    usuario_registro VARCHAR(50),
    observaciones TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (comision_id) REFERENCES comisiones(comision_id),
    FOREIGN KEY (apartado_id) REFERENCES apartados(apartado_id),
    INDEX idx_egreso_periodo (periodo_año, periodo_mes),
    INDEX idx_egreso_tipo (tipo_egreso),
    INDEX idx_egreso_fecha (fecha_egreso),
    INDEX idx_egreso_autorizado (autorizado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLAS DE CONTROL Y AUDITORÍA
-- =================================================================

-- Tabla de bitácora de cambios importantes
CREATE TABLE bitacora_cambios (
    bitacora_id INT PRIMARY KEY AUTO_INCREMENT,
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    tipo_cambio ENUM('insert', 'update', 'delete') NOT NULL,
    campo_modificado VARCHAR(50),
    valor_anterior TEXT,
    valor_nuevo TEXT,
    motivo_cambio TEXT,
    usuario VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bitacora_tabla (tabla_afectada, registro_id),
    INDEX idx_bitacora_fecha (fecha_cambio),
    INDEX idx_bitacora_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- DATOS DE CONFIGURACIÓN INICIAL
-- =================================================================

-- Insertar planes de financiamiento de ejemplo
INSERT INTO tipos_plan_financiamiento (
    codigo_plan, nombre_plan, descripcion,
    permite_apartado, monto_minimo_apartado, dias_para_completar_enganche,
    aplica_penalizacion, tipo_penalizacion, porcentaje_penalizacion,
    destino_apartado_vencido, requiere_enganche, porcentaje_enganche_min,
    porcentaje_enganche_sugerido, permite_cero_enganche, plazo_minimo_meses,
    plazo_maximo_meses, plazo_sugerido_meses, aplica_intereses,
    tasa_interes_anual, tipo_interes, dias_pago_permitidos,
    dia_pago_default, mensualidades_para_vendedor
) VALUES 
('CONTADO', 'Pago de Contado', 'Pago total en una sola exhibición',
 FALSE, 0, 0, FALSE, 'sin_penalizacion', 0, 'devolucion_parcial',
 FALSE, 100, 100, FALSE, 0, 0, 0, FALSE, 0, 'sin_intereses', '1,5,10,15,20,25,30', 1, 0),

('TRAD2024', 'Plan Tradicional 24 Meses', 'Financiamiento a 24 meses con 20% de enganche',
 TRUE, 5000, 10, TRUE, 'porcentual', 10, 'devolucion_parcial',
 TRUE, 20, 20, FALSE, 12, 36, 24, TRUE, 12, 'fijo', '5,10,15,20,25', 5, 0),

('CERO2024', 'Plan Cero Enganche', 'Sin enganche, primeras 2 mensualidades para vendedor',
 FALSE, 0, 0, FALSE, 'sin_penalizacion', 0, 'devolucion_parcial',
 FALSE, 0, 0, TRUE, 24, 48, 36, TRUE, 15, 'fijo', '5,10,15,20,25', 10, 2),

('MSI18', 'Meses Sin Intereses 18', '18 meses sin intereses con 10% enganche',
 TRUE, 3000, 15, TRUE, 'fija', 1000, 'abono_enganche',
 TRUE, 10, 10, FALSE, 18, 18, 18, FALSE, 0, 'sin_intereses', '5,10,15,20,25', 5, 0);

-- Insertar configuraciones de comisiones
INSERT INTO configuracion_comisiones (
    tipo_plan_id, nombre_config, tipo_calculo, porcentaje_sobre,
    porcentaje_comision, paga_al_enganchar, paga_por_cobranza,
    porcentaje_al_enganchar
) VALUES
(1, 'Comisión Contado Estándar', 'porcentaje', 'precio_venta', 5.0, TRUE, FALSE, 100),
(2, 'Comisión Plan Tradicional', 'porcentaje', 'precio_venta', 4.0, TRUE, FALSE, 50),
(3, 'Comisión Plan Cero Enganche', 'porcentaje', 'precio_venta', 3.5, FALSE, TRUE, 0),
(4, 'Comisión MSI', 'porcentaje', 'precio_venta', 4.5, TRUE, FALSE, 70);