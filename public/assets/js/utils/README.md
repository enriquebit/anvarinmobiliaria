# ANVAR Utilities - Sistema de Utilidades JavaScript

## 📁 Estructura Organizada

El sistema de utilidades está organizado en módulos especializados para facilitar el mantenimiento y escalabilidad:

```
/assets/js/
├── anvar-utils.js          # Archivo principal (carga todos los módulos)
└── utils/
    ├── format.js           # Formateo de datos
    ├── validation.js       # Validaciones
    ├── ui.js              # Interfaz de usuario
    ├── data.js            # Manipulación de datos
    └── README.md          # Esta documentación
```

## 🚀 Uso Básico

### Incluir en las vistas

```html
<script src="<?= base_url('/assets/js/anvar-utils.js') ?>?v=<?= time() ?>"></script>
```

### Verificar que las utilidades estén cargadas

```javascript
// Opción 1: Event listener
window.addEventListener('anvarUtilsLoaded', function(e) {
    console.log('Utilidades ANVAR cargadas, versión:', e.detail.version);
    // Tu código aquí
});

// Opción 2: Callback
window.onAnvarUtilsLoaded = function() {
    console.log('Utilidades ANVAR listas');
    // Tu código aquí
};

// Opción 3: Verificación manual
function verificarUtilidades() {
    if (typeof formatearMoneda !== 'undefined') {
        // Utilidades listas
    } else {
        setTimeout(verificarUtilidades, 100);
    }
}
```

## 📋 Funciones Disponibles

### 💰 Formateo (format.js)

```javascript
// Formatear moneda (FUNCIÓN PRINCIPAL)
formatearMoneda(53550)          // "$53,550.00"
formatearMoneda(0)              // "$0.00"

// Formatear números
formatearNumero(1234.567, 2)    // "1,234.57"
formatearNumero(1234.567, 0)    // "1,235"

// Formatear porcentajes  
formatearPorcentaje(15.5)       // "15.50%"

// Formatear área
formatearArea(150.25)           // "150.25 m²"

// Formatear fechas
formatearFecha('2024-12-31')    // "31/12/2024"
formatearFecha('2024-12-31', true) // "31/12/2024 00:00"

// Formatear folios
formatearFolio(123, 6, 'APT')   // "APT-000123"

// Aliases para compatibilidad
formatPrecio(53550)             // "$53,550.00"
formatCurrency(53550)           // "$53,550.00"
```

### ✅ Validaciones (validation.js)

```javascript
// Validaciones básicas
esNumeroValido('123.45')        // true
esEmailValido('test@email.com') // true
esTelefonoValido('5551234567')  // true
esRequerido('')                 // false

// Validaciones mexicanas
esCurpValido('XEXX010101HNEXXXA4') // true
esRfcValido('XAXX010101000')       // true

// Validaciones de longitud
longitudMinima('texto', 5)      // true
longitudMaxima('texto', 10)     // true
enRango(15, 10, 20)            // true
```

### 🎨 Interfaz (ui.js)

```javascript
// Notificaciones
mostrarToast('¡Guardado exitoso!', 'success', 3000);
mostrarToast('Error al guardar', 'error');

// Confirmaciones
mostrarConfirmacion(
    '¿Eliminar registro?',
    'Esta acción no se puede deshacer',
    function() {
        // Acción a ejecutar
    }
);

// Loading states
mostrarLoading('#contenedor', 'Cargando...');
ocultarLoading('#contenedor');

// Utilidades de texto
capitalizarTexto('juan pérez')  // "Juan Pérez"
truncarTexto('Texto largo...', 10) // "Texto lar..."

// Copiar al portapapeles
copiarAlPortapapeles('texto', function() {
    mostrarToast('Copiado al portapapeles', 'success');
});

// Debounce para optimizar eventos
const busquedaDebounced = debounce(function(termino) {
    // Realizar búsqueda
}, 500);
```

### 📊 Manipulación de Datos (data.js)

```javascript
// Limpiar formatos
limpiarMoneda('$1,234.56')      // 1234.56
limpiarNumero('1,234.56')       // 1234.56

// Trabajar con arrays
const datos = [
    {nombre: 'Juan', edad: 25, salario: 50000},
    {nombre: 'Ana', edad: 30, salario: 60000}
];

buscarEnArray(datos, 'nombre', 'Juan')  // {objeto Juan}
filtrarArray(datos, 'edad', 25)         // [objeto Juan]
ordenarArray(datos, 'salario', 'desc')  // Ordenado por salario

// Cálculos
sumarCampo(datos, 'salario')      // 110000
promediarCampo(datos, 'edad')     // 27.5
maximoCampo(datos, 'salario')     // 60000

// Utilidades
generarUUID()                     // "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx"
```

## 🔄 Migración desde Código Existente

### Antes (código disperso)
```javascript
// En cada vista, definición diferente
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}
```

### Después (función única)
```javascript
// Una sola función en todo el proyecto
formatearMoneda(amount)  // Siempre disponible y consistente
```

## 🛠️ Desarrollo y Mantenimiento

### Agregar nueva función
1. Identificar la categoría apropiada (format, validation, ui, data)
2. Agregar la función al archivo correspondiente
3. Exportar como `window.nombreFuncion = nombreFuncion`
4. Documentar en este README

### Crear nueva categoría
1. Crear nuevo archivo en `/utils/categoria.js`
2. Agregar a la lista en `anvar-utils.js`
3. Documentar funciones aquí

## 🐛 Debugging

```javascript
// Verificar estado del sistema
console.log(window.ANVAR_UTILS);
// {version: "2.0.0", loaded: true, modules: ["format", "validation", "ui", "data"]}

// Ver funciones disponibles
console.log(Object.keys(window).filter(key => key.includes('formatear')));
```

## ⚡ Características

- ✅ Carga asíncrona de módulos
- ✅ Funciones de emergencia en caso de fallo
- ✅ Compatibilidad con código existente
- ✅ Sistema de eventos personalizado
- ✅ Versionado y logging
- ✅ Modular y escalable
- ✅ Cache busting automático

## 📈 Próximas Funcionalidades

- [ ] Módulo de matemáticas financieras
- [ ] Módulo de manejo de archivos
- [ ] Módulo de gráficos y reportes
- [ ] Módulo de exportación (PDF, Excel)
- [ ] Módulo de comunicación con APIs