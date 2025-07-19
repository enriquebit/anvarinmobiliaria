<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateVentasAddMissingFields extends Migration
{
    public function up()
    {
        // Agregar campos faltantes a la tabla ventas
        $fields = [
            
            // Información de empresa y proyecto
            'empresas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'vendedor_id'
            ],
            'proyectos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'empresas_id'
            ],
            'manzanas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'proyectos_id'
            ],
            
            // Información financiera
            'anticipo_pagado' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'after' => 'anticipo'
            ],
            'anticipo_cobrado' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'anticipo_pagado'
            ],
            'es_credito' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'total_pagado'
            ],
            'cobrar_interes' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'es_credito'
            ],
            'redondear' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Toggle para redondear pagos mensuales',
                'after' => 'cobrar_interes'
            ],
            
            // Comisiones
            'comision_apartado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'after' => 'redondear'
            ],
            'comision_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'after' => 'comision_apartado'
            ],
            'comision_pagada' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'comision_total'
            ],
            'fecha_pago_comision' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'comision_pagada'
            ],
            
            // Información adicional
            'area_vendida' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'after' => 'fecha_pago_comision'
            ],
            'fecha_venta' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'area_vendida'
            ],
            'hora_venta' => [
                'type' => 'TIME',
                'null' => true,
                'after' => 'fecha_venta'
            ],
            'fecha_confirmacion' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'hora_venta'
            ],
            
            // Cancelación
            'texto_cancelacion' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'fecha_limite_apartado'
            ],
            
            // Auditoría
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario que registró la venta',
                'after' => 'texto_cancelacion'
            ],
            'ip_registro' => [
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
                'after' => 'usuario_id'
            ],
            'origen_venta' => [
                'type' => 'ENUM',
                'constraint' => ['sistema', 'api', 'importacion'],
                'default' => 'sistema',
                'after' => 'ip_registro'
            ],
            
            // Contacto
            'telefono_contacto' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'after' => 'origen_venta'
            ]
        ];

        $this->forge->addColumn('ventas', $fields);

        // Agregar índices
        $this->forge->addKey('empresas_id');
        $this->forge->addKey('proyectos_id');
        $this->forge->addKey('manzanas_id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('fecha_venta');
        $this->forge->addKey('estado');
        $this->forge->addKey('es_credito');
        
        // Agregar foreign keys
        $this->forge->addForeignKey('empresas_id', 'empresas', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('proyectos_id', 'proyectos', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('manzanas_id', 'manzanas', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('usuario_id', 'users', 'id', 'SET NULL', 'SET NULL');
    }

    public function down()
    {
        // Eliminar foreign keys primero
        $this->forge->dropForeignKey('ventas', 'ventas_empresas_id_foreign');
        $this->forge->dropForeignKey('ventas', 'ventas_proyectos_id_foreign');
        $this->forge->dropForeignKey('ventas', 'ventas_manzanas_id_foreign');
        $this->forge->dropForeignKey('ventas', 'ventas_usuario_id_foreign');
        
        // Eliminar columnas
        $this->forge->dropColumn('ventas', [
            'empresas_id',
            'proyectos_id',
            'manzanas_id',
            'anticipo_pagado',
            'anticipo_cobrado',
            'es_credito',
            'cobrar_interes',
            'redondear',
            'comision_apartado',
            'comision_total',
            'comision_pagada',
            'fecha_pago_comision',
            'area_vendida',
            'fecha_venta',
            'hora_venta',
            'fecha_confirmacion',
            'texto_cancelacion',
            'usuario_id',
            'ip_registro',
            'origen_venta',
            'telefono_contacto'
        ]);
    }
}