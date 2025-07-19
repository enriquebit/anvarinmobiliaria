<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentosProyectoTable extends Migration
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
            'proyecto_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo_documento' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'nombre_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'ruta_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'tamaño_archivo' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addForeignKey('proyecto_id', 'proyectos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('documentos_proyecto');
    }

    public function down()
    {
        $this->forge->dropTable('documentos_proyecto');
    }
}
