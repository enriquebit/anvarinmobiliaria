<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEtapaProcesoToClientes extends Migration
{
    public function up()
    {
        // Agregar campos para el proceso de venta
        $fields = [
            'etapa_proceso' => [
                'type' => 'ENUM',
                'constraint' => ['interesado', 'calificado', 'documentacion', 'contrato', 'cerrado'],
                'default' => 'interesado',
                'after' => 'activo'
            ],
            'fecha_primer_contacto' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'etapa_proceso'
            ],
            'fecha_ultima_actividad' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'fecha_primer_contacto'
            ],
            'asesor_asignado' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'fecha_ultima_actividad'
            ],
            'notas_internas' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'asesor_asignado'
            ]
        ];

        $this->forge->addColumn('clientes', $fields);
        
        // Agregar índices para performance
        $this->forge->addKey(['etapa_proceso'], false, false, 'idx_clientes_etapa');
        $this->forge->addKey(['asesor_asignado'], false, false, 'idx_clientes_asesor'); 
        $this->forge->addKey(['fecha_primer_contacto'], false, false, 'idx_clientes_contacto');
        
        log_message('info', 'Migración AddEtapaProcesoToClientes ejecutada correctamente');
    }

    public function down()
    {
        // Eliminar índices primero
        if ($this->db->indexExists('clientes', 'idx_clientes_etapa')) {
            $this->forge->dropKey('clientes', 'idx_clientes_etapa');
        }
        if ($this->db->indexExists('clientes', 'idx_clientes_asesor')) {
            $this->forge->dropKey('clientes', 'idx_clientes_asesor');
        }
        if ($this->db->indexExists('clientes', 'idx_clientes_contacto')) {
            $this->forge->dropKey('clientes', 'idx_clientes_contacto');
        }
        
        // Eliminar campos
        $this->forge->dropColumn('clientes', [
            'etapa_proceso',
            'fecha_primer_contacto', 
            'fecha_ultima_actividad',
            'asesor_asignado',
            'notas_internas'
        ]);
        
        log_message('info', 'Rollback AddEtapaProcesoToClientes ejecutado correctamente');
    }
}