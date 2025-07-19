<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\InformacionLaboral;

class InformacionLaboralModel extends Model
{
    protected $table = 'informacion_laboral_clientes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\InformacionLaboral::class;
    protected $useSoftDeletes = false;

    // Campos permitidos para inserción/actualización
    protected $allowedFields = [
        'cliente_id', 'nombre_empresa', 'puesto_cargo', 'antiguedad', 
        'telefono_trabajo', 'direccion_trabajo', 'activo'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validaciones básicas
    protected $validationRules = [
        'cliente_id' => 'required|integer',
        'nombre_empresa' => 'permit_empty|max_length[300]',
        'puesto_cargo' => 'permit_empty|max_length[200]',
        'antiguedad' => 'permit_empty|max_length[100]',
        'telefono_trabajo' => 'permit_empty|max_length[15]',
        'direccion_trabajo' => 'permit_empty',
        'activo' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'cliente_id' => [
            'required' => 'El ID del cliente es obligatorio',
            'integer' => 'El ID del cliente debe ser un número entero'
        ]
    ];

    // =====================================================================
    // MÉTODOS PRINCIPALES
    // =====================================================================

    /**
     * Obtener información laboral por cliente
     */
    public function obtenerPorCliente(int $clienteId): ?InformacionLaboral
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->first();
    }

    /**
     * Guardar o actualizar información laboral
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
            if (empty($datos['nombre_empresa']) && empty($datos['puesto_cargo'])) {
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
                $entity = new InformacionLaboral($datos);
                $resultado = $this->save($entity);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción');
            }

            return $resultado;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarInformacion (laboral): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar información laboral (soft delete)
     */
    public function eliminar(int $id): bool
    {
        return $this->update($id, ['activo' => 0]);
    }

    /**
     * Obtener todas las informaciones laborales activas
     */
    public function obtenerTodosActivos(): array
    {
        return $this->select('informacion_laboral_clientes.*, clientes.nombres, clientes.apellido_paterno')
                   ->join('clientes', 'clientes.id = informacion_laboral_clientes.cliente_id')
                   ->where('informacion_laboral_clientes.activo', 1)
                   ->orderBy('informacion_laboral_clientes.nombre_empresa', 'ASC')
                   ->findAll();
    }

    /**
     * Buscar por empresa
     */
    public function buscarPorEmpresa(string $empresa, int $limit = 20): array
    {
        return $this->like('nombre_empresa', $empresa)
                   ->where('activo', 1)
                   ->limit($limit)
                   ->orderBy('nombre_empresa', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener empresas más frecuentes
     */
    public function obtenerEmpresasFrecuentes(int $limit = 10): array
    {
        return $this->select('nombre_empresa, COUNT(*) as total')
                   ->where('activo', 1)
                   ->where('nombre_empresa IS NOT NULL')
                   ->where('nombre_empresa !=', '')
                   ->groupBy('nombre_empresa')
                   ->orderBy('total', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtener estadísticas laborales
     */
    public function obtenerEstadisticas(): array
    {
        $stats = [];
        
        // Total de registros activos
        $stats['total_registros'] = $this->where('activo', 1)->countAllResults(false);
        
        // Empresas únicas
        $stats['empresas_unicas'] = $this->select('nombre_empresa')
                                         ->where('activo', 1)
                                         ->where('nombre_empresa IS NOT NULL')
                                         ->where('nombre_empresa !=', '')
                                         ->distinct()
                                         ->countAllResults(false);
        
        // Clientes con información laboral
        $stats['clientes_con_info'] = $this->select('cliente_id')
                                           ->where('activo', 1)
                                           ->distinct()
                                           ->countAllResults();

        return $stats;
    }

    // =====================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =====================================================================

    /**
     * Limpiar y formatear datos
     */
    private function limpiarDatos(array $datos): array
    {
        // Limpiar nombre de empresa
        if (isset($datos['nombre_empresa'])) {
            $datos['nombre_empresa'] = $this->limpiarTexto($datos['nombre_empresa']);
        }

        // Limpiar puesto/cargo
        if (isset($datos['puesto_cargo'])) {
            $datos['puesto_cargo'] = $this->limpiarTexto($datos['puesto_cargo']);
        }

        // Limpiar antigüedad
        if (isset($datos['antiguedad'])) {
            $datos['antiguedad'] = trim($datos['antiguedad']);
            if (empty($datos['antiguedad'])) {
                $datos['antiguedad'] = null;
            }
        }

        // Limpiar teléfono de trabajo
        if (isset($datos['telefono_trabajo'])) {
            $datos['telefono_trabajo'] = preg_replace('/\D/', '', $datos['telefono_trabajo']);
            if (empty($datos['telefono_trabajo'])) {
                $datos['telefono_trabajo'] = null;
            }
        }

        // Limpiar dirección de trabajo
        if (isset($datos['direccion_trabajo'])) {
            $datos['direccion_trabajo'] = trim($datos['direccion_trabajo']);
            if (empty($datos['direccion_trabajo'])) {
                $datos['direccion_trabajo'] = null;
            }
        }

        return $datos;
    }

    /**
     * Limpiar texto y convertir a título case
     */
    private function limpiarTexto(string $texto): ?string
    {
        $texto = trim($texto);
        if (empty($texto)) {
            return null;
        }

        return ucwords(strtolower($texto));
    }
}