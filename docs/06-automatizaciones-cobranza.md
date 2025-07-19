# 🤖 Automatizaciones de Cobranza

## Descripción General

Este documento describe los comandos automáticos implementados para el sistema de cobranza, diseñados para replicar la funcionalidad del sistema legacy y optimizar los procesos de gestión de pagos.

## Comandos Disponibles

### 1. Cancelación Automática de Apartados Vencidos

```bash
php spark cobranza:cancelar-apartados [opciones]
```

**Propósito:** Cancela automáticamente apartados que han excedido el tiempo límite configurado.

**Opciones:**
- `--dry-run`: Simula la ejecución sin realizar cambios
- `--proyecto=ID`: Procesa solo un proyecto específico
- `--force`: Fuerza la ejecución aunque ya se haya ejecutado hoy
- `--dias-extra=N`: Días adicionales de gracia antes de cancelar

**Ejemplos:**
```bash
# Simular cancelaciones
php spark cobranza:cancelar-apartados --dry-run

# Cancelar apartados del proyecto 1
php spark cobranza:cancelar-apartados --proyecto=1

# Dar 5 días extra de gracia
php spark cobranza:cancelar-apartados --dias-extra=5

# Forzar ejecución
php spark cobranza:cancelar-apartados --force
```

### 2. Cálculo Automático de Intereses Moratorios

```bash
php spark cobranza:calcular-intereses [opciones]
```

**Propósito:** Calcula automáticamente intereses moratorios en planes de pago vencidos.

**Opciones:**
- `--dry-run`: Simula el cálculo sin aplicar intereses
- `--proyecto=ID`: Procesa solo un proyecto específico
- `--dias-gracia=N`: Días de gracia antes de aplicar intereses (default: 5)
- `--force`: Fuerza el cálculo aunque ya se haya ejecutado hoy
- `--recalcular`: Recalcula todos los intereses del proyecto
- `--cliente=ID`: Procesa solo un cliente específico
- `--limite-monto=N`: Procesa solo planes con saldo mayor a N

**Ejemplos:**
```bash
# Simular cálculo de intereses
php spark cobranza:calcular-intereses --dry-run

# Calcular con 3 días de gracia
php spark cobranza:calcular-intereses --dias-gracia=3

# Recalcular todos los intereses del proyecto 1
php spark cobranza:calcular-intereses --recalcular --proyecto=1

# Procesar solo cliente específico con monto mínimo
php spark cobranza:calcular-intereses --cliente=123 --limite-monto=1000
```

### 3. Procesamiento Automático Completo

```bash
php spark cobranza:procesar-automatico [opciones]
```

**Propósito:** Ejecuta todos los procesos automáticos en el orden correcto: intereses primero, luego cancelaciones.

**Opciones:**
- `--dry-run`: Simula todo el procesamiento
- `--proyecto=ID`: Procesa solo un proyecto específico
- `--dias-gracia=N`: Días de gracia para intereses (default: 5)
- `--dias-extra=N`: Días extra para cancelaciones (default: 0)
- `--force`: Fuerza ejecución completa
- `--solo-intereses`: Ejecuta solo cálculo de intereses
- `--solo-cancelaciones`: Ejecuta solo cancelación de apartados

**Ejemplos:**
```bash
# Procesamiento completo en modo simulación
php spark cobranza:procesar-automatico --dry-run

# Procesamiento de proyecto específico
php spark cobranza:procesar-automatico --proyecto=1

# Solo calcular intereses
php spark cobranza:procesar-automatico --solo-intereses

# Configuración personalizada
php spark cobranza:procesar-automatico --dias-gracia=3 --dias-extra=2
```

## Configuración del Sistema

### Requisitos de Base de Datos

Los comandos requieren las siguientes configuraciones en la tabla `configuracion_cobranza`:

```sql
-- Configuración por proyecto
INSERT INTO configuracion_cobranza (
    proyectos_id,
    dias_cancelacion_apartado,
    cobrar_interes_moratorio,
    tasa_interes_moratorio,
    interes_compuesto,
    limite_interes_moratorio,
    activo
) VALUES (
    1,                    -- ID del proyecto
    15,                   -- Días para cancelar apartados
    TRUE,                 -- Cobrar interés moratorio
    36.0,                 -- 36% anual
    FALSE,                -- Interés simple
    50.0,                 -- Límite máximo 50%
    TRUE                  -- Configuración activa
);
```

### Programación Automática (Cron)

**Configuración recomendada para producción:**

```bash
# Editar crontab
crontab -e

# Agregar las siguientes líneas:

# Procesamiento automático diario a las 6:30 AM
30 6 * * * /usr/bin/php /ruta/al/proyecto/spark cobranza:procesar-automatico >> /var/log/cobranza_automatica.log 2>&1

# Cálculo de intereses diario a las 7:00 AM (respaldo)
0 7 * * * /usr/bin/php /ruta/al/proyecto/spark cobranza:calcular-intereses >> /var/log/intereses_automaticos.log 2>&1

# Cancelación de apartados diario a las 6:00 AM (respaldo)
0 6 * * * /usr/bin/php /ruta/al/proyecto/spark cobranza:cancelar-apartados >> /var/log/cancelaciones_automaticas.log 2>&1
```

**Para desarrollo/testing:**
```bash
# Ejecutar cada hora en horario laboral (8 AM - 6 PM)
0 8-18 * * * /usr/bin/php /ruta/al/proyecto/spark cobranza:procesar-automatico --dry-run
```

## Logs y Monitoreo

### Ubicación de Logs

Los comandos generan logs en:
- `writable/logs/cancelacion_automatica.log`
- `writable/logs/calculo_intereses.log`
- `writable/logs/cobranza_automatica.log`

### Formato de Logs

```
[2025-07-06 06:30:15] PROCESAMIENTO_AUTOMATICO_COMPLETADO - Intereses: EXITOSO, Cancelaciones: EXITOSO
[2025-07-06 07:00:22] CALCULO_COMPLETADO - Intereses calculados: 15, Errores: 0, Monto total: $12,500.50
[2025-07-06 06:00:10] EJECUCION_COMPLETADA - Cancelados: 3, Errores: 0
```

### Monitoreo Recomendado

1. **Verificación diaria de logs:**
```bash
# Verificar ejecuciones del día
grep "$(date +%Y-%m-%d)" /ruta/logs/cobranza_automatica.log

# Verificar errores
grep "ERROR" /ruta/logs/cobranza_automatica.log
```

2. **Alertas por email en caso de errores:**
```bash
# Script de monitoreo (guardar como monitor_cobranza.sh)
#!/bin/bash
LOG_FILE="/ruta/logs/cobranza_automatica.log"
TODAY=$(date +%Y-%m-%d)

if grep "$TODAY.*ERROR" "$LOG_FILE" > /dev/null; then
    echo "Error detectado en procesamiento automático de cobranza" | mail -s "Error Cobranza Automática" admin@empresa.com
fi
```

## Casos de Uso Específicos

### 1. Migración/Activación Inicial

```bash
# 1. Ejecutar en modo dry-run para verificar configuración
php spark cobranza:procesar-automatico --dry-run

# 2. Procesar proyecto piloto
php spark cobranza:procesar-automatico --proyecto=1

# 3. Activar para todos los proyectos
php spark cobranza:procesar-automatico
```

### 2. Recuperación después de Downtime

```bash
# Forzar ejecución completa
php spark cobranza:procesar-automatico --force

# Recalcular intereses de un proyecto específico
php spark cobranza:calcular-intereses --recalcular --proyecto=1
```

### 3. Procesamiento de Fin de Mes

```bash
# Ejecutar con parámetros más estrictos
php spark cobranza:procesar-automatico --dias-gracia=0 --dias-extra=0
```

### 4. Mantenimiento y Debugging

```bash
# Ver ayuda detallada
php spark cobranza:procesar-automatico --help

# Ejecutar solo una parte del proceso
php spark cobranza:procesar-automatico --solo-intereses --dry-run
```

## Consideraciones de Rendimiento

### Volumen de Datos
- **Menos de 1,000 planes:** Ejecución típica < 1 minuto
- **1,000 - 10,000 planes:** Ejecución típica 2-5 minutos  
- **Más de 10,000 planes:** Considerar fragmentación por proyecto

### Optimizaciones
- Usar `--proyecto=ID` para fragmentar el procesamiento
- Ejecutar en horarios de bajo tráfico
- Monitorear uso de memoria y CPU
- Configurar timeouts apropiados en el servidor

### Backup y Recuperación
- Realizar backup antes de ejecuciones masivas
- Mantener logs por al menos 30 días
- Implementar alertas de monitoreo
- Tener procedimiento de rollback documentado

## Resolución de Problemas

### Errores Comunes

1. **"Ya se ejecutó hoy"**
   - Solución: Usar `--force` si es necesario ejecutar nuevamente

2. **"No hay configuración de cobranza"**
   - Verificar tabla `configuracion_cobranza`
   - Asegurar que `activo = TRUE` y `cobrar_interes_moratorio = TRUE`

3. **"Error en transacción"**
   - Verificar permisos de base de datos
   - Revisar logs de base de datos
   - Verificar espacio disponible

4. **Memoria insuficiente**
   - Aumentar `memory_limit` en PHP
   - Fragmentar por proyecto
   - Optimizar consultas de base de datos

### Debugging
```bash
# Activar modo verbose en CodeIgniter
export CI_ENVIRONMENT=development

# Ejecutar con más información
php spark cobranza:procesar-automatico --dry-run --proyecto=1

# Revisar logs de CodeIgniter
tail -f writable/logs/log-$(date +%Y-%m-%d).php
```

## Integración con Sistema Legacy

Los comandos están diseñados para mantener compatibilidad con el sistema legacy:

- **Campos de auditoría:** Todos los cambios incluyen usuario, IP, fecha/hora
- **Estados consistentes:** Los estados de ventas y lotes se mantienen sincronizados
- **Lógica de negocio:** Se replica exactamente la lógica del sistema original
- **Logs de actividad:** Se mantiene trazabilidad completa de todas las operaciones

## Seguridad

### Permisos Requeridos
- Lectura/escritura en tablas de ventas, planes de pago, lotes
- Permisos de escritura en directorio `writable/logs/`
- Acceso a configuraciones de cobranza

### Auditoría
- Todos los cambios quedan registrados con usuario y timestamp
- Los logs incluyen detalles completos de cada operación
- Se mantiene historial en tablas de auditoría

### Validaciones
- Verificación de estados antes de modificar
- Validación de configuraciones de cobranza
- Prevención de ejecuciones duplicadas
- Rollback automático en caso de errores