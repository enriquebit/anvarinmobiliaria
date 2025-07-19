<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVentasPagosTable extends Migration
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
            'ventas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Referencia a la venta'
            ],
            'metodo_pago' => [
                'type' => 'ENUM',
                'constraint' => ['efectivo', 'transferencia', 'tarjeta', 'cheque', 'credito'],
                'comment' => 'Método de pago utilizado'
            ],
            'forma_pago_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Referencia al catálogo de formas de pago'
            ],
            'cuenta_bancaria_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Cuenta bancaria donde se deposita el dinero'
            ],
            'monto' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'comment' => 'Monto del pago'
            ],
            'concepto' => [
                'type' => 'ENUM',
                'constraint' => ['apartado', 'capital', 'interes', 'penalizacion'],
                'default' => 'apartado',
                'comment' => 'Concepto del pago'
            ],
            'referencia' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
                'comment' => 'Número de referencia, cheque, etc.'
            ],
            'fecha_pago' => [
                'type' => 'DATE',
                'comment' => 'Fecha del pago'
            ],
            'hora_pago' => [
                'type' => 'TIME',
                'null' => true,
                'comment' => 'Hora del pago'
            ],
            'archivo_comprobante' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'comment' => 'Ruta del archivo de comprobante'
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones del pago'
            ],
            'usuario_registro_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario que registró el pago'
            ],
            'ip_registro' => [
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
                'comment' => 'IP desde donde se registró'
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'aplicado', 'cancelado'],
                'default' => 'aplicado',
                'comment' => 'Estado del pago'
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
        $this->forge->addKey('ventas_id');
        $this->forge->addKey('metodo_pago');
        $this->forge->addKey('forma_pago_id');
        $this->forge->addKey('cuenta_bancaria_id');
        $this->forge->addKey('fecha_pago');
        $this->forge->addKey('concepto');
        $this->forge->addKey('estado');
        $this->forge->addKey('usuario_registro_id');

        $this->forge->createTable('ventas_pagos');

        // Agregar foreign keys
        $this->forge->addForeignKey('ventas_id', 'ventas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('forma_pago_id', 'formas_pago', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('cuenta_bancaria_id', 'cuentas_bancarias', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('usuario_registro_id', 'users', 'id', 'SET NULL', 'SET NULL');
    }

    public function down()
    {
        $this->forge->dropTable('ventas_pagos');
    }
}