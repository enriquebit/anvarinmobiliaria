<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;

class TestController extends BaseController
{
    public function groups()
    {
        $config = config('AuthGroups');
        
        echo "<h3>Grupos configurados:</h3>";
        foreach ($config->groups as $key => $group) {
            echo "<p><strong>$key:</strong> {$group['titulo']} - {$group['description']}</p>";
        }
        
        echo "<h3>Permisos configurados:</h3>";
        foreach ($config->permissions as $key => $permission) {
            echo "<p><strong>$key:</strong> $permission</p>";
        }
    }
   public function superadmin()
{
    $users = auth()->getProvider();
    $superadmin = $users->findByCredentials(['email' => 'superadmin@nuevoanvar.test']);
    
    if ($superadmin) {
        echo "<h3>SuperAdmin encontrado:</h3>";
        echo "<p><strong>ID:</strong> {$superadmin->id}</p>";
        echo "<p><strong>Email:</strong> {$superadmin->email}</p>";
        echo "<p><strong>Username:</strong> {$superadmin->username}</p>";
        echo "<p><strong>Activo:</strong> " . ($superadmin->active ? 'Sí' : 'No') . "</p>";
        echo "<p><strong>Grupos:</strong> " . implode(', ', $superadmin->getGroups()) . "</p>";
        echo "<p><strong>Permisos:</strong> " . implode(', ', $superadmin->getPermissions()) . "</p>";
    } else {
        echo "<p>SuperAdmin no encontrado</p>";
    }
} 
}