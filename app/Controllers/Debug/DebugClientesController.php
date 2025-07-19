<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use CodeIgniter\Shield\Models\UserModel;

/**
 * CREAR ESTE ARCHIVO: app/Controllers/Debug/DebugClientesController.php
 * Controlador específico para debuggear el flujo completo de clientes
 */
class DebugClientesController extends BaseController
{
    protected $clienteModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Panel principal de debug
     */
    public function index()
    {
        $data = [
            'titulo' => 'Debug Sistema de Clientes',
            'tests' => [
                'test_form_data' => 'Probar captura de datos del formulario',
                'test_database' => 'Probar conexión y estructura de BD',
                'test_shield' => 'Probar creación de usuario Shield',
                'test_cliente_insert' => 'Probar inserción directa de cliente',
                'test_full_flow' => 'Probar flujo completo',
                'test_validation' => 'Probar validaciones',
                'show_logs' => 'Ver logs recientes'
            ]
        ];

        return view('debug/clientes/index', $data);
    }

    /**
     * TEST 1: Verificar estructura de base de datos
     */
    public function testDatabase()
    {
        $result = ['success' => true, 'data' => [], 'errors' => []];

        try {
            // Verificar tablas
            $tables = ['users', 'auth_identities', 'auth_groups_users', 'clientes', 
                      'direcciones_clientes', 'informacion_laboral_clientes', 
                      'informacion_conyuge_clientes', 'referencias_clientes'];

            foreach ($tables as $table) {
                if ($this->db->tableExists($table)) {
                    $count = $this->db->table($table)->countAllResults();
                    $result['data'][$table] = "✅ Existe - {$count} registros";
                } else {
                    $result['data'][$table] = "❌ No existe";
                    $result['success'] = false;
                }
            }

            // Verificar estructura de tabla clientes
            $fields = $this->db->getFieldData('clientes');
            $result['data']['clientes_structure'] = [];
            foreach ($fields as $field) {
                $result['data']['clientes_structure'][] = $field->name . ' (' . $field->type . ')';
            }

            // Verificar último registro
            $lastUser = $this->db->table('users')->orderBy('id', 'DESC')->get()->getRowArray();
            $lastCliente = $this->db->table('clientes')->orderBy('id', 'DESC')->get()->getRowArray();
            
            $result['data']['last_user'] = $lastUser;
            $result['data']['last_cliente'] = $lastCliente;

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $this->response->setJSON($result);
    }

    /**
     * TEST 2: Probar datos del formulario simulado
     */
    public function testFormData()
    {
        // Datos de prueba exactos del formulario
        $testData = [
            // Datos básicos
            'nombres' => 'JUAN CARLOS',
            'apellido_paterno' => 'PÉREZ',
            'apellido_materno' => 'GONZÁLEZ',
            'email' => 'test_debug_' . time() . '@test.com',
            'telefono' => '5551234567',
            'genero' => 'M',
            'fecha_nacimiento' => '1990-05-15',
            'lugar_nacimiento' => 'CIUDAD DE MÉXICO',
            'nacionalidad' => 'mexicana',
            'profesion' => 'INGENIERO',
            'rfc' => 'PEGJ900515ABC',
            'curp' => 'PEGJ900515HDFRNN01',
            'estado_civil' => 'soltero',
            'fuente_informacion' => 'referido',
            'residente' => 'permanente',
            'identificacion' => 'ine',
            'numero_identificacion' => '1234567890123',
            'notas_internas' => 'Cliente de prueba para debug',

            // Dirección
            'direccion_domicilio' => 'AV INSURGENTES SUR 123',
            'direccion_numero' => 'DEPTO 4A',
            'direccion_colonia' => 'DEL VALLE',
            'direccion_cp' => '03100',
            'direccion_ciudad' => 'CIUDAD DE MÉXICO',
            'direccion_estado' => 'CDMX',
            'direccion_tipo_residencia' => 'propia',
            'direccion_tiempo_radicando' => '5 AÑOS',

            // Información laboral
            'laboral_empresa' => 'EMPRESA TEST SA',
            'laboral_puesto' => 'GERENTE DE SISTEMAS',
            'laboral_telefono' => '5559876543',
            'laboral_antiguedad' => '3 AÑOS',
            'laboral_direccion' => 'AV REFORMA 456, COL CENTRO',

            // Cónyuge
            'conyuge_nombre' => 'MARÍA FERNANDA LÓPEZ',
            'conyuge_telefono' => '5556789012',
            'conyuge_profesion' => 'DOCTORA',
            'conyuge_email' => 'maria@test.com',

            // Referencias
            'referencia1_nombre' => 'CARLOS RAMÍREZ',
            'referencia1_telefono' => '5554567890',
            'referencia1_parentesco' => 'HERMANO',
            'referencia1_genero' => 'M',

            'referencia2_nombre' => 'ANA GARCÍA',
            'referencia2_telefono' => '5553456789',
            'referencia2_parentesco' => 'AMIGA',
            'referencia2_genero' => 'F'
        ];

        $result = ['success' => true, 'data' => $testData, 'processed' => []];

        try {
            // Procesar datos como lo haría el controlador
            $result['processed'] = [
                'cliente_data' => [
                    'nombres' => strtoupper(trim($testData['nombres'])),
                    'apellido_paterno' => strtoupper(trim($testData['apellido_paterno'])),
                    'apellido_materno' => strtoupper(trim($testData['apellido_materno'])),
                    'email' => strtolower(trim($testData['email'])),
                    'telefono' => preg_replace('/[^0-9]/', '', $testData['telefono']),
                    'genero' => $testData['genero'],
                    'fecha_nacimiento' => $testData['fecha_nacimiento'],
                    'activo' => 1,
                    'etapa_proceso' => 'interesado'
                ],
                'direccion_found' => !empty($testData['direccion_domicilio']),
                'laboral_found' => !empty($testData['laboral_empresa']),
                'conyuge_found' => !empty($testData['conyuge_nombre']),
                'referencias_found' => (!empty($testData['referencia1_nombre']) || !empty($testData['referencia2_nombre']))
            ];

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $this->response->setJSON($result);
    }

    /**
     * TEST 3: Probar creación de usuario Shield
     */
    public function testShield()
    {
        $result = ['success' => true, 'data' => [], 'errors' => []];

        try {
            $testEmail = 'test_shield_' . time() . '@test.com';
            
            // Crear usuario Shield
            $userData = [
                'email' => $testEmail,
                'password' => 'temp_12345678',
                'active' => true
            ];

            $user = new \CodeIgniter\Shield\Entities\User($userData);
            
            if (!$this->userModel->save($user)) {
                throw new \RuntimeException('Error Shield: ' . implode(', ', $this->userModel->errors()));
            }

            $userId = $this->userModel->getInsertID();
            $result['data']['user_id'] = $userId;

            // Asignar grupo
            $user = $this->userModel->find($userId);
            $user->addGroup('cliente');

            // Verificar en BD
            $userInDb = $this->db->table('users')->where('id', $userId)->get()->getRowArray();
            $authInDb = $this->db->table('auth_identities')->where('user_id', $userId)->get()->getRowArray();
            $groupInDb = $this->db->table('auth_groups_users')->where('user_id', $userId)->get()->getRowArray();

            $result['data']['user_in_db'] = $userInDb;
            $result['data']['auth_in_db'] = $authInDb;
            $result['data']['group_in_db'] = $groupInDb;

            // Limpiar - eliminar usuario de prueba
            $this->userModel->delete($userId, true);
            $result['data']['cleanup'] = 'Usuario de prueba eliminado';

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $this->response->setJSON($result);
    }

    /**
     * TEST 4: Probar inserción directa de cliente
     */
    public function testClienteInsert()
    {
        $result = ['success' => true, 'data' => [], 'errors' => []];

        try {
            // Crear usuario de prueba primero
            $testEmail = 'test_cliente_' . time() . '@test.com';
            
            $userData = [
                'email' => $testEmail,
                'password' => 'temp_12345678',
                'active' => true
            ];

            $user = new \CodeIgniter\Shield\Entities\User($userData);
            
            if (!$this->userModel->save($user)) {
                throw new \RuntimeException('Error creando usuario: ' . implode(', ', $this->userModel->errors()));
            }

            $userId = $this->userModel->getInsertID();
            $result['data']['user_created'] = $userId;

            // Asignar grupo
            $user = $this->userModel->find($userId);
            $user->addGroup('cliente');

            // Insertar cliente directamente con Query Builder
            $clienteData = [
                'user_id' => $userId,
                'nombres' => 'JUAN CARLOS',
                'apellido_paterno' => 'PÉREZ',
                'apellido_materno' => 'GONZÁLEZ',
                'email' => $testEmail,
                'telefono' => '5551234567',
                'genero' => 'M',
                'fecha_nacimiento' => '1990-05-15',
                'activo' => 1,
                'etapa_proceso' => 'interesado',
                'fecha_primer_contacto' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $insertSuccess = $this->db->table('clientes')->insert($clienteData);
            
            if (!$insertSuccess) {
                throw new \RuntimeException('Error insertando cliente');
            }

            $clienteId = $this->db->insertID();
            $result['data']['cliente_created'] = $clienteId;

            // Verificar en BD
            $clienteInDb = $this->db->table('clientes')->where('id', $clienteId)->get()->getRowArray();
            $result['data']['cliente_in_db'] = $clienteInDb;

            // Probar información adicional
            $direccionData = [
                'cliente_id' => $clienteId,
                'domicilio' => 'AV INSURGENTES SUR 123',
                'colonia' => 'DEL VALLE',
                'codigo_postal' => '03100',
                'ciudad' => 'CIUDAD DE MÉXICO',
                'estado' => 'CDMX',
                'tipo' => 'principal',
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $direccionInsert = $this->db->table('direcciones_clientes')->insert($direccionData);
            $result['data']['direccion_created'] = $direccionInsert;

            if ($direccionInsert) {
                $direccionId = $this->db->insertID();
                $result['data']['direccion_id'] = $direccionId;
            }

            // NO LIMPIAR para poder ver los datos creados
            $result['data']['note'] = 'Datos de prueba creados exitosamente. Verificar en BD.';

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $this->response->setJSON($result);
    }

    /**
     * TEST 5: Simulación completa del flujo del controlador
     */
    public function testFullFlow()
    {
        $result = ['success' => true, 'data' => [], 'errors' => []];

        // Simular datos POST del formulario
        $formData = [
            'nombres' => 'CARLOS EDUARDO',
            'apellido_paterno' => 'MARTÍNEZ',
            'apellido_materno' => 'LÓPEZ',
            'email' => 'test_full_' . time() . '@test.com',
            'telefono' => '5559998888',
            'genero' => 'M',
            'fecha_nacimiento' => '1985-12-10'
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // PASO 1: Crear usuario Shield
            $userData = [
                'email' => $formData['email'],
                'password' => 'temp_' . random_string('alnum', 8),
                'active' => true
            ];

            $user = new \CodeIgniter\Shield\Entities\User($userData);
            
            if (!$this->userModel->save($user)) {
                throw new \RuntimeException('Error al crear usuario: ' . implode(', ', $this->userModel->errors()));
            }

            $userId = $this->userModel->getInsertID();
            $result['data']['step1_user_created'] = $userId;

            // Asignar grupo cliente
            $user = $this->userModel->find($userId);
            $user->addGroup('cliente');
            $result['data']['step2_group_assigned'] = true;

            // PASO 2: Crear cliente
            $clienteData = [
                'user_id' => $userId,
                'nombres' => strtoupper(trim($formData['nombres'])),
                'apellido_paterno' => strtoupper(trim($formData['apellido_paterno'])),
                'apellido_materno' => strtoupper(trim($formData['apellido_materno'])),
                'email' => strtolower(trim($formData['email'])),
                'telefono' => preg_replace('/[^0-9]/', '', $formData['telefono']),
                'genero' => $formData['genero'],
                'fecha_nacimiento' => $formData['fecha_nacimiento'],
                'activo' => 1,
                'fecha_activacion' => date('Y-m-d H:i:s'),
                'activado_por' => 1, // ID del superadmin
                'fecha_primer_contacto' => date('Y-m-d H:i:s'),
                'etapa_proceso' => 'interesado',
                'empresa_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!$this->db->table('clientes')->insert($clienteData)) {
                throw new \RuntimeException('Error al insertar cliente en BD');
            }

            $clienteId = $this->db->insertID();
            $result['data']['step3_cliente_created'] = $clienteId;

            // Completar transacción
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción de BD');
            }

            $result['data']['step4_transaction_completed'] = true;

            // Verificar datos finales
            $finalUser = $this->db->table('users')->where('id', $userId)->get()->getRowArray();
            $finalCliente = $this->db->table('clientes')->where('id', $clienteId)->get()->getRowArray();
            
            $result['data']['final_verification'] = [
                'user' => $finalUser,
                'cliente' => $finalCliente
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $this->response->setJSON($result);
    }

    /**
     * TEST 6: Ver logs recientes
     */
    public function showLogs()
    {
        $result = ['success' => true, 'data' => [], 'errors' => []];

        try {
            $logPath = WRITEPATH . 'logs';
            $files = glob($logPath . '/log-*.log');
            
            if (empty($files)) {
                $result['data']['message'] = 'No se encontraron archivos de log';
                return $this->response->setJSON($result);
            }

            // Obtener el archivo más reciente
            $latestFile = max($files);
            $result['data']['latest_file'] = basename($latestFile);

            // Leer últimas 50 líneas
            $lines = file($latestFile);
            $lastLines = array_slice($lines, -50);

            $result['data']['recent_logs'] = [];
            foreach ($lastLines as $line) {
                if (strpos($line, 'Cliente') !== false || 
                    strpos($line, 'Usuario') !== false || 
                    strpos($line, 'ERROR') !== false ||
                    strpos($line, 'DEBUG') !== false) {
                    $result['data']['recent_logs'][] = trim($line);
                }
            }

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $this->response->setJSON($result);
    }

    /**
     * TEST 7: Interceptar datos del formulario real
     */
    public function interceptFormData()
    {
        $result = ['success' => true, 'data' => [], 'errors' => []];

        try {
            // Capturar todos los datos POST
            $allPost = $this->request->getPost();
            $result['data']['all_post_data'] = $allPost;
            
            // Analizar datos
            $result['data']['analysis'] = [
                'total_fields' => count($allPost),
                'required_fields_present' => [
                    'nombres' => !empty($allPost['nombres']),
                    'apellido_paterno' => !empty($allPost['apellido_paterno']),
                    'apellido_materno' => !empty($allPost['apellido_materno']),
                    'email' => !empty($allPost['email']),
                    'telefono' => !empty($allPost['telefono'])
                ],
                'direccion_fields' => [],
                'laboral_fields' => [],
                'referencias_fields' => []
            ];

            // Buscar campos de dirección
            foreach ($allPost as $key => $value) {
                if (strpos($key, 'direccion_') === 0) {
                    $result['data']['analysis']['direccion_fields'][$key] = $value;
                }
                if (strpos($key, 'laboral_') === 0) {
                    $result['data']['analysis']['laboral_fields'][$key] = $value;
                }
                if (strpos($key, 'referencia') === 0) {
                    $result['data']['analysis']['referencias_fields'][$key] = $value;
                }
            }

            // Log temporal
            log_message('info', '🔍 DATOS INTERCEPTADOS DEL FORMULARIO: ' . json_encode($allPost));

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $this->response->setJSON($result);
    }
}