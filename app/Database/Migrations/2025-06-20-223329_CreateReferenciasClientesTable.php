<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReferenciasClientesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cliente_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'numero' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'comment' => '1 para referencia #1, 2 para referencia #2, 3 para beneficiario',
            ],
            'nombre_completo' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => true,
            ],
            'parentesco' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['referencia_1', 'referencia_2', 'beneficiario'],
                'default'    => 'referencia_1',
            ],
            'activo' => [
                'type'    => 'TINYINT',
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
        $this->forge->addKey('cliente_id');
        $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('referencias_clientes');
    }

    public function down()
    {
        $this->forge->dropTable('referencias_clientes');
    }
}