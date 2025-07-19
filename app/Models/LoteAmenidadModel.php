<?php

namespace App\Models;

use CodeIgniter\Model;

class LoteAmenidadModel extends Model
{
    protected $table            = 'lotes_amenidades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\LoteAmenidad';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['lotes_id', 'amenidades_id', 'cantidad', 'activo', 'created_at'];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'lotes_id'      => 'required|integer|is_not_unique[lotes.id]',
        'amenidades_id' => 'required|integer|is_not_unique[amenidades.id]'
    ];

    protected $validationMessages = [
        'lotes_id' => [
            'required' => 'El lote es obligatorio',
            'integer' => 'El lote debe ser un número válido',
            'is_not_unique' => 'El lote seleccionado no existe'
        ],
        'amenidades_id' => [
            'required' => 'La amenidad es obligatoria',
            'integer' => 'La amenidad debe ser un número válido',
            'is_not_unique' => 'La amenidad seleccionada no existe'
        ]
    ];

    protected $skipValidation = false;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['validarRelacionUnica'];
    protected $beforeUpdate   = ['validarRelacionUnica'];

    /**
     * Validar que no exista la relación duplicada
     */
    protected function validarRelacionUnica(array $data): array
    {
        if (!isset($data['data']['lotes_id']) || !isset($data['data']['amenidades_id'])) {
            return $data;
        }

        $builder = $this->builder();
        $builder->where('lotes_id', $data['data']['lotes_id'])
                ->where('amenidades_id', $data['data']['amenidades_id']);

        // Si es actualización, excluir el registro actual
        if (isset($data['id'])) {
            $builder->where('id !=', $data['id']);
        }

        if ($builder->countAllResults() > 0) {
            throw new \Exception('Esta amenidad ya está asignada al lote');
        }

        return $data;
    }

    /**
     * Obtener amenidades de un lote específico
     */
    public function getAmenidadesPorLote(int $loteId): array
    {
        $builder = $this->db->table($this->table . ' la');
        return $builder->select('a.*, la.created_at as fecha_asignacion, la.cantidad')
                      ->join('amenidades a', 'a.id = la.amenidades_id')
                      ->where('la.lotes_id', $loteId)
                      ->where('la.activo', true)
                      ->where('a.activo', true)
                      ->orderBy('a.nombre', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener lotes que tienen una amenidad específica
     */
    public function getLotesPorAmenidad(int $amenidadId): array
    {
        $builder = $this->db->table($this->table . ' la');
        return $builder->select('l.*, la.created_at as fecha_asignacion')
                      ->join('lotes l', 'l.id = la.lotes_id')
                      ->where('la.amenidades_id', $amenidadId)
                      ->where('l.activo', true)
                      ->orderBy('l.numero', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Asignar múltiples amenidades a un lote
     */
    public function asignarAmenidadesALote(int $loteId, array $amenidadIds): bool
    {
        log_message('debug', "Asignando amenidades al lote $loteId: " . json_encode($amenidadIds));
        
        $this->transBegin();

        try {
            // Primero eliminar amenidades existentes
            $deleted = $this->where('lotes_id', $loteId)->delete();
            log_message('debug', "Eliminadas $deleted relaciones existentes para lote $loteId");

            // Insertar nuevas amenidades
            $inserted = 0;
            foreach ($amenidadIds as $amenidadId) {
                if (!empty($amenidadId)) {
                    $dataToInsert = [
                        'lotes_id' => $loteId,
                        'amenidades_id' => $amenidadId,
                        'cantidad' => 1,
                        'activo' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    
                    log_message('debug', "Intentando insertar: " . json_encode($dataToInsert));
                    
                    $result = $this->insert($dataToInsert);
                    if ($result) {
                        $inserted++;
                        log_message('debug', "Insertada amenidad $amenidadId para lote $loteId");
                    } else {
                        log_message('error', "Error insertando amenidad $amenidadId para lote $loteId: " . json_encode($this->errors()));
                    }
                }
            }
            
            log_message('debug', "Total insertadas: $inserted amenidades para lote $loteId");

            $this->transCommit();
            return true;

        } catch (\Exception $e) {
            $this->transRollback();
            log_message('error', "Error en asignarAmenidadesALote: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar amenidad específica de un lote
     */
    public function eliminarAmenidadDeLote(int $loteId, int $amenidadId): bool
    {
        return $this->where('lotes_id', $loteId)
                   ->where('amenidades_id', $amenidadId)
                   ->delete();
    }

    /**
     * Obtener conteo de amenidades por lote
     */
    public function getConteoAmenidadesPorLote(): array
    {
        $builder = $this->db->table($this->table . ' la');
        return $builder->select('la.lotes_id, COUNT(la.amenidades_id) as total_amenidades')
                      ->join('lotes l', 'l.id = la.lotes_id AND l.activo = 1')
                      ->groupBy('la.lotes_id')
                      ->orderBy('total_amenidades', 'DESC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Obtener amenidades más usadas
     */
    public function getAmenidadesMasUsadas(int $limite = 10): array
    {
        $builder = $this->db->table($this->table . ' la');
        return $builder->select('a.nombre, a.clase, COUNT(la.lotes_id) as uso_total')
                      ->join('amenidades a', 'a.id = la.amenidades_id')
                      ->join('lotes l', 'l.id = la.lotes_id AND l.activo = 1')
                      ->where('a.activo', true)
                      ->groupBy('la.amenidades_id')
                      ->orderBy('uso_total', 'DESC')
                      ->limit($limite)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Verificar si un lote tiene una amenidad específica
     */
    public function lotetieneAmenidad(int $loteId, int $amenidadId): bool
    {
        return $this->where('lotes_id', $loteId)
                   ->where('amenidades_id', $amenidadId)
                   ->countAllResults() > 0;
    }
}