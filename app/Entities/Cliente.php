<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Cliente extends Entity
{
    /**
     * Mapeo de tipos de datos automático
     */
    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'estado_civil_id' => '?int',
        'empresa_id' => '?int',
        'origen_informacion_id' => '?int',
        'asesor_asignado' => '?int',
    ];
    
    /**
     * Valores por defecto
     */
  protected $attributes = [
        'id' => null,
        'user_id' => null,
        'nombres' => null,
        'apellido_paterno' => null,
        'apellido_materno' => null,
        'genero' => null,
        'razon_social' => null,
        'identificacion' => null,
        'numero_identificacion' => null,
        'fecha_nacimiento' => null,
        'lugar_nacimiento' => null,
        'nacionalidad' => null,
        'profesion' => null,
        'rfc' => null,
        'curp' => null,
        'email' => null,
        'estado_civil_id' => null,
        'estado_civil' => null,
        'leyenda_civil' => null,
        'tiempo_radicando' => null,
        'tipo_residencia' => null,
        'residente' => null,
        'contacto' => null,
        'empresa_id' => null,
        'origen_informacion_id' => null,
        'otro_origen' => null,
        'fuente_informacion' => null,
        'telefono' => null,
        'etapa_proceso' => 'interesado',
        'fecha_primer_contacto' => null,
        'fecha_ultima_actividad' => null,
        'asesor_asignado' => null,
        'notas_internas' => null,
        'persona_moral' => 0,
        'created_at' => null,
        'updated_at' => null
    ];
        protected $datamap = [];
    /**
     * Cache para lazy loading
     */
    /**
     * Campos de fecha que deben ser convertidos a DateTime
     */
    protected $dates = [
        'fecha_nacimiento',
        'fecha_primer_contacto', 
        'fecha_ultima_actividad',
        'created_at',
        'updated_at'
    ];

    
    /**
     * ============================================================================
     * MÉTODOS BÁSICOS ESENCIALES
     * ============================================================================
     */
    
    /**
     * Obtener nombre completo del cliente
     */
    public function getNombreCompleto(): string
    {
        return trim(
            ($this->nombres ?? '') . ' ' . 
            ($this->apellido_paterno ?? '') . ' ' . 
            ($this->apellido_materno ?? '')
        );
    }
    
    /**
     * Obtener iniciales del nombre
     */
    public function getIniciales(): string
    {
        $iniciales = '';
        
        if (!empty($this->nombres)) {
            $iniciales .= substr($this->nombres, 0, 1);
        }
        
        if (!empty($this->apellido_paterno)) {
            $iniciales .= substr($this->apellido_paterno, 0, 1);
        }
        
        if (!empty($this->apellido_materno)) {
            $iniciales .= substr($this->apellido_materno, 0, 1);
        }
        
        return strtoupper($iniciales);
    }
    
    /**
     * ¿Está activo el cliente? (basado en users.active)
     */
    public function isActivo(): bool
    {
        // Si tenemos user_id, consultar el estado del usuario
        if ($this->user_id) {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($this->user_id);
            return $user ? (bool) $user->active : false;
        }
        return false;
    }
    
    /**
     * ¿Tiene información básica completa?
     */
    public function hasInfoBasica(): bool
    {
        return !empty($this->nombres) && 
               !empty($this->apellido_paterno) && 
               !empty($this->apellido_materno) &&
               !empty($this->email) &&
               !empty($this->telefono);
    }
    
    /**
     * ¿Tiene información completa para proceso de venta?
     */
    public function hasInfoCompleta(): bool
    {
        return $this->hasInfoBasica() && 
               !empty($this->rfc) &&
               !empty($this->curp) &&
               $this->fecha_nacimiento !== null;
    }
    
    /**
     * Obtener etapa en formato legible
     */
    public function getEtapaFormatted(): string
    {
        $etapas = [
            'interesado' => 'Interesado',
            'calificado' => 'Calificado', 
            'documentacion' => 'En Documentación',
            'contrato' => 'Firmando Contrato',
            'cerrado' => 'Venta Cerrada'
        ];
        
        return $etapas[$this->etapa_proceso] ?? 'Sin Etapa';
    }
    
    /**
     * ============================================================================
     * MÉTODOS PARA VISTAS (FORMATEO)
     * ============================================================================
     */
    
    /**
     * Calcular edad SOLO para mostrar en formulario
     */
 /**
     * Obtener edad del cliente
     */
    public function getEdad(): ?int
    {
        if (!$this->fecha_nacimiento) {
            return null;
        }

        $fechaNac = $this->fecha_nacimiento;
        if (is_string($fechaNac)) {
            $fechaNac = new \DateTime($fechaNac);
        }

        $hoy = new \DateTime();
        return $hoy->diff($fechaNac)->y;
    }
    
    /**
     * Estado básico para vistas
     */
    public function getEstadoIcono(): string
    {
        if ($this->isActivo()) {
            return '<span class="badge badge-success"><i class="fas fa-check"></i> Activo</span>';
        } else {
            return '<span class="badge badge-danger"><i class="fas fa-times"></i> Inactivo</span>';
        }
    }
    
    /**
     * Teléfono formateado para mostrar
     */
    public function getTelefonoFormateado(): string
    {
        if (empty($this->telefono)) {
            return '';
        }
        
        $tel = $this->telefono;
        
        if (strlen($tel) === 10) {
            return '(' . substr($tel, 0, 2) . ') ' . substr($tel, 2, 4) . '-' . substr($tel, 6, 4);
        }
        
        return $tel;
    }
    
    /**
     * RFC formateado básico
     */
public function getRfcFormateado(): ?string
    {
        if (!$this->rfc) {
            return null;
        }

        return strtoupper($this->rfc);
    }
    
    /**
     * Score básico del cliente
     */
   public function getScoreCliente(): int
    {
        $score = 0;
        $campos = [
            'nombres', 'apellido_paterno', 'email', 'telefono', // Básicos (40%)
            'fecha_nacimiento', 'nacionalidad', 'profesion', // Personales (30%)
            'rfc', 'curp', // Fiscales (20%)
            'estado_civil' // Extra (10%)
        ];

        $valorPorCampo = 100 / count($campos);

        foreach ($campos as $campo) {
            if (!empty($this->$campo)) {
                $score += $valorPorCampo;
            }
        }

        return (int) round($score);
    }
    
    /**
     * ============================================================================
     * LAZY LOADING DE RELACIONES (SOLO PARA FORMULARIOS)
     * ============================================================================
     */
    
    /**
     * Obtener dirección del cliente para edición
     */
    public function getDireccion()
    {
        if (!$this->id) {
            return null;
        }

        $db = \Config\Database::connect();
        $data = $db->table('direcciones_clientes')
                   ->where('cliente_id', $this->id)
                   ->where('tipo', 'principal')
                   ->where('activo', 1)
                   ->get()
                   ->getRowArray();

        if (!$data) {
            // Retornar objeto vacío para evitar errores en la vista
            return (object) [
                'domicilio' => '',
                'numero' => '',
                'colonia' => '',
                'codigo_postal' => '',
                'ciudad' => '',
                'estado' => '',
                'tiempo_radicando' => '',
                'tipo_residencia' => 'propia'
            ];
        }

        return (object) $data;
    }
    
    /**
     * Obtener información laboral para edición
     */
    public function getInformacionLaboral()
    {
        if (!$this->id) {
            return null;
        }

        $db = \Config\Database::connect();
        $data = $db->table('informacion_laboral_clientes')
                   ->where('cliente_id', $this->id)
                   ->where('activo', 1)
                   ->get()
                   ->getRowArray();

        if (!$data) {
            return (object) [
                'nombre_empresa' => '',
                'puesto_cargo' => '',
                'antiguedad' => '',
                'telefono_trabajo' => '',
                'direccion_trabajo' => '',
                'salario' => 0
            ];
        }

        return (object) $data;
    }

    
    /**
     * Obtener información del cónyuge para edición
     */
    public function getInformacionConyuge()
    {
        if (!$this->id) {
            return null;
        }

        $db = \Config\Database::connect();
        $data = $db->table('informacion_conyuge_clientes')
                   ->where('cliente_id', $this->id)
                   ->where('activo', 1)
                   ->get()
                   ->getRowArray();

        if (!$data) {
            return (object) [
                'nombre_completo' => '',
                'profesion' => '',
                'email' => '',
                'telefono' => ''
            ];
        }

        return (object) $data;
    }

    
    /**
     * Obtener referencias para edición
     */
    public function getReferencias()
    {
        if (!$this->id) {
            return [
                (object) ['nombre_completo' => '', 'parentesco' => '', 'telefono' => ''],
                (object) ['nombre_completo' => '', 'parentesco' => '', 'telefono' => '']
            ];
        }

        $db = \Config\Database::connect();
        $referencias = $db->table('referencias_clientes')
                         ->where('cliente_id', $this->id)
                         ->whereIn('tipo', ['referencia_1', 'referencia_2'])
                         ->where('activo', 1)
                         ->orderBy('numero', 'ASC')
                         ->get()
                         ->getResultArray();

        $ref1 = (object) ['nombre_completo' => '', 'parentesco' => '', 'telefono' => ''];
        $ref2 = (object) ['nombre_completo' => '', 'parentesco' => '', 'telefono' => ''];

        if (isset($referencias[0])) {
            $ref1 = (object) $referencias[0];
        }
        if (isset($referencias[1])) {
            $ref2 = (object) $referencias[1];
        }

        return [$ref1, $ref2];
    }
    
    /**
     * ============================================================================
     * MÉTODOS DE ADMINISTRACIÓN BÁSICOS
     * ============================================================================
     */
    
    /**
     * Activar cliente (actualiza users.active)
     */
    public function activar(int $activadoPor): self
    {
        if ($this->user_id) {
            $userModel = new \App\Models\UserModel();
            $userModel->update($this->user_id, ['active' => 1]);
        }
        
        // activado_por se guarda en users.updated_at automáticamente
        
        return $this;
    }
    
    /**
     * Desactivar cliente (actualiza users.active)
     */
    public function desactivar(): self
    {
        if ($this->user_id) {
            $userModel = new \App\Models\UserModel();
            $userModel->update($this->user_id, ['active' => 0]);
        }
        
        // activado_por se maneja automáticamente
        
        return $this;
    }
    
    /**
     * Actualizar etapa del proceso
     */
    public function setEtapaProceso(string $etapa): self
    {
        $etapasValidas = ['interesado', 'calificado', 'documentacion', 'contrato', 'cerrado'];
        
        if (in_array($etapa, $etapasValidas)) {
            $this->etapa_proceso = $etapa;
            $this->fecha_ultima_actividad = date('Y-m-d H:i:s');
        }
        
        return $this;
    }
    
    /**
     * ¿Puede avanzar a la siguiente etapa?
     */
    public function puedeAvanzarEtapa(): bool
    {
        switch ($this->etapa_proceso) {
            case 'interesado':
                return $this->hasInfoBasica();
                
            case 'calificado':
                return $this->hasInfoCompleta();
                
            case 'documentacion':
                return true;
                
            case 'contrato':
                return $this->isActivo();
                
            default:
                return false;
        }
    }
    
    /**
     * ============================================================================
     * FORMATEO AUTOMÁTICO (SETTERS)
     * ============================================================================
     */
    
    /**
     * Formatear RFC (solo letras y números)
     */
    public function setRfc(?string $rfc): self
    {
        if ($rfc) {
            $this->attributes['rfc'] = strtoupper(preg_replace('/[^A-Z0-9]/', '', $rfc));
        } else {
            $this->attributes['rfc'] = null;
        }
        
        return $this;
    }
    
    /**
     * Formatear CURP (solo letras y números)
     */
    public function setCurp(?string $curp): self
    {
        if ($curp) {
            $this->attributes['curp'] = strtoupper(preg_replace('/[^A-Z0-9]/', '', $curp));
        } else {
            $this->attributes['curp'] = null;
        }
        
        return $this;
    }
    
    /**
     * Formatear teléfono (solo números)
     */
    public function setTelefono(?string $telefono): self
    {
        if ($telefono) {
            $this->attributes['telefono'] = preg_replace('/[^0-9]/', '', $telefono);
        } else {
            $this->attributes['telefono'] = null;
        }
        
        return $this;
    }
    
    /**
     * ============================================================================
     * UTILIDADES
     * ============================================================================
     */
    
    /**
     * Limpiar caché después de updates
     */
    public function limpiarCache(): self
    {
        $this->direccionCargada = false;
        $this->laboralCargada = false;
        $this->conyugeCargada = false;
        $this->referenciasCargadas = false;
        
        unset($this->direccion);
        unset($this->informacion_laboral);
        unset($this->informacion_conyuge);
        unset($this->referencias);
        
        return $this;
    }

    /**
     * ============================================================================
     * MÉTODOS ADICIONALES PARA LA VISTA SHOW
     * ============================================================================
     */
    
    /**
     * Obtener teléfono formateado (alias para vista)
     */
    public function getTelefonoFormatted(): string
    {
        return $this->getTelefonoFormateado();
    }
    
    /**
     * Obtener RFC formateado (alias para vista)
     */
    public function getRfcFormatted(): string
    {
        return $this->getRfcFormateado();
    }
    
    /**
     * Verificar si el cliente tiene información completa
     */
    public function tieneInformacionCompleta(): bool
    {
        $camposRequeridos = ['nombres', 'apellido_paterno', 'email', 'telefono'];
        
        foreach ($camposRequeridos as $campo) {
            if (empty($this->$campo)) {
                return false;
            }
        }
        
        return true;
    }


    /**
     * Estado completo del cliente (para admin)
     */
    public function getEstadoCompleto(): array
    {
        return [
            'activo' => $this->isActivo(),
            'etapa' => $this->etapa_proceso,
            'etapa_formatted' => $this->getEtapaFormatted(),
            'score' => $this->getScoreCliente(),
            'info_basica' => $this->hasInfoBasica(),
            'info_completa' => $this->hasInfoCompleta(),
            'puede_avanzar' => $this->puedeAvanzarEtapa(),
            'dias_sin_actividad' => $this->getDiasSinActividad()
        ];
    }
    
    /**
     * Verificar si tiene información de dirección
     */
    public function hasDireccion(): bool
    {
        $direccion = $this->getDireccion();
        return $direccion && (!empty($direccion->domicilio) || !empty($direccion->colonia));
    }
    
    /**
     * Verificar si tiene información laboral
     */
    public function hasInformacionLaboral(): bool
    {
        $laboral = $this->getInformacionLaboral();
        return $laboral && (!empty($laboral->nombre_empresa) || !empty($laboral->puesto_cargo));
    }
    
    /**
     * Verificar si tiene información del cónyuge
     */
    public function hasInformacionConyuge(): bool
    {
        $conyuge = $this->getInformacionConyuge();
        return $conyuge && !empty($conyuge->nombre_completo);
    }
    
    /**
     * Verificar si tiene referencias
     */
    public function hasReferencias(): bool
    {
        $referencias = $this->getReferencias();
        return !empty($referencias);
    }
    
    /**
     * Porcentaje de completitud del perfil
     */
  public function getPorcentajeCompletitud(): int
    {
        $campos = [
            // Datos básicos (50% del total)
            'nombres' => 8,
            'apellido_paterno' => 8,
            'apellido_materno' => 8,
            'email' => 10,
            'telefono' => 10,
            'genero' => 3,
            'fecha_nacimiento' => 3,
            
            // Datos personales (25% del total)
            'lugar_nacimiento' => 3,
            'nacionalidad' => 3,
            'profesion' => 5,
            'estado_civil' => 3,
            'fuente_informacion' => 3,
            'residente' => 3,
            'identificacion' => 2,
            'numero_identificacion' => 3,
            
            // Datos fiscales (15% del total)
            'rfc' => 8,
            'curp' => 7,
            
            // Información adicional (10% del total)
            'razon_social' => 2, // Solo si aplica
            'notas_internas' => 2,
        ];

        $puntajeTotal = 0;
        $puntajeMaximo = 0;

        foreach ($campos as $campo => $puntos) {
            $puntajeMaximo += $puntos;
            
            // Verificar si el campo tiene valor
            $valor = $this->attributes[$campo] ?? null;
            
            if (!empty($valor) && trim($valor) !== '') {
                $puntajeTotal += $puntos;
            }
        }

        // Agregar puntos por dirección (10%)
        $direccion = $this->getDireccion();
        if (!empty($direccion)) {
            $camposDireccion = ['domicilio', 'colonia', 'ciudad', 'codigo_postal'];
            $puntajeDireccion = 0;
            $maxPuntajeDireccion = 10;
            
            foreach ($camposDireccion as $campo) {
                if (!empty($direccion->$campo)) {
                    $puntajeDireccion += 2.5; // 10/4 campos
                }
            }
            
            $puntajeTotal += $puntajeDireccion;
            $puntajeMaximo += $maxPuntajeDireccion;
        } else {
            $puntajeMaximo += 10;
        }

        // Calcular porcentaje
        if ($puntajeMaximo === 0) {
            return 0;
        }

        $porcentaje = round(($puntajeTotal / $puntajeMaximo) * 100);
        
        // Asegurar que esté entre 0 y 100
        return max(0, min(100, $porcentaje));
    }
    
    /**
     * Resumen para mostrar en la vista
     */
    public function getResumenCliente(): array
    {
        return [
            'nombre_completo' => $this->getNombreCompleto(),
            'iniciales' => $this->getIniciales(),
            'email' => $this->email,
            'telefono' => $this->getTelefonoFormateado(),
            'estado' => $this->getEstadoIcono(),
            'etapa' => $this->getEtapaFormatted(),
            'score' => $this->getScoreCliente(),
            'completitud' => $this->getPorcentajeCompletitud(),
            'activo' => $this->isActivo(),
            'fecha_registro' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : 'Sin fecha'
        ];
    }
}