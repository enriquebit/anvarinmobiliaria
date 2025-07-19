<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManzanasTable extends Migration
{
    public function up()
    {
        // TABLA: manzanas
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'codigo' => ['type' => 'VARCHAR', 'constraint' => 20, 'comment' => 'Código único de la manzana (MZ-001)'],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Nombre descriptivo de la manzana'],
            'proyecto_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'ID del proyecto al que pertenece'],
            'division_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'ID de la división (etapa)'],
            'superficie_total' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true, 'comment' => 'Superficie total en m²'],
            'numero_lotes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'comment' => 'Número total de lotes en la manzana'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado de la manzana'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['proyecto_id']);
        $this->forge->addKey(['division_id']);
        $this->forge->addKey(['codigo']);
        $this->forge->addKey(['activo']);
        
        // Foreign Keys
        $this->forge->addForeignKey('proyecto_id', 'proyectos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('division_id', 'divisiones', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('manzanas');
    }

    public function down()
    {
        $this->forge->dropTable('manzanas');
    }
}