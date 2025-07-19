<?php

namespace App\Controllers\Debug;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use CodeIgniter\Shield\Models\UserModel;

/**
 * CREAR ESTE ARCHIVO: app/Controllers/Debug/SimpleDebugController.php
 * Controlador simplificado para debuggear directamente
 */
class SimpleDebugController extends BaseController
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
     * Test directo del problema - CREAR UN CLIENTE COMPLETO
     */
    public function testDirecto()
    {
        echo "<h1>🔧 TEST DIRECTO - CREAR CLIENTE COMPLETO</h1>";
        echo "<pre>";
        
        // Simular datos exactos del formulario
        $formData = [
            // Datos básicos
            'nombres' => 'JUAN CARLOS',
            'apellido_paterno' => 'PÉREZ', 
            'apellido_materno' => 'GONZÁLEZ',
            'email' => 'test_directo_' . time() . '@test.com',
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
            'notas_internas' => 'Cliente de prueba directo',

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

        echo "📋 DATOS A PROCESAR:\n";
        echo json_encode($formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

        // Iniciar transacción
        $this->db->transStart();

        try {
            echo "🔄 PASO 1: Crear usuario Shield...\n";
            
            // Crear usuario Shield
            $userData = [
                'email' => $formData['email'],
                'password' => 'temp_' . random_string('alnum', 8),
                'active' => true
            ];

            $user = new \CodeIgniter\Shield\Entities\User($userData);
            
            if (!$this->userModel->save($user)) {
                throw new \RuntimeException('Error Shield: ' . implode(', ', $this->userModel->errors()));
            }

            $userId = $this->userModel->getInsertID();
            echo "✅ Usuario Shield creado - ID: {$userId}\n";

            // Asignar grupo cliente
            $user = $this->userModel->find($userId);
            $user->addGroup('cliente');
            echo "✅ Grupo 'cliente' asignado\n";

            echo "\n🔄 PASO 2: Crear cliente principal...\n";

            // Preparar datos del cliente
            $clienteData = [
                'user_id' => $userId,
                'nombres' => strtoupper(trim($formData['nombres'])),
                'apellido_paterno' => strtoupper(trim($formData['apellido_paterno'])),
                'apellido_materno' => strtoupper(trim($formData['apellido_materno'])),
                'genero' => $formData['genero'],
                'fecha_nacimiento' => $formData['fecha_nacimiento'],
                'lugar_nacimiento' => strtoupper(trim($formData['lugar_nacimiento'])),
                'nacionalidad' => $formData['nacionalidad'],
                'profesion' => strtoupper(trim($formData['profesion'])),
                'rfc' => strtoupper(trim($formData['rfc'])),
                'curp' => strtoupper(trim($formData['curp'])),
                'email' => strtolower(trim($formData['email'])),
                'telefono' => preg_replace('/[^0-9]/', '', $formData['telefono']),
                'estado_civil' => $formData['estado_civil'],
                'fuente_informacion' => $formData['fuente_informacion'],
                'residente' => $formData['residente'],
                'identificacion' => $formData['identificacion'],
                'numero_identificacion' => $formData['numero_identificacion'],
                'notas_internas' => $formData['notas_internas'],
                'empresa_id' => 1,
                'activo' => 1,
                'fecha_activacion' => date('Y-m-d H:i:s'),
                'activado_por' => 1,
                'fecha_primer_contacto' => date('Y-m-d H:i:s'),
                'etapa_proceso' => 'interesado',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            echo "📝 DATOS CLIENTE PREPARADOS:\n";
            echo json_encode($clienteData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

            // Insertar cliente
            $insertSuccess = $this->db->table('clientes')->insert($clienteData);
            
            if (!$insertSuccess) {
                throw new \RuntimeException('Error al insertar cliente');
            }

            $clienteId = $this->db->insertID();
            echo "✅ Cliente principal creado - ID: {$clienteId}\n";

            echo "\n🔄 PASO 3: Insertar información adicional...\n";

            // INSERTAR DIRECCIÓN
            echo "\n🏠 Insertando dirección...\n";
            $direccionData = [
                'cliente_id' => $clienteId,
                'domicilio' => strtoupper(trim($formData['direccion_domicilio'])),
                'numero' => trim($formData['direccion_numero']),
                'colonia' => strtoupper(trim($formData['direccion_colonia'])),
                'codigo_postal' => trim($formData['direccion_cp']),
                'ciudad' => strtoupper(trim($formData['direccion_ciudad'])),
                'estado' => strtoupper(trim($formData['direccion_estado'])),
                'tipo_residencia' => $formData['direccion_tipo_residencia'],
                'tiempo_radicando' => trim($formData['direccion_tiempo_radicando']),
                'tipo' => 'principal',
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            echo "📝 DATOS DIRECCIÓN:\n";
            echo json_encode($direccionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

            $direccionInsert = $this->db->table('direcciones_clientes')->insert($direccionData);
            
            if ($direccionInsert) {
                $direccionId = $this->db->insertID();
                echo "✅ Dirección insertada - ID: {$direccionId}\n";
            } else {
                echo "❌ Error insertando dirección\n";
            }

            // INSERTAR INFORMACIÓN LABORAL
            echo "\n💼 Insertando información laboral...\n";
            $laboralData = [
                'cliente_id' => $clienteId,
                'nombre_empresa' => strtoupper(trim($formData['laboral_empresa'])),
                'puesto_cargo' => strtoupper(trim($formData['laboral_puesto'])),
                'telefono_trabajo' => preg_replace('/[^0-9]/', '', $formData['laboral_telefono']),
                'antiguedad' => trim($formData['laboral_antiguedad']),
                'direccion_trabajo' => trim($formData['laboral_direccion']),
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            echo "📝 DATOS LABORAL:\n";
            echo json_encode($laboralData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

            $laboralInsert = $this->db->table('informacion_laboral_clientes')->insert($laboralData);
            
            if ($laboralInsert) {
                $laboralId = $this->db->insertID();
                echo "✅ Información laboral insertada - ID: {$laboralId}\n";
            } else {
                echo "❌ Error insertando información laboral\n";
            }

            // INSERTAR CÓNYUGE
            echo "\n💑 Insertando información cónyuge...\n";
            $conyugeData = [
                'cliente_id' => $clienteId,
                'nombre_completo' => strtoupper(trim($formData['conyuge_nombre'])),
                'telefono' => preg_replace('/[^0-9]/', '', $formData['conyuge_telefono']),
                'profesion' => strtoupper(trim($formData['conyuge_profesion'])),
                'email' => strtolower(trim($formData['conyuge_email'])),
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            echo "📝 DATOS CÓNYUGE:\n";
            echo json_encode($conyugeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

            $conyugeInsert = $this->db->table('informacion_conyuge_clientes')->insert($conyugeData);
            
            if ($conyugeInsert) {
                $conyugeId = $this->db->insertID();
                echo "✅ Información cónyuge insertada - ID: {$conyugeId}\n";
            } else {
                echo "❌ Error insertando información cónyuge\n";
            }

            // INSERTAR REFERENCIAS
            echo "\n👤 Insertando referencia #1...\n";
            $ref1Data = [
                'cliente_id' => $clienteId,
                'numero' => 1,
                'nombre_completo' => strtoupper(trim($formData['referencia1_nombre'])),
                'telefono' => preg_replace('/[^0-9]/', '', $formData['referencia1_telefono']),
                'parentesco' => strtoupper(trim($formData['referencia1_parentesco'])),
                'genero' => $formData['referencia1_genero'],
                'tipo' => 'referencia_1',
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            echo "📝 DATOS REFERENCIA 1:\n";
            echo json_encode($ref1Data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

            $ref1Insert = $this->db->table('referencias_clientes')->insert($ref1Data);
            
            if ($ref1Insert) {
                $ref1Id = $this->db->insertID();
                echo "✅ Referencia #1 insertada - ID: {$ref1Id}\n";
            } else {
                echo "❌ Error insertando referencia #1\n";
            }

            echo "\n👥 Insertando referencia #2...\n";
            $ref2Data = [
                'cliente_id' => $clienteId,
                'numero' => 2,
                'nombre_completo' => strtoupper(trim($formData['referencia2_nombre'])),
                'telefono' => preg_replace('/[^0-9]/', '', $formData['referencia2_telefono']),
                'parentesco' => strtoupper(trim($formData['referencia2_parentesco'])),
                'genero' => $formData['referencia2_genero'],
                'tipo' => 'referencia_2',
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            echo "📝 DATOS REFERENCIA 2:\n";
            echo json_encode($ref2Data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

            $ref2Insert = $this->db->table('referencias_clientes')->insert($ref2Data);
            
            if ($ref2Insert) {
                $ref2Id = $this->db->insertID();
                echo "✅ Referencia #2 insertada - ID: {$ref2Id}\n";
            } else {
                echo "❌ Error insertando referencia #2\n";
            }

            // Completar transacción
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción');
            }

            echo "\n🎉 TRANSACCIÓN COMPLETADA EXITOSAMENTE\n";

            // Verificar datos finales
            echo "\n🔍 VERIFICACIÓN FINAL:\n";
            
            $clienteVerificacion = $this->db->table('clientes')->where('id', $clienteId)->get()->getRowArray();
            echo "Cliente en BD: " . ($clienteVerificacion ? 'SÍ' : 'NO') . "\n";
            
            $direccionVerificacion = $this->db->table('direcciones_clientes')->where('cliente_id', $clienteId)->countAllResults();
            echo "Direcciones: {$direccionVerificacion}\n";
            
            $laboralVerificacion = $this->db->table('informacion_laboral_clientes')->where('cliente_id', $clienteId)->countAllResults();
            echo "Info laboral: {$laboralVerificacion}\n";
            
            $conyugeVerificacion = $this->db->table('informacion_conyuge_clientes')->where('cliente_id', $clienteId)->countAllResults();
            echo "Info cónyuge: {$conyugeVerificacion}\n";
            
            $referenciasVerificacion = $this->db->table('referencias_clientes')->where('cliente_id', $clienteId)->countAllResults();
            echo "Referencias: {$referenciasVerificacion}\n";

            echo "\n✅ PRUEBA DIRECTA COMPLETADA - CLIENTE ID: {$clienteId}\n";

        } catch (\Exception $e) {
            $this->db->transRollback();
            echo "\n💥 ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }

        echo "</pre>";
    }

    /**
     * Verificar estado actual de las tablas
     */
    public function verificarTablas()
    {
        echo "<h1>📊 ESTADO ACTUAL DE LAS TABLAS</h1>";
        echo "<pre>";

        $tablas = [
            'users' => 'Usuarios',
            'auth_identities' => 'Identidades Auth',
            'auth_groups_users' => 'Grupos de Usuarios',
            'clientes' => 'Clientes',
            'direcciones_clientes' => 'Direcciones',
            'informacion_laboral_clientes' => 'Info Laboral',
            'informacion_conyuge_clientes' => 'Info Cónyuge',
            'referencias_clientes' => 'Referencias'
        ];

        foreach ($tablas as $tabla => $nombre) {
            if ($this->db->tableExists($tabla)) {
                $count = $this->db->table($tabla)->countAllResults();
                echo "✅ {$nombre}: {$count} registros\n";
                
                // Mostrar últimos 3 registros si existen
                if ($count > 0) {
                    $ultimos = $this->db->table($tabla)->orderBy('id', 'DESC')->limit(3)->get()->getResultArray();
                    echo "   Últimos registros:\n";
                    foreach ($ultimos as $registro) {
                        echo "   - ID: {$registro['id']}\n";
                    }
                }
            } else {
                echo "❌ {$nombre}: Tabla no existe\n";
            }
            echo "\n";
        }

        echo "</pre>";
    }

    /**
     * Limpiar datos de prueba
     */
    public function limpiarPruebas()
    {
        echo "<h1>🧹 LIMPIAR DATOS DE PRUEBA</h1>";
        echo "<pre>";

        try {
            // Eliminar usuarios de prueba
            $usuariosPrueba = $this->db->table('auth_identities')
                                      ->like('secret', 'test_', 'after')
                                      ->get()
                                      ->getResultArray();

            foreach ($usuariosPrueba as $usuario) {
                $userId = $usuario['user_id'];
                
                // Buscar cliente asociado
                $cliente = $this->db->table('clientes')->where('user_id', $userId)->get()->getRowArray();
                
                if ($cliente) {
                    $clienteId = $cliente['id'];
                    
                    // Eliminar información relacionada
                    $this->db->table('direcciones_clientes')->where('cliente_id', $clienteId)->delete();
                    $this->db->table('informacion_laboral_clientes')->where('cliente_id', $clienteId)->delete();
                    $this->db->table('informacion_conyuge_clientes')->where('cliente_id', $clienteId)->delete();
                    $this->db->table('referencias_clientes')->where('cliente_id', $clienteId)->delete();
                    
                    // Eliminar cliente
                    $this->db->table('clientes')->where('id', $clienteId)->delete();
                    
                    echo "🗑️ Cliente ID {$clienteId} y datos relacionados eliminados\n";
                }
                
                // Eliminar usuario Shield
                $this->userModel->delete($userId, true);
                echo "🗑️ Usuario ID {$userId} eliminado\n";
            }

            echo "\n✅ Limpieza completada\n";

        } catch (\Exception $e) {
            echo "💥 Error en limpieza: " . $e->getMessage() . "\n";
        }

        echo "</pre>";
    }
}