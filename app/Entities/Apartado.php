<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Apartado extends Entity
{
    protected $datamap = [];
    protected $dates   = [
        'fecha_apartado', 
        'fecha_limite_enganche', 
        'fecha_cambio_estatus',
        'fecha_vencimiento',
        'fecha_devolucion',
        'created_at', 
        'updated_at'
    ];
    protected $casts   = [
        'id' => 'integer',
        'lote_id' => 'integer',
        'cliente_id' => 'integer',
        'user_id' => 'integer',
        'perfil_financiamiento_id' => 'integer',
        'venta_id' => '?integer',
        'monto_apartado' => 'float',
        'monto_enganche_requerido' => 'float',
        'dias_plazo' => 'integer',
        'aplico_penalizacion' => 'boolean',
        'monto_penalizacion' => 'float',
        'monto_devuelto' => 'float'
    ];

    /**
     * Verifica si el apartado está vencido
     */
    public function estaVencido(): bool
    {
        if ($this->estatus_apartado !== 'vigente') {
            return false;
        }
        
        return strtotime($this->fecha_limite_enganche) < strtotime('today');
    }

    /**
     * Obtiene los días restantes para completar el enganche
     */
    public function getDiasRestantes(): int
    {
        if ($this->estatus_apartado !== 'vigente') {
            return 0;
        }
        
        $hoy = strtotime('today');
        $limite = strtotime($this->fecha_limite_enganche);
        $dias = ($limite - $hoy) / 86400;
        
        return max(0, (int)$dias);
    }

    /**
     * Verifica si puede convertirse en venta
     */
    public function puedeConvertirseEnVenta(): bool
    {
        return $this->estatus_apartado === 'vigente' && !$this->estaVencido();
    }

    /**
     * Calcula el porcentaje del enganche cubierto por el apartado
     */
    public function getPorcentajeEngancheCubierto(): float
    {
        if ($this->monto_enganche_requerido <= 0) {
            return 100;
        }
        
        return min(100, ($this->monto_apartado / $this->monto_enganche_requerido) * 100);
    }

    /**
     * Obtiene el monto restante para completar el enganche
     */
    public function getMontoRestanteEnganche(): float
    {
        return max(0, $this->monto_enganche_requerido - $this->monto_apartado);
    }

    /**
     * Obtiene el folio del apartado basado en su ID
     * Formato: AP-ID (ejemplo: AP-45, AP-123)
     * El prefijo NO se almacena en BD, solo se genera para mostrar
     */
    public function getFolio(): string
    {
        return "AP-{$this->id}";
    }
    
    /**
     * Método estático para generar folio desde ID
     */
    public static function generarFolioDesdeId(int $apartadoId): string
    {
        return "AP-{$apartadoId}";
    }

    /**
     * Verifica si tiene comprobante adjunto
     */
    public function tieneComprobante(): bool
    {
        return !empty($this->comprobante_url);
    }
}