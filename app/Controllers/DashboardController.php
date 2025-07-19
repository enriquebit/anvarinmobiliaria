<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        // ¿Está logueado?
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }
        
        $user = auth()->user();
        
        // ¿Está activo el usuario?
        if (!$user->active) {
            // Desloguear al usuario inactivo
            auth()->logout();
            
            return redirect()->to('/login')
                           ->with('error', 'Tu usuario se encuentra desactivado. Contacta al administrador.');
        }
        
        // ARREGLADO: Verificar superadmin Y admin
        if ($user->inGroup('superadmin', 'admin')) {
            return redirect()->to('/admin/dashboard');
        }
        
        // Verificar si es cliente
        if ($user->inGroup('cliente')) {
            return redirect()->to('/cliente/dashboard');
        }
        
        // No tiene rol válido
        return redirect()->to('/login')->with('error', 'Rol no válido');
    }
}