<?php

namespace Tests\Unit\Services;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\VentaCalculoService;

/**
 * VentaCalculoServiceTest
 * 
 * Tests unitarios para el servicio de cálculos financieros
 * Valida precisión y correctitud de operaciones críticas
 */
class VentaCalculoServiceTest extends CIUnitTestCase
{
    private VentaCalculoService $calculoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculoService = new VentaCalculoService();
    }

    /**
     * Test validación de precisión financiera
     */
    public function testValidarPrecision(): void
    {
        // Test casos normales
        $this->assertEquals(1234.57, $this->calculoService->validarPrecision(1234.56789));
        $this->assertEquals(0.00, $this->calculoService->validarPrecision(0.004));
        $this->assertEquals(1000.00, $this->calculoService->validarPrecision(1000));
        
        // Test casos extremos
        $this->assertEquals(999999.99, $this->calculoService->validarPrecision(999999.999));
        $this->assertEquals(0.01, $this->calculoService->validarPrecision(0.009));
    }

    /**
     * Test amortización francesa básica
     */
    public function testAmortizacionFrancesaBasica(): void
    {
        $capital = 100000;
        $tasaAnual = 12; // 12% anual
        $meses = 12;

        $resultado = $this->calculoService->calcularAmortizacionFrancesa($capital, $tasaAnual, $meses);

        // Validar estructura del resultado
        $this->assertArrayHasKey('tabla_amortizacion', $resultado);
        $this->assertArrayHasKey('resumen', $resultado);
        
        // Validar resumen
        $resumen = $resultado['resumen'];
        $this->assertEquals($capital, $resumen['capital_prestado']);
        $this->assertEquals($tasaAnual, $resumen['tasa_anual_porcentaje']);
        $this->assertEquals($meses, $resumen['numero_pagos']);
        
        // Validar que hay 12 cuotas
        $this->assertCount($meses, $resultado['tabla_amortizacion']);
        
        // Validar primera cuota
        $primeraCuota = $resultado['tabla_amortizacion'][0];
        $this->assertEquals(1, $primeraCuota['numero_pago']);
        $this->assertEquals($capital, $primeraCuota['saldo_inicial']);
        $this->assertGreaterThan(0, $primeraCuota['cuota_fija']);
        $this->assertGreaterThan(0, $primeraCuota['interes']);
        $this->assertGreaterThan(0, $primeraCuota['capital']);
        
        // Validar última cuota
        $ultimaCuota = $resultado['tabla_amortizacion'][$meses - 1];
        $this->assertEquals($meses, $ultimaCuota['numero_pago']);
        $this->assertEquals(0, $ultimaCuota['saldo_final']);
    }

    /**
     * Test amortización sin interés
     */
    public function testAmortizacionSinInteres(): void
    {
        $capital = 50000;
        $tasaAnual = 0; // Sin interés
        $meses = 10;

        $resultado = $this->calculoService->calcularAmortizacionFrancesa($capital, $tasaAnual, $meses);

        // Validar que no hay intereses
        $this->assertEquals(0, $resultado['resumen']['total_intereses']);
        $this->assertEquals($capital, $resultado['resumen']['total_a_pagar']);
        
        // Validar cuota fija
        $cuotaEsperada = $capital / $meses;
        $this->assertEquals($cuotaEsperada, $resultado['resumen']['cuota_fija']);
        
        // Validar que todas las cuotas no tienen interés
        foreach ($resultado['tabla_amortizacion'] as $cuota) {
            $this->assertEquals(0, $cuota['interes']);
            $this->assertEquals($cuotaEsperada, $cuota['capital']);
        }
    }

    /**
     * Test cálculo de interés moratorio
     */
    public function testCalculoInteresMoratorio(): void
    {
        $saldo = 5000;
        $diasVencido = 30;
        $tasaAnual = 24; // 24% anual
        
        $interes = $this->calculoService->calcularInteresMoratorio($saldo, $diasVencido, $tasaAnual);
        
        // Calcular manualmente: 5000 * (24/365/100) * 30
        $interesEsperado = $saldo * ($tasaAnual / 365 / 100) * $diasVencido;
        $interesEsperado = round($interesEsperado, 2);
        
        $this->assertEquals($interesEsperado, $interes);
        
        // Test casos extremos
        $this->assertEquals(0, $this->calculoService->calcularInteresMoratorio(0, 30, 24));
        $this->assertEquals(0, $this->calculoService->calcularInteresMoratorio(5000, 0, 24));
        $this->assertEquals(0, $this->calculoService->calcularInteresMoratorio(-1000, 30, 24));
    }

    /**
     * Test cálculo de anticipo mínimo
     */
    public function testCalculoAnticipoMinimo(): void
    {
        $precio = 200000;
        $porcentaje = 15; // 15%
        $montoMinimo = 25000;
        
        // Caso 1: Porcentaje mayor que monto mínimo
        $anticipo = $this->calculoService->calcularAnticipoMinimo($precio, $porcentaje, $montoMinimo);
        $this->assertEquals(30000, $anticipo); // 15% de 200,000 = 30,000
        
        // Caso 2: Monto mínimo mayor que porcentaje
        $anticipo2 = $this->calculoService->calcularAnticipoMinimo($precio, 5, 25000); // 5% = 10,000
        $this->assertEquals(25000, $anticipo2); // Debe usar el monto mínimo
        
        // Caso 3: Sin monto mínimo
        $anticipo3 = $this->calculoService->calcularAnticipoMinimo($precio, 10);
        $this->assertEquals(20000, $anticipo3); // 10% de 200,000
    }

    /**
     * Test cálculo de descuento
     */
    public function testCalculoDescuento(): void
    {
        $precioOriginal = 100000;
        
        // Test descuento por porcentaje
        $resultado1 = $this->calculoService->calcularDescuento($precioOriginal, 10);
        $this->assertEquals(10000, $resultado1['descuento_monto']);
        $this->assertEquals(10, $resultado1['descuento_porcentaje']);
        $this->assertEquals(90000, $resultado1['precio_final']);
        
        // Test descuento por monto fijo
        $resultado2 = $this->calculoService->calcularDescuento($precioOriginal, 0, 15000);
        $this->assertEquals(15000, $resultado2['descuento_monto']);
        $this->assertEquals(15, $resultado2['descuento_porcentaje']);
        $this->assertEquals(85000, $resultado2['precio_final']);
        
        // Test sin descuento
        $resultado3 = $this->calculoService->calcularDescuento($precioOriginal);
        $this->assertEquals(0, $resultado3['descuento_monto']);
        $this->assertEquals($precioOriginal, $resultado3['precio_final']);
    }

    /**
     * Test validación de consistencia financiera
     */
    public function testValidacionConsistenciaFinanciera(): void
    {
        $capital = 50000;
        $amortizacion = $this->calculoService->calcularAmortizacionFrancesa($capital, 12, 12);
        
        $validacion = $this->calculoService->validarConsistenciaFinanciera(
            $amortizacion['tabla_amortizacion'], 
            $capital
        );
        
        $this->assertTrue($validacion['es_valida']);
        $this->assertEmpty($validacion['errores']);
        $this->assertEquals($capital, $validacion['totales']['capital']);
    }

    /**
     * Test validaciones de entrada
     */
    public function testValidacionesEntrada(): void
    {
        // Test capital negativo
        $this->expectException(\InvalidArgumentException::class);
        $this->calculoService->calcularAmortizacionFrancesa(-1000, 12, 12);
    }

    public function testValidacionTasaInvalida(): void
    {
        // Test tasa mayor a 100%
        $this->expectException(\InvalidArgumentException::class);
        $this->calculoService->calcularAmortizacionFrancesa(50000, 150, 12);
    }

    public function testValidacionMesesExtremos(): void
    {
        // Test meses excesivos
        $this->expectException(\InvalidArgumentException::class);
        $this->calculoService->calcularAmortizacionFrancesa(50000, 12, 700);
    }

    /**
     * Test formateo de montos
     */
    public function testFormateoMontos(): void
    {
        $this->assertEquals('$1,234.57', $this->calculoService->formatearMonto(1234.57));
        $this->assertEquals('€1,000.00', $this->calculoService->formatearMonto(1000, '€'));
        $this->assertEquals('$0.50', $this->calculoService->formatearMonto(0.5));
        $this->assertEquals('$1,000,000.00', $this->calculoService->formatearMonto(1000000));
    }

    /**
     * Test interés compuesto
     */
    public function testInteresCompuesto(): void
    {
        $capital = 10000;
        $tasaAnual = 12;
        $periodos = 12; // 12 meses
        
        $interes = $this->calculoService->calcularInteresCompuesto($capital, $tasaAnual, $periodos, 'mensual');
        
        // Verificar que genera interés
        $this->assertGreaterThan(0, $interes);
        
        // Verificar que el interés es menor al capital
        $this->assertLessThan($capital, $interes);
        
        // Test casos especiales
        $this->assertEquals(0, $this->calculoService->calcularInteresCompuesto(0, 12, 12));
        $this->assertEquals(0, $this->calculoService->calcularInteresCompuesto(10000, 0, 12));
        $this->assertEquals(0, $this->calculoService->calcularInteresCompuesto(10000, 12, 0));
    }

    /**
     * Test recálculo de saldos después de pago
     */
    public function testRecalculoSaldosDespuesPago(): void
    {
        // Crear tabla de amortización pequeña
        $amortizacion = $this->calculoService->calcularAmortizacionFrancesa(10000, 12, 3);
        $tabla = $amortizacion['tabla_amortizacion'];
        
        // Simular pago de la primera cuota
        $montoPago = $tabla[0]['cuota_fija'];
        $resultado = $this->calculoService->recalcularSaldosDespuesPago($tabla, $montoPago, 1);
        
        $this->assertTrue($resultado['monto_aplicado'] > 0);
        $this->assertEquals(0, $resultado['saldo_restante_pago']);
        
        // Verificar que la primera cuota está marcada como pagada
        $tablaActualizada = $resultado['tabla_actualizada'];
        $this->assertTrue($tablaActualizada[0]['cobrado']);
    }

    /**
     * Test casos de rendimiento
     */
    public function testRendimiento(): void
    {
        $startTime = microtime(true);
        
        // Calcular amortización grande
        $this->calculoService->calcularAmortizacionFrancesa(1000000, 12, 360); // 30 años
        
        $endTime = microtime(true);
        $tiempoTranscurrido = $endTime - $startTime;
        
        // Debe completarse en menos de 1 segundo
        $this->assertLessThan(1.0, $tiempoTranscurrido, 'El cálculo debe ser eficiente');
    }

    /**
     * Test precisión en casos extremos
     */
    public function testPrecisionCasosExtremos(): void
    {
        // Test con números muy pequeños
        $resultado1 = $this->calculoService->calcularAmortizacionFrancesa(1, 1, 12);
        $this->assertIsArray($resultado1);
        $this->assertEquals(1, $resultado1['resumen']['capital_prestado']);
        
        // Test con números muy grandes
        $resultado2 = $this->calculoService->calcularAmortizacionFrancesa(99999999, 1, 12);
        $this->assertIsArray($resultado2);
        $this->assertEquals(99999999, $resultado2['resumen']['capital_prestado']);
        
        // Validar que no hay errores de redondeo significativos
        $validacion = $this->calculoService->validarConsistenciaFinanciera(
            $resultado2['tabla_amortizacion'], 
            99999999
        );
        $this->assertTrue($validacion['es_valida']);
    }
}