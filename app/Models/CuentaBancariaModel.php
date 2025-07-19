<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\CuentaBancaria;

class CuentaBancariaModel extends Model
{
    protected $table = 'cuentas_bancarias';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = CuentaBancaria::class;
    protected $useSoftDeletes = true;

    // Campos permitidos para inserción/actualización
    protected $allowedFields = [
        'descripcion', 'banco', 'numero_cuenta', 'clabe', 'swift',
        'titular', 'convenio', 'saldo_inicial', 'saldo_actual',
        'moneda', 'tipo_cuenta', 'permite_depositos', 'permite_retiros',
        'color_identificacion', 'logotipo_banco', 'proyecto_id', 'empresa_id',
        'activo', 'notas', 'fecha_apertura', 'fecha_ultimo_corte'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validaciones
    protected $validationRules = [
        'descripcion' => 'required|max_length[255]',
        'banco' => 'required|max_length[100]',
        'numero_cuenta' => 'required|max_length[20]',
        'clabe' => 'permit_empty|exact_length[18]|numeric',
        'swift' => 'permit_empty|min_length[8]|max_length[11]|alpha_numeric',
        'titular' => 'required|max_length[255]',
        'convenio' => 'permit_empty|max_length[50]',
        'saldo_inicial' => 'permit_empty|decimal',
        'saldo_actual' => 'permit_empty|decimal',
        'moneda' => 'required|in_list[MXN,USD,EUR]',
        'tipo_cuenta' => 'required|in_list[corriente,ahorro,inversion,efectivo]',
        'permite_depositos' => 'permit_empty|in_list[0,1]',
        'permite_retiros' => 'permit_empty|in_list[0,1]',
        'color_identificacion' => 'permit_empty|regex_match[/^#[0-9A-Fa-f]{6}$/]',
        'proyecto_id' => 'permit_empty|is_natural_no_zero',
        'empresa_id' => 'required|is_natural_no_zero',
        'activo' => 'permit_empty|in_list[0,1]',
        'fecha_apertura' => 'permit_empty|valid_date[Y-m-d]',
        'fecha_ultimo_corte' => 'permit_empty|valid_date[Y-m-d]'
    ];

    protected $validationMessages = [
        'descripcion' => [
            'required' => 'La descripción de la cuenta es obligatoria',
            'max_length' => 'La descripción no puede exceder 255 caracteres'
        ],
        'banco' => [
            'required' => 'El nombre del banco es obligatorio',
            'max_length' => 'El nombre del banco no puede exceder 100 caracteres'
        ],
        'numero_cuenta' => [
            'required' => 'El número de cuenta es obligatorio',
            'max_length' => 'El número de cuenta no puede exceder 20 caracteres'
        ],
        'clabe' => [
            'exact_length' => 'La CLABE debe tener exactamente 18 dígitos',
            'numeric' => 'La CLABE solo debe contener números'
        ],
        'titular' => [
            'required' => 'El nombre del titular es obligatorio',
            'max_length' => 'El nombre del titular no puede exceder 255 caracteres'
        ],
        'moneda' => [
            'required' => 'El tipo de moneda es obligatorio',
            'in_list' => 'La moneda debe ser MXN, USD o EUR'
        ],
        'tipo_cuenta' => [
            'required' => 'El tipo de cuenta es obligatorio',
            'in_list' => 'Tipo de cuenta inválido'
        ],
        'empresa_id' => [
            'required' => 'La empresa es obligatoria',
            'is_natural_no_zero' => 'Debe seleccionar una empresa válida'
        ]
    ];

    // =====================================================================
    // MÉTODOS DE CONSULTA ESPECÍFICOS
    // =====================================================================

    /**
     * Obtener todas las cuentas activas
     */
    public function obtenerActivas(): array
    {
        return $this->where('activo', 1)
                   ->orderBy('descripcion', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener cuentas por empresa
     */
    public function obtenerPorEmpresa(int $empresaId): array
    {
        return $this->where('empresa_id', $empresaId)
                   ->where('activo', 1)
                   ->orderBy('descripcion', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener cuentas por proyecto
     */
    public function obtenerPorProyecto(int $proyectoId): array
    {
        return $this->where('proyecto_id', $proyectoId)
                   ->where('activo', 1)
                   ->orderBy('descripcion', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener cuentas con datos completos para DataTables
     */
    public function obtenerParaDataTable(): array
    {
        $builder = $this->db->table($this->table . ' cb');
        
        return $builder->select('
                cb.id,
                cb.descripcion,
                cb.banco,
                cb.numero_cuenta,
                cb.clabe,
                cb.titular,
                cb.saldo_actual,
                cb.moneda,
                cb.tipo_cuenta,
                cb.permite_depositos,
                cb.permite_retiros,
                cb.activo,
                cb.color_identificacion,
                cb.proyecto_id,
                cb.empresa_id,
                cb.fecha_ultimo_corte,
                COALESCE(p.nombre, "Sin Proyecto") as proyecto_nombre,
                COALESCE(e.nombre, "Sin Empresa") as empresa_nombre
            ')
            ->join('proyectos p', 'p.id = cb.proyecto_id', 'left')
            ->join('empresas e', 'e.id = cb.empresa_id', 'left')
            ->where('cb.deleted_at IS NULL')
            ->orderBy('cb.activo', 'DESC')
            ->orderBy('cb.descripcion', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener estadísticas generales
     */
    public function obtenerEstadisticas(): array
    {
        $builder = $this->db->table($this->table);
        
        $stats = [
            'total_cuentas' => $builder->where('deleted_at IS NULL')->countAllResults(),
            'cuentas_activas' => $builder->where('activo', 1)->where('deleted_at IS NULL')->countAllResults(),
            'cuentas_inactivas' => $builder->where('activo', 0)->where('deleted_at IS NULL')->countAllResults(),
        ];
        
        // Saldo total por moneda
        $saldos = $builder->select('moneda, SUM(saldo_actual) as total')
                         ->where('activo', 1)
                         ->where('deleted_at IS NULL')
                         ->groupBy('moneda')
                         ->get()
                         ->getResultArray();
        
        $stats['saldos_por_moneda'] = [];
        foreach ($saldos as $saldo) {
            $stats['saldos_por_moneda'][$saldo['moneda']] = $saldo['total'];
        }
        
        // Distribución por tipo de cuenta
        $tipos = $builder->select('tipo_cuenta, COUNT(*) as cantidad')
                        ->where('activo', 1)
                        ->where('deleted_at IS NULL')
                        ->groupBy('tipo_cuenta')
                        ->get()
                        ->getResultArray();
        
        $stats['por_tipo'] = [];
        foreach ($tipos as $tipo) {
            $stats['por_tipo'][$tipo['tipo_cuenta']] = $tipo['cantidad'];
        }
        
        return $stats;
    }

    /**
     * Obtener opciones para select/dropdown
     */
    public function obtenerOpcionesSelect(?int $proyectoId = null): array
    {
        $builder = $this->where('activo', 1);
        
        if ($proyectoId) {
            $builder->where('proyecto_id', $proyectoId);
        }
        
        $cuentas = $builder->orderBy('descripcion', 'ASC')->findAll();
        $opciones = [];
        
        foreach ($cuentas as $cuenta) {
            $opciones[$cuenta->id] = $cuenta->getNombreCompleto() . ' - ' . $cuenta->getSaldoFormateado();
        }
        
        return $opciones;
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function cambiarEstado(int $id, bool $activo): bool
    {
        return $this->update($id, ['activo' => $activo ? 1 : 0]);
    }

    /**
     * Actualizar saldo de una cuenta
     */
    public function actualizarSaldo(int $cuentaId, float $nuevoSaldo): bool
    {
        return $this->update($cuentaId, ['saldo_actual' => $nuevoSaldo]);
    }

    /**
     * Verificar si una cuenta bancaria ya existe para un proyecto específico
     */
    public function verificarCuentaExistente(string $numeroCuenta, ?int $proyectoId = null, ?int $excluirId = null): bool
    {
        $builder = $this->where('numero_cuenta', $numeroCuenta);
        
        if ($proyectoId) {
            $builder->where('proyecto_id', $proyectoId);
        }
        
        if ($excluirId) {
            $builder->where('id !=', $excluirId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Verificar si una CLABE ya existe para un proyecto específico
     */
    public function verificarClabeExistente(string $clabe, ?int $proyectoId = null, ?int $excluirId = null): bool
    {
        if (empty($clabe)) {
            return false;
        }
        
        $builder = $this->where('clabe', $clabe);
        
        if ($proyectoId) {
            $builder->where('proyecto_id', $proyectoId);
        }
        
        if ($excluirId) {
            $builder->where('id !=', $excluirId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Validación personalizada antes de guardar
     */
    protected function beforeInsert(array $data): array
    {
        return $this->validarCuentaUnica($data);
    }

    protected function beforeUpdate(array $data): array
    {
        return $this->validarCuentaUnica($data);
    }

    private function validarCuentaUnica(array $data): array
    {
        if (!isset($data['data'])) {
            return $data;
        }

        $datos = $data['data'];
        $id = $data['id'][0] ?? null;

        // Solo validar si hay cambios en los campos críticos
        if (isset($datos['numero_cuenta']) || isset($datos['clabe']) || isset($datos['proyecto_id'])) {
            
            $numeroCuenta = $datos['numero_cuenta'] ?? null;
            $clabe = $datos['clabe'] ?? null;
            $proyectoId = $datos['proyecto_id'] ?? null;

            // Si el proyecto_id está vacío, permitir múltiples cuentas generales
            if (!empty($proyectoId)) {
                
                // Verificar número de cuenta
                if ($numeroCuenta && $this->verificarCuentaExistente($numeroCuenta, $proyectoId, $id)) {
                    throw new \RuntimeException('Esta cuenta bancaria ya está asociada a este proyecto');
                }

                // Verificar CLABE
                if ($clabe && $this->verificarClabeExistente($clabe, $proyectoId, $id)) {
                    throw new \RuntimeException('Esta CLABE ya está asociada a este proyecto');
                }
            }
        }

        return $data;
    }

    /**
     * Obtener cuentas bancarias por empresa
     */
    public function getCuentasPorEmpresa($empresaId)
    {
        return $this->where('empresa_id', $empresaId)
                    ->where('activo', 1)
                    ->orderBy('banco', 'ASC')
                    ->orderBy('numero_cuenta', 'ASC')
                    ->findAll();
    }
}