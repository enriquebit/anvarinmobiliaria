<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegistroClientesTable extends Migration
{
    public function up()
    {
        // =====================================================================
        // TABLA: registro_clientes (Sistema de Leads)
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true
            ],
            'firstname' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'lastname' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'apellido_materno' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Apellido materno del titular'
            ],
            'rfc_curp' => [
                'type' => 'VARCHAR',
                'constraint' => 18,
                'null' => true,
                'comment' => 'RFC o CURP del cliente para nomenclatura de archivos'
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150
            ],
            'mobilephone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'WhatsApp del cliente'
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Teléfono para llamadas'
            ],
            'medio_de_contacto' => [
                'type' => 'ENUM',
                'constraint' => ['whatsapp', 'llamada_telefonica'],
                'null' => true,
                'comment' => 'Medio de contacto preferido del cliente'
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'desarrollo' => [
                'type' => 'ENUM',
                'constraint' => ['valle_natura', 'cordelia'],
                'comment' => 'Desarrollo de inversión'
            ],
            'manzana' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true
            ],
            'lote' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true
            ],
            'numero_casa_depto' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'Número de casa/departamento (Valle Natura)'
            ],
            'nombre_copropietario' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'parentesco_copropietario' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'agente_referido' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'etapa_proceso' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'calificado', 'enviar_documento_para_firma', 'documento_enviado_para_firma', 'documento_recibido_firmado'],
                'default' => 'pendiente',
                'comment' => 'Etapa actual del proceso del lead'
            ],
            // Integración HubSpot
            'hubspot_contact_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'hubspot_ticket_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'hubspot_sync_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending'
            ],
            'hubspot_sync_error' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'hubspot_sync_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'hubspot_last_sync' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ],
            // Integración Google Drive
            'google_drive_folder_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'google_drive_folder_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true
            ],
            'google_drive_sync_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending'
            ],
            'google_drive_sync_error' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'google_drive_sync_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'google_drive_last_sync' => [
                'type' => 'TIMESTAMP',
                'null' => true
            ],
            // Metadatos del registro
            'fuente_registro' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'formulario_web',
                'comment' => 'Origen del registro (formulario_web, importacion, etc.)'
            ],
            'ip_registro' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP desde donde se registró'
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'User agent del navegador'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Lead activo'
            ],
            'fecha_registro' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP'
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
        $this->forge->addKey(['email']);
        $this->forge->addKey(['telefono']);
        $this->forge->addKey(['desarrollo']);
        $this->forge->addKey(['etapa_proceso']);
        $this->forge->addKey(['hubspot_contact_id']);
        $this->forge->addKey(['hubspot_sync_status']);
        $this->forge->addKey(['google_drive_sync_status']);
        $this->forge->addKey(['fecha_registro']);
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('registro_clientes');

        // =====================================================================
        // TABLA: registro_documentos
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'registro_cliente_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Relación con registro_clientes'
            ],
            'tipo_documento' => [
                'type' => 'ENUM',
                'constraint' => ['ine_frontal', 'ine_trasera', 'pasaporte', 'comprobante_ingresos', 'comprobante_domicilio', 'acta_nacimiento', 'contrato_firmado', 'otro'],
                'comment' => 'Tipo de documento subido'
            ],
            'nombre_archivo_original' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nombre original del archivo'
            ],
            'nombre_archivo_sistema' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Nombre del archivo en el sistema'
            ],
            'ruta_archivo' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'comment' => 'Ruta completa del archivo'
            ],
            'google_drive_file_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'ID del archivo en Google Drive'
            ],
            'google_drive_file_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => 'URL del archivo en Google Drive'
            ],
            'tamaño_archivo' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Tamaño del archivo en bytes'
            ],
            'extension_archivo' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'Extensión del archivo'
            ],
            'validado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Documento validado por staff'
            ],
            'validado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID del usuario que validó'
            ],
            'fecha_validacion' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Fecha de validación'
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Observaciones sobre el documento'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Documento activo'
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
        $this->forge->addKey(['registro_cliente_id']);
        $this->forge->addKey(['tipo_documento']);
        $this->forge->addKey(['validado']);
        $this->forge->addKey(['google_drive_file_id']);
        
        // Foreign Key
        $this->forge->addForeignKey('registro_cliente_id', 'registro_clientes', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('registro_documentos');

        // =====================================================================
        // TABLA: registro_api_logs
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'registro_cliente_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Relación con registro_clientes'
            ],
            'servicio' => [
                'type' => 'ENUM',
                'constraint' => ['hubspot', 'google_drive'],
                'comment' => 'Servicio de API utilizado'
            ],
            'accion' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Acción realizada (create_contact, upload_file, etc.)'
            ],
            'request_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'Datos enviados en JSON'
            ],
            'response_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'Respuesta recibida en JSON'
            ],
            'status_code' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Código de estado HTTP'
            ],
            'success' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Operación exitosa'
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Mensaje de error si aplica'
            ],
            'execution_time' => [
                'type' => 'DECIMAL',
                'constraint' => '8,3',
                'null' => true,
                'comment' => 'Tiempo de ejecución en segundos'
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP desde donde se ejecutó'
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'User agent'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP'
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['registro_cliente_id']);
        $this->forge->addKey(['servicio']);
        $this->forge->addKey(['success']);
        $this->forge->addKey(['created_at']);
        
        $this->forge->createTable('registro_api_logs');

        // =====================================================================
        // TABLA: registro_configuracion
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'clave' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Clave de configuración'
            ],
            'valor' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Valor de configuración'
            ],
            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['string', 'integer', 'boolean', 'json', 'text'],
                'default' => 'string',
                'comment' => 'Tipo de dato'
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Descripción de la configuración'
            ],
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Categoría de configuración'
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
        $this->forge->addKey(['clave'], false, true); // Unique key
        $this->forge->addKey(['categoria']);
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('registro_configuracion');
    }

    public function down()
    {
        $this->forge->dropTable('registro_configuracion');
        $this->forge->dropTable('registro_api_logs');
        $this->forge->dropTable('registro_documentos');
        $this->forge->dropTable('registro_clientes');
    }
}