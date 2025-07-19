<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    /**
     * Permite SOLO a administradores y superadministradores
     * acceder a rutas /admin/*
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Ignorar rutas especiales como .well-known, assets, etc.
        $uri = $request->getUri()->getPath();
        if (strpos($uri, '.well-known') !== false || 
            strpos($uri, 'assets') !== false ||
            strpos($uri, 'favicon') !== false) {
            return null;
        }
        
        // ¿Está logueado?
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')
                           ->with('error', 'Debes iniciar sesión primero');
        }
        
        $user = auth()->user();
        
        // ¿Está activo el usuario?
        if (!$user->active) {
            // Desloguear al usuario inactivo
            auth()->logout();
            
            return redirect()->to('/login')
                           ->with('error', 'Tu usuario se encuentra desactivado. Contacta al administrador.');
        }
        
        // ¿Tiene rol administrativo o de ventas?
        $rolesAdmin = ['admin', 'superadmin', 'vendedor', 'supervendedor', 'subvendedor'];
        $tieneAccesoAdmin = false;
        
        foreach ($rolesAdmin as $rol) {
            if ($user->inGroup($rol)) {
                $tieneAccesoAdmin = true;
                break;
            }
        }
        
        if (!$tieneAccesoAdmin) {
            // Si es cliente, enviarlo a SU panel
            if ($user->inGroup('cliente')) {
                return redirect()->to('/cliente/dashboard')
                               ->with('error', 'No tienes permisos para acceder al panel administrativo');
            }
            
            // Si no tiene rol válido, al login
            return redirect()->to('/login')
                           ->with('error', 'No tienes permisos para acceder a esta sección');
        }
        
        // Si llegó aquí, es admin/superadmin activo - permitir acceso
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No necesitamos hacer nada después
    }
}