<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTareasTable extends Migration
{
    public function up()
    {
        // =====================================================================
        // TABLA: tareas
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'titulo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Título de la tarea'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción detallada de la tarea'
            ],
            'prioridad' => [
                'type' => 'ENUM',
                'constraint' => ['baja', 'media', 'alta', 'urgente'],
                'default' => 'media',
                'comment' => 'Prioridad de la tarea'
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'en_proceso', 'completada', 'cancelada'],
                'default' => 'pendiente',
                'comment' => 'Estado actual de la tarea'
            ],
            'fecha_vencimiento' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Fecha límite para completar la tarea'
            ],
            'fecha_completada' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Fecha cuando se completó la tarea'
            ],
            'asignado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID del admin que asignó la tarea'
            ],
            'asignado_a' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID del usuario (staff) asignado'
            ],
            'comentarios_admin' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comentarios del administrador'
            ],
            'comentarios_usuario' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comentarios del usuario asignado'
            ],
            'progreso' => [
                'type' => 'TINYINT',
                'constraint' => 3,
                'unsigned' => true,
                'default' => 0,
                'comment' => 'Porcentaje de progreso (0-100)'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP'
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP',
                'on_update' => 'CURRENT_TIMESTAMP'
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['asignado_por']);
        $this->forge->addKey(['asignado_a']);
        $this->forge->addKey(['estado']);
        $this->forge->addKey(['prioridad']);
        $this->forge->addKey(['fecha_vencimiento']);
        
        // Foreign Keys
        $this->forge->addForeignKey('asignado_por', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('asignado_a', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('tareas');

        // =====================================================================
        // TABLA: tareas_historial
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'tarea_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID de la tarea'
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'ID del usuario que hizo el cambio'
            ],
            'accion' => [
                'type' => 'ENUM',
                'constraint' => ['creada', 'asignada', 'iniciada', 'progreso_actualizado', 'completada', 'cancelada', 'comentario_agregado'],
                'comment' => 'Tipo de acción realizada'
            ],
            'estado_anterior' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'en_proceso', 'completada', 'cancelada'],
                'null' => true,
                'comment' => 'Estado anterior de la tarea'
            ],
            'estado_nuevo' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'en_proceso', 'completada', 'cancelada'],
                'null' => true,
                'comment' => 'Nuevo estado de la tarea'
            ],
            'progreso_anterior' => [
                'type' => 'TINYINT',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Progreso anterior'
            ],
            'progreso_nuevo' => [
                'type' => 'TINYINT',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Nuevo progreso'
            ],
            'comentario' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Comentario asociado al cambio'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP'
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['tarea_id']);
        $this->forge->addKey(['usuario_id']);
        $this->forge->addKey(['accion']);
        $this->forge->addKey(['created_at']);
        
        // Foreign Keys
        $this->forge->addForeignKey('tarea_id', 'tareas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('usuario_id', 'users', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('tareas_historial');
    }

    public function down()
    {
        $this->forge->dropTable('tareas_historial');
        $this->forge->dropTable('tareas');
    }
}