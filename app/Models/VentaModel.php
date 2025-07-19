<?php

namespace App\Models;

use CodeIgniter\Model;

class VentaModel extends Model
{
    protected $table            = 'ventas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Venta::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folio_venta', 'lote_id', 'cliente_id', 'vendedor_id', 'perfil_financiamiento_id', 'apartado_id',
        'fecha_venta', 'precio_lista', 'descuento_aplicado', 'motivo_descuento', 'precio_venta_final',
        'tipo_venta', 'estatus_venta', 'fecha_liquidacion', 'fecha_cancelacion', 'motivo_cancelacion',
        'contrato_generado', 'fecha_contrato', 'numero_contrato', 'observaciones'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'folio_venta' => 'required|max_length[20]|is_unique[ventas.folio_venta,id,{id}]',
        'lote_id' => 'required|integer',
        'cliente_id' => 'required|integer',
        'vendedor_id' => 'required|integer',
        'perfil_financiamiento_id' => 'required|integer',
        'fecha_venta' => 'required|valid_date',
        'precio_lista' => 'required|decimal|greater_than[0]',
        'precio_venta_final' => 'required|decimal|greater_than[0]',
        'tipo_venta' => 'required|in_list[contado,financiado]'
    ];

    public function getVentasActivas()
    {
        return $this->where('estatus_venta', 'activa')
                    ->orderBy('fecha_venta', 'DESC')
                    ->findAll();
    }

    public function getVentasPorVendedor($vendedorId, $fechaInicio = null, $fechaFin = null)
    {
        $builder = $this->where('vendedor_id', $vendedorId);
        
        if ($fechaInicio) {
            $builder->where('fecha_venta >=', $fechaInicio);
        }
        
        if ($fechaFin) {
            $builder->where('fecha_venta <=', $fechaFin);
        }
        
        return $builder->orderBy('fecha_venta', 'DESC')->findAll();
    }

    public function getVentasConRelaciones($filtros = [], $limit = null, $offset = null)
    {
        $builder = $this->db->table('ventas v')
                            ->select('v.*, 
                                     CONCAT(c.nombres, " ", c.apellido_paterno, " ", COALESCE(c.apellido_materno, "")) as cliente_nombres,
                                     c.nombres, c.apellido_paterno, c.apellido_materno,
                                     l.clave as lote_clave, l.area, 
                                     CONCAT(s.nombres, " ", s.apellido_paterno, " ", COALESCE(s.apellido_materno, "")) as vendedor_nombres,
                                     cf.nombre as nombre_plan,
                                     p.nombre as proyecto_nombre, m.nombre as manzana_nombre')
                            ->join('clientes c', 'c.id = v.cliente_id')
                            ->join('lotes l', 'l.id = v.lote_id')
                            ->join('staff s', 's.user_id = v.vendedor_id')
                            ->join('users u', 'u.id = v.vendedor_id', 'left')
                            ->join('perfiles_financiamiento cf', 'cf.id = v.perfil_financiamiento_id', 'left')
                            ->join('manzanas m', 'm.id = l.manzanas_id')
                            ->join('proyectos p', 'p.id = m.proyectos_id');
        
        if (!empty($filtros['estatus'])) {
            $builder->where('v.estatus_venta', $filtros['estatus']);
        }
        
        if (!empty($filtros['tipo'])) {
            $builder->where('v.tipo_venta', $filtros['tipo']);
        }
        
        if (!empty($filtros['vendedor_id'])) {
            $builder->where('v.vendedor_id', $filtros['vendedor_id']);
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $builder->where('v.fecha_venta >=', $filtros['fecha_inicio']);
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $builder->where('v.fecha_venta <=', $filtros['fecha_fin']);
        }
        
        $builder->orderBy('v.fecha_venta', 'DESC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResult();
    }

    public function getEstadisticasVentas($fechaInicio = null, $fechaFin = null)
    {
        $builder = $this->db->table('ventas');
        
        if ($fechaInicio) {
            $builder->where('fecha_venta >=', $fechaInicio);
        }
        
        if ($fechaFin) {
            $builder->where('fecha_venta <=', $fechaFin);
        }
        
        return $builder->select('COUNT(*) as total_ventas,
                                SUM(CASE WHEN estatus_venta = "activa" THEN 1 ELSE 0 END) as ventas_activas,
                                SUM(CASE WHEN estatus_venta = "liquidada" THEN 1 ELSE 0 END) as ventas_liquidadas,
                                SUM(CASE WHEN estatus_venta = "cancelada" THEN 1 ELSE 0 END) as ventas_canceladas,
                                SUM(precio_venta_final) as monto_total,
                                AVG(precio_venta_final) as ticket_promedio,
                                SUM(CASE WHEN tipo_venta = "contado" THEN 1 ELSE 0 END) as ventas_contado,
                                SUM(CASE WHEN tipo_venta = "financiado" THEN 1 ELSE 0 END) as ventas_financiadas')
                       ->get()
                       ->getRow();
    }

    public function actualizarEstatus($ventaId, $nuevoEstatus, $motivo = null)
    {
        $data = ['estatus_venta' => $nuevoEstatus];
        
        switch ($nuevoEstatus) {
            case 'liquidada':
                $data['fecha_liquidacion'] = date('Y-m-d');
                break;
            case 'cancelada':
                $data['fecha_cancelacion'] = date('Y-m-d');
                if ($motivo) {
                    $data['motivo_cancelacion'] = $motivo;
                }
                break;
        }
        
        return $this->update($ventaId, $data);
    }

    public function loteTieneVentaActiva($loteId)
    {
        $venta = $this->where('lote_id', $loteId)
                      ->whereIn('estatus_venta', ['activa', 'juridico'])
                      ->first();
        
        return $venta !== null;
    }

    // ==========================================
    // MÉTODOS ESTADO DE CUENTA AVANZADOS
    // ==========================================

    /**
     * Obtiene todas las ventas activas de un cliente específico
     */
    public function getVentasActivasCliente(int $clienteId): array
    {
        $query = $this->db->table($this->table . ' v')
                         ->select('
                             v.*,
                             l.clave as lote_clave,
                             l.area as lote_area,
                             CONCAT(m.nombre, " - ", l.clave) as descripcion_lote,
                             p.nombre as proyecto_nombre,
                             m.nombre as manzana_nombre,
                             cf.nombre as plan_financiamiento,
                             cf.meses_con_intereses as numero_pagos,
                             cf.porcentaje_interes_anual as tasa_interes_anual
                         ')
                         ->join('lotes l', 'l.id = v.lote_id', 'inner')
                         ->join('manzanas m', 'm.id = l.manzanas_id', 'inner')
                         ->join('proyectos p', 'p.id = m.proyectos_id', 'inner')
                         ->join('perfiles_financiamiento cf', 'cf.id = v.perfil_financiamiento_id', 'left')
                         ->where('v.cliente_id', $clienteId)
                         ->whereIn('v.estatus_venta', ['activa', 'juridico'])
                         ->orderBy('v.fecha_venta', 'DESC');

        return $query->get()->getResult();
    }

    /**
     * Genera la tabla de amortización para una venta financiada
     */
    public function generarAmortizacion(int $ventaId): array
    {
        $venta = $this->find($ventaId);
        
        if (!$venta) {
            return [
                'success' => false,
                'error' => 'Venta no encontrada'
            ];
        }

        // Usar método Entity para generar
        $resultadoEntity = $venta->generarTablaAmortizacion();
        
        if (!$resultadoEntity['success']) {
            return $resultadoEntity;
        }

        // Si la venta requiere tabla de amortización, crearla
        if ($resultadoEntity['requiere_generacion']) {
            // Obtener configuración del perfil de financiamiento
            $perfilModel = new \App\Models\PerfilFinanciamientoModel();
            $perfil = $perfilModel->find($venta->perfil_financiamiento_id);
            
            if (!$perfil) {
                return [
                    'success' => false,
                    'error' => 'Perfil de financiamiento no encontrado'
                ];
            }

            // TODO: Implementar generación usando helper cuando esté disponible
            return [
                'success' => true,
                'message' => 'Tabla de amortización pendiente de generar',
                'config_financiamiento' => [
                    'monto_financiar' => $venta->precio_venta_final,
                    'tasa_interes_anual' => $perfil->tasa_interes_anual ?? 12.0,
                    'numero_pagos' => $perfil->numero_pagos ?? 60,
                    'fecha_primer_pago' => date('Y-m-d', strtotime('+1 month'))
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'Venta no requiere tabla de amortización',
            'venta_data' => $resultadoEntity
        ];
    }

    /**
     * Obtiene el saldo total pendiente de todas las propiedades de un cliente
     */
    public function getSaldoTotal(int $ventaId): array
    {
        $venta = $this->find($ventaId);
        
        if (!$venta) {
            return [
                'success' => false,
                'error' => 'Venta no encontrada'
            ];
        }

        $resumenFinanciero = $venta->getResumenFinanciero();
        
        return [
            'success' => true,
            'venta_id' => $ventaId,
            'folio_venta' => $venta->folio_venta,
            'resumen_financiero' => $resumenFinanciero
        ];
    }

    /**
     * Obtiene el resumen financiero completo de un cliente
     */
    public function getResumenFinanciero(int $clienteId): array
    {
        $ventasActivas = $this->getVentasActivasCliente($clienteId);
        
        $resumen = [
            'cliente_id' => $clienteId,
            'total_propiedades' => count($ventasActivas),
            'monto_total_ventas' => 0,
            'saldo_total_pendiente' => 0,
            'total_pagado' => 0,
            'porcentaje_liquidacion_promedio' => 0,
            'propiedades_liquidadas' => 0,
            'propiedades_con_atrasos' => 0,
            'detalle_propiedades' => []
        ];

        if (empty($ventasActivas)) {
            return $resumen;
        }

        $sumaPorcentajes = 0;
        
        foreach ($ventasActivas as $ventaData) {
            // Crear Entity para usar métodos avanzados
            $venta = new \App\Entities\Venta((array) $ventaData);
            $resumenPropiedad = $venta->getResumenFinanciero();
            $estadoVisual = $venta->getEstadoVisualCompleto();
            
            // Acumular totales
            $resumen['monto_total_ventas'] += $resumenPropiedad['precio_venta'];
            $resumen['saldo_total_pendiente'] += $resumenPropiedad['saldo_pendiente'];
            $resumen['total_pagado'] += $resumenPropiedad['total_pagado'];
            $sumaPorcentajes += $resumenPropiedad['porcentaje_liquidacion'];
            
            if ($resumenPropiedad['esta_liquidada']) {
                $resumen['propiedades_liquidadas']++;
            }
            
            // Verificar atrasos
            $diasProximaMensualidad = $venta->getDiasProximaMensualidad();
            if ($diasProximaMensualidad < 0) {
                $resumen['propiedades_con_atrasos']++;
            }
            
            // Agregar detalle de la propiedad
            $resumen['detalle_propiedades'][] = [
                'venta_id' => $venta->id,
                'folio_venta' => $venta->folio_venta,
                'descripcion_lote' => $ventaData->descripcion_lote,
                'proyecto_nombre' => $ventaData->proyecto_nombre,
                'resumen_financiero' => $resumenPropiedad,
                'estado_visual' => $estadoVisual,
                'dias_proxima_mensualidad' => $diasProximaMensualidad
            ];
        }

        // Calcular promedios
        if ($resumen['total_propiedades'] > 0) {
            $resumen['porcentaje_liquidacion_promedio'] = $sumaPorcentajes / $resumen['total_propiedades'];
        }

        return $resumen;
    }

    /**
     * Obtiene las próximas mensualidades a vencer del cliente
     */
    public function getProximasMensualidadesCliente(int $clienteId, int $diasAnticipacion = 30): array
    {
        $ventasActivas = $this->where('cliente_id', $clienteId)
                             ->whereIn('estatus_venta', ['activa', 'juridico'])
                             ->findAll();

        $proximasMensualidades = [];
        
        foreach ($ventasActivas as $venta) {
            $proximaMensualidad = $venta->getProximaMensualidad();
            
            if ($proximaMensualidad) {
                $fechaVencimiento = new \DateTime($proximaMensualidad->fecha_vencimiento);
                $hoy = new \DateTime();
                $diasHastaVencimiento = $hoy->diff($fechaVencimiento)->days;
                
                if ($diasHastaVencimiento <= $diasAnticipacion) {
                    $proximasMensualidades[] = [
                        'venta_id' => $venta->id,
                        'folio_venta' => $venta->folio_venta,
                        'mensualidad' => $proximaMensualidad,
                        'dias_hasta_vencimiento' => $diasHastaVencimiento,
                        'esta_vencida' => $diasHastaVencimiento < 0
                    ];
                }
            }
        }

        // Ordenar por días hasta vencimiento
        usort($proximasMensualidades, function($a, $b) {
            return $a['dias_hasta_vencimiento'] <=> $b['dias_hasta_vencimiento'];
        });

        return $proximasMensualidades;
    }

    /**
     * Busca ventas con filtros avanzados para estado de cuenta
     */
    public function buscarVentasEstadoCuenta(array $filtros = []): array
    {
        $query = $this->db->table($this->table . ' v')
                         ->select('
                             v.*,
                             CONCAT(c.nombres, " ", c.apellido_paterno, " ", COALESCE(c.apellido_materno, "")) as nombre_cliente,
                             c.email as cliente_email,
                             l.clave as lote_clave,
                             CONCAT(m.nombre, " - ", l.clave) as descripcion_lote,
                             p.nombre as proyecto_nombre,
                             cf.nombre as plan_financiamiento
                         ')
                         ->join('clientes c', 'c.id = v.cliente_id', 'inner')
                         ->join('lotes l', 'l.id = v.lote_id', 'inner')
                         ->join('manzanas m', 'm.id = l.manzanas_id', 'inner')
                         ->join('proyectos p', 'p.id = m.proyectos_id', 'inner')
                         ->join('perfiles_financiamiento cf', 'cf.id = v.perfil_financiamiento_id', 'left');

        // Aplicar filtros
        if (!empty($filtros['cliente_id'])) {
            $query->where('v.cliente_id', $filtros['cliente_id']);
        }

        if (!empty($filtros['estatus_venta'])) {
            if (is_array($filtros['estatus_venta'])) {
                $query->whereIn('v.estatus_venta', $filtros['estatus_venta']);
            } else {
                $query->where('v.estatus_venta', $filtros['estatus_venta']);
            }
        } else {
            // Por defecto solo ventas activas
            $query->whereIn('v.estatus_venta', ['activa', 'juridico']);
        }

        if (!empty($filtros['tipo_venta'])) {
            $query->where('v.tipo_venta', $filtros['tipo_venta']);
        }

        if (!empty($filtros['proyecto_id'])) {
            $query->where('p.id', $filtros['proyecto_id']);
        }

        if (!empty($filtros['vendedor_id'])) {
            $query->where('v.vendedor_id', $filtros['vendedor_id']);
        }

        if (!empty($filtros['nombre_cliente'])) {
            $query->groupStart();
            $query->like('c.nombres', $filtros['nombre_cliente']);
            $query->orLike('c.apellido_paterno', $filtros['nombre_cliente']);
            $query->orLike('c.apellido_materno', $filtros['nombre_cliente']);
            $query->groupEnd();
        }

        if (!empty($filtros['folio_venta'])) {
            $query->like('v.folio_venta', $filtros['folio_venta']);
        }

        // Ordenamiento
        $ordenPor = $filtros['orden_por'] ?? 'v.fecha_venta';
        $direccion = $filtros['direccion'] ?? 'DESC';
        $query->orderBy($ordenPor, $direccion);

        // Paginación
        if (!empty($filtros['limite'])) {
            $offset = $filtros['offset'] ?? 0;
            $query->limit($filtros['limite'], $offset);
        }

        return $query->get()->getResult();
    }

    /**
     * Obtiene alertas de vencimientos para dashboard
     */
    public function getAlertasVencimientos(int $diasAnticipacion = 7): array
    {
        $fechaLimite = date('Y-m-d', strtotime("+{$diasAnticipacion} days"));

        $query = $this->db->table($this->table . ' v')
                         ->select('
                             v.id as venta_id,
                             v.folio_venta,
                             CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente,
                             c.email as cliente_email,
                             ta.numero_pago,
                             ta.fecha_vencimiento,
                             ta.monto_total,
                             ta.estatus,
                             DATEDIFF(ta.fecha_vencimiento, CURDATE()) as dias_hasta_vencimiento,
                             CONCAT(m.nombre, " - ", l.clave) as descripcion_lote
                         ')
                         ->join('clientes c', 'c.id = v.cliente_id', 'inner')
                         ->join('lotes l', 'l.id = v.lote_id', 'inner')
                         ->join('manzanas m', 'm.id = l.manzanas_id', 'inner')
                         ->join('pagos_ventas pv', 'pv.venta_id = v.id', 'inner')
                         ->join('tabla_amortizacion ta', 'ta.id = pv.tabla_amortizacion_id', 'inner')
                         ->where('v.estatus_venta', 'activa')
                         ->where('ta.fecha_vencimiento <=', $fechaLimite)
                         ->whereNotIn('ta.estatus', ['pagada', 'cancelada'])
                         ->orderBy('ta.fecha_vencimiento', 'ASC');

        $alertas = $query->get()->getResult();

        // Categorizar alertas
        $categorizadas = [
            'vencidas' => [],
            'hoy' => [],
            'mañana' => [],
            'esta_semana' => [],
            'proxima_semana' => []
        ];

        foreach ($alertas as $alerta) {
            $dias = $alerta->dias_hasta_vencimiento;
            
            if ($dias < 0) {
                $categorizadas['vencidas'][] = $alerta;
            } elseif ($dias == 0) {
                $categorizadas['hoy'][] = $alerta;
            } elseif ($dias == 1) {
                $categorizadas['mañana'][] = $alerta;
            } elseif ($dias <= 7) {
                $categorizadas['esta_semana'][] = $alerta;
            } else {
                $categorizadas['proxima_semana'][] = $alerta;
            }
        }

        return $categorizadas;
    }

    /**
     * Obtiene estadísticas del estado de cuenta general
     */
    public function getEstadisticasEstadoCuenta(): array
    {
        // Estadísticas generales de ventas activas
        $ventasActivas = $this->db->table($this->table)
                                 ->selectCount('id', 'total_ventas_activas')
                                 ->selectSum('precio_venta_final', 'monto_total_cartera')
                                 ->where('estatus_venta', 'activa')
                                 ->get()
                                 ->getRow();

        // Estadísticas de mensualidades (usando la relación correcta a través de perfiles_financiamiento)
        $mensualidades = $this->db->table('tabla_amortizacion ta')
                                 ->select('
                                     COUNT(ta.id) as total_mensualidades,
                                     SUM(CASE WHEN ta.estatus = "pendiente" THEN 1 ELSE 0 END) as pendientes,
                                     SUM(CASE WHEN ta.estatus = "vencida" THEN 1 ELSE 0 END) as vencidas,
                                     SUM(CASE WHEN ta.estatus = "pagada" THEN 1 ELSE 0 END) as pagadas,
                                     SUM(CASE WHEN ta.estatus != "pagada" THEN ta.saldo_pendiente ELSE 0 END) as saldo_total_pendiente,
                                     SUM(ta.interes_moratorio) as moratorios_totales
                                 ')
                                 ->join('ventas v', 'v.id = ta.plan_financiamiento_id', 'inner')
                                 ->where('v.estatus_venta', 'activa')
                                 ->get()
                                 ->getRow();

        // Clientes únicos con ventas activas
        $clientesActivos = $this->db->table($this->table)
                                   ->select('COUNT(DISTINCT cliente_id) as clientes_activos')
                                   ->where('estatus_venta', 'activa')
                                   ->get()
                                   ->getRow();

        // Total pagado de todas las ventas activas
        $totalPagado = $this->db->table('pagos_ventas pv')
                               ->select('SUM(pv.monto_pago) as total_pagado')
                               ->join('ventas v', 'v.id = pv.venta_id', 'inner')
                               ->where('v.estatus_venta', 'activa')
                               ->where('pv.estatus_pago', 'aplicado')
                               ->get()
                               ->getRow();

        // Saldo pendiente total (precio_venta_final total - total_pagado)
        $precioTotal = $this->db->table($this->table)
                               ->select('SUM(precio_venta_final) as precio_total')
                               ->where('estatus_venta', 'activa')
                               ->get()
                               ->getRow();

        $saldoPendienteTotal = ($precioTotal->precio_total ?? 0) - ($totalPagado->total_pagado ?? 0);

        return [
            'total_propiedades' => $ventasActivas->ventas_activas ?? 0,
            'total_pagado' => $totalPagado->total_pagado ?? 0,
            'saldo_pendiente' => max(0, $saldoPendienteTotal),
            'ventas' => $ventasActivas,
            'mensualidades' => $mensualidades,
            'clientes' => $clientesActivos,
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];
    }
}