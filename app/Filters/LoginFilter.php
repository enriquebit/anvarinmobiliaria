<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LoginFilter implements FilterInterface
{
    /**
     * Verifica que el usuario esté autenticado
     * Para rutas que requieren login pero no un rol específico
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
            // Guardar la URL a la que quería ir
            session()->setTempdata('beforeLoginUrl', current_url(), 300);
            
            return redirect()->to('/login');
        }
        
        // ¿Está activo el usuario?
        $user = auth()->user();
        if (!$user->active) {
            // Desloguear al usuario inactivo
            auth()->logout();
            
            return redirect()->to('/login')
                           ->with('error', 'Tu usuario se encuentra desactivado. Contacta al administrador.');
        }

        // Está logueado y activo - permitir acceso
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No necesitamos hacer nada después
    }
}