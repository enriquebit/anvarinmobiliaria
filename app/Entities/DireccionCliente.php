<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class DireccionCliente extends Entity
{
    /**
     * Mapeo de tipos de datos automático
     */
    protected $casts = [
        'cliente_id' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Valores por defecto
     */
    protected $attributes = [
        'activo' => true,
        'tipo' => 'principal',
        'tipo_residencia' => 'propia',
        'numero' => 'S/N'
    ];

    /**
     * ============================================================================
     * MÉTODOS BÁSICOS ESENCIALES
     * ============================================================================
     */

    /**
     * Obtener dirección completa formateada
     */
    public function getDireccionCompleta(): string
    {
        $direccion = '';
        
        if (!empty($this->domicilio)) {
            $direccion .= $this->domicilio;
        }
        
        if (!empty($this->numero)) {
            $direccion .= ' #' . $this->numero;
        }
        
        if (!empty($this->colonia)) {
            $direccion .= ', Col. ' . $this->colonia;
        }
        
        if (!empty($this->ciudad)) {
            $direccion .= ', ' . $this->ciudad;
        }
        
        if (!empty($this->estado)) {
            $direccion .= ', ' . $this->estado;
        }
        
        if (!empty($this->codigo_postal)) {
            $direccion .= ' C.P. ' . $this->codigo_postal;
        }
        
        return trim($direccion, ', ');
    }

    /**
     * Obtener dirección corta (calle y número)
     */
    public function getDireccionCorta(): string
    {
        $direccion = '';
        
        if (!empty($this->domicilio)) {
            $direccion .= $this->domicilio;
        }
        
        if (!empty($this->numero)) {
            $direccion .= ' #' . $this->numero;
        }
        
        return $direccion;
    }

    /**
     * ¿Es la dirección principal?
     */
    public function esPrincipal(): bool
    {
        return $this->tipo === 'principal';
    }

    /**
     * ¿Está activa la dirección?
     */
    public function isActiva(): bool
    {
        return (bool) $this->activo;
    }

    /**
     * ¿Tiene información completa?
     */
    public function esCompleta(): bool
    {
        return !empty($this->domicilio) &&
               !empty($this->colonia) &&
               !empty($this->ciudad) &&
               !empty($this->estado) &&
               !empty($this->codigo_postal);
    }

    /**
     * Obtener etiqueta del tipo de residencia
     */
    public function getTipoResidenciaLabel(): string
    {
        $tipos = [
            'propia' => 'Propia',
            'rentada' => 'Rentada',
            'familiar' => 'Familiar',
            'otro' => 'Otro'
        ];

        return $tipos[$this->tipo_residencia] ?? 'No especificado';
    }

    /**
     * Obtener etiqueta del tipo de dirección
     */
    public function getTipoLabel(): string
    {
        $tipos = [
            'principal' => 'Principal',
            'secundaria' => 'Secundaria',
            'trabajo' => 'Trabajo',
            'familiar' => 'Familiar'
        ];

        return $tipos[$this->tipo] ?? 'No especificado';
    }

    /**
     * ¿Es residencia propia?
     */
    public function esResidenciaPropia(): bool
    {
        return $this->tipo_residencia === 'propia';
    }

    /**
     * ¿Es residencia rentada?
     */
    public function esResidenciaRentada(): bool
    {
        return $this->tipo_residencia === 'rentada';
    }

    /**
     * Obtener tiempo radicando formateado
     */
    public function getTiempoRadicandoFormateado(): string
    {
        if (empty($this->tiempo_radicando)) {
            return 'No especificado';
        }

        return $this->tiempo_radicando;
    }

    /**
     * ============================================================================
     * MÉTODOS DE VALIDACIÓN
     * ============================================================================
     */

    /**
     * Validar código postal mexicano
     */
    public function tieneCodigoPostalValido(): bool
    {
        if (empty($this->codigo_postal)) {
            return false;
        }

        $cp = preg_replace('/\D/', '', $this->codigo_postal);
        return strlen($cp) === 5 && $cp >= '01000' && $cp <= '99999';
    }

    /**
     * ¿Tiene todos los campos obligatorios?
     */
    public function tieneCamposObligatorios(): bool
    {
        return !empty($this->domicilio) &&
               !empty($this->colonia) &&
               !empty($this->ciudad) &&
               !empty($this->estado);
    }

    /**
     * ============================================================================
     * MÉTODOS DE FORMATEO
     * ============================================================================
     */

    /**
     * Formatear para JSON/API con información extendida
     */
    public function toArrayExtended(): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->cliente_id,
            'direccion_completa' => $this->getDireccionCompleta(),
            'direccion_corta' => $this->getDireccionCorta(),
            'domicilio' => $this->domicilio,
            'numero' => $this->numero,
            'colonia' => $this->colonia,
            'codigo_postal' => $this->codigo_postal,
            'ciudad' => $this->ciudad,
            'estado' => $this->estado,
            'tiempo_radicando' => $this->getTiempoRadicandoFormateado(),
            'tipo_residencia' => $this->tipo_residencia,
            'tipo_residencia_label' => $this->getTipoResidenciaLabel(),
            'tipo' => $this->tipo,
            'tipo_label' => $this->getTipoLabel(),
            'es_principal' => $this->esPrincipal(),
            'es_completa' => $this->esCompleta(),
            'activo' => $this->isActiva(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s')
        ];
    }

    /**
     * ============================================================================
     * MUTADORES AUTOMÁTICOS
     * ============================================================================
     */

    /**
     * Formatear domicilio automáticamente
     */
    public function setDomicilio(?string $domicilio): self
    {
        $this->attributes['domicilio'] = !empty($domicilio) ? 
            strtoupper(trim($domicilio)) : null;
        return $this;
    }

    /**
     * Formatear colonia automáticamente
     */
    public function setColonia(?string $colonia): self
    {
        $this->attributes['colonia'] = !empty($colonia) ? 
            strtoupper(trim($colonia)) : null;
        return $this;
    }

    /**
     * Formatear ciudad automáticamente
     */
    public function setCiudad(?string $ciudad): self
    {
        $this->attributes['ciudad'] = !empty($ciudad) ? 
            strtoupper(trim($ciudad)) : null;
        return $this;
    }

    /**
     * Formatear estado automáticamente
     */
    public function setEstado(?string $estado): self
    {
        $this->attributes['estado'] = !empty($estado) ? 
            strtoupper(trim($estado)) : null;
        return $this;
    }

    /**
     * Formatear código postal automáticamente
     */
    public function setCodigoPostal(?string $codigoPostal): self
    {
        $this->attributes['codigo_postal'] = !empty($codigoPostal) ? 
            preg_replace('/\D/', '', $codigoPostal) : null;
        return $this;
    }

    /**
     * Formatear número automáticamente
     */
    public function setNumero(?string $numero): self
    {
        $this->attributes['numero'] = !empty(trim($numero ?? '')) ? 
            trim($numero) : 'S/N';
        return $this;
    }

    /**
     * Validar tipo de residencia
     */
    public function setTipoResidencia(?string $tipoResidencia): self
    {
        $tiposValidos = ['propia', 'rentada', 'familiar', 'otro'];
        $this->attributes['tipo_residencia'] = in_array($tipoResidencia, $tiposValidos) ? 
            $tipoResidencia : 'propia';
        return $this;
    }

    /**
     * Validar tipo de dirección
     */
    public function setTipo(?string $tipo): self
    {
        $tiposValidos = ['principal', 'secundaria', 'trabajo', 'familiar'];
        $this->attributes['tipo'] = in_array($tipo, $tiposValidos) ? 
            $tipo : 'principal';
        return $this;
    }

    /**
     * ============================================================================
     * MÉTODOS ESTÁTICOS ÚTILES
     * ============================================================================
     */

    /**
     * Obtener tipos de residencia disponibles
     */
    public static function getTiposResidencia(): array
    {
        return [
            'propia' => 'Propia',
            'rentada' => 'Rentada',
            'familiar' => 'Familiar',
            'otro' => 'Otro'
        ];
    }

    /**
     * Obtener tipos de dirección disponibles
     */
    public static function getTiposDireccion(): array
    {
        return [
            'principal' => 'Principal',
            'secundaria' => 'Secundaria',
            'trabajo' => 'Trabajo',
            'familiar' => 'Familiar'
        ];
    }

    /**
     * Crear dirección desde array de datos del formulario
     */
    public static function fromFormData(array $data, int $clienteId): self
    {
        $direccion = new self();
        
        $direccion->cliente_id = $clienteId;
        $direccion->domicilio = $data['domicilio'] ?? '';
        $direccion->numero = $data['numero'] ?? 'S/N';
        $direccion->colonia = $data['colonia'] ?? '';
        $direccion->codigo_postal = $data['codigo_postal'] ?? '';
        $direccion->ciudad = $data['ciudad'] ?? '';
        $direccion->estado = $data['estado'] ?? '';
        $direccion->tiempo_radicando = $data['tiempo_radicando'] ?? null;
        $direccion->tipo_residencia = $data['tipo_residencia'] ?? 'propia';
        $direccion->tipo = $data['tipo'] ?? 'principal';
        $direccion->activo = true;
        
        return $direccion;
    }
}