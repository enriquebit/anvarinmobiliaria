<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLotesTable extends Migration
{
    public function up()
    {
        // =====================================================================
        // TABLA: divisiones
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Nombre de la división/etapa'
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Código único de la división (ET-01)'
            ],
            'proyecto_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID del proyecto al que pertenece'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción de la división'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Estado de la división'
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
        $this->forge->addKey(['proyecto_id']);
        $this->forge->addKey(['codigo']);
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('divisiones');

        // =====================================================================
        // TABLA: estados_lotes
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Nombre del estado (Disponible, Vendido, Apartado, etc.)'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción del estado'
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'default' => '#ffffff',
                'comment' => 'Color hexadecimal para UI'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Estado activo'
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
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('estados_lotes');

        // =====================================================================
        // TABLA: lotes
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'numero' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Número del lote dentro de la manzana'
            ],
            'clave' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Clave única auto-generada'
            ],
            'empresas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a empresas'
            ],
            'proyectos_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a proyectos'
            ],
            'divisiones_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relación con tabla divisiones - requerido para nomenclatura de lotes'
            ],
            'manzanas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a manzanas'
            ],
            'categorias_lotes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a categorias_lotes'
            ],
            'tipos_lotes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a tipos_lotes'
            ],
            'estados_lotes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
                'comment' => 'FK a estados_lotes (1=Disponible)'
            ],
            // Medidas del lote
            'area' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'comment' => 'Área total en m²'
            ],
            'frente' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => '0.00',
                'comment' => 'Metros de frente'
            ],
            'fondo' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => '0.00',
                'comment' => 'Metros de fondo'
            ],
            'lateral_izquierdo' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => '0.00',
                'comment' => 'Metros lateral izquierdo'
            ],
            'lateral_derecho' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => '0.00',
                'comment' => 'Metros lateral derecho'
            ],
            'construccion' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'comment' => 'Área construida en m²'
            ],
            // Colindancias
            'colindancia_norte' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Colindancia norte'
            ],
            'colindancia_sur' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Colindancia sur'
            ],
            'colindancia_oriente' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Colindancia oriente'
            ],
            'colindancia_poniente' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Colindancia poniente'
            ],
            // Precios
            'precio_lista' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Precio de lista'
            ],
            'precio_venta' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Precio de venta actual'
            ],
            'precio_metro' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'comment' => 'Precio por metro cuadrado'
            ],
            // Información adicional
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones del lote'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Estado del lote'
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
        $this->forge->addKey(['clave'], false, true); // Unique
        $this->forge->addKey(['empresas_id']);
        $this->forge->addKey(['proyectos_id']);
        $this->forge->addKey(['divisiones_id']);
        $this->forge->addKey(['manzanas_id']);
        $this->forge->addKey(['categorias_lotes_id']);
        $this->forge->addKey(['tipos_lotes_id']);
        $this->forge->addKey(['estados_lotes_id']);
        $this->forge->addKey(['numero']);
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('lotes');

        // =====================================================================
        // TABLA: lotes_amenidades
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'lotes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a lotes'
            ],
            'amenidades_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK a amenidades'
            ],
            'cantidad' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'comment' => 'Cantidad de la amenidad (ej: 3 recámaras)'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Estado de la relación'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['lotes_id', 'amenidades_id'], false, true); // Unique
        $this->forge->addKey(['lotes_id']);
        $this->forge->addKey(['amenidades_id']);
        
        $this->forge->createTable('lotes_amenidades');

        // =====================================================================
        // TABLA: cuentas_bancarias
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
            'banco' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Nombre del banco'
            ],
            'numero_cuenta' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Número de cuenta'
            ],
            'clabe' => [
                'type' => 'VARCHAR',
                'constraint' => 18,
                'null' => true,
                'comment' => 'CLABE interbancaria'
            ],
            'tipo_cuenta' => [
                'type' => 'ENUM',
                'constraint' => ['cheques', 'ahorros', 'inversion'],
                'default' => 'cheques',
                'comment' => 'Tipo de cuenta bancaria'
            ],
            'moneda' => [
                'type' => 'VARCHAR',
                'constraint' => 3,
                'default' => 'MXN',
                'comment' => 'Moneda de la cuenta'
            ],
            'titular' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nombre del titular de la cuenta'
            ],
            'sucursal' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Sucursal del banco'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Cuenta activa'
            ],
            'predeterminada' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Cuenta predeterminada para pagos'
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones de la cuenta'
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
        $this->forge->addKey(['numero_cuenta']);
        $this->forge->addKey(['clabe']);
        $this->forge->addKey(['activo']);
        $this->forge->addKey(['predeterminada']);
        
        $this->forge->createTable('cuentas_bancarias');

        // =====================================================================
        // TABLA: comisiones
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'venta_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con ventas'
            ],
            'vendedor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con vendedor (users)'
            ],
            'tipo_comision' => [
                'type' => 'ENUM',
                'constraint' => ['venta', 'apartado', 'referido', 'bono'],
                'default' => 'venta',
                'comment' => 'Tipo de comisión'
            ],
            'monto_venta' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Monto de la venta base'
            ],
            'porcentaje_comision' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => '0.00',
                'comment' => 'Porcentaje de comisión aplicado'
            ],
            'monto_comision' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => '0.00',
                'comment' => 'Monto de comisión calculado'
            ],
            'fecha_generacion' => [
                'type' => 'DATE',
                'comment' => 'Fecha de generación de la comisión'
            ],
            'fecha_pago' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha de pago de la comisión'
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'pagada', 'cancelada'],
                'default' => 'pendiente',
                'comment' => 'Estado de la comisión'
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones de la comisión'
            ],
            'pagado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario que pagó la comisión'
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
        $this->forge->addKey(['venta_id']);
        $this->forge->addKey(['vendedor_id']);
        $this->forge->addKey(['estado']);
        $this->forge->addKey(['fecha_generacion']);
        
        $this->forge->createTable('comisiones');
    }

    public function down()
    {
        $this->forge->dropTable('comisiones');
        $this->forge->dropTable('cuentas_bancarias');
        $this->forge->dropTable('lotes_amenidades');
        $this->forge->dropTable('lotes');
        $this->forge->dropTable('estados_lotes');
        $this->forge->dropTable('divisiones');
    }
}