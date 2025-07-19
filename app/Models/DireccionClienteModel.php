<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\DireccionCliente;

class DireccionClienteModel extends Model
{
    protected $table = 'direcciones_clientes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\DireccionCliente::class;
    protected $useSoftDeletes = false;

    // Campos permitidos para inserción/actualización
    protected $allowedFields = [
        'cliente_id', 'domicilio', 'numero', 'colonia', 'codigo_postal',
        'ciudad', 'estado', 'tiempo_radicando', 'tipo_residencia',
        'residente', 'tipo', 'activo'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validaciones básicas
    protected $validationRules = [
        'cliente_id' => 'required|integer',
        'domicilio' => 'max_length[500]',
        'numero' => 'max_length[20]',
        'colonia' => 'max_length[200]',
        'codigo_postal' => 'max_length[10]',
        'ciudad' => 'max_length[150]',
        'estado' => 'max_length[100]'
    ];

    protected $validationMessages = [
        'cliente_id' => [
            'required' => 'El ID del cliente es obligatorio',
            'integer' => 'El ID del cliente debe ser un número entero'
        ]
    ];

    // =====================================================================
    // MÉTODOS BÁSICOS PARA DIRECCIONES
    // =====================================================================

    /**
     * Obtener dirección principal de un cliente
     */
    public function obtenerDireccionPrincipal(int $clienteId): ?DireccionCliente
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('tipo', 'principal')
                   ->where('activo', 1)
                   ->first();
    }

    /**
     * Obtener todas las direcciones de un cliente
     */
    public function obtenerDireccionesCliente(int $clienteId): array
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->orderBy('tipo', 'ASC')
                   ->findAll();
    }

    /**
     * Crear o actualizar dirección principal
     */
    public function guardarDireccionPrincipal(int $clienteId, array $direccionData): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Buscar dirección principal existente
            $direccionExistente = $this->obtenerDireccionPrincipal($clienteId);

            $direccionData['cliente_id'] = $clienteId;
            $direccionData['tipo'] = 'principal';
            $direccionData['activo'] = 1;

            // Limpiar datos
            $direccionData = $this->limpiarDatosDireccion($direccionData);

            if ($direccionExistente) {
                // Actualizar dirección existente
                $resultado = $this->update($direccionExistente->id, $direccionData);
            } else {
                // Crear nueva dirección
                $direccionEntity = new DireccionCliente($direccionData);
                $resultado = $this->save($direccionEntity);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción al guardar dirección');
            }

            return $resultado;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarDireccionPrincipal: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar dirección (soft delete)
     */
    public function eliminarDireccion(int $id): bool
    {
        return $this->update($id, ['activo' => 0]);
    }

    /**
     * Buscar direcciones por código postal
     */
    public function buscarPorCodigoPostal(string $cp): array
    {
        return $this->like('codigo_postal', $cp)
                   ->where('activo', 1)
                   ->groupBy('colonia, ciudad, estado')
                   ->findAll();
    }

    /**
     * Obtener direcciones con paginación
     */
    public function obtenerDireccionesPaginadas(int $clienteId, int $limit = 10, int $offset = 0): array
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->limit($limit, $offset)
                   ->orderBy('tipo', 'ASC')
                   ->orderBy('updated_at', 'DESC')
                   ->findAll();
    }

    /**
     * Contar direcciones activas de un cliente
     */
    public function contarDireccionesCliente(int $clienteId): int
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->countAllResults();
    }

    /**
     * Buscar clientes por ciudad
     */
    public function buscarClientesPorCiudad(string $ciudad): array
    {
        return $this->select('cliente_id')
                   ->where('ciudad', $ciudad)
                   ->where('activo', 1)
                   ->groupBy('cliente_id')
                   ->findAll();
    }

    /**
     * Buscar clientes por estado
     */
    public function buscarClientesPorEstado(string $estado): array
    {
        return $this->select('cliente_id')
                   ->where('estado', $estado)
                   ->where('activo', 1)
                   ->groupBy('cliente_id')
                   ->findAll();
    }

    /**
     * Obtener estadísticas de direcciones por ciudad
     */
    public function getEstadisticasPorCiudad(): array
    {
        return $this->select('ciudad, COUNT(*) as total')
                   ->where('activo', 1)
                   ->groupBy('ciudad')
                   ->orderBy('total', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener estadísticas de direcciones por estado
     */
    public function getEstadisticasPorEstado(): array
    {
        return $this->select('estado, COUNT(*) as total')
                   ->where('activo', 1)
                   ->groupBy('estado')
                   ->orderBy('total', 'DESC')
                   ->findAll();
    }

    // =====================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =====================================================================

    /**
     * Limpiar y formatear datos de dirección
     */
    private function limpiarDatosDireccion(array $datos): array
    {
        // Limpiar código postal (solo números)
        if (isset($datos['codigo_postal'])) {
            $datos['codigo_postal'] = preg_replace('/\D/', '', $datos['codigo_postal']);
            if (empty($datos['codigo_postal'])) {
                $datos['codigo_postal'] = null;
            }
        }

        // Formatear textos en mayúsculas
        $camposTexto = ['domicilio', 'colonia', 'ciudad', 'estado'];
        foreach ($camposTexto as $campo) {
            if (isset($datos[$campo]) && !empty($datos[$campo])) {
                $datos[$campo] = strtoupper(trim($datos[$campo]));
            }
        }

        // Limpiar número
        if (isset($datos['numero'])) {
            $datos['numero'] = trim($datos['numero']);
            if (empty($datos['numero'])) {
                $datos['numero'] = 'S/N';
            }
        }

        // Validar tipo de residencia
        if (isset($datos['tipo_residencia'])) {
            $tiposValidos = ['propia', 'rentada', 'familiar', 'otro'];
            if (!in_array($datos['tipo_residencia'], $tiposValidos)) {
                $datos['tipo_residencia'] = 'propia';
            }
        }

        // Formatear tiempo radicando
        if (isset($datos['tiempo_radicando'])) {
            $datos['tiempo_radicando'] = trim($datos['tiempo_radicando']);
            if (empty($datos['tiempo_radicando'])) {
                $datos['tiempo_radicando'] = null;
            }
        }

        // Validar tipo
        if (isset($datos['tipo'])) {
            $tiposValidos = ['principal', 'secundaria', 'trabajo', 'familiar'];
            if (!in_array($datos['tipo'], $tiposValidos)) {
                $datos['tipo'] = 'principal';
            }
        }

        return $datos;
    }

    /**
     * Validar dirección completa
     */
    public function validarDireccionCompleta(array $datos): array
    {
        $errores = [];

        // Validaciones obligatorias
        if (empty($datos['domicilio'])) {
            $errores[] = 'El domicilio es obligatorio';
        }

        if (empty($datos['colonia'])) {
            $errores[] = 'La colonia es obligatoria';
        }

        if (empty($datos['ciudad'])) {
            $errores[] = 'La ciudad es obligatoria';
        }

        if (empty($datos['estado'])) {
            $errores[] = 'El estado es obligatorio';
        }

        // Validar código postal
        if (isset($datos['codigo_postal'])) {
            $cp = preg_replace('/\D/', '', $datos['codigo_postal']);
            if (strlen($cp) !== 5) {
                $errores[] = 'El código postal debe tener 5 dígitos';
            }
        }

        // Validar longitud de campos
        if (isset($datos['domicilio']) && strlen($datos['domicilio']) > 500) {
            $errores[] = 'El domicilio no puede exceder 500 caracteres';
        }

        if (isset($datos['numero']) && strlen($datos['numero']) > 20) {
            $errores[] = 'El número no puede exceder 20 caracteres';
        }

        if (isset($datos['colonia']) && strlen($datos['colonia']) > 200) {
            $errores[] = 'La colonia no puede exceder 200 caracteres';
        }

        if (isset($datos['ciudad']) && strlen($datos['ciudad']) > 150) {
            $errores[] = 'La ciudad no puede exceder 150 caracteres';
        }

        if (isset($datos['estado']) && strlen($datos['estado']) > 100) {
            $errores[] = 'El estado no puede exceder 100 caracteres';
        }

        return $errores;
    }

    /**
     * Formatear dirección completa para mostrar
     */
    public function formatearDireccionCompleta(DireccionCliente $direccion): string
    {
        $direccionCompleta = '';
        
        if (!empty($direccion->domicilio)) {
            $direccionCompleta .= $direccion->domicilio;
        }
        
        if (!empty($direccion->numero)) {
            $direccionCompleta .= ' #' . $direccion->numero;
        }
        
        if (!empty($direccion->colonia)) {
            $direccionCompleta .= ', Col. ' . $direccion->colonia;
        }
        
        if (!empty($direccion->ciudad)) {
            $direccionCompleta .= ', ' . $direccion->ciudad;
        }
        
        if (!empty($direccion->estado)) {
            $direccionCompleta .= ', ' . $direccion->estado;
        }
        
        if (!empty($direccion->codigo_postal)) {
            $direccionCompleta .= ' C.P. ' . $direccion->codigo_postal;
        }
        
        return $direccionCompleta;
    }

    /**
     * Verificar si un cliente tiene dirección completa
     */
    public function clienteTieneDireccionCompleta(int $clienteId): bool
    {
        $direccion = $this->obtenerDireccionPrincipal($clienteId);
        
        if (!$direccion) {
            return false;
        }
        
        return !empty($direccion->domicilio) &&
               !empty($direccion->colonia) &&
               !empty($direccion->ciudad) &&
               !empty($direccion->estado) &&
               !empty($direccion->codigo_postal);
    }

    /**
     * Duplicar dirección para otro cliente
     */
    public function duplicarDireccion(int $direccionId, int $nuevoClienteId, string $nuevoTipo = 'principal'): bool
    {
        $direccionOriginal = $this->find($direccionId);
        
        if (!$direccionOriginal) {
            return false;
        }
        
        $nuevaDireccion = [
            'cliente_id' => $nuevoClienteId,
            'domicilio' => $direccionOriginal->domicilio,
            'numero' => $direccionOriginal->numero,
            'colonia' => $direccionOriginal->colonia,
            'codigo_postal' => $direccionOriginal->codigo_postal,
            'ciudad' => $direccionOriginal->ciudad,
            'estado' => $direccionOriginal->estado,
            'tiempo_radicando' => $direccionOriginal->tiempo_radicando,
            'tipo_residencia' => $direccionOriginal->tipo_residencia,
            'residente' => $direccionOriginal->residente,
            'tipo' => $nuevoTipo,
            'activo' => 1
        ];
        
        $entity = new DireccionCliente($nuevaDireccion);
        return $this->save($entity);
    }

    /**
     * Cambiar tipo de dirección
     */
    public function cambiarTipoDireccion(int $direccionId, string $nuevoTipo): bool
    {
        $tiposValidos = ['principal', 'secundaria', 'trabajo', 'familiar'];
        
        if (!in_array($nuevoTipo, $tiposValidos)) {
            return false;
        }
        
        return $this->update($direccionId, ['tipo' => $nuevoTipo]);
    }

    /**
     * Obtener direcciones por tipo
     */
    public function obtenerDireccionesPorTipo(int $clienteId, string $tipo): array
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('tipo', $tipo)
                   ->where('activo', 1)
                   ->findAll();
    }
}