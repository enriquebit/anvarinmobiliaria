<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ActualizarTablasRelacionadas extends Migration
{
    public function up()
    {
        // ===== ACTUALIZAR TABLA DIRECCIONES_CLIENTES =====
        $fieldsDir = [];
        
        if (!$this->db->fieldExists('tiempo_radicando', 'direcciones_clientes')) {
            $fieldsDir['tiempo_radicando'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'estado'
            ];
        }

        if (!$this->db->fieldExists('tipo_residencia', 'direcciones_clientes')) {
            $fieldsDir['tipo_residencia'] = [
                'type' => 'ENUM',
                'constraint' => ['propia', 'renta', 'hipoteca', 'padres', 'otro'],
                'default' => 'propia',
                'null' => true,
                'after' => 'tiempo_radicando'
            ];
        }

        if (!$this->db->fieldExists('residente', 'direcciones_clientes')) {
            $fieldsDir['residente'] = [
                'type' => 'ENUM',
                'constraint' => ['temporal', 'permanente'],
                'default' => 'permanente',
                'null' => true,
                'after' => 'tipo_residencia'
            ];
        }

        if (!empty($fieldsDir)) {
            $this->forge->addColumn('direcciones_clientes', $fieldsDir);
        }

        // ===== ACTUALIZAR TABLA INFORMACION_LABORAL_CLIENTES =====
        // Esta tabla ya tiene todos los campos necesarios, solo verificamos

        // ===== ACTUALIZAR TABLA REFERENCIAS_CLIENTES =====
        $fieldsRef = [];
        
        if (!$this->db->fieldExists('genero', 'referencias_clientes')) {
            $fieldsRef['genero'] = [
                'type' => 'ENUM',
                'constraint' => ['M', 'F'],
                'null' => true,
                'after' => 'tipo'
            ];
        }

        if (!empty($fieldsRef)) {
            $this->forge->addColumn('referencias_clientes', $fieldsRef);
        }

        log_message('info', 'Migración ActualizarTablasRelacionadas ejecutada correctamente');
    }

    public function down()
    {
        // Revertir direcciones_clientes
        $camposDir = ['tiempo_radicando', 'tipo_residencia', 'residente'];
        foreach ($camposDir as $campo) {
            if ($this->db->fieldExists($campo, 'direcciones_clientes')) {
                $this->forge->dropColumn('direcciones_clientes', $campo);
            }
        }

        // Revertir referencias_clientes
        if ($this->db->fieldExists('genero', 'referencias_clientes')) {
            $this->forge->dropColumn('referencias_clientes', 'genero');
        }

        log_message('info', 'Rollback ActualizarTablasRelacionadas ejecutado correctamente');
    }
}