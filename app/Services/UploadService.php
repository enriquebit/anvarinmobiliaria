<?php

namespace App\Services;

use CodeIgniter\Files\File;

class UploadService
{
    private $uploadPath;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    private $maxSize = 10485760; // 10MB
    private $maxSizeFoto = 2097152; // 2MB para fotos de perfil

    public function __construct()
    {
        $this->uploadPath = FCPATH . 'uploads/';
    }

    /**
     * Subir foto de perfil
     */
    public function subirFotoPerfil($archivo, string $rfc, string $tipoUsuario = 'clientes'): array
    {
        // Validar que sea imagen
        $allowedImageTypes = ['jpg', 'jpeg', 'png'];
        
        if (!$archivo->isValid()) {
            return ['success' => false, 'error' => 'Archivo no válido'];
        }

        $extension = strtolower($archivo->getClientExtension());
        if (!in_array($extension, $allowedImageTypes)) {
            return ['success' => false, 'error' => 'Solo se permiten imágenes JPG, JPEG y PNG'];
        }

        if ($archivo->getSize() > $this->maxSizeFoto) {
            return ['success' => false, 'error' => 'La imagen no puede ser mayor a 2MB'];
        }

        // Crear directorio si no existe
        $userPath = $this->uploadPath . $tipoUsuario . '/' . $rfc . '/';
        if (!is_dir($userPath)) {
            mkdir($userPath, 0755, true);
        }

        // Generar nombre único
        $nombreArchivo = 'foto_perfil_' . $rfc . '.' . $extension;
        $rutaCompleta = $userPath . $nombreArchivo;

        // Mover archivo
        if ($archivo->move($userPath, $nombreArchivo)) {
            // Generar thumbnail 150x150
            $this->generarThumbnail($rutaCompleta, $userPath . 'foto_perfil_' . $rfc . '_thumb.jpg');
            
            return [
                'success' => true,
                'ruta' => 'uploads/' . $tipoUsuario . '/' . $rfc . '/' . $nombreArchivo,
                'nombre' => $nombreArchivo
            ];
        }

        return ['success' => false, 'error' => 'Error al subir el archivo'];
    }

    /**
     * Subir documento (INE, comprobantes, etc.)
     */
    public function subirDocumento($archivo, string $rfc, string $tipoDocumento, string $tipoUsuario = 'clientes'): array
    {
        if (!$archivo->isValid()) {
            return ['success' => false, 'error' => 'Archivo no válido'];
        }

        $extension = strtolower($archivo->getClientExtension());
        if (!in_array($extension, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Solo se permiten archivos JPG, PNG y PDF'];
        }

        if ($archivo->getSize() > $this->maxSize) {
            return ['success' => false, 'error' => 'El archivo no puede ser mayor a 10MB'];
        }

        // Crear directorio si no existe
        $userPath = $this->uploadPath . $tipoUsuario . '/' . $rfc . '/';
        if (!is_dir($userPath)) {
            mkdir($userPath, 0755, true);
        }

        // Generar nombre único
        $nombreArchivo = $rfc . '_' . $tipoDocumento . '.' . $extension;
        $rutaCompleta = $userPath . $nombreArchivo;

        // Mover archivo
        if ($archivo->move($userPath, $nombreArchivo)) {
            return [
                'success' => true,
                'ruta' => 'uploads/' . $tipoUsuario . '/' . $rfc . '/' . $nombreArchivo,
                'nombre' => $nombreArchivo,
                'tamano' => $archivo->getSize(),
                'extension' => $extension,
                'mime_type' => $archivo->getMimeType()
            ];
        }

        return ['success' => false, 'error' => 'Error al subir el archivo'];
    }

    /**
     * Generar thumbnail para foto de perfil
     */
    private function generarThumbnail(string $rutaOriginal, string $rutaThumbnail): bool
    {
        try {
            $service = \Config\Services::image();
            
            $service->withFile($rutaOriginal)
                   ->fit(150, 150, 'center')
                   ->save($rutaThumbnail, 80); // 80% calidad
                   
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error generando thumbnail: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar archivo del servidor
     */
    public function eliminarArchivo(string $rutaArchivo): bool
    {
        $rutaCompleta = FCPATH . $rutaArchivo;
        
        if (file_exists($rutaCompleta)) {
            return unlink($rutaCompleta);
        }
        
        return false;
    }

    /**
     * Validar imagen y redimensionar si es necesario
     */
    public function validarYRedimensionarImagen($archivo, int $maxWidth = 400, int $maxHeight = 400): array
    {
        if (!$archivo->isValid()) {
            return ['success' => false, 'error' => 'Archivo no válido'];
        }

        $allowedImageTypes = ['jpg', 'jpeg', 'png'];
        $extension = strtolower($archivo->getClientExtension());
        
        if (!in_array($extension, $allowedImageTypes)) {
            return ['success' => false, 'error' => 'Solo se permiten imágenes JPG, JPEG y PNG'];
        }

        try {
            $service = \Config\Services::image();
            $imageInfo = getimagesize($archivo->getTempName());
            
            if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
                // Redimensionar manteniendo aspecto
                $service->withFile($archivo->getTempName())
                       ->resize($maxWidth, $maxHeight, true, 'auto')
                       ->save($archivo->getTempName());
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error procesando imagen: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener tipos MIME permitidos
     */
    public function getTiposMimePermitidos(): array
    {
        return [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 
            'png' => 'image/png',
            'pdf' => 'application/pdf'
        ];
    }

    /**
     * Validar tamaño de archivo
     */
    public function validarTamanoArchivo($archivo, string $tipoArchivo = 'documento'): bool
    {
        $maxSize = ($tipoArchivo === 'foto') ? $this->maxSizeFoto : $this->maxSize;
        return $archivo->getSize() <= $maxSize;
    }
}