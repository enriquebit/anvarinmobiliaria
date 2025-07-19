<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group - Los nuevos registros serán clientes
     * --------------------------------------------------------------------
     */
    public string $defaultGroup = 'cliente';

    /**
     * --------------------------------------------------------------------
     * Groups - SOLO 2 ROLES SIMPLES
     * --------------------------------------------------------------------
     */
 public array $groups = [
        // ============ ROLES ADMINISTRATIVOS ============
        'superadmin' => [
            'title' => 'Super Administrador',
            'description' => 'Acceso Total al sistema - Configuración y gestión completa',
        ],
        'admin' => [
            'title' => 'Administrador',
            'description' => 'Gestión completa excepto configuración del sistema',
        ],
        'supervendedor' => [
            'title' => 'Super Vendedor',
            'description' => 'Vendedor con permisos avanzados y supervisión',
        ],
        'vendedor' => [
            'title' => 'Vendedor',
            'description' => 'Gestión de ventas, clientes y contratos',
        ],
        'subvendedor' => [
            'title' => 'Sub-Vendedor',
            'description' => 'Vendedor con permisos limitados y supervisión',
        ],
        'visor' => [
            'title' => 'Visor',
            'description' => 'Solo lectura de información autorizada',
        ],
        
        // ============ CLIENTES ============
        'cliente' => [
            'title' => 'Cliente',
            'description' => 'Solo ve su información personal',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions - SOLO 4 PERMISOS CRUD BÁSICOS
     * --------------------------------------------------------------------
     */
    public array $permissions = [
        // ============ VENTAS ============
        'ventas.create'      => 'Crear ventas',
        'ventas.read'        => 'Ver ventas',
        'ventas.update'      => 'Editar ventas',
        'ventas.delete'      => 'Eliminar ventas',
        
        // ============ EXPEDIENTES ============
        'expedientes.create' => 'Crear expedientes',
        'expedientes.read'   => 'Ver expedientes',
        'expedientes.update' => 'Editar expedientes',
        'expedientes.delete' => 'Eliminar expedientes',
        
        // ============ INGRESOS ============
        'ingresos.create'    => 'Crear ingresos',
        'ingresos.read'      => 'Ver ingresos',
        'ingresos.update'    => 'Editar ingresos',
        'ingresos.delete'    => 'Eliminar ingresos',
        
        // ============ FLUJOS (RESÚMENES) ============
        'flujos.read'        => 'Ver flujos de caja',
        'flujos.export'      => 'Exportar flujos',
        
        // ============ EGRESOS ============
        'egresos.create'     => 'Crear egresos',
        'egresos.read'       => 'Ver egresos',
        'egresos.update'     => 'Editar egresos',
        'egresos.delete'     => 'Eliminar egresos',
        
        // ============ COBRANZA ============
        'cobranza.create'    => 'Crear cobranza',
        'cobranza.read'      => 'Ver cobranza',
        'cobranza.update'    => 'Editar cobranza',
        'cobranza.delete'    => 'Eliminar cobranza',
        
        // ============ CLIENTES ============
        'clientes.create'    => 'Crear clientes',
        'clientes.read'      => 'Ver clientes',
        'clientes.update'    => 'Editar clientes',
        'clientes.delete'    => 'Eliminar clientes',
        
        // ============ COMISIONES ============
        'comisiones.create'  => 'Crear comisiones',
        'comisiones.read'    => 'Ver comisiones',
        'comisiones.update'  => 'Editar comisiones',
        'comisiones.delete'  => 'Eliminar comisiones',
        
        // ============ EMPRESAS ============
        'empresas.create'    => 'Crear empresas',
        'empresas.read'      => 'Ver empresas',
        'empresas.update'    => 'Editar empresas',
        'empresas.delete'    => 'Eliminar empresas',
        
        // ============ PROYECTOS ============
        'proyectos.create'   => 'Crear proyectos',
        'proyectos.read'     => 'Ver proyectos',
        'proyectos.update'   => 'Editar proyectos',
        'proyectos.delete'   => 'Eliminar proyectos',
        
        // ============ LOTES ============
        'lotes.create'       => 'Crear lotes',
        'lotes.read'         => 'Ver lotes',
        'lotes.update'       => 'Editar lotes',
        'lotes.delete'       => 'Eliminar lotes',
        
        // ============ GRUPOS ============
        'grupos.create'      => 'Crear grupos',
        'grupos.read'        => 'Ver grupos',
        'grupos.update'      => 'Editar grupos',
        'grupos.delete'      => 'Eliminar grupos',
        
        // ============ MANZANAS ============
        'manzanas.create'    => 'Crear manzanas',
        'manzanas.read'      => 'Ver manzanas',
        'manzanas.update'    => 'Editar manzanas',
        'manzanas.delete'    => 'Eliminar manzanas',
        
        // ============ TIPOS ============
        'tipos.create'       => 'Crear tipos',
        'tipos.read'         => 'Ver tipos',
        'tipos.update'       => 'Editar tipos',
        'tipos.delete'       => 'Eliminar tipos',
        
        // ============ DIVISIONES ============
        'divisiones.create'  => 'Crear divisiones',
        'divisiones.read'    => 'Ver divisiones',
        'divisiones.update'  => 'Editar divisiones',
        'divisiones.delete'  => 'Eliminar divisiones',
        
        // ============ AMENIDADES ============
        'amenidades.create'  => 'Crear amenidades',
        'amenidades.read'    => 'Ver amenidades',
        'amenidades.update'  => 'Editar amenidades',
        'amenidades.delete'  => 'Eliminar amenidades',
        
        // ============ CATEGORÍAS ============
        'categorias.create'  => 'Crear categorías',
        'categorias.read'    => 'Ver categorías',
        'categorias.update'  => 'Editar categorías',
        'categorias.delete'  => 'Eliminar categorías',
        
        // ============ CUENTAS ============
        'cuentas.create'     => 'Crear cuentas',
        'cuentas.read'       => 'Ver cuentas',
        'cuentas.update'     => 'Editar cuentas',
        'cuentas.delete'     => 'Eliminar cuentas',
        
        // ============ USUARIOS ============
        'usuarios.create'    => 'Crear usuarios',
        'usuarios.read'      => 'Ver usuarios',
        'usuarios.update'    => 'Editar usuarios',
        'usuarios.delete'    => 'Eliminar usuarios',
        
        // ============ ESTADÍSTICAS ============
        'estadisticas.read'  => 'Ver estadísticas',
        'estadisticas.export'=> 'Exportar estadísticas',
        
        // ============ REPORTES ============
        'reportes.ventas'     => 'Reportes de ventas',
        'reportes.ingresos'   => 'Reportes de ingresos',
        'reportes.flujos'     => 'Reportes de flujos de caja',
        'reportes.cobranza'   => 'Reportes de cobranza',
        'reportes.comisiones' => 'Reportes de comisiones',
        'reportes.apartados'  => 'Reportes de apartados',
        'reportes.export'     => 'Exportar reportes',
        
        // ============ CONTRATOS ============
        'contratos.create'   => 'Crear contratos',
        'contratos.read'     => 'Ver contratos',
        'contratos.update'   => 'Editar contratos',
        'contratos.delete'   => 'Eliminar contratos',
        'contratos.sign'     => 'Firmar contratos',
        
        // ============ PERMISOS ESPECIALES ============
        'profile.view'       => 'Ver perfil propio',
        'profile.update'     => 'Editar perfil propio',
        'system.config'      => 'Configuración del sistema',
        'system.backup'      => 'Respaldos del sistema',
        'system.logs'        => 'Ver logs del sistema',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix - ASIGNACIÓN GRANULAR POR ROLES
     * --------------------------------------------------------------------
     */
    public array $matrix = [
        // ============ SUPER ADMINISTRADOR - ACCESO TOTAL ============
        'superadmin' => [
            '*', // Wildcard - Acceso a TODO
        ],
        
        // ============ ADMINISTRADOR - GESTIÓN COMPLETA ============
        'admin' => [
            // Gestión completa de todos los módulos
            'ventas.*',
            'expedientes.*',
            'ingresos.*',
            'flujos.*',
            'egresos.*',
            'cobranza.*',
            'clientes.*',
            'comisiones.*',
            'empresas.*',
            'proyectos.*',
            'lotes.*',
            'grupos.*',
            'manzanas.*',
            'tipos.*',
            'divisiones.*',
            'amenidades.*',
            'categorias.*',
            'cuentas.*',
            
            // Usuarios (excepto eliminar superadmin)
            'usuarios.create',
            'usuarios.read',
            'usuarios.update',
            
            // Estadísticas y reportes
            'estadisticas.*',
            'reportes.*',
            
            // Contratos
            'contratos.*',
            
            // Perfil
            'profile.*',
        ],
        
        // ============ SUPER VENDEDOR - VENTAS + SUPERVISIÓN ============
        'supervendedor' => [
            // Gestión completa de ventas
            'ventas.*',
            'expedientes.*',
            'clientes.*',
            'contratos.*',
            
            // Supervisión de comisiones
            'comisiones.read',
            'comisiones.update',
            
            // Proyectos y lotes
            'proyectos.read',
            'proyectos.update',
            'lotes.*',
            'grupos.read',
            'manzanas.read',
            'amenidades.read',
            
            // Algunos ingresos y flujos
            'ingresos.read',
            'ingresos.create',
            'flujos.read',
            'cobranza.read',
            'cobranza.update',
            
            // Reportes de ventas
            'reportes.ventas',
            'reportes.ingresos',
            'reportes.comisiones',
            'reportes.apartados',
            'estadisticas.read',
            
            // Usuarios limitado
            'usuarios.read',
            
            // Perfil
            'profile.*',
        ],
        
        // ============ VENDEDOR - VENTAS Y CLIENTES ============
        'vendedor' => [
            // Ventas básicas
            'ventas.create',
            'ventas.read',
            'ventas.update',
            
            // Expedientes
            'expedientes.*',
            
            // Clientes
            'clientes.*',
            
            // Contratos
            'contratos.create',
            'contratos.read',
            'contratos.update',
            'contratos.sign',
            
            // Comisiones (solo lectura propia)
            'comisiones.read',
            
            // Proyectos (solo lectura)
            'proyectos.read',
            'lotes.read',
            'lotes.update', // Para apartar/vender
            'grupos.read',
            'manzanas.read',
            'amenidades.read',
            
            // Cobranza básica
            'cobranza.read',
            'cobranza.create',
            
            // Reportes propios
            'reportes.ventas',
            'reportes.comisiones',
            
            // Perfil
            'profile.*',
        ],
        
        // ============ SUB-VENDEDOR - VENTAS LIMITADAS ============
        'subvendedor' => [
            // Ventas limitadas
            'ventas.create',
            'ventas.read',
            
            // Clientes básico
            'clientes.create',
            'clientes.read',
            'clientes.update',
            
            // Expedientes básico
            'expedientes.create',
            'expedientes.read',
            'expedientes.update',
            
            // Solo lectura de proyectos
            'proyectos.read',
            'lotes.read',
            'grupos.read',
            'amenidades.read',
            
            // Comisiones solo lectura
            'comisiones.read',
            
            // Perfil
            'profile.*',
        ],
        
        // ============ VISOR - SOLO LECTURA ============
        'visor' => [
            // Solo lectura en módulos principales
            'ventas.read',
            'expedientes.read',
            'ingresos.read',
            'flujos.read',
            'clientes.read',
            'comisiones.read',
            'proyectos.read',
            'lotes.read',
            'grupos.read',
            'manzanas.read',
            'amenidades.read',
            'categorias.read',
            
            // Estadísticas
            'estadisticas.read',
            
            // Reportes
            'reportes.ventas',
            'reportes.ingresos',
            'reportes.flujos',
            'reportes.cobranza',
            'reportes.comisiones',
            'reportes.apartados',
            
            // Contratos solo lectura
            'contratos.read',
            
            // Perfil
            'profile.view',
        ],
        
        // ============ CLIENTE - SOLO PERFIL ============
        'cliente' => [
            'profile.view',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix - SIMPLE Y DIRECTO
     * --------------------------------------------------------------------
     */


     
    /**
     * --------------------------------------------------------------------
     * MÉTODO HELPER: Obtener permisos por módulo
     * --------------------------------------------------------------------
     */
    public function getPermissionsByModule(string $module): array
    {
        $modulePermissions = [];
        
        foreach ($this->permissions as $permission => $description) {
            if (strpos($permission, $module . '.') === 0) {
                $modulePermissions[$permission] = $description;
            }
        }
        
        return $modulePermissions;
    }
    
    /**
     * --------------------------------------------------------------------
     * MÉTODO HELPER: Obtener todos los módulos disponibles
     * --------------------------------------------------------------------
     */
    public function getAvailableModules(): array
    {
        $modules = [];
        
        foreach ($this->permissions as $permission => $description) {
            $parts = explode('.', $permission);
            if (count($parts) > 1) {
                $module = $parts[0];
                if (!in_array($module, $modules)) {
                    $modules[] = $module;
                }
            }
        }
        
        sort($modules);
        return $modules;
    }
    
    /**
     * --------------------------------------------------------------------
     * MÉTODO HELPER: Mapear checkboxes del formulario a permisos
     * --------------------------------------------------------------------
     */
    public function getFormPermissionsMapping(): array
    {
        return [
            'ch_ventas'      => ['ventas.create', 'ventas.read', 'ventas.update', 'ventas.delete'],
            'ch_expedientes' => ['expedientes.create', 'expedientes.read', 'expedientes.update', 'expedientes.delete'],
            'ch_ingresos'    => ['ingresos.create', 'ingresos.read', 'ingresos.update', 'ingresos.delete'],
            'ch_egresos'     => ['egresos.create', 'egresos.read', 'egresos.update', 'egresos.delete'],
            'ch_cobranza'    => ['cobranza.create', 'cobranza.read', 'cobranza.update', 'cobranza.delete'],
            'ch_clientes'    => ['clientes.create', 'clientes.read', 'clientes.update', 'clientes.delete'],
            'ch_comisiones'  => ['comisiones.create', 'comisiones.read', 'comisiones.update', 'comisiones.delete'],
            'ch_empresas'    => ['empresas.create', 'empresas.read', 'empresas.update', 'empresas.delete'],
            'ch_proyectos'   => ['proyectos.create', 'proyectos.read', 'proyectos.update', 'proyectos.delete'],
            'ch_lotes'       => ['lotes.create', 'lotes.read', 'lotes.update', 'lotes.delete'],
            'ch_grupos'      => ['grupos.create', 'grupos.read', 'grupos.update', 'grupos.delete'],
            'ch_manzanas'    => ['manzanas.create', 'manzanas.read', 'manzanas.update', 'manzanas.delete'],
            'ch_tipos'       => ['tipos.create', 'tipos.read', 'tipos.update', 'tipos.delete'],
            'ch_divisiones'  => ['divisiones.create', 'divisiones.read', 'divisiones.update', 'divisiones.delete'],
            'ch_amenidades'  => ['amenidades.create', 'amenidades.read', 'amenidades.update', 'amenidades.delete'],
            'ch_categorias'  => ['categorias.create', 'categorias.read', 'categorias.update', 'categorias.delete'],
            'ch_cuentas'     => ['cuentas.create', 'cuentas.read', 'cuentas.update', 'cuentas.delete'],
            'ch_usuarios'    => ['usuarios.create', 'usuarios.read', 'usuarios.update', 'usuarios.delete'],
            'ch_estadisticas'=> ['estadisticas.read', 'estadisticas.export'],
            'ch_rventas'     => ['reportes.ventas'],
            'ch_rflujos'     => ['reportes.flujos'],
            'ch_rcobranza'   => ['reportes.cobranza'],
            'ch_rcomisiones' => ['reportes.comisiones'],
            'ch_rapartados'  => ['reportes.apartados'],
            'ch_contratos'   => ['contratos.create', 'contratos.read', 'contratos.update', 'contratos.delete', 'contratos.sign'],
        ];
    }
    
}