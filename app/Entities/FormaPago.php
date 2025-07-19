<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * FormaPago Entity
 * 
 * Implementa el catálogo de formas de pago del sistema legacy
 * Maneja tipos específicos con requerimientos (referencia, cuenta bancaria)
 * Compatible con el sistema de múltiples formas simultáneas
 */
class FormaPago extends Entity
{
    protected $datamap = [];
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $casts = [
        'id'                       => 'integer',
        'requiere_referencia'      => 'boolean',
        'requiere_cuenta_bancaria' => 'boolean',
        'activa'                   => 'boolean',
        'orden_visualizacion'      => 'integer',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
    ];

    // Tipos de forma de pago según sistema legacy
    public const TIPO_EFECTIVO = 'efectivo';
    public const TIPO_TRANSFERENCIA = 'transferencia';
    public const TIPO_TARJETA = 'tarjeta';
    public const TIPO_CHEQUE = 'cheque';

    /**
     * Atributos por defecto
     */
    protected $attributes = [
        'requiere_referencia'      => false,
        'requiere_cuenta_bancaria' => false,
        'activa'                   => true,
        'orden_visualizacion'      => 1,
    ];

    /**
     * Verificar si es forma de pago en efectivo
     */
    public function esEfectivo(): bool
    {
        return $this->tipo === self::TIPO_EFECTIVO;
    }

    /**
     * Verificar si es transferencia bancaria
     */
    public function esTransferencia(): bool
    {
        return $this->tipo === self::TIPO_TRANSFERENCIA;
    }

    /**
     * Verificar si es pago con tarjeta
     */
    public function esTarjeta(): bool
    {
        return $this->tipo === self::TIPO_TARJETA;
    }

    /**
     * Verificar si es pago con cheque
     */
    public function esCheque(): bool
    {
        return $this->tipo === self::TIPO_CHEQUE;
    }

    /**
     * Verificar si está activa
     */
    public function estaActiva(): bool
    {
        return $this->activa === true;
    }

    /**
     * Verificar si requiere número de referencia
     */
    public function requiereReferencia(): bool
    {
        return $this->requiere_referencia === true;
    }

    /**
     * Verificar si requiere selección de cuenta bancaria
     */
    public function requiereCuentaBancaria(): bool
    {
        return $this->requiere_cuenta_bancaria === true;
    }

    /**
     * Activar forma de pago
     */
    public function activar(): static
    {
        $this->attributes['activa'] = true;
        return $this;
    }

    /**
     * Desactivar forma de pago
     */
    public function desactivar(): static
    {
        $this->attributes['activa'] = false;
        return $this;
    }

    /**
     * Configurar requerimientos específicos
     */
    public function configurarRequerimientos(bool $requiereReferencia, bool $requiereCuentaBancaria): static
    {
        $this->attributes['requiere_referencia'] = $requiereReferencia;
        $this->attributes['requiere_cuenta_bancaria'] = $requiereCuentaBancaria;

        return $this;
    }

    /**
     * Establecer orden de visualización
     */
    public function setOrdenVisualizacion(int $orden): static
    {
        if ($orden < 1) {
            throw new \InvalidArgumentException('El orden de visualización debe ser mayor a 0');
        }

        $this->attributes['orden_visualizacion'] = $orden;
        return $this;
    }

    /**
     * Validar configuración según tipo
     */
    public function validarConfiguracion(): bool
    {
        switch ($this->tipo) {
            case self::TIPO_EFECTIVO:
                // Efectivo normalmente requiere cuenta bancaria para depositar
                // Pero no requiere referencia
                break;
                
            case self::TIPO_TRANSFERENCIA:
                // Transferencias siempre requieren referencia y cuenta destino
                if (!$this->requiere_referencia) {
                    throw new \Exception('Las transferencias deben requerir número de referencia');
                }
                if (!$this->requiere_cuenta_bancaria) {
                    throw new \Exception('Las transferencias deben requerir cuenta bancaria');
                }
                break;
                
            case self::TIPO_TARJETA:
                // Tarjetas pueden requerir cuenta bancaria destino
                // Generalmente no requieren referencia específica
                break;
                
            case self::TIPO_CHEQUE:
                // Cheques requieren número de cheque (referencia) y cuenta destino
                if (!$this->requiere_referencia) {
                    throw new \Exception('Los cheques deben requerir número de referencia');
                }
                if (!$this->requiere_cuenta_bancaria) {
                    throw new \Exception('Los cheques deben requerir cuenta bancaria');
                }
                break;
                
            default:
                throw new \Exception('Tipo de forma de pago no válido: ' . $this->tipo);
        }

        return true;
    }

    /**
     * Obtener información completa de la forma de pago
     */
    public function getInfoCompleta(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'clave' => $this->clave,
            'tipo' => $this->tipo,
            'requiere_referencia' => $this->requiere_referencia,
            'requiere_cuenta_bancaria' => $this->requiere_cuenta_bancaria,
            'activa' => $this->activa,
            'orden_visualizacion' => $this->orden_visualizacion,
            'icono_css' => $this->getIconoCSS(),
            'color_css' => $this->getColorCSS(),
        ];
    }

    /**
     * Obtener icono CSS según tipo
     */
    public function getIconoCSS(): string
    {
        switch ($this->tipo) {
            case self::TIPO_EFECTIVO:
                return 'fas fa-money-bill-wave';
            case self::TIPO_TRANSFERENCIA:
                return 'fas fa-university';
            case self::TIPO_TARJETA:
                return 'fas fa-credit-card';
            case self::TIPO_CHEQUE:
                return 'fas fa-money-check';
            default:
                return 'fas fa-coins';
        }
    }

    /**
     * Obtener color CSS según tipo
     */
    public function getColorCSS(): string
    {
        switch ($this->tipo) {
            case self::TIPO_EFECTIVO:
                return 'success'; // Verde
            case self::TIPO_TRANSFERENCIA:
                return 'primary'; // Azul
            case self::TIPO_TARJETA:
                return 'warning'; // Amarillo
            case self::TIPO_CHEQUE:
                return 'info'; // Cian
            default:
                return 'secondary'; // Gris
        }
    }

    /**
     * Obtener instrucciones para el usuario
     */
    public function getInstrucciones(): string
    {
        $instrucciones = [];

        if ($this->requiere_referencia) {
            switch ($this->tipo) {
                case self::TIPO_TRANSFERENCIA:
                    $instrucciones[] = 'Proporcione el número de referencia bancaria';
                    break;
                case self::TIPO_CHEQUE:
                    $instrucciones[] = 'Proporcione el número de cheque';
                    break;
                default:
                    $instrucciones[] = 'Proporcione el número de referencia';
            }
        }

        if ($this->requiere_cuenta_bancaria) {
            $instrucciones[] = 'Seleccione la cuenta bancaria de destino';
        }

        if ($this->esTransferencia()) {
            $instrucciones[] = 'Especifique el nombre del ordenante';
        }

        return implode('. ', $instrucciones);
    }

    /**
     * Crear configuración específica para formularios
     */
    public function getConfiguracionFormulario(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'clave' => $this->clave,
            'tipo' => $this->tipo,
            'icono' => $this->getIconoCSS(),
            'color' => $this->getColorCSS(),
            'campos_requeridos' => [
                'referencia' => $this->requiere_referencia,
                'cuenta_bancaria' => $this->requiere_cuenta_bancaria,
                'ordenante' => $this->esTransferencia(),
                'fecha_referencia' => $this->esTransferencia() || $this->esCheque(),
            ],
            'validaciones' => [
                'monto_minimo' => $this->esEfectivo() ? 0.01 : 0.01,
                'monto_maximo' => $this->esEfectivo() ? 999999.99 : null,
            ],
            'instrucciones' => $this->getInstrucciones(),
            'placeholder_referencia' => $this->getPlaceholderReferencia(),
        ];
    }

    /**
     * Obtener placeholder para campo de referencia
     */
    private function getPlaceholderReferencia(): string
    {
        switch ($this->tipo) {
            case self::TIPO_TRANSFERENCIA:
                return 'Ej: 1234567890';
            case self::TIPO_CHEQUE:
                return 'Ej: 001234';
            case self::TIPO_TARJETA:
                return 'Ej: Autorización 123456';
            default:
                return 'Número de referencia';
        }
    }

    /**
     * Verificar compatibilidad con otras formas de pago
     */
    public function esCompatibleCon(FormaPago $otraForma): bool
    {
        // Todas las formas de pago son compatibles entre sí
        // según el sistema legacy (pagos múltiples simultáneos)
        return true;
    }

    /**
     * Clonar forma de pago para crear una nueva
     */
    public function clonar(string $nuevoNombre, string $nuevaClave): static
    {
        $nueva = new static();
        
        $nueva->nombre = $nuevoNombre;
        $nueva->clave = $nuevaClave;
        $nueva->tipo = $this->tipo;
        $nueva->requiere_referencia = $this->requiere_referencia;
        $nueva->requiere_cuenta_bancaria = $this->requiere_cuenta_bancaria;
        $nueva->activa = true;
        $nueva->orden_visualizacion = $this->orden_visualizacion + 1;

        return $nueva;
    }
}