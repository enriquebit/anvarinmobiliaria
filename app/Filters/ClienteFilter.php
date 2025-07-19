<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ClienteFilter implements FilterInterface
{
    /**
     * Permite SOLO a clientes acceder a rutas /cliente/*
     * Los admins NO pueden acceder a las vistas de cliente
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // DEBUG: Log que el filtro se está ejecutando
        $uri = $request->getUri()->getPath();
        log_message('info', "🛡️ ClienteFilter ejecutándose para URI: $uri");
        
        // Ignorar rutas especiales como .well-known, assets, etc.
        if (strpos($uri, '.well-known') !== false || 
            strpos($uri, 'assets') !== false ||
            strpos($uri, 'favicon') !== false) {
            log_message('info', "🛡️ ClienteFilter: Ignorando ruta especial: $uri");
            return null;
        }
        
        // ¿Está logueado?
        if (!auth()->loggedIn()) {
            log_message('info', "🛡️ ClienteFilter: Usuario NO logueado, redirigiendo a login");
            return redirect()->to('/login')
                           ->with('error', 'Debes iniciar sesión primero');
        }
        
        $user = auth()->user();
        log_message('info', "🛡️ ClienteFilter: Usuario logueado - ID: " . $user->id);
        
        // ¿Está activo el usuario?
        if (!$user->active) {
            // Desloguear al usuario inactivo
            auth()->logout();
            
            return redirect()->to('/login')
                           ->with('error', 'Tu usuario se encuentra desactivado. Contacta al administrador.');
        }
        
        // ¿Es cliente?
        $esCliente = $user->inGroup('cliente');
        log_message('info', "🛡️ ClienteFilter: ¿Es cliente? " . ($esCliente ? 'SÍ' : 'NO'));
        
        if (!$esCliente) {
            // Verificar si tiene algún rol administrativo/vendedor
            $rolesAdmin = ['admin', 'superadmin', 'vendedor', 'supervendedor', 'subvendedor'];
            $esRolAdmin = false;
            
            foreach ($rolesAdmin as $rol) {
                if ($user->inGroup($rol)) {
                    $esRolAdmin = true;
                    log_message('info', "🛡️ ClienteFilter: Usuario tiene rol admin: $rol");
                    break;
                }
            }
            
            if ($esRolAdmin) {
                log_message('info', "🛡️ ClienteFilter: Admin intentando acceder a cliente, redirigiendo");
                return redirect()->to('/admin/dashboard')
                               ->with('error', 'Los usuarios administrativos no pueden acceder al panel de clientes');
            }
            
            // Si no tiene rol válido, al login
            log_message('info', "🛡️ ClienteFilter: Usuario sin rol válido, redirigiendo a login");
            return redirect()->to('/login')
                           ->with('error', 'No tienes permisos para acceder a esta sección');
        }
        
        // Verificar si necesita configurar contraseña por primera vez
        $clienteModel = new \App\Models\ClienteModel();
        $cliente = $clienteModel->where('user_id', $user->id)->first();
        
        if ($cliente) {
            log_message('info', "🛡️ ClienteFilter: Cliente encontrado - ID: " . $cliente->id);
            
            // Verificar únicamente el flag force_reset de Shield
            $identity = $user->getEmailIdentity();
            $forceResetStatus = ($identity && $identity->force_reset) ? 'SÍ' : 'NO';
            $debeConfigurarPassword = ($identity && $identity->force_reset);
            
            log_message('info', "🛡️ ClienteFilter: force_reset=" . $forceResetStatus);
            log_message('info', "🛡️ ClienteFilter: debeConfigurarPassword=" . ($debeConfigurarPassword ? 'SÍ' : 'NO'));
            
            if ($debeConfigurarPassword) {
                // Permitir acceso solo a rutas de configuración de contraseña
                $rutasPermitidas = [
                    '/cliente/configurar-password',
                    '/cliente/guardar-password',
                    '/cliente/mi-perfil' // Para permitir cambio en mi-perfil también
                ];
                
                $uriActual = $request->getUri()->getPath();
                $rutaPermitida = false;
                
                foreach ($rutasPermitidas as $ruta) {
                    if (strpos($uriActual, $ruta) !== false) {
                        $rutaPermitida = true;
                        break;
                    }
                }
                
                log_message('info', "🛡️ ClienteFilter: URI actual: $uriActual, ruta permitida: " . ($rutaPermitida ? 'SÍ' : 'NO'));
                
                if (!$rutaPermitida) {
                    // Redirigir a mi-perfil (tab config) para cambio de contraseña
                    log_message('info', "🛡️ ClienteFilter: ¡REDIRIGIENDO a mi-perfil para cambio de contraseña!");
                    return redirect()->to('/cliente/mi-perfil')
                                   ->with('info', 'Debes configurar tu contraseña personal antes de continuar.');
                }
            }
        } else {
            log_message('info', "🛡️ ClienteFilter: ¡Cliente NO encontrado para user_id: " . $user->id . "!");
        }
        
        // Si llegó aquí, es cliente activo - permitir acceso
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No necesitamos hacer nada después
    }
}