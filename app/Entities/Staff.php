<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Staff extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at', 'ultimo_cambio_password', 'fecha_nacimiento'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'creado_por' => '?integer',
        'activo' => 'boolean',
        'debe_cambiar_password' => 'boolean',
    ];
    
    // 🎯 Cache para lazy loading
    private $usuario = null;
    private $creadoPor = null;
    
    // 🎯 Datos de Shield para usuarios sin información de staff
    public $_shield_data = null;
    
    /**
     * ============================================================================
     * ACCESSORS - PROPIEDADES VIRTUALES PARA VISTAS
     * ============================================================================
     */
    
    /**
     * Nombre formateado para mostrar (Title Case)
     */
    public function getNombreFormateado(): string
    {
        $nombres = $this->attributes['nombres'] ?? '';
        $apellidoPaterno = $this->attributes['apellido_paterno'] ?? '';
        $apellidoMaterno = $this->attributes['apellido_materno'] ?? '';
        
        // Si no tiene nombre en staff, mostrar indicación
        if (empty($nombres) || $nombres === 'SIN INFORMACIÓN') {
            return '<span class="text-warning">Sin información adicional</span>';
        }
        
        $nombreCompleto = trim($nombres . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno);
        return ucwords(strtolower($nombreCompleto));
    }
    
    /**
     * Teléfono formateado estilo mexicano: (55) 1234-5678
     */
    public function getTelefonoFormateado(): string
    {
        $telefono = $this->attributes['telefono'] ?? '';
        
        if (empty($telefono)) {
            return '<span class="text-muted">Sin teléfono</span>';
        }
        
        // Formatear teléfono mexicano: (55) 1234-5678
        if (strlen($telefono) === 10) {
            return '(' . substr($telefono, 0, 2) . ') ' . substr($telefono, 2, 4) . '-' . substr($telefono, 6);
        }
        
        return esc($telefono);
    }
    
    /**
     * Agencia formateada para mostrar
     */
    public function getAgenciaFormateada(): string
    {
        $agencia = $this->attributes['agencia'] ?? '';
        
        if (empty($agencia)) {
            return '<span class="text-muted">Sin agencia</span>';
        }
        
        return ucwords(strtolower($agencia));
    }
    
    /**
     * Calcular edad a partir de fecha de nacimiento
     */
    public function getEdad(): ?int
    {
        $fechaNacimiento = $this->fecha_nacimiento;
        
        if (!$fechaNacimiento) {
            return null;
        }
        
        $hoy = new \DateTime();
        return $hoy->diff($fechaNacimiento)->y;
    }
    
    /**
     * Tipo formateado para mostrar
     */
    public function getTipoFormateado(): string
    {
        $tipos = [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'supervendedor' => 'Super Vendedor',
            'vendedor' => 'Vendedor',
            'subvendedor' => 'Sub-Vendedor',
            'visor' => 'Visor',
        ];
        
        $tipo = $this->attributes['tipo'] ?? '';
        return $tipos[$tipo] ?? ucfirst($tipo);
    }
    
    /**
     * Badge del tipo con color HTML
     */
    public function getTipoBadge(): string
    {
        $badges = [
            'superadmin' => 'badge-danger',
            'admin' => 'badge-warning',
            'supervendedor' => 'badge-success',
            'vendedor' => 'badge-primary',
            'subvendedor' => 'badge-info',
            'visor' => 'badge-secondary',
        ];
        
        $tipo = $this->attributes['tipo'] ?? '';
        $class = $badges[$tipo] ?? 'badge-dark';
        $text = $this->getTipoFormateado();
        
        return "<span class=\"badge {$class}\">{$text}</span>";
    }
    
    /**
     * Fecha de creación formateada
     */
    public function getFechaCreacionFormateada(): string
    {
        $fecha = $this->attributes['created_at'] ?? null;
        
        if (!$fecha) {
            return 'Sin fecha';
        }
        
        // 🔧 FIX: Manejar tanto strings como objetos CodeIgniter\I18n\Time
        if ($fecha instanceof \CodeIgniter\I18n\Time) {
            return $fecha->format('d/m/Y H:i');
        }
        
        // Si es string, usar strtotime
        if (is_string($fecha)) {
            $timestamp = strtotime($fecha);
            return date('d/m/Y H:i', $timestamp);
        }
        
        return 'Fecha inválida';
    }
    
    /**
     * Tiempo relativo desde creación (ej: "hace 3 días")
     */
    public function getTiempoDesdeCreacion(): string
    {
        $fecha = $this->attributes['created_at'] ?? null;
        
        if (!$fecha) {
            return '';
        }
        
        // 🔧 FIX: Manejar tanto strings como objetos CodeIgniter\I18n\Time
        if ($fecha instanceof \CodeIgniter\I18n\Time) {
            return $fecha->humanize();
        }
        
        // Si es string, convertir a timestamp y calcular diferencia
        if (is_string($fecha)) {
            $timestamp = strtotime($fecha);
            $diff = time() - $timestamp;
            
            if ($diff < 60) {
                return 'Hace ' . $diff . ' segundos';
            } elseif ($diff < 3600) {
                return 'Hace ' . floor($diff / 60) . ' minutos';
            } elseif ($diff < 86400) {
                return 'Hace ' . floor($diff / 3600) . ' horas';
            } else {
                return 'Hace ' . floor($diff / 86400) . ' días';
            }
        }
        
        return '';
    }
    
    /**
     * Información de contacto completa HTML
     */
    public function getContactoCompleto(): string
    {
        $contacto = [];
        
        // Email del usuario (lazy loading)
        $usuario = $this->getUsuario();
        if ($usuario && !empty($usuario->email)) {
            $contacto[] = '<i class="fas fa-envelope text-primary"></i> ' . esc($usuario->email);
        }
        
        // Teléfono
        if (!empty($this->attributes['telefono'])) {
            $contacto[] = '<i class="fas fa-phone text-success"></i> ' . $this->getTelefonoFormateado();
        }
        
        return !empty($contacto) ? implode('<br>', $contacto) : '<span class="text-muted">Sin información de contacto</span>';
    }
    
    /**
     * Resumen del staff para widgets/cards
     */
    public function getResumenStaff(): string
    {
        $estado = $this->getEstadoUsuario() ? '✅' : '❌';
        $nombre = $this->getNombreFormateado();
        $tipo = $this->getTipoFormateado();
        $agencia = $this->attributes['agencia'] ?? '';
        
        $resumen = "{$estado} {$nombre} ({$tipo})";
        
        if (!empty($agencia)) {
            $resumen .= " - {$agencia}";
        }
        
        return $resumen;
    }
    
    /**
     * ============================================================================
     * LAZY LOADING - RELACIONES CON OTRAS ENTIDADES
     * ============================================================================
     */
    
    /**
     * Obtener información del usuario asociado (lazy loading)
     */
    public function getUsuario(): ?\App\Entities\User
    {
        if ($this->usuario === null && !empty($this->attributes['user_id'])) {
            $userModel = new \App\Models\UserModel();
            $this->usuario = $userModel->find($this->attributes['user_id']);
        }
        
        return $this->usuario;
    }
    
    /**
     * Obtener email del usuario (delegado o desde Shield data)
     */
    public function getEmail(): ?string
    {
        // Si tiene datos de Shield cached
        if (!empty($this->_shield_data['email'])) {
            return $this->_shield_data['email'];
        }
        
        // Sino, lazy loading desde User Entity
        $usuario = $this->getUsuario();
        return $usuario ? $usuario->email : null;
    }
    
    /**
     * Obtener estado activo del usuario desde Shield (delegado o cached)
     */
    public function getEstadoUsuario(): bool
    {
        // Si tiene datos de Shield cached
        if (isset($this->_shield_data['active'])) {
            return (bool)$this->_shield_data['active'];
        }
        
        // Sino, lazy loading desde User Entity
        $usuario = $this->getUsuario();
        return $usuario ? (bool)$usuario->active : false;
    }
    
    /**
     * Badge del estado del usuario
     */
    public function getEstadoBadge(): string
    {
        if ($this->getEstadoUsuario()) {
            return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Activo</span>';
        }
        
        return '<span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Inactivo</span>';
    }
    
    /**
     * Obtener información de quien lo creó (lazy loading)
     */
    public function getCreadoPor(): ?array
    {
        if ($this->creadoPor === null && !empty($this->attributes['creado_por'])) {
            $db = \Config\Database::connect();
            $this->creadoPor = $db->table('staff s')
                ->select('s.nombres, u.active')
                ->join('users u', 'u.id = s.user_id', 'left')
                ->where('s.user_id', $this->attributes['creado_por'])
                ->get()
                ->getRowArray();
        }
        
        return $this->creadoPor;
    }
    
    /**
     * Nombre de quien lo creó
     */
    public function getCreadoPorNombre(): string
    {
        $creador = $this->getCreadoPor();
        return $creador ? $creador['nombres'] : 'Sistema';
    }
    
    /**
     * ============================================================================
     * MÉTODOS DE VALIDACIÓN Y PERMISOS
     * ============================================================================
     */
    
    /**
     * ¿Es superadmin?
     */
    public function isSuperAdmin(): bool
    {
        return ($this->attributes['tipo'] ?? '') === 'superadmin';
    }
    
    /**
     * ¿Es admin? (superadmin o admin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->attributes['tipo'] ?? '', ['superadmin', 'admin']);
    }
    
    /**
     * ¿Es vendedor? (cualquier tipo de vendedor)
     */
    public function isVendedor(): bool
    {
        return in_array($this->attributes['tipo'] ?? '', ['supervendedor', 'vendedor', 'subvendedor']);
    }
    
    /**
     * ¿Es un usuario existente sin información de staff?
     */
    public function esSinInformacionStaff(): bool
    {
        return !empty($this->_shield_data) || 
               ($this->attributes['nombres'] ?? '') === 'SIN INFORMACIÓN';
    }
    
    /**
     * ¿Necesita completar información de staff?
     */
    public function necesitaCompletarInfo(): bool
    {
        return $this->esSinInformacionStaff();
    }
    
 
    public function puedeSerEditado(): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }
        
        $usuarioActual = auth()->user();
        
        // Superadmin puede editar a todos
        if ($usuarioActual->inGroup('superadmin')) {
            return true;
        }
        
        // Admin puede editar todos excepto superadmin
        if ($usuarioActual->inGroup('admin') && !$this->isSuperAdmin()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * ¿Puede ser eliminado?
     */
    public function puedeSerEliminado(): bool
    {
        // No eliminar superadmin
        if ($this->isSuperAdmin()) {
            return false;
        }
        
        // Verificar si tiene dependencias (otros usuarios creados por él)
        if (!empty($this->attributes['id'])) {
            $db = \Config\Database::connect();
            $dependencias = $db->table('staff')
                ->where('creado_por', $this->attributes['user_id'])
                ->countAllResults();
            
            return $dependencias === 0;
        }
        
        return true;
    }
    
    /**
     * ============================================================================
     * MÉTODOS PARA VISTAS - BOTONES Y ACCIONES
     * ============================================================================
     */
    
    /**
     * Botones de acción para DataTables/Listados
     */
    public function getBotonesAccion(): string
    {
        $userId = $this->attributes['user_id'] ?? 0;
        $activo = $this->getEstadoUsuario();
        $sinInfo = $this->esSinInformacionStaff();
        
        $botones = '';
        
        // Botón Editar/Completar información
        if ($sinInfo) {
            $botones .= '<a href="' . site_url("admin/usuarios/edit/{$userId}") . '" class="btn btn-sm btn-success mr-1" title="Completar información">
                            <i class="fas fa-plus"></i> Completar
                         </a>';
        } else {
            $botones .= '<a href="' . site_url("admin/usuarios/edit/{$userId}") . '" class="btn btn-sm btn-warning mr-1" title="Editar">
                            <i class="fas fa-edit"></i>
                         </a>';
        }
        
        // Botón Activar/Desactivar
        if ($activo) {
            $botones .= '<button type="button" class="btn btn-sm btn-secondary mr-1" onclick="cambiarEstadoUsuario(' . $userId . ', 0)" title="Desactivar">
                            <i class="fas fa-toggle-off"></i>
                         </button>';
        } else {
            $botones .= '<button type="button" class="btn btn-sm btn-success mr-1" onclick="cambiarEstadoUsuario(' . $userId . ', 1)" title="Activar">
                            <i class="fas fa-toggle-on"></i>
                         </button>';
        }
        
        return $botones;
    }
    
    /**
     * ============================================================================
     * MODIFIERS - MUTADORES PARA LIMPIEZA AUTOMÁTICA
     * ============================================================================
     */
    
    /**
     * Limpiar y formatear nombres automáticamente
     */
    public function setNombres(?string $nombres): self
    {
        $this->attributes['nombres'] = !empty($nombres) ? strtoupper(trim($nombres)) : null;
        return $this;
    }
    
    /**
     * Limpiar teléfono automáticamente (solo números)
     */
    public function setTelefono(?string $telefono): self
    {
        if (empty($telefono)) {
            $this->attributes['telefono'] = null;
            return $this;
        }
        
        // Extraer solo números
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
        
        // Si queda vacío o tiene menos de 10 dígitos, null
        if (empty($telefonoLimpio) || strlen($telefonoLimpio) < 10) {
            $this->attributes['telefono'] = null;
        } else {
            $this->attributes['telefono'] = $telefonoLimpio;
        }
        
        return $this;
    }
    
    /**
     * Limpiar apellido paterno automáticamente
     */
    public function setApellidoPaterno(?string $apellidoPaterno): self
    {
        $this->attributes['apellido_paterno'] = !empty($apellidoPaterno) ? strtoupper(trim($apellidoPaterno)) : null;
        return $this;
    }
    
    /**
     * Limpiar apellido materno automáticamente
     */
    public function setApellidoMaterno(?string $apellidoMaterno): self
    {
        $this->attributes['apellido_materno'] = !empty($apellidoMaterno) ? strtoupper(trim($apellidoMaterno)) : null;
        return $this;
    }
    
    /**
     * Validar y formatear fecha de nacimiento
     */
    public function setFechaNacimiento($fechaNacimiento): self
    {
        if (empty($fechaNacimiento)) {
            $this->attributes['fecha_nacimiento'] = null;
            return $this;
        }
        
        // Si es un objeto Time de CodeIgniter, convertir a string
        if ($fechaNacimiento instanceof \CodeIgniter\I18n\Time) {
            $this->attributes['fecha_nacimiento'] = $fechaNacimiento->format('Y-m-d');
            return $this;
        }
        
        // Si es DateTime, convertir a string
        if ($fechaNacimiento instanceof \DateTime) {
            $this->attributes['fecha_nacimiento'] = $fechaNacimiento->format('Y-m-d');
            return $this;
        }
        
        // Si es string, validar formato
        if (is_string($fechaNacimiento)) {
            try {
                $fecha = \DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
                if ($fecha && $fecha->format('Y-m-d') === $fechaNacimiento) {
                    $this->attributes['fecha_nacimiento'] = $fechaNacimiento;
                } else {
                    $this->attributes['fecha_nacimiento'] = null;
                }
            } catch (\Exception $e) {
                $this->attributes['fecha_nacimiento'] = null;
            }
        } else {
            $this->attributes['fecha_nacimiento'] = null;
        }
        
        return $this;
    }
    
    /**
     * Limpiar agencia automáticamente
     */
    public function setAgencia(?string $agencia): self
    {
        $this->attributes['agencia'] = !empty($agencia) ? strtoupper(trim($agencia)) : null;
        return $this;
    }
    
    /**
     * ============================================================================
     * MÉTODOS PARA JSON/AJAX - DATOS ESTRUCTURADOS
     * ============================================================================
     */
    
    /**
     * Convertir a array para DataTables AJAX
     */
    public function toDataTableArray(): array
    {
        return [
            'id' => $this->attributes['id'] ?? 0,
            'user_id' => $this->attributes['user_id'] ?? 0,
            'nombres' => $this->getNombreFormateado(),
            'email' => $this->getEmail(),
            'telefono' => $this->getTelefonoFormateado(),
            'agencia' => $this->getAgenciaFormateada(),
            'tipo' => $this->getTipoBadge(),
            'estado' => $this->getEstadoBadge(),
            'fecha_creacion' => $this->getFechaCreacionFormateada(),
            'creado_por' => $this->getCreadoPorNombre(),
            'acciones' => $this->getBotonesAccion(),
        ];
    }
    
    /**
     * Resumen para widgets/dashboard
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->attributes['id'] ?? 0,
            'user_id' => $this->attributes['user_id'] ?? 0,
            'nombres' => $this->getNombreFormateado(),
            'email' => $this->getEmail(),
            'tipo' => $this->getTipoFormateado(),
            'agencia' => $this->attributes['agencia'] ?? '',
            'activo' => $this->getEstadoUsuario(),
            'es_admin' => $this->isAdmin(),
            'es_vendedor' => $this->isVendedor(),
            'puede_editar' => $this->puedeSerEditado(),
            'puede_eliminar' => $this->puedeSerEliminado(),
        ];
    }
}