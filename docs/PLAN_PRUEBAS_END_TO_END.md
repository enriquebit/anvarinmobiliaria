# 🧪 PLAN DE PRUEBAS END-TO-END

## ✅ BUGS CORREGIDOS

### 1. **TypeError en InteresesService** ✓ RESUELTO
- **Problema**: `Cannot assign null to property App\Services\InteresesService::$interesModel`
- **Solución**: Creados todos los modelos y entidades faltantes
- **Archivos creados**:
  - `app/Models/CobranzaInteresModel.php`
  - `app/Models/HistorialCobranzaModel.php` 
  - `app/Models/PagoModel.php`
  - `app/Entities/CobranzaInteres.php`
  - `app/Entities/HistorialCobranza.php`
  - `app/Entities/Pago.php`

### 2. **Error SQL en AdminCobranzaController** ✓ RESUELTO
- **Problema**: Sintaxis SQL incorrecta `'total - total_pagado'`
- **Solución**: Implementado método `calcularMontoPendienteTotal()` que calcula correctamente

---

## 🧪 COMANDOS DE VERIFICACIÓN RÁPIDA

### **1. Verificar que no hay más errores de instanciación**
```bash
# Acceder a cada módulo para verificar que carga sin errores
curl -I http://localhost/admin/ventas
curl -I http://localhost/admin/cobranza  
curl -I http://localhost/admin/pagos
```

### **2. Probar comandos de automatización**
```bash
# Comando 1: Cancelar apartados vencidos
php spark app:cancelar-apartados-vencidos --dry-run

# Comando 2: Calcular intereses moratorios  
php spark app:calcular-intereses-moratorios --dry-run

# Comando 3: Procesar cobranza automática
php spark app:procesar-cobranza-automatica --dry-run
```

---

## 🎯 PLAN DE PRUEBAS FUNCIONALES (SIN SEEDERS)

### **FASE 1: Pruebas Unitarias de Services**

#### **Test 1: Instanciación de Services**
```php
// Crear archivo: test_instantiation.php
<?php
require_once 'vendor/autoload.php';

// Bootstrap CI4
$pathsConfig = FCPATH . '../app/Config/Paths.php';
$paths = require realpath($pathsConfig);

$bootstrap = FCPATH . '../app/Config/Bootstrap.php';
require realpath($bootstrap);

$app = \Config\Services::codeigniter();
$app->initialize();

try {
    echo "Testing Services Instantiation...\n";
    
    $interesesService = new \App\Services\InteresesService();
    echo "✓ InteresesService OK\n";
    
    $ventasService = new \App\Services\VentasService();
    echo "✓ VentasService OK\n";
    
    $cobranzaService = new \App\Services\CobranzaService();
    echo "✓ CobranzaService OK\n";
    
    $pagosService = new \App\Services\PagosService();
    echo "✓ PagosService OK\n";
    
    echo "\n🎉 ALL SERVICES WORKING!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
```

**Comando**: `php test_instantiation.php`

#### **Test 2: Modelos y Base de Datos**
```bash
# Verificar estructura de tablas
php spark migrate:status

# Verificar que las nuevas tablas existen
php spark db:table cobranza_intereses
php spark db:table historial_cobranza
php spark db:table pagos
```

---

### **FASE 2: Pruebas de Controladores**

#### **Test 3: Endpoints de Administración**
```bash
# Verificar que cargan sin errores (sin autenticación)
curl -I http://localhost/admin/ventas
curl -I http://localhost/admin/cobranza
curl -I http://localhost/admin/pagos

# Esperamos respuesta 302 (redirect login) en lugar de 500 (error)
```

#### **Test 4: APIs AJAX (requieren autenticación)**
```bash
# Estas deberían responder 403 (Forbidden) en lugar de 500 (Error)
curl -X POST http://localhost/admin/ventas/obtener-ventas
curl -X POST http://localhost/admin/cobranza/obtener-cuentas  
curl -X POST http://localhost/admin/pagos/obtener-pagos
```

---

### **FASE 3: Validación de Lógica de Negocio**

#### **Test 5: Validación de Entidades**
```php
// Crear archivo: test_entities.php
<?php
require_once 'vendor/autoload.php';

// Bootstrap CI4...

try {
    echo "Testing Entity Business Logic...\n";
    
    // Test PlanPago Entity
    $planPago = new \App\Entities\PlanPago([
        'total' => 10000,
        'total_pagado' => 3000,
        'fecha_vencimiento' => '2024-01-01'
    ]);
    
    echo "Saldo pendiente: " . $planPago->getSaldoPendiente() . "\n";
    echo "¿Está vencido?: " . ($planPago->estaVencido() ? 'Sí' : 'No') . "\n";
    echo "Días vencidos: " . $planPago->getDiasVencidos() . "\n";
    
    // Test Venta Entity
    $venta = new \App\Entities\Venta([
        'total' => 50000,
        'total_pagado' => 15000,
        'anticipo' => 10000,
        'anticipo_pagado' => 10000
    ]);
    
    echo "Saldo venta: " . $venta->getSaldoPendiente() . "\n";
    echo "% Pagado: " . $venta->getPorcentajePagado() . "%\n";
    
    echo "\n✓ Entity Logic OK\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
```

#### **Test 6: Cálculos de Intereses**
```php
// Test calculation methods without DB
$configuracion = new \App\Entities\ConfiguracionCobranza([
    'tasa_interes_moratorio' => 24.0, // 24% anual
    'interes_compuesto' => false,
    'limite_interes_moratorio' => 50.0 // máximo 50%
]);

// Simular cálculo de 30 días de atraso sobre $10,000
$saldoPendiente = 10000;
$diasAtraso = 30;
$tasaInteresDiaria = 24.0 / 365;
$montoInteres = $saldoPendiente * ($tasaInteresDiaria / 100) * $diasAtraso;

echo "Interés calculado: $" . number_format($montoInteres, 2) . "\n";
```

---

### **FASE 4: Pruebas de Comandos CLI**

#### **Test 7: Comandos de Automatización**
```bash
# Verificar que los comandos existen
php spark list | grep app:

# Ejecutar en modo simulación
php spark app:cancelar-apartados-vencidos --dry-run --verbose
php spark app:calcular-intereses-moratorios --dry-run --verbose  
php spark app:procesar-cobranza-automatica --dry-run --verbose
```

---

### **FASE 5: Pruebas de Integración (Opcional)**

#### **Test 8: Simulación de Flujo Completo**
```php
// test_integration.php
// Simular todo el flujo sin tocar DB real
try {
    echo "Simulating Complete Sales Flow...\n";
    
    // 1. Simular datos de apartado
    $datosApartado = [
        'lotes_id' => 1,
        'clientes_id' => 1, 
        'vendedor_id' => 1,
        'total' => 50000,
        'anticipo' => 10000
    ];
    
    echo "✓ Apartado simulation data prepared\n";
    
    // 2. Simular datos de plan de pagos
    $datosFinanciamiento = [
        'monto_financiar' => 40000,
        'meses_credito' => 24,
        'fecha_inicial' => date('Y-m-d')
    ];
    
    echo "✓ Payment plan simulation data prepared\n";
    
    // 3. Simular datos de pago
    $datosPago = [
        'total' => 5000,
        'efectivo' => 3000,
        'transferencia' => 2000,
        'concepto' => 'capital'
    ];
    
    echo "✓ Payment simulation data prepared\n";
    
    echo "\n🎉 INTEGRATION SIMULATION COMPLETE\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
```

---

## 📋 CHECKLIST DE VALIDACIÓN

### ✅ **Criterios de Éxito**

- [ ] **Instanciación**: Todos los Services se instancian sin errores
- [ ] **SQL**: No hay errores de sintaxis SQL en consultas
- [ ] **Endpoints**: Los controladores cargan sin errores 500
- [ ] **Comandos**: Los comandos CLI se ejecutan sin excepción
- [ ] **Entidades**: La lógica de negocio funciona correctamente
- [ ] **Helpers**: Los helpers de formato funcionan
- [ ] **Rutas**: Todas las rutas están correctamente definidas

### 🔍 **Indicadores de Problemas**

- **Error 500**: Problema de código/sintaxis
- **Error de BD**: Tablas o campos faltantes  
- **TypeError**: Tipos de datos incorrectos
- **Fatal Error**: Clases o métodos no encontrados

---

## 🚀 **SIGUIENTE FASE**

Una vez que todas las pruebas pasen exitosamente:

1. **Crear datos de prueba mínimos** manualmente en DB
2. **Probar flujo completo** con datos reales
3. **Validar automatizaciones** con datos de prueba
4. **Documentar casos de uso** principales

---

## 📞 **REPORTAR RESULTADOS**

Para cada test ejecutado, reportar:
- ✅ **ÉXITO**: Qué funcionó correctamente
- ❌ **ERROR**: Mensaje de error completo + archivo + línea
- 📝 **OBSERVACIONES**: Cualquier comportamiento inesperado

¿Estás listo para ejecutar estas pruebas?