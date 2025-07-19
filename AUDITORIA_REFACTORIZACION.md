# 🔍 AUDITORÍA DE REFACTORIZACIÓN - MÓDULO DE VENTAS

## 📊 Resumen Ejecutivo

**TODAS** las tablas del módulo de ventas necesitan refactorización de sus llaves primarias para cumplir con el estándar CodeIgniter 4 de usar `id` como llave primaria.

## 📋 Tablas Afectadas (16 tablas)

### Tablas con sus llaves primarias actuales:
1. `apartados` → `apartado_id`
2. `bonos_comisiones` → `bono_id`
3. `comisiones_ventas` → `comision_id`
4. `configuracion_comisiones` → `config_comision_id`
5. `devoluciones_ventas` → `devolucion_id`
6. `lotes_bloqueados` → `bloqueo_id`
7. `pagos_aplicados` → `aplicacion_id`
8. `pagos_comisiones` → `pago_comision_id`
9. `pagos_ventas` → `pago_id`
10. `planes_financiamiento` → `plan_id`
11. `tabla_amortizacion` → `amortizacion_id`
12. `tipos_plan_financiamiento` → `tipo_plan_id`
13. `ventas` → `venta_id`
14. `ventas_documentos` → `documento_id`
15. `ventas_historial` → `historial_id`
16. `ventas_notificaciones` → `notificacion_id`

## 🔗 Impacto en Foreign Keys

### Tablas más referenciadas:
- **ventas** (6 referencias)
- **tipos_plan_financiamiento** (3 referencias)
- **apartados** (2 referencias)
- **tabla_amortizacion** (2 referencias)
- **planes_financiamiento** (2 referencias)

## 📝 Cambios Requeridos

### 1. Base de Datos
- Script SQL creado: `refactor_primary_keys_ventas.sql`
- Cambiar todas las llaves primarias a `id`
- Actualizar todas las foreign keys para usar el formato `tabla_id`

### 2. Entidades (7 archivos)
- `TipoPlanFinanciamiento.php`
- `Apartado.php`
- `Venta.php`
- `PlanFinanciamiento.php`
- `TablaAmortizacion.php`
- `PagoVenta.php`
- `ComisionVenta.php`

### 3. Modelos (Pendientes de crear)
- Todos los modelos deberán usar `protected $primaryKey = 'id';` (valor por defecto)

### 4. Controladores (Pendientes de crear)
- Los controladores usarán el estándar `id` para todas las operaciones CRUD

## ⚠️ Consideraciones Importantes

1. **Orden de ejecución**: El script SQL debe ejecutarse en el orden especificado para mantener la integridad referencial
2. **Foreign Keys**: Se renombrarán para seguir el patrón `tabla_id` (ej: `venta_id` en otras tablas que referencian a `ventas`)
3. **Compatibilidad**: Este cambio afectará cualquier código existente que use las llaves primarias antiguas

## 🚀 Plan de Acción

1. ✅ Auditoría completada
2. ✅ Script SQL de refactorización creado
3. ⏳ Ejecutar script SQL
4. ⏳ Actualizar Entidades
5. ⏳ Crear/Actualizar Modelos
6. ⏳ Crear Controladores
7. ⏳ Crear Vistas