<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReestructuracionesTable extends Migration
{
    public function up()
    {
        // Tabla principal de reestructuraciones
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'venta_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'folio_reestructuracion' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'motivo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fecha_reestructuracion' => [
                'type' => 'DATE',
            ],
            'fecha_vencimiento_original' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'saldo_pendiente_original' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'saldo_capital_original' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'saldo_interes_original' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'saldo_moratorio_original' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'quita_aplicada' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'descuento_intereses' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'descuento_moratorios' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'nuevo_saldo_capital' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'nuevo_plazo_meses' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'nueva_tasa_interes' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => '0.00',
            ],
            'nuevo_pago_mensual' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'enganche_convenio' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'fecha_primer_pago' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'estatus' => [
                'type' => 'ENUM',
                'constraint' => ['propuesta', 'autorizada', 'firmada', 'activa', 'cancelada'],
                'default' => 'propuesta',
            ],
            'autorizado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'fecha_autorizacion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'autorizado_por_nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'registrado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'documento_convenio' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('venta_id');
        $this->forge->addKey('folio_reestructuracion');
        $this->forge->addKey('fecha_reestructuracion');
        $this->forge->addKey('estatus');
        $this->forge->createTable('reestructuraciones');

        // Tabla de detalle de pagos reestructurados
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'reestructuracion_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'numero_pago' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'fecha_vencimiento' => [
                'type' => 'DATE',
            ],
            'saldo_inicial' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'capital' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'interes' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'monto_total' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'saldo_final' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'estatus' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'pagada', 'vencida', 'parcial'],
                'default' => 'pendiente',
            ],
            'monto_pagado' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
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
        $this->forge->addKey('reestructuracion_id');
        $this->forge->addKey(['reestructuracion_id', 'numero_pago']);
        $this->forge->addKey('fecha_vencimiento');
        $this->forge->addKey('estatus');
        $this->forge->createTable('reestructuraciones_detalle');

        // Tabla de historial de reestructuraciones
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'reestructuracion_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'accion' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'datos_anterior' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'datos_nuevo' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'realizado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('reestructuracion_id');
        $this->forge->addKey('accion');
        $this->forge->addKey('created_at');
        $this->forge->createTable('reestructuraciones_historial');
    }

    public function down()
    {
        $this->forge->dropTable('reestructuraciones_historial');
        $this->forge->dropTable('reestructuraciones_detalle');
        $this->forge->dropTable('reestructuraciones');
    }
}