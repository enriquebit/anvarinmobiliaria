<?php

namespace Tests\Unit\Services;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\DocumentoService;
use App\Models\VentaModel;
use App\Models\ClienteModel;
use App\Models\LoteModel;
use App\Models\ProyectoModel;
use App\Models\EmpresaModel;

/**
 * DocumentoServiceTest
 * 
 * Tests unitarios para el servicio de generación de documentos
 * Valida la generación correcta de todos los tipos de documentos
 */
class DocumentoServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private DocumentoService $documentoService;
    private int $ventaIdPrueba;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $seed = 'Tests\Support\Database\Seeds\VentasTestSeeder';

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentoService = new DocumentoService();
        $this->ventaIdPrueba = $this->crearDatosPrueba();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->limpiarArchivosGenerados();
    }

    /**
     * Test obtener tipos de documentos disponibles
     */
    public function testObtenerTiposDocumentosDisponibles(): void
    {
        $tipos = $this->documentoService->obtenerTiposDocumentosDisponibles();
        
        $this->assertIsArray($tipos);
        $this->assertNotEmpty($tipos);
        
        // Verificar que contiene los tipos principales
        $tiposEsperados = [
            DocumentoService::TIPO_ESTADO_CUENTA,
            DocumentoService::TIPO_CARATULA,
            DocumentoService::TIPO_RECIBO_APARTADO,
            DocumentoService::TIPO_CONTRATO
        ];
        
        foreach ($tiposEsperados as $tipo) {
            $this->assertArrayHasKey($tipo, $tipos);
            $this->assertNotEmpty($tipos[$tipo]);
        }
    }

    /**
     * Test generación de carátula de expediente
     */
    public function testGenerarCaratulaExpediente(): void
    {
        $resultado = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_CARATULA,
            $this->ventaIdPrueba
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals(DocumentoService::TIPO_CARATULA, $resultado['tipo']);
        $this->assertArrayHasKey('archivo', $resultado);
        $this->assertArrayHasKey('nombre_archivo', $resultado);
        
        // Verificar que el archivo existe
        $this->assertFileExists($resultado['archivo']);
        
        // Verificar contenido básico
        $contenido = file_get_contents($resultado['archivo']);
        $this->assertStringContainsString('Expediente de Cliente', $contenido);
        $this->assertStringContainsString('FOLIO:', $contenido);
    }

    /**
     * Test generación de estado de cuenta
     */
    public function testGenerarEstadoCuenta(): void
    {
        $parametros = [
            'meses_credito' => 12,
            'tasa_anual' => 12,
            'fecha_inicio' => '2024-01-01'
        ];

        $resultado = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_ESTADO_CUENTA,
            $this->ventaIdPrueba,
            $parametros
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals(DocumentoService::TIPO_ESTADO_CUENTA, $resultado['tipo']);
        $this->assertFileExists($resultado['archivo']);
        
        // Verificar contenido del estado de cuenta
        $contenido = file_get_contents($resultado['archivo']);
        $this->assertStringContainsString('ESTADO DE CUENTA', $contenido);
        $this->assertStringContainsString('CRONOGRAMA DE PAGOS', $contenido);
        $this->assertStringContainsString('RESUMEN FINANCIERO', $contenido);
    }

    /**
     * Test generación de recibo
     */
    public function testGenerarRecibo(): void
    {
        $parametros = [
            'monto' => 5000.00,
            'concepto' => 'Pago de apartado',
            'forma_pago' => 'Efectivo',
            'referencia' => 'REF-001',
            'observaciones' => 'Pago inicial del apartado'
        ];

        $resultado = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_RECIBO_APARTADO,
            $this->ventaIdPrueba,
            $parametros
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals(DocumentoService::TIPO_RECIBO_APARTADO, $resultado['tipo']);
        $this->assertArrayHasKey('numero_recibo', $resultado);
        $this->assertEquals(5000.00, $resultado['monto']);
        $this->assertFileExists($resultado['archivo']);
        
        // Verificar contenido del recibo
        $contenido = file_get_contents($resultado['archivo']);
        $this->assertStringContainsString('Recibo de Pago', $contenido);
        $this->assertStringContainsString('$5,000.00', $contenido);
        $this->assertStringContainsString('REF-001', $contenido);
        $this->assertStringContainsString('RECIBIDO', $contenido);
    }

    /**
     * Test generación de contrato
     */
    public function testGenerarContrato(): void
    {
        $parametros = [
            'tipo_contrato' => 'Promesa de Compraventa',
            'fecha_firma' => '01/12/2024',
            'lugar_firma' => 'Mazatlán, Sinaloa',
            'clausulas_especiales' => ['Cláusula especial de prueba'],
            'testigos' => [
                ['nombre' => 'Juan Pérez', 'cargo' => 'Testigo 1'],
                ['nombre' => 'María López', 'cargo' => 'Testigo 2']
            ]
        ];

        $resultado = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_CONTRATO,
            $this->ventaIdPrueba,
            $parametros
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals(DocumentoService::TIPO_CONTRATO, $resultado['tipo']);
        $this->assertArrayHasKey('numero_contrato', $resultado);
        $this->assertFileExists($resultado['archivo']);
        
        // Verificar contenido del contrato
        $contenido = file_get_contents($resultado['archivo']);
        $this->assertStringContainsString('Contrato de Promesa de Compraventa', $contenido);
        $this->assertStringContainsString('La Vendedora', $contenido);
        $this->assertStringContainsString('El Comprador', $contenido);
        $this->assertStringContainsString('Mazatlán, Sinaloa', $contenido);
        $this->assertStringContainsString('TESTIGOS', $contenido);
    }

    /**
     * Test generación de documentos legales
     */
    public function testGenerarDocumentoLegal(): void
    {
        $parametros = [
            'contenido_adicional' => 'Contenido adicional para el aviso de privacidad'
        ];

        $resultado = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_AVISO_PRIVACIDAD,
            $this->ventaIdPrueba,
            $parametros
        );

        $this->assertTrue($resultado['success']);
        $this->assertEquals(DocumentoService::TIPO_AVISO_PRIVACIDAD, $resultado['tipo']);
        $this->assertFileExists($resultado['archivo']);
    }

    /**
     * Test generación con venta inexistente
     */
    public function testGenerarConVentaInexistente(): void
    {
        $resultado = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_CARATULA,
            99999 // ID que no existe
        );

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Venta no encontrada', $resultado['message']);
    }

    /**
     * Test generación con tipo de documento inválido
     */
    public function testGenerarConTipoInvalido(): void
    {
        $resultado = $this->documentoService->generarDocumento(
            'tipo_inexistente',
            $this->ventaIdPrueba
        );

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Tipo de documento no soportado', $resultado['message']);
    }

    /**
     * Test generación de recibo sin monto
     */
    public function testGenerarReciboSinMonto(): void
    {
        $resultado = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_RECIBO_APARTADO,
            $this->ventaIdPrueba,
            ['monto' => 0] // Monto inválido
        );

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('monto del recibo debe ser mayor a 0', $resultado['message']);
    }

    /**
     * Test nombres de archivos únicos
     */
    public function testNombresArchivosUnicos(): void
    {
        // Generar dos documentos del mismo tipo
        $resultado1 = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_CARATULA,
            $this->ventaIdPrueba
        );

        // Esperar un momento para asegurar timestamp diferente
        sleep(1);

        $resultado2 = $this->documentoService->generarDocumento(
            DocumentoService::TIPO_CARATULA,
            $this->ventaIdPrueba
        );

        $this->assertTrue($resultado1['success']);
        $this->assertTrue($resultado2['success']);
        $this->assertNotEquals($resultado1['nombre_archivo'], $resultado2['nombre_archivo']);
    }

    /**
     * Test generación múltiple de recibos
     */
    public function testGeneracionMultipleRecibos(): void
    {
        $tiposRecibos = [
            DocumentoService::TIPO_RECIBO_APARTADO,
            DocumentoService::TIPO_RECIBO_ENGANCHE,
            DocumentoService::TIPO_RECIBO_VENTA,
            DocumentoService::TIPO_RECIBO_MENSUALIDAD,
            DocumentoService::TIPO_RECIBO_ABONO,
            DocumentoService::TIPO_RECIBO_COMISION
        ];

        foreach ($tiposRecibos as $tipo) {
            $resultado = $this->documentoService->generarDocumento(
                $tipo,
                $this->ventaIdPrueba,
                ['monto' => 1000.00]
            );

            $this->assertTrue($resultado['success'], "Falló generación de {$tipo}");
            $this->assertEquals($tipo, $resultado['tipo']);
            $this->assertFileExists($resultado['archivo']);
        }
    }

    /**
     * Test integridad de templates
     */
    public function testIntegridadTemplates(): void
    {
        $templatesEsperados = [
            'estado_cuenta.html',
            'caratula_expediente.html',
            'recibo_general.html',
            'contrato_standard.html'
        ];

        $rutaTemplates = WRITEPATH . 'templates/documentos/';

        foreach ($templatesEsperados as $template) {
            $rutaCompleta = $rutaTemplates . $template;
            $this->assertFileExists($rutaCompleta, "Template {$template} no existe");
            
            $contenido = file_get_contents($rutaCompleta);
            $this->assertNotEmpty($contenido, "Template {$template} está vacío");
            $this->assertStringContainsString('<!DOCTYPE html>', $contenido, "Template {$template} no es HTML válido");
        }
    }

    /**
     * Test manejo de errores en templates
     */
    public function testManejoErroresTemplates(): void
    {
        // Simular template inexistente modificando temporalmente el servicio
        $reflection = new \ReflectionClass($this->documentoService);
        $method = $reflection->getMethod('generarDesdeTemplate');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Template no encontrado');
        
        $method->invoke(
            $this->documentoService,
            'template_inexistente.html',
            [],
            'test.html'
        );
    }

    /**
     * Test performance de generación
     */
    public function testPerformanceGeneracion(): void
    {
        $startTime = microtime(true);

        // Generar varios documentos
        for ($i = 0; $i < 5; $i++) {
            $this->documentoService->generarDocumento(
                DocumentoService::TIPO_CARATULA,
                $this->ventaIdPrueba
            );
        }

        $endTime = microtime(true);
        $tiempoTranscurrido = $endTime - $startTime;

        // Debe generar 5 documentos en menos de 3 segundos
        $this->assertLessThan(3.0, $tiempoTranscurrido, 'La generación debe ser eficiente');
    }

    /**
     * MÉTODOS AUXILIARES
     */

    private function crearDatosPrueba(): int
    {
        // Crear empresa de prueba
        $empresaModel = new EmpresaModel();
        $empresaId = $empresaModel->insert([
            'razon_social' => 'Empresa Test S.A. de C.V.',
            'nombre_comercial' => 'Empresa Test',
            'rfc' => 'ETE123456789',
            'telefono' => '6691234567',
            'email' => 'test@empresa.com',
            'domicilio' => 'Calle Test #123, Col. Test, Mazatlán, Sin.',
            'activo' => true
        ]);

        // Crear proyecto de prueba
        $proyectoModel = new ProyectoModel();
        $proyectoId = $proyectoModel->insert([
            'nombre' => 'Proyecto Test',
            'empresas_id' => $empresaId,
            'activo' => true
        ]);

        // Crear cliente de prueba
        $clienteModel = new ClienteModel();
        $clienteId = $clienteModel->insert([
            'nombres' => 'Juan Carlos',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'González',
            'email' => 'juan.perez@test.com',
            'telefono' => '6691234567',
            'rfc' => 'PEGJ800101ABC',
            'curp' => 'PEGJ800101HSLRZN00',
            'activo' => true
        ]);

        // Crear lote de prueba
        $loteModel = new LoteModel();
        $loteId = $loteModel->insert([
            'clave' => 'LOT-001',
            'proyectos_id' => $proyectoId,
            'manzana' => 'A',
            'area' => 150.50,
            'precio_total' => 250000.00,
            'disponible' => true
        ]);

        // Crear venta de prueba
        $ventaModel = new VentaModel();
        $ventaId = $ventaModel->insert([
            'folio' => 'VTA-TEST-001',
            'lotes_id' => $loteId,
            'clientes_id' => $clienteId,
            'empresas_id' => $empresaId,
            'proyectos_id' => $proyectoId,
            'total' => 250000.00,
            'anticipo' => 37500.00,
            'estado' => 'apartado',
            'fecha_venta' => date('Y-m-d'),
            'activo' => true
        ]);

        return $ventaId;
    }

    private function limpiarArchivosGenerados(): void
    {
        $rutaGenerados = WRITEPATH . 'documentos/generados/';
        
        if (is_dir($rutaGenerados)) {
            $archivos = glob($rutaGenerados . '*');
            foreach ($archivos as $archivo) {
                if (is_file($archivo) && strpos(basename($archivo), 'test') !== false) {
                    unlink($archivo);
                }
            }
        }
    }
}