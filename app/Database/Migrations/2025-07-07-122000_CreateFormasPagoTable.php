<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFormasPagoTable extends Migration
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
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'comment' => 'Nombre de la forma de pago'
            ],
            'clave' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'comment' => 'Clave única de la forma de pago'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción de la forma de pago'
            ],
            'metodo_pago' => [
                'type' => 'ENUM',
                'constraint' => ['transferencia', 'efectivo', 'tarjeta', 'cheque'],
                'comment' => 'Método de pago principal al que pertenece'
            ],
            'requiere_referencia' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Si requiere número de referencia'
            ],
            'requiere_comprobante' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Si requiere archivo de comprobante'
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => true,
                'comment' => 'Estado activo/inactivo'
            ],
            'orden' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Orden de visualización'
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
        $this->forge->addUniqueKey('clave');
        $this->forge->addKey('activo');
        $this->forge->addKey('metodo_pago');
        $this->forge->addKey('orden');

        $this->forge->createTable('formas_pago');

        // Insertar formas de pago según el análisis del legacy
        $data = [
            // TRANSFERENCIAS
            [
                'nombre' => 'SPEI',
                'clave' => 'SPEI',
                'descripcion' => 'Sistema de Pagos Electrónicos Interbancarios',
                'metodo_pago' => 'transferencia',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'ELECTRÓNICA',
                'clave' => 'ELECTRONICA',
                'descripcion' => 'Transferencia electrónica bancaria',
                'metodo_pago' => 'transferencia',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 2,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'DEPÓSITO VENTANILLA',
                'clave' => 'DEPOSITO_VENTANILLA',
                'descripcion' => 'Depósito bancario en ventanilla',
                'metodo_pago' => 'transferencia',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 3,
                'created_at' => date('Y-m-d H:i:s')
            ],

            // EFECTIVO
            [
                'nombre' => 'EFECTIVO',
                'clave' => 'EFECTIVO',
                'descripcion' => 'Pago en efectivo directo',
                'metodo_pago' => 'efectivo',
                'requiere_referencia' => false,
                'requiere_comprobante' => false,
                'activo' => true,
                'orden' => 4,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'EFECTIVO ATM',
                'clave' => 'EFECTIVO_ATM',
                'descripcion' => 'Depósito en efectivo vía ATM',
                'metodo_pago' => 'efectivo',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 5,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'DEP EFECTIVO',
                'clave' => 'DEP_EFECTIVO',
                'descripcion' => 'Depósito en efectivo bancario',
                'metodo_pago' => 'efectivo',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 6,
                'created_at' => date('Y-m-d H:i:s')
            ],

            // TARJETAS
            [
                'nombre' => 'TARJETA DE DÉBITO',
                'clave' => 'TARJETA_DEBITO',
                'descripcion' => 'Pago con tarjeta de débito',
                'metodo_pago' => 'tarjeta',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 7,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'TARJETA DE CRÉDITO',
                'clave' => 'TARJETA_CREDITO',
                'descripcion' => 'Pago con tarjeta de crédito',
                'metodo_pago' => 'tarjeta',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 8,
                'created_at' => date('Y-m-d H:i:s')
            ],

            // CHEQUE
            [
                'nombre' => 'CHEQUE',
                'clave' => 'CHEQUE',
                'descripcion' => 'Pago con cheque nominativo',
                'metodo_pago' => 'cheque',
                'requiere_referencia' => true,
                'requiere_comprobante' => true,
                'activo' => true,
                'orden' => 9,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('formas_pago')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('formas_pago');
    }
}