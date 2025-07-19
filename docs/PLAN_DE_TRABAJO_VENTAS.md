Perfecto, con esta información ahora puedo crear un prompt detallado para Claude Code. Aquí está:

## Prompt para Claude Code - Sistema CRM Inmobiliario: Módulo de Ventas
## Metodología de Trabajo "Entity-First", "MVP", "DRY" ##
### Revisa si exíste siempre código relacionado o servible para la implementación ###
### Debemos ejecutar scripts .sql para la implementacióny actualziación de BBDD, se encuentra desincronizado la tabla "Migrations" de CodeIgniter4 por lo cual no es posible realizar migraciones###
### Contexto del Proyecto

Estamos desarrollando un sistema CRM inmobiliario en CodeIgniter/PHP con MySQL. El módulo principal es **Ventas** se pretende REFACTORIZAR y debe integrarse con otros módulos como Cobranza, Proyectos,Comisiones,   Esquemas de Financiamiento y Expedientes
### Flujo Completo de Venta
SE VA A MOVER TODO EL BLOQUE DE "CONFIGURACIONN FINANCIERA, CONFIGURACION DE COMISI{ON, FINANCIEMIENTO Y POLITICAS, RESUMEN Y CONFIGURACION} AJUSTE DE PROCENTAJES Y COMISIONES "admin/empresas/" hacia "admin/proyectos" y aquí se pretende consumir porque creemos que es más conveniente por que cada proyecto es variable en cuanto a su CONFIGURACI{ON FINANCIERA} o bien también podría colocarlo en el nuevo modulo "esquemas de financiemiento" por que va mas relacionadi sin embargo te lo dejo a tu criterio como experto


#### 1. **Inicio de Venta**
- Seleccionar lote disponible (validar estado: disponible/apartado/vendido)
- Seleccionar o crear cliente
- Asignar vendedor
- Definir esquema de financiamiento

#### 2. **Esquemas de Financiamiento (3 tipos)**

**A) Cero Enganche**
- Sin pago inicial
- Primera mensualidad se cobra el mismo día de la venta
- Financiamiento a 60 meses sin intereses
- La fecha de inicio de la mensualidades es el días de pago, paga su primer mensualidad desde que se hace el primer pago (primer mensualidad)
- Comisión: 2 primeras mensualidades para el vendedor

**B) Tradicional (Con Enganche)**
- Requiere anticipo/enganche según el terreno:
  - Terrenos ≤180 m²: Monto fijo en pesos (definido en módulo empresa)
  - Terrenos >180 m²: Porcentaje del valor total (definido en módulo empresa)
- Plazo: múltiplos de 3 (3, 6, 9, 12... hasta 60 meses)
- Sin intereses
- Comisión fija: $10,000 para terrenos ≤180 m²

**C) Comercial**
- Para lotes comerciales
- Enganche: 12% del valor total
- Comisión: 12% del valor de venta
- Financiamiento hasta 60 meses sin intereses

#### 3. **Cálculo de Comisiones**

```php
// Pseudocódigo para el cálculo
if ($esquema == 'cero_enganche') {
    $comision = $mensualidad * 2; // 2 primeras mensualidades
} elseif ($terreno->superficie <= 180 && $esquema == 'tradicional') {
    $comision = 10000; // Fija
} elseif ($terreno->superficie > 180 || $terreno->tipo == 'comercial') {
    $porcentaje = ($terreno->tipo == 'comercial') ? 0.12 //Variables Dinámicas: 0.07//Variables  Dinámicas dependiendo el esquema de financiamiento;
    $comision = $precio_total * $porcentaje;
}
```

#### 4. **Pagos Adicionales**
- Son pagos programados (no inmediatos)
- Se definen durante la venta como acuerdos especiales
- Pueden ser:
  - Anualidades
  - Abonos a capital
  - Adelanto de mensualidades
- Se integran a la tabla de amortización

#### 5. **Generación de Documentos**

Al confirmar la venta, generar automáticamente:

1. **Contrato de compraventa**
2. **Tabla de amortización** (con pagos adicionales si aplica)
3. **Carátula del cliente**
4. **Datos bancarios para pagos**
5. **Mapa de lotificación** (pendiente: requiere coordenadas poligonales)
6. **Estado de cuenta inicial**
7. **Requerimientos especiales** (si aplica)
8. **Memorándum**
9. **Aviso de privacidad**
10. **Acuerdo de servicios** (pendiente de implementar)

#### 6. **Gestión del Expediente**

Estructura de carpetas:
```
/clientes/
  /{RFC_o_CURP_o_ClienteID}/
    /documentos_venta/
    /documentos_personales/
    /comprobantes_pago/
```

#### 7. **Estados del Lote**
- Disponible → Apartado (con pago de apartado) → Vendido (con enganche/primera mensualidad)
- En caso de cancelación: Vendido → Disponible (con registro histórico)

#### 8. **Integración con Otros Módulos**

**Cobranza:**
- Generar calendario de pagos automáticamente
- Primer pago según esquema (inmediato para cero enganche, mes siguiente para tradicional)

**Reestructuración:**
- Para pagos adicionales post-venta
- Para cambios de esquema de financiamiento
- Para manejo de atrasos

### Funcionalidades Adicionales Requeridas

1. **Módulo de Esquemas de Financiamiento**
   - CRUD de esquemas predefinidos
   - Parámetros configurables por empresa/proyecto
   - Cálculo automático de montos y plazos

2. **Validaciones Importantes**
   - No permitir venta de lotes no disponibles
   - Editar montos mínimos de anticipos
   - Verificar que el folio de venta sea único
   - En cero enganche: validar ajuste de fecha para cobro inmediato

3. **Reportes Necesarios**
   - Ventas por periodo
   - Comisiones por vendedor
   - Estado de cartera
   - Lotes disponibles/vendidos por etapa

### Consideraciones Técnicas

- Usar transacciones MySQL para garantizar integridad
- Implementar logs de auditoría para cambios en ventas
- API REST para integración con módulos
- Manejo de archivos PDF con TCPDF o similar
- Sistema de plantillas para documentos personalizables

### Prioridades de Desarrollo

1. Completar flujo básico de venta con los 3 esquemas
2. Integración con tabla de amortización dinámica
3. Generación automática de documentos
4. Módulo de esquemas de financiamiento configurable
5. Sistema de reestructuración de créditos
6. Implementación de mapas con coordenadas poligonales