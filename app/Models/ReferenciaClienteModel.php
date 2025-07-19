<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ReferenciaCliente;

class ReferenciaClienteModel extends Model
{
    protected $table = 'referencias_clientes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\ReferenciaCliente::class;
    protected $useSoftDeletes = false;

    // Campos permitidos para inserción/actualización
    protected $allowedFields = [
        'cliente_id', 'numero', 'nombre_completo', 'parentesco', 
        'telefono', 'tipo', 'genero', 'activo'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validaciones básicas
    protected $validationRules = [
        'cliente_id' => 'required|integer',
        'numero' => 'permit_empty|integer|greater_than[0]',
        'nombre_completo' => 'permit_empty|max_length[300]',
        'parentesco' => 'permit_empty|max_length[100]',
        'telefono' => 'permit_empty|max_length[15]',
        'tipo' => 'required|in_list[referencia_1,referencia_2,beneficiario]',
        'genero' => 'permit_empty|in_list[M,F]'
    ];

    protected $validationMessages = [
        'cliente_id' => [
            'required' => 'El ID del cliente es obligatorio',
            'integer' => 'El ID del cliente debe ser un número entero'
        ],
        'tipo' => [
            'required' => 'El tipo de referencia es obligatorio',
            'in_list' => 'El tipo debe ser: referencia_1, referencia_2 o beneficiario'
        ]
    ];

    // =====================================================================
    // MÉTODOS PRINCIPALES PARA REFERENCIAS
    // =====================================================================

    /**
     * Obtener todas las referencias de un cliente
     */
    public function obtenerReferenciasCliente(int $clienteId): array
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->orderBy('tipo', 'ASC')
                   ->orderBy('numero', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener referencias personales (referencia_1 y referencia_2)
     */
    public function obtenerReferenciasPersonales(int $clienteId): array
    {
        return $this->where('cliente_id', $clienteId)
                   ->whereIn('tipo', ['referencia_1', 'referencia_2'])
                   ->where('activo', 1)
                   ->orderBy('tipo', 'ASC')
                   ->orderBy('numero', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener beneficiarios de un cliente
     */
    public function obtenerBeneficiarios(int $clienteId): array
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('tipo', 'beneficiario')
                   ->where('activo', 1)
                   ->orderBy('numero', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener una referencia específica por tipo y número
     */
    public function obtenerReferenciaPorTipo(int $clienteId, string $tipo, int $numero = 1): ?ReferenciaCliente
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('tipo', $tipo)
                   ->where('numero', $numero)
                   ->where('activo', 1)
                   ->first();
    }

    // =====================================================================
    // MÉTODO PRINCIPAL: GUARDAR REFERENCIAS DESDE FORMULARIO
    // =====================================================================

    /**
     * Guardar múltiples referencias desde formulario
     * Este es el método principal que se llama desde el controlador
     */
    public function guardarReferenciasFormulario(int $clienteId, array $datosFormulario): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            log_message('info', "Guardando referencias para cliente {$clienteId}: " . json_encode($datosFormulario));

            // =====================================================================
            // PROCESAR REFERENCIA_1
            // =====================================================================
            
            if (!empty($datosFormulario['referencia1_nombre'])) {
                $ref1 = [
                    'nombre_completo' => $datosFormulario['referencia1_nombre'],
                    'parentesco' => $datosFormulario['referencia1_parentesco'] ?? '',
                    'telefono' => $datosFormulario['referencia1_telefono'] ?? '',
                    'genero' => $datosFormulario['referencia1_genero'] ?? ''
                ];
                
                if (!$this->guardarReferencia($clienteId, $ref1, 'referencia_1', 1)) {
                    throw new \RuntimeException('Error al guardar referencia personal #1');
                }
                
                log_message('info', "Referencia 1 guardada exitosamente para cliente {$clienteId}");
            } else {
                // Si está vacía, eliminar referencia existente
                $refExistente = $this->obtenerReferenciaPorTipo($clienteId, 'referencia_1', 1);
                if ($refExistente) {
                    $this->eliminarReferencia($refExistente->id);
                    log_message('info', "Referencia 1 eliminada para cliente {$clienteId}");
                }
            }

            // =====================================================================
            // PROCESAR REFERENCIA_2
            // =====================================================================
            
            if (!empty($datosFormulario['referencia2_nombre'])) {
                $ref2 = [
                    'nombre_completo' => $datosFormulario['referencia2_nombre'],
                    'parentesco' => $datosFormulario['referencia2_parentesco'] ?? '',
                    'telefono' => $datosFormulario['referencia2_telefono'] ?? '',
                    'genero' => $datosFormulario['referencia2_genero'] ?? ''
                ];
                
                if (!$this->guardarReferencia($clienteId, $ref2, 'referencia_2', 1)) {
                    throw new \RuntimeException('Error al guardar referencia personal #2');
                }
                
                log_message('info', "Referencia 2 guardada exitosamente para cliente {$clienteId}");
            } else {
                // Si está vacía, eliminar referencia existente
                $refExistente = $this->obtenerReferenciaPorTipo($clienteId, 'referencia_2', 1);
                if ($refExistente) {
                    $this->eliminarReferencia($refExistente->id);
                    log_message('info', "Referencia 2 eliminada para cliente {$clienteId}");
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción al guardar referencias');
            }

            log_message('info', "Referencias guardadas exitosamente para cliente {$clienteId}");
            return true;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarReferenciasFormulario: ' . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // MÉTODO AUXILIAR: GUARDAR UNA REFERENCIA INDIVIDUAL
    // =====================================================================

    /**
     * Guardar o actualizar una referencia individual
     */
    public function guardarReferencia(int $clienteId, array $datos, string $tipo, int $numero = 1): bool
    {
        try {
            // Buscar referencia existente por tipo y número
            $referenciaExistente = $this->obtenerReferenciaPorTipo($clienteId, $tipo, $numero);

            $datos['cliente_id'] = $clienteId;
            $datos['tipo'] = $tipo;
            $datos['numero'] = $numero;
            $datos['activo'] = 1;

            // Limpiar y validar datos
            $datos = $this->limpiarDatosReferencia($datos);

            // Validar si hay datos mínimos para guardar
            if (empty($datos['nombre_completo']) && empty($datos['telefono'])) {
                // Si no hay datos importantes, eliminar registro existente
                if ($referenciaExistente) {
                    return $this->eliminarReferencia($referenciaExistente->id);
                }
                return true; // No hay nada que hacer
            }

            if ($referenciaExistente) {
                // Actualizar referencia existente
                $resultado = $this->update($referenciaExistente->id, $datos);
                log_message('info', "Referencia actualizada: {$tipo} para cliente {$clienteId}");
            } else {
                // Crear nueva referencia usando la entidad
                $referenciaEntity = new ReferenciaCliente($datos);
                $resultado = $this->save($referenciaEntity);
                log_message('info', "Nueva referencia creada: {$tipo} para cliente {$clienteId}");
            }

            return $resultado;

        } catch (\Exception $e) {
            log_message('error', 'Error en guardarReferencia: ' . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // MÉTODOS DE ADMINISTRACIÓN
    // =====================================================================

    /**
     * Eliminar referencia (soft delete)
     */
    public function eliminarReferencia(int $id): bool
    {
        return $this->update($id, ['activo' => 0]);
    }

    /**
     * Contar referencias activas de un cliente
     */
    public function contarReferenciasActivas(int $clienteId): int
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->countAllResults();
    }

    /**
     * Obtener estadísticas de referencias
     */
    public function obtenerEstadisticas(): array
    {
        $stats = [];
        
        // Total de referencias activas
        $stats['total_referencias'] = $this->where('activo', 1)->countAllResults(false);
        
        // Referencias por tipo
        $stats['por_tipo'] = [];
        $tipos = ['referencia_1', 'referencia_2', 'beneficiario'];
        
        foreach ($tipos as $tipo) {
            $stats['por_tipo'][$tipo] = $this->where('tipo', $tipo)
                                             ->where('activo', 1)
                                             ->countAllResults(false);
        }
        
        // Clientes con referencias completas (2 referencias personales)
        $stats['clientes_completos'] = $this->select('cliente_id')
                                           ->where('activo', 1)
                                           ->whereIn('tipo', ['referencia_1', 'referencia_2'])
                                           ->groupBy('cliente_id')
                                           ->having('COUNT(*) = 2')
                                           ->countAllResults();

        return $stats;
    }

    /**
     * Buscar referencias por nombre
     */
    public function buscarPorNombre(string $nombre, int $limit = 20): array
    {
        return $this->like('nombre_completo', $nombre)
                   ->where('activo', 1)
                   ->limit($limit)
                   ->orderBy('nombre_completo', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener todas las referencias activas con información del cliente
     */
    public function obtenerTodasConCliente(): array
    {
        return $this->select('referencias_clientes.*, 
                             clientes.nombres, 
                             clientes.apellido_paterno, 
                             clientes.apellido_materno')
                   ->join('clientes', 'clientes.id = referencias_clientes.cliente_id')
                   ->where('referencias_clientes.activo', 1)
                   ->orderBy('clientes.nombres', 'ASC')
                   ->orderBy('referencias_clientes.tipo', 'ASC')
                   ->findAll();
    }

    // =====================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =====================================================================

    /**
     * Limpiar y formatear datos de referencia
     */
    private function limpiarDatosReferencia(array $datos): array
    {
        // Limpiar nombre completo (título case)
        if (isset($datos['nombre_completo'])) {
            $datos['nombre_completo'] = $this->toTitleCase(trim($datos['nombre_completo']));
            if (empty($datos['nombre_completo'])) {
                $datos['nombre_completo'] = null;
            }
        }

        // Limpiar parentesco (título case)
        if (isset($datos['parentesco'])) {
            $datos['parentesco'] = $this->toTitleCase(trim($datos['parentesco']));
            if (empty($datos['parentesco'])) {
                $datos['parentesco'] = null;
            }
        }

        // Limpiar teléfono (solo números)
        if (isset($datos['telefono'])) {
            $datos['telefono'] = preg_replace('/\D/', '', $datos['telefono']);
            if (empty($datos['telefono']) || strlen($datos['telefono']) < 10) {
                $datos['telefono'] = null;
            }
        }

        // Limpiar género
        if (isset($datos['genero'])) {
            $datos['genero'] = strtoupper(trim($datos['genero']));
            if (!in_array($datos['genero'], ['M', 'F'])) {
                $datos['genero'] = null;
            }
        }

        return $datos;
    }

    /**
     * Convertir texto a título case
     */
    private function toTitleCase(string $texto): string
    {
        if (empty($texto)) {
            return $texto;
        }

        // Manejar casos especiales
        $texto = trim($texto);
        
        // Convertir a minúsculas y luego capitalizar cada palabra
        $palabras = explode(' ', strtolower($texto));
        $palabrasFormateadas = [];
        
        foreach ($palabras as $palabra) {
            if (!empty($palabra)) {
                $palabrasFormateadas[] = ucfirst($palabra);
            }
        }
        
        return implode(' ', $palabrasFormateadas);
    }

    // =====================================================================
    // =====================================================================

    /**
     * Método para debugging - obtener todas las referencias de un cliente con detalles
     */
    public function debugReferenciasCliente(int $clienteId): array
    {
        $referencias = $this->where('cliente_id', $clienteId)
                           ->orderBy('tipo', 'ASC')
                           ->orderBy('numero', 'ASC')
                           ->findAll();

        $debug = [];
        foreach ($referencias as $ref) {
            $debug[] = [
                'id' => $ref->id,
                'tipo' => $ref->tipo,
                'numero' => $ref->numero,
                'nombre' => $ref->nombre_completo,
                'telefono' => $ref->telefono,
                'activo' => $ref->activo,
                'entidad_completa' => $ref
            ];
        }

        return $debug;
    }
}