<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStaffTable extends Migration
{
    public function up()
    {
        // ====================================================================
        // TABLA STAFF - MVP BÁSICO (sin índices, sin foreign keys)
        // ====================================================================
        
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT', 
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'usuario' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'agencia' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'vendedor',
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => false,
            ],
            'creado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'notas' => [
                'type' => 'TEXT',
                'null' => true,
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
        
        // SOLO PRIMARY KEY AUTOMÁTICO
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('staff');
        
        // ====================================================================
        // TABLA STAFF_EMPRESAS - MVP BÁSICO
        // ====================================================================
        
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'staff_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'empresa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        // SOLO PRIMARY KEY AUTOMÁTICO
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('staff_empresas');
        
        log_message('info', '✅ MVP: Tablas staff básicas creadas sin optimizaciones');
    }
    
    public function down()
    {
        $this->forge->dropTable('staff_empresas');
        $this->forge->dropTable('staff');
        
        log_message('info', '✅ Tablas staff eliminadas');
    }
}