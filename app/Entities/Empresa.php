<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Entidad Empresa
 * 
 * Propósito: Encapsular lógica de negocio y formateo de datos de empresas
 * Beneficios: Reutilización, robustez, métodos específicos del dominio
 * 
 * @author Sistema Inmobiliario ANVAR
 * @version 1.0 MVP
 */
class Empresa extends Entity
{
    // =====================================================
    // CONFIGURACIÓN DE CAMPOS Y CASTING
    // =====================================================

    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'proyectos' => 'integer',
        'activo' => 'boolean'
    ];

    protected $attributes = [
        'proyectos' => 0,
        'activo' => true
    ];

    // =====================================================
    // MUTATORS - FORMATEO AUTOMÁTICO AL ASIGNAR
    // =====================================================

    /**
     * Mutator para nombre - Primera letra mayúscula
     */
    public function setNombre(string $value): void
    {
        $this->attributes['nombre'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Mutator para RFC - Mayúsculas y sin espacios
     */
    public function setRfc(string $value): void
    {
        $this->attributes['rfc'] = strtoupper(trim($value));
    }

    /**
     * Mutator para razón social - Primera letra mayúscula
     */
    public function setRazonSocial(?string $value): void
    {
        $this->attributes['razon_social'] = $value ? ucwords(strtolower(trim($value))) : null;
    }

    /**
     * Mutator para email - Minúsculas
     */
    public function setEmail(?string $value): void
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }

    /**
     * Mutator para teléfono - Solo números
     */
    public function setTelefono(?string $value): void
    {
        $this->attributes['telefono'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    /**
     * Mutator para representante - Primera letra mayúscula
     */
    public function setRepresentante(?string $value): void
    {
        $this->attributes['representante'] = $value ? ucwords(strtolower(trim($value))) : null;
    }

    // =====================================================
    // ACCESSORS - FORMATEO AL OBTENER DATOS
    // =====================================================

    /**
     * Accessor para nombre completo con razón social
     */
    public function getNombreCompleto(): string
    {
        if (!empty($this->razon_social) && $this->razon_social !== $this->nombre) {
            return $this->nombre . ' (' . $this->razon_social . ')';
        }
        return $this->nombre;
    }

    /**
     * Accessor para teléfono formateado
     */
    public function getTelefonoFormateado(): ?string
    {
        if (!$this->telefono) {
            return null;
        }

        $telefono = $this->telefono;
        
        // Formatear números mexicanos (10 dígitos)
        if (strlen($telefono) === 10) {
            return substr($telefono, 0, 3) . ' ' . substr($telefono, 3, 3) . ' ' . substr($telefono, 6, 4);
        }

        return $telefono;
    }

    /**
     * Accessor para estado (activo/inactivo)
     */
    public function getEstadoTexto(): string
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }

    /**
     * Accessor para clase CSS del estado
     */
    public function getEstadoClase(): string
    {
        return $this->activo ? 'badge-success' : 'badge-danger';
    }

    // =====================================================
    // MÉTODOS DE NEGOCIO ESPECÍFICOS
    // =====================================================

    /**
     * Verificar si puede ser eliminada (soft delete)
     * 
     * @return bool True si puede ser eliminada
     */
    public function puedeEliminarse(): bool
    {
        // Verificar si tiene proyectos activos asociados
        $db = \Config\Database::connect();
        $totalProyectos = $db->table('proyectos')
                            ->where('empresas_id', $this->id)
                            ->where('estatus', 'activo')
                            ->countAllResults();
        
        return $totalProyectos === 0;
    }

    /**
     * Verificar si la empresa está completamente configurada
     * 
     * @return bool True si está configurada correctamente
     */
    public function estaConfigurada(): bool
    {
        return !empty($this->nombre) && !empty($this->rfc);
    }
}