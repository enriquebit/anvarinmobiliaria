<?php

namespace App\Models;

use CodeIgniter\Model;

class ReestructuracionHistorialModel extends Model
{
    protected $table            = 'reestructuraciones_historial';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'reestructuracion_id', 'accion', 'descripcion', 
        'datos_anterior', 'datos_nuevo', 'realizado_por'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id' => 'integer',
        'reestructuracion_id' => 'integer',
        'realizado_por' => 'integer'
    ];

    protected array $castHandlers = [];

    // Validation
    protected $validationRules = [
        'reestructuracion_id' => 'required|is_natural_no_zero',
        'accion' => 'required|max_length[100]',
        'realizado_por' => 'required|is_natural_no_zero'
    ];

    protected $validationMessages = [
        'reestructuracion_id' => [
            'required' => 'La reestructuración es requerida',
            'is_natural_no_zero' => 'Debe ser un ID válido'
        ],
        'accion' => [
            'required' => 'La acción es requerida',
            'max_length' => 'La acción no puede exceder 100 caracteres'
        ],
        'realizado_por' => [
            'required' => 'El usuario es requerido',
            'is_natural_no_zero' => 'Debe ser un ID válido'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // ==========================================
    // MÉTODOS BUSINESS LOGIC
    // ==========================================

    /**
     * Obtiene el historial de una reestructuración
     */
    public function getHistorialReestructuracion(int $reestructuracionId): array
    {
        return $this->select('
            rh.*,
            CONCAT(s.nombres, " ", s.apellido_paterno) as nombre_usuario
        ')
        ->join('users u', 'u.id = rh.realizado_por')
        ->join('staff s', 's.user_id = u.id')
        ->where('rh.reestructuracion_id', $reestructuracionId)
        ->orderBy('rh.created_at', 'DESC')
        ->findAll();
    }

    /**
     * Registra una acción en el historial
     */
    public function registrarAccion(int $reestructuracionId, string $accion, string $descripcion, array $datosAnterior = null, array $datosNuevo = null): bool
    {
        $data = [
            'reestructuracion_id' => $reestructuracionId,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'realizado_por' => session()->get('user_id') ?? 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($datosAnterior) {
            $data['datos_anterior'] = json_encode($datosAnterior);
        }

        if ($datosNuevo) {
            $data['datos_nuevo'] = json_encode($datosNuevo);
        }

        return $this->insert($data);
    }

    /**
     * Obtiene actividad reciente
     */
    public function getActividadReciente(int $limite = 20): array
    {
        return $this->select('
            reestructuraciones_historial.*,
            r.folio_reestructuracion,
            v.folio_venta,
            CONCAT(c.nombres, " ", c.apellido_paterno) as nombre_cliente,
            CONCAT(s.nombres, " ", s.apellido_paterno) as nombre_usuario
        ')
        ->join('reestructuraciones r', 'r.id = reestructuraciones_historial.reestructuracion_id')
        ->join('ventas v', 'v.id = r.venta_id')
        ->join('clientes c', 'c.id = v.cliente_id')
        ->join('users u', 'u.id = reestructuraciones_historial.realizado_por')
        ->join('staff s', 's.user_id = u.id')
        ->orderBy('reestructuraciones_historial.created_at', 'DESC')
        ->limit($limite)
        ->findAll();
    }

    /**
     * Obtiene estadísticas de actividad
     */
    public function getEstadisticasActividad(): array
    {
        $hoy = date('Y-m-d');
        $estaSemana = date('Y-m-d', strtotime('-7 days'));
        $esteMes = date('Y-m-d', strtotime('-30 days'));

        return [
            'actividades_hoy' => $this->where('DATE(created_at)', $hoy)->countAllResults(),
            'actividades_semana' => $this->where('created_at >=', $estaSemana)->countAllResults(),
            'actividades_mes' => $this->where('created_at >=', $esteMes)->countAllResults(),
            'total_actividades' => $this->countAll()
        ];
    }
}