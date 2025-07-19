<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\InformacionConyuge;

class InformacionConyugeModel extends Model
{
    protected $table = 'informacion_conyuge_clientes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\InformacionConyuge::class;
    protected $useSoftDeletes = false;

    // Campos permitidos para inserción/actualización
    protected $allowedFields = [
        'cliente_id', 'nombre_completo', 'profesion', 'email', 'telefono', 'activo'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validaciones básicas
    protected $validationRules = [
        'cliente_id' => 'required|integer',
        'nombre_completo' => 'permit_empty|max_length[300]',
        'profesion' => 'permit_empty|max_length[150]',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'telefono' => 'permit_empty|max_length[15]',
        'activo' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'cliente_id' => [
            'required' => 'El ID del cliente es obligatorio',
            'integer' => 'El ID del cliente debe ser un número entero'
        ],
        'email' => [
            'valid_email' => 'Debe ingresar un email válido',
            'max_length' => 'El email no puede exceder 255 caracteres'
        ]
    ];

    // =====================================================================
    // MÉTODOS PRINCIPALES
    // =====================================================================

    /**
     * Obtener información del cónyuge por cliente
     */
    public function obtenerPorCliente(int $clienteId): ?InformacionConyuge
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->first();
    }

    /**
     * Guardar o actualizar información del cónyuge
     */
    public function guardarInformacion(int $clienteId, array $datos): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Buscar información existente
            $existente = $this->obtenerPorCliente($clienteId);

            $datos['cliente_id'] = $clienteId;
            $datos['activo'] = 1;

            // Limpiar y validar datos
            $datos = $this->limpiarDatos($datos);

            // Validar si hay datos mínimos
            if (empty($datos['nombre_completo']) && empty($datos['telefono']) && empty($datos['email'])) {
                // Si no hay datos importantes, eliminar registro existente
                if ($existente) {
                    $this->eliminar($existente->id);
                }
                
                $db->transComplete();
                return true;
            }

            if ($existente) {
                // Actualizar existente
                $resultado = $this->update($existente->id, $datos);
            } else {
                // Crear nuevo
                $entity = new InformacionConyuge($datos);
                $resultado = $this->save($entity);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción');
            }

            return $resultado;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarInformacion (cónyuge): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar información del cónyuge (soft delete)
     */
    public function eliminar(int $id): bool
    {
        return $this->update($id, ['activo' => 0]);
    }

    /**
     * Obtener todos los cónyuges activos
     */
    public function obtenerTodosActivos(): array
    {
        return $this->select('informacion_conyuge_clientes.*, clientes.nombres, clientes.apellido_paterno')
                   ->join('clientes', 'clientes.id = informacion_conyuge_clientes.cliente_id')
                   ->where('informacion_conyuge_clientes.activo', 1)
                   ->orderBy('informacion_conyuge_clientes.nombre_completo', 'ASC')
                   ->findAll();
    }

    /**
     * Buscar cónyuges por nombre
     */
    public function buscarPorNombre(string $nombre, int $limit = 20): array
    {
        return $this->like('nombre_completo', $nombre)
                   ->where('activo', 1)
                   ->limit($limit)
                   ->orderBy('nombre_completo', 'ASC')
                   ->findAll();
    }

    // =====================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =====================================================================

    /**
     * Limpiar y formatear datos
     */
    private function limpiarDatos(array $datos): array
    {
        // Limpiar nombre completo
        if (isset($datos['nombre_completo'])) {
            $datos['nombre_completo'] = $this->limpiarNombre($datos['nombre_completo']);
        }

        // Limpiar profesión
        if (isset($datos['profesion'])) {
            $datos['profesion'] = $this->limpiarNombre($datos['profesion']);
        }

        // Limpiar email
        if (isset($datos['email'])) {
            $datos['email'] = strtolower(trim($datos['email']));
            if (empty($datos['email'])) {
                $datos['email'] = null;
            }
        }

        // Limpiar teléfono
        if (isset($datos['telefono'])) {
            $datos['telefono'] = preg_replace('/\D/', '', $datos['telefono']);
            if (empty($datos['telefono'])) {
                $datos['telefono'] = null;
            }
        }

        return $datos;
    }

    /**
     * Limpiar nombres y convertir a título case
     */
    private function limpiarNombre(string $texto): ?string
    {
        $texto = trim($texto);
        if (empty($texto)) {
            return null;
        }

        return ucwords(strtolower($texto));
    }
}