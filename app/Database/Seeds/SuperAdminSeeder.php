<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Datos del superadmin
        $userData = [
            'username' => 'superadmin',
            'email'    => 'superadmin@nuevoanvar.test',
            'password' => 'secret1234',
        ];

        // Verificar si ya existe
        $users = auth()->getProvider();
        $existingUser = $users->findByCredentials(['email' => $userData['email']]);
        
        if ($existingUser !== null) {
            echo "El usuario superadmin ya existe.\n";
            return;
        }

        // Crear el usuario
        $user = new User($userData);
        $users->save($user);

        // Activar el usuario
        $user = $users->findByCredentials(['email' => $userData['email']]);
        $user->activate();

        // Asignar el grupo superadmin
        $user->addGroup('superadmin');

        echo "Usuario superadmin creado exitosamente.\n";
        echo "Email: {$userData['email']}\n";
        echo "Password: {$userData['password']}\n";
    }
}