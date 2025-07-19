<?php

namespace App\Controllers;

/**
 * Controlador de Documentos Seguros
 * Enfoque estándar de CodeIgniter 4
 * Basado en el patrón exitoso de MiPerfilController
 */
class SecureFileController extends BaseController
{
    public function __construct()
    {
        helper('documento');
    }

    /**
     * Ver documento en el navegador
     * Ruta: documento/ver/{tipo}/{id}
     */
    public function verDocumento($tipo, $documentoId)
    {
        if (!auth()->loggedIn()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $documento = $this->obtenerDocumento($documentoId, $tipo);
        
        if (!$documento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rutaArchivo = $documento['ruta_archivo'];
        
        if (!file_exists($rutaArchivo)) {
            log_message('error', "Archivo no encontrado: {$rutaArchivo}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mimeType = $documento['mime_type'] ?? mime_content_type($rutaArchivo) ?? 'application/octet-stream';
        
        $this->response->setHeader('Content-Type', $mimeType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $documento['nombre_archivo'] . '"');
        $this->response->setHeader('Content-Length', filesize($rutaArchivo));
        
        return $this->response->setBody(file_get_contents($rutaArchivo));
    }

    /**
     * Descargar documento
     * Ruta: documento/descargar/{tipo}/{id}
     */
    public function descargarDocumento($tipo, $documentoId)
    {
        if (!auth()->loggedIn()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $documento = $this->obtenerDocumento($documentoId, $tipo);
        
        if (!$documento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rutaArchivo = $documento['ruta_archivo'];
        
        if (!file_exists($rutaArchivo)) {
            log_message('error', "Archivo no encontrado para descarga: {$rutaArchivo}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mimeType = $documento['mime_type'] ?? mime_content_type($rutaArchivo) ?? 'application/octet-stream';
        
        $this->response->setHeader('Content-Type', $mimeType);
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $documento['nombre_archivo'] . '"');
        $this->response->setHeader('Content-Length', filesize($rutaArchivo));
        $this->response->setHeader('Cache-Control', 'must-revalidate');
        $this->response->setHeader('Pragma', 'public');
        
        return $this->response->setBody(file_get_contents($rutaArchivo));
    }

    /**
     * Obtener documento según tipo - Patrón estándar CodeIgniter 4
     */
    private function obtenerDocumento($documentoId, $tipo)
    {
        $user = auth()->user();
        
        switch ($tipo) {
            case 'staff':
                $staffModel = new \App\Models\StaffModel();
                $staff = $staffModel->where('user_id', $user->id)->first();
                
                if (!$staff) {
                    return null;
                }
                
                $docModel = new \App\Models\DocumentoStaffModel();
                return $docModel->where('id', $documentoId)
                               ->where('staff_id', $staff->id)
                               ->first();
                
            case 'cliente':
                if (!$user->inGroup('admin')) {
                    return null;
                }
                
                $docModel = new \App\Models\DocumentoClienteModel();
                return $docModel->find($documentoId);
                
            case 'venta':
                if (!$user->inGroup('admin', 'vendedor', 'supervendedor')) {
                    return null;
                }
                
                $docModel = new \App\Models\VentaDocumentoModel();
                return $docModel->find($documentoId);
                
            default:
                return null;
        }
    }

    /**
     * Compatibilidad con avatares
     */
    public function avatar($encodedPath)
    {
        if (!auth()->loggedIn()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        $filePath = base64_decode($encodedPath);
        $fullPath = FCPATH . 'uploads/' . $filePath;
        
        if (!file_exists($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        $mimeType = mime_content_type($fullPath);
        
        return $this->response
            ->setContentType($mimeType)
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($fullPath));
    }
}