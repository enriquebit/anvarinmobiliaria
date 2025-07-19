<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    /**
     * Cache para relaciones
     */
    private $cliente = null;
    private $clienteCargado = false;
    
    /**
     * ============================================================================
     * RELACIÓN CON CLIENTE
     * ============================================================================
     */
    
    /**
     * Obtener cliente asociado
     */
    public function getCliente(): ?\App\Entities\Cliente
    {
        if (!$this->id) return null;
        
        if (!$this->clienteCargado) {
            $clienteModel = new \App\Models\ClienteModel();
            $this->cliente = $clienteModel->getByUserId($this->id);
            $this->clienteCargado = true;
        }
        
        return $this->cliente;
    }
    
    /**
     * ¿Tiene información de cliente?
     */
    public function hasCliente(): bool
    {
        return $this->getCliente() !== null;
    }

    /**
     * ============================================================================
     * 🎯 VIRTUAL PROPERTIES - PARA USAR EN VISTAS
     * ============================================================================
     */
    
    /**
     * VIRTUAL PROPERTY: Nombre completo del usuario
     */
    public function getNombreCompleto(): string
    {
        $cliente = $this->getCliente();
        
        if (!$cliente) {
            return $this->email; // Fallback al email
        }
        
        return $cliente->getNombreCompleto();
    }
    
    /**
     * VIRTUAL PROPERTY: Nombre formateado para mostrar
     */
    public function getNombreFormateado(): string
    {
        $cliente = $this->getCliente();
        
        if (!$cliente) {
            return $this->email;
        }
        
        return $cliente->getNombreCompletoFormatted();
    }
    
    /**
     * VIRTUAL PROPERTY: Iniciales del usuario
     */
    public function getIniciales(): string
    {
        $cliente = $this->getCliente();
        
        if (!$cliente) {
            return strtoupper(substr($this->email, 0, 2));
        }
        
        return $cliente->getIniciales();
    }
    
    /**
     * VIRTUAL PROPERTY: Información de contacto completa
     */
    public function getContactoCompleto(): string
    {
        $cliente = $this->getCliente();
        
        if (!$cliente) {
            return $this->email;
        }
        
        $contacto = [];
        
        if ($cliente->email) {
            $contacto[] = '📧 ' . $cliente->email;
        }
        
        if ($cliente->telefono) {
            $contacto[] = '📱 ' . $cliente->getTelefonoFormatted();
        }
        
        return implode(' | ', $contacto);
    }
    
    /**
     * VIRTUAL PROPERTY: Resumen del usuario para admin
     */
    public function getResumenUsuario(): string
    {
        $nombre = $this->getNombreCompleto();
        $estado = $this->active ? '✅' : '❌';
        $cliente = $this->getCliente();
        $etapa = $cliente ? $cliente->getEtapaFormatted() : 'Sin info';
        
        return "{$estado} {$nombre} ({$etapa})";
    }
    
    /**
     * VIRTUAL PROPERTY: Rol principal del usuario
     */
    public function getRolPrincipal(): string
    {
        $groups = $this->getGroups();
        
        if (in_array('superadmin', $groups)) return 'Super Administrador';
        if (in_array('admin', $groups)) return 'Administrador';
        if (in_array('cliente', $groups)) return 'Cliente';
        
        return 'Sin rol';
    }
    
    /**
     * VIRTUAL PROPERTY: Estado del usuario con iconos
     */
    public function getEstadoIcono(): string
    {
        if (!$this->active) return '❌ Inactivo';
        
        $cliente = $this->getCliente();
        
        if (!$cliente) return '⚠️ Sin información';
        
        if ($cliente->isActivo()) return '✅ Activo';
        
        return '🟡 Pendiente activación';
    }

    /**
     * ============================================================================
     * INFORMACIÓN PERSONAL (DELEGADO A CLIENTE)
     * ============================================================================
     */
    
    /**
     * Teléfono del cliente
     */
    public function getTelefono(): ?string
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->telefono : null;
    }
    
    /**
     * Teléfono formateado
     */
    public function getTelefonoFormateado(): ?string
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->getTelefonoFormatted() : null;
    }
    
    /**
     * RFC del cliente
     */
    public function getRfc(): ?string
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->rfc : null;
    }
    
    /**
     * RFC formateado
     */
    public function getRfcFormateado(): ?string
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->getRfcFormatted() : null;
    }

    /**
     * ============================================================================
     * VALIDACIONES Y PERMISOS
     * ============================================================================
     */
    
    /**
     * ¿Es cliente activo? (usuario + cliente activos)
     */
    public function isClienteActivo(): bool
    {
        if (!$this->active) return false;
        
        $cliente = $this->getCliente();
        return $cliente && $cliente->isActivo();
    }
    
    /**
     * ¿Puede comprar? (validaciones de negocio)
     */
    public function puedeComprar(): bool
    {
        if (!$this->active) return false;
        
        $cliente = $this->getCliente();
        
        if (!$cliente) return false;
        
        return $cliente->isActivo() && $cliente->hasInfoCompleta();
    }
    
    /**
     * ¿Necesita completar información?
     */
    public function necesitaCompletarInfo(): bool
    {
        $cliente = $this->getCliente();
        
        if (!$cliente) return true;
        
        return !$cliente->hasInfoBasica();
    }
    
    /**
     * Porcentaje de completitud del perfil
     */
    public function getPorcentajeCompletitud(): int
    {
        $cliente = $this->getCliente();
        
        if (!$cliente) return 0;
        
        return $cliente->getPorcentajeCompletitud();
    }

    /**
     * ============================================================================
     * PROCESO DE VENTA
     * ============================================================================
     */
    
    /**
     * Etapa actual del proceso
     */
    public function getEtapaProceso(): ?string
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->etapa_proceso : null;
    }
    
    /**
     * Etapa formateada para mostrar
     */
    public function getEtapaFormatted(): string
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->getEtapaFormatted() : 'Sin información';
    }
    
    /**
     * ¿Puede avanzar a la siguiente etapa?
     */
    public function puedeAvanzarEtapa(): bool
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->puedeAvanzarEtapa() : false;
    }
    
    /**
     * Días desde el primer contacto
     */
    public function getDiasDesdeContacto(): ?int
    {
        $cliente = $this->getCliente();
        return $cliente ? $cliente->getDiasDesdeContacto() : null;
    }

    /**
     * ============================================================================
     * 🎯 VIRTUAL PROPERTIES PARA BUSINESS INTELLIGENCE
     * ============================================================================
     */
    
    /**
     * VIRTUAL PROPERTY: Score del cliente
     */
    public function getScoreCliente(): int
    {
        $cliente = $this->getCliente();
        
        if (!$cliente) return 0;
        
        $score = 0;
        
        // Usuario activo (20 puntos)
        if ($this->active) $score += 20;
        
        // Cliente activo (20 puntos)
        if ($cliente->isActivo()) $score += 20;
        
        // Información básica (30 puntos)
        if ($cliente->hasInfoBasica()) $score += 30;
        
        // Información completa (30 puntos)
        if ($cliente->hasInfoCompleta()) $score += 30;
        
        return $score;
    }
    
    /**
     * VIRTUAL PROPERTY: Categoría del usuario
     */
    public function getCategoriaUsuario(): string
    {
        $score = $this->getScoreCliente();
        
        if ($score >= 90) return 'Premium';
        if ($score >= 70) return 'VIP';
        if ($score >= 50) return 'Activo';
        if ($score >= 30) return 'Potencial';
        
        return 'Nuevo';
    }
    
    /**
     * VIRTUAL PROPERTY: Prioridad para seguimiento
     */
    public function getPrioridadSeguimiento(): string
    {
        if (!$this->active) return 'BAJA';
        
        $cliente = $this->getCliente();
        
        if (!$cliente) return 'BAJA';
        
        $etapa = $cliente->etapa_proceso;
        $dias = $cliente->getDiasSinActividad();
        
        if ($etapa === 'contrato' && $dias > 2) return 'URGENTE';
        if ($etapa === 'documentacion' && $dias > 5) return 'ALTA';
        if ($etapa === 'calificado' && $dias > 7) return 'MEDIA';
        
        return 'NORMAL';
    }

    /**
     * ============================================================================
     * MÉTODOS PARA ARRAYS Y JSON (PARA APIS)
     * ============================================================================
     */
    
    /**
     * Convertir a array con información de cliente
     */
    public function toArrayWithCliente(): array
    {
        $userData = $this->toArray();
        $cliente = $this->getCliente();
        
        if ($cliente) {
            $userData['cliente'] = $cliente->toArray();
            $userData['nombre_completo'] = $this->getNombreCompleto();
            $userData['contacto_completo'] = $this->getContactoCompleto();
            $userData['categoria'] = $this->getCategoriaUsuario();
            $userData['score'] = $this->getScoreCliente();
        }
        
        return $userData;
    }
    
    /**
     * Información resumida para vistas
     */
    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'active' => $this->active,
            'nombre_completo' => $this->getNombreCompleto(),
            'rol' => $this->getRolPrincipal(),
            'estado' => $this->getEstadoIcono(),
            'categoria' => $this->getCategoriaUsuario(),
            'prioridad' => $this->getPrioridadSeguimiento(),
            'completitud' => $this->getPorcentajeCompletitud()
        ];
    }
}