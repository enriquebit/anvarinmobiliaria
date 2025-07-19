<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConfiguracionesFinancierasTable extends Migration
{
    public function up()
    {
        // Tabla principal de configuraciones financieras
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'empresa_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'proyecto_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'NULL = Configuración global para la empresa'
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Nombre descriptivo de la configuración'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            
            // Configuración de Anticipo
            'tipo_anticipo' => [
                'type'       => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'default'    => 'porcentaje',
            ],
            'porcentaje_anticipo' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 10.00,
                'comment'    => 'Porcentaje de anticipo (0-100)'
            ],
            'anticipo_fijo' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'comment'    => 'Monto fijo de anticipo en moneda local'
            ],
            'apartado_minimo' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 5000.00,
                'comment'    => 'Apartado mínimo requerido'
            ],
            
            // Configuración de Comisión
            'tipo_comision' => [
                'type'       => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'default'    => 'porcentaje',
            ],
            'porcentaje_comision' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 5.00,
                'comment'    => 'Porcentaje de comisión (0-100)'
            ],
            'comision_fija' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'comment'    => 'Monto fijo de comisión'
            ],
            
            // Configuración de Financiamiento
            'meses_sin_intereses' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
                'comment'    => 'Meses sin intereses'
            ],
            'meses_con_intereses' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 60,
                'comment'    => 'Meses con intereses'
            ],
            'porcentaje_interes_anual' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'comment'    => 'Tasa de interés anual'
            ],
            'dias_anticipo' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 30,
                'comment'    => 'Días de anticipo para pagos'
            ],
            'porcentaje_cancelacion' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 100.00,
                'comment'    => 'Porcentaje de devolución en cancelaciones'
            ],
            
            // Control de estado y auditoría
            'es_default' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1 = Configuración por defecto para la empresa'
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
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['empresa_id', 'activo']);
        $this->forge->addKey(['proyecto_id']);
        $this->forge->addKey(['es_default', 'activo']);
        
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('proyecto_id', 'proyectos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'SET NULL');
        
        $this->forge->createTable('configuraciones_financieras');
    }

    public function down()
    {
        $this->forge->dropTable('configuraciones_financieras');
    }
}