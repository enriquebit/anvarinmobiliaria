<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConfiguracionEmpresaTable extends Migration
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
            'empresas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Referencia a la empresa'
            ],
            'proyectos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Configuración específica por proyecto'
            ],
            
            // Configuración de Anticipos
            'tipo_anticipo' => [
                'type' => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'default' => 'porcentaje',
                'comment' => 'Tipo de anticipo requerido'
            ],
            'porcentaje_anticipo' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
                'comment' => 'Porcentaje de anticipo cuando es tipo porcentaje'
            ],
            'anticipo_fijo' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'comment' => 'Monto fijo de anticipo cuando es tipo fijo'
            ],
            'apartado_minimo' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'comment' => 'Monto mínimo para apartar un lote'
            ],
            
            // Configuración de Comisiones
            'tipo_comision' => [
                'type' => 'ENUM',
                'constraint' => ['porcentaje', 'fijo'],
                'default' => 'porcentaje',
                'comment' => 'Tipo de comisión para vendedores'
            ],
            'porcentaje_comision' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00,
                'comment' => 'Porcentaje de comisión sobre venta total'
            ],
            'comision_fija' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'comment' => 'Monto fijo de comisión cuando es tipo fijo'
            ],
            'apartado_comision' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'comment' => 'Comisión fija por apartado exitoso'
            ],
            
            // Configuración de Financiamiento
            'meses_sin_intereses' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Meses sin intereses disponibles'
            ],
            'meses_con_intereses' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Meses con intereses disponibles'
            ],
            'porcentaje_interes_anual' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 12.00,
                'comment' => 'Tasa de interés anual para financiamiento'
            ],
            
            // Configuración de Plazos
            'dias_anticipo' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 30,
                'comment' => 'Días para completar el anticipo'
            ],
            'dias_cancelacion' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 15,
                'comment' => 'Días límite para apartados antes de cancelación'
            ],
            'porcentaje_cancelacion' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 100.00,
                'comment' => 'Porcentaje de devolución al cancelar'
            ],
            
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true,
                'comment' => 'Estado activo/inactivo'
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
        $this->forge->addKey('empresas_id');
        $this->forge->addKey('proyectos_id');
        $this->forge->addKey('activo');

        $this->forge->createTable('configuracion_empresa');

        // Agregar foreign keys
        $this->forge->addForeignKey('empresas_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('proyectos_id', 'proyectos', 'id', 'SET NULL', 'SET NULL');

        // Insertar configuración por defecto para empresa 1
        $data = [
            'empresas_id' => 1,
            'proyectos_id' => null, // Configuración general
            'tipo_anticipo' => 'porcentaje',
            'porcentaje_anticipo' => 12.00,
            'anticipo_fijo' => 0.00,
            'apartado_minimo' => 5000.00,
            'tipo_comision' => 'porcentaje',
            'porcentaje_comision' => 7.00,
            'comision_fija' => 0.00,
            'apartado_comision' => 1000.00,
            'meses_sin_intereses' => 12,
            'meses_con_intereses' => 60,
            'porcentaje_interes_anual' => 12.00,
            'dias_anticipo' => 30,
            'dias_cancelacion' => 15,
            'porcentaje_cancelacion' => 100.00,
            'activo' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('configuracion_empresa')->insert($data);
    }

    public function down()
    {
        $this->forge->dropTable('configuracion_empresa');
    }
}