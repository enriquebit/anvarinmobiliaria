<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInformacionLaboralClientesTable extends Migration
{
    public function up()
    {
        try {
            log_message('info', 'Iniciando creación de tabla informacion_laboral_clientes');
            
            // Verificar si la tabla ya existe
            if ($this->db->tableExists('informacion_laboral_clientes')) {
                log_message('warning', 'La tabla informacion_laboral_clientes ya existe');
                return;
            }

            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'cliente_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                ],
                'nombre_empresa' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 300,
                    'null'       => true,
                ],
                'puesto_cargo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 200,
                    'null'       => true,
                ],
                'antiguedad' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'telefono_trabajo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 15,
                    'null'       => true,
                ],
                'direccion_trabajo' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'activo' => [
                    'type'    => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('cliente_id');
            
            // Verificar si la tabla clientes existe antes de crear la FK
            if (!$this->db->tableExists('clientes')) {
                throw new \Exception('La tabla clientes no existe. Debe crearse primero.');
            }
            
            $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE');
            
            $result = $this->forge->createTable('informacion_laboral_clientes');
            
            if ($result) {
                log_message('info', 'Tabla informacion_laboral_clientes creada exitosamente');
            } else {
                log_message('error', 'Error al crear la tabla informacion_laboral_clientes');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error en migración informacion_laboral_clientes: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down()
    {
        try {
            log_message('info', 'Eliminando tabla informacion_laboral_clientes');
            $this->forge->dropTable('informacion_laboral_clientes');
            log_message('info', 'Tabla informacion_laboral_clientes eliminada exitosamente');
        } catch (\Exception $e) {
            log_message('error', 'Error al eliminar tabla informacion_laboral_clientes: ' . $e->getMessage());
            throw $e;
        }
    }
}