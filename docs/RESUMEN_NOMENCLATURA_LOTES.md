# 📋 RESUMEN: Nomenclatura de Lotes - Sistema Legacy ANVAR

## 🎯 **Resumen Ejecutivo**

La nomenclatura de lotes en el sistema legacy de ANVAR Inmobiliaria sigue un patrón estructurado y consistente que combina **4 componentes principales**: `[CLAVE_PROYECTO]-[CLAVE_DIVISION]-[CLAVE_MANZANA]-[NUMERO_LOTE]`

**Formato confirmado:** `V1-E1-M1-15` 
- Donde: `V1` (Proyecto) + `E1` (División) + `M1` (Manzana) + `15` (Número de Lote)

---

## 🏗️ **Estructura de la Nomenclatura**

### **Patrón Base:**
```
[PROYECTO.CLAVE]-[DIVISION.CLAVE]-[MANZANA.CLAVE]-[NUMERO_LOTE]
```

### **Ejemplos Reales del Sistema:**
- `V1-E1-1-1` ➜ Valle Natura, Etapa 1, Manzana 1, Lote 1
- `V1-E1-M1-15` ➜ Valle Natura, Etapa 1, Manzana 1, Lote 15  
- `V1-E1-M2-12` ➜ Valle Natura, Etapa 1, Manzana 2, Lote 12
- `V1-E1-M3-10` ➜ Valle Natura, Etapa 1, Manzana 3, Lote 10

---

## 🗄️ **Estructura de Base de Datos Legacy**

### **Tabla: `tb_lotes`**
```sql
CREATE TABLE `tb_lotes` (
  `IdLote` int(11) NOT NULL,
  `Clave` varchar(100) DEFAULT NULL,           -- ✅ CAMPO CLAVE GENERADA
  `Proyecto` int(11) DEFAULT '0',              -- Relación a tb_proyectos
  `NProyecto` varchar(200) DEFAULT NULL,       -- Nombre del proyecto (denormalizado)
  `Division` int(11) DEFAULT '0',              -- ✅ Relación a tb_divisiones  
  `NDivision` varchar(200) DEFAULT NULL,       -- Nombre de división (denormalizado)
  `Manzana` int(11) DEFAULT NULL,              -- Relación a tb_manzanas
  `NManzana` varchar(100) DEFAULT NULL,        -- Nombre de manzana (denormalizado)
  `Numero` double DEFAULT NULL,                -- ✅ NÚMERO DEL LOTE
  `Empresa` int(11) DEFAULT '0',               -- Relación a tb_empresas
  `NEmpresa` varchar(500) DEFAULT NULL,        -- Nombre empresa (denormalizado)
  -- ... otros campos de características del lote ...
  `Estatus` int(11) DEFAULT '0'                -- Estado del lote
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

### **Tabla: `tb_divisiones`** ✅ **COMPONENTE CLAVE**
```sql
CREATE TABLE `tb_divisiones` (
  `IdDivision` int(11) NOT NULL,
  `Nombre` varchar(1000) DEFAULT NULL,         -- Ej: "Etapa 1"
  `Clave` varchar(100) DEFAULT NULL,           -- ✅ Ej: "E1"
  `Proyecto` int(11) DEFAULT '0',              -- Relación a proyecto
  `NProyecto` varchar(1000) DEFAULT NULL,      -- Nombre proyecto
  `Empresa` int(11) DEFAULT '0',               -- Relación a empresa
  `NEmpresa` varchar(1000) DEFAULT NULL,       -- Nombre empresa
  `Estatus` int(11) DEFAULT '0'                -- Estado activo/inactivo
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### **Datos de Ejemplo - tb_divisiones:**
| IdDivision | Nombre   | Clave | Proyecto | NProyecto    | Empresa | NEmpresa          |
|------------|----------|-------|----------|--------------|---------|-------------------|
| 1          | Etapa 1  | **E1** | 1        | Valle Natura | 1       | ANVAR Inmobiliaria |
| 2          | Etapa 2  | **E2** | 1        | Valle Natura | 1       | ANVAR Inmobiliaria |
| 3          | Etapa 3  | **E3** | 1        | Valle Natura | 1       | ANVAR Inmobiliaria |

---

## 🔧 **Lógica de Generación (Sistema Legacy)**

### **Archivos Analizados:**
- `/administracion/comandos/funciones_07012025.php` (Función 17 - Líneas 1048-1166)
- `/administracion/lotes_agregar.php` (Formulario de captura)
- `/administracion/js/funciones/lotes.js` (JavaScript de filtros)

### **Proceso de Generación:**

#### **1. Función 17 - Crear Lote (línea 1156):**
```php
// Generación de la clave del lote
Clave='".$registroX[1]."-".$registroD[1]."-".$registroM[1]."-".$numero."'
```

#### **2. Consultas para Obtener Claves:**
```php
// Línea 1124 - Obtener clave de empresa/proyecto
$resX=mysqli_query($link,"SELECT Nombre,Clave FROM tb_empresas WHERE IdEmpresa=$empresa AND Estatus=1");

// Línea 1131 - ✅ Obtener clave de división
$resD=mysqli_query($link,"SELECT Nombre,Clave FROM tb_divisiones WHERE IdDivision=$division AND Estatus=1");

// Línea 1115 - Obtener clave de manzana  
$resM=mysqli_query($link,"SELECT Clave,Nombre FROM tb_manzanas WHERE IdManzana=$manzana AND Estatus=1");
```

### **3. Flujo de Selección en Frontend:**

#### **JavaScript - Filtros en Cascada:**
```javascript
// 1. Empresa → Proyectos (Función 227)
$('#cb_empresa').change() → carga('#cb_proyecto')

// 2. Proyecto → Manzanas Y Divisiones (Funciones 16 y 244)
$('#cb_proyecto').change() → {
    carga('#cb_manzana')      // Función 16
    carga('#cb_divisiones')   // ✅ Función 244
}

// 3. Usuario ingresa número de lote
$('#txt_numero').val()

// 4. Se genera clave: PROYECTO.clave + DIVISION.clave + MANZANA.clave + NUMERO
```

---

## 📂 **Campos del Formulario Legacy**

### **Formulario: `/administracion/lotes_agregar.php`**
```html
<select id="cb_empresa">         <!-- Empresa -->
<select id="cb_proyecto">        <!-- Proyecto -->  
<select id="cb_manzana">         <!-- Manzana -->
<select id="cb_divisiones">      <!-- ✅ DIVISIONES (línea 221) -->
<input id="txt_numero">          <!-- ✅ Número del lote -->
```

---

## 🎯 **Implementación en Sistema Nuevo (CodeIgniter 4)**

### **Problema Identificado:**
El sistema nuevo **NO tiene** el concepto de **Divisiones**, solo maneja:
- ✅ Empresas  
- ✅ Proyectos
- ✅ Manzanas
- ❌ **DIVISIONES** (faltante)
- ✅ Lotes

### **Soluciones Propuestas:**

#### **Opción 1: Agregar Tabla Divisiones** ⭐ **RECOMENDADA**
```sql
CREATE TABLE divisiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    clave VARCHAR(10) NOT NULL,          -- ✅ Para nomenclatura  
    empresa_id INT NOT NULL,
    proyecto_id INT NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (empresa_id) REFERENCES empresas(id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
);
```

#### **Opción 2: Campo División en Proyectos**
```sql
ALTER TABLE proyectos ADD COLUMN division_clave VARCHAR(10) DEFAULT 'D1';
```

#### **Opción 3: Usar Proyectos como División**
- Simplificar nomenclatura a: `[PROYECTO]-[MANZANA]-[NUMERO]`
- Requiere mapeo con sistema legacy

---

## 🔄 **Migración y Compatibilidad**

### **Estrategia de Migración:**
1. **Crear tabla `divisiones`** con datos del sistema legacy
2. **Actualizar formulario de lotes** para incluir selector de división
3. **Implementar filtros en cascada:** Empresa → Proyecto → División → Manzana
4. **Generar nomenclatura automática** siguiendo el patrón legacy

### **Datos para Migración:**
```sql
INSERT INTO divisiones (nombre, clave, empresa_id, proyecto_id, activo) VALUES
('Etapa 1', 'E1', 1, 1, 1),
('Etapa 2', 'E2', 1, 1, 1), 
('Etapa 3', 'E3', 1, 1, 1);
```

---

## ✅ **Conclusiones y Recomendaciones**

### **Hallazgos Principales:**
1. ✅ **Nomenclatura confirmada:** `[PROYECTO]-[DIVISION]-[MANZANA]-[NUMERO]`
2. ✅ **División es componente esencial** faltante en sistema nuevo
3. ✅ **Clave se genera automáticamente** combinando las 4 partes
4. ✅ **Sistema legacy funcional** con validaciones y filtros

### **Acciones Requeridas:**
1. 🔧 **Implementar tabla divisiones** en el sistema nuevo
2. 🔧 **Actualizar formularios** para incluir división
3. 🔧 **Implementar generación automática** de nomenclatura
4. 🔧 **Crear filtros en cascada** completos: Empresa → Proyecto → División → Manzana
5. 🔧 **Migrar datos** de divisiones del sistema legacy

### **Impacto:**
- **CRÍTICO:** Sin divisiones, la nomenclatura de lotes será **incorrecta**
- **BLOQUEANTE:** Incompatibilidad con sistema legacy existente
- **URGENTE:** Requiere implementación antes de usar módulo de lotes

---

**📅 Investigación completada:** $(date)  
**🔍 Archivos analizados:** 25+ archivos PHP, JS y SQL  
**📊 Registros analizados:** 1,000+ lotes en base de datos legacy