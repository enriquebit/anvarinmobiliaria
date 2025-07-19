# DATABASE.md - Estructura de Base de Datos NuevoAnvar

Documentación completa de la estructura de base de datos del sistema inmobiliario basada en la implementación real.

## 🎯 ESTRATEGIA DE BASE DE DATOS

### Filosofía de Desarrollo
- **Incremental**: Agregamos tablas según módulos implementados
- **Modular**: Cada módulo aporta sus tablas específicas  
- **Relacional**: Mantenemos integridad referencial con foreign keys
- **Escalable**: Diseño que permite crecimiento futuro

### Estado Actual
✅ **BASE DE DATOS ESTABLE**: 19 tablas implementadas y funcionando en producción. Schema completo disponible en `app/Database/nuevoanvar.sql`.

---

## 🗄️ TABLAS IMPLEMENTADAS Y FUNCIONANDO

### 📊 Resumen de Implementación
```
✅ Autenticación (Shield): 6 tablas
✅ Clientes Completo: 6 tablas  
✅ Empresas: 1 tabla
✅ Staff: 2 tablas
✅ Proyectos: 2 tablas (MÓDULO COMPLETO)
✅ Catálogos: 2 tablas

Total implementado: 19 tablas funcionando
```

### 🔐 Módulo de Autenticación (Shield)
```sql
-- Sistema CodeIgniter Shield (6 tablas implementadas)
users                     # Usuarios del sistema (80 registros)
auth_identities          # Credenciales email/password (73 registros)
auth_groups_users        # Asignación de roles (78 registros)
auth_remember_tokens     # Tokens "recordarme"
auth_permissions_users   # Permisos específicos por usuario
auth_logins              # Historial de logins (107 registros)
auth_token_logins        # Logins por token
```

**Roles implementados:**
- `superadmin`: Acceso total
- `admin`: Gestión operativa  
- `cliente`: Acceso limitado
- `vendedor`: Staff de ventas

### 👥 Módulo de Clientes (Sistema Completo)
```sql
-- Cliente principal (51 registros)
clientes (
    id, user_id, nombres, apellido_paterno, apellido_materno,
    genero, razon_social, identificacion, numero_identificacion,
    fecha_nacimiento, lugar_nacimiento, nacionalidad, profesion,
    rfc, curp, email, estado_civil_id, estado_civil, leyenda_civil,
    tiempo_radicando, tipo_residencia, residente, contacto,
    empresa_id, clave, origen_informacion_id, otro_origen,
    fuente_informacion, telefono, etapa_proceso,
    fecha_primer_contacto, fecha_ultima_actividad,
    asesor_asignado, notas_internas, created_at, updated_at
);

-- Direcciones múltiples por cliente (22 registros)
direcciones_clientes (
    id, cliente_id, domicilio, numero, colonia, codigo_postal,
    ciudad, estado, tiempo_radicando, tipo_residencia,
    residente, tipo, activo, created_at, updated_at
);

-- Referencias familiares/comerciales (31 registros)
referencias_clientes (
    id, cliente_id, numero, nombre_completo, parentesco,
    telefono, tipo, genero, activo, created_at, updated_at
);

-- Información laboral (19 registros)
informacion_laboral_clientes (
    id, cliente_id, nombre_empresa, puesto_cargo, antiguedad,
    telefono_trabajo, direccion_trabajo, activo, created_at, updated_at
);

-- Información del cónyuge (12 registros)
informacion_conyuge_clientes (
    id, cliente_id, nombre_completo, profesion, email,
    telefono, activo, created_at, updated_at
);

-- Documentos clientes (0 registros - listo para usar)
documentos_clientes (
    id, cliente_id, tipo_documento, nombre_archivo, ruta_archivo,
    tamano_archivo, extension, mime_type, estado, observaciones,
    aprobado_por, fecha_aprobacion, activo, created_at, updated_at
);
```

**Tipos de documento válidos:**
- `carta_domicilio`, `comprobante_domicilio`, `curp`
- `hoja_requerimientos`, `identificacion_oficial`, `ofac`, `rfc`

**Etapas del proceso de cliente:**
- `interesado` → `calificado` → `documentacion` → `contrato` → `cerrado`

### 🏢 Módulo de Empresas (Configuración Inmobiliaria)
```sql
-- Empresas inmobiliarias (2 registros)
empresas (
    id, nombre, rfc, razon_social, domicilio, telefono, email,
    representante, proyectos, 
    -- Configuración de anticipos
    tipo_anticipo, porcentaje_anticipo, anticipo_fijo, apartado_minimo,
    -- Configuración de comisiones  
    tipo_comision, porcentaje_comision, comision_fija, apartado_comision,
    -- Configuración de financiamiento
    meses_sin_intereses, meses_con_intereses, porcentaje_interes_anual,
    dias_anticipo, porcentaje_cancelacion,
    activo, created_at, updated_at
);
```

**Empresas registradas:**
- **Anvar Inmobiliaria**: Empresa principal con configuración completa
- **Estabi**: Empresa de desarrollo con parámetros específicos

### 👨‍💼 Módulo de Staff Interno (8 registros)
```sql
-- Staff interno
staff (
    id, user_id, nombres, email, telefono, agencia,
    tipo, activo, creado_por, notas, created_at, updated_at
);

-- Relación staff-empresas
staff_empresas (
    id, staff_id, empresa_id, created_at
);
```

**Tipos de staff:**
- `superadministrador`, `administrador`, `admin`, `vendedor`

### 🏗️ Módulo de Proyectos (COMPLETAMENTE IMPLEMENTADO)
```sql
-- Proyectos inmobiliarios (2 registros)
proyectos (
    id, nombre, clave, empresa_id, descripcion, direccion,
    longitud, latitud, color, created_at, updated_at
);

-- Documentos adjuntos a proyectos (2 registros)
documentos_proyecto (
    id, proyecto_id, tipo_documento, nombre_archivo,
    ruta_archivo, tamaño_archivo, created_at, updated_at
);
```

**Proyectos activos:**
- **Valle Natura (VN)**: Proyecto de Anvar en Mazatlán
- **Cordelia Seminario (CS)**: Proyecto en desarrollo

### 📋 Catálogos de Sistema
```sql
-- Estados civiles (4 registros)
estados_civiles (
    id, nombre, valor, activo, created_at, updated_at
);

-- Fuentes de información (8 registros)  
fuentes_informacion (
    id, nombre, valor, activo, created_at, updated_at
);
```

**Estados civiles disponibles:**
- Soltero(a), Casado(a), Unión Libre, Viudo(a)

**Fuentes de información:**
- Referido, Señalítica, Facebook, Navegador, Instagram, Agencia Inmobiliaria, Espectacular, Otro

### 🔧 Tablas de Sistema
```sql
-- Migraciones ejecutadas (20 registros)
migrations (
    id, version, class, group, namespace, time, batch
);

-- Configuraciones del sistema (CodeIgniter Settings)
settings (
    id, class, key, value, type, context, created_at, updated_at
);
```

---

## 🚧 TABLAS PENDIENTES POR MÓDULO

### 🏠 Módulo Propiedades (Próximo)
```sql
-- Propiedades principales (lotes, casas, departamentos)
propiedades (
    id, proyecto_id, tipo_propiedad, lote, manzana, superficie,
    frente, fondo, precio, estado, descripcion, coordenadas_gps,
    nivel, estacionamientos, created_at, updated_at
);

-- Amenidades disponibles
amenidades (
    id, nombre, descripcion, icono, activo, created_at, updated_at
);

-- Amenidades por proyecto
proyecto_amenidades (
    proyecto_id, amenidad_id
);

-- Amenidades específicas por propiedad
propiedad_amenidades (
    propiedad_id, amenidad_id
);
```

### 💰 Módulo Ventas
```sql
-- Ventas principales
ventas (
    id, cliente_id, propiedad_id, asesor_id, fecha_venta,
    precio_venta, enganche, financiamiento, plazo_meses,
    tasa_interes, estado, observaciones, created_at, updated_at
);

-- Contratos de venta
contratos (
    id, venta_id, tipo_contrato, numero_contrato, fecha_firma,
    archivo_contrato, estado, created_at, updated_at
);

-- Etapas del proceso de venta
etapas_venta (
    id, venta_id, etapa, fecha_etapa, usuario_id,
    observaciones, created_at
);
```

### 💳 Módulo Pagos y Cobranza
```sql
-- Planes de pago
plan_pagos (
    id, venta_id, numero_pago, concepto, monto,
    fecha_vencimiento, estado, created_at, updated_at
);

-- Pagos realizados
pagos (
    id, plan_pago_id, venta_id, monto_pagado, fecha_pago,
    forma_pago, referencia, recibo_url, observaciones,
    registrado_por, created_at, updated_at
);

-- Sistema de tickets para cobranza
cobranza_tickets (
    id, cliente_id, venta_id, asunto, descripcion, prioridad,
    estado, asignado_a, created_at, updated_at
);
```

### 👨‍💼 Módulo Asesores
```sql
-- Asesores de ventas
asesores (
    id, user_id, codigo_asesor, telefono, fecha_ingreso,
    porcentaje_comision, meta_mensual, activo, created_at, updated_at
);

-- Asignación de clientes a asesores
asesor_clientes (
    id, asesor_id, cliente_id, fecha_asignacion, activo,
    created_at, updated_at
);

-- Comisiones de asesores
comisiones (
    id, asesor_id, venta_id, porcentaje, monto_comision,
    fecha_pago, estado, observaciones, created_at, updated_at
);
```

### 📊 Módulo Reportes
```sql
-- Configuración de reportes personalizados
reportes_configuracion (
    id, usuario_id, nombre_reporte, tipo_reporte, parametros,
    frecuencia, activo, created_at, updated_at
);

-- Métricas del sistema (para dashboards)
metricas_sistema (
    id, fecha, total_ventas_dia, total_pagos_dia,
    clientes_nuevos_dia, propiedades_vendidas_dia,
    tickets_abiertos, tickets_cerrados, created_at
);
```

---

## 🔄 ESTRATEGIA DE MIGRACIONES

### Flujo de Desarrollo Implementado
```
1. Sistema base (Shield + Settings)
2. Módulo clientes completo (6 tablas)
3. Catálogos básicos (estados civiles, fuentes)
4. Expansión empresas (configuración inmobiliaria)
5. Sistema staff interno
6. Módulo proyectos (COMPLETADO)
7. → Próximo: Propiedades
```

### Comandos de Migración
```bash
# Ver estado actual
php spark migrate:status

# Ejecutar migraciones pendientes
php spark migrate

# Rollback específico
php spark migrate:rollback

# Crear nueva migración para próximo módulo
php spark make:migration CreatePropiedadesTable
```

### Convenciones Implementadas
```bash
# Formato real de archivos
YYYY-MM-DD-HHMMSS_NombreMigracionTable.php

# Ejemplos del proyecto
2025-06-29-003602_CreateProyectosTable.php
2025-06-29-003612_CreateDocumentosProyectoTable.php
2025-06-28-020011_CreateStaffTable.php
```

---

## 📈 DATOS DE PRODUCCIÓN

### Registros Actuales por Tabla
```
✅ Tablas con datos:
- users: 80 registros
- auth_identities: 73 registros
- auth_groups_users: 78 registros
- auth_logins: 107 registros
- clientes: 51 registros
- direcciones_clientes: 22 registros
- referencias_clientes: 31 registros
- informacion_laboral_clientes: 19 registros
- informacion_conyuge_clientes: 12 registros
- empresas: 2 registros
- staff: 8 registros
- proyectos: 2 registros
- documentos_proyecto: 2 registros
- estados_civiles: 4 registros
- fuentes_informacion: 8 registros
- migrations: 20 registros

📂 Archivos cargados: 2 documentos en /uploads/proyectos/
```

### Integridad Referencial
- ✅ **Foreign keys activas** en todas las relaciones
- ✅ **CASCADE delete** en relaciones dependientes
- ✅ **Índices optimizados** para consultas frecuentes

---

## 🔧 PROCESO DE INCORPORACIÓN DE NUEVAS TABLAS

### 1. Identificar Necesidades del Módulo
```bash
# Ejemplo para Propiedades:
- ¿Qué tipos de propiedades? (lote, casa, departamento)
- ¿Relación con proyectos? (FK a proyectos)
- ¿Amenidades por proyecto o por propiedad?
```

### 2. Diseñar Relaciones con Tablas Existentes
```sql
-- Ejemplo: Propiedades se relaciona con:
proyecto_id → proyectos(id)           # Cada propiedad pertenece a un proyecto
cliente_id → clientes(id)             # Propiedades vendidas
asesor_id → asesores(id)              # Asesor asignado (futuro)
```

### 3. Crear y Ejecutar Migración
```bash
php spark make:migration CreatePropiedadesTable
# Editar archivo generado con estructura necesaria
php spark migrate
```

### 4. Actualizar Schema de Referencia
```bash
# Después de crear nuevas tablas:
mysqldump nuevoanvar > app/Database/nuevoanvar.sql
```

### 5. Implementar Modelos y Controladores
```php
// Seguir patrón establecido:
- app/Models/PropiedadModel.php
- app/Entities/Propiedad.php
- app/Controllers/Admin/AdminPropiedadesController.php
```

---

## ⚠️ IMPORTANTE para Claude Code

### Al Trabajar con Nuevas Tablas:
1. **Revisar tablas existentes** antes de crear nuevas
2. **Usar foreign keys apropiadas** para mantener integridad
3. **Seguir convenciones** del proyecto (created_at, updated_at)
4. **Considerar relaciones futuras** al diseñar campos
5. **Actualizar nuevoanvar.sql** después de cambios

### Comandos Útiles para Desarrollo:
```bash
# Ver estructura de tabla específica
DESCRIBE nombre_tabla;

# Ver foreign keys de una tabla
SHOW CREATE TABLE nombre_tabla;

# Verificar datos en tabla
SELECT COUNT(*) FROM nombre_tabla;
```

---

## 📁 ARCHIVOS DE REFERENCIA

### Schema Completo
- **`app/Database/nuevoanvar.sql`** - Estructura completa actualizada
- **`app/Database/Migrations/`** - Historial de cambios incremental

### Comandos de Backup
```bash
# Generar backup completo
mysqldump nuevoanvar > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql nuevoanvar < backup_fecha.sql
```

---

**La base de datos está sólida y lista para recibir los próximos módulos! 🗄️**# DATABASE.md - Estructura de Base de Datos NuevoAnvar

Documentación completa de la estructura de base de datos del sistema inmobiliario en desarrollo incremental.

## 🎯 ESTRATEGIA DE BASE DE DATOS

### Filosofía de Desarrollo
- **Incremental**: Agregamos tablas según módulos implementados
- **Modular**: Cada módulo aporta sus tablas específicas
- **Relacional**: Mantenemos integridad referencial con foreign keys
- **Escalable**: Diseño que permite crecimiento futuro

### Estado Actual
⚠️ **BASE DE DATOS EN EXPANSIÓN**: Sistema en construcción activa. Comenzamos con tablas fundamentales y expandimos según necesidades de cada módulo.

---

## 🗄️ TABLAS IMPLEMENTADAS Y FUNCIONANDO

### 📊 Resumen de Implementación
```
✅ Autenticación (Shield): 6 tablas
✅ Clientes Completo: 6 tablas
✅ Empresas: 1 tabla
✅ Staff: 1 tabla
✅ Proyectos: 2 tablas (MÓDULO COMPLETO)
✅ Catálogos: 2 tablas

Total implementado: 18 tablas
```

### 🔐 Módulo de Autenticación (Shield)
```sql
-- Tablas del sistema CodeIgniter Shield (implementadas)
users                    # Usuarios del sistema
auth_identities         # Credenciales (email/password)
auth_groups_users       # Asignación de roles
auth_remember_tokens    # Tokens "recordarme"
auth_permissions_users  # Permisos específicos por usuario
auth_groups             # Grupos/roles del sistema
```

### 👥 Módulo de Clientes (Completo)
```sql
-- Cliente principal
clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,                    # FK a users
    nombres VARCHAR(100),
    apellido_paterno VARCHAR(100),
    apellido_materno VARCHAR(100),
    fecha_nacimiento DATE,
    genero ENUM('masculino', 'femenino'),
    estado_civil_id INT,           # FK a estados_civiles
    curp VARCHAR(18),
    rfc VARCHAR(13),
    telefono VARCHAR(15),
    email VARCHAR(255),
    fuente_informacion_id INT,     # FK a fuentes_informacion
    etapa_proceso VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (estado_civil_id) REFERENCES estados_civiles(id),
    FOREIGN KEY (fuente_informacion_id) REFERENCES fuentes_informacion(id)
);

-- Direcciones múltiples por cliente
direcciones_clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,                # FK a clientes
    domicilio VARCHAR(500),
    numero VARCHAR(20),
    colonia VARCHAR(200),
    codigo_postal VARCHAR(10),
    ciudad VARCHAR(150),
    estado VARCHAR(100),
    tiempo_radicando VARCHAR(50),
    tipo_residencia VARCHAR(100),
    residente VARCHAR(100),
    tipo ENUM('principal', 'secundaria'),
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Referencias familiares/comerciales
referencias_clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,                # FK a clientes
    nombre_completo VARCHAR(255),
    telefono VARCHAR(15),
    parentesco VARCHAR(100),
    ocupacion VARCHAR(150),
    empresa VARCHAR(200),
    tiempo_conocerlo VARCHAR(50),
    tipo ENUM('familiar', 'comercial'),
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Información laboral
informacion_laboral (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,                # FK a clientes
    empresa VARCHAR(200),
    puesto VARCHAR(150),
    telefono_empresa VARCHAR(15),
    direccion_empresa VARCHAR(500),
    tiempo_laborando VARCHAR(50),
    ingresos_mensuales DECIMAL(12,2),
    otros_ingresos DECIMAL(12,2),
    descripcion_otros_ingresos TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Información del cónyuge
informacion_conyuges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,                # FK a clientes
    nombre_completo VARCHAR(255),
    fecha_nacimiento DATE,
    telefono VARCHAR(15),
    empresa VARCHAR(200),
    puesto VARCHAR(150),
    ingresos_mensuales DECIMAL(12,2),
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);
```

### 🏢 Módulo de Empresas
```sql
empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    razon_social VARCHAR(255),
    rfc VARCHAR(13),
    direccion VARCHAR(500),
    telefono VARCHAR(15),
    email VARCHAR(255),
    representante_legal VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME
);
```

### 👨‍💼 Módulo de Staff Interno
```sql
staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,                   # FK a users
    codigo_empleado VARCHAR(50),
    nombres VARCHAR(100),
    apellidos VARCHAR(200),
    puesto VARCHAR(100),
    departamento VARCHAR(100),
    fecha_ingreso DATE,
    telefono VARCHAR(15),
    extension VARCHAR(10),
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 🏗️ Módulo de Proyectos (COMPLETO)
```sql
-- Proyectos inmobiliarios principales
proyectos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    clave VARCHAR(100),            # Clave única del proyecto
    empresa_id INT,               # FK a empresas
    descripcion TEXT,
    direccion VARCHAR(500),
    longitud VARCHAR(50),         # Coordenada GPS
    latitud VARCHAR(50),          # Coordenada GPS
    color VARCHAR(7),             # Color hex para identificación
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
);

-- Documentos adjuntos a proyectos
documentos_proyecto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proyecto_id INT,              # FK a proyectos
    tipo_documento VARCHAR(100),
    nombre_archivo VARCHAR(255),
    ruta_archivo VARCHAR(500),
    tamaño_archivo INT,           # En bytes
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
);
```

### 📋 Catálogos de Sistema
```sql
-- Estados civiles
estados_civiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),           # Soltero, Casado, etc.
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME
);

-- Fuentes de información (cómo nos conocieron)
fuentes_informacion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),          # Facebook, Referencia, etc.
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME
);
```

---

## 🚧 TABLAS PENDIENTES POR MÓDULO

### 🏠 Módulo Propiedades (Próximo)
```sql
-- Propiedades principales (lotes, casas, departamentos)
propiedades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proyecto_id INT,              # FK a proyectos
    tipo_propiedad ENUM('lote', 'casa', 'departamento', 'local'),
    lote VARCHAR(10),             # Número de lote
    manzana VARCHAR(10),          # Manzana (opcional)
    superficie DECIMAL(10,2),     # En m²
    frente DECIMAL(10,2),         # Metros de frente
    fondo DECIMAL(10,2),          # Metros de fondo
    precio DECIMAL(12,2),         # Precio de venta
    estado ENUM('disponible', 'apartado', 'vendido', 'reservado'),
    descripcion TEXT,
    coordenadas_gps VARCHAR(100), # Para ubicación específica
    nivel INT,                    # Para departamentos (piso)
    estacionamientos INT,         # Número de estacionamientos
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
);

-- Amenidades disponibles
amenidades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),          # Alberca, Casa club, etc.
    descripcion TEXT,
    icono VARCHAR(50),            # Clase de ícono FontAwesome
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME
);

-- Amenidades por proyecto
proyecto_amenidades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    proyecto_id INT,              # FK a proyectos
    amenidad_id INT,             # FK a amenidades
    
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    FOREIGN KEY (amenidad_id) REFERENCES amenidades(id) ON DELETE CASCADE,
    UNIQUE KEY unique_proyecto_amenidad (proyecto_id, amenidad_id)
);

-- Amenidades específicas por propiedad (opcional)
propiedad_amenidades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    propiedad_id INT,            # FK a propiedades
    amenidad_id INT,             # FK a amenidades
    
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    FOREIGN KEY (amenidad_id) REFERENCES amenidades(id) ON DELETE CASCADE,
    UNIQUE KEY unique_propiedad_amenidad (propiedad_id, amenidad_id)
);
```

### 💰 Módulo Ventas
```sql
-- Ventas principales
ventas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,               # FK a clientes
    propiedad_id INT,            # FK a propiedades
    asesor_id INT,               # FK a asesores
    fecha_venta DATE,
    precio_venta DECIMAL(12,2),
    enganche DECIMAL(12,2),
    financiamiento DECIMAL(12,2),
    plazo_meses INT,
    tasa_interes DECIMAL(5,2),
    estado ENUM('prospecto', 'apartado', 'contrato', 'escriturado', 'cancelado'),
    observaciones TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id),
    FOREIGN KEY (asesor_id) REFERENCES asesores(id)
);

-- Contratos de venta
contratos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT,                # FK a ventas
    tipo_contrato VARCHAR(100),   # Promesa de venta, Contrato definitivo
    numero_contrato VARCHAR(50),
    fecha_firma DATE,
    archivo_contrato VARCHAR(500), # Ruta del PDF
    estado ENUM('borrador', 'firmado', 'cancelado'),
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
);

-- Etapas del proceso de venta
etapas_venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT,                # FK a ventas
    etapa VARCHAR(100),          # Prospecto, Calificación, etc.
    fecha_etapa DATETIME,
    usuario_id INT,              # Quién marcó la etapa
    observaciones TEXT,
    created_at DATETIME,
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);
```

### 💳 Módulo Pagos y Cobranza
```sql
-- Planes de pago
plan_pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT,                # FK a ventas
    numero_pago INT,             # 1, 2, 3...
    concepto VARCHAR(200),       # Enganche, Mensualidad, etc.
    monto DECIMAL(12,2),
    fecha_vencimiento DATE,
    estado ENUM('pendiente', 'pagado', 'vencido'),
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
);

-- Pagos realizados
pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_pago_id INT,            # FK a plan_pagos
    venta_id INT,                # FK a ventas (para consultas rápidas)
    monto_pagado DECIMAL(12,2),
    fecha_pago DATE,
    forma_pago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta'),
    referencia VARCHAR(100),     # Referencia bancaria
    recibo_url VARCHAR(500),     # PDF del recibo
    observaciones TEXT,
    registrado_por INT,          # FK a users
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (plan_pago_id) REFERENCES plan_pagos(id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (registrado_por) REFERENCES users(id)
);

-- Sistema de tickets para cobranza
cobranza_tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,              # FK a clientes
    venta_id INT,                # FK a ventas
    asunto VARCHAR(255),
    descripcion TEXT,
    prioridad ENUM('baja', 'media', 'alta', 'urgente'),
    estado ENUM('abierto', 'en_proceso', 'pendiente_documentos', 'resuelto', 'cerrado'),
    asignado_a INT,              # FK a users (staff)
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (asignado_a) REFERENCES users(id)
);
```

### 👨‍💼 Módulo Asesores
```sql
-- Asesores de ventas
asesores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,                 # FK a users
    codigo_asesor VARCHAR(20),   # Código único
    telefono VARCHAR(15),
    fecha_ingreso DATE,
    porcentaje_comision DECIMAL(5,2), # % de comisión por defecto
    meta_mensual DECIMAL(12,2),
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Asignación de clientes a asesores
asesor_clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asesor_id INT,               # FK a asesores
    cliente_id INT,              # FK a clientes
    fecha_asignacion DATE,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (asesor_id) REFERENCES asesores(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Comisiones de asesores
comisiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asesor_id INT,               # FK a asesores
    venta_id INT,                # FK a ventas
    porcentaje DECIMAL(5,2),     # % aplicado
    monto_comision DECIMAL(12,2), # Monto calculado
    fecha_pago DATE,             # Cuándo se pagó
    estado ENUM('pendiente', 'pagada', 'cancelada'),
    observaciones TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (asesor_id) REFERENCES asesores(id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id)
);
```

### 📊 Módulo Reportes
```sql
-- Configuración de reportes personalizados
reportes_configuracion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,              # FK a users
    nombre_reporte VARCHAR(255),
    tipo_reporte VARCHAR(100),   # ventas, cobranza, asesores
    parametros JSON,             # Filtros y configuración
    frecuencia ENUM('manual', 'diario', 'semanal', 'mensual'),
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME,
    updated_at DATETIME,
    
    FOREIGN KEY (usuario_id) REFERENCES users(id)
);

-- Métricas del sistema (para dashboards)
metricas_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE,
    total_ventas_dia DECIMAL(12,2),
    total_pagos_dia DECIMAL(12,2),
    clientes_nuevos_dia INT,
    propiedades_vendidas_dia INT,
    tickets_abiertos INT,
    tickets_cerrados INT,
    created_at DATETIME
);
```

---

## 🔄 ESTRATEGIA DE MIGRACIONES

### Flujo de Desarrollo
```
1. Diseñar tablas del módulo
2. Crear migraciones incrementales
3. Ejecutar migraciones
4. Implementar modelos/entidades
5. Crear controladores/vistas
6. Actualizar schema de referencia
```

### Comandos de Migración
```bash
# Crear nueva migración
php spark make:migration CreateTablaModuloTable

# Ejecutar migraciones pendientes
php spark migrate

# Rollback última migración
php spark migrate:rollback

# Verificar estado
php spark migrate:status
```

### Convenciones de Nombres
```bash
# Formato de archivos
YYYY-MM-DD-HHMMSS_CreateNombreTablaTable.php

# Ejemplos reales
2025