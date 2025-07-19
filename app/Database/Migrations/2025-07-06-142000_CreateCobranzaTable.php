<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCobranzaTable extends Migration
{
    public function up()
    {
        // =====================================================================
        // TABLA: plan_pagos
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'ventas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'empresas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con empresas'
            ],
            'proyectos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con proyectos'
            ],
            'manzanas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con manzanas'
            ],
            'lotes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con lotes'
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con clientes'
            ],
            'vendedor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con vendedor (users)'
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario que registró'
            ],
            'fecha_registro' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha de registro'
            ],
            'hora_registro' => [
                'type' => 'TIME',
                'null' => true,
                'comment' => 'Hora de registro'
            ],
            'ip_registro' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'IP de registro'
            ],
            'dias_credito' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Días de crédito'
            ],
            'fecha_referencia' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha de referencia para cálculos'
            ],
            'plazo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'M',
                'comment' => 'Descripción del plazo (M=mensual, A=anual)'
            ],
            'parcialidad' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'comment' => 'Número de parcialidad'
            ],
            'fecha_vencimiento' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha de vencimiento'
            ],
            'importe' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Importe de la parcialidad'
            ],
            'interes' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Interés de la parcialidad'
            ],
            'total_parcialidad' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Total de la parcialidad (importe + interés)'
            ],
            'saldo_pendiente' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Saldo pendiente de pago'
            ],
            'estatus' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'pendiente',
                'comment' => 'Estado del plan de pago'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['ventas_id']);
        $this->forge->addKey(['clientes_id']);
        $this->forge->addKey(['fecha_vencimiento']);
        $this->forge->addKey(['estatus']);
        
        $this->forge->createTable('plan_pagos');

        // =====================================================================
        // TABLA: pagos
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'ventas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'plan_pagos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario que registró el pago'
            ],
            'ip_registro' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'IP de registro'
            ],
            'fecha_pago' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha del pago (separada de created_at)'
            ],
            'hora_pago' => [
                'type' => 'TIME',
                'null' => true,
                'comment' => 'Hora del pago'
            ],
            'cuenta_bancaria_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con cuentas_bancarias - Cuenta destino del pago'
            ],
            'tipo_pago' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'normal',
                'comment' => 'Tipo de pago (normal, adelanto, etc.)'
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'efectivo' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'transferencia' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'cheque' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'tarjeta' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'referencia' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones del pago'
            ],
            'comprobante' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Archivo de comprobante'
            ],
            'estatus' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'activo',
                'comment' => 'Estado del pago'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['ventas_id']);
        $this->forge->addKey(['plan_pagos_id']);
        $this->forge->addKey(['fecha_pago']);
        $this->forge->addKey(['cuenta_bancaria_id']);
        
        $this->forge->createTable('pagos');

        // =====================================================================
        // TABLA: configuracion_cobranza
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'empresa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con empresas'
            ],
            'dias_gracia' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Días de gracia antes de aplicar intereses'
            ],
            'interes_moratorio' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => '0.00',
                'comment' => 'Porcentaje de interés moratorio mensual'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Configuración activa'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['empresa_id']);
        
        $this->forge->createTable('configuracion_cobranza');

        // =====================================================================
        // TABLA: cobranza_intereses
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'ventas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con ventas'
            ],
            'plan_pagos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con plan de pagos'
            ],
            'fecha_calculo' => [
                'type' => 'DATE',
                'comment' => 'Fecha del cálculo de intereses'
            ],
            'dias_atraso' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Días de atraso'
            ],
            'saldo_vencido' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Saldo vencido'
            ],
            'tasa_interes' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => '0.00',
                'comment' => 'Tasa de interés aplicada'
            ],
            'interes_calculado' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Interés calculado'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['ventas_id']);
        $this->forge->addKey(['plan_pagos_id']);
        $this->forge->addKey(['fecha_calculo']);
        
        $this->forge->createTable('cobranza_intereses');

        // =====================================================================
        // TABLA: historial_cobranza
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'ventas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con ventas'
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Usuario que registró la acción'
            ],
            'accion' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Tipo de acción realizada'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción de la acción'
            ],
            'monto_involucrado' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'comment' => 'Monto involucrado en la acción'
            ],
            'fecha_accion' => [
                'type' => 'DATETIME',
                'comment' => 'Fecha y hora de la acción'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['ventas_id']);
        $this->forge->addKey(['usuario_id']);
        $this->forge->addKey(['fecha_accion']);
        
        $this->forge->createTable('historial_cobranza');
    }

    public function down()
    {
        $this->forge->dropTable('historial_cobranza');
        $this->forge->dropTable('cobranza_intereses');
        $this->forge->dropTable('configuracion_cobranza');
        $this->forge->dropTable('pagos');
        $this->forge->dropTable('plan_pagos');
    }
}