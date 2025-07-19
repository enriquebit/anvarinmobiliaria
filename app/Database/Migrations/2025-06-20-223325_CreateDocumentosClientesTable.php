<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentosClientesTable extends Migration
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
            'tipo_documento' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'carta_domicilio',
                    'comprobante_domicilio', 
                    'curp',
                    'hoja_requerimientos',
                    'identificacion_oficial',
                    'ofac',
                    'rfc'
                ],
            ],
            'nombre_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'ruta_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'tamano_archivo' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Tamaño en bytes',
            ],
            'extension' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'aprobado', 'rechazado'],
                'default'    => 'pendiente',
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'aprobado_por' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => true,
            ],
            'fecha_aprobacion' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->createTable('documentos_clientes');
    }

    public function down()
    {
        $this->forge->dropTable('documentos_clientes');
    }
}