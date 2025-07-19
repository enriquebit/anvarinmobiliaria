<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;

class UpdateUserPasswordCommand extends BaseCommand
{
    protected $group       = 'Auth';
    protected $name        = 'auth:update-password';
    protected $description = 'Update user password by email';
    protected $usage       = 'auth:update-password [email] [password]';
    protected $arguments   = [
        'email'    => 'User email address',
        'password' => 'New password',
    ];

    public function run(array $params)
    {
        $email = $params[0] ?? CLI::prompt('Enter user email');
        $password = $params[1] ?? CLI::prompt('Enter new password');

        if (empty($email) || empty($password)) {
            CLI::error('Email and password are required.');
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findByCredentials(['email' => $email]);

        if (!$user) {
            CLI::error("User with email '{$email}' not found.");
            return;
        }

        // Update password
        $user->password = $password;
        $userModel->save($user);

        CLI::write("Password updated successfully for user: {$email}", 'green');
        CLI::write("User ID: {$user->id}");
        CLI::write("New password: {$password}");
    }
}