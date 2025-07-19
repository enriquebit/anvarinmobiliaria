# 📋 Propiedades Personalizadas para HubSpot

## 🚨 Problema Actual
HubSpot está rechazando las propiedades personalizadas porque no existen en el sistema:
```
Property "numero_casa_depto" does not exist
```

## ✅ Solución Temporal Implementada
Se modificó `HubSpotService.php` para:
1. **Solo enviar propiedades estándar** (firstname, lastname, email, mobilephone, phone)
2. **Guardar información adicional en notas** del contacto
3. **Comentar propiedades personalizadas** hasta que se creen en HubSpot

## 📝 Propiedades Personalizadas a Crear en HubSpot

### Instrucciones:
1. Ir a HubSpot > Settings > Properties > Contact Properties
2. Crear las siguientes propiedades personalizadas:

### 1. **apellido_materno**
- **Label**: Apellido Materno
- **Field Type**: Single-line text
- **Internal Name**: `apellido_materno`
- **Group**: Contact information

### 2. **medio_de_contacto**
- **Label**: Medio de Contacto Preferido
- **Field Type**: Dropdown select
- **Internal Name**: `medio_de_contacto`
- **Options**:
  - `whatsapp` → WhatsApp
  - `telefono` → Llamada telefónica
- **Group**: Contact information

### 3. **desarrollo**
- **Label**: Desarrollo de Interés
- **Field Type**: Dropdown select
- **Internal Name**: `desarrollo`
- **Options**:
  - `valle_natura` → Valle Natura
  - `cordelia` → Cordelia
- **Group**: Sales information

### 4. **manzana**
- **Label**: Manzana
- **Field Type**: Single-line text
- **Internal Name**: `manzana`
- **Group**: Sales information

### 5. **lote**
- **Label**: Lote
- **Field Type**: Single-line text
- **Internal Name**: `lote`
- **Group**: Sales information

### 6. **numero_casa_depto**
- **Label**: Número Casa/Departamento
- **Field Type**: Single-line text
- **Internal Name**: `numero_casa_depto`
- **Group**: Sales information

### 7. **nombre_copropietario**
- **Label**: Nombre del Co-propietario
- **Field Type**: Single-line text
- **Internal Name**: `nombre_copropietario`
- **Group**: Sales information

### 8. **parentesco_copropietario**
- **Label**: Parentesco del Co-propietario
- **Field Type**: Dropdown select
- **Internal Name**: `parentesco_copropietario`
- **Options**:
  - `conyuge` → Cónyuge
  - `hijo` → Hijo/a
  - `padre` → Padre/Madre
  - `hermano` → Hermano/a
  - `otro` → Otro
- **Group**: Sales information

### 9. **agente**
- **Label**: Agente Referido
- **Field Type**: Single-line text
- **Internal Name**: `agente`
- **Group**: Sales information

## 🔧 Activar Propiedades en el Código

Una vez creadas las propiedades en HubSpot, editar `/app/Libraries/HubSpotService.php`:

1. En el método `prepararPropiedadesContacto()`, descomentar las líneas 291-299:
```php
$propiedades['apellido_materno'] = $datosCliente['apellido_materno'] ?? '';
$propiedades['medio_de_contacto'] = $datosCliente['medio_de_contacto'] ?? '';
$propiedades['desarrollo'] = $datosCliente['desarrollo'];
$propiedades['manzana'] = $datosCliente['manzana'] ?? '';
$propiedades['lote'] = $datosCliente['lote'] ?? '';
$propiedades['numero_casa_depto'] = $datosCliente['numero_casa_depto'] ?? '';
$propiedades['nombre_copropietario'] = $datosCliente['nombre_copropietario'] ?? '';
$propiedades['parentesco_copropietario'] = $datosCliente['parentesco_copropietario'] ?? '';
```

2. Descomentar línea 303:
```php
$propiedades['agente'] = $datosCliente['agente_referido'] ?? '';
```

3. Opcionalmente, remover o ajustar el método `generarNotasContacto()` si ya no es necesario.

## 📊 Mientras Tanto

La información se está guardando en el campo de notas del contacto con formato:
```
Apellido Materno: López
Desarrollo de interés: Valle Natura
Ubicación: Manzana 1, Lote 3
Medio de contacto preferido: WhatsApp
Co-propietario: Juan Pérez (Parentesco: conyuge)
Agente referido: 123
Fecha de registro: 02/07/2025 13:45:00
Fuente: Formulario web de registro
```

## ⚡ Beneficios de Crear las Propiedades

1. **Mejor segmentación** en HubSpot
2. **Reportes más detallados** por desarrollo, manzana, lote
3. **Automatizaciones** basadas en medio de contacto
4. **Asignación automática** por agente referido
5. **Filtros avanzados** en listas y vistas

## 🔍 Verificación

Para verificar que las propiedades funcionan:
1. Crear una propiedad de prueba primero
2. Probar con un registro
3. Si funciona, crear todas las demás
4. Descomentar el código
5. Probar registro completo