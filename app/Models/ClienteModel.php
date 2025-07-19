<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\Cliente::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id', 'nombres', 'apellido_paterno', 'apellido_materno', 'genero',
        'razon_social', 'identificacion', 'numero_identificacion', 'fecha_nacimiento',
        'lugar_nacimiento', 'nacionalidad', 'profesion', 'rfc', 'curp', 'email',
        'estado_civil_id', 'estado_civil', 'leyenda_civil', 'contacto', 'empresa_id', 
        'origen_informacion_id', 'otro_origen', 'fuente_informacion', 'telefono',
        'etapa_proceso', 'fecha_primer_contacto', 'fecha_ultima_actividad',
        'asesor_asignado', 'notas_internas', 'persona_moral'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'nombres' => 'required|min_length[2]|max_length[100]',
        'apellido_paterno' => 'required|min_length[2]|max_length[50]',
        'apellido_materno' => 'required|min_length[2]|max_length[50]',
        'email' => 'required|valid_email|max_length[255]',
        'telefono' => 'required|min_length[10]|max_length[15]'
    ];

    protected $validationMessages = [
        'nombres' => ['required' => 'Los nombres son requeridos'],
        'apellido_paterno' => ['required' => 'El apellido paterno es requerido'],
        'apellido_materno' => ['required' => 'El apellido materno es requerido'],
        'email' => ['required' => 'El email es requerido', 'valid_email' => 'Email inválido'],
        'telefono' => ['required' => 'El teléfono es requerido']
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // =====================================================================
    // MÉTODOS CRUD PRINCIPALES
    // =====================================================================
public function getByUserId(int $userId): ?\App\Entities\Cliente
{
    return $this->where('user_id', $userId)->first();
}
    /**
     * Obtener cliente completo con toda la información relacionada
     */
    public function getClienteCompleto(int $clienteId): ?\App\Entities\Cliente
    {
        return $this->find($clienteId);
    }

    /**
     * Obtener clientes para el administrador con paginación
     */
    public function getClientesParaAdmin(int $limit = 20, int $offset = 0, array $filtros = []): array
    {
        $this->select('
            clientes.id,
            clientes.nombres,
            clientes.apellido_paterno,
            clientes.apellido_materno,
            clientes.email,
            clientes.telefono,
            clientes.etapa_proceso,
            users.active as activo,
            clientes.created_at,
            auth_identities.secret as email_usuario,
            empresas.nombre as empresa_nombre
        ')
        ->join('users', 'users.id = clientes.user_id', 'left')
        ->join('auth_identities', 'auth_identities.user_id = clientes.user_id AND auth_identities.type = "email_password"', 'left')
        ->join('auth_groups_users', 'auth_groups_users.user_id = clientes.user_id')
        ->where('auth_groups_users.group', 'cliente')
        ->where('users.deleted_at IS NULL')
        ->join('empresas', 'empresas.id = clientes.empresa_id', 'left');

        // Aplicar filtros de búsqueda
        if (!empty($filtros['search'])) {
            $this->groupStart()
                ->like('clientes.nombres', $filtros['search'])
                ->orLike('clientes.apellido_paterno', $filtros['search'])
                ->orLike('clientes.apellido_materno', $filtros['search'])
                ->orLike('auth_identities.secret', $filtros['search'])
                ->orLike('clientes.telefono', $filtros['search'])
                ->groupEnd();
        }

        // Filtro por etapa
        if (!empty($filtros['etapa'])) {
            $this->where('clientes.etapa_proceso', $filtros['etapa']);
        }

        // Filtro por estado activo
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $this->where('users.active', $filtros['activo']);
        }

        return $this->limit($limit, $offset)
                    ->orderBy('clientes.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Contar total de clientes para paginación
     */
    public function getTotalClientesAdmin(array $filtros = []): int
    {
        $this->join('users', 'users.id = clientes.user_id')
             ->join('auth_groups_users', 'auth_groups_users.user_id = clientes.user_id')
             ->where('auth_groups_users.group', 'cliente')
             ->where('users.deleted_at IS NULL');

        if (!empty($filtros['search'])) {
            $this->join('auth_identities', 'auth_identities.user_id = clientes.user_id AND auth_identities.type = "email_password"', 'left')
                 ->groupStart()
                 ->like('clientes.nombres', $filtros['search'])
                 ->orLike('clientes.apellido_paterno', $filtros['search'])
                 ->orLike('clientes.apellido_materno', $filtros['search'])
                 ->orLike('auth_identities.secret', $filtros['search'])
                 ->orLike('clientes.telefono', $filtros['search'])
                 ->groupEnd();
        }

        if (!empty($filtros['etapa'])) {
            $this->where('clientes.etapa_proceso', $filtros['etapa']);
        }

        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $this->join('users', 'users.id = clientes.user_id', 'left');
            $this->where('users.active', $filtros['activo']);
        }

        return $this->countAllResults();
    }


    /**
     * Buscar clientes por término
     */
    public function buscarClientes(string $termino, int $limit = 20): array
    {
        return $this->select('
                clientes.id,
                clientes.nombres,
                clientes.apellido_paterno,
                clientes.apellido_materno,
                clientes.email,
                clientes.telefono,
                auth_identities.secret as email_usuario
            ')
            ->join('auth_identities', 'auth_identities.user_id = clientes.user_id AND auth_identities.type = "email_password"', 'left')
            ->groupStart()
                ->like('nombres', $termino)
                ->orLike('apellido_paterno', $termino)
                ->orLike('apellido_materno', $termino)
                ->orLike('auth_identities.secret', $termino)
                ->orLike('telefono', $termino)
                ->orLike('rfc', $termino)
                ->orLike('curp', $termino)
            ->groupEnd()
            ->limit($limit)
            ->orderBy('nombres', 'ASC')
            ->findAll();
    }

    /**
     * Activar/Desactivar cliente (ahora usa users.active)
     */
    public function cambiarEstadoCliente(int $clienteId, bool $activo): bool
    {
        // Obtener el user_id del cliente
        $cliente = $this->find($clienteId);
        if (!$cliente) {
            return false;
        }
        
        // Actualizar el estado en la tabla users
        $userModel = new \App\Models\UserModel();
        $result = $userModel->update($cliente->user_id, [
            'active' => $activo ? 1 : 0
        ]);
        
        // El estado activo ahora se maneja solo desde users.active
        
        return $result;
    }

    /**
     * Cambiar etapa del proceso
     */
    public function cambiarEtapa(int $clienteId, string $nuevaEtapa): bool
    {
        $etapasValidas = ['interesado', 'calificado', 'documentacion', 'contrato', 'cerrado'];
        
        if (!in_array($nuevaEtapa, $etapasValidas)) {
            return false;
        }

        return $this->update($clienteId, [
            'etapa_proceso' => $nuevaEtapa,
            'fecha_ultima_actividad' => date('Y-m-d H:i:s')
        ]);
    }

    // =====================================================================
    // MÉTODOS PRIVADOS - MAPEO Y FORMATEO
    // =====================================================================

    private function mapearDatosCliente(array $data): array
    {
        return [
            'user_id' => $data['user_id'] ?? null,
            'nombres' => $this->formatearTexto($data['nombres'] ?? ''),
            'apellido_paterno' => $this->formatearTexto($data['apellido_paterno'] ?? ''),
            'apellido_materno' => $this->formatearTexto($data['apellido_materno'] ?? ''),
            'genero' => $data['genero'] ?? 'M',
            'fecha_nacimiento' => $this->formatearFecha($data['fecha_nacimiento'] ?? ''),
            'email' => strtolower(trim($data['email'] ?? '')),
            'telefono' => preg_replace('/[^0-9]/', '', $data['telefono'] ?? ''),
            'rfc' => strtoupper(trim($data['rfc'] ?? '')),
            'curp' => strtoupper(trim($data['curp'] ?? '')),
            'profesion' => $this->formatearTexto($data['profesion'] ?? ''),
            'estado_civil_id' => !empty($data['estado_civil_id']) ? (int)$data['estado_civil_id'] : null,
            'empresa_id' => !empty($data['empresa_id']) ? (int)$data['empresa_id'] : 1,
            'etapa_proceso' => $data['etapa_proceso'] ?? 'interesado',
            'fecha_primer_contacto' => date('Y-m-d H:i:s'),
            'notas_internas' => $data['notas_internas'] ?? ''
        ];
    }

    private function insertarDireccion(int $clienteId, array $data): void
    {
        if (empty($data['direccion'])) return;

        $direccionData = [
            'cliente_id' => $clienteId,
            'domicilio' => $this->formatearTexto($data['direccion']['domicilio'] ?? ''),
            'numero' => $this->formatearTexto($data['direccion']['numero'] ?? ''),
            'colonia' => $this->formatearTexto($data['direccion']['colonia'] ?? ''),
            'codigo_postal' => trim($data['direccion']['codigo_postal'] ?? ''),
            'ciudad' => $this->formatearTexto($data['direccion']['ciudad'] ?? ''),
            'estado' => $this->formatearTexto($data['direccion']['estado'] ?? ''),
            'tipo' => 'principal',
            'activo' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($direccionData['domicilio']) || !empty($direccionData['ciudad'])) {
            $this->db->table('direcciones_clientes')->insert($direccionData);
        }
    }

    private function insertarInformacionLaboral(int $clienteId, array $data): void
    {
        if (empty($data['laboral'])) return;

        $laboralData = [
            'cliente_id' => $clienteId,
            'nombre_empresa' => $this->formatearTexto($data['laboral']['empresa'] ?? ''),
            'puesto_cargo' => $this->formatearTexto($data['laboral']['puesto'] ?? ''),
            'telefono_trabajo' => preg_replace('/[^0-9]/', '', $data['laboral']['telefono'] ?? ''),
            'activo' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($laboralData['nombre_empresa'])) {
            $this->db->table('informacion_laboral_clientes')->insert($laboralData);
        }
    }

    private function insertarInformacionConyuge(int $clienteId, array $data): void
    {
        if (empty($data['conyuge']['nombre'])) return;

        $conyugeData = [
            'cliente_id' => $clienteId,
            'nombre_completo' => $this->formatearTexto($data['conyuge']['nombre']),
            'telefono' => preg_replace('/[^0-9]/', '', $data['conyuge']['telefono'] ?? ''),
            'activo' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->table('informacion_conyuge_clientes')->insert($conyugeData);
    }

    private function insertarReferencias(int $clienteId, array $data): void
    {
        if (empty($data['referencias'])) return;

        foreach ($data['referencias'] as $index => $referencia) {
            if (empty($referencia['nombre'])) continue;

            $referenciaData = [
                'cliente_id' => $clienteId,
                'numero' => $index + 1,
                'nombre_completo' => $this->formatearTexto($referencia['nombre']),
                'telefono' => preg_replace('/[^0-9]/', '', $referencia['telefono'] ?? ''),
                'parentesco' => $this->formatearTexto($referencia['parentesco'] ?? ''),
                'tipo' => 'referencia_' . ($index + 1),
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('referencias_clientes')->insert($referenciaData);
        }
    }

    private function actualizarDireccion(int $clienteId, array $data): void
    {
        // Implementar actualización de dirección
        // Por simplicidad, eliminar y recrear
        $this->db->table('direcciones_clientes')
                 ->where('cliente_id', $clienteId)
                 ->update(['activo' => 0]);
        
        $this->insertarDireccion($clienteId, $data);
    }




    private function formatearTexto(string $texto): string
    {
        return mb_strtoupper(trim($texto), 'UTF-8');
    }

    private function formatearFecha($fecha): ?string
    {
        if (empty($fecha)) return null;
        
        try {
            if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $fecha)) {
                return $fecha;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
// =====================================================================
// SOLUCIÓN 1: MODIFICAR ClienteModel.php - MÉTODO ESPECÍFICO PARA DATATABLE
// =====================================================================

/**
 * Método específico para DataTable que retorna arrays
 */
public function getClientesParaDataTable(int $limit = 20, int $offset = 0, array $filtros = []): array
{
    $builder = $this->db->table('clientes');
    
    $builder->select('
        clientes.id,
        clientes.nombres,
        clientes.apellido_paterno,
        clientes.apellido_materno,
        clientes.email,
        clientes.telefono,
        clientes.etapa_proceso,
        users.active as activo,
        clientes.created_at,
        auth_identities.secret as email_usuario,
        empresas.nombre as empresa_nombre
    ')
    ->join('users', 'users.id = clientes.user_id', 'left')
    ->join('auth_identities', 'auth_identities.user_id = clientes.user_id AND auth_identities.type = "email_password"', 'left')
    ->join('auth_groups_users', 'auth_groups_users.user_id = clientes.user_id', 'left')
    ->where('auth_groups_users.group', 'cliente')
    ->where('users.deleted_at IS NULL')
    ->join('empresas', 'empresas.id = clientes.empresa_id', 'left');

    // Aplicar filtros
    if (!empty($filtros['search'])) {
        $builder->groupStart()
                ->like('clientes.nombres', $filtros['search'])
                ->orLike('clientes.apellido_paterno', $filtros['search'])
                ->orLike('clientes.apellido_materno', $filtros['search'])
                ->orLike('auth_identities.secret', $filtros['search'])
                ->orLike('clientes.telefono', $filtros['search'])
                ->groupEnd();
    }

    if (!empty($filtros['etapa'])) {
        $builder->where('clientes.etapa_proceso', $filtros['etapa']);
    }

    if (isset($filtros['activo']) && $filtros['activo'] !== '') {
        $builder->where('users.active', $filtros['activo']);
    }

    return $builder->limit($limit, $offset)
                   ->orderBy('clientes.created_at', 'DESC')
                   ->get()
                   ->getResultArray();
}
/**
     * ============================================================================
     * MÉTODOS PARA GESTIÓN COMPLETA DE CLIENTES (FALTANTES)
     * ============================================================================
     */

    /**
     * Crear cliente completo con información relacionada
     */
    public function crearClienteCompleto(array $clienteData): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Crear el cliente principal
            $cliente = new \App\Entities\Cliente($clienteData);
            
            if (!$this->save($cliente)) {
                throw new \RuntimeException('Error al crear cliente: ' . implode(', ', $this->errors()));
            }

            $clienteId = $this->getInsertID();

            // 2. Crear información de dirección si existe
            if (!empty($clienteData['direccion']) && !empty($clienteData['direccion']['domicilio'])) {
                $direccionData = $clienteData['direccion'];
                $direccionData['cliente_id'] = $clienteId;
                $direccionData['tipo'] = 'principal';
                $direccionData['activo'] = 1;
                $direccionData['created_at'] = date('Y-m-d H:i:s');
                $direccionData['updated_at'] = date('Y-m-d H:i:s');

                $db->table('direcciones_clientes')->insert($direccionData);
            }

            // 3. Crear información laboral si existe
            if (!empty($clienteData['laboral']) && !empty($clienteData['laboral']['nombre_empresa'])) {
                $laboralData = $clienteData['laboral'];
                $laboralData['cliente_id'] = $clienteId;
                $laboralData['activo'] = 1;
                $laboralData['created_at'] = date('Y-m-d H:i:s');
                $laboralData['updated_at'] = date('Y-m-d H:i:s');

                $db->table('informacion_laboral_clientes')->insert($laboralData);
            }

            // 4. Crear información del cónyuge si existe
            if (!empty($clienteData['conyuge']) && !empty($clienteData['conyuge']['nombre_completo'])) {
                $conyugeData = $clienteData['conyuge'];
                $conyugeData['cliente_id'] = $clienteId;
                $conyugeData['activo'] = 1;
                $conyugeData['created_at'] = date('Y-m-d H:i:s');
                $conyugeData['updated_at'] = date('Y-m-d H:i:s');

                $db->table('informacion_conyuge_clientes')->insert($conyugeData);
            }

            // 5. Crear referencias si existen
            if (!empty($clienteData['referencias'])) {
                foreach ($clienteData['referencias'] as $index => $referencia) {
                    if (!empty($referencia['nombre_completo'])) {
                        $referenciaData = $referencia;
                        $referenciaData['cliente_id'] = $clienteId;
                        $referenciaData['numero'] = $index + 1;
                        $referenciaData['tipo'] = 'referencia_' . ($index + 1);
                        $referenciaData['activo'] = 1;
                        $referenciaData['created_at'] = date('Y-m-d H:i:s');
                        $referenciaData['updated_at'] = date('Y-m-d H:i:s');

                        $db->table('referencias_clientes')->insert($referenciaData);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción');
            }

            log_message('info', "Cliente completo creado - ID: {$clienteId}");
            return true;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en crearClienteCompleto(): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualizar cliente completo con información relacionada
     */
    public function actualizarClienteCompleto(int $clienteId, array $clienteData): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Actualizar el cliente principal
            $cliente = $this->find($clienteId);
            if (!$cliente) {
                throw new \RuntimeException('Cliente no encontrado');
            }

            // Separar los datos del cliente de los datos relacionados
            $soloClienteData = $clienteData;
            unset($soloClienteData['direccion'], $soloClienteData['laboral'], $soloClienteData['conyuge'], $soloClienteData['referencias']);
            
            // Actualizar campos del cliente
            foreach ($soloClienteData as $campo => $valor) {
                $cliente->$campo = $valor;
            }
            $cliente->updated_at = date('Y-m-d H:i:s');

            if (!$this->save($cliente)) {
                throw new \RuntimeException('Error al actualizar cliente: ' . implode(', ', $this->errors()));
            }

            // 2. Actualizar/crear información de dirección
            if (isset($clienteData['direccion'])) {
                $this->actualizarDireccionCliente($clienteId, $clienteData['direccion']);
            }

            // 3. Actualizar/crear información laboral
            if (isset($clienteData['laboral'])) {
                $this->actualizarInformacionLaboral($clienteId, $clienteData['laboral']);
            }

            // 4. Actualizar/crear información del cónyuge
            if (isset($clienteData['conyuge'])) {
                $this->actualizarInformacionConyuge($clienteId, $clienteData['conyuge']);
            }

            // 5. Actualizar/crear referencias
            if (isset($clienteData['referencias'])) {
                $this->actualizarReferencias($clienteId, $clienteData['referencias']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Error en la transacción');
            }

            log_message('info', "Cliente completo actualizado - ID: {$clienteId}");
            return true;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en actualizarClienteCompleto(): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ============================================================================
     * MÉTODOS PRIVADOS PARA ACTUALIZACIÓN DE INFORMACIÓN RELACIONADA
     * ============================================================================
     */

    /**
     * Actualizar dirección del cliente
     */
    private function actualizarDireccionCliente(int $clienteId, array $direccionData): void
    {
        if (empty($direccionData['domicilio'])) {
            return; // No crear dirección vacía
        }

        $db = \Config\Database::connect();
        
        // Buscar dirección existente
        $direccionExistente = $db->table('direcciones_clientes')
                                ->where('cliente_id', $clienteId)
                                ->where('tipo', 'principal')
                                ->where('activo', 1)
                                ->get()
                                ->getRowArray();

        $direccionData['cliente_id'] = $clienteId;
        $direccionData['tipo'] = 'principal';
        $direccionData['activo'] = 1;
        $direccionData['updated_at'] = date('Y-m-d H:i:s');

        if ($direccionExistente) {
            // Actualizar existente
            $db->table('direcciones_clientes')
               ->where('id', $direccionExistente['id'])
               ->update($direccionData);
        } else {
            // Crear nueva
            $direccionData['created_at'] = date('Y-m-d H:i:s');
            $db->table('direcciones_clientes')->insert($direccionData);
        }
    }

    /**
     * Actualizar información laboral del cliente
     */
    private function actualizarInformacionLaboral(int $clienteId, array $laboralData): void
    {
        if (empty($laboralData['nombre_empresa'])) {
            return; // No crear información laboral vacía
        }

        $db = \Config\Database::connect();
        
        // Buscar información laboral existente
        $laboralExistente = $db->table('informacion_laboral_clientes')
                              ->where('cliente_id', $clienteId)
                              ->where('activo', 1)
                              ->get()
                              ->getRowArray();

        $laboralData['cliente_id'] = $clienteId;
        $laboralData['activo'] = 1;
        $laboralData['updated_at'] = date('Y-m-d H:i:s');

        if ($laboralExistente) {
            // Actualizar existente
            $db->table('informacion_laboral_clientes')
               ->where('id', $laboralExistente['id'])
               ->update($laboralData);
        } else {
            // Crear nueva
            $laboralData['created_at'] = date('Y-m-d H:i:s');
            $db->table('informacion_laboral_clientes')->insert($laboralData);
        }
    }

    /**
     * Actualizar información del cónyuge
     */
    private function actualizarInformacionConyuge(int $clienteId, array $conyugeData): void
    {
        if (empty($conyugeData['nombre_completo'])) {
            return; // No crear información de cónyuge vacía
        }

        $db = \Config\Database::connect();
        
        // Buscar información del cónyuge existente
        $conyugeExistente = $db->table('informacion_conyuge_clientes')
                              ->where('cliente_id', $clienteId)
                              ->where('activo', 1)
                              ->get()
                              ->getRowArray();

        $conyugeData['cliente_id'] = $clienteId;
        $conyugeData['activo'] = 1;
        $conyugeData['updated_at'] = date('Y-m-d H:i:s');

        if ($conyugeExistente) {
            // Actualizar existente
            $db->table('informacion_conyuge_clientes')
               ->where('id', $conyugeExistente['id'])
               ->update($conyugeData);
        } else {
            // Crear nueva
            $conyugeData['created_at'] = date('Y-m-d H:i:s');
            $db->table('informacion_conyuge_clientes')->insert($conyugeData);
        }
    }

    /**
     * Actualizar referencias del cliente
     */
    private function actualizarReferencias(int $clienteId, array $referenciasData): void
    {
        $db = \Config\Database::connect();
        
        // Desactivar referencias existentes
        $db->table('referencias_clientes')
           ->where('cliente_id', $clienteId)
           ->update(['activo' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

        // Crear/actualizar nuevas referencias
        foreach ($referenciasData as $index => $referencia) {
            if (!empty($referencia['nombre_completo'])) {
                $referenciaData = $referencia;
                $referenciaData['cliente_id'] = $clienteId;
                $referenciaData['numero'] = $index + 1;
                $referenciaData['tipo'] = 'referencia_' . ($index + 1);
                $referenciaData['activo'] = 1;
                $referenciaData['created_at'] = date('Y-m-d H:i:s');
                $referenciaData['updated_at'] = date('Y-m-d H:i:s');

                $db->table('referencias_clientes')->insert($referenciaData);
            }
        }
    }

    /**
     * Obtener clientes activos (Shield v1.1 compatible)
     */
    public function getClientesActivos(): array
    {
        return $this->select('
                clientes.*,
                CONCAT(clientes.nombres, " ", clientes.apellido_paterno, " ", IFNULL(clientes.apellido_materno, "")) as nombre_completo
            ')
            ->join('users', 'users.id = clientes.user_id', 'left')
            ->where('users.active', 1)
            ->orderBy('clientes.apellido_paterno', 'ASC')
            ->orderBy('clientes.apellido_materno', 'ASC')
            ->orderBy('clientes.nombres', 'ASC')
            ->findAll();
    }
}