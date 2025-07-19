<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Division extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'           => 'integer',
        'empresas_id'  => 'integer',
        'proyectos_id' => 'integer',
        'orden'        => 'integer',
        'activo'       => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime'
    ];

    // Mutadores
    public function setNombre(?string $nombre): self
    {
        $this->attributes['nombre'] = $nombre ? trim(ucwords(strtolower($nombre))) : null;
        return $this;
    }

    public function setClave(?string $clave): self
    {
        $this->attributes['clave'] = $clave ? strtoupper(trim($clave)) : null;
        return $this;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->attributes['descripcion'] = $descripcion ? trim($descripcion) : null;
        return $this;
    }

    // Accesores
    public function getNombreCompleto(): string
    {
        return $this->nombre . ' (' . $this->clave . ')';
    }

    public function getEstadoTexto(): string
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }

    public function getColorBadge(): string
    {
        return $this->activo ? 'success' : 'danger';
    }

    // Validaciones de negocio
    public function puedeSerEliminada(): bool
    {
        // Verificar si hay lotes asociados
        $loteModel = model('LoteModel');
        $lotesCount = $loteModel->where('divisiones_id', $this->id)->countAllResults();
        
        return $lotesCount === 0;
    }

    public function tieneLotesAsociados(): bool
    {
        $loteModel = model('LoteModel');
        return $loteModel->where('divisiones_id', $this->id)->countAllResults() > 0;
    }

    // Información para formularios
    public function getEmpresaNombre(): ?string
    {
        if (empty($this->empresas_id)) {
            return null;
        }

        $empresaModel = model('EmpresaModel');
        $empresa = $empresaModel->find($this->empresas_id);
        
        return $empresa ? $empresa->nombre : null;
    }

    public function getProyectoNombre(): ?string
    {
        if (empty($this->proyectos_id)) {
            return null;
        }

        $proyectoModel = model('ProyectoModel');
        $proyecto = $proyectoModel->find($this->proyectos_id);
        
        return $proyecto ? $proyecto->nombre : null;
    }

    // Validación de clave única
    public function validarClaveUnica(?int $exceptoId = null): bool
    {
        $divisionModel = model('DivisionModel');
        
        $builder = $divisionModel->builder();
        $builder->where('clave', $this->clave)
                ->where('empresas_id', $this->empresas_id)
                ->where('proyectos_id', $this->proyectos_id)
                ->where('activo', true);
        
        if ($exceptoId) {
            $builder->where('id !=', $exceptoId);
        }
        
        return $builder->countAllResults() === 0;
    }

    // Generar clave automática sugerida
    public function generarClaveSugerida(): string
    {
        if (empty($this->nombre)) {
            return 'D1';
        }

        // Extraer las primeras letras del nombre
        $palabras = explode(' ', trim($this->nombre));
        $clave = '';
        
        foreach ($palabras as $palabra) {
            if (!empty($palabra)) {
                $clave .= strtoupper(substr($palabra, 0, 1));
            }
        }
        
        // Si es solo una palabra, tomar las primeras 2 letras
        if (count($palabras) === 1 && strlen($this->nombre) > 1) {
            $clave = strtoupper(substr($this->nombre, 0, 2));
        }
        
        // Agregar número si es necesario
        $divisionModel = model('DivisionModel');
        $contador = 1;
        $claveOriginal = $clave;
        
        while (!$this->validarClaveUnicaTemporal($clave)) {
            $contador++;
            $clave = $claveOriginal . $contador;
        }
        
        return $clave;
    }

    private function validarClaveUnicaTemporal(string $clave): bool
    {
        $divisionModel = model('DivisionModel');
        
        return $divisionModel->where('clave', $clave)
                           ->where('empresas_id', $this->empresas_id)
                           ->where('proyectos_id', $this->proyectos_id)
                           ->where('activo', true)
                           ->countAllResults() === 0;
    }
}