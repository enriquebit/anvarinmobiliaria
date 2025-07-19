<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Libraries\HubSpotService;
use App\Models\UserModel;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

class MagicLoginController extends BaseController
{
    protected $hubSpotService;
    protected $userModel;

    public function __construct()
    {
        $this->hubSpotService = new HubSpotService();
        $this->userModel = new UserModel();
    }

    /**
     * Procesar magic link de cliente
     */
    public function login(string $token)
    {
        try {
            // Validar token
            if (empty($token) || strlen($token) < 32) {
                return $this->mostrarError('Token inválido', 'El enlace de acceso no es válido.');
            }

            // Buscar cliente en HubSpot por token
            $clienteData = $this->hubSpotService->obtenerClientePorMagicToken($token);
            
            if (!$clienteData) {
                return $this->mostrarError('Token no encontrado', 'El enlace de acceso ha expirado o no es válido.');
            }

            // Verificar si el token ha expirado
            if (isset($clienteData['properties']['magic_link_expires'])) {
                $fechaExpiracion = strtotime($clienteData['properties']['magic_link_expires']);
                if (time() > $fechaExpiracion) {
                    return $this->mostrarError('Enlace expirado', 'El enlace de acceso ha expirado. Contacte a su agente para obtener uno nuevo.');
                }
            }

            // Crear o encontrar usuario local para el cliente
            $usuario = $this->crearOActualizarUsuarioLocal($clienteData);
            
            if (!$usuario) {
                return $this->mostrarError('Error de usuario', 'No se pudo crear la sesión del cliente. Contacte a soporte.');
            }

            // Iniciar sesión automáticamente
            $auth = auth('session');
            $auth->login($usuario);

            // Actualizar estado del magic link en HubSpot
            $this->hubSpotService->actualizarEstadoMagicLink(
                $clienteData['id'], 
                'accessed', 
                date('Y-m-d H:i:s')
            );

            // Log del acceso exitoso
            log_message('info', "[MAGIC_LOGIN] Cliente accedió exitosamente: {$clienteData['properties']['email']} via token: " . substr($token, 0, 8) . '...');

            // Redirigir al dashboard del cliente
            return redirect()->to('/cliente/dashboard')->with('success', 'Bienvenido a su portal de cliente ANVAR');

        } catch (\Exception $e) {
            log_message('error', "[MAGIC_LOGIN] Error procesando magic link: " . $e->getMessage());
            return $this->mostrarError('Error del sistema', 'Ocurrió un error procesando su acceso. Intente más tarde o contacte a soporte.');
        }
    }

    /**
     * Crear o actualizar usuario local para el cliente
     */
    private function crearOActualizarUsuarioLocal(array $clienteData): ?object
    {
        try {
            $email = $clienteData['properties']['email'];
            $emailCliente = $clienteData['properties']['email_cliente'] ?? $email;
            $nombres = $clienteData['properties']['firstname'] ?? 'Cliente';
            $apellidos = ($clienteData['properties']['lastname'] ?? '') . ' ' . ($clienteData['properties']['apellido_materno'] ?? '');

            // Buscar usuario existente por email
            $usuarioExistente = $this->userModel->findByCredentials(['email' => $emailCliente]);
            
            if ($usuarioExistente) {
                // Actualizar información del usuario existente
                $this->userModel->update($usuarioExistente->id, [
                    'firstname' => $nombres,
                    'lastname' => trim($apellidos),
                    'active' => 1,
                    'hubspot_contact_id' => $clienteData['id'],
                    'last_active' => date('Y-m-d H:i:s')
                ]);

                return $usuarioExistente;
            } else {
                // Crear nuevo usuario
                $datosUsuario = [
                    'email' => $emailCliente,
                    'firstname' => $nombres,
                    'lastname' => trim($apellidos),
                    'active' => 1,
                    'hubspot_contact_id' => $clienteData['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'password' => null // Magic link users don't have passwords
                ];

                $userId = $this->userModel->insert($datosUsuario);
                
                if ($userId) {
                    // Asignar rol de cliente
                    $usuario = $this->userModel->find($userId);
                    $usuario->addToGroup('cliente');

                    log_message('info', "[MAGIC_LOGIN] Usuario cliente creado: {$emailCliente} (ID: {$userId})");
                    return $usuario;
                }
            }

            return null;

        } catch (\Exception $e) {
            log_message('error', "[MAGIC_LOGIN] Error creando usuario local: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mostrar página de error
     */
    private function mostrarError(string $titulo, string $mensaje)
    {
        $data = [
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'contacto_soporte' => [
                'email' => 'soporte@anvar.com.mx',
                'telefono' => '+52 (xxx) xxx-xxxx',
                'whatsapp' => '+52 (xxx) xxx-xxxx'
            ]
        ];

        return view('cliente/magic-login-error', $data);
    }

    /**
     * Endpoint para validar token sin iniciar sesión (opcional)
     */
    public function validarToken(string $token)
    {
        try {
            $clienteData = $this->hubSpotService->obtenerClientePorMagicToken($token);
            
            $valido = $clienteData !== null;
            $expirado = false;
            
            if ($valido && isset($clienteData['properties']['magic_link_expires'])) {
                $fechaExpiracion = strtotime($clienteData['properties']['magic_link_expires']);
                $expirado = time() > $fechaExpiracion;
            }

            return $this->response->setJSON([
                'valido' => $valido && !$expirado,
                'expirado' => $expirado,
                'cliente' => $valido ? [
                    'nombre' => $clienteData['properties']['firstname'] ?? '',
                    'email' => $clienteData['properties']['email'] ?? ''
                ] : null
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'valido' => false,
                'error' => 'Error validando token'
            ]);
        }
    }
}