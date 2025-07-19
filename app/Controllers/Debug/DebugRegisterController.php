<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Models\UserModel;

class DebugRegisterController extends BaseController
{
    /**
     * Dashboard de debug - TODO EN UNA PÁGINA
     */
    public function index()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        // Configurar para mostrar errores
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        $html = $this->getHTMLHeader();
        
        try {
            $db = \Config\Database::connect();
            
            // SECCIÓN 1: Estado del Sistema
            $html .= "<h2>📊 Estado del Sistema</h2>";
            $html .= "<div class='card'>";
            
            $totalUsers = $db->table('users')->countAll();
            $totalClientes = $db->table('clientes')->countAll();
            $totalIdentities = $db->table('auth_identities')->countAll();
            
            $html .= "<p><strong>Usuarios:</strong> $totalUsers</p>";
            $html .= "<p><strong>Clientes:</strong> $totalClientes</p>";
            $html .= "<p><strong>Identities:</strong> $totalIdentities</p>";
            $html .= "</div>";
            
            // SECCIÓN 2: Verificación de Archivos
            $html .= "<h2>🔧 Verificación de Archivos</h2>";
            $html .= "<div class='card'>";
            
            $files = [
                'UserModel' => APPPATH . 'Models/UserModel.php',
                'RegisterController' => APPPATH . 'Controllers/Auth/RegisterController.php',
                'SimpleErrorChecker' => APPPATH . 'Controllers/SimpleErrorChecker.php'
            ];
            
            foreach ($files as $name => $path) {
                $status = file_exists($path) ? '✅ OK' : '❌ NO EXISTE';
                $html .= "<p><strong>$name:</strong> $status</p>";
            }
            $html .= "</div>";
            
            // SECCIÓN 3: Test de Funcionalidad
            $html .= "<h2>🧪 Test de Funcionalidad</h2>";
            $html .= "<div class='card'>";
            
            // Verificar UserModel
            try {
                $userModel = new UserModel();
                $html .= "<p>✅ UserModel cargado</p>";
                
                if (method_exists($userModel, 'createClienteUser')) {
                    $html .= "<p>✅ Método createClienteUser existe</p>";
                } else {
                    $html .= "<p>❌ Método createClienteUser NO existe</p>";
                }
            } catch (\Exception $e) {
                $html .= "<p>❌ Error cargando UserModel: " . $e->getMessage() . "</p>";
            }
            
            // Verificar Shield
            if (class_exists('\CodeIgniter\Shield\Models\UserModel')) {
                $html .= "<p>✅ Shield disponible</p>";
            } else {
                $html .= "<p>❌ Shield NO disponible</p>";
            }
            
            $html .= "</div>";
            
            // SECCIÓN 4: Logs Recientes
            $html .= "<h2>📋 Logs Recientes</h2>";
            $html .= "<div class='card'>";
            $html .= "<div class='logs'>";
            
            $logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
            if (file_exists($logFile)) {
                $logs = file_get_contents($logFile);
                $lines = explode("\n", $logs);
                $recentLines = array_slice($lines, -20);
                
                foreach ($recentLines as $line) {
                    if (strpos($line, 'register') !== false || 
                        strpos($line, '[REGISTRO]') !== false ||
                        strpos($line, 'ERROR') !== false) {
                        $html .= htmlspecialchars($line) . "\n";
                    }
                }
            } else {
                $html .= "No hay logs para hoy\n";
            }
            
            $html .= "</div></div>";
            
            // SECCIÓN 5: Enlaces de Acción
            $html .= "<h2>🔗 Acciones</h2>";
            $html .= "<div class='card'>";
            $html .= "<a href='/debug-register/test-simple' class='btn btn-success'>Test Simple</a> ";
            $html .= "<a href='/check-errors' class='btn btn-warning'>Verificar Errores</a> ";
            $html .= "<a href='/register' class='btn'>Formulario Registro</a> ";
            $html .= "<a href='/debug/last-client' class='btn'>Último Cliente</a>";
            $html .= "</div>";
            
        } catch (\Exception $e) {
            $html .= "<div class='error'>";
            $html .= "<h2>💥 Error en Debug:</h2>";
            $html .= "<p>" . $e->getMessage() . "</p>";
            $html .= "</div>";
        }
        
        $html .= $this->getHTMLFooter();
        
        return $this->response->setBody($html);
    }
    
    /**
     * Test simple y directo
     */
    public function testSimple()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        $html = $this->getHTMLHeader();
        $html .= "<h1>🧪 TEST SIMPLE DE REGISTRO</h1>";
        
        try {
            // Datos de prueba
            $testData = [
                'nombres' => 'TEST',
                'apellido_paterno' => 'USER',
                'apellido_materno' => 'SIMPLE',
                'email' => 'test.simple.' . time() . '@example.com',
                'telefono' => '5500000001',
                'password' => 'test123456'
            ];
            
            $html .= "<h3>📝 Datos de prueba:</h3>";
            $html .= "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";
            
            // Test UserModel
            $userModel = new UserModel();
            $html .= "<p>✅ UserModel cargado</p>";
            
            if (method_exists($userModel, 'createClienteUser')) {
                $html .= "<p>✅ Método createClienteUser existe</p>";
                
                // Intentar crear usuario
                $user = $userModel->createClienteUser($testData);
                
                if ($user && $user->id) {
                    $html .= "<h3>🎉 ¡ÉXITO!</h3>";
                    $html .= "<p>Usuario creado con ID: {$user->id}</p>";
                    $html .= "<p>Email: {$user->email}</p>";
                    
                    // Verificar cliente
                    $cliente = $user->getCliente();
                    if ($cliente) {
                        $html .= "<p>✅ Cliente creado: {$cliente->getNombreCompleto()}</p>";
                    } else {
                        $html .= "<p>❌ Cliente NO creado</p>";
                    }
                } else {
                    $html .= "<p>❌ Usuario NO creado</p>";
                }
                
            } else {
                $html .= "<p>❌ Método createClienteUser NO existe</p>";
            }
            
        } catch (\Exception $e) {
            $html .= "<div class='error'>";
            $html .= "<h3>💥 ERROR:</h3>";
            $html .= "<p>Mensaje: " . $e->getMessage() . "</p>";
            $html .= "<p>Archivo: " . $e->getFile() . "</p>";
            $html .= "<p>Línea: " . $e->getLine() . "</p>";
            $html .= "<h4>Stack Trace:</h4>";
            $html .= "<pre>" . $e->getTraceAsString() . "</pre>";
            $html .= "</div>";
        }
        
        $html .= "<p><a href='/debug-register'>← Volver</a></p>";
        $html .= $this->getHTMLFooter();
        
        return $this->response->setBody($html);
    }
    
    /**
     * Verificar base de datos
     */
    public function checkDatabase()
    {
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
        
        $html = $this->getHTMLHeader();
        $html .= "<h1>🗄️ VERIFICACIÓN DE BASE DE DATOS</h1>";
        
        try {
            $db = \Config\Database::connect();
            
            $tables = ['users', 'auth_identities', 'auth_groups_users', 'clientes'];
            
            foreach ($tables as $table) {
                $html .= "<h3>📋 Tabla: $table</h3>";
                
                if ($db->tableExists($table)) {
                    $count = $db->table($table)->countAll();
                    $html .= "<p>✅ Existe - Registros: $count</p>";
                    
                    if ($count > 0 && $table === 'clientes') {
                        $ultimo = $db->query("SELECT * FROM $table ORDER BY id DESC LIMIT 1")->getRow();
                        $html .= "<p><strong>Último registro:</strong></p>";
                        $html .= "<pre>" . json_encode($ultimo, JSON_PRETTY_PRINT) . "</pre>";
                    }
                } else {
                    $html .= "<p>❌ NO existe</p>";
                }
            }
            
        } catch (\Exception $e) {
            $html .= "<div class='error'>";
            $html .= "<p>Error: " . $e->getMessage() . "</p>";
            $html .= "</div>";
        }
        
        $html .= "<p><a href='/debug-register'>← Volver</a></p>";
        $html .= $this->getHTMLFooter();
        
        return $this->response->setBody($html);
    }
    
    /**
     * Header HTML común
     */
    private function getHTMLHeader(): string
    {
        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Debug Sistema</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
                .card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 4px; background: #fafafa; }
                .btn { padding: 8px 16px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
                .btn-success { background: #28a745; }
                .btn-warning { background: #ffc107; color: black; }
                .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
                .logs { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; max-height: 200px; overflow-y: auto; white-space: pre-wrap; }
                pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
                h1, h2, h3 { color: #333; }
                h1 { border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            </style>
        </head>
        <body>
        <div class="container">
        ';
    }
    
    /**
     * Footer HTML común
     */
    private function getHTMLFooter(): string
    {
        return '
        </div>
        </body>
        </html>
        ';
    }
}