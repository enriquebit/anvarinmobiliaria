<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Models\PerfilFinanciamientoModel;
use App\Services\PdfService;

class DebugPresupuestosController extends BaseController
{
    protected $perfilFinanciamientoModel;
    protected $pdfService;
    
    public function __construct()
    {
        $this->perfilFinanciamientoModel = new PerfilFinanciamientoModel();
        $this->pdfService = new PdfService();
    }

    /**
     * Debug general de presupuestos
     */
    public function index()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/admin/dashboard')->with('error', 'Debug solo disponible en desarrollo');
        }

        $data = [
            'titulo' => 'Debug - Presupuestos',
            'info' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'environment' => ENVIRONMENT,
                'base_url' => base_url(),
                'routes_loaded' => service('routes')->getRoutes(),
                'dompdf_available' => class_exists('Dompdf\\Dompdf'),
                'pdf_service_available' => class_exists('App\\Services\\PdfService'),
                'config_financiera_count' => $this->perfilFinanciamientoModel->countAllResults()
            ]
        ];

        return view('debug/presupuestos/index', $data);
    }

    /**
     * Debug de parámetros de exportación PDF
     */
    public function debugExportarPDF()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/admin/dashboard')->with('error', 'Debug solo disponible en desarrollo');
        }

        $parametros = [
            'GET' => $this->request->getGet(),
            'POST' => $this->request->getPost(),
            'REQUEST_URI' => $this->request->getUri()->getPath(),
            'REQUEST_METHOD' => $this->request->getMethod(),
            'USER_AGENT' => $this->request->getUserAgent(),
            'HEADERS' => $this->request->headers()
        ];

        $data = [
            'titulo' => 'Debug - Exportar PDF',
            'parametros' => $parametros,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return view('debug/presupuestos/export_pdf', $data);
    }

    /**
     * Test de PdfService
     */
    public function testPdfService()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/admin/dashboard')->with('error', 'Debug solo disponible en desarrollo');
        }

        try {
            // Test simple de HTML a PDF
            $html = '<h1>Test PDF Service</h1><p>Fecha: ' . date('Y-m-d H:i:s') . '</p>';
            $resultado = $this->pdfService->generateFromHtml($html, 'test.pdf');
            
            if ($resultado['success']) {
                // Configurar headers para descarga
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="test_pdf_service.pdf"');
                header('Content-Length: ' . $resultado['size']);
                
                echo $resultado['content'];
                exit;
            } else {
                return redirect()->back()->with('error', 'Error en PdfService: ' . $resultado['error']);
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Excepción en PdfService: ' . $e->getMessage());
        }
    }

    /**
     * Test de datos de presupuesto simulados
     */
    public function testPresupuestoSimulado()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/admin/dashboard')->with('error', 'Debug solo disponible en desarrollo');
        }

        try {
            // Obtener una configuración financiera
            $configuracion = $this->perfilFinanciamientoModel->where('activo', 1)->first();
            
            if (!$configuracion) {
                return redirect()->back()->with('error', 'No hay configuraciones financieras activas');
            }

            // Datos simulados
            $datosPresupuesto = [
                'lote' => [
                    'id' => 0,
                    'clave' => 'TEST-' . date('YmdHis'),
                    'proyecto_nombre' => 'Proyecto Test',
                    'manzana_nombre' => 'Manzana A',
                    'area_total' => 120,
                    'tipo_lote_nombre' => 'Residencial',
                    'categoria_nombre' => 'Premium',
                    'frente' => 8,
                    'fondo' => 15,
                    'lateral_izquierda' => 15,
                    'lateral_derecha' => 15,
                    'precio_metro' => 1900,
                    'precio_total' => 228000
                ],
                'cliente' => [
                    'id' => 0,
                    'nombre_completo' => 'Cliente Test PDF',
                    'email' => 'test@example.com',
                    'telefono' => '555-1234',
                    'rfc' => 'TEST123456ABC'
                ],
                'configuracion' => $configuracion,
                'datos' => [
                    'precio_total' => 228000,
                    'descuento_monto' => 0,
                    'precio_final' => 228000,
                    'enganche_monto' => 28000,
                    'monto_financiar' => 200000,
                    'mensualidad' => 2640,
                    'plazo_meses' => 84,
                    'tasa_interes' => 12,
                    'enganche_porcentaje' => 12.28,
                    'descuento_porcentaje' => 0
                ],
                'pagos_anticipados' => [
                    [
                        'mes_aplicacion' => 12,
                        'monto' => 10000,
                        'descripcion' => 'Pago extra 1'
                    ],
                    [
                        'mes_aplicacion' => 24,
                        'monto' => 20000,
                        'descripcion' => 'Pago extra 2'
                    ]
                ],
                'es_presupuesto' => true,
                'es_pdf' => true
            ];

            // Generar PDF usando PdfService
            $resultado = $this->pdfService->generarPresupuestoPdf($datosPresupuesto);
            
            if ($resultado['success']) {
                // Configurar headers para descarga
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $resultado['filename'] . '"');
                header('Content-Length: ' . $resultado['size']);
                
                echo $resultado['content'];
                exit;
            } else {
                return redirect()->back()->with('error', 'Error generando PDF: ' . $resultado['error']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error en test presupuesto simulado: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Test de configuración de email
     */
    public function testEmailConfig()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/admin/dashboard')->with('error', 'Debug solo disponible en desarrollo');
        }

        try {
            // Obtener configuración de email
            $emailConfig = new \Config\Email();
            
            $configInfo = [
                'fromEmail' => $emailConfig->fromEmail,
                'fromName' => $emailConfig->fromName,
                'protocol' => $emailConfig->protocol,
                'SMTPHost' => $emailConfig->SMTPHost,
                'SMTPUser' => $emailConfig->SMTPUser,
                'SMTPPort' => $emailConfig->SMTPPort,
                'SMTPTimeout' => $emailConfig->SMTPTimeout,
                'SMTPCrypto' => $emailConfig->SMTPCrypto,
                'mailType' => $emailConfig->mailType,
                'charset' => $emailConfig->charset,
                'validate' => $emailConfig->validate ? 'true' : 'false',
                'priority' => $emailConfig->priority
            ];
            
            // Mostrar configuración
            $data = [
                'titulo' => 'Debug - Configuración de Email',
                'config' => $configInfo,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            return view('debug/presupuestos/email_config', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Error en test configuración email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Test de envío de email
     */
    public function testEnviarEmail()
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/admin/dashboard')->with('error', 'Debug solo disponible en desarrollo');
        }

        try {
            // Datos de prueba
            $datosEmail = [
                'cliente' => [
                    'nombre_completo' => 'Cliente Test Email',
                    'email' => 'test@example.com',
                    'telefono' => '555-1234'
                ],
                'datos' => [
                    'precio_total' => 228000,
                    'enganche_monto' => 28000,
                    'monto_financiar' => 200000,
                    'mensualidad' => 2640,
                    'plazo_meses' => 84,
                    'tasa_interes' => 12
                ],
                'mensaje' => 'Este es un email de prueba del sistema de presupuestos.',
                'fecha' => date('d/m/Y'),
                'folio' => 'TEST-' . strtoupper(substr(uniqid(), -8))
            ];

            // Renderizar vista del email
            $htmlEmail = view('emails/presupuesto', $datosEmail);
            
            // Configurar email
            $email = \Config\Services::email();
            
            // Usar configuración desde .env
            $emailConfig = new \Config\Email();
            $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName . ' - Debug');
            $email->setTo('test@example.com');
            $email->setSubject('Test Email - Presupuesto de Financiamiento');
            $email->setMessage($htmlEmail);
            
            // Intentar enviar
            if ($email->send()) {
                return redirect()->back()->with('success', 'Email de prueba enviado correctamente');
            } else {
                $debugInfo = $email->printDebugger();
                log_message('error', 'Error enviando email de prueba: ' . $debugInfo);
                return redirect()->back()->with('error', 'Error enviando email: ' . $debugInfo);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error en test envío email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}