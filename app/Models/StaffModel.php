<?php

namespace App\Models;

use CodeIgniter\Model;

class StaffModel extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\Staff::class;
    protected $useSoftDeletes = false;

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Campos permitidos según estructura real de BD (nuevoanvar_vacio_backup-20250806_domingo.sql)
    protected $allowedFields = [
        'user_id',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'email',
        'telefono',
        'agencia',
        'tipo',
        'activo',
        'creado_por',
        'notas',
        'foto_perfil',
        'rfc',
        'debe_cambiar_password',
        'ultimo_cambio_password'
    ];

    // Validación según BD real
    protected $validationRules = [
        'user_id' => 'required|integer|is_unique[staff.user_id,id,{id}]',
        'nombres' => 'required|max_length[100]',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'telefono' => 'permit_empty|max_length[20]',
        'agencia' => 'permit_empty|max_length[100]',
        'tipo' => 'required|in_list[superadmin,admin,supervendedor,vendedor,subvendedor,visor]',
        'activo' => 'permit_empty|integer|in_list[0,1]',
        'notas' => 'permit_empty',
        'foto_perfil' => 'permit_empty|max_length[255]',
        'rfc' => 'permit_empty|max_length[13]',
        'debe_cambiar_password' => 'permit_empty|integer|in_list[0,1]',
        'ultimo_cambio_password' => 'permit_empty|valid_date',
        'creado_por' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'El ID de usuario es obligatorio',
            'integer' => 'El ID de usuario debe ser un número',
            'is_unique' => 'Este usuario ya tiene un registro de staff'
        ],
        'nombres' => [
            'required' => 'Los nombres son obligatorios',
            'max_length' => 'Los nombres no pueden exceder 100 caracteres'
        ],
        'tipo' => [
            'required' => 'El tipo de staff es obligatorio',
            'in_list' => 'Tipo de staff no válido'
        ]
    ];

    /**
     * =====================================================================
     * MÉTODOS DE CONSULTA PARA ADMINISTRACIÓN
     * =====================================================================
     */

    /**
     * Obtener staff con información del usuario y estado de Shield
     */
    public function getStaffWithUserInfo($id = null)
    {
        $builder = $this->db->table($this->table . ' s')
            ->select('
                s.*,
                u.active as user_active,
                u.created_at as user_created_at,
                ai.secret as email,
                agu.group as user_group,
                creador.nombres as creado_por_nombre
            ')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->join('auth_identities ai', 'ai.user_id = s.user_id AND ai.type = "email_password"', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = s.user_id', 'left')
            ->join('staff creador', 'creador.user_id = s.creado_por', 'left');

        if ($id !== null) {
            $builder->where('s.id', $id);
            return $builder->get()->getRow();
        }

        return $builder->get()->getResult();
    }

    /**
     * Obtener staff por user_id
     */
    public function getByUserId(int $userId): ?\App\Entities\Staff
    {
        return $this->where('user_id', $userId)->first();
    }
    
    /**
     * ¿Existe información de staff para este usuario?
     */
    public function existsForUser(int $userId): bool
    {
        return $this->where('user_id', $userId)->countAllResults() > 0;
    }

    /**
     * Obtener estadísticas básicas de staff
     */
    public function getStatsBasic(): array
    {
        // Contar por tipo usando Shield active status
        $stats = [];
        
        $query = $this->db->query("
            SELECT 
                s.tipo,
                COUNT(*) as total,
                SUM(CASE WHEN u.active = 1 THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN u.active = 0 THEN 1 ELSE 0 END) as inactivos
            FROM staff s
            JOIN users u ON u.id = s.user_id
            GROUP BY s.tipo
        ");

        $resultados = $query->getResultArray();
        
        $total = 0;
        $totalActivos = 0;
        
        foreach ($resultados as $row) {
            $stats['por_tipo'][$row['tipo']] = [
                'total' => $row['total'],
                'activos' => $row['activos'],
                'inactivos' => $row['inactivos']
            ];
            $total += $row['total'];
            $totalActivos += $row['activos'];
        }

        $stats['resumen'] = [
            'total' => $total,
            'activos' => $totalActivos,
            'inactivos' => $total - $totalActivos
        ];

        return $stats;
    }

    /**
     * Obtener staff para listado administrativo con filtros
     */
    public function getStaffForAdmin(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        $builder = $this->db->table($this->table . ' s')
            ->select('
                s.id,
                s.nombres,
                s.telefono,
                s.agencia,
                s.tipo,
                s.notas,
                s.created_at,
                u.active as user_active,
                ai.secret as email,
                creador.nombres as creado_por_nombre
            ')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->join('auth_identities ai', 'ai.user_id = s.user_id AND ai.type = "email_password"', 'left')
            ->join('staff creador', 'creador.user_id = s.creado_por', 'left');

        // Aplicar filtros
        if (!empty($filtros['buscar'])) {
            $builder->groupStart()
                ->like('s.nombres', $filtros['buscar'])
                ->orLike('ai.secret', $filtros['buscar'])
                ->orLike('s.telefono', $filtros['buscar'])
                ->groupEnd();
        }

        if (!empty($filtros['tipo'])) {
            $builder->where('s.tipo', $filtros['tipo']);
        }

        if (!empty($filtros['agencia'])) {
            $builder->where('s.agencia', $filtros['agencia']);
        }

        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $builder->where('u.active', $filtros['activo']);
        }

        // Contar total
        $total = $builder->countAllResults(false);

        // Obtener registros paginados
        $data = $builder->orderBy('s.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Verificar si puede ser eliminado
     */
    public function canBeDeleted(int $staffId): bool
    {
        // Verificar si tiene registros dependientes
        // Por ejemplo, si creó otros usuarios
        $dependientes = $this->where('creado_por', $staffId)->countAllResults();
        
        return $dependientes === 0;
    }

    /**
     * Buscar staff por término
     */
    public function searchStaff(string $termino, int $limit = 10): array
    {
        return $this->db->table($this->table . ' s')
            ->select('s.id, s.nombres, s.telefono, s.agencia, s.tipo, ai.secret as email')
            ->join('auth_identities ai', 'ai.user_id = s.user_id AND ai.type = "email_password"', 'left')
            ->groupStart()
                ->like('s.nombres', $termino)
                ->orLike('ai.secret', $termino)
                ->orLike('s.telefono', $termino)
            ->groupEnd()
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}