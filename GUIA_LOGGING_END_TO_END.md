# 🔍 GUÍA DE LOGGING END-TO-END - FLUJO CLIENTES-VENTAS

## 📊 SISTEMA DE TRAZABILIDAD COMPLETA

Este sistema permite realizar un seguimiento detallado de todos los procesos del flujo cliente-ventas, registrando cada interacción, consulta y operación en un archivo de log independiente.

## 🛠️ COMPONENTES IMPLEMENTADOS

### 1. Helper de Logging (`app/Helpers/e2e_logging_helper.php`)
Proporciona funciones especializadas para diferentes tipos de logging:

- `e2e_log()` - Función principal de logging
- `e2e_log_controller()` - Para controladores
- `e2e_log_model()` - Para modelos y consultas BD
- `e2e_log_entity()` - Para entidades
- `e2e_log_ajax()` - Para llamadas AJAX
- `e2e_log_query()` - Para queries específicas
- `e2e_log_validation()` - Para validaciones
- `e2e_log_error()` - Para errores
- `e2e_log_performance()` - Para métricas de rendimiento
- `e2e_start_flow()` / `e2e_end_flow()` - Para marcar inicio/fin de flujos

### 2. Controladores Instrumentados
- **AdminClientesController** - Gestión de clientes
- **AdminVentasController** - Proceso de ventas
- **AdminFinanciamientoController** - Configuración financiera

### 3. Modelos Instrumentados
- **ClienteModel** - Operaciones de clientes
- **VentaModel** - Operaciones de ventas

## 📁 ARCHIVO DE LOG

**Ubicación**: `/writable/logs/end-to-end-clientes-flujo-ventas-YYYY-MM-DD.log`

### Formato de Entrada
```
[TIMESTAMP] [EVENTO] [COMPONENTE::MÉTODO] [FLUJO:nombre] [USER:id] [SESSION:id] {datos_json}
[DETALLES] Memory: uso | Peak: pico | IP: dirección
```

## 🔄 FLUJOS MONITOREADOS

### 1. **cliente-listado**
- Carga de vista principal de clientes
- Obtención de estadísticas
- Renderizado de DataTable

### 2. **cliente-datatable**
- Peticiones AJAX para datos de tabla
- Filtros aplicados
- Resultados obtenidos

### 3. **cliente-gestion**
- Operaciones CRUD de clientes
- Validaciones
- Actualizaciones de estado

### 4. **venta-creacion**
- Proceso completo de creación de venta
- Validación de datos
- Verificación de disponibilidad
- Inserción en BD
- Generación de documentos

### 5. **ventas-gestion**
- Listado de ventas
- Configuración de ventas
- Reportes

## 📝 INFORMACIÓN REGISTRADA

### Por cada evento se captura:
- **Timestamp** con microsegundos
- **Tipo de evento** (CONTROLLER, MODEL, AJAX, QUERY, etc.)
- **Componente** y método específico
- **Parámetros** de entrada y salida
- **Usuario** y sesión activa
- **Dirección IP** del cliente
- **Uso de memoria** y pico de memoria
- **Tiempo de ejecución** para operaciones críticas
- **Errores** detallados con contexto

### Para consultas SQL:
- Query completa ejecutada
- Parámetros vinculados
- Tiempo de ejecución en ms
- Hash de la query para identificación

### Para operaciones AJAX:
- Endpoint llamado
- Datos enviados
- Respuesta obtenida
- Tamaño de la respuesta

## 🎯 CASOS DE USO

### 1. **Depuración de Problemas**
```bash
# Ver errores en el flujo de ventas
grep "ERROR.*venta-creacion" /path/to/log

# Ver todas las operaciones de un usuario específico
grep "USER:123" /path/to/log

# Ver rendimiento de consultas lentas
grep "execution_time_ms.*[5-9][0-9][0-9]" /path/to/log
```

### 2. **Análisis de Rendimiento**
```bash
# Ver métricas de memoria
grep "PERFORMANCE" /path/to/log

# Ver flujos que fallan frecuentemente
grep "FLOW_END.*false" /path/to/log
```

### 3. **Auditoría de Seguridad**
```bash
# Ver intentos de acceso no autorizado
grep "acceso_denegado" /path/to/log

# Rastrear actividad de un usuario específico
grep "USER:456" /path/to/log | grep -E "(CONTROLLER|MODEL)"
```

## 🚀 EJEMPLO DE FLUJO COMPLETO

### Flujo: Usuario crea una nueva venta

1. **Inicio del flujo**
```
[2025-07-14 18:30:01] [FLOW] [FLOW_MANAGER::start] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"action":"FLOW_START","descripcion":"Proceso completo de creación de venta"}
```

2. **Controlador recibe petición**
```
[2025-07-14 18:30:01] [CONTROLLER] [AdminVentasController::store] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"method":"POST","uri":"/admin/ventas","parametros":{"lote_id":15,"cliente_id":8}}
```

3. **Validación de datos**
```
[2025-07-14 18:30:01] [VALIDATION] [AdminVentasController::validate] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"validation_rules":{"lote_id":"required|integer"},"is_valid":true}
```

4. **Verificación de disponibilidad**
```
[2025-07-14 18:30:01] [VERIFICATION] [AdminVentasController::verificar_disponibilidad_lote] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"lote_id":15,"disponible":true}
```

5. **Operación en modelo**
```
[2025-07-14 18:30:01] [MODEL] [VentaModel::save] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"parametros":{"folio_venta":"V-2025-001","lote_id":15}}
```

6. **Query ejecutada**
```
[2025-07-14 18:30:01] [QUERY] [DATABASE::execute] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"query":"INSERT INTO ventas (folio_venta, lote_id...) VALUES (?, ?...)","execution_time_ms":45.2}
```

7. **Resultado exitoso**
```
[2025-07-14 18:30:01] [DATABASE] [VentaModel::insert_successful] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"venta_id":123,"folio_venta":"V-2025-001"}
```

8. **Métricas de rendimiento**
```
[2025-07-14 18:30:01] [PERFORMANCE] [AdminVentasController::store] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"tiempo_ejecucion_ms":156.7,"memoria_final":"12.5 MB"}
```

9. **Fin del flujo**
```
[2025-07-14 18:30:01] [FLOW] [FLOW_MANAGER::end] [FLUJO:venta-creacion] [USER:5] [SESSION:abc123] {"action":"FLOW_END","exitoso":true,"resultados":{"venta_id":123}}
```

## 📊 HERRAMIENTAS DE ANÁLISIS

### Script de análisis básico:
```bash
#!/bin/bash
LOG_FILE="/path/to/end-to-end-clientes-flujo-ventas-$(date +%Y-%m-%d).log"

echo "=== RESUMEN DEL DÍA ==="
echo "Total de eventos: $(wc -l < $LOG_FILE)"
echo "Flujos iniciados: $(grep -c "FLOW_START" $LOG_FILE)"
echo "Flujos exitosos: $(grep -c "FLOW_END.*true" $LOG_FILE)"
echo "Flujos fallidos: $(grep -c "FLOW_END.*false" $LOG_FILE)"
echo "Errores total: $(grep -c "ERROR" $LOG_FILE)"
echo "Queries ejecutadas: $(grep -c "QUERY" $LOG_FILE)"
```

## ⚠️ CONSIDERACIONES

### Rendimiento
- Los logs se escriben de forma asíncrona para minimizar impacto
- Se incluyen métricas de memoria para detectar leaks
- Se mide tiempo de ejecución de operaciones críticas

### Seguridad
- No se registran contraseñas ni datos sensibles
- Se puede filtrar información sensible en producción
- Los logs se rotan diariamente

### Mantenimiento
- Los archivos de log se crean diariamente
- Se recomienda implementar rotación automática
- Establecer políticas de retención según necesidades

## 🔧 CONFIGURACIÓN ADICIONAL

### Para activar en producción:
1. Verificar permisos de escritura en `/writable/logs/`
2. Configurar rotación de logs
3. Establecer alertas para errores críticos
4. Implementar dashboard de monitoreo

### Para desarrollo:
- Todos los niveles de log están activos
- Se incluyen detalles completos de debug
- Se registran todas las operaciones

---

**Nota**: Este sistema está diseñado para proporcionar visibilidad completa del flujo cliente-ventas, facilitando la depuración, el análisis de rendimiento y la auditoría de operaciones.