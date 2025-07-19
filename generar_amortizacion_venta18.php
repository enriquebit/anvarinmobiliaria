<?php

// Script para generar tabla de amortización para venta 18

// Cargar el framework CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require_once 'app/Config/Paths.php';

$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';
require_once $paths->appDirectory . '/Config/Autoload.php';
require_once $paths->appDirectory . '/Config/Services.php';

$bootstrap = \Config\Services::codeigniter();
$bootstrap->initialize();

// Cargar helper de amortización
helper('amortizacion');

// Configuración financiera para venta 18
$configFinanciera = [
    'monto_financiar' => 208800.00,
    'tasa_interes_anual' => 5.00,
    'numero_pagos' => 48,
    'fecha_primer_pago' => '2025-08-15'
];

echo "=== GENERANDO TABLA DE AMORTIZACIÓN PARA VENTA 18 ===\n\n";
echo "Configuración:\n";
echo "- Monto a financiar: $" . number_format($configFinanciera['monto_financiar'], 2) . "\n";
echo "- Tasa anual: " . $configFinanciera['tasa_interes_anual'] . "%\n";
echo "- Número de pagos: " . $configFinanciera['numero_pagos'] . " meses\n";
echo "- Primer pago: " . $configFinanciera['fecha_primer_pago'] . "\n\n";

// Simular primero para verificar cálculos
echo "=== SIMULACIÓN ===\n";
$simulacion = simular_amortizacion($configFinanciera);

if ($simulacion['success']) {
    $resumen = $simulacion['resumen'];
    echo "✅ Simulación exitosa:\n";
    echo "- Pago mensual: $" . number_format($resumen['pago_mensual'], 2) . "\n";
    echo "- Total intereses: $" . number_format($resumen['total_intereses'], 2) . "\n";
    echo "- Total a pagar: $" . number_format($resumen['total_a_pagar'], 2) . "\n";
    echo "- Fecha final: " . $resumen['fecha_final'] . "\n\n";
    
    // Mostrar primeras 3 mensualidades
    echo "Primeras 3 mensualidades:\n";
    for ($i = 0; $i < 3 && $i < count($simulacion['tabla_simulada']); $i++) {
        $mens = $simulacion['tabla_simulada'][$i];
        echo sprintf(
            "  %d. %s - Capital: $%s - Interés: $%s - Total: $%s\n",
            $mens['numero_pago'],
            $mens['fecha_vencimiento'],
            number_format($mens['capital'], 2),
            number_format($mens['interes'], 2),
            number_format($mens['pago_total'], 2)
        );
    }
    echo "\n";
} else {
    echo "❌ Error en simulación: " . $simulacion['error'] . "\n";
    exit(1);
}

// Ahora generar la tabla real
echo "=== GENERANDO TABLA REAL ===\n";
$resultado = generar_tabla_amortizacion(18, $configFinanciera);

if ($resultado['success']) {
    echo "✅ Tabla de amortización generada exitosamente:\n";
    echo "- Mensualidades generadas: " . $resultado['mensualidades_generadas'] . "\n";
    echo "- IDs insertados: " . implode(', ', $resultado['ids_insertados']) . "\n";
    echo "- Monto financiado: $" . number_format($resultado['monto_financiado'], 2) . "\n";
    echo "- Pago mensual: $" . number_format($resultado['pago_mensual'], 2) . "\n";
    echo "- Total intereses: $" . number_format($resultado['total_intereses'], 2) . "\n";
    echo "- Total a pagar: $" . number_format($resultado['total_a_pagar'], 2) . "\n\n";
    
    echo "✅ Venta 18 ahora tiene tabla de amortización completa.\n";
    echo "✅ Puede acceder al estado de cuenta en: http://localhost/admin/estado-cuenta/18\n";
} else {
    echo "❌ Error generando tabla: " . $resultado['error'] . "\n";
    exit(1);
}

echo "\n=== PROCESO COMPLETADO ===\n";