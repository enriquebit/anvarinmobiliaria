<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestión de empresas inmobiliarias
 * 
 * Propósito: Manejo CRUD de empresas con configuraciones financieras
 * Enfoque: MVP - Funcionalidad esencial sin optimizaciones
 * 
 * @author Sistema Inmobiliario ANVAR
 * @version 1.0 MVP
 */
class EmpresaModel extends Model
{
    // =====================================================
    // CONFIGURACIÓN BÁSICA DEL MODELO
    // =====================================================
    
    protected $table = 'empresas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = \App\Entities\Empresa::class;
    protected $useSoftDeletes = false; // Manejamos soft delete con campo 'activo'

    // ✅ CAMPOS PERMITIDOS PARA INSERT/UPDATE
    protected $allowedFields = [
        'nombre', 'rfc', 'razon_social', 'domicilio', 'telefono', 'email',
        'representante', 'activo'
    ];

    // ✅ TIMESTAMPS AUTOMÁTICOS
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // =====================================================
    // VALIDACIONES MVP - SOLO CAMPOS OBLIGATORIOS
    // =====================================================
    
    protected $validationRules = [
        'nombre' => 'required|min_length[2]|max_length[255]',
        'rfc' => 'required|min_length[12]|max_length[13]',
        'razon_social' => 'permit_empty|max_length[300]',
        'domicilio' => 'permit_empty',
        'telefono' => 'permit_empty|max_length[15]',
        'email' => 'permit_empty|valid_email|max_length[100]',
        'representante' => 'permit_empty|max_length[200]',
        'activo' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la empresa es obligatorio',
            'min_length' => 'El nombre debe tener al menos 2 caracteres',
            'max_length' => 'El nombre no puede exceder 255 caracteres'
        ],
        'rfc' => [
            'required' => 'El RFC es obligatorio',
            'min_length' => 'El RFC debe tener al menos 12 caracteres',
            'max_length' => 'El RFC no puede exceder 13 caracteres'
        ],
        'email' => [
            'valid_email' => 'El email debe tener un formato válido'
        ]
    ];

    // =====================================================
    // MÉTODOS PRINCIPALES CRUD
    // =====================================================

    /**
     * Obtener todas las empresas activas
     * 
     * @return array Lista de empresas activas
     */
    public function obtenerEmpresasActivas(): array
    {
        return $this->where('activo', 1)
                   ->orderBy('nombre', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener empresa por ID (solo activas)
     * 
     * @param int $id ID de la empresa
     * @return \App\Entities\Empresa|null Entidad empresa o null
     */
    public function obtenerEmpresaPorId(int $id): ?\App\Entities\Empresa
    {
        return $this->where('id', $id)
                   ->where('activo', 1)
                   ->first();
    }

    /**
     * Crear nueva empresa con datos limpios
     * 
     * @param array $datos Datos de la empresa
     * @return bool|int ID de la empresa creada o false
     */
    public function crearEmpresa(array $datos)
    {
        // ✅ Limpiar y formatear datos antes de guardar
        $datosLimpios = $this->limpiarDatosEmpresa($datos);
        $datosLimpios['activo'] = 1; // Activa por defecto

        if ($this->save($datosLimpios)) {
            return $this->getInsertID();
        }

        return false;
    }

    /**
     * Actualizar empresa existente
     * 
     * @param int $id ID de la empresa
     * @param array $datos Nuevos datos
     * @return bool Éxito de la operación
     */
    public function actualizarEmpresa(int $id, array $datos): bool
    {
        $empresa = $this->obtenerEmpresaPorId($id);
        if (!$empresa) {
            return false;
        }

        // ✅ Limpiar datos antes de actualizar
        $datosLimpios = $this->limpiarDatosEmpresa($datos);

        return $this->update($id, $datosLimpios);
    }

    /**
     * Eliminar empresa (soft delete)
     * 
     * @param int $id ID de la empresa
     * @return bool Éxito de la operación
     */
    public function eliminarEmpresa(int $id): bool
    {
        $empresa = $this->obtenerEmpresaPorId($id);
        if (!$empresa) {
            return false;
        }

        // ✅ Soft delete usando campo activo
        return $this->update($id, ['activo' => 0]);
    }

    // =====================================================
    // MÉTODOS AUXILIARES PARA CONSULTAS
    // =====================================================

    /**
     * Obtener empresas con conteo dinámico de proyectos
     * 
     * @return array Empresas con conteo de proyectos
     */
    public function obtenerEmpresasConProyectos(): array
    {
        return $this->select('empresas.*, COUNT(proyectos.id) as total_proyectos')
                   ->join('proyectos', 'proyectos.empresas_id = empresas.id', 'left')
                   ->where('empresas.activo', 1)
                   ->groupBy('empresas.id')
                   ->orderBy('empresas.nombre', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener opciones para select HTML
     * 
     * @return array Array id => nombre para selects
     */
    public function obtenerOpcionesSelect(): array
    {
        $empresas = $this->obtenerEmpresasActivas();
        $opciones = [];

        foreach ($empresas as $empresa) {
            $opciones[$empresa->id] = $empresa->nombre;
        }

        return $opciones;
    }

    /**
     * Buscar empresas por nombre
     * 
     * @param string $termino Término de búsqueda
     * @return array Empresas encontradas
     */
    public function buscarPorNombre(string $termino): array
    {
        return $this->where('activo', 1)
                   ->like('nombre', $termino)
                   ->orderBy('nombre', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener total de empresas activas
     * 
     * @return int Total de empresas
     */
    public function contarEmpresasActivas(): int
    {
        return $this->where('activo', 1)->countAllResults();
    }

    /**
     * Verificar si RFC ya existe
     * 
     * @param string $rfc RFC a verificar
     * @param int|null $excluirId ID a excluir de la búsqueda (para ediciones)
     * @return bool True si existe, false si no
     */
    public function existeRFC(string $rfc, ?int $excluirId = null): bool
    {
        $query = $this->where('rfc', strtoupper(trim($rfc)))
                     ->where('activo', 1);

        if ($excluirId !== null) {
            $query->where('id !=', $excluirId);
        }

        return $query->countAllResults() > 0;
    }

    // =====================================================
    // MÉTODOS PRIVADOS AUXILIARES
    // =====================================================

    /**
     * Limpiar y formatear datos de empresa
     * 
     * @param array $datos Datos sin procesar
     * @return array Datos limpios y formateados
     */
    private function limpiarDatosEmpresa(array $datos): array
    {
        $datosLimpios = [];

        // ✅ Limpiar campos de texto
        if (isset($datos['nombre'])) {
            $datosLimpios['nombre'] = $this->formatearTexto($datos['nombre']);
        }

        if (isset($datos['rfc'])) {
            $datosLimpios['rfc'] = strtoupper(trim($datos['rfc']));
        }

        if (isset($datos['razon_social'])) {
            $datosLimpios['razon_social'] = $this->formatearTexto($datos['razon_social']);
        }

        if (isset($datos['domicilio'])) {
            $datosLimpios['domicilio'] = trim($datos['domicilio']);
        }

        if (isset($datos['representante'])) {
            $datosLimpios['representante'] = $this->formatearTexto($datos['representante']);
        }

        if (isset($datos['email'])) {
            $datosLimpios['email'] = strtolower(trim($datos['email']));
        }

        // ✅ Limpiar teléfono (solo números)
        if (isset($datos['telefono'])) {
            $datosLimpios['telefono'] = preg_replace('/[^0-9]/', '', $datos['telefono']);
        }

        // ✅ Asegurar valores numéricos correctos
        $camposNumericos = [
            'porcentaje_anticipo', 'anticipo_fijo', 'apartado_minimo',
            'porcentaje_comision', 'comision_fija', 'apartado_comision',
            'meses_sin_intereses', 'meses_con_intereses', 'porcentaje_interes_anual',
            'dias_anticipo', 'porcentaje_cancelacion'
        ];

        foreach ($camposNumericos as $campo) {
            if (isset($datos[$campo])) {
                $datosLimpios[$campo] = is_numeric($datos[$campo]) ? $datos[$campo] : 0;
            }
        }

        // ✅ Asegurar valores ENUM correctos
        if (isset($datos['tipo_anticipo'])) {
            $datosLimpios['tipo_anticipo'] = in_array($datos['tipo_anticipo'], ['fijo', 'porcentaje']) 
                ? $datos['tipo_anticipo'] : 'porcentaje';
        }

        if (isset($datos['tipo_comision'])) {
            $datosLimpios['tipo_comision'] = in_array($datos['tipo_comision'], ['fijo', 'porcentaje']) 
                ? $datos['tipo_comision'] : 'porcentaje';
        }

        return $datosLimpios;
    }

    /**
     * Formatear texto (Primera letra mayúscula)
     * 
     * @param string $texto Texto a formatear
     * @return string Texto formateado
     */
    private function formatearTexto(string $texto): string
    {
        return ucwords(strtolower(trim($texto)));
    }
}