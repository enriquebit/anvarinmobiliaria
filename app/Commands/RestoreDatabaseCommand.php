<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RestoreDatabaseCommand extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:restore';
    protected $description = 'Restore both CI4 and Legacy databases';
    protected $usage       = 'db:restore';

    public function run(array $params)
    {
        CLI::write('Starting database restoration...', 'yellow');

        // Database connection details from .env
        $host = 'localhost';
        $username = 'root';
        $password = '';
        
        try {
            // 1. Restore CI4 database (nuevoanvar_vacio)
            CLI::write('Restoring CI4 database (nuevoanvar_vacio)...', 'blue');
            
            $ci4BackupFile = ROOTPATH . 'nuevoanvar_vacio_backup.sql';
            if (!file_exists($ci4BackupFile)) {
                CLI::error("CI4 backup file not found: {$ci4BackupFile}");
                return;
            }

            $mysqlCommand = "mysql -h{$host} -u{$username}" . ($password ? " -p{$password}" : '') . " nuevoanvar_vacio < \"{$ci4BackupFile}\"";
            
            // Execute the command
            $output = [];
            $returnCode = 0;
            exec($mysqlCommand . ' 2>&1', $output, $returnCode);
            
            if ($returnCode === 0) {
                CLI::write('✓ CI4 database restored successfully!', 'green');
            } else {
                CLI::error('Failed to restore CI4 database:');
                foreach ($output as $line) {
                    CLI::write($line, 'red');
                }
                return;
            }

            // 2. Verify CI4 database has Shield tables
            CLI::write('Verifying Shield tables...', 'blue');
            
            $mysqli = new \mysqli($host, $username, $password, 'nuevoanvar_vacio');
            if ($mysqli->connect_error) {
                CLI::error("Connection failed: " . $mysqli->connect_error);
                return;
            }

            $tables = ['users', 'auth_identities', 'auth_groups_users'];
            foreach ($tables as $table) {
                $result = $mysqli->query("SHOW TABLES LIKE '{$table}'");
                if ($result->num_rows > 0) {
                    CLI::write("✓ Table '{$table}' exists", 'green');
                } else {
                    CLI::error("✗ Table '{$table}' not found");
                }
            }

            // 3. Check if superadmin user exists
            $result = $mysqli->query("SELECT u.id, u.username, ai.secret FROM users u JOIN auth_identities ai ON u.id = ai.user_id WHERE ai.secret = 'superadmin@nuevoanvar.test' LIMIT 1");
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                CLI::write("✓ Superadmin user found - ID: {$user['id']}, Email: {$user['secret']}", 'green');
            } else {
                CLI::write("! Superadmin user not found, will create one", 'yellow');
                
                // Create superadmin user
                $hashedPassword = password_hash('secret1234', PASSWORD_DEFAULT);
                $now = date('Y-m-d H:i:s');
                
                // Insert user
                $insertUser = "INSERT INTO users (username, active, created_at) VALUES ('superadmin', 1, '{$now}')";
                if ($mysqli->query($insertUser)) {
                    $userId = $mysqli->insert_id;
                    CLI::write("✓ User created with ID: {$userId}", 'green');
                    
                    // Insert auth identity
                    $insertAuth = "INSERT INTO auth_identities (user_id, type, secret, secret2, created_at, updated_at) VALUES ({$userId}, 'email_password', 'superadmin@nuevoanvar.test', '{$hashedPassword}', '{$now}', '{$now}')";
                    if ($mysqli->query($insertAuth)) {
                        CLI::write("✓ Auth identity created", 'green');
                        
                        // Add to superadmin group
                        $insertGroup = "INSERT INTO auth_groups_users (user_id, group, created_at) VALUES ({$userId}, 'superadmin', '{$now}')";
                        if ($mysqli->query($insertGroup)) {
                            CLI::write("✓ Added to superadmin group", 'green');
                        }
                    }
                }
            }

            $mysqli->close();

            // 4. Verify legacy database exists
            CLI::write('Verifying legacy database (anvarinm_web)...', 'blue');
            
            $mysqli = new \mysqli($host, $username, $password, 'anvarinm_web');
            if ($mysqli->connect_error) {
                CLI::error("Legacy database connection failed: " . $mysqli->connect_error);
                CLI::write("Please ensure 'anvarinm_web' database exists and is populated", 'yellow');
            } else {
                $tables = ['tb_ventas', 'tb_cobranza', 'tb_clientes'];
                foreach ($tables as $table) {
                    $result = $mysqli->query("SHOW TABLES LIKE '{$table}'");
                    if ($result->num_rows > 0) {
                        CLI::write("✓ Legacy table '{$table}' exists", 'green');
                    } else {
                        CLI::error("✗ Legacy table '{$table}' not found");
                    }
                }
                $mysqli->close();
            }

            CLI::write('', '');
            CLI::write('Database restoration completed!', 'green');
            CLI::write('You can now login with:', 'blue');
            CLI::write('Email: superadmin@nuevoanvar.test', 'white');
            CLI::write('Password: secret1234', 'white');

        } catch (\Exception $e) {
            CLI::error('Error during restoration: ' . $e->getMessage());
        }
    }
}