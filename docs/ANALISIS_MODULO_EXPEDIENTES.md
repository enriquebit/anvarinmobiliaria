# 📋 ANÁLISIS DEL MÓDULO DE EXPEDIENTES

## 🔍 **FUNCIONALIDAD PRINCIPAL**

El módulo de expedientes es un **visor completo del historial y estado de ventas/apartados** que permite:

### 📊 **Características del Módulo Legacy**

1. **Búsqueda y Filtrado Avanzado**:
   - Por fechas (con estados predefinidos en `tb_fechas_estados`)
   - Por referencia/folio interno
   - Por estado de pago (Pagado/No Pagado)
   - Por cliente específico
   - Por vendedor
   - Por lote

2. **Vista de Expediente Individual** (`ver_expediente.php`):
   - Información completa de la venta/apartado
   - Historial de pagos y cobranza
   - Cálculo de intereses moratorios
   - Estado actual del expediente
   - Documentos asociados (contratos, avisos de privacidad)

### 🗃️ **Tablas Principales Utilizadas**

- **`tb_ventas`** - Tabla principal de ventas/apartados
- **`tb_cobranza`** - Plan de pagos mensual
- **`tb_fechas_estados`** - Estados predefinidos para filtros
- **`tb_clientes`** - Información de clientes
- **`tb_lotes`** - Información de lotes
- **`tb_usuarios`** - Vendedores y usuarios

### 🔧 **Funciones Clave del Sistema Legacy**

1. **Cálculo automático de intereses moratorios**
2. **Cancelación automática de apartados vencidos** 
3. **Generación de folios internos**
4. **Gestión de documentos (contratos, avisos)**
5. **Historial completo de movimientos**

---

## 🚀 **PLAN DE MIGRACIÓN A CODEIGNITER 4**

### **COMPONENTES A CREAR:**

1. **Controller**: `AdminExpedientesController`
2. **Views**: 
   - `expedientes/index.php` (listado principal)
   - `expedientes/show.php` (vista detallada)
3. **Service**: `ExpedientesService` (lógica de negocio)
4. **Routes**: Integración en el sistema de rutas

### **FUNCIONALIDADES A MIGRAR:**

✅ **Búsqueda y filtrado avanzado**
✅ **Vista detallada de expedientes**  
✅ **Integración con sistema de pagos**
✅ **Cálculo de intereses**
✅ **Generación de reportes**
✅ **Gestión de documentos**

---

## 🎯 **ESTRUCTURA DE DATOS EXPEDIENTES**

### **Campo Principal: `tb_ventas`**
```sql
- IdVenta (PK)
- Folio, FolioInterno  
- Total, TotalPagado
- Cliente, NCliente, Cliente2, NCliente2
- Vendedor, NVendedor
- Proyecto, NProyecto, Empresa, NEmpresa
- Lote, NLote, Manzana, NManzana
- Estado (1=Apartado, 2=Vendido), Estatus (1=Activo, 2=Cancelado)
- Fecha, Hora
- Anticipo, AnticipoPagado, AnticipoCredito, AnticipoCobrado
- CobrarInteres, Intereses
- Observaciones
```

### **Integración con Sistema Actual CI4:**
- Se integra perfectamente con las **Entities** ya creadas (`Venta`, `PlanPago`, `Cliente`)
- Utiliza los **Services** existentes (`VentasService`, `CobranzaService`, `PagosService`)
- Compatible con el sistema de **permisos y autenticación** actual

---

## ✨ **MEJORAS EN LA MIGRACIÓN**

1. **UI/UX Moderna** con AdminLTE3
2. **AJAX DataTables** para mejor performance
3. **Responsive Design** 
4. **Integración completa** con el sistema de pagos CI4
5. **API REST** para integraciones futuras
6. **Filtros avanzados** mejorados
7. **Exportación** a Excel/PDF
8. **Sistema de notificaciones** integrado