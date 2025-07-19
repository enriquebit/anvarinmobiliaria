<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\AnticipoApartado;

/**
 * Model AnticipoApartadoModel
 * 
 * Gestiona los anticipos de apartado y su conversión
 * a liquidación de enganche
 */
class AnticipoApartadoModel extends Model
{
    protected $table            = 'anticipos_apartado';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = AnticipoApartado::class;
    protected $useSoftDeletes    = false;
    protected $protectFields     = true;
    protected $allowedFields     = [
        'venta_id',
        'numero_anticipo',
        'monto_anticipo',
        'monto_acumulado',
        'monto_requerido_enganche',
        'porcentaje_completado',
        'fecha_anticipo',
        'fecha_vencimiento',
        'dias_vigencia',
        'estado',
        'aplica_interes',
        'tasa_interes_mensual',
        'convertido_a_liquidacion',
        'liquidacion_enganche_id',
        'fecha_conversion',
        'observaciones'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'venta_id' => 'required|integer',
        'monto_anticipo' => 'required|decimal|greater_than[0]',
        'monto_requerido_enganche' => 'required|decimal|greater_than[0]',
        'estado' => 'in_list[pendiente,aplicado,vencido,convertido]'
    ];

    /**
     * Obtener anticipos acumulados por venta
     */
    public function getAnticiposAcumulados(int $ventaId): array
    {
        return $this->where('venta_id', $ventaId)
                   ->where('estado !=', 'vencido')
                   ->orderBy('fecha_anticipo', 'DESC')
                   ->findAll();
    }

    /**
     * Calcular faltante para completar enganche
     */
    public function calcularFaltanteEnganche(int $ventaId): array
    {
        $anticipos = $this->getAnticiposAcumulados($ventaId);
        
        if (empty($anticipos)) {
            return [
                'anticipo_encontrado' => false,
                'monto_acumulado' => 0,
                'monto_requerido' => 0,
                'faltante' => 0,
                'porcentaje_completado' => 0
            ];
        }

        $ultimoAnticipo = $anticipos[0];
        
        return [
            'anticipo_encontrado' => true,
            'monto_acumulado' => $ultimoAnticipo->monto_acumulado,
            'monto_requerido' => $ultimoAnticipo->monto_requerido_enganche,
            'faltante' => $ultimoAnticipo->getMontoFaltante(),
            'porcentaje_completado' => $ultimoAnticipo->porcentaje_completado,
            'listo_conversion' => $ultimoAnticipo->listoParaConversion()
        ];
    }

    /**
     * Procesar nuevo anticipo de apartado
     */
    public function procesarAnticipoApartado(array $pagoData): array
    {
        try {
            $this->db->transStart();

            // Buscar o crear registro de anticipo
            $anticipo = $this->where('venta_id', $pagoData['venta_id'])
                             ->where('estado !=', 'vencido')
                             ->first();

            if (!$anticipo) {
                // Crear nuevo registro
                $anticipo = new AnticipoApartado();
                $anticipo->venta_id = $pagoData['venta_id'];
                $anticipo->numero_anticipo = 0;
                $anticipo->monto_acumulado = 0;
                $anticipo->monto_requerido_enganche = $pagoData['monto_enganche_requerido'];
                $anticipo->dias_vigencia = $pagoData['dias_vigencia'] ?? 30;
                $anticipo->aplica_interes = $pagoData['aplica_interes'] ?? false;
                $anticipo->tasa_interes_mensual = $pagoData['tasa_interes'] ?? 0;
                $anticipo->estado = 'pendiente';
                
                // Calcular fecha de vencimiento
                $fechaVencimiento = new \DateTime();
                $fechaVencimiento->modify("+{$anticipo->dias_vigencia} days");
                $anticipo->fecha_vencimiento = $fechaVencimiento->format('Y-m-d');
            }

            // Aplicar nuevo anticipo
            $resultado = $anticipo->aplicarNuevoAnticipo($pagoData['monto_anticipo']);
            
            if (!$resultado['success']) {
                $this->db->transRollback();
                return $resultado;
            }

            // Guardar cambios
            $this->save($anticipo);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'mensaje' => 'Error al procesar el anticipo'
                ];
            }

            return array_merge($resultado, [
                'anticipo_id' => $anticipo->id,
                'fecha_vencimiento' => $anticipo->fecha_vencimiento
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error procesando anticipo: ' . $e->getMessage());
            
            return [
                'success' => false,
                'mensaje' => 'Error interno al procesar el anticipo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si el enganche está completo
     */
    public function verificarCompletitudEnganche(int $ventaId): bool
    {
        $anticipo = $this->where('venta_id', $ventaId)
                         ->where('estado', 'aplicado')
                         ->first();

        return $anticipo ? $anticipo->listoParaConversion() : false;
    }

    /**
     * Convertir anticipos a liquidación de enganche
     */
    public function convertirALiquidacion(int $ventaId): array
    {
        try {
            $this->db->transStart();

            // Verificar que esté listo para conversión
            $anticipo = $this->where('venta_id', $ventaId)
                             ->where('estado', 'aplicado')
                             ->first();

            if (!$anticipo || !$anticipo->listoParaConversion()) {
                return [
                    'success' => false,
                    'mensaje' => 'Los anticipos no están listos para conversión'
                ];
            }

            // Crear liquidación de enganche
            $liquidacionModel = new \App\Models\LiquidacionEngancheModel();
            $liquidacion = new \App\Entities\LiquidacionEnganche();
            
            $liquidacion->venta_id = $ventaId;
            $liquidacion->monto_enganche_requerido = $anticipo->monto_requerido_enganche;
            $liquidacion->monto_anticipos_aplicados = $anticipo->monto_acumulado;
            $liquidacion->monto_pago_directo = 0;
            $liquidacion->tipo_origen = 'anticipos';
            $liquidacion->estado = 'completada';
            $liquidacion->cuenta_abierta = false;
            $liquidacion->tabla_amortizacion_generada = false;
            $liquidacion->anticipos_ids = [$anticipo->id];
            $liquidacion->fecha_liquidacion = date('Y-m-d');
            
            $liquidacion->generarFolio();
            $liquidacionModel->save($liquidacion);

            // Marcar anticipo como convertido
            $anticipo->convertirALiquidacion($liquidacion->id);
            $this->save($anticipo);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'mensaje' => 'Error al crear la liquidación de enganche'
                ];
            }

            return [
                'success' => true,
                'liquidacion_id' => $liquidacion->id,
                'folio_liquidacion' => $liquidacion->folio_liquidacion,
                'monto_liquidado' => $liquidacion->monto_total_liquidado,
                'mensaje' => 'Anticipo convertido exitosamente a liquidación de enganche'
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error convirtiendo a liquidación: ' . $e->getMessage());
            
            return [
                'success' => false,
                'mensaje' => 'Error interno en la conversión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener anticipos próximos a vencer
     */
    public function getAnticiposProximosVencer(int $diasAviso = 7): array
    {
        $fechaLimite = date('Y-m-d', strtotime("+{$diasAviso} days"));
        
        return $this->select('anticipos_apartado.*, v.folio_venta, c.nombres, c.apellido_paterno, c.apellido_materno')
                   ->join('ventas v', 'v.id = anticipos_apartado.venta_id')
                   ->join('clientes c', 'c.id = v.cliente_id')
                   ->where('anticipos_apartado.estado', 'aplicado')
                   ->where('anticipos_apartado.fecha_vencimiento <=', $fechaLimite)
                   ->where('anticipos_apartado.convertido_a_liquidacion', false)
                   ->orderBy('anticipos_apartado.fecha_vencimiento', 'ASC')
                   ->findAll();
    }

    /**
     * Marcar anticipos vencidos
     */
    public function marcarAnticiposVencidos(): int
    {
        $fechaHoy = date('Y-m-d');
        
        return $this->where('fecha_vencimiento <', $fechaHoy)
                   ->where('estado', 'aplicado')
                   ->where('convertido_a_liquidacion', false)
                   ->set('estado', 'vencido')
                   ->update();
    }

    /**
     * Obtener estadísticas de anticipos
     */
    public function getEstadisticasAnticipos(): array
    {
        $stats = $this->select('
                estado,
                COUNT(*) as cantidad,
                SUM(monto_acumulado) as monto_total,
                AVG(porcentaje_completado) as porcentaje_promedio
            ')
            ->where('estado !=', 'vencido')
            ->groupBy('estado')
            ->findAll();

        $estadisticas = [
            'pendiente' => ['cantidad' => 0, 'monto_total' => 0, 'porcentaje_promedio' => 0],
            'aplicado' => ['cantidad' => 0, 'monto_total' => 0, 'porcentaje_promedio' => 0],
            'convertido' => ['cantidad' => 0, 'monto_total' => 0, 'porcentaje_promedio' => 0]
        ];

        foreach ($stats as $stat) {
            $estadisticas[$stat->estado] = [
                'cantidad' => $stat->cantidad,
                'monto_total' => $stat->monto_total,
                'porcentaje_promedio' => round($stat->porcentaje_promedio, 2)
            ];
        }

        return $estadisticas;
    }

    /**
     * Buscar anticipos por criterios
     */
    public function buscarAnticipos(array $filtros = []): array
    {
        $builder = $this->select('
                anticipos_apartado.*,
                v.folio_venta,
                c.nombres,
                c.apellido_paterno,
                c.apellido_materno,
                c.email,
                c.telefono,
                l.clave as lote_clave
            ')
            ->join('ventas v', 'v.id = anticipos_apartado.venta_id')
            ->join('clientes c', 'c.id = v.cliente_id')
            ->join('lotes l', 'l.id = v.lote_id');

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $builder->where('anticipos_apartado.estado', $filtros['estado']);
        }

        if (!empty($filtros['cliente_nombre'])) {
            $builder->groupStart()
                   ->like('c.nombres', $filtros['cliente_nombre'])
                   ->orLike('c.apellido_paterno', $filtros['cliente_nombre'])
                   ->orLike('c.apellido_materno', $filtros['cliente_nombre'])
                   ->groupEnd();
        }

        if (!empty($filtros['folio_venta'])) {
            $builder->like('v.folio_venta', $filtros['folio_venta']);
        }

        if (!empty($filtros['fecha_inicio'])) {
            $builder->where('anticipos_apartado.fecha_anticipo >=', $filtros['fecha_inicio']);
        }

        if (!empty($filtros['fecha_fin'])) {
            $builder->where('anticipos_apartado.fecha_anticipo <=', $filtros['fecha_fin']);
        }

        if (!empty($filtros['monto_minimo'])) {
            $builder->where('anticipos_apartado.monto_acumulado >=', $filtros['monto_minimo']);
        }

        if (!empty($filtros['listo_conversion'])) {
            $builder->where('anticipos_apartado.porcentaje_completado >=', 100);
        }

        return $builder->orderBy('anticipos_apartado.fecha_anticipo', 'DESC')
                      ->findAll();
    }
}