<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRemainingTables extends Migration
{
    public function up()
    {
        // =====================================================================
        // TABLA: personas_morales
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'razon_social' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
                'comment' => 'Razón social de la empresa'
            ],
            'rfc' => [
                'type' => 'VARCHAR',
                'constraint' => 13,
                'comment' => 'RFC de la empresa'
            ],
            'representante_legal' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'comment' => 'Nombre del representante legal'
            ],
            'domicilio_fiscal' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Domicilio fiscal completo'
            ],
            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Teléfono principal'
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'comment' => 'Email corporativo'
            ],
            'giro_comercial' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'comment' => 'Giro comercial de la empresa'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Empresa activa'
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
        $this->forge->addKey(['rfc'], false, true); // Unique
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('personas_morales');

        // =====================================================================
        // TABLA: staff_empresas
        // =====================================================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'staff_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con staff'
            ],
            'empresa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Relación con empresas'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Relación activa'
            ],
            'fecha_asignacion' => [
                'type' => 'DATETIME',
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'Fecha de asignación'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['staff_id', 'empresa_id'], false, true); // Unique
        $this->forge->addKey(['staff_id']);
        $this->forge->addKey(['empresa_id']);
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('staff_empresas');

        // =====================================================================
        // TABLA: fuentes_informacion
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
                'comment' => 'Nombre de la fuente de información'
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción de la fuente'
            ],
            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['digital', 'tradicional', 'referido', 'evento', 'otro'],
                'default' => 'otro',
                'comment' => 'Tipo de fuente de información'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Fuente activa'
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
        $this->forge->addKey(['tipo']);
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('fuentes_informacion');

        // =====================================================================
        // TABLA: estados_civiles
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
                'constraint' => 50,
                'comment' => 'Nombre del estado civil'
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'comment' => 'Descripción del estado civil'
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Estado civil activo'
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
        
        $this->forge->createTable('estados_civiles');

        // =====================================================================
        // TABLA: settings (Configuración global del sistema)
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
                'constraint' => ['string', 'integer', 'boolean', 'json', 'text', 'decimal'],
                'default' => 'string',
                'comment' => 'Tipo de dato'
            ],
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Categoría de configuración'
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Descripción de la configuración'
            ],
            'editable' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => 'Configuración editable por usuarios'
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
        $this->forge->addKey(['clave'], false, true); // Unique
        $this->forge->addKey(['categoria']);
        $this->forge->addKey(['activo']);
        
        $this->forge->createTable('settings');

        // Insertar configuraciones básicas
        $this->db->table('settings')->insertBatch([
            [
                'clave' => 'sistema_nombre',
                'valor' => 'Sistema Inmobiliario ANVAR',
                'tipo' => 'string',
                'categoria' => 'general',
                'descripcion' => 'Nombre del sistema',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'empresa_principal',
                'valor' => '1',
                'tipo' => 'integer',
                'categoria' => 'general',
                'descripcion' => 'ID de la empresa principal',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'timezone',
                'valor' => 'America/Mexico_City',
                'tipo' => 'string',
                'categoria' => 'general',
                'descripcion' => 'Zona horaria del sistema',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'hubspot_enabled',
                'valor' => '1',
                'tipo' => 'boolean',
                'categoria' => 'integraciones',
                'descripcion' => 'Integración con HubSpot habilitada',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'clave' => 'google_drive_enabled',
                'valor' => '1',
                'tipo' => 'boolean',
                'categoria' => 'integraciones',
                'descripcion' => 'Integración con Google Drive habilitada',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);

        // Insertar estados civiles básicos
        $this->db->table('estados_civiles')->insertBatch([
            [
                'nombre' => 'Soltero(a)',
                'descripcion' => 'Estado civil soltero',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Casado(a)',
                'descripcion' => 'Estado civil casado',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Divorciado(a)',
                'descripcion' => 'Estado civil divorciado',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Viudo(a)',
                'descripcion' => 'Estado civil viudo',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Unión Libre',
                'descripcion' => 'Unión libre o concubinato',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);

        // Insertar fuentes de información básicas
        $this->db->table('fuentes_informacion')->insertBatch([
            [
                'nombre' => 'Facebook',
                'descripcion' => 'Redes sociales - Facebook',
                'tipo' => 'digital',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Instagram',
                'descripcion' => 'Redes sociales - Instagram',
                'tipo' => 'digital',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Google Ads',
                'descripcion' => 'Publicidad en Google',
                'tipo' => 'digital',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Referido de Cliente',
                'descripcion' => 'Cliente actual que refiere',
                'tipo' => 'referido',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Radio',
                'descripcion' => 'Publicidad en radio',
                'tipo' => 'tradicional',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Televisión',
                'descripcion' => 'Publicidad en televisión',
                'tipo' => 'tradicional',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Espectacular',
                'descripcion' => 'Publicidad exterior - Espectacular',
                'tipo' => 'tradicional',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'nombre' => 'Feria/Expo',
                'descripcion' => 'Evento - Feria o exposición',
                'tipo' => 'evento',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);

        // Insertar estados de lotes básicos
        $this->db->table('estados_lotes')->insertBatch([
            [
                'id' => 1,
                'nombre' => 'Disponible',
                'descripcion' => 'Lote disponible para venta',
                'color' => '#28a745',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'nombre' => 'Apartado',
                'descripcion' => 'Lote apartado por cliente',
                'color' => '#ffc107',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'nombre' => 'Vendido',
                'descripcion' => 'Lote vendido',
                'color' => '#dc3545',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'nombre' => 'No Disponible',
                'descripcion' => 'Lote no disponible para venta',
                'color' => '#6c757d',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('settings');
        $this->forge->dropTable('estados_civiles');
        $this->forge->dropTable('fuentes_informacion');
        $this->forge->dropTable('staff_empresas');
        $this->forge->dropTable('personas_morales');
    }
}