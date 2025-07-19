<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\EmailChangeService;

/**
 * Controlador para verificación de cambios de email
 * 
 * Maneja las rutas públicas para verificar tokens de cambio de email
 * que llegan desde los enlaces enviados por correo electrónico
 */
class EmailChangeController extends BaseController
{
    protected EmailChangeService $emailChangeService;

    public function __construct()
    {
        $this->emailChangeService = new EmailChangeService();
    }

    /**
     * Verificar token de cambio de email
     * Ruta pública accesible desde enlaces de email
     * 
     * URL: /verificar-cambio-email?token=xxxxx
     */
    public function verificarCambio()
    {
        $token = $this->request->getGet('token');
        
        if (empty($token)) {
            return $this->mostrarResultado(
                false, 
                'Token no proporcionado',
                'El enlace de verificación no es válido.'
            );
        }

        // Procesar verificación
        $resultado = $this->emailChangeService->verificarYCompletarCambio($token);

        if ($resultado['exito']) {
            return $this->mostrarResultado(
                true,
                'Email actualizado correctamente',
                'Tu email de acceso ha sido cambiado a: ' . $resultado['nuevo_email'] . '. Ya puedes iniciar sesión con tu nuevo email.',
                $resultado['nuevo_email']
            );
        } else {
            return $this->mostrarResultado(
                false,
                'Error en la verificación',
                $resultado['mensaje']
            );
        }
    }

    /**
     * Mostrar página de resultado de verificación
     */
    private function mostrarResultado(bool $exito, string $titulo, string $mensaje, string $nuevoEmail = null)
    {
        $data = [
            'titulo' => $titulo,
            'exito' => $exito,
            'mensaje' => $mensaje,
            'nuevo_email' => $nuevoEmail,
            'breadcrumb' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Verificación de Email', 'url' => '']
            ]
        ];

        return view('auth/verificacion_email_resultado', $data);
    }

    /**
     * Página de información sobre cambio de email
     * Para usuarios que lleguen sin token
     */
    public function informacion()
    {
        $data = [
            'titulo' => 'Cambio de Email',
            'breadcrumb' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Cambio de Email', 'url' => '']
            ]
        ];

        return view('auth/cambio_email_info', $data);
    }
}