<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\VentasService;
use App\Services\CobranzaService;
use App\Services\DocumentoService;
use App\Models\VentaModel;
use App\Models\ClienteModel;
use App\Models\LoteModel;
use App\Models\ProyectoModel;
use App\Models\EmpresaModel;

/**
 * VentasFlowTest
 * 
 * Tests de integración para el flujo completo de ventas
 * Valida la interacción entre todos los servicios del módulo
 */
class VentasFlowTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private VentasService $ventasService;
    private CobranzaService $cobranzaService;
    private DocumentoService $documentoService;
    private array $datosPrueba;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->ventasService = new VentasService();
        $this->cobranzaService = new CobranzaService();
        $this->documentoService = new DocumentoService();
        
        $this->datosPrueba = $this->crearDatosBasePrueba();
    }

    /**
     * Test flujo completo de apartado simple
     */
    public function testFlujoCompletoApartadoSimple(): void
    {
        $datosApartado = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 250000.00,
            'anticipo' => 37500.00,
            'observaciones' => 'Apartado de prueba',
            'generar_documentos' => true
        ];

        // 1. Crear apartado
        $resultado = $this->ventasService->crearApartadoSimple($datosApartado);
        
        $this->assertTrue($resultado['success']);
        $this->assertArrayHasKey('venta_id', $resultado);
        $this->assertArrayHasKey('folio', $resultado);
        $this->assertArrayHasKey('documentos_generados', $resultado);
        
        $ventaId = $resultado['venta_id'];
        
        // 2. Verificar que la venta existe en base de datos
        $ventaModel = new VentaModel();
        $venta = $ventaModel->find($ventaId);
        
        $this->assertNotNull($venta);
        $this->assertEquals('apartado', $venta->estado);
        $this->assertEquals(250000.00, $venta->total);
        $this->assertEquals(37500.00, $venta->anticipo);
        
        // 3. Verificar que el lote cambió de estado
        $loteModel = new LoteModel();
        $lote = $loteModel->find($this->datosPrueba['lote_id']);
        $this->assertFalse($lote->disponible);
        
        // 4. Verificar documentos generados
        $this->assertNotEmpty($resultado['documentos_generados']);
        
        // 5. Generar documento adicional manualmente
        $estadoCuenta = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_ESTADO_CUENTA,
            $ventaId,
            ['meses_credito' => 12, 'tasa_anual' => 12]
        );
        
        $this->assertTrue($estadoCuenta['success']);
        $this->assertFileExists($estadoCuenta['archivo']);
    }

    /**
     * Test flujo completo de venta a crédito
     */
    public function testFlujoCompletoVentaCredito(): void
    {
        $datosVenta = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 250000.00,
            'anticipo' => 50000.00,
            'monto_financiar' => 200000.00,
            'meses_credito' => 24,
            'tasa_anual' => 12,
            'fecha_inicial' => date('Y-m-d'),
            'generar_documentos' => true
        ];

        // 1. Crear venta a crédito
        $resultado = $this->ventasService->crearVentaCredito($datosVenta);
        
        $this->assertTrue($resultado['success']);
        $this->assertArrayHasKey('venta_id', $resultado);
        $this->assertArrayHasKey('plan_pagos_generado', $resultado);
        $this->assertArrayHasKey('cuota_mensual', $resultado);
        $this->assertArrayHasKey('documentos_generados', $resultado);
        
        $ventaId = $resultado['venta_id'];
        
        // 2. Verificar plan de pagos generado
        $this->assertEquals(24, $resultado['plan_pagos_generado']);
        $this->assertGreaterThan(0, $resultado['cuota_mensual']);
        
        // 3. Verificar documentos generados (estado de cuenta y contrato)
        $this->assertCount(2, $resultado['documentos_generados']);
        
        // 4. Obtener estado de cuenta de la venta
        $estadoCuenta = $this->cobranzaService->obtenerEstadoCuenta($ventaId);
        
        $this->assertArrayHasKey('venta', $estadoCuenta);
        $this->assertArrayHasKey('resumen', $estadoCuenta);
        $this->assertArrayHasKey('planes_pago', $estadoCuenta);
        $this->assertEquals(250000.00, $estadoCuenta['resumen']['total_venta']);
        
        // 5. Simular pago de primera mensualidad
        $primerasCuotas = array_slice($estadoCuenta['planes_pago'], 0, 1);
        if (!empty($primerasCuotas)) {
            $primerPlan = $primerasCuotas[0];
            $resultadoPago = $this->cobranzaService->aplicarPago(
                $primerPlan['id'], 
                $primerPlan['total']
            );
            
            $this->assertTrue($resultadoPago['success']);
            $this->assertTrue($resultadoPago['cobrado_completamente']);
        }
    }

    /**
     * Test flujo de apartado con pagos
     */
    public function testFlujoApartadoConPagos(): void
    {
        $datosApartado = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 250000.00,
            'anticipo' => 37500.00,
            'pagos' => [
                [
                    'monto' => 20000.00,
                    'forma_pago' => 'efectivo',
                    'concepto' => 'Abono inicial'
                ],
                [
                    'monto' => 17500.00,
                    'forma_pago' => 'transferencia',
                    'concepto' => 'Completar anticipo',
                    'referencia' => 'TRANS-001'
                ]
            ],
            'generar_documentos' => true
        ];

        // 1. Crear apartado con pagos
        $resultado = $this->ventasService->crearApartadoConPagos($datosApartado);
        
        $this->assertTrue($resultado['success']);
        $this->assertEquals(2, $resultado['pagos_procesados']);
        $this->assertEquals(37500.00, $resultado['anticipo_pagado']);
        $this->assertEquals(0, $resultado['saldo_anticipo_pendiente']);
        
        $ventaId = $resultado['venta_id'];
        
        // 2. Confirmar apartado como venta a crédito
        $datosConfirmacion = [
            'meses_credito' => 18,
            'tasa_anual' => 15
        ];
        
        $confirmacion = $this->ventasService->confirmarApartado($ventaId, $datosConfirmacion);
        
        $this->assertTrue($confirmacion['success']);
        $this->assertEquals('crédito', $confirmacion['tipo_venta']);
        
        // 3. Verificar que se generó plan de pagos para saldo restante
        $estadoCuenta = $this->cobranzaService->obtenerEstadoCuenta($ventaId);
        $saldoPendiente = $estadoCuenta['resumen']['saldo_pendiente'];
        
        $this->assertEquals(212500.00, $saldoPendiente); // 250,000 - 37,500
    }

    /**
     * Test flujo de venta de contado
     */
    public function testFlujoVentaContado(): void
    {
        $datosVenta = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 250000.00,
            'pagos' => [
                [
                    'monto' => 250000.00,
                    'forma_pago' => 'transferencia',
                    'concepto' => 'Pago total',
                    'referencia' => 'TRANS-TOTAL-001'
                ]
            ],
            'generar_documentos' => true
        ];

        // 1. Crear venta de contado
        $resultado = $this->ventasService->crearVentaContado($datosVenta);
        
        $this->assertTrue($resultado['success']);
        $this->assertArrayHasKey('venta_id', $resultado);
        
        $ventaId = $resultado['venta_id'];
        
        // 2. Verificar que la venta está completamente pagada
        $ventaModel = new VentaModel();
        $venta = $ventaModel->find($ventaId);
        
        $this->assertEquals('venta_contado', $venta->estado);
        $this->assertEquals(250000.00, $venta->total_pagado);
        $this->assertEquals(0, $venta->getSaldoPendiente());
        
        // 3. Verificar que el lote está vendido
        $loteModel = new LoteModel();
        $lote = $loteModel->find($this->datosPrueba['lote_id']);
        $this->assertEquals('vendido', $lote->estado);
    }

    /**
     * Test flujo de cancelación de venta
     */
    public function testFlujoCancelacionVenta(): void
    {
        // 1. Crear apartado
        $datosApartado = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 250000.00,
            'anticipo' => 37500.00
        ];

        $resultado = $this->ventasService->crearApartadoSimple($datosApartado);
        $this->assertTrue($resultado['success']);
        
        $ventaId = $resultado['venta_id'];
        
        // 2. Cancelar venta
        $motivo = 'Cliente desistió de la compra';
        $cancelacion = $this->ventasService->cancelarVenta($ventaId, $motivo);
        
        $this->assertTrue($cancelacion['success']);
        
        // 3. Verificar estado de la venta
        $ventaModel = new VentaModel();
        $venta = $ventaModel->find($ventaId);
        $this->assertEquals('cancelado', $venta->estado);
        
        // 4. Verificar que el lote volvió a estar disponible
        $loteModel = new LoteModel();
        $lote = $loteModel->find($this->datosPrueba['lote_id']);
        $this->assertTrue($lote->disponible);
    }

    /**
     * Test flujo de intereses moratorios
     */
    public function testFlujoInteresesMoratorios(): void
    {
        // 1. Crear venta a crédito
        $datosVenta = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 100000.00,
            'monto_financiar' => 80000.00,
            'meses_credito' => 12,
            'tasa_anual' => 12,
            'generar_documentos' => false
        ];

        $resultado = $this->ventasService->crearVentaCredito($datosVenta);
        $this->assertTrue($resultado['success']);
        
        $ventaId = $resultado['venta_id'];
        
        // 2. Obtener planes de pago
        $estadoCuenta = $this->cobranzaService->obtenerEstadoCuenta($ventaId);
        $planes = $estadoCuenta['planes_pago'];
        $this->assertNotEmpty($planes);
        
        // 3. Simular plan vencido (modificar fecha manualmente)
        $primerPlan = $planes[0];
        
        // Modificar fecha de vencimiento a hace 10 días
        $db = \Config\Database::connect();
        $db->table('plan_pagos')
           ->where('id', $primerPlan['id'])
           ->update(['fecha_vencimiento' => date('Y-m-d', strtotime('-10 days'))]);
        
        // 4. Obtener planes vencidos y calcular intereses
        $planesVencidos = $this->cobranzaService->obtenerPlanesVencidos(5);
        $this->assertNotEmpty($planesVencidos);
        
        // Verificar que incluye cálculo de interés moratorio
        $planVencido = $planesVencidos[0];
        $this->assertGreaterThan(0, $planVencido->interes_moratorio);
        $this->assertEquals(10, $planVencido->dias_vencido);
    }

    /**
     * Test flujo completo con múltiples documentos
     */
    public function testFlujoMultiplesDocumentos(): void
    {
        // 1. Crear venta a crédito
        $datosVenta = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 300000.00,
            'monto_financiar' => 240000.00,
            'meses_credito' => 36,
            'tasa_anual' => 12,
            'generar_documentos' => true
        ];

        $resultado = $this->ventasService->crearVentaCredito($datosVenta);
        $this->assertTrue($resultado['success']);
        
        $ventaId = $resultado['venta_id'];
        
        // 2. Generar todos los tipos de documentos
        $tiposDocumentos = [
            DocumentoService::TIPO_CARATULA,
            DocumentoService::TIPO_ESTADO_CUENTA,
            DocumentoService::TIPO_CONTRATO,
            DocumentoService::TIPO_RECIBO_APARTADO,
            DocumentoService::TIPO_AVISO_PRIVACIDAD
        ];

        $documentosGenerados = [];
        foreach ($tiposDocumentos as $tipo) {
            $parametros = [];
            
            if ($tipo === DocumentoService::TIPO_ESTADO_CUENTA) {
                $parametros = ['meses_credito' => 36, 'tasa_anual' => 12];
            } elseif (strpos($tipo, 'recibo_') === 0) {
                $parametros = ['monto' => 5000.00];
            }
            
            $documento = $this->documentoService->generarDocumento($tipo, $ventaId, $parametros);
            
            $this->assertTrue($documento['success'], "Falló generación de {$tipo}");
            $this->assertFileExists($documento['archivo']);
            
            $documentosGenerados[] = $documento;
        }
        
        // 3. Verificar que todos los documentos son únicos
        $nombres = array_column($documentosGenerados, 'nombre_archivo');
        $this->assertEquals(count($nombres), count(array_unique($nombres)));
        
        // 4. Verificar contenido de al menos un documento
        $estadoCuenta = $documentosGenerados[1]; // Estado de cuenta
        $contenido = file_get_contents($estadoCuenta['archivo']);
        $this->assertStringContainsString('CRONOGRAMA DE PAGOS', $contenido);
        $this->assertStringContainsString('$300,000.00', $contenido);
    }

    /**
     * Test manejo de errores en el flujo
     */
    public function testManejoErroresFlujo(): void
    {
        // 1. Intentar venta con lote inexistente
        $datosInvalidos = [
            'lotes_id' => 99999,
            'clientes_id' => $this->datosPrueba['cliente_id'],
            'vendedor_id' => 1,
            'total' => 250000.00
        ];

        $resultado = $this->ventasService->crearApartadoSimple($datosInvalidos);
        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Lote no encontrado', $resultado['message']);
        
        // 2. Intentar venta con cliente inexistente
        $datosInvalidos2 = [
            'lotes_id' => $this->datosPrueba['lote_id'],
            'clientes_id' => 99999,
            'vendedor_id' => 1,
            'total' => 250000.00
        ];

        $resultado2 = $this->ventasService->crearApartadoSimple($datosInvalidos2);
        $this->assertFalse($resultado2['success']);
        
        // 3. Intentar cancelar venta inexistente
        $cancelacion = $this->ventasService->cancelarVenta(99999);
        $this->assertFalse($cancelacion['success']);
    }

    /**
     * MÉTODOS AUXILIARES
     */

    private function crearDatosBasePrueba(): array
    {
        // Crear empresa
        $empresaModel = new EmpresaModel();
        $empresaId = $empresaModel->insert([
            'razon_social' => 'Empresa Test Integración S.A.',
            'nombre_comercial' => 'Test Integración',
            'rfc' => 'ETI123456789',
            'telefono' => '6691234567',
            'email' => 'integracion@test.com',
            'domicilio' => 'Calle Integración #123',
            'activo' => true
        ]);

        // Crear proyecto
        $proyectoModel = new ProyectoModel();
        $proyectoId = $proyectoModel->insert([
            'nombre' => 'Proyecto Integración Test',
            'empresas_id' => $empresaId,
            'activo' => true
        ]);

        // Crear cliente
        $clienteModel = new ClienteModel();
        $clienteId = $clienteModel->insert([
            'nombres' => 'María Elena',
            'apellido_paterno' => 'Rodríguez',
            'apellido_materno' => 'Martínez',
            'email' => 'maria.rodriguez@integracion.test',
            'telefono' => '6691234567',
            'rfc' => 'ROMM850315ABC',
            'activo' => true
        ]);

        // Crear lote
        $loteModel = new LoteModel();
        $loteId = $loteModel->insert([
            'clave' => 'INT-001',
            'proyectos_id' => $proyectoId,
            'manzana' => 'A',
            'area' => 200.00,
            'precio_total' => 250000.00,
            'disponible' => true,
            'estado' => 'disponible'
        ]);

        return [
            'empresa_id' => $empresaId,
            'proyecto_id' => $proyectoId,
            'cliente_id' => $clienteId,
            'lote_id' => $loteId
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Limpiar archivos de documentos generados en tests
        $rutaGenerados = WRITEPATH . 'documentos/generados/';
        if (is_dir($rutaGenerados)) {
            $archivos = glob($rutaGenerados . '*');
            foreach ($archivos as $archivo) {
                if (is_file($archivo)) {
                    unlink($archivo);
                }
            }
        }
    }
}