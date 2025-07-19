<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompleteDatabaseStructure extends Migration
{
    public function up()
    {
        // =====================================================================
        // TABLAS DE LOTES Y CATÁLOGOS
        // =====================================================================
        
        // TABLA: amenidades
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Nombre de la amenidad'],
            'icono' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'comment' => 'Clase de icono FontAwesome'],
            'descripcion' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Descripción de la amenidad'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado de la amenidad'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('activo');
        $this->forge->addKey('nombre');
        $this->forge->createTable('amenidades');

        // TABLA: categorias_lotes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Nombre de la categoría (PERIMETRAL, PREFERENCIAL, etc.)'],
            'descripcion' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Descripción de la categoría'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado de la categoría'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('activo');
        $this->forge->addKey('nombre');
        $this->forge->createTable('categorias_lotes');

        // TABLA: tipos_lotes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Tipo de lote (Lote, Casa, Departamento, Local)'],
            'descripcion' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Descripción del tipo'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado del tipo'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('activo');
        $this->forge->addKey('nombre');
        $this->forge->createTable('tipos_lotes');

        // TABLA: estados_lotes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Estado del lote'],
            'codigo' => ['type' => 'INT', 'comment' => 'Código legacy (0=Disponible, 1=Apartado, 2=Vendido, 3=Suspendido)'],
            'color' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#28a745', 'comment' => 'Color para representación visual'],
            'descripcion' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Descripción del estado'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado activo'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->addKey('activo');
        $this->forge->addKey('codigo');
        $this->forge->createTable('estados_lotes');

        // TABLA: manzanas
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Nombre de la manzana (1, 2, 3, A, B, etc.)'],
            'clave' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Clave auto-generada: {proyecto.clave}-{nombre}'],
            'descripcion' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Descripción opcional de la manzana'],
            'proyectos_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a tabla proyectos'],
            'longitud' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'Coordenada GPS longitud'],
            'latitud' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'Coordenada GPS latitud'],
            'color' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#3498db', 'comment' => 'Color hex para identificación visual'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado de la manzana (soft delete)'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['nombre', 'proyectos_id']);
        $this->forge->addKey('proyectos_id');
        $this->forge->addKey('activo');
        $this->forge->addKey('clave');
        $this->forge->createTable('manzanas');

        // TABLA: divisiones
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Nombre de la división'],
            'clave' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Clave auto-generada'],
            'descripcion' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Descripción de la división'],
            'empresas_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a empresas'],
            'proyectos_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a proyectos'],
            'color' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#3498db', 'comment' => 'Color hex para identificación visual'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado de la división'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['nombre', 'proyectos_id']);
        $this->forge->addKey('empresas_id');
        $this->forge->addKey('proyectos_id');
        $this->forge->addKey('activo');
        $this->forge->addKey('clave');
        $this->forge->createTable('divisiones');

        // TABLA: lotes
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'numero' => ['type' => 'VARCHAR', 'constraint' => 20, 'comment' => 'Número del lote dentro de la manzana'],
            'clave' => ['type' => 'VARCHAR', 'constraint' => 100, 'comment' => 'Clave única auto-generada'],
            'empresas_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a empresas'],
            'proyectos_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a proyectos'],
            'divisiones_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'FK a divisiones'],
            'manzanas_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a manzanas'],
            'categorias_lotes_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a categorias_lotes'],
            'tipos_lotes_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a tipos_lotes'],
            'estados_lotes_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1, 'comment' => 'FK a estados_lotes (1=Disponible)'],
            'area' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00', 'comment' => 'Área total en m²'],
            'frente' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '0.00', 'comment' => 'Metros de frente'],
            'fondo' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '0.00', 'comment' => 'Metros de fondo'],
            'lateral_izquierdo' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '0.00', 'comment' => 'Metros lateral izquierdo'],
            'lateral_derecho' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'default' => '0.00', 'comment' => 'Metros lateral derecho'],
            'construccion' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00', 'comment' => 'Área construida en m²'],
            'colindancia_norte' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Colindancia norte'],
            'colindancia_sur' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Colindancia sur'],
            'colindancia_oriente' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Colindancia oriente'],
            'colindancia_poniente' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'Colindancia poniente'],
            'precio_m2' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00', 'comment' => 'Precio por metro cuadrado'],
            'precio_total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00', 'comment' => 'Precio total (area * precio_m2)'],
            'coordenadas_poligono' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Coordenadas del polígono para mapas'],
            'longitud' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'Coordenada GPS longitud'],
            'latitud' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'Coordenada GPS latitud'],
            'descripcion' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Descripción adicional del lote'],
            'observaciones' => ['type' => 'TEXT', 'null' => true, 'comment' => 'Observaciones internas'],
            'color' => ['type' => 'VARCHAR', 'constraint' => 7, 'default' => '#3498db', 'comment' => 'Color para identificación visual'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado del lote (soft delete)'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['numero', 'manzanas_id']);
        $this->forge->addUniqueKey('clave');
        $this->forge->addKey('empresas_id');
        $this->forge->addKey('proyectos_id');
        $this->forge->addKey('divisiones_id');
        $this->forge->addKey('manzanas_id');
        $this->forge->addKey('categorias_lotes_id');
        $this->forge->addKey('tipos_lotes_id');
        $this->forge->addKey('estados_lotes_id');
        $this->forge->addKey('activo');
        $this->forge->addKey('clave');
        $this->forge->addKey('numero');
        $this->forge->createTable('lotes');

        // TABLA: lotes_amenidades
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'lotes_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a lotes'],
            'amenidades_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK a amenidades'],
            'cantidad' => ['type' => 'INT', 'default' => 1, 'comment' => 'Cantidad de la amenidad (ej: 3 recámaras)'],
            'activo' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => 'Estado de la relación'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['lotes_id', 'amenidades_id']);
        $this->forge->addKey('lotes_id');
        $this->forge->addKey('amenidades_id');
        $this->forge->addKey('activo');
        $this->forge->createTable('lotes_amenidades');

        // =====================================================================
        // TABLA: registro_clientes (agregar etapa_proceso)
        // =====================================================================
        $this->forge->addColumn('registro_clientes', [
            'etapa_proceso' => [
                'type' => 'ENUM',
                'constraint' => ['pendiente', 'calificado', 'enviar_documento_para_firma', 'documento_enviado_para_firma', 'documento_recibido_firmado'],
                'default' => 'pendiente',
                'null' => false,
                'after' => 'medio_contacto'
            ]
        ]);

        // =====================================================================
        // INSERTAR DATOS POR DEFECTO
        // =====================================================================
        $this->insertarDatosPorDefecto();

        // =====================================================================
        // CREAR FOREIGN KEYS (después de insertar datos)
        // =====================================================================
        $this->crearForeignKeys();
    }

    private function insertarDatosPorDefecto()
    {
        // Datos para amenidades
        $amenidades = [
            ['nombre' => 'Alberca', 'icono' => 'fas fa-swimming-pool', 'descripcion' => 'Alberca comunitaria', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Recámaras', 'icono' => 'fas fa-bed', 'descripcion' => 'Número de recámaras', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Baños', 'icono' => 'fas fa-bath', 'descripcion' => 'Número de baños completos', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Estacionamiento', 'icono' => 'fas fa-car', 'descripcion' => 'Cajón de estacionamiento', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Jardín', 'icono' => 'fas fa-leaf', 'descripcion' => 'Área de jardín privado', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Terraza', 'icono' => 'fas fa-home', 'descripcion' => 'Terraza o balcón', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Casa Club', 'icono' => 'fas fa-building', 'descripcion' => 'Casa club comunitaria', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Gimnasio', 'icono' => 'fas fa-dumbbell', 'descripcion' => 'Gimnasio comunitario', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Área de Juegos', 'icono' => 'fas fa-child', 'descripcion' => 'Área de juegos infantiles', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Seguridad 24/7', 'icono' => 'fas fa-shield-alt', 'descripcion' => 'Seguridad las 24 horas', 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('amenidades')->insertBatch($amenidades);

        // Datos para categorias_lotes
        $categorias = [
            ['nombre' => 'PERIMETRAL', 'descripcion' => 'Lote ubicado en el perímetro del proyecto', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'PERIMETRAL/ESQ', 'descripcion' => 'Lote perimetral en esquina (mayor valor)', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'PREFERENCIAL', 'descripcion' => 'Lote con ubicación preferencial', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'PREFERENCIAL/ESQ', 'descripcion' => 'Lote preferencial en esquina (máximo valor)', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'REGULAR', 'descripcion' => 'Lote estándar del proyecto', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'REGULAR/ESQ', 'descripcion' => 'Lote regular en esquina', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'COMERCIAL', 'descripcion' => 'Lote destinado para uso comercial', 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('categorias_lotes')->insertBatch($categorias);

        // Datos para tipos_lotes
        $tipos = [
            ['nombre' => 'Lote', 'descripcion' => 'Terreno sin construcción', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Casa', 'descripcion' => 'Lote con casa construida', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Departamento', 'descripcion' => 'Unidad habitacional en edificio', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Local Comercial', 'descripcion' => 'Espacio para negocio', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Townhouse', 'descripcion' => 'Casa adosada', 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('tipos_lotes')->insertBatch($tipos);

        // Datos para estados_lotes
        $estados = [
            ['nombre' => 'Disponible', 'codigo' => 0, 'color' => '#28a745', 'descripcion' => 'Lote disponible para venta', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Apartado', 'codigo' => 1, 'color' => '#ffc107', 'descripcion' => 'Lote reservado temporalmente', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Vendido', 'codigo' => 2, 'color' => '#dc3545', 'descripcion' => 'Lote vendido completamente', 'created_at' => date('Y-m-d H:i:s')],
            ['nombre' => 'Suspendido', 'codigo' => 3, 'color' => '#6c757d', 'descripcion' => 'Lote suspendido de venta', 'created_at' => date('Y-m-d H:i:s')],
        ];
        $this->db->table('estados_lotes')->insertBatch($estados);

        // =====================================================================
        // CREAR USUARIO SUPERADMIN
        // =====================================================================
        $this->crearUsuarioSuperadmin();
    }

    private function crearUsuarioSuperadmin()
    {
        // Crear usuario en tabla users
        $userData = [
            'id' => 1,
            'username' => null,
            'status' => null,
            'status_message' => null,
            'active' => 1,
            'last_active' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'deleted_at' => null,
        ];
        $this->db->table('users')->insert($userData);

        // Crear identidad de email/password
        $identityData = [
            'id' => 1,
            'user_id' => 1,
            'type' => 'email_password',
            'name' => null,
            'secret' => 'superadmin@nuevoanvar.test',
            'secret2' => password_hash('secret1234', PASSWORD_DEFAULT),
            'expires' => null,
            'extra' => null,
            'force_reset' => 0,
            'last_used_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('auth_identities')->insert($identityData);

        // Asignar grupo superadmin
        $groupData = [
            'id' => 1,
            'user_id' => 1,
            'group' => 'superadmin',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table('auth_groups_users')->insert($groupData);
    }

    private function crearForeignKeys()
    {
        // Foreign keys para manzanas
        if ($this->db->tableExists('proyectos')) {
            $this->forge->addForeignKey('proyectos_id', 'proyectos', 'id', 'CASCADE', 'CASCADE');
        }

        // Foreign keys para divisiones
        if ($this->db->tableExists('empresas') && $this->db->tableExists('proyectos')) {
            $this->forge->addForeignKey('empresas_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('proyectos_id', 'proyectos', 'id', 'CASCADE', 'CASCADE');
        }

        // Foreign keys para lotes (solo si las tablas existen)
        $tablesForLotes = ['empresas', 'proyectos', 'manzanas', 'categorias_lotes', 'tipos_lotes', 'estados_lotes'];
        $foreignKeys = [
            'empresas_id' => 'empresas',
            'proyectos_id' => 'proyectos', 
            'manzanas_id' => 'manzanas',
            'categorias_lotes_id' => 'categorias_lotes',
            'tipos_lotes_id' => 'tipos_lotes',
            'estados_lotes_id' => 'estados_lotes'
        ];

        foreach ($foreignKeys as $field => $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->addForeignKey($field, $table, 'id', 'CASCADE', 'CASCADE');
            }
        }

        // Foreign keys para lotes_amenidades
        $this->forge->addForeignKey('lotes_id', 'lotes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('amenidades_id', 'amenidades', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        // Eliminar en orden inverso para evitar problemas con foreign keys
        $this->forge->dropTable('lotes_amenidades', true);
        $this->forge->dropTable('lotes', true);
        $this->forge->dropTable('divisiones', true);
        $this->forge->dropTable('manzanas', true);
        $this->forge->dropTable('estados_lotes', true);
        $this->forge->dropTable('tipos_lotes', true);
        $this->forge->dropTable('categorias_lotes', true);
        $this->forge->dropTable('amenidades', true);
        
        // Remover columna de registro_clientes si existe
        if ($this->db->fieldExists('etapa_proceso', 'registro_clientes')) {
            $this->forge->dropColumn('registro_clientes', 'etapa_proceso');
        }
    }
}