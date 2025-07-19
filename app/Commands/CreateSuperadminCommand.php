<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;

class CreateSuperadminCommand extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:create-superadmin';
    protected $description = 'Create superadmin user';
    protected $usage       = 'auth:create-superadmin';

    public function run(array $params)
    {
        $email = 'superadmin@nuevoanvar.test';
        $password = 'secret1234';
        $username = 'superadmin';

        $userModel = new UserModel();
        
        // Check if user already exists
        $existingUser = $userModel->findByCredentials(['email' => $email]);
        if ($existingUser) {
            CLI::write("User already exists. Updating password...", 'yellow');
            $existingUser->password = $password;
            $userModel->save($existingUser);
            CLI::write("Password updated for existing user: {$email}", 'green');
            return;
        }

        // Create new user
        $userData = [
            'email'    => $email,
            'username' => $username,
            'password' => $password,
            'active'   => 1,
        ];

        $user = $userModel->save($userData);
        if (!$user) {
            CLI::error('Failed to create user.');
            CLI::write('Validation errors:');
            foreach ($userModel->errors() as $error) {
                CLI::write("- {$error}", 'red');
            }
            return;
        }

        $userId = $userModel->getInsertID();
        CLI::write("Superadmin user created successfully!", 'green');
        CLI::write("Email: {$email}");
        CLI::write("Password: {$password}");
        CLI::write("User ID: {$userId}");

        // Add to superadmin group if groups exist
        try {
            $user = $userModel->find($userId);
            if ($user && method_exists($user, 'addGroup')) {
                $user->addGroup('superadmin');
                CLI::write("Added to superadmin group.", 'green');
            }
        } catch (\Exception $e) {
            CLI::write("Note: Groups not configured yet.", 'yellow');
        }
    }
}