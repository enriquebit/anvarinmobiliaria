<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientesTable extends Migration
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
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'nombres' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'apellido_paterno' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'apellido_materno' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'genero' => [
                'type'       => 'ENUM',
                'constraint' => ['M', 'F'],
                'null'       => true,
            ],
            'razon_social' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'identificacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'numero_identificacion' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'fecha_nacimiento' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'lugar_nacimiento' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'nacionalidad' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'profesion' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'rfc' => [
                'type'       => 'VARCHAR',
                'constraint' => 13,
                'null'       => true,
            ],
            'curp' => [
                'type'       => 'VARCHAR',
                'constraint' => 18,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'estado_civil_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'leyenda_civil' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tiempo_radicando' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tipo_residencia' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'contacto' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'empresa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'clave' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'origen_informacion_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'otro_origen' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'null'       => true,
            ],
            'activo' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'fecha_activacion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'activado_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
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
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}