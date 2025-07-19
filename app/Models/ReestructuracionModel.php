<?php

namespace App\Models;

use CodeIgniter\Model;

class ReestructuracionModel extends Model
{
    protected $table            = 'reestructuraciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'venta_id', 'folio_reestructuracion', 'motivo', 'fecha_reestructuracion',
        'fecha_vencimiento_original', 'saldo_pendiente_original', 'saldo_capital_original',
        'saldo_interes_original', 'saldo_moratorio_original', 'quita_aplicada',
        'descuento_intereses', 'descuento_moratorios', 'nuevo_saldo_capital',
        'nuevo_plazo_meses', 'nueva_tasa_interes', 'nuevo_pago_mensual',
        'enganche_convenio', 'fecha_primer_pago', 'estatus', 'autorizado_por',
        'fecha_autorizacion', 'autorizado_por_nombre', 'registrado_por',
        'observaciones', 'documento_convenio'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'venta_id' => 'integer',
        'saldo_pendiente_original' => 'float',
        'saldo_capital_original' => 'float',
        'saldo_interes_original' => 'float',
        'saldo_moratorio_original' => 'float',
        'quita_aplicada' => 'float',
        'descuento_intereses' => 'float',
        'descuento_moratorios' => 'float',
        'nuevo_saldo_capital' => 'float',
        'nuevo_plazo_meses' => 'integer',
        'nueva_tasa_interes' => 'float',
        'nuevo_pago_mensual' => 'float',
        'enganche_convenio' => 'float',
        'autorizado_por' => 'integer',
        'registrado_por' => 'integer'
    ];

    protected array $castHandlers = [];

    // Validation
    protected $validationRules = [
        'venta_id' => 'required|is_natural_no_zero',
        'folio_reestructuracion' => 'required|max_length[50]',
        'fecha_reestructuracion' => 'required|valid_date',
        'saldo_pendiente_original' => 'required|decimal|greater_than_equal_to[0]',
        'nuevo_saldo_capital' => 'required|decimal|greater_than_equal_to[0]',
        'nuevo_plazo_meses' => 'required|is_natural_no_zero|greater_than[0]',
        'nueva_tasa_interes' => 'required|decimal|greater_than_equal_to[0]',
        'nuevo_pago_mensual' => 'required|decimal|greater_than[0]',
        'registrado_por' => 'required|is_natural_no_zero'
    ];

    protected $validationMessages = [
        'venta_id' => [
            'required' => 'La venta es requerida',
            'is_natural_no_zero' => 'Debe ser un ID válido'
        ],
        'folio_reestructuracion' => [
            'required' => 'El folio es requerido',
            'max_length' => 'El folio no puede exceder 50 caracteres'
        ],
        'fecha_reestructuracion' => [
            'required' => 'La fecha de reestructuración es requerida',
            'valid_date' => 'Debe ser una fecha válida'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateFolio'];
    protected $afterInsert    = ['registrarHistorial'];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = ['registrarHistorial'];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // ==========================================
    // MÉTODOS BUSINESS LOGIC
    // ==========================================

    /**
     * Obtiene reestructuraciones por venta
     */
    public function getReestructuracionesByVenta(int $ventaId): array
    {
        return $this->where('venta_id', $ventaId)
                   ->orderBy('fecha_reestructuracion', 'DESC')
                   ->findAll();
    }

    /**
     * Obtiene reestructuraciones pendientes de autorización
     */
    public function getReestructuracionesPendientes(): array
    {
        return $this->select('reestructuraciones.*, v.folio_venta, CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente')
                   ->join('ventas v', 'v.id = reestructuraciones.venta_id')
                   ->join('clientes c', 'c.id = v.cliente_id')
                   ->where('reestructuraciones.estatus', 'propuesta')
                   ->orderBy('reestructuraciones.fecha_reestructuracion', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene reestructuraciones activas
     */
    public function getReestructuracionesActivas(): array
    {
        return $this->select('reestructuraciones.*, v.folio_venta, CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente')
                   ->join('ventas v', 'v.id = reestructuraciones.venta_id')
                   ->join('clientes c', 'c.id = v.cliente_id')
                   ->where('reestructuraciones.estatus', 'activa')
                   ->orderBy('reestructuraciones.fecha_reestructuracion', 'DESC')
                   ->findAll();
    }

    /**
     * Calcula estadísticas de reestructuraciones
     */
    public function getEstadisticasReestructuraciones(): array
    {
        $estadisticas = [
            'total_reestructuraciones' => $this->countAll(),
            'pendientes_autorizacion' => $this->where('estatus', 'propuesta')->countAllResults(),
            'activas' => $this->where('estatus', 'activa')->countAllResults(),
            'canceladas' => $this->where('estatus', 'cancelada')->countAllResults(),
        ];

        // Obtener montos
        $montos = $this->select('
            SUM(saldo_pendiente_original) as total_saldo_original,
            SUM(nuevo_saldo_capital) as total_nuevo_saldo,
            SUM(quita_aplicada) as total_quitas_aplicadas,
            SUM(descuento_intereses + descuento_moratorios) as total_descuentos
        ')->first();

        return array_merge($estadisticas, (array) $montos);
    }

    /**
     * Autoriza una reestructuración
     */
    public function autorizar(int $id, int $autorizadoPor, string $autorizadoPorNombre): bool
    {
        $data = [
            'estatus' => 'autorizada',
            'autorizado_por' => $autorizadoPor,
            'fecha_autorizacion' => date('Y-m-d H:i:s'),
            'autorizado_por_nombre' => $autorizadoPorNombre
        ];

        return $this->update($id, $data);
    }

    /**
     * Activa una reestructuración (después de firma)
     */
    public function activar(int $id): bool
    {
        return $this->update($id, ['estatus' => 'activa']);
    }

    /**
     * Cancela una reestructuración
     */
    public function cancelar(int $id, string $motivo = null): bool
    {
        $data = ['estatus' => 'cancelada'];
        if ($motivo) {
            $data['observaciones'] = $motivo;
        }

        return $this->update($id, $data);
    }

    /**
     * Obtiene el detalle de una reestructuración con datos relacionados
     */
    public function getDetalleCompleto(int $id): ?object
    {
        return $this->select('
            reestructuraciones.*,
            v.folio_venta,
            v.precio_venta_final,
            v.estatus_venta,
            CONCAT(c.nombres, " ", c.apellido_paterno, " ", c.apellido_materno) as nombre_cliente,
            c.telefono as telefono_cliente,
            c.email as email_cliente,
            l.clave as clave_lote,
            p.nombre as nombre_proyecto,
            CONCAT(s.nombres, " ", s.apellido_paterno) as nombre_vendedor,
            CONCAT(sr.nombres, " ", sr.apellido_paterno) as nombre_registrado_por
        ')
        ->join('ventas v', 'v.id = reestructuraciones.venta_id')
        ->join('clientes c', 'c.id = v.cliente_id')
        ->join('lotes l', 'l.id = v.lote_id')
        ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
        ->join('users u', 'u.id = v.vendedor_id')
        ->join('staff s', 's.user_id = u.id')
        ->join('users ur', 'ur.id = reestructuraciones.registrado_por')
        ->join('staff sr', 'sr.user_id = ur.id')
        ->where('reestructuraciones.id', $id)
        ->first();
    }

    // ==========================================
    // CALLBACKS
    // ==========================================

    /**
     * Genera folio automáticamente
     */
    protected function generateFolio(array $data): array
    {
        if (empty($data['data']['folio_reestructuracion'])) {
            $year = date('Y');
            $month = date('m');
            
            // Formato: REEST-AAAAMM-XXXXX
            $count = $this->like('folio_reestructuracion', "REEST-{$year}{$month}", 'after')
                         ->countAllResults() + 1;
            
            $data['data']['folio_reestructuracion'] = sprintf("REEST-%s%s-%05d", $year, $month, $count);
        }
        
        return $data;
    }

    /**
     * Registra en historial
     */
    protected function registrarHistorial(array $data): array
    {
        $historialModel = new \App\Models\ReestructuracionHistorialModel();
        
        $accion = isset($data['id']) ? 'actualizar' : 'crear';
        $descripcion = $accion === 'crear' ? 'Reestructuración creada' : 'Reestructuración actualizada';
        
        $historialModel->insert([
            'reestructuracion_id' => $data['id'] ?? $this->getInsertID(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos_nuevo' => json_encode($data['data'] ?? []),
            'realizado_por' => session()->get('user_id') ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $data;
    }

    /**
     * Busca reestructuraciones con filtros
     */
    public function buscarReestructuraciones(array $filtros = []): array
    {
        $query = $this->select('
            reestructuraciones.*,
            v.folio_venta,
            CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente,
            l.clave as clave_lote,
            p.nombre as nombre_proyecto
        ')
        ->join('ventas v', 'v.id = reestructuraciones.venta_id')
        ->join('clientes c', 'c.id = v.cliente_id')
        ->join('lotes l', 'l.id = v.lote_id')
        ->join('proyectos p', 'p.id = l.proyectos_id', 'left');

        // Aplicar filtros
        if (!empty($filtros['estatus'])) {
            $query->where('reestructuraciones.estatus', $filtros['estatus']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('reestructuraciones.fecha_reestructuracion >=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('reestructuraciones.fecha_reestructuracion <=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['cliente_nombre'])) {
            $query->groupStart()
                  ->like('c.nombres', $filtros['cliente_nombre'])
                  ->orLike('c.apellido_paterno', $filtros['cliente_nombre'])
                  ->orLike('c.apellido_materno', $filtros['cliente_nombre'])
                  ->groupEnd();
        }

        if (!empty($filtros['folio_venta'])) {
            $query->like('v.folio_venta', $filtros['folio_venta']);
        }

        // Ordenamiento
        $ordenPor = $filtros['orden_por'] ?? 'reestructuraciones.fecha_reestructuracion';
        $direccion = $filtros['direccion'] ?? 'DESC';
        $query->orderBy($ordenPor, $direccion);

        return $query->findAll();
    }
}