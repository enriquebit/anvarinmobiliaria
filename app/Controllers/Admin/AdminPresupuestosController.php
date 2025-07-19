<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LoteModel;
use App\Models\PerfilFinanciamientoModel;
use App\Services\PdfService;

class AdminPresupuestosController extends BaseController
{
    protected $perfilFinanciamientoModel;
    protected $pdfService;
    
    public function __construct()
    {
        $this->perfilFinanciamientoModel = new PerfilFinanciamientoModel();
        $this->pdfService = new PdfService();
    }

    /**
     * Formulario para crear nuevo presupuesto
     */
    public function crear()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        // Obtener financiamientos disponibles
        $configuraciones = $this->perfilFinanciamientoModel->where('activo', 1)->findAll();

        $data = [
            'titulo' => 'Generar Presupuesto',
            'configuraciones' => $configuraciones
        ];

        return view('admin/presupuestos/crear', $data);
    }

    /**
     * Generar tabla de amortización para presupuesto
     * Utiliza lógica de cálculo financiero independiente
     */
    public function generarTablaPresupuesto()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        try {
            // Obtener parámetros del formulario
            $precioLote = floatval($this->request->getPost('precio_lote') ?? 0);
            $tipoFinanciamientoId = intval($this->request->getPost('perfil_financiamiento_id'));
            $enganchePorcentaje = floatval($this->request->getPost('enganche_porcentaje') ?? 0);
            $plazoMeses = intval($this->request->getPost('plazo_meses') ?? 12);
            $tasaInteres = floatval($this->request->getPost('tasa_interes') ?? 0);
            $descuentoPorcentaje = floatval($this->request->getPost('descuento_porcentaje') ?? 0);
            $pagosAnticipadosJson = $this->request->getPost('pagos_anticipados') ?? '[]';

            // Datos del cliente
            $nombreCliente = $this->request->getPost('nombre_cliente') ?? 'Cliente';
            $emailCliente = $this->request->getPost('email_cliente') ?? '';
            $telefonoCliente = $this->request->getPost('telefono_cliente') ?? '';

            // Validar parámetros obligatorios
            if (!$precioLote || !$tipoFinanciamientoId) {
                return redirect()->back()->with('error', 'Precio del lote y configuración financiera son obligatorios');
            }

            // Obtener configuración financiera
            $configuracion = $this->perfilFinanciamientoModel->find($tipoFinanciamientoId);
            if (!$configuracion) {
                return redirect()->back()->with('error', 'Configuración financiera no encontrada');
            }

            // Decodificar pagos anticipados
            $pagosAnticipados = json_decode($pagosAnticipadosJson, true) ?? [];

            // Calcular datos financieros (misma lógica que AdminVentasController)
            $descuentoMonto = $precioLote * ($descuentoPorcentaje / 100);
            $precioFinal = $precioLote - $descuentoMonto;
            $engancheMonto = $precioFinal * ($enganchePorcentaje / 100);
            $montoFinanciar = $precioFinal - $engancheMonto;

            // Calcular mensualidad base
            $tasaMensual = $tasaInteres / 12 / 100;
            $mensualidad = 0;

            if ($montoFinanciar > 0 && $plazoMeses > 0) {
                if ($configuracion->meses_sin_intereses > 0) {
                    // Para esquemas con meses sin intereses, usar el total de meses del plazo
                    $mensualidad = $montoFinanciar / $plazoMeses;
                } else if ($tasaMensual > 0) {
                    // Solo con intereses
                    $mensualidad = $montoFinanciar * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / (pow(1 + $tasaMensual, $plazoMeses) - 1);
                } else {
                    // Sin intereses
                    $mensualidad = $montoFinanciar / $plazoMeses;
                }
            }

            // Simular datos del lote para compatibilidad con la vista existente
            $loteSimulado = [
                'id' => 0,
                'clave' => 'PRESUPUESTO-' . date('YmdHis'),
                'proyecto_nombre' => 'Presupuesto',
                'manzana_nombre' => 'N/A',
                'area_total' => 0,
                'tipo_lote_nombre' => 'Presupuesto',
                'categoria_nombre' => 'General',
                'frente' => 0,
                'fondo' => 0,
                'lateral_izquierda' => 0,
                'lateral_derecha' => 0,
                'precio_metro' => 0,
                'precio_total' => $precioLote
            ];

            // Datos del cliente simulado
            $clienteSimulado = [
                'id' => 0,
                'nombre_completo' => $nombreCliente,
                'email' => $emailCliente,
                'telefono' => $telefonoCliente,
                'rfc' => 'N/A'
            ];

            // Preparar datos para la vista (misma estructura que AdminVentasController)
            $datos = [
                'precio_total' => $precioLote,
                'descuento_monto' => $descuentoMonto,
                'precio_final' => $precioFinal,
                'enganche_monto' => $engancheMonto,
                'monto_financiar' => $montoFinanciar,
                'mensualidad' => $mensualidad,
                'plazo_meses' => $plazoMeses,
                'tasa_interes' => $tasaInteres,
                'enganche_porcentaje' => $enganchePorcentaje,
                'descuento_porcentaje' => $descuentoPorcentaje
            ];

            $data = [
                'lote' => $loteSimulado,
                'cliente' => $clienteSimulado,
                'configuracion' => $configuracion,
                'datos' => $datos,
                'pagos_anticipados' => $pagosAnticipados,
                'es_presupuesto' => true // Flag para identificar que es presupuesto
            ];

            return view('admin/presupuestos/tabla_presupuesto', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error generando presupuesto: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Vista especializada para PDF (sin tabs, todas las tablas visibles)
     * Usa parámetros GET como el módulo de ventas
     */
    public function tablaPDF()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        try {
            // Obtener parámetros de la URL (mismo formato que AdminVentasController::tablaAmortizacion)
            $precioLote = floatval($this->request->getGet('precio_lote') ?? 0);
            $tipoFinanciamientoId = intval($this->request->getGet('perfil_financiamiento_id'));
            $enganchePorcentaje = floatval($this->request->getGet('enganche_porcentaje') ?? 0);
            $plazoMeses = intval($this->request->getGet('plazo_meses') ?? 12);
            $tasaInteres = floatval($this->request->getGet('tasa_interes') ?? 0);
            $descuentoPorcentaje = floatval($this->request->getGet('descuento_porcentaje') ?? 0);
            $pagosAnticipadosJson = $this->request->getGet('pagos_anticipados') ?? '[]';
            
            // Datos del cliente
            $nombreCliente = $this->request->getGet('nombre_cliente') ?? 'Cliente';
            $emailCliente = $this->request->getGet('email_cliente') ?? '';
            $telefonoCliente = $this->request->getGet('telefono_cliente') ?? '';

            // Validar parámetros obligatorios
            if (!$precioLote || !$tipoFinanciamientoId) {
                return redirect()->to('admin/presupuestos')->with('error', 'Parámetros incompletos para generar PDF');
            }

            // Reutilizar la misma lógica de cálculo del método generarTablaPresupuesto
            $configuracion = $this->perfilFinanciamientoModel->find($tipoFinanciamientoId);
            if (!$configuracion) {
                return redirect()->to('admin/presupuestos')->with('error', 'Configuración financiera no encontrada');
            }

            $pagosAnticipados = json_decode($pagosAnticipadosJson, true) ?? [];

            // Calcular datos financieros
            $descuentoMonto = $precioLote * ($descuentoPorcentaje / 100);
            $precioFinal = $precioLote - $descuentoMonto;
            $engancheMonto = $precioFinal * ($enganchePorcentaje / 100);
            $montoFinanciar = $precioFinal - $engancheMonto;

            $tasaMensual = $tasaInteres / 12 / 100;
            $mensualidad = 0;

            if ($montoFinanciar > 0 && $plazoMeses > 0) {
                if ($configuracion->meses_sin_intereses > 0) {
                    $mensualidad = $montoFinanciar / $plazoMeses;
                } else if ($tasaMensual > 0) {
                    $mensualidad = $montoFinanciar * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / (pow(1 + $tasaMensual, $plazoMeses) - 1);
                } else {
                    $mensualidad = $montoFinanciar / $plazoMeses;
                }
            }

            // Simular datos para compatibilidad
            $loteSimulado = [
                'id' => 0,
                'clave' => 'PRESUPUESTO-' . date('YmdHis'),
                'proyecto_nombre' => 'Presupuesto',
                'manzana_nombre' => 'N/A',
                'area_total' => 0,
                'tipo_lote_nombre' => 'Presupuesto',
                'categoria_nombre' => 'General',
                'frente' => 0,
                'fondo' => 0,
                'lateral_izquierda' => 0,
                'lateral_derecha' => 0,
                'precio_metro' => 0,
                'precio_total' => $precioLote
            ];

            $clienteSimulado = [
                'id' => 0,
                'nombre_completo' => $nombreCliente,
                'email' => $emailCliente,
                'telefono' => $telefonoCliente,
                'rfc' => 'N/A'
            ];

            $datos = [
                'precio_total' => $precioLote,
                'descuento_monto' => $descuentoMonto,
                'precio_final' => $precioFinal,
                'enganche_monto' => $engancheMonto,
                'monto_financiar' => $montoFinanciar,
                'mensualidad' => $mensualidad,
                'plazo_meses' => $plazoMeses,
                'tasa_interes' => $tasaInteres,
                'enganche_porcentaje' => $enganchePorcentaje,
                'descuento_porcentaje' => $descuentoPorcentaje
            ];

            $data = [
                'lote' => $loteSimulado,
                'cliente' => $clienteSimulado,
                'configuracion' => $configuracion,
                'datos' => $datos,
                'pagos_anticipados' => $pagosAnticipados,
                'es_presupuesto' => true,
                'es_pdf' => true // Flag especial para PDF (sin tabs)
            ];

            return view('admin/presupuestos/tabla_presupuesto_pdf', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error generando vista PDF del presupuesto: ' . $e->getMessage());
            return redirect()->to('admin/presupuestos')->with('error', 'Error al generar vista PDF');
        }
    }

    /**
     * Exportar presupuesto como PDF
     * Utiliza PdfService para generar PDF desde vista especializada
     */
    public function exportarPDF()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        try {
            // Obtener parámetros de la URL (mismos que tablaPDF)
            $precioLote = floatval($this->request->getGet('precio_lote') ?? 0);
            $tipoFinanciamientoId = intval($this->request->getGet('perfil_financiamiento_id'));
            $enganchePorcentaje = floatval($this->request->getGet('enganche_porcentaje') ?? 0);
            $plazoMeses = intval($this->request->getGet('plazo_meses') ?? 12);
            $tasaInteres = floatval($this->request->getGet('tasa_interes') ?? 0);
            $descuentoPorcentaje = floatval($this->request->getGet('descuento_porcentaje') ?? 0);
            $pagosAnticipadosJson = $this->request->getGet('pagos_anticipados') ?? '[]';
            
            // Datos del cliente
            $nombreCliente = $this->request->getGet('nombre_cliente') ?? 'Cliente';
            $emailCliente = $this->request->getGet('email_cliente') ?? '';
            $telefonoCliente = $this->request->getGet('telefono_cliente') ?? '';

            // Validar parámetros obligatorios
            if (!$precioLote || !$tipoFinanciamientoId) {
                return redirect()->back()->with('error', 'Parámetros incompletos para generar PDF');
            }

            // Obtener configuración financiera
            $configuracion = $this->perfilFinanciamientoModel->find($tipoFinanciamientoId);
            if (!$configuracion) {
                return redirect()->back()->with('error', 'Configuración financiera no encontrada');
            }

            $pagosAnticipados = json_decode($pagosAnticipadosJson, true) ?? [];

            // Calcular datos financieros (misma lógica que tablaPDF)
            $descuentoMonto = $precioLote * ($descuentoPorcentaje / 100);
            $precioFinal = $precioLote - $descuentoMonto;
            $engancheMonto = $precioFinal * ($enganchePorcentaje / 100);
            $montoFinanciar = $precioFinal - $engancheMonto;

            $tasaMensual = $tasaInteres / 12 / 100;
            $mensualidad = 0;

            if ($montoFinanciar > 0 && $plazoMeses > 0) {
                if ($configuracion->meses_sin_intereses > 0) {
                    $mensualidad = $montoFinanciar / $plazoMeses;
                } else if ($tasaMensual > 0) {
                    $mensualidad = $montoFinanciar * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / (pow(1 + $tasaMensual, $plazoMeses) - 1);
                } else {
                    $mensualidad = $montoFinanciar / $plazoMeses;
                }
            }

            // Simular datos para compatibilidad
            $loteSimulado = [
                'id' => 0,
                'clave' => 'PRESUPUESTO-' . date('YmdHis'),
                'proyecto_nombre' => 'Presupuesto',
                'manzana_nombre' => 'N/A',
                'area_total' => 0,
                'tipo_lote_nombre' => 'Presupuesto',
                'categoria_nombre' => 'General',
                'frente' => 0,
                'fondo' => 0,
                'lateral_izquierda' => 0,
                'lateral_derecha' => 0,
                'precio_metro' => 0,
                'precio_total' => $precioLote
            ];

            $clienteSimulado = [
                'id' => 0,
                'nombre_completo' => $nombreCliente,
                'email' => $emailCliente,
                'telefono' => $telefonoCliente,
                'rfc' => 'N/A'
            ];

            $datos = [
                'precio_total' => $precioLote,
                'descuento_monto' => $descuentoMonto,
                'precio_final' => $precioFinal,
                'enganche_monto' => $engancheMonto,
                'monto_financiar' => $montoFinanciar,
                'mensualidad' => $mensualidad,
                'plazo_meses' => $plazoMeses,
                'tasa_interes' => $tasaInteres,
                'enganche_porcentaje' => $enganchePorcentaje,
                'descuento_porcentaje' => $descuentoPorcentaje
            ];

            // Preparar datos para PdfService
            $datosPresupuesto = [
                'lote' => $loteSimulado,
                'cliente' => $clienteSimulado,
                'configuracion' => $configuracion,
                'datos' => $datos,
                'pagos_anticipados' => $pagosAnticipados,
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
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                
                // Enviar contenido del PDF
                echo $resultado['content'];
                exit;
            } else {
                log_message('error', 'Error generando PDF: ' . $resultado['error']);
                return redirect()->back()->with('error', 'Error al generar PDF: ' . $resultado['error']);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error exportando PDF del presupuesto: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Enviar presupuesto por email
     * Genera PDF y lo envía como adjunto
     */
    public function enviarEmail()
    {
        if (!isAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        try {
            // Validar datos POST
            $emailDestino = $this->request->getPost('email_destino');
            $asunto = $this->request->getPost('asunto') ?: 'Presupuesto de Financiamiento';
            $mensaje = $this->request->getPost('mensaje') ?: 'Estimado cliente, le enviamos su presupuesto de financiamiento.';
            
            // Obtener parámetros del presupuesto
            $precioLote = floatval($this->request->getPost('precio_lote') ?? 0);
            $tipoFinanciamientoId = intval($this->request->getPost('perfil_financiamiento_id'));
            $enganchePorcentaje = floatval($this->request->getPost('enganche_porcentaje') ?? 0);
            $plazoMeses = intval($this->request->getPost('plazo_meses') ?? 12);
            $tasaInteres = floatval($this->request->getPost('tasa_interes') ?? 0);
            $descuentoPorcentaje = floatval($this->request->getPost('descuento_porcentaje') ?? 0);
            $pagosAnticipadosJson = $this->request->getPost('pagos_anticipados') ?: '[]';
            
            // Datos del cliente
            $nombreCliente = $this->request->getPost('nombre_cliente') ?: 'Cliente';
            $emailCliente = $this->request->getPost('email_cliente') ?: '';
            $telefonoCliente = $this->request->getPost('telefono_cliente') ?: '';

            // Validar email destino
            if (!$emailDestino || !filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->with('error', 'Debe proporcionar un email válido');
            }

            // Validar parámetros obligatorios
            if (!$precioLote || !$tipoFinanciamientoId) {
                return redirect()->back()->with('error', 'Parámetros del presupuesto incompletos');
            }

            // Obtener configuración financiera
            $configuracion = $this->perfilFinanciamientoModel->find($tipoFinanciamientoId);
            if (!$configuracion) {
                return redirect()->back()->with('error', 'Configuración financiera no encontrada');
            }

            $pagosAnticipados = json_decode($pagosAnticipadosJson, true) ?? [];

            // Calcular datos financieros (misma lógica que exportarPDF)
            $descuentoMonto = $precioLote * ($descuentoPorcentaje / 100);
            $precioFinal = $precioLote - $descuentoMonto;
            $engancheMonto = $precioFinal * ($enganchePorcentaje / 100);
            $montoFinanciar = $precioFinal - $engancheMonto;

            $tasaMensual = $tasaInteres / 12 / 100;
            $mensualidad = 0;

            if ($montoFinanciar > 0 && $plazoMeses > 0) {
                if ($configuracion->meses_sin_intereses > 0) {
                    $mensualidad = $montoFinanciar / $plazoMeses;
                } else if ($tasaMensual > 0) {
                    $mensualidad = $montoFinanciar * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / (pow(1 + $tasaMensual, $plazoMeses) - 1);
                } else {
                    $mensualidad = $montoFinanciar / $plazoMeses;
                }
            }

            // Simular datos para compatibilidad
            $loteSimulado = [
                'id' => 0,
                'clave' => 'PRESUPUESTO-' . date('YmdHis'),
                'proyecto_nombre' => 'Presupuesto',
                'manzana_nombre' => 'N/A',
                'area_total' => 0,
                'tipo_lote_nombre' => 'Presupuesto',
                'categoria_nombre' => 'General',
                'frente' => 0,
                'fondo' => 0,
                'lateral_izquierda' => 0,
                'lateral_derecha' => 0,
                'precio_metro' => 0,
                'precio_total' => $precioLote
            ];

            $clienteSimulado = [
                'id' => 0,
                'nombre_completo' => $nombreCliente,
                'email' => $emailCliente,
                'telefono' => $telefonoCliente,
                'rfc' => 'N/A'
            ];

            $datos = [
                'precio_total' => $precioLote,
                'descuento_monto' => $descuentoMonto,
                'precio_final' => $precioFinal,
                'enganche_monto' => $engancheMonto,
                'monto_financiar' => $montoFinanciar,
                'mensualidad' => $mensualidad,
                'plazo_meses' => $plazoMeses,
                'tasa_interes' => $tasaInteres,
                'enganche_porcentaje' => $enganchePorcentaje,
                'descuento_porcentaje' => $descuentoPorcentaje
            ];

            // Preparar datos para PdfService
            $datosPresupuesto = [
                'lote' => $loteSimulado,
                'cliente' => $clienteSimulado,
                'configuracion' => $configuracion,
                'datos' => $datos,
                'pagos_anticipados' => $pagosAnticipados,
                'es_presupuesto' => true,
                'es_pdf' => true
            ];

            // Generar PDF
            $resultadoPdf = $this->pdfService->generarPresupuestoPdf($datosPresupuesto);
            
            if (!$resultadoPdf['success']) {
                return redirect()->back()->with('error', 'Error al generar PDF: ' . $resultadoPdf['error']);
            }

            // Configurar y enviar email
            $email = \Config\Services::email();
            
            // Usar configuración desde .env
            $emailConfig = new \Config\Email();
            $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
            $email->setTo($emailDestino);
            $email->setSubject($asunto);
            
            // Preparar datos para la vista del email
            $datosEmail = [
                'cliente' => $clienteSimulado,
                'datos' => $datos,
                'mensaje' => $mensaje,
                'fecha' => date('d/m/Y'),
                'folio' => strtoupper(substr(uniqid(), -8))
            ];
            
            // Renderizar vista del email
            $htmlEmail = view('emails/presupuesto', $datosEmail);
            $email->setMessage($htmlEmail);
            
            // Adjuntar PDF
            $email->attach($resultadoPdf['content'], 'attachment', $resultadoPdf['filename'], 'application/pdf');
            
            // Enviar email
            if ($email->send()) {
                return redirect()->back()->with('success', 'Presupuesto enviado exitosamente a ' . $emailDestino);
            } else {
                log_message('error', 'Error enviando email: ' . $email->printDebugger());
                return redirect()->back()->with('error', 'Error al enviar el email. Inténtelo nuevamente.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error enviando presupuesto por email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al enviar presupuesto: ' . $e->getMessage());
        }
    }
}