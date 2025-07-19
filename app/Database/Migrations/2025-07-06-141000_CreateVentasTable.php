<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVentasTable extends Migration
{
    public function up()
    {
        // =====================================================================
        // TABLA: ventas
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folio' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'lotes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'cliente_secundario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Cliente 2 (cónyuge)'
            ],
            'vendedor_id' => [
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
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'total_pagado' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'anticipo' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00'
            ],
            'anticipo_pagado' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Monto pagado del enganche'
            ],
            'anticipo_credito' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '¿El enganche es a crédito?'
            ],
            'anticipo_cobrado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '¿Se cobró completamente el enganche?'
            ],
            'tipo_anticipo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Porcentaje vs monto fijo'
            ],
            'descuento' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Monto de descuento aplicado'
            ],
            'tipo_descuento' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Tipo de descuento'
            ],
            'es_credito' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'TRUE/FALSE venta a crédito'
            ],
            'cobrar_interes' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '¿Aplican intereses?'
            ],
            'intereses_acumulados' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Intereses moratorios acumulados'
            ],
            'area_vendida' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'comment' => 'Área del lote al momento de venta'
            ],
            'fecha_venta' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha de la venta'
            ],
            'hora_venta' => [
                'type' => 'TIME',
                'null' => true,
                'comment' => 'Hora de la venta'
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario que registró'
            ],
            'ip_registro' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'IP de registro'
            ],
            'forma_pago' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Forma de pago principal'
            ],
            'estatus' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'comment' => '1=Activo, 2=Cancelado'
            ],
            'telefono_contacto' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Teléfono de contacto'
            ],
            'archivo_contrato' => [
                'type' => 'VARCHAR',
                'constraint' => 1000,
                'null' => true,
                'comment' => 'Nombre del archivo del contrato'
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones de la venta'
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
        $this->forge->addKey(['folio']);
        $this->forge->addKey(['lotes_id']);
        $this->forge->addKey(['clientes_id']);
        $this->forge->addKey(['vendedor_id']);
        $this->forge->addKey(['empresas_id']);
        $this->forge->addKey(['proyectos_id']);
        $this->forge->addKey(['estatus']);
        $this->forge->addKey(['fecha_venta']);
        
        $this->forge->createTable('ventas');

        // =====================================================================
        // TABLA: ventas_amortizaciones
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
                'null' => true,
                'comment' => 'Relación con ventas'
            ],
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Cliente principal'
            ],
            'cliente_secundario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Cliente secundario (cónyuge)'
            ],
            'lotes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con lotes'
            ],
            'vendedor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con vendedor'
            ],
            'meses' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => '0.00',
                'comment' => 'Número de meses del plan'
            ],
            'recibido' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Monto recibido inicial'
            ],
            'fecha_inicial' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha inicial del plan'
            ],
            'financiar' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Monto a financiar'
            ],
            'meses_interes' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => '0.00',
                'comment' => 'Meses con interés'
            ],
            'interes' => [
                'type' => 'DECIMAL',
                'constraint' => '8,4',
                'default' => '0.0000',
                'comment' => 'Porcentaje de interés'
            ],
            'enganche' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Monto del enganche'
            ],
            'total' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Total del lote'
            ],
            'anualidades' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Anualidades especiales en formato JSON'
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
        $this->forge->addKey(['lotes_id']);
        
        $this->forge->createTable('ventas_amortizaciones');

        // =====================================================================
        // TABLA: ventas_documentos
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
            'tipo_documento' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Tipo de documento'
            ],
            'nombre_archivo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nombre del archivo'
            ],
            'ruta_archivo' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'comment' => 'Ruta completa del archivo'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción del documento'
            ],
            'subido_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Usuario que subió el documento'
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
        $this->forge->addKey(['tipo_documento']);
        
        $this->forge->createTable('ventas_documentos');

        // =====================================================================
        // TABLA: ventas_mensajes
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
                'comment' => 'Usuario que envió el mensaje'
            ],
            'mensaje' => [
                'type' => 'TEXT',
                'comment' => 'Contenido del mensaje'
            ],
            'tipo_mensaje' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'nota',
                'comment' => 'Tipo de mensaje (nota, seguimiento, etc.)'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['ventas_id']);
        $this->forge->addKey(['usuario_id']);
        $this->forge->addKey(['created_at']);
        
        $this->forge->createTable('ventas_mensajes');
    }

    public function down()
    {
        $this->forge->dropTable('ventas_mensajes');
        $this->forge->dropTable('ventas_documentos');
        $this->forge->dropTable('ventas_amortizaciones');
        $this->forge->dropTable('ventas');
    }
}