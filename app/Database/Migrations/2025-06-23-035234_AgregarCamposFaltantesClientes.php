<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarCamposFaltantesClientes extends Migration
{
    public function up()
    {
        // ===== AGREGAR SOLO CAMPOS FALTANTES A LA TABLA CLIENTES =====
        // No duplicar campos que van en las tablas relacionadas
        
        $fields = [];

        // Verificar y agregar solo campos que no existen
        if (!$this->db->fieldExists('fuente_informacion', 'clientes')) {
            $fields['fuente_informacion'] = [
                'type' => 'ENUM',
                'constraint' => ['referido', 'senalitica', 'facebook', 'navegador', 'instagram', 'agencia_inmobiliaria', 'espectacular', 'otro'],
                'default' => 'referido',
                'null' => true,
                'after' => 'otro_origen'
            ];
        }

        if (!$this->db->fieldExists('estado_civil', 'clientes')) {
            $fields['estado_civil'] = [
                'type' => 'ENUM',
                'constraint' => ['soltero', 'casado', 'union_libre', 'viudo'],
                'default' => 'soltero',
                'null' => true,
                'after' => 'estado_civil_id'
            ];
        }

        if (!$this->db->fieldExists('residente', 'clientes')) {
            $fields['residente'] = [
                'type' => 'ENUM',
                'constraint' => ['temporal', 'permanente'],
                'default' => 'permanente',
                'null' => true,
                'after' => 'tipo_residencia'
            ];
        }

        // Agregar campos solo si no existen
        if (!empty($fields)) {
            $this->forge->addColumn('clientes', $fields);
        }
        
        // ===== MODIFICAR CAMPO GENERO PARA QUE SEA COMPATIBLE =====
        $this->forge->modifyColumn('clientes', [
            'genero' => [
                'type' => 'ENUM',
                'constraint' => ['M', 'F', '1', '2'],
                'null' => true
            ]
        ]);

        log_message('info', 'Migración AgregarCamposFaltantesClientes ejecutada correctamente');
    }

    public function down()
    {
        // Eliminar campos solo si existen
        $campos = ['fuente_informacion', 'estado_civil', 'residente'];
        
        foreach ($campos as $campo) {
            if ($this->db->fieldExists($campo, 'clientes')) {
                $this->forge->dropColumn('clientes', $campo);
            }
        }
        
        // Revertir campo genero
        $this->forge->modifyColumn('clientes', [
            'genero' => [
                'type' => 'ENUM',
                'constraint' => ['M', 'F'],
                'null' => true
            ]
        ]);

        log_message('info', 'Rollback AgregarCamposFaltantesClientes ejecutado correctamente');
    }
}