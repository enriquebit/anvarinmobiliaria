<?php

/**
 * Helper de Autenticación - OPTIMIZADO PARA VISTAS + NUEVOS ROLES
 * app/Helpers/auth_helper.php
 * 
 * Objetivo: Hacer que las vistas sean limpias usando:
 * <?php if(can('ventas.read')): ?> ... <?php endif; ?>
 * <?php if(isVendedor()): ?> ... <?php endif; ?>
 * 
 * ✅ ACTUALIZADO con roles: superadmin, admin, supervendedor, vendedor, subvendedor, visor, cliente
 */

/**
 * ============================================================================
 * 🎯 VERIFICACIONES DE ROLES - PARA USAR EN VISTAS
 * ============================================================================
 */

if (!function_exists('isAdmin')) {
    /**
     * ¿Es administrador? (admin O superadmin)
     * Uso en vistas: <?php if(isAdmin()): ?>
     */
    function isAdmin(): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        return auth()->user()->inGroup('admin', 'superadmin');
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * ¿Es super administrador?
     * Uso en vistas: <?php if(isSuperAdmin()): ?>
     */
    function isSuperAdmin(): bool
    {
        return auth()->loggedIn() && auth()->user()->inGroup('superadmin');
    }
}

// ============================================================================
// 🆕 NUEVOS ROLES DE VENDEDORES
// ============================================================================

if (!function_exists('isSuperVendedor')) {
    /**
     * ¿Es super vendedor?
     * Uso en vistas: <?php if(isSuperVendedor()): ?>
     */
    function isSuperVendedor(): bool
    {
        return auth()->loggedIn() && auth()->user()->inGroup('supervendedor');
    }
}

if (!function_exists('isVendedor')) {
    /**
     * ¿Es vendedor estándar?
     * Uso en vistas: <?php if(isVendedor()): ?>
     */
    function isVendedor(): bool
    {
        return auth()->loggedIn() && auth()->user()->inGroup('vendedor');
    }
}

if (!function_exists('isSubVendedor')) {
    /**
     * ¿Es sub-vendedor?
     * Uso en vistas: <?php if(isSubVendedor()): ?>
     */
    function isSubVendedor(): bool
    {
        return auth()->loggedIn() && auth()->user()->inGroup('subvendedor');
    }
}

if (!function_exists('isVisor')) {
    /**
     * ¿Es visor (solo lectura)?
     * Uso en vistas: <?php if(isVisor()): ?>
     */
    function isVisor(): bool
    {
        return auth()->loggedIn() && auth()->user()->inGroup('visor');
    }
}

if (!function_exists('isCliente')) {
    /**
     * ¿Es cliente?
     * Uso en vistas: <?php if(isCliente()): ?>
     */
    function isCliente(): bool
    {
        return auth()->loggedIn() && auth()->user()->inGroup('cliente');
    }
}

// ============================================================================
// 🆕 FUNCIONES DE CONVENIENCIA PARA GRUPOS DE ROLES
// ============================================================================

if (!function_exists('isStaff')) {
    /**
     * ¿Es cualquier tipo de staff administrativo?
     * Uso en vistas: <?php if(isStaff()): ?>
     */
    function isStaff(): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        return auth()->user()->inGroup(
            'superadmin', 'admin', 'supervendedor', 'vendedor', 'subvendedor', 'visor'
        );
    }
}

if (!function_exists('isVendedorType')) {
    /**
     * ¿Es cualquier tipo de vendedor?
     * Uso en vistas: <?php if(isVendedorType()): ?>
     */
    function isVendedorType(): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        return auth()->user()->inGroup('supervendedor', 'vendedor', 'subvendedor');
    }
}

if (!function_exists('isManagerLevel')) {
    /**
     * ¿Tiene nivel gerencial? (superadmin, admin, supervendedor)
     * Uso en vistas: <?php if(isManagerLevel()): ?>
     */
    function isManagerLevel(): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        return auth()->user()->inGroup('superadmin', 'admin', 'supervendedor');
    }
}

if (!function_exists('canCreateUsers')) {
    /**
     * ¿Puede crear otros usuarios?
     * Uso en vistas: <?php if(canCreateUsers()): ?>
     */
    function canCreateUsers(): bool
    {
        return can('usuarios.create');
    }
}

if (!function_exists('canSellProperty')) {
    /**
     * ¿Puede vender propiedades?
     * Uso en vistas: <?php if(canSellProperty()): ?>
     */
    function canSellProperty(): bool
    {
        return canAny(['ventas.create', 'ventas.update']);
    }
}

/**
 * ============================================================================
 * 🎯 SISTEMA DE PERMISOS - PARA USAR EN VISTAS
 * ============================================================================
 */

if (!function_exists('can')) {
    /**
     * ¿Tiene este permiso específico?
     * Uso en vistas: <?php if(can('ventas.read')): ?>
     */
    function can(string $permission): bool
    {
        return auth()->loggedIn() && auth()->user()->can($permission);
    }
}

if (!function_exists('cannot')) {
    /**
     * ¿NO tiene este permiso?
     * Uso en vistas: <?php if(cannot('ventas.delete')): ?>
     */
    function cannot(string $permission): bool
    {
        return !can($permission);
    }
}

if (!function_exists('canAny')) {
    /**
     * ¿Tiene ALGUNO de estos permisos?
     * Uso: <?php if(canAny(['ventas.read', 'ventas.update'])): ?>
     */
    function canAny(array $permissions): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        foreach ($permissions as $permission) {
            if (can($permission)) {
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('canAll')) {
    /**
     * ¿Tiene TODOS estos permisos?
     * Uso: <?php if(canAll(['ventas.read', 'ventas.update'])): ?>
     */
    function canAll(array $permissions): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        foreach ($permissions as $permission) {
            if (!can($permission)) {
                return false;
            }
        }
        
        return true;
    }
}

// ============================================================================
// 🆕 FUNCIONES AVANZADAS DE PERMISOS
// ============================================================================

if (!function_exists('canModule')) {
    /**
     * ¿Tiene acceso a este módulo? (cualquier permiso del módulo)
     * Uso: <?php if(canModule('ventas')): ?>
     */
    function canModule(string $module): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        // Verificar permisos comunes del módulo
        $modulePermissions = [
            $module . '.read',
            $module . '.create',
            $module . '.update',
            $module . '.delete'
        ];
        
        return canAny($modulePermissions);
    }
}

if (!function_exists('canModuleFull')) {
    /**
     * ¿Tiene acceso completo al módulo? (CRUD completo)
     * Uso: <?php if(canModuleFull('ventas')): ?>
     */
    function canModuleFull(string $module): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        $modulePermissions = [
            $module . '.create',
            $module . '.read',
            $module . '.update',
            $module . '.delete'
        ];
        
        return canAll($modulePermissions);
    }
}

/**
 * ============================================================================
 * 🎯 INFORMACIÓN DEL USUARIO - USANDO VIRTUAL PROPERTIES
 * ============================================================================
 */

if (!function_exists('userName')) {
    /**
     * Nombre del usuario usando Virtual Properties
     * En vistas: <?= userName() ?>
     */
    function userName(): string
    {
        if (!auth()->loggedIn()) {
            return 'Invitado';
        }
        
        $user = auth()->user();
        
        // Usar virtual property de nuestra Entity
        if ($user instanceof \App\Entities\User) {
            return $user->getNombreCompleto();
        }
        
        return $user->email ?? 'Usuario';
    }
}

if (!function_exists('userNameFormatted')) {
    /**
     * Nombre formateado para mostrar (Título Case)
     * En vistas: <?= userNameFormatted() ?>
     */
    function userNameFormatted(): string
    {
        if (!auth()->loggedIn()) {
            return 'Invitado';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getNombreFormateado();
        }
        
        return $user->email ?? 'Usuario';
    }
}

if (!function_exists('userRole')) {
    /**
     * Rol principal del usuario - ACTUALIZADO CON NUEVOS ROLES
     * En vistas: <?= userRole() ?>
     */
    function userRole(): string
    {
        if (!auth()->loggedIn()) {
            return '';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getRolPrincipal();
        }
        
        // ✅ FALLBACK ACTUALIZADO CON TODOS LOS ROLES
        if (isSuperAdmin()) return 'Super Administrador';
        if (isAdmin()) return 'Administrador';
        if (isSuperVendedor()) return 'Super Vendedor';
        if (isVendedor()) return 'Vendedor';
        if (isSubVendedor()) return 'Sub-Vendedor';
        if (isVisor()) return 'Visor';
        if (isCliente()) return 'Cliente';
        
        return 'Sin rol';
    }
}

// ============================================================================
// 🆕 BADGE DEL ROL CON COLORES ACTUALIZADOS
// ============================================================================

if (!function_exists('userRoleBadge')) {
    /**
     * Badge HTML del rol con color
     * En vistas: <?= userRoleBadge() ?>
     */
    function userRoleBadge(): string
    {
        if (!auth()->loggedIn()) {
            return '<span class="badge badge-secondary">Invitado</span>';
        }
        
        $role = userRole();
        
        $badgeClasses = [
            'Super Administrador' => 'badge-danger',
            'Administrador' => 'badge-warning',
            'Super Vendedor' => 'badge-success',
            'Vendedor' => 'badge-primary',
            'Sub-Vendedor' => 'badge-info',
            'Visor' => 'badge-secondary',
            'Cliente' => 'badge-light'
        ];
        
        $class = $badgeClasses[$role] ?? 'badge-dark';
        
        return "<span class=\"badge {$class}\">{$role}</span>";
    }
}

if (!function_exists('userInitials')) {
    /**
     * Iniciales del usuario
     * En vistas: <?= userInitials() ?>
     */
    function userInitials(): string
    {
        if (!auth()->loggedIn()) {
            return 'IN';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getIniciales();
        }
        
        return strtoupper(substr($user->email ?? 'US', 0, 2));
    }
}

if (!function_exists('userStatus')) {
    /**
     * Estado del usuario con iconos
     * En vistas: <?= userStatus() ?>
     */
    function userStatus(): string
    {
        if (!auth()->loggedIn()) {
            return '❌ No logueado';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getEstadoIcono();
        }
        
        return $user->active ? '✅ Activo' : '❌ Inactivo';
    }
}

if (!function_exists('userContact')) {
    /**
     * Información de contacto completa
     * En vistas: <?= userContact() ?>
     */
    function userContact(): string
    {
        if (!auth()->loggedIn()) {
            return '';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getContactoCompleto();
        }
        
        return $user->email ?? '';
    }
}

/**
 * ============================================================================
 * 🎯 INFORMACIÓN ESPECÍFICA DE CLIENTE
 * ============================================================================
 */

if (!function_exists('userPhone')) {
    /**
     * Teléfono del usuario
     * En vistas: <?= userPhone() ?>
     */
    function userPhone(): ?string
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return null;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getTelefono();
        }
        
        return null;
    }
}

if (!function_exists('userPhoneFormatted')) {
    /**
     * Teléfono formateado para mostrar
     * En vistas: <?= userPhoneFormatted() ?>
     */
    function userPhoneFormatted(): ?string
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return null;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getTelefonoFormateado();
        }
        
        return null;
    }
}

if (!function_exists('userRfc')) {
    /**
     * RFC del usuario
     * En vistas: <?= userRfc() ?>
     */
    function userRfc(): ?string
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return null;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getRfc();
        }
        
        return null;
    }
}

if (!function_exists('userRfcFormatted')) {
    /**
     * RFC formateado para mostrar
     * En vistas: <?= userRfcFormatted() ?>
     */
    function userRfcFormatted(): ?string
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return null;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getRfcFormateado();
        }
        
        return null;
    }
}

/**
 * ============================================================================
 * 🎯 VALIDACIONES Y ESTADOS
 * ============================================================================
 */

if (!function_exists('userCanBuy')) {
    /**
     * ¿Puede el usuario comprar?
     * En vistas: <?php if(userCanBuy()): ?>
     */
    function userCanBuy(): bool
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return false;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->puedeComprar();
        }
        
        return false;
    }
}

if (!function_exists('userNeedsInfo')) {
    /**
     * ¿Necesita completar información?
     * En vistas: <?php if(userNeedsInfo()): ?>
     */
    function userNeedsInfo(): bool
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return false;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->necesitaCompletarInfo();
        }
        
        return true;
    }
}

if (!function_exists('userCompleteness')) {
    /**
     * Porcentaje de completitud del perfil
     * En vistas: <?= userCompleteness() ?>%
     */
    function userCompleteness(): int
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return 0;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getPorcentajeCompletitud();
        }
        
        return 0;
    }
}

if (!function_exists('userSalesStage')) {
    /**
     * Etapa del proceso de venta
     * En vistas: <?= userSalesStage() ?>
     */
    function userSalesStage(): string
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return 'No aplicable';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getEtapaFormatted();
        }
        
        return 'Sin información';
    }
}

if (!function_exists('userCanAdvanceStage')) {
    /**
     * ¿Puede avanzar a la siguiente etapa?
     * En vistas: <?php if(userCanAdvanceStage()): ?>
     */
    function userCanAdvanceStage(): bool
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return false;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->puedeAvanzarEtapa();
        }
        
        return false;
    }
}

/**
 * ============================================================================
 * 🎯 BUSINESS INTELLIGENCE
 * ============================================================================
 */

if (!function_exists('userScore')) {
    /**
     * Score del cliente (0-100)
     * En vistas: <?= userScore() ?>
     */
    function userScore(): int
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return 0;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getScoreCliente();
        }
        
        return 0;
    }
}

if (!function_exists('userCategory')) {
    /**
     * Categoría del usuario (Premium, VIP, etc.)
     * En vistas: <?= userCategory() ?>
     */
    function userCategory(): string
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return 'No aplicable';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getCategoriaUsuario();
        }
        
        return 'Nuevo';
    }
}

if (!function_exists('userPriority')) {
    /**
     * Prioridad de seguimiento
     * En vistas: <?= userPriority() ?>
     */
    function userPriority(): string
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return 'NORMAL';
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getPrioridadSeguimiento();
        }
        
        return 'NORMAL';
    }
}

if (!function_exists('userDaysFromContact')) {
    /**
     * Días desde primer contacto
     * En vistas: <?= userDaysFromContact() ?>
     */
    function userDaysFromContact(): ?int
    {
        if (!auth()->loggedIn() || !isCliente()) {
            return null;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->getDiasDesdeContacto();
        }
        
        return null;
    }
}

// ============================================================================
// 🆕 FUNCIONES ESPECÍFICAS PARA STAFF
// ============================================================================

if (!function_exists('staffId')) {
    /**
     * ID del staff (para relaciones en activado_por, etc.)
     * En controladores: $staffId = staffId();
     */
    function staffId(): ?int
    {
        if (!auth()->loggedIn() || !isStaff()) {
            return null;
        }
        
        // Obtener ID del staff desde tabla staff
        $db = \Config\Database::connect();
        $staff = $db->table('staff')
                   ->where('user_id', auth()->id())
                   ->get()
                   ->getRow();
        
        return $staff->id ?? null;
    }
}

if (!function_exists('staffInfo')) {
    /**
     * Información completa del staff actual
     * En controladores: $staff = staffInfo();
     */
    function staffInfo(): ?array
    {
        if (!auth()->loggedIn() || !isStaff()) {
            return null;
        }
        
        $db = \Config\Database::connect();
        $staff = $db->table('staff')
                   ->where('user_id', auth()->id())
                   ->get()
                   ->getRowArray();
        
        return $staff;
    }
}

/**
 * ============================================================================
 * 🎯 UTILIDADES PARA VISTAS
 * ============================================================================
 */

if (!function_exists('currentUser')) {
    /**
     * Obtener usuario actual (Entity completa)
     * En controladores: $user = currentUser();
     */
    function currentUser(): ?\App\Entities\User
    {
        if (!auth()->loggedIn()) {
            return null;
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user;
        }
        
        // Convertir si es necesario
        if (is_object($user) && method_exists($user, 'toArray')) {
            $data = $user->toArray();
            return new \App\Entities\User($data);
        }
        
        return null;
    }
}

if (!function_exists('userSummary')) {
    /**
     * Resumen del usuario para widgets
     * En vistas: <?php $summary = userSummary(); ?>
     */
    function userSummary(): array
    {
        if (!auth()->loggedIn()) {
            return [
                'nombre' => 'Invitado',
                'rol' => '',
                'estado' => '❌ No logueado',
                'categoria' => '',
                'completitud' => 0
            ];
        }
        
        $user = auth()->user();
        
        if ($user instanceof \App\Entities\User) {
            return $user->toSummary();
        }
        
        return [
            'nombre' => $user->email ?? 'Usuario',
            'rol' => userRole(),
            'estado' => userStatus(),
            'categoria' => 'Básico',
            'completitud' => 0
        ];
    }
}

// ============================================================================
// 🆕 UTILIDADES PARA MENÚS Y SIDEBAR
// ============================================================================

if (!function_exists('showSidebarSection')) {
    /**
     * ¿Mostrar sección del sidebar?
     * Uso: <?php if(showSidebarSection('ventas')): ?>
     */
    function showSidebarSection(string $section): bool
    {
        $sectionPermissions = [
            'ventas' => ['ventas.read'],
            'clientes' => ['clientes.read'],
            'proyectos' => ['proyectos.read'],
            'reportes' => ['reportes.ventas', 'reportes.ingresos', 'reportes.flujos', 'reportes.cobranza'],
            'usuarios' => ['usuarios.read'],
            'configuracion' => ['system.config'],
            'cobranza' => ['cobranza.read'],
            'comisiones' => ['comisiones.read'],
        ];
        
        if (!isset($sectionPermissions[$section])) {
            return false;
        }
        
        return canAny($sectionPermissions[$section]);
    }
}

/**
 * ============================================================================
 * 🎯 HELPERS PARA BADGES Y ESTILOS
 * ============================================================================
 */

if (!function_exists('userBadgeClass')) {
    /**
     * Clase CSS para badge según categoría
     * En vistas: <span class="badge <?= userBadgeClass() ?>">
     */
    function userBadgeClass(): string
    {
        $category = userCategory();
        
        $classes = [
            'Premium' => 'badge-warning',
            'VIP' => 'badge-success',
            'Activo' => 'badge-primary',
            'Potencial' => 'badge-info',
            'Nuevo' => 'badge-secondary'
        ];
        
        return $classes[$category] ?? 'badge-light';
    }
}

if (!function_exists('userPriorityClass')) {
    /**
     * Clase CSS para prioridad
     * En vistas: <span class="<?= userPriorityClass() ?>">
     */
    function userPriorityClass(): string
    {
        $priority = userPriority();
        
        $classes = [
            'URGENTE' => 'text-danger font-weight-bold',
            'ALTA' => 'text-warning font-weight-bold',
            'MEDIA' => 'text-info',
            'NORMAL' => 'text-muted'
        ];
        
        return $classes[$priority] ?? 'text-muted';
    }
}

if (!function_exists('userStatusClass')) {
    /**
     * Clase CSS para estado
     * En vistas: <span class="<?= userStatusClass() ?>">
     */
    function userStatusClass(): string
    {
        if (!auth()->loggedIn()) {
            return 'text-danger';
        }
        
        $user = auth()->user();
        
        if (!$user->active) {
            return 'text-danger';
        }
        
        if (isCliente()) {
            return userCanBuy() ? 'text-success' : 'text-warning';
        }
        
        return 'text-success';
    }
}