# 🔧 MIGRACIÓN DE TRIGGERS A CODEIGNITER

## ⚠️ PROBLEMA RESUELTO

Los triggers MySQL estaban causando errores de importación en phpMyAdmin debido a restricciones de privilegios SUPER en hosting compartido:

```sql
#1227 - Acceso negado. Usted necesita el privilegio SUPER para esta operación
```

## ✅ SOLUCIÓN IMPLEMENTADA

Se migró la lógica de generación de folios de **triggers MySQL** a **CodeIgniter 4**, eliminando la dependencia de privilegios SUPER.

## 📁 ARCHIVOS MODIFICADOS/CREADOS

### 1. **Helper de Folios** 
`app/Helpers/folio_helper.php`
- Funciones para generar folios únicos
- Validación de formato de folios
- Extracción de año y número secuencial

### 2. **Modelo Actualizado**
`app/Models/RegistroClientesModel.php`
- Agregado campo `'folio'` a `$allowedFields`
- Callbacks `generarFolioAntes()` y `actualizarFolioDespues()`
- Método `insertarConFolio()` para reemplazar triggers
- Métodos de utilidad: `repararFolios()`, `getEstadisticasPorAno()`

### 3. **Controlador Actualizado**
`app/Controllers/Public/RegistroClientesController.php`
- Cambio de `insert()` a `insertarConFolio()` en línea 481

### 4. **Script de Base de Datos Limpio**
`DATABASE_SIN_TRIGGERS_PHPMYADMIN.sql`
- Todas las 40 tablas incluidas
- **SIN triggers** que requieran privilegios SUPER
- Optimizado para phpMyAdmin

### 5. **Comando de Mantenimiento**
`app/Commands/RepararFoliosCommand.php`
- Comando CLI para reparar folios existentes
- Modo dry-run para simular cambios
- Estadísticas antes y después

## 🚀 FUNCIONALIDAD

### Generación Automática de Folios

**Antes (Trigger MySQL):**
```sql
TRIGGER generar_folio_simple BEFORE INSERT ON registro_clientes
SET NEW.folio = CONCAT('REG-', YEAR(NOW()), '-', LPAD(NEW.id, 6, '0'));
```

**Ahora (CodeIgniter):**
```php
// Automático en insert
$registroId = $registroModel->insertarConFolio($datos);

// Folio generado: REG-2025-000001, REG-2025-000002, etc.
```

### Formato de Folios
- **Patrón:** `REG-YYYY-NNNNNN`
- **Ejemplo:** `REG-2025-000001`
- **Secuencial por año**

## 🔧 USO

### 1. Insertar Nuevo Registro
```php
use App\Models\RegistroClientesModel;

$registroModel = new RegistroClientesModel();

$data = [
    'firstname' => 'Juan',
    'lastname' => 'Pérez',
    'email' => 'juan@ejemplo.com',
    // ... otros campos
    // NO incluir 'folio' - se genera automáticamente
];

$id = $registroModel->insertarConFolio($data);
// Folio se genera automáticamente: REG-2025-000001
```

### 2. Reparar Folios Existentes
```bash
# Simular cambios (no ejecuta)
php spark folio:reparar --dry-run

# Ejecutar reparación
php spark folio:reparar --force
```

### 3. Buscar por Folio
```php
$registro = $registroModel->buscarPorFolio('REG-2025-000001');
```

### 4. Estadísticas
```php
$stats = $registroModel->getEstadisticasPorAno(2025);
// Retorna: total_registros, con_folio, pendientes_folio
```

## 📊 VENTAJAS DE LA MIGRACIÓN

### ✅ Compatibilidad con Hosting
- **Sin privilegios SUPER:** Funciona en hosting compartido/cPanel
- **Sin triggers:** No requiere permisos especiales de MySQL
- **phpMyAdmin compatible:** Importación sin errores

### ✅ Mayor Control
- **Logs detallados:** Seguimiento completo en CodeIgniter logs
- **Manejo de errores:** Recuperación graceful ante fallos
- **Transacciones:** Rollback automático si hay errores

### ✅ Mantenibilidad
- **Código PHP:** Más fácil de debuggear que SQL
- **Reparación:** Comando para corregir folios problemáticos
- **Estadísticas:** Métricas integradas de folios

### ✅ Flexibilidad
- **Formato configurable:** Fácil cambiar patrón de folios
- **Múltiples prefijos:** REG, CLI, VEN, etc.
- **Validación:** Verificación de formato integrada

## 🛠️ MIGRACIÓN PASO A PASO

### 1. Importar Base de Datos Limpia
```sql
-- Usar: DATABASE_SIN_TRIGGERS_PHPMYADMIN.sql
-- Este archivo NO tiene triggers y es compatible con phpMyAdmin
```

### 2. Reparar Folios Existentes
```bash
# Ver qué registros necesitan reparación
php spark folio:reparar --dry-run

# Ejecutar reparación
php spark folio:reparar --force
```

### 3. Verificar Funcionamiento
```bash
# Probar registro nuevo desde formulario público
# El folio debe generarse automáticamente
```

## 🐛 TROUBLESHOOTING

### Problema: Folio duplicado
```php
// Verificar si hay registros con folios problemáticos
$problematicos = $registroModel->where('folio', 'REG-2025-000000')->findAll();
```

### Problema: Helper no cargado
```php
// En el modelo, verificar que se carga el helper
public function __construct()
{
    parent::__construct();
    helper('folio'); // ← Debe estar presente
}
```

### Problema: Logs no aparecen
```php
// Verificar configuración de logs en .env
logger.threshold = 9  // Nivel DEBUG
```

## 📝 NOTAS IMPORTANTES

1. **Backward Compatibility:** Los registros existentes con folios válidos no se modifican
2. **Performance:** La generación de folios es eficiente (una consulta COUNT)
3. **Concurrencia:** Las transacciones evitan folios duplicados
4. **Rollback:** Si falla la asignación de folio, se deshace el insert completo

## 🎯 RESULTADO FINAL

✅ **Base de datos sin triggers** compatible con hosting compartido  
✅ **Folios únicos** generados automáticamente por CodeIgniter  
✅ **Importación exitosa** en phpMyAdmin sin errores SUPER  
✅ **Funcionalidad completa** mantenida sin pérdida de características  
✅ **Herramientas de mantenimiento** para reparar datos existentes  

---

**Migración completada exitosamente** 🎉  
**Fecha:** 2025-07-03  
**Responsable:** Claude Code Assistant