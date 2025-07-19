<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearCatalogosBasicos extends Migration
{
    public function up()
    {
        // ===== TABLA ESTADOS CIVILES =====
        if (!$this->db->tableExists('estados_civiles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nombre' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'valor' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                ],
                'activo' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('estados_civiles');
        }

        // ===== TABLA EMPRESAS =====
        if (!$this->db->tableExists('empresas')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nombre' => [
                    'type' => 'VARCHAR',
                    'constraint' => 300,
                ],
                'activo' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('empresas');
        }

        // ===== TABLA FUENTES INFORMACION =====
        if (!$this->db->tableExists('fuentes_informacion')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nombre' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'valor' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'activo' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('fuentes_informacion');
        }

        // ===== INSERTAR DATOS INICIALES =====
        $this->insertarDatosIniciales();

        log_message('info', 'Migración CrearCatalogosBasicos ejecutada correctamente');
    }

    private function insertarDatosIniciales()
    {
        // Estados civiles
        $estadosCiviles = [
            ['id' => 1, 'nombre' => 'Soltero(a)', 'valor' => 'soltero', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'nombre' => 'Casado(a)', 'valor' => 'casado', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 3, 'nombre' => 'Unión Libre', 'valor' => 'union_libre', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 4, 'nombre' => 'Viudo(a)', 'valor' => 'viudo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];
        
        $this->db->table('estados_civiles')->insertBatch($estadosCiviles);

        // Empresas
        $empresas = [
            ['id' => 1, 'nombre' => 'ANVAR Inmobiliaria', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        ];
        
        $this->db->table('empresas')->insertBatch($empresas);

        // Fuentes de información
        $fuentes = [
            ['id' => 1, 'nombre' => 'Referido', 'valor' => 'referido', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'nombre' => 'Señalítica', 'valor' => 'senalitica', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 3, 'nombre' => 'Facebook', 'valor' => 'facebook', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 4, 'nombre' => 'Navegador', 'valor' => 'navegador', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 5, 'nombre' => 'Instagram', 'valor' => 'instagram', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 6, 'nombre' => 'Agencia Inmobiliaria', 'valor' => 'agencia_inmobiliaria', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 7, 'nombre' => 'Espectacular', 'valor' => 'espectacular', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => 8, 'nombre' => 'Otro', 'valor' => 'otro', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ];
        
        $this->db->table('fuentes_informacion')->insertBatch($fuentes);
    }

    public function down()
    {
        $this->forge->dropTable('fuentes_informacion');
        $this->forge->dropTable('empresas');
        $this->forge->dropTable('estados_civiles');
        
        log_message('info', 'Rollback CrearCatalogosBasicos ejecutado correctamente');
    }
}