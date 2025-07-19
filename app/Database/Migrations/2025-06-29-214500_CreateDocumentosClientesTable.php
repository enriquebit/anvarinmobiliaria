<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentosClientesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'cliente_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_documento' => [
                'type' => 'ENUM',
                'constraint' => ['ine', 'acta_nacimiento', 'comprobante_domicilio', 'comprobante_ingresos', 'estado_cuenta', 'rfc', 'curp', 'pasaporte', 'cedula', 'licencia', 'otro'],
                'default' => 'ine',
            ],
            'nombre_original' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'nombre_archivo' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'ruta_archivo' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
            ],
            'tamaño_archivo' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tipo_mime' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'subido_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('subido_por', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['cliente_id', 'tipo_documento']);
        
        $this->forge->createTable('documentos_clientes');
    }

    public function down()
    {
        $this->forge->dropTable('documentos_clientes');
    }
}