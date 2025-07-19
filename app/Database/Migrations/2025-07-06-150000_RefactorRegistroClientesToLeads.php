<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorRegistroClientesToLeads extends Migration
{
    public function up()
    {
        // =====================================================================
        // PASO 1: RENOMBRAR TABLA registro_clientes A registro_leads
        // =====================================================================
        
        // Verificar si la tabla registro_clientes existe
        if ($this->db->tableExists('registro_clientes')) {
            // Renombrar la tabla
            $this->forge->renameTable('registro_clientes', 'registro_leads');
            
            log_message('info', 'Tabla registro_clientes renombrada a registro_leads');
        }
        
        // =====================================================================
        // PASO 2: RENOMBRAR TABLA registro_documentos A documentos_leads
        // =====================================================================
        
        // Verificar si la tabla registro_documentos existe
        if ($this->db->tableExists('registro_documentos')) {
            // Renombrar la tabla
            $this->forge->renameTable('registro_documentos', 'documentos_leads');
            
            log_message('info', 'Tabla registro_documentos renombrada a documentos_leads');
        }
        
        // =====================================================================
        // PASO 3: AGREGAR NUEVOS CAMPOS A registro_leads
        // =====================================================================
        
        // Agregar campos RFC y CURP separados
        $this->forge->addColumn('registro_leads', [
            'rfc' => [
                'type' => 'VARCHAR',
                'constraint' => 13,
                'null' => true,
                'after' => 'rfc_curp',
                'comment' => 'RFC del cliente (prioritario para nomenclatura)'
            ],
            'curp' => [
                'type' => 'VARCHAR',
                'constraint' => 18,
                'null' => true,
                'after' => 'rfc',
                'comment' => 'CURP del cliente (alternativo si no hay RFC)'
            ],
            'identificador_expediente' => [
                'type' => 'VARCHAR',
                'constraint' => 18,
                'null' => true,
                'after' => 'curp',
                'comment' => 'Identificador para expediente (RFC prioritario, CURP alternativo)'
            ],
            'google_drive_sync_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => true,
                'after' => 'google_drive_sync_error',
                'comment' => 'Intentos de sincronización con Google Drive'
            ],
            'google_drive_last_sync' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'after' => 'google_drive_sync_attempts',
                'comment' => 'Última sincronización con Google Drive'
            ],
            'fuente_registro' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'formulario_web',
                'after' => 'google_drive_last_sync',
                'comment' => 'Origen del registro'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'fuente_registro',
                'comment' => 'Lead activo'
            ],
            'convertido_a_cliente' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'activo',
                'comment' => 'Indica si el lead fue convertido a cliente'
            ],
            'fecha_conversion' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'convertido_a_cliente',
                'comment' => 'Fecha de conversión a cliente'
            ],
            'cliente_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'fecha_conversion',
                'comment' => 'ID del cliente generado tras conversión'
            ]
        ]);
        
        // =====================================================================
        // PASO 4: MODIFICAR TABLA documentos_leads
        // =====================================================================
        
        // Renombrar columna para mantener consistencia
        $this->forge->modifyColumn('documentos_leads', [
            'registro_cliente_id' => [
                'name' => 'registro_lead_id',
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Relación con registro_leads'
            ]
        ]);
        
        // Agregar nuevos campos para el manejo de archivos
        $this->forge->addColumn('documentos_leads', [
            'ruta_archivo_local' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'google_drive_view_url',
                'comment' => 'Ruta local del archivo en carpeta leads/'
            ],
            'validado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'ruta_archivo_local',
                'comment' => 'Documento validado por staff'
            ],
            'validado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'validado',
                'comment' => 'ID del usuario que validó'
            ],
            'fecha_validacion' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'validado_por',
                'comment' => 'Fecha de validación'
            ],
            'migrado_a_cliente' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'fecha_validacion',
                'comment' => 'Indica si fue migrado a documentos de cliente'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'migrado_a_cliente',
                'comment' => 'Documento activo'
            ]
        ]);
        
        // =====================================================================
        // PASO 5: AGREGAR ÍNDICES PARA OPTIMIZACIÓN
        // =====================================================================
        
        // Índices para registro_leads
        $this->forge->addKey(['rfc'], false, false, 'registro_leads');
        $this->forge->addKey(['curp'], false, false, 'registro_leads');
        $this->forge->addKey(['identificador_expediente'], false, false, 'registro_leads');
        $this->forge->addKey(['convertido_a_cliente'], false, false, 'registro_leads');
        $this->forge->addKey(['activo'], false, false, 'registro_leads');
        $this->forge->addKey(['google_drive_sync_status'], false, false, 'registro_leads');
        $this->forge->addKey(['hubspot_sync_status'], false, false, 'registro_leads');
        
        // Índices para documentos_leads
        $this->forge->addKey(['registro_lead_id'], false, false, 'documentos_leads');
        $this->forge->addKey(['validado'], false, false, 'documentos_leads');
        $this->forge->addKey(['migrado_a_cliente'], false, false, 'documentos_leads');
        $this->forge->addKey(['activo'], false, false, 'documentos_leads');
        
        // =====================================================================
        // PASO 6: ACTUALIZAR DATOS EXISTENTES
        // =====================================================================
        
        // Actualizar identificador_expediente con el valor de rfc_curp existente
        $this->db->query("
            UPDATE registro_leads 
            SET identificador_expediente = COALESCE(rfc_curp, CONCAT('LEAD_', id))
            WHERE identificador_expediente IS NULL
        ");
        
        log_message('info', 'Migración de registro_clientes a registro_leads completada');
    }
    
    public function down()
    {
        // Revertir cambios
        if ($this->db->tableExists('registro_leads')) {
            // Eliminar columnas agregadas
            $this->forge->dropColumn('registro_leads', [
                'rfc', 'curp', 'identificador_expediente', 
                'google_drive_sync_attempts', 'google_drive_last_sync',
                'fuente_registro', 'activo', 'convertido_a_cliente',
                'fecha_conversion', 'cliente_id'
            ]);
            
            // Renombrar de vuelta
            $this->forge->renameTable('registro_leads', 'registro_clientes');
        }
        
        if ($this->db->tableExists('documentos_leads')) {
            // Eliminar columnas agregadas
            $this->forge->dropColumn('documentos_leads', [
                'ruta_archivo_local', 'validado', 'validado_por',
                'fecha_validacion', 'migrado_a_cliente', 'activo'
            ]);
            
            // Renombrar columna de vuelta
            $this->forge->modifyColumn('documentos_leads', [
                'registro_lead_id' => [
                    'name' => 'registro_cliente_id',
                    'type' => 'INT',
                    'constraint' => 11,
                    'comment' => 'Relación con registro_clientes'
                ]
            ]);
            
            // Renombrar tabla de vuelta
            $this->forge->renameTable('documentos_leads', 'registro_documentos');
        }
        
        log_message('info', 'Rollback de migración registro_leads completado');
    }
}