<?php

namespace App\Services;

use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Email\Email;

/**
 * Servicio para gestión segura de cambio de email de autenticación
 * 
 * Maneja todo el flujo de cambio de email incluyendo:
 * - Validación de email único
 * - Generación de tokens de verificación
 * - Envío de emails de confirmación
 * - Actualización segura en auth_identities
 */
class EmailChangeService
{
    protected BaseConnection $db;
    protected UserModel $userModel;
    protected Email $email;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new UserModel();
        $this->email = \Config\Services::email();
    }

    /**
     * Iniciar proceso de cambio de email
     * 
     * @param int $userId ID del usuario
     * @param string $nuevoEmail Nuevo email a establecer
     * @return array Resultado del proceso
     */
    public function iniciarCambioEmail(int $userId, string $nuevoEmail): array
    {
        try {
            // 1. Validaciones básicas
            $validacion = $this->validarCambioEmail($userId, $nuevoEmail);
            if (!$validacion['valido']) {
                return $validacion;
            }

            // 2. Generar token de verificación
            $token = $this->generarTokenVerificacion();
            
            // 3. Guardar solicitud de cambio en auth_identities
            $resultado = $this->guardarSolicitudCambio($userId, $nuevoEmail, $token);
            if (!$resultado) {
                return [
                    'exito' => false,
                    'mensaje' => 'Error al guardar la solicitud de cambio'
                ];
            }

            // 4. Enviar email de verificación
            $envioEmail = $this->enviarEmailVerificacion($nuevoEmail, $token, $userId);
            if (!$envioEmail) {
                return [
                    'exito' => false,
                    'mensaje' => 'Error al enviar el email de verificación'
                ];
            }

            log_message('info', "Solicitud de cambio de email iniciada para usuario {$userId} hacia {$nuevoEmail}");

            return [
                'exito' => true,
                'mensaje' => 'Se ha enviado un email de verificación a ' . $nuevoEmail,
                'token' => $token // Solo para desarrollo/debug
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error en iniciarCambioEmail: ' . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error interno del sistema'
            ];
        }
    }

    /**
     * Verificar token y completar cambio de email
     * 
     * @param string $token Token de verificación
     * @return array Resultado de la verificación
     */
    public function verificarYCompletarCambio(string $token): array
    {
        try {
            // 1. Buscar solicitud por token
            $solicitud = $this->obtenerSolicitudPorToken($token);
            if (!$solicitud) {
                return [
                    'exito' => false,
                    'mensaje' => 'Token inválido o expirado'
                ];
            }

            // 2. Verificar que el token no haya expirado
            if ($this->tokenExpirado($solicitud)) {
                $this->eliminarSolicitud($token);
                return [
                    'exito' => false,
                    'mensaje' => 'El token ha expirado. Solicita un nuevo cambio de email.'
                ];
            }

            // 3. Realizar el cambio definitivo
            $this->db->transStart();

            // Actualizar email en auth_identities
            $actualizacion = $this->actualizarEmailAuth($solicitud->user_id, $solicitud->secret);
            if (!$actualizacion) {
                $this->db->transRollback();
                return [
                    'exito' => false,
                    'mensaje' => 'Error al actualizar las credenciales'
                ];
            }

            // Eliminar solicitud temporal
            $this->eliminarSolicitud($token);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Error en transacción de cambio de email');
                return [
                    'exito' => false,
                    'mensaje' => 'Error en la base de datos'
                ];
            }

            log_message('info', "Cambio de email completado para usuario {$solicitud->user_id} hacia {$solicitud->secret}");

            return [
                'exito' => true,
                'mensaje' => 'Email actualizado correctamente',
                'nuevo_email' => $solicitud->secret
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error en verificarYCompletarCambio: ' . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error interno del sistema'
            ];
        }
    }

    /**
     * Validar si el cambio de email es posible
     */
    private function validarCambioEmail(int $userId, string $nuevoEmail): array
    {
        // Validar formato de email
        if (!filter_var($nuevoEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'valido' => false,
                'exito' => false,
                'mensaje' => 'El formato del email no es válido'
            ];
        }

        // Verificar que el usuario existe
        $usuario = $this->userModel->find($userId);
        if (!$usuario) {
            return [
                'valido' => false,
                'exito' => false,
                'mensaje' => 'Usuario no encontrado'
            ];
        }

        // Obtener email actual
        $emailActual = $this->obtenerEmailActual($userId);
        if ($emailActual === $nuevoEmail) {
            return [
                'valido' => false,
                'exito' => false,
                'mensaje' => 'El nuevo email es igual al actual'
            ];
        }

        // Verificar que el nuevo email no esté en uso
        if ($this->emailYaEnUso($nuevoEmail)) {
            return [
                'valido' => false,
                'exito' => false,
                'mensaje' => 'Este email ya está registrado por otro usuario'
            ];
        }

        // Verificar que no hay solicitud pendiente para este usuario
        if ($this->tieneSolicitudPendiente($userId)) {
            return [
                'valido' => false,
                'exito' => false,
                'mensaje' => 'Ya tienes una solicitud de cambio de email pendiente'
            ];
        }

        return [
            'valido' => true,
            'email_actual' => $emailActual
        ];
    }

    /**
     * Generar token único para verificación
     */
    private function generarTokenVerificacion(): string
    {
        return bin2hex(random_bytes(32)) . '_' . time();
    }

    /**
     * Guardar solicitud temporal en auth_identities
     */
    private function guardarSolicitudCambio(int $userId, string $nuevoEmail, string $token): bool
    {
        $data = [
            'user_id' => $userId,
            'type' => 'email_change_request',
            'name' => 'cambio_email',
            'secret' => $nuevoEmail,
            'secret2' => $token,
            'expires' => date('Y-m-d H:i:s', strtotime('+2 hours')), // Token válido por 2 horas
            'force_reset' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('auth_identities')->insert($data);
    }

    /**
     * Enviar email de verificación
     */
    private function enviarEmailVerificacion(string $nuevoEmail, string $token, int $userId): bool
    {
        try {
            $enlaceVerificacion = site_url("verificar-cambio-email?token=" . urlencode($token));
            
            $usuario = $this->userModel->find($userId);
            $nombreUsuario = $usuario->username ?? 'Usuario';

            $this->email->setFrom('noreply@anvarinmobiliaria.com', 'ANVAR Inmobiliaria');
            $this->email->setTo($nuevoEmail);
            $this->email->setSubject('Verificación de cambio de email - ANVAR');

            $mensaje = $this->generarMensajeEmail($nombreUsuario, $enlaceVerificacion, $nuevoEmail);
            $this->email->setMessage($mensaje);

            return $this->email->send();

        } catch (\Exception $e) {
            log_message('error', 'Error enviando email de verificación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar contenido HTML del email
     */
    private function generarMensajeEmail(string $nombreUsuario, string $enlaceVerificacion, string $nuevoEmail): string
    {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
                .logo { color: #007bff; font-size: 24px; font-weight: bold; }
                .content { line-height: 1.6; color: #333; }
                .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; text-align: center; }
                .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>ANVAR INMOBILIARIA</div>
                    <h2>Verificación de Cambio de Email</h2>
                </div>
                
                <div class='content'>
                    <p>Hola <strong>{$nombreUsuario}</strong>,</p>
                    
                    <p>Hemos recibido una solicitud para cambiar tu email de acceso al sistema ANVAR a:</p>
                    <p><strong>{$nuevoEmail}</strong></p>
                    
                    <p>Para confirmar este cambio, haz clic en el siguiente botón:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$enlaceVerificacion}' class='button'>Confirmar Cambio de Email</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Importante:</strong>
                        <ul>
                            <li>Este enlace es válido por <strong>2 horas</strong></li>
                            <li>Si no solicitaste este cambio, ignora este email</li>
                            <li>Después de confirmar, usarás este nuevo email para iniciar sesión</li>
                        </ul>
                    </div>
                    
                    <p>Si no puedes hacer clic en el botón, copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all; color: #007bff;'>{$enlaceVerificacion}</p>
                </div>
                
                <div class='footer'>
                    <p>Este email fue enviado automáticamente. No respondas a este mensaje.</p>
                    <p>© " . date('Y') . " ANVAR Inmobiliaria - Sistema de Gestión</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Obtener email actual del usuario
     */
    private function obtenerEmailActual(int $userId): ?string
    {
        $identity = $this->db->table('auth_identities')
                            ->where('user_id', $userId)
                            ->where('type', 'email_password')
                            ->get()
                            ->getRow();

        return $identity ? $identity->secret : null;
    }

    /**
     * Verificar si email ya está en uso
     */
    private function emailYaEnUso(string $email): bool
    {
        $count = $this->db->table('auth_identities')
                         ->where('secret', $email)
                         ->where('type', 'email_password')
                         ->countAllResults();

        return $count > 0;
    }

    /**
     * Verificar si usuario tiene solicitud pendiente
     */
    private function tieneSolicitudPendiente(int $userId): bool
    {
        $count = $this->db->table('auth_identities')
                         ->where('user_id', $userId)
                         ->where('type', 'email_change_request')
                         ->where('expires >', date('Y-m-d H:i:s'))
                         ->countAllResults();

        return $count > 0;
    }

    /**
     * Obtener solicitud por token
     */
    private function obtenerSolicitudPorToken(string $token): ?object
    {
        return $this->db->table('auth_identities')
                       ->where('secret2', $token)
                       ->where('type', 'email_change_request')
                       ->get()
                       ->getRow();
    }

    /**
     * Verificar si token expiró
     */
    private function tokenExpirado(object $solicitud): bool
    {
        return strtotime($solicitud->expires) < time();
    }

    /**
     * Actualizar email en auth_identities principal
     */
    private function actualizarEmailAuth(int $userId, string $nuevoEmail): bool
    {
        $affected = $this->db->table('auth_identities')
                            ->where('user_id', $userId)
                            ->where('type', 'email_password')
                            ->update(['secret' => $nuevoEmail, 'updated_at' => date('Y-m-d H:i:s')]);

        return $affected > 0;
    }

    /**
     * Eliminar solicitud temporal
     */
    private function eliminarSolicitud(string $token): bool
    {
        return $this->db->table('auth_identities')
                       ->where('secret2', $token)
                       ->where('type', 'email_change_request')
                       ->delete();
    }

    /**
     * Limpiar solicitudes expiradas (para tarea cron)
     */
    public function limpiarSolicitudesExpiradas(): int
    {
        $eliminadas = $this->db->table('auth_identities')
                              ->where('type', 'email_change_request')
                              ->where('expires <', date('Y-m-d H:i:s'))
                              ->delete();

        if ($eliminadas > 0) {
            log_message('info', "Limpiadas {$eliminadas} solicitudes de cambio de email expiradas");
        }

        return $eliminadas;
    }

    /**
     * Obtener solicitudes pendientes de un usuario
     */
    public function obtenerSolicitudesPendientes(int $userId): array
    {
        $solicitudes = $this->db->table('auth_identities')
                               ->where('user_id', $userId)
                               ->where('type', 'email_change_request')
                               ->where('expires >', date('Y-m-d H:i:s'))
                               ->get()
                               ->getResultArray();

        return $solicitudes;
    }

    /**
     * Cancelar solicitud pendiente
     */
    public function cancelarSolicitud(int $userId): bool
    {
        $eliminadas = $this->db->table('auth_identities')
                              ->where('user_id', $userId)
                              ->where('type', 'email_change_request')
                              ->delete();

        if ($eliminadas > 0) {
            log_message('info', "Solicitud de cambio de email cancelada para usuario {$userId}");
        }

        return $eliminadas > 0;
    }
}