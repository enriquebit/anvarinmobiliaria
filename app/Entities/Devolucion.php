<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Devolucion extends Entity
{
    protected $datamap = [];
    
    protected $dates = ['created_at', 'updated_at'];
    
    protected $casts = [
        'id'                      => 'integer',
        'ventas_id'               => 'integer',
        'monto_devolucion'        => 'float',
        'procesado_por_user_id'   => 'integer',
        'estado_anterior_lote'    => 'integer',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    // Motivos de devolución
    public const MOTIVO_SOLICITUD_CLIENTE = 'solicitud_cliente';
    public const MOTIVO_INCUMPLIMIENTO = 'incumplimiento_contrato';
    public const MOTIVO_ERROR_ADMIN = 'error_administrativo';
    public const MOTIVO_PROBLEMA_LEGAL = 'problema_legal';
    public const MOTIVO_OTRO = 'otro';

    // Métodos de devolución
    public const METODO_EFECTIVO = 'efectivo';
    public const METODO_TRANSFERENCIA = 'transferencia';
    public const METODO_CHEQUE = 'cheque';
    public const METODO_NOTA_CREDITO = 'nota_credito';

    /**
     * Atributos por defecto
     */
    protected $attributes = [
        'motivo'             => self::MOTIVO_SOLICITUD_CLIENTE,
        'metodo_devolucion'  => self::METODO_EFECTIVO,
        'monto_devolucion'   => 0.00,
    ];

    /**
     * Obtener descripción del motivo
     */
    public function getDescripcionMotivo(): string
    {
        $motivos = [
            self::MOTIVO_SOLICITUD_CLIENTE => 'Solicitud del Cliente',
            self::MOTIVO_INCUMPLIMIENTO => 'Incumplimiento de Contrato',
            self::MOTIVO_ERROR_ADMIN => 'Error Administrativo',
            self::MOTIVO_PROBLEMA_LEGAL => 'Problema Legal',
            self::MOTIVO_OTRO => 'Otro Motivo'
        ];

        return $motivos[$this->motivo] ?? 'Motivo Desconocido';
    }

    /**
     * Obtener descripción del método de devolución
     */
    public function getDescripcionMetodo(): string
    {
        $metodos = [
            self::METODO_EFECTIVO => 'Efectivo',
            self::METODO_TRANSFERENCIA => 'Transferencia Bancaria',
            self::METODO_CHEQUE => 'Cheque',
            self::METODO_NOTA_CREDITO => 'Nota de Crédito'
        ];

        return $metodos[$this->metodo_devolucion] ?? 'Método Desconocido';
    }

    /**
     * Verificar si tiene referencia de devolución
     */
    public function tieneReferencia(): bool
    {
        return !empty($this->referencia_devolucion);
    }

    /**
     * Obtener información completa de la devolución
     */
    public function getInfoCompleta(): array
    {
        return [
            'id' => $this->id,
            'ventas_id' => $this->ventas_id,
            'motivo' => $this->motivo,
            'motivo_descripcion' => $this->getDescripcionMotivo(),
            'monto_devolucion' => $this->monto_devolucion,
            'monto_formateado' => '$' . number_format($this->monto_devolucion, 2),
            'metodo_devolucion' => $this->metodo_devolucion,
            'metodo_descripcion' => $this->getDescripcionMetodo(),
            'referencia_devolucion' => $this->referencia_devolucion,
            'tiene_referencia' => $this->tieneReferencia(),
            'observaciones' => $this->observaciones,
            'procesado_por_user_id' => $this->procesado_por_user_id,
            'estado_anterior_venta' => $this->estado_anterior_venta,
            'estado_anterior_lote' => $this->estado_anterior_lote,
            'fecha_devolucion' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : '',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}