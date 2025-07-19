# 📋 Refactorización: Leads vs Clientes

## 🎯 Objetivo
Diferenciar claramente entre:
- **Leads/Prospectos**: Personas que se registran pero aún no son clientes oficiales
- **Clientes**: Personas aprobadas con contrato firmado

## 📁 Estructura Actual vs Propuesta

### **Actual (Confuso)**
```
public/registro-clientes/     → Formulario público de registro
admin/registro-clientes/      → Gestión de registros (leads)
admin/clientes/              → Gestión de clientes oficiales
```

### **Propuesta (Clara)**
```
public/registro-clientes/     → Formulario público (sin cambios)
admin/leads/                 → Gestión de leads/prospectos
admin/clientes/              → Gestión de clientes oficiales
```

## 🔄 Plan de Refactorización

### **Fase 1: Renombrar Rutas Administrativas**
- `admin/registro-clientes/*` → `admin/leads/*`
- Mantener `public/registro-clientes` sin cambios (es la entrada pública)

### **Fase 2: Renombrar Controladores**
- `AdminRegistroClientesController` → `AdminLeadsController`
- Mantener `RegistroClientesController` (público) sin cambios

### **Fase 3: Actualizar Vistas**
- Carpeta `admin/registro-clientes/` → `admin/leads/`
- Actualizar referencias en las vistas

### **Fase 4: Ajustar Modelos y Entidades**
- `RegistroClientesModel` → `LeadModel`
- `RegistroCliente` (Entity) → `Lead` (Entity)
- Tabla `registro_clientes` → mantener nombre por ahora (evitar migración DB)

## 📊 Diferencias Clave

### **Lead/Prospecto**
- Estado: Pre-registro, en revisión, aprobado, rechazado
- Documentos: INE, comprobante domicilio (pendientes de verificación)
- Sin acceso al sistema
- Sin contrato firmado
- Carpeta en Google Drive: `ANVAR_Clientes/[RFC]/`

### **Cliente**
- Estado: Activo, inactivo, suspendido
- Documentos verificados + contrato firmado
- Acceso al portal de clientes
- Historial de pagos y propiedades
- Carpeta en Google Drive: Misma estructura

## 🔗 Flujo de Conversión

```
1. Persona llena formulario → Se crea LEAD
2. Admin revisa documentos → Lead EN_REVISION
3. Documentos aprobados → Lead APROBADO
4. Se envía contrato → Lead CONTRATO_ENVIADO
5. Firma contrato → Se crea CLIENTE (lead → cliente)
6. Lead cambia a estado CONVERTIDO
```

## 📝 Nomenclatura de Archivos

### **Documentos de Leads**
```
ANVAR_Clientes/
└── [RFC]/
    ├── INE_FRENTE_[RFC].jpg
    ├── INE_REVERSO_[RFC].jpg
    └── COMPROBANTE_DOMICILIO_[RFC].pdf
```

### **Documentos de Clientes (adicionales)**
```
ANVAR_Clientes/
└── [RFC]/
    ├── (documentos anteriores...)
    ├── CONTRATO_[RFC]_[FECHA].pdf
    ├── PAGARE_[RFC]_[FECHA].pdf
    └── ANEXOS/
```

## ✅ Beneficios de la Refactorización

1. **Claridad**: No hay confusión entre leads y clientes
2. **Escalabilidad**: Fácil agregar nuevos estados y flujos
3. **Seguridad**: Diferentes permisos para leads vs clientes
4. **Reportes**: Métricas separadas para conversión
5. **Mantenimiento**: Código más organizado y predecible