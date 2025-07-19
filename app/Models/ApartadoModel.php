<?php

namespace App\Models;

use CodeIgniter\Model;

class ApartadoModel extends Model
{
    protected $table            = 'apartados';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Apartado::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'folio_apartado', 'lote_id', 'cliente_id', 'user_id', 'perfil_financiamiento_id',
        'fecha_apartado', 'monto_apartado', 'monto_enganche_requerido', 'fecha_limite_enganche',
        'forma_pago', 'referencia_pago', 'comprobante_url',
        'estatus_apartado', 'fecha_cambio_estatus', 'motivo_cancelacion',
        'fecha_vencimiento', 'aplico_penalizacion', 'tipo_penalizacion_aplicada',
        'monto_penalizacion', 'monto_devuelto', 'fecha_devolucion',
        'venta_id', 'observaciones'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules      = [
        'folio_apartado' => 'required|max_length[20]|is_unique[apartados.folio_apartado,id,{id}]',
        'lote_id' => 'required|integer',
        'cliente_id' => 'required|integer',
        'user_id' => 'required|integer',
        'perfil_financiamiento_id' => 'required|integer',
        'fecha_apartado' => 'required|valid_date',
        'monto_apartado' => 'required|decimal|greater_than[0]',
        'monto_enganche_requerido' => 'required|decimal|greater_than[0]',
        'fecha_limite_enganche' => 'required|valid_date',
        'forma_pago' => 'required|in_list[efectivo,transferencia,cheque,tarjeta,deposito]'
    ];

    public function getApartadosVigentes()
    {
        return $this->where('estatus_apartado', 'vigente')
                    ->orderBy('fecha_apartado', 'DESC')
                    ->findAll();
    }

    public function getApartadosVencidosSinProcesar()
    {
        return $this->where('estatus_apartado', 'vigente')
                    ->where('fecha_limite_enganche <', date('Y-m-d'))
                    ->findAll();
    }

    public function getApartadosPorVendedor($vendedorId, $estatus = null)
    {
        $builder = $this->where('user_id', $vendedorId);
        
        if ($estatus) {
            $builder->where('estatus_apartado', $estatus);
        }
        
        return $builder->orderBy('fecha_apartado', 'DESC')->findAll();
    }

    public function lotetieneApartadoVigente($loteId)
    {
        $apartado = $this->where('lote_id', $loteId)
                         ->where('estatus_apartado', 'vigente')
                         ->first();
        
        return $apartado !== null;
    }

    public function getApartadosConRelaciones($limit = null, $offset = null)
    {
        $builder = $this->db->table('apartados a')
                            ->select('a.*, c.nombres as cliente_nombre, c.apellido_paterno, 
                                     c.apellido_materno, l.clave as lote_clave, 
                                     l.area, u.username as vendedor_nombre, cf.nombre as nombre_plan')
                            ->join('clientes c', 'c.id = a.cliente_id')
                            ->join('lotes l', 'l.id = a.lote_id')
                            ->join('users u', 'u.id = a.user_id')
                            ->join('perfiles_financiamiento cf', 'cf.id = a.perfil_financiamiento_id', 'left')
                            ->orderBy('a.fecha_apartado', 'DESC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResult();
    }

    public function actualizarEstatus($apartadoId, $nuevoEstatus, $motivo = null)
    {
        $data = [
            'estatus_apartado' => $nuevoEstatus,
            'fecha_cambio_estatus' => date('Y-m-d H:i:s')
        ];
        
        if ($motivo) {
            $data['motivo_cancelacion'] = $motivo;
        }
        
        return $this->update($apartadoId, $data);
    }

    public function convertirEnVenta($apartadoId, $ventaId)
    {
        return $this->update($apartadoId, [
            'venta_id' => $ventaId,
            'estatus_apartado' => 'completado',
            'fecha_cambio_estatus' => date('Y-m-d H:i:s')
        ]);
    }
}