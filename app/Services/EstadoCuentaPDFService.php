<?php

namespace App\Services;

use App\Models\VentaModel;
use App\Models\TablaAmortizacionModel;
use App\Models\PagoVentaModel;
use App\Models\ClienteModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Servicio para generar PDFs de Estado de Cuenta
 * Aplica metodología DRY reutilizando helpers existentes
 */
class EstadoCuentaPDFService
{
    private Dompdf $dompdf;
    private VentaModel $ventaModel;
    private TablaAmortizacionModel $tablaAmortizacionModel;
    private PagoVentaModel $pagoVentaModel;
    private ClienteModel $clienteModel;

    public function __construct()
    {
        $this->ventaModel = new VentaModel();
        $this->tablaAmortizacionModel = new TablaAmortizacionModel();
        $this->pagoVentaModel = new PagoVentaModel();
        $this->clienteModel = new ClienteModel();
        
        $this->configurarDompdf();
    }

    private function configurarDompdf(): void
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        
        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generar PDF del estado de cuenta general del cliente
     */
    public function generarEstadoCuentaCliente(int $clienteId): array
    {
        try {
            // Obtener datos del cliente usando helper existente
            $resumenCliente = generar_resumen_cliente($clienteId);
            
            if (empty($resumenCliente['propiedades'])) {
                throw new \Exception('Cliente sin propiedades activas');
            }

            // Generar HTML del PDF
            $html = $this->generarHTMLEstadoCuentaCliente($resumenCliente);
            
            // Generar PDF
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4', 'portrait');
            $this->dompdf->render();
            
            $nombreArchivo = 'estado_cuenta_cliente_' . $clienteId . '_' . date('Y-m-d') . '.pdf';
            
            return [
                'success' => true,
                'pdf_content' => $this->dompdf->output(),
                'filename' => $nombreArchivo,
                'size' => strlen($this->dompdf->output())
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF estado cuenta cliente: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar PDF del estado de cuenta de una venta específica
     */
    public function generarEstadoCuentaVenta(int $ventaId): array
    {
        try {
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta) {
                throw new \Exception('Venta no encontrada');
            }

            $cliente = $this->clienteModel->find($venta->cliente_id);
            if (!$cliente) {
                throw new \Exception('Cliente no encontrado');
            }

            // Obtener datos de la venta
            $tablaAmortizacion = $this->tablaAmortizacionModel
                ->where('venta_id', $ventaId)
                ->orderBy('numero_pago', 'ASC')
                ->findAll();

            $historialPagos = $this->pagoVentaModel
                ->where('venta_id', $ventaId)
                ->orderBy('fecha_pago', 'DESC')
                ->findAll();

            // Calcular resumen financiero usando Entity
            $ventaEntity = $venta->getResumenFinanciero();

            $datos = [
                'venta' => $venta,
                'cliente' => $cliente,
                'tabla_amortizacion' => $tablaAmortizacion,
                'historial_pagos' => $historialPagos,
                'resumen_financiero' => $ventaEntity
            ];

            // Generar HTML del PDF
            $html = $this->generarHTMLEstadoCuentaVenta($datos);
            
            // Generar PDF
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4', 'portrait');
            $this->dompdf->render();
            
            $nombreArchivo = 'estado_cuenta_' . $venta->folio_venta . '_' . date('Y-m-d') . '.pdf';
            
            return [
                'success' => true,
                'pdf_content' => $this->dompdf->output(),
                'filename' => $nombreArchivo,
                'size' => strlen($this->dompdf->output())
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF estado cuenta venta: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar PDF de comprobante de pago
     */
    public function generarComprobantePago(int $pagoId): array
    {
        try {
            $pago = $this->pagoVentaModel->find($pagoId);
            if (!$pago) {
                throw new \Exception('Pago no encontrado');
            }

            // Obtener datos relacionados
            $venta = $this->ventaModel->find($pago->venta_id);
            $cliente = $this->clienteModel->find($venta->cliente_id);
            $mensualidad = $this->tablaAmortizacionModel->find($pago->tabla_amortizacion_id);

            $datos = [
                'pago' => $pago,
                'venta' => $venta,
                'cliente' => $cliente,
                'mensualidad' => $mensualidad
            ];

            // Generar HTML del comprobante usando helper existente
            $html = $this->generarHTMLComprobantePago($datos);
            
            // Generar PDF
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper('A4', 'portrait');
            $this->dompdf->render();
            
            $nombreArchivo = 'comprobante_' . $pago->folio_pago . '.pdf';
            
            return [
                'success' => true,
                'pdf_content' => $this->dompdf->output(),
                'filename' => $nombreArchivo,
                'size' => strlen($this->dompdf->output())
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error generando comprobante PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar HTML para estado de cuenta del cliente
     */
    private function generarHTMLEstadoCuentaCliente(array $resumenCliente): string
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Estado de Cuenta - ' . esc($resumenCliente['cliente']->nombres) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .logo { max-height: 60px; }
                .cliente-info { background: #f5f5f5; padding: 15px; margin-bottom: 20px; }
                .resumen-financiero { background: #e3f2fd; padding: 15px; margin-bottom: 20px; }
                .propiedad { border: 1px solid #ddd; margin-bottom: 20px; padding: 15px; }
                .tabla { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .tabla th, .tabla td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .tabla th { background-color: #f2f2f2; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-success { color: #28a745; }
                .text-warning { color: #ffc107; }
                .text-danger { color: #dc3545; }
                .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
                .page-break { page-break-before: always; }
            </style>
        </head>
        <body>';

        // Header
        $html .= '<div class="header">
            <h1>ESTADO DE CUENTA</h1>
            <p>Fecha de generación: ' . date('d/m/Y H:i') . '</p>
        </div>';

        // Información del cliente
        $cliente = $resumenCliente['cliente'];
        $html .= '<div class="cliente-info">
            <h3>Información del Cliente</h3>
            <table class="tabla">
                <tr>
                    <th width="150">Nombre:</th>
                    <td>' . esc($cliente->nombres . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) . '</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>' . esc($cliente->email) . '</td>
                </tr>
                <tr>
                    <th>Teléfono:</th>
                    <td>' . esc($cliente->telefono) . '</td>
                </tr>
                <tr>
                    <th>RFC:</th>
                    <td>' . esc($cliente->rfc ?? 'No registrado') . '</td>
                </tr>
            </table>
        </div>';

        // Resumen financiero general
        $resumen = $resumenCliente['resumen_financiero'];
        $html .= '<div class="resumen-financiero">
            <h3>Resumen Financiero General</h3>
            <table class="tabla">
                <tr>
                    <th>Total Propiedades:</th>
                    <td class="text-right">' . count($resumenCliente['propiedades']) . '</td>
                </tr>
                <tr>
                    <th>Total Pagado:</th>
                    <td class="text-right text-success">$' . number_format($resumen['total_pagado'], 2) . '</td>
                </tr>
                <tr>
                    <th>Saldo Pendiente:</th>
                    <td class="text-right text-warning">$' . number_format($resumen['saldo_pendiente'], 2) . '</td>
                </tr>
                <tr>
                    <th>Mensualidades Vencidas:</th>
                    <td class="text-right text-danger">' . ($resumen['mensualidades_vencidas'] ?? 0) . '</td>
                </tr>
            </table>
        </div>';

        // Detalle por propiedad
        foreach ($resumenCliente['propiedades'] as $index => $propiedad) {
            if ($index > 0) {
                $html .= '<div class="page-break"></div>';
            }
            
            $html .= '<div class="propiedad">
                <h3>Propiedad: ' . esc($propiedad->lote_clave) . '</h3>
                <table class="tabla">
                    <tr>
                        <th>Folio Venta:</th>
                        <td>' . esc($propiedad->folio_venta) . '</td>
                        <th>Tipo Venta:</th>
                        <td>' . ucfirst($propiedad->tipo_venta) . '</td>
                    </tr>
                    <tr>
                        <th>Proyecto:</th>
                        <td>' . esc($propiedad->proyecto_nombre ?? 'N/A') . '</td>
                        <th>Precio Final:</th>
                        <td>$' . number_format($propiedad->precio_venta_final, 2) . '</td>
                    </tr>
                </table>';

            // Solo mostrar tabla de amortización si es financiado
            if ($propiedad->tipo_venta === 'financiado') {
                $tablaAmortizacion = $this->tablaAmortizacionModel
                    ->where('venta_id', $propiedad->id)
                    ->orderBy('numero_pago', 'ASC')
                    ->findAll();

                $html .= '<h4>Tabla de Amortización</h4>
                    <table class="tabla">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vencimiento</th>
                                <th>Monto</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th>Días Atraso</th>
                            </tr>
                        </thead>
                        <tbody>';

                foreach ($tablaAmortizacion as $mensualidad) {
                    $estadoClass = '';
                    switch ($mensualidad->estatus) {
                        case 'pagada':
                            $estadoClass = 'text-success';
                            break;
                        case 'vencida':
                            $estadoClass = 'text-danger';
                            break;
                        default:
                            $estadoClass = '';
                    }

                    $html .= '<tr>
                        <td>' . $mensualidad->numero_pago . '</td>
                        <td>' . date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) . '</td>
                        <td>$' . number_format($mensualidad->monto_total, 2) . '</td>
                        <td>$' . number_format($mensualidad->saldo_pendiente_total, 2) . '</td>
                        <td class="' . $estadoClass . '">' . ucfirst($mensualidad->estatus) . '</td>
                        <td>' . ($mensualidad->dias_atraso > 0 ? $mensualidad->dias_atraso . ' días' : '-') . '</td>
                    </tr>';
                }

                $html .= '</tbody></table>';
            }

            $html .= '</div>';
        }

        // Footer
        $html .= '<div class="footer">
            <p>Este documento fue generado automáticamente el ' . date('d/m/Y H:i:s') . '</p>
            <p>Para cualquier aclaración, contacte a nuestro equipo de atención al cliente.</p>
        </div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Generar HTML para estado de cuenta de una venta específica
     */
    private function generarHTMLEstadoCuentaVenta(array $datos): string
    {
        $venta = $datos['venta'];
        $cliente = $datos['cliente'];
        $tablaAmortizacion = $datos['tabla_amortizacion'];
        $historialPagos = $datos['historial_pagos'];
        $resumen = $datos['resumen_financiero'];

        // Similar estructura pero enfocada en una sola venta
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Estado de Cuenta - ' . esc($venta->folio_venta) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .info-section { background: #f5f5f5; padding: 15px; margin-bottom: 20px; }
                .tabla { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .tabla th, .tabla td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .tabla th { background-color: #f2f2f2; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-success { color: #28a745; }
                .text-warning { color: #ffc107; }
                .text-danger { color: #dc3545; }
                .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
            </style>
        </head>
        <body>';

        // Contenido similar pero específico para una venta...
        $html .= '<div class="header">
            <h1>ESTADO DE CUENTA</h1>
            <h2>' . esc($venta->lote_clave) . ' - ' . esc($venta->folio_venta) . '</h2>
            <p>Fecha de generación: ' . date('d/m/Y H:i') . '</p>
        </div>';

        // Resto del HTML específico para la venta...
        
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Generar HTML para comprobante de pago
     */
    private function generarHTMLComprobantePago(array $datos): string
    {
        // Reutilizar la lógica del helper de recibos existente
        return generar_template_comprobante_pago($datos);
    }

    /**
     * Guardar PDF en el sistema de archivos
     */
    public function guardarPDF(string $contenidoPDF, string $nombreArchivo, string $directorio = 'estados_cuenta'): array
    {
        try {
            $rutaCompleta = WRITEPATH . 'documentos/' . $directorio;
            
            if (!is_dir($rutaCompleta)) {
                mkdir($rutaCompleta, 0755, true);
            }
            
            $rutaArchivo = $rutaCompleta . '/' . $nombreArchivo;
            
            if (file_put_contents($rutaArchivo, $contenidoPDF) !== false) {
                return [
                    'success' => true,
                    'path' => $rutaArchivo,
                    'url' => base_url('documentos/' . $directorio . '/' . $nombreArchivo)
                ];
            }
            
            throw new \Exception('No se pudo guardar el archivo PDF');

        } catch (\Exception $e) {
            log_message('error', 'Error guardando PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}