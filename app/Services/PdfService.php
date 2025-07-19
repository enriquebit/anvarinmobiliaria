<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private $dompdf;
    private $options;

    public function __construct()
    {
        // Configurar opciones de Dompdf
        $this->options = new Options();
        $this->options->set('defaultFont', 'dejavu sans');
        $this->options->set('isRemoteEnabled', true);
        $this->options->set('isHtml5ParserEnabled', true);
        $this->options->set('isPhpEnabled', true);
        $this->options->set('debugKeepTemp', false);
        $this->options->set('debugPng', false);
        $this->options->set('debugCss', false);
        $this->options->set('debugLayout', false);
        $this->options->set('debugLayoutLines', false);
        $this->options->set('debugLayoutBlocks', false);
        $this->options->set('debugLayoutInline', false);
        $this->options->set('debugLayoutPaddingBox', false);
        $this->options->set('chroot', FCPATH);
        
        // Configurar directorio de fuentes para evitar problemas de permisos
        $fontDir = WRITEPATH . 'dompdf_fonts';
        if (!is_dir($fontDir)) {
            mkdir($fontDir, 0777, true);
        }
        $this->options->set('fontDir', $fontDir);
        $this->options->set('fontCache', $fontDir);
        $this->options->set('tempDir', WRITEPATH . 'temp');
        
        // Inicializar Dompdf
        $this->dompdf = new Dompdf($this->options);
    }

    /**
     * Generar PDF desde HTML
     *
     * @param string $html Contenido HTML
     * @param string $filename Nombre del archivo (opcional)
     * @param string $paper Tamaño del papel (letter, a4, etc.)
     * @param string $orientation Orientación (portrait, landscape)
     * @return array Datos del PDF generado
     */
    public function generateFromHtml($html, $filename = null, $paper = 'letter', $orientation = 'portrait')
    {
        try {
            // Configurar papel y orientación
            $this->dompdf->setPaper($paper, $orientation);
            
            // Cargar HTML
            $this->dompdf->loadHtml($html);
            
            // Renderizar PDF
            $this->dompdf->render();
            
            // Generar nombre de archivo si no se proporciona
            if (!$filename) {
                $filename = 'document_' . date('YmdHis') . '.pdf';
            }
            
            // Asegurar extensión .pdf
            if (!str_ends_with($filename, '.pdf')) {
                $filename .= '.pdf';
            }
            
            // Obtener contenido del PDF
            $pdfContent = $this->dompdf->output();
            
            return [
                'success' => true,
                'content' => $pdfContent,
                'filename' => $filename,
                'size' => strlen($pdfContent),
                'mime_type' => 'application/pdf'
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar PDF desde una vista de CodeIgniter
     *
     * @param string $view Nombre de la vista
     * @param array $data Datos para la vista
     * @param string $filename Nombre del archivo
     * @param string $paper Tamaño del papel
     * @param string $orientation Orientación
     * @return array Datos del PDF generado
     */
    public function generateFromView($view, $data = [], $filename = null, $paper = 'letter', $orientation = 'portrait')
    {
        try {
            // Renderizar vista como HTML
            $html = view($view, $data);
            
            // Generar PDF desde HTML
            return $this->generateFromHtml($html, $filename, $paper, $orientation);
            
        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF desde vista: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Descargar PDF directamente al navegador
     *
     * @param string $html Contenido HTML
     * @param string $filename Nombre del archivo
     * @param string $paper Tamaño del papel
     * @param string $orientation Orientación
     * @return void
     */
    public function downloadFromHtml($html, $filename = null, $paper = 'letter', $orientation = 'portrait')
    {
        try {
            $result = $this->generateFromHtml($html, $filename, $paper, $orientation);
            
            if ($result['success']) {
                // Configurar headers para descarga
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
                header('Content-Length: ' . $result['size']);
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                
                // Enviar contenido
                echo $result['content'];
                exit;
            } else {
                throw new \Exception($result['error']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error descargando PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mostrar PDF en el navegador (inline)
     *
     * @param string $html Contenido HTML
     * @param string $filename Nombre del archivo
     * @param string $paper Tamaño del papel
     * @param string $orientation Orientación
     * @return void
     */
    public function streamFromHtml($html, $filename = null, $paper = 'letter', $orientation = 'portrait')
    {
        try {
            $result = $this->generateFromHtml($html, $filename, $paper, $orientation);
            
            if ($result['success']) {
                // Configurar headers para mostrar en navegador
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $result['filename'] . '"');
                header('Content-Length: ' . $result['size']);
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                
                // Enviar contenido
                echo $result['content'];
                exit;
            } else {
                throw new \Exception($result['error']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error mostrando PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Guardar PDF en el servidor
     *
     * @param string $html Contenido HTML
     * @param string $filepath Ruta completa donde guardar
     * @param string $paper Tamaño del papel
     * @param string $orientation Orientación
     * @return array Resultado de la operación
     */
    public function saveFromHtml($html, $filepath, $paper = 'letter', $orientation = 'portrait')
    {
        try {
            $result = $this->generateFromHtml($html, null, $paper, $orientation);
            
            if ($result['success']) {
                // Crear directorio si no existe
                $directory = dirname($filepath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Guardar archivo
                $bytesWritten = file_put_contents($filepath, $result['content']);
                
                if ($bytesWritten === false) {
                    throw new \Exception('No se pudo guardar el archivo PDF');
                }
                
                return [
                    'success' => true,
                    'filepath' => $filepath,
                    'size' => $bytesWritten
                ];
            } else {
                return $result;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error guardando PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar PDF de presupuesto específicamente
     *
     * @param array $datosPresupuesto Datos completos del presupuesto
     * @return array Resultado de la operación
     */
    public function generarPresupuestoPdf($datosPresupuesto)
    {
        try {
            // Generar nombre de archivo basado en cliente y fecha
            $nombreCliente = $datosPresupuesto['cliente']['nombre_completo'];
            $nombreCliente = preg_replace('/[^a-zA-Z0-9\s]/', '', $nombreCliente);
            $nombreCliente = str_replace(' ', '_', $nombreCliente);
            $filename = 'presupuesto_' . $nombreCliente . '_' . date('YmdHis') . '.pdf';
            
            // Generar PDF usando la vista especializada
            return $this->generateFromView(
                'admin/presupuestos/tabla_presupuesto_pdf',
                $datosPresupuesto,
                $filename,
                'letter',
                'portrait'
            );
            
        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF de presupuesto: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener información del PDF generado
     *
     * @return array Información del PDF
     */
    public function getInfo()
    {
        return [
            'dompdf_version' => $this->dompdf->getVersion(),
            'options' => $this->options->getOptions(),
            'supported_formats' => ['letter', 'a4', 'legal', 'tabloid'],
            'supported_orientations' => ['portrait', 'landscape']
        ];
    }
}