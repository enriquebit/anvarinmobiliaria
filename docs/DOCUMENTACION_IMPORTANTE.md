# 🏗️ PROMPT: ANÁLISIS PROFUNDO DE SISTEMA INMOBILIARIO LEGACY PARA MIGRACIÓN A CODEIGNITER 4

## 🎯 OBJETIVO PRINCIPAL
Realizar ingeniería inversa completa de un sistema inmobiliario legacy (PHP 7) para comprender su lógica de negocio y generar documentación técnica detallada que permita su migración exitosa a CodeIgniter 4, manteniendo la funcionalidad actual pero con arquitectura moderna y escalable.

## 📋 CONTEXTO DEL SISTEMA ACTUAL
- **Tecnología**: PHP 7, código espagueti sin estructura MVC
- **Arquitectura**: Procedural/funcional, sin separación de responsabilidades
- **Estado**: Sistema en producción, ~300 archivos PHP core
- **Documentación**: Inexistente (solo comentarios en código)
- **Entry Point**: `login.php`
- **Configuraciones**: `./administracion/comandos/`, `./administracion/`, `./administracion/claves/`

## 🔍 INSTRUCCIONES DE ANÁLISIS

### FASE 1: MAPEO INICIAL DEL SISTEMA
1. **Comenzar desde el entry point** (`login.php`) y mapear el flujo de navegación
2. **Identificar la estructura de carpetas** y su propósito funcional
3. **Analizar archivos de configuración** en las carpetas mencionadas
4. **Detectar patrones de inclusión** de archivos y dependencias
5. **Documentar el flujo de autenticación** y manejo de sesiones

### FASE 2: ANÁLISIS DE LÓGICA DE NEGOCIO (PRIORIDAD ALTA)
Analizar en este orden específico:

#### 2.1 GESTIÓN DE PROYECTOS Y LOTIFICACIÓN
- Creación y administración de proyectos inmobiliarios
- Sistema de lotificación (división en manzanas/lotes)
- Configuración de precios por terreno
- Disponibilidad y estados de lotes
- Relaciones proyecto → manzana → lote

#### 2.2 SISTEMA FINANCIERO Y PAGOS
- **Tipos de pago**: Enganche (puede ser 0), mensualidades, pagos únicos
- **Tablas de amortización**: Cálculos, intereses, capital
- **Planes de pago**: Configuración flexible
- **Cobranza**: Vencimientos, intereses moratorios, recargos
- **Contratos**: Generación, términos, condiciones
- **Flujo de efectivo**: Ingresos, egresos, balances

#### 2.3 REPORTES Y CONTABILIDAD
- Estados de cuenta por cliente/lote
- Reportes de cartera vencida
- Balances financieros
- Reportes de ventas y cobranza

### FASE 3: ANÁLISIS TÉCNICO DETALLADO
1. **Mapear todas las consultas SQL** y relaciones de base de datos
2. **Identificar funciones críticas** y algoritmos de cálculo
3. **Documentar validaciones** y reglas de negocio
4. **Analizar manejo de errores** y casos edge
5. **Detectar vulnerabilidades** de seguridad básicas

## 📊 ESTRUCTURA DE DOCUMENTACIÓN REQUERIDA

### DOCUMENTO PRINCIPAL: `ANALISIS_SISTEMA_LEGACY.md`
```markdown
# 1. RESUMEN EJECUTIVO
- Arquitectura actual detectada
- Módulos principales identificados
- Complejidad técnica estimada
- Recomendaciones de migración

# 2. MAPA DEL SISTEMA
- Estructura de carpetas y archivos
- Flujos de navegación principales
- Entry points y dependencias críticas

# 3. ANÁLISIS DE BASE DE DATOS
- Esquema actual (tablas, relaciones)
- Queries críticas identificadas
- Propuesta de normalización para CI4

# 4. MÓDULOS DE NEGOCIO IDENTIFICADOS
## 4.1 Gestión de Proyectos y Lotificación
## 4.2 Sistema Financiero y Pagos
## 4.3 Reportes y Contabilidad
## 4.4 Administración de Usuarios
## 4.5 [Otros módulos detectados]

# 5. LÓGICA DE NEGOCIO CRÍTICA
- Algoritmos de cálculo financiero
- Reglas de validación
- Procesos automatizados

# 6. PROPUESTA DE ARQUITECTURA CI4
- Estructura de controladores sugerida
- Modelos y entidades necesarias
- Servicios y bibliotecas helper
- Integración con Shield (ya implementado)
```

### DOCUMENTOS ADICIONALES (SI ES NECESARIO)
- `MODULO_PROYECTOS_LOTIFICACION.md`
- `MODULO_SISTEMA_FINANCIERO.md`
- `MODULO_REPORTES_CONTABILIDAD.md`
- `MIGRACION_BASE_DATOS.md`
- `ROADMAP_IMPLEMENTACION.md`

## 🎯 ENFOQUE ESPECÍFICO PARA CI4

### ARQUITECTURA OBJETIVO
- **Patrón**: Entity-First + MVC
- **Metodología**: MVP (Minimum Viable Product) + DRY
- **Seguridad**: Niveles mínimos sin interferir desarrollo
- **Autenticación**: Shield (ya implementado en base actual)

### ANÁLISIS DEL PROYECTO BASE CI4
1. **Evaluar estructura actual**: CRUD de clientes, usuarios, empresas, proyectos
2. **Identificar gaps**: Entidades faltantes (lotes, pagos, contratos)
3. **Proponer evolución incremental** de la base existente
4. **Sugerir mejoras** en controladores/modelos actuales

## ⚡ METODOLOGÍA DE EJECUCIÓN

### COMANDO DE INICIO
```bash
/init "Analiza este sistema inmobiliario legacy (~300 archivos PHP) comenzando desde login.php. Genera documentación técnica completa para migración a CI4. Prioriza: 1) Proyectos/lotificación, 2) Sistema financiero/pagos, 3) Reportes. Crea ANALISIS_SISTEMA_LEGACY.md principal + documentos específicos si es necesario. Enfoque Entity-First + MVC para CI4."
```

### PROCESO DE ANÁLISIS
1. **Exploración sistemática**: Carpeta por carpeta, archivo por archivo
2. **Documentación incremental**: Generar .md conforme avanza
3. **Validación cruzada**: Verificar lógica entre módulos
4. **Propuestas técnicas**: Sugerencias proactivas para CI4

## 🚨 CONSIDERACIONES CRÍTICAS

### ASPECTOS FINANCIEROS (MÁXIMA PRIORIDAD)
- **Precisión en cálculos**: Verificar algoritmos de amortización
- **Integridad de datos**: Validar consistencia en pagos/saldos
- **Auditabilidad**: Rastrear transacciones y modificaciones
- **Reportes críticos**: Estados de cuenta, cartera vencida

### MIGRACIÓN DE DATOS
- **Compatibilidad**: 0% - Enfocar en lógica de negocio
- **Optimización**: Normalizar y mejorar estructura DB
- **Validación**: Verificar integridad en migración

### SEGURIDAD MÍNIMA
- Validación básica de inputs
- Sanitización de queries
- Control de acceso por roles
- Logs de transacciones críticas

## 📈 ENTREGABLES ESPERADOS

1. **Documentación técnica completa** en formato .md
2. **Mapeo de entidades** para implementar en CI4
3. **Propuesta de arquitectura** específica para el proyecto
4. **Roadmap de implementación** incremental
5. **Identificación de riesgos** y puntos críticos
6. **Sugerencias de mejora** sobre base CI4 actual

## 🎪 INSTRUCCIONES FINALES

- **Ser proactivo**: Sugerir mejoras basadas en experiencia en sistemas inmobiliarios
- **Detalle técnico**: Nivel comprensible para Claude Code
- **Enfoque práctico**: Priorizar implementación sobre teoría
- **Documentación viva**: Archivos .md listos para usar como referencia de desarrollo

**¡PROCEDE CON EL ANÁLISIS COMPLETO!**