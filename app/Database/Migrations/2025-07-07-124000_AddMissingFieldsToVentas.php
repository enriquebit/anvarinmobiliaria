<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingFieldsToVentas extends Migration
{
    public function up()
    {
        // Solo agregar los 3 campos que realmente faltan
        $fields = [
            'redondear' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Toggle para redondear pagos mensuales',
                'after' => 'cobrar_interes'
            ],
            'comision_pagada' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'estatus'
            ],
            'fecha_pago_comision' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'comision_pagada'
            ]
        ];

        $this->forge->addColumn('ventas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('ventas', [
            'redondear',
            'comision_pagada', 
            'fecha_pago_comision'
        ]);
    }
}