<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;

class BienvenidaController extends BaseController
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Página de bienvenida que autentica con magic link
     */
    public function index()
    {
        try {
            $token = $this->request->getGet('token');
            $email = $this->request->getGet('email');
            
            if (!$token || !$email) {
                return redirect()->to('/login')->with('error', 'Enlace de acceso inválido o expirado.');
            }
            
            // Verificar token usando Shield
            $userModel = new \CodeIgniter\Shield\Models\UserModel();
            $user = $userModel->findByCredentials(['email' => $email]);
            
            if (!$user) {
                return redirect()->to('/login')->with('error', 'Usuario no encontrado.');
            }
            
            // Verificar si el token es válido
            $tokenModel = new \CodeIgniter\Shield\Models\UserIdentityModel();
            $tokenRecord = $tokenModel->where('secret', hash('sha256', $token))
                                   ->where('user_id', $user->id)
                                   ->where('type', 'access_token')
                                   ->where('name LIKE', 'magic-link-%')
                                   ->first();
            
            if (!$tokenRecord) {
                return redirect()->to('/login')->with('error', 'Enlace de acceso inválido o expirado.');
            }
            
            // Cerrar cualquier sesión activa antes de autenticar
            if (auth()->loggedIn()) {
                auth()->logout();
            }
            
            // Autenticar al usuario automáticamente
            auth()->login($user);
            
            // Eliminar el token para que no se pueda usar de nuevo
            $tokenModel->delete($tokenRecord->id);
            
            // Verificar si ya tiene contraseña configurada
            $identity = $user->getEmailIdentity();
            if ($identity && !empty($identity->secret2)) {
                // Ya tiene contraseña, redirigir a dashboard
                return redirect()->to('/cliente/dashboard')->with('success', '¡Bienvenido de vuelta!');
            }
            
            // No tiene contraseña, redirigir a configuración
            return redirect()->to('/cliente/configurar-password')->with('info', 'Para completar tu registro, configura tu contraseña personalizada.');
            
        } catch (\Exception $e) {
            log_message('error', 'Error en magic login bienvenida: ' . $e->getMessage());
            return redirect()->to('/login')->with('error', 'Error al procesar el enlace de acceso. Contacta a soporte.');
        }
    }
    
    /**
     * Página para configurar contraseña por primera vez
     */
    public function configurarPassword()
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Debes acceder con tu enlace de bienvenida primero.');
        }
        
        $user = auth()->user();
        
        // Verificar si ya tiene contraseña configurada
        $identity = $user->getEmailIdentity();
        if ($identity && !empty($identity->secret2)) {
            return redirect()->to('/cliente/dashboard')->with('info', 'Tu contraseña ya está configurada.');
        }
        
        // Obtener datos del cliente para personalizar la vista
        $clienteModel = new \App\Models\ClienteModel();
        $cliente = $clienteModel->where('user_id', $user->id)->first();
        
        $data = [
            'title' => 'Configurar Contraseña - Bienvenido a ANVAR',
            'user' => $user,
            'cliente' => $cliente
        ];
        
        return view('cliente/configurar_password', $data);
    }
    
    /**
     * Procesar configuración de contraseña
     */
    public function guardarPassword()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'Sesión expirada.');
        }
        
        // Validar contraseña
        $rules = [
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];
        
        $messages = [
            'password' => [
                'required' => 'La contraseña es obligatoria',
                'min_length' => 'La contraseña debe tener al menos 8 caracteres'
            ],
            'password_confirm' => [
                'required' => 'Confirma tu contraseña',
                'matches' => 'Las contraseñas no coinciden'
            ]
        ];
        
        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        try {
            $user = auth()->user();
            $newPassword = $this->request->getPost('password');
            
            // Actualizar contraseña usando Shield
            $user->password = $newPassword;
            $userModel = new \CodeIgniter\Shield\Models\UserModel();
            
            if ($userModel->save($user)) {
                // También actualizar el campo debe_cambiar_password en la tabla clientes
                $clienteModel = new \App\Models\ClienteModel();
                $clienteModel->where('user_id', $user->id)
                           ->set([
                               'debe_cambiar_password' => 0,
                               'ultimo_cambio_password' => date('Y-m-d H:i:s')
                           ])
                           ->update();
                
                log_message('info', "Contraseña configurada para usuario ID: {$user->id}");
                return redirect()->to('/cliente/dashboard')->with('success', '¡Contraseña configurada exitosamente! Bienvenido a tu portal de cliente.');
            } else {
                throw new \Exception('Error al guardar la contraseña');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error configurando contraseña: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al configurar contraseña. Intenta de nuevo.');
        }
    }
}