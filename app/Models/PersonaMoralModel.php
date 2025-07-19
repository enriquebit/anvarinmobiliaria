<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonaMoralModel extends Model
{
    protected $table = 'personas_morales';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Campos permitidos (MVP - solo campos esenciales)
    protected $allowedFields = [
        'cliente_id',
        'razon_social',
        'rfc_empresa',
        'direccion_fiscal',
        'telefono_empresa',
        'email_empresa',
        'activo'
    ];

    // Validación MVP - básica
    protected $validationRules = [
        'cliente_id' => 'required|integer',
        'razon_social' => 'permit_empty|max_length[500]',
        'rfc_empresa' => 'permit_empty|max_length[13]',
        'direccion_fiscal' => 'permit_empty',
        'telefono_empresa' => 'permit_empty|max_length[20]',
        'email_empresa' => 'permit_empty|valid_email|max_length[255]'
    ];

    protected $validationMessages = [
        'cliente_id' => [
            'required' => 'El ID del cliente es obligatorio',
            'integer' => 'El ID del cliente debe ser un número'
        ],
        'email_empresa' => [
            'valid_email' => 'El email de la empresa debe ser válido'
        ]
    ];

    /**
     * Obtener datos de persona moral por cliente_id
     */
    public function getByClienteId(int $clienteId): ?array
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->first();
    }

    /**
     * Crear o actualizar persona moral
     */
    public function guardarPersonaMoral(int $clienteId, array $data): bool
    {
        // Verificar si ya existe
        $existente = $this->getByClienteId($clienteId);
        
        $data['cliente_id'] = $clienteId;
        $data['activo'] = 1;
        
        if ($existente) {
            // Actualizar
            return $this->update($existente['id'], $data);
        } else {
            // Crear nuevo
            return $this->save($data);
        }
    }

    /**
     * Eliminar persona moral (soft delete)
     */
    public function eliminarPersonaMoral(int $clienteId): bool
    {
        return $this->where('cliente_id', $clienteId)
                   ->set(['activo' => 0])
                   ->update();
    }

    /**
     * Verificar si cliente tiene datos de persona moral
     */
    public function tienePersonaMoral(int $clienteId): bool
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->countAllResults() > 0;
    }
}