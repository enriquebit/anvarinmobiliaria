<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveActivoFromClientes extends Migration
{
    public function up()
    {
        // ✅ Eliminar campos redundantes de la tabla clientes
        $this->forge->dropColumn('clientes', [
            'activo',
            'fecha_activacion', 
            'activado_por'
        ]);
        
        echo "✅ Campos 'activo', 'fecha_activacion' y 'activado_por' eliminados de tabla 'clientes'\n";
        echo "🔧 Ahora se usará únicamente 'users.active' de Shield\n";
    }

    public function down()
    {
        // ✅ Recrear campos si necesitamos rollback
        $fields = [
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'DEPRECATED: Usar users.active en su lugar'
            ],
            'fecha_activacion' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'DEPRECATED: Usar users.updated_at en su lugar'
            ],
            'activado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'DEPRECATED: Funcionalidad movida a Shield'
            ]
        ];
        
        $this->forge->addColumn('clientes', $fields);
        
        echo "⚠️ Campos restaurados en tabla 'clientes' (ROLLBACK)\n";
    }
}