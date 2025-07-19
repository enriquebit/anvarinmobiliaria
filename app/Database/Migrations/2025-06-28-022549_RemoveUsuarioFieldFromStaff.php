<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveUsuarioFieldFromStaff extends Migration
{
    public function up()
    {
        // ====================================================================
        // ELIMINAR CAMPO USUARIO - SOLO EMAIL COMO IDENTIFICADOR
        // ====================================================================
        
        log_message('info', '🔧 [STAFF FIX] Eliminando campo usuario redundante...');
        
        // Verificar si el campo existe antes de eliminarlo
        if ($this->db->fieldExists('usuario', 'staff')) {
            $this->forge->dropColumn('staff', 'usuario');
            log_message('info', '✅ [STAFF FIX] Campo usuario eliminado exitosamente');
        } else {
            log_message('info', '⚠️ [STAFF FIX] Campo usuario no existe, omitiendo...');
        }
        
        log_message('info', '✅ [STAFF FIX] Corrección completada - Solo email como identificador');
    }
    
    public function down()
    {
        // ====================================================================
        // RESTAURAR CAMPO USUARIO SI ES NECESARIO
        // ====================================================================
        
        log_message('info', '🔄 [STAFF FIX] Restaurando campo usuario...');
        
        $this->forge->addColumn('staff', [
            'usuario' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'after' => 'email',
                'comment' => 'Username único para login',
            ]
        ]);
        
        log_message('info', '✅ [STAFF FIX] Campo usuario restaurado');
    }
}