<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Lote extends Entity
{
    protected $datamap = [];
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $casts = [
        'id'                     => 'integer',
        'empresas_id'            => 'integer',
        'proyectos_id'           => 'integer',
        'manzanas_id'            => 'integer',
        'categorias_lotes_id'    => 'integer',
        'tipos_lotes_id'         => 'integer',
        'estados_lotes_id'       => 'integer',
        'area'                   => 'float',
        'frente'                 => 'float',
        'fondo'                  => 'float',
        'lateral_izquierdo'      => 'float',
        'lateral_derecho'        => 'float',
        'construccion'           => 'float',
        'precio_m2'              => 'float',
        'precio_total'           => 'float',
        'activo'                 => 'boolean',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    // Estados del lote según IDs de tabla estados_lotes
    public const ESTADO_DISPONIBLE = 0; // "Disponible"
    public const ESTADO_APARTADO   = 1; // "Apartado"  
    public const ESTADO_VENDIDO    = 2; // "Vendido"
    public const ESTADO_SUSPENDIDO = 3; // "Suspendido"

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $attributes = [
        'activo'           => true,
        'color'            => '#3498db',
        'estados_lotes_id' => self::ESTADO_DISPONIBLE,
        'area'             => 0.00,
        'precio_m2'        => 0.00,
        'precio_total'     => 0.00,
    ];

    /**
     * Genera automáticamente la clave del lote
     * Formato: {proyecto.clave}-{manzana.nombre}-{numero}
     */
    public function setClave(?string $valor = null): static
    {
        if ($valor !== null) {
            $this->attributes['clave'] = $valor;
            return $this;
        }

        // Auto-generar clave si tenemos los datos necesarios
        if (isset($this->attributes['proyectos_id']) && 
            isset($this->attributes['manzanas_id']) && 
            isset($this->attributes['numero'])) {
            
            $proyectoModel = model('ProyectoModel');
            $manzanaModel = model('ManzanaModel');
            
            $proyecto = $proyectoModel->find($this->attributes['proyectos_id']);
            $manzana = $manzanaModel->find($this->attributes['manzanas_id']);
            
            if ($proyecto && $manzana) {
                $this->attributes['clave'] = $proyecto->clave . '-' . $manzana->nombre . '-' . $this->attributes['numero'];
            }
        }

        return $this;
    }

    /**
     * Mutator para el número - siempre mayúsculas y sin espacios extra
     */
    public function setNumero(string $numero): static
    {
        $this->attributes['numero'] = strtoupper(trim($numero));
        
        // Auto-generar clave cuando se establece el número
        $this->setClave();
        
        return $this;
    }

    /**
     * Calcular precio total automáticamente
     */
    public function setPrecioTotal(?float $valor = null): static
    {
        if ($valor !== null) {
            $this->attributes['precio_total'] = $valor;
            return $this;
        }

        // Auto-calcular si tenemos área y precio_m2
        if (isset($this->attributes['area']) && isset($this->attributes['precio_m2'])) {
            $this->attributes['precio_total'] = $this->attributes['area'] * $this->attributes['precio_m2'];
        }

        return $this;
    }

    /**
     * Mutator para área - recalcular precio total
     */
    public function setArea(float $area): static
    {
        $this->attributes['area'] = $area;
        $this->setPrecioTotal(); // Recalcular precio total
        return $this;
    }

    /**
     * Mutator para precio por m2 - recalcular precio total
     */
    public function setPrecioM2(float $precio): static
    {
        $this->attributes['precio_m2'] = $precio;
        $this->setPrecioTotal(); // Recalcular precio total
        return $this;
    }

    /**
     * Mutator para coordenadas GPS - validar formato
     */
    public function setLongitud(?string $longitud): static
    {
        if ($longitud !== null && $longitud !== '') {
            if (is_numeric($longitud) && $longitud >= -180 && $longitud <= 180) {
                $this->attributes['longitud'] = $longitud;
            }
        } else {
            $this->attributes['longitud'] = null;
        }
        
        return $this;
    }

    public function setLatitud(?string $latitud): static
    {
        if ($latitud !== null && $latitud !== '') {
            if (is_numeric($latitud) && $latitud >= -90 && $latitud <= 90) {
                $this->attributes['latitud'] = $latitud;
            }
        } else {
            $this->attributes['latitud'] = null;
        }
        
        return $this;
    }

    /**
     * Accessor para obtener el nombre de la empresa relacionada
     */
    public function getNombreEmpresa(): string
    {
        if (isset($this->attributes['empresas_id'])) {
            $empresaModel = model('EmpresaModel');
            $empresa = $empresaModel->find($this->attributes['empresas_id']);
            return $empresa ? $empresa->nombre : 'Empresa no encontrada';
        }
        
        return '';
    }

    /**
     * Accessor para obtener el nombre del proyecto relacionado
     */
    public function getNombreProyecto(): string
    {
        if (isset($this->attributes['proyectos_id'])) {
            $proyectoModel = model('ProyectoModel');
            $proyecto = $proyectoModel->find($this->attributes['proyectos_id']);
            return $proyecto ? $proyecto->nombre : 'Proyecto no encontrado';
        }
        
        return '';
    }

    /**
     * Accessor para obtener el nombre de la manzana relacionada
     */
    public function getNombreManzana(): string
    {
        if (isset($this->attributes['manzanas_id'])) {
            $manzanaModel = model('ManzanaModel');
            $manzana = $manzanaModel->find($this->attributes['manzanas_id']);
            return $manzana ? $manzana->nombre : 'Manzana no encontrada';
        }
        
        return '';
    }

    /**
     * Accessor para obtener la división del lote
     */
    public function getNombreDivision(): string
    {
        if (isset($this->attributes['divisiones_id'])) {
            $divisionModel = model('DivisionModel');
            $division = $divisionModel->find($this->attributes['divisiones_id']);
            return $division ? $division->nombre : 'División no encontrada';
        }
        
        return '';
    }

    /**
     * Accessor para obtener el estado del lote
     */
    public function getNombreEstado(): string
    {
        if (isset($this->attributes['estados_lotes_id'])) {
            $estadoModel = model('EstadoLoteModel');
            $estado = $estadoModel->find($this->attributes['estados_lotes_id']);
            return $estado ? $estado->nombre : 'Estado no encontrado';
        }
        
        return '';
    }

    /**
     * Accessor para obtener la categoría del lote
     */
    public function getNombreCategoria(): string
    {
        if (isset($this->attributes['categorias_lotes_id'])) {
            $categoriaModel = model('CategoriaLoteModel');
            $categoria = $categoriaModel->find($this->attributes['categorias_lotes_id']);
            return $categoria ? $categoria->nombre : 'Categoría no encontrada';
        }
        
        return '';
    }

    /**
     * Accessor para obtener el tipo del lote
     */
    public function getNombreTipo(): string
    {
        if (isset($this->attributes['tipos_lotes_id'])) {
            $tipoModel = model('TipoLoteModel');
            $tipo = $tipoModel->find($this->attributes['tipos_lotes_id']);
            return $tipo ? $tipo->nombre : 'Tipo no encontrado';
        }
        
        return '';
    }

    /**
     * Obtener el código del estado actual
     */
    public function getCodigoEstado(): int
    {
        static $codigosCache = [];
        
        if (!isset($codigosCache[$this->estados_lotes_id])) {
            $estadoModel = model('EstadoLoteModel');
            $estado = $estadoModel->find($this->estados_lotes_id);
            $codigosCache[$this->estados_lotes_id] = $estado ? $estado->codigo : -1;
        }
        
        return $codigosCache[$this->estados_lotes_id];
    }

    /**
     * Verificar si el lote está disponible para venta
     */
    public function estaDisponible(): bool
    {
        return $this->getCodigoEstado() === self::ESTADO_DISPONIBLE && $this->activo;
    }

    /**
     * Verificar si el lote está apartado
     */
    public function estaApartado(): bool
    {
        return $this->getCodigoEstado() === self::ESTADO_APARTADO;
    }

    /**
     * Verificar si el lote está vendido
     */
    public function estaVendido(): bool
    {
        return $this->getCodigoEstado() === self::ESTADO_VENDIDO;
    }

    /**
     * Verificar si el lote puede ser eliminado
     */
    public function puedeSerEliminado(): bool
    {
        // No se puede eliminar si está vendido o apartado
        return !$this->estaVendido() && !$this->estaApartado();
    }

    /**
     * Calcular superficie total (incluye área + construcción)
     */
    public function getSuperficieTotal(): float
    {
        return $this->area + $this->construccion;
    }

    /**
     * Obtener coordenadas como array
     */
    public function getCoordenadas(): ?array
    {
        if ($this->longitud && $this->latitud) {
            return [
                'longitud' => (float) $this->longitud,
                'latitud'  => (float) $this->latitud,
            ];
        }
        
        return null;
    }

    /**
     * Obtener dimensiones como array
     */
    public function getDimensiones(): array
    {
        return [
            'frente'            => $this->frente,
            'fondo'             => $this->fondo,
            'lateral_izquierdo' => $this->lateral_izquierdo,
            'lateral_derecho'   => $this->lateral_derecho,
        ];
    }

    /**
     * Obtener amenidades del lote
     */
    public function getAmenidades(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }

        $loteAmenidadModel = model('LoteAmenidadModel');
        return $loteAmenidadModel->getAmenidadesPorLote($this->id);
    }

    /**
     * Obtener información completa del lote
     */
    public function getInfoCompleta(): array
    {
        return [
            'id'                => $this->id,
            'numero'            => $this->numero,
            'clave'             => $this->clave,
            'proyecto'          => $this->getNombreProyecto(),
            'manzana'           => $this->getNombreManzana(),
            'estado'            => $this->getNombreEstado(),
            'categoria'         => $this->getNombreCategoria(),
            'tipo'              => $this->getNombreTipo(),
            'area'              => $this->area,
            'precio_m2'         => $this->precio_m2,
            'precio_total'      => $this->precio_total,
            'dimensiones'       => $this->getDimensiones(),
            'coordenadas_poligono'       => $this->getCoordenadas(),
            'amenidades'        => $this->getAmenidades(),
            'disponible'        => $this->estaDisponible(),
            'apartado'          => $this->estaApartado(),
            'vendido'           => $this->estaVendido(),
            'activo'            => $this->activo,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }

    /**
     * Cambiar estado del lote (busca por código y asigna el ID correspondiente)
     */
    public function cambiarEstado(int $codigoEstado): static
    {
        $estadosValidos = [
            self::ESTADO_DISPONIBLE,
            self::ESTADO_APARTADO,
            self::ESTADO_VENDIDO,
            self::ESTADO_SUSPENDIDO
        ];

        if (in_array($codigoEstado, $estadosValidos)) {
            // Buscar el ID del estado por su código
            $estadoModel = model('EstadoLoteModel');
            $estado = $estadoModel->where('codigo', $codigoEstado)->first();
            
            if ($estado) {
                $this->attributes['estados_lotes_id'] = $estado->id;
            }
        }

        return $this;
    }

    /**
     * MÉTODOS PARA MÓDULO DE VENTAS
     */

    /**
     * Apartar lote para cliente/vendedor
     * NOTA: Para operaciones complejas usar EstadoLoteService
     */
    public function apartar(): static
    {
        if (!$this->estaDisponible()) {
            throw new \Exception('Lote no disponible para apartado');
        }
        
        $this->cambiarEstado(self::ESTADO_APARTADO);
        return $this;
    }

    /**
     * Confirmar venta del lote
     * NOTA: Para operaciones complejas usar EstadoLoteService
     */
    public function vender(): static
    {
        if (!$this->estaApartado() && !$this->estaDisponible()) {
            throw new \Exception('Lote no válido para venta');
        }
        
        $this->cambiarEstado(self::ESTADO_VENDIDO);
        return $this;
    }

    /**
     * Liberar lote (cancelar apartado o devolución)
     * NOTA: Para operaciones complejas usar EstadoLoteService
     */
    public function liberar(): static
    {
        if (!$this->estaApartado() && !$this->estaVendido()) {
            throw new \Exception('Solo se pueden liberar lotes apartados o vendidos (para devoluciones)');
        }
        
        $this->cambiarEstado(self::ESTADO_DISPONIBLE);
        return $this;
    }

    /**
     * Suspender lote (quitar de venta)
     */
    public function suspender(): static
    {
        $this->cambiarEstado(self::ESTADO_SUSPENDIDO);
        return $this;
    }

    /**
     * Reactivar lote suspendido
     */
    public function reactivar(): static
    {
        if ($this->getCodigoEstado() !== self::ESTADO_SUSPENDIDO) {
            throw new \Exception('Solo se pueden reactivar lotes suspendidos');
        }
        
        $this->cambiarEstado(self::ESTADO_DISPONIBLE);
        return $this;
    }

    /**
     * Verificar si puede cambiar de estado
     */
    public function puedeApartar(): bool
    {
        return $this->estaDisponible() && $this->activo;
    }

    /**
     * Verificar si puede ser vendido
     */
    public function puedeVenderse(): bool
    {
        return ($this->estaDisponible() || $this->estaApartado()) && $this->activo;
    }

    /**
     * Calcular precio con descuento
     */
    public function calcularPrecioConDescuento(float $porcentajeDescuento): float
    {
        if ($porcentajeDescuento < 0 || $porcentajeDescuento > 100) {
            throw new \InvalidArgumentException('Descuento debe estar entre 0 y 100');
        }
        
        $descuento = $this->precio_total * ($porcentajeDescuento / 100);
        return $this->precio_total - $descuento;
    }

    /**
     * Obtener información para venta
     */
    public function getInfoVenta(): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'clave' => $this->clave,
            'proyecto' => $this->getNombreProyecto(),
            'manzana' => $this->getNombreManzana(),
            'area' => $this->area,
            'precio_m2' => $this->precio_m2,
            'precio_total' => $this->precio_total,
            'construccion' => $this->construccion,
            'estado_actual' => $this->getNombreEstado(),
            'estado_codigo' => $this->getCodigoEstado(),
            'disponible' => $this->estaDisponible(),
            'apartado' => $this->estaApartado(),
            'vendido' => $this->estaVendido(),
            'puede_apartar' => $this->puedeApartar(),
            'puede_venderse' => $this->puedeVenderse(),
            'dimensiones' => $this->getDimensiones(),
            'amenidades' => $this->getAmenidades()
        ];
    }
    
    /**
     * Verificar si el lote puede cambiar a un estado específico
     */
    public function puedeTransicionarA(int $estadoDestino): bool
    {
        // Usar EstadoLoteService para validaciones complejas
        $estadoService = \Config\Services::estadoLoteService();
        return $estadoService->validarTransicion($this, $estadoDestino, 'verificar');
    }
    
    /**
     * Obtener transiciones disponibles
     */
    public function getTransicionesDisponibles(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }
        
        $estadoService = new \App\Services\EstadoLoteService();
        return $estadoService->getTransicionesDisponibles($this->id);
    }
    
    /**
     * Obtener historial de cambios de estado
     */
    public function getHistorialEstados(): array
    {
        if (!isset($this->attributes['id'])) {
            return [];
        }
        
        $estadoService = new \App\Services\EstadoLoteService();
        return $estadoService->getHistorialEstados($this->id);
    }
}