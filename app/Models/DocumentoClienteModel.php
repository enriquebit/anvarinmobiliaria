<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentoClienteModel extends Model
{
    protected $table = 'documentos_clientes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'cliente_id',
        'tipo_documento',
        'nombre_original',
        'nombre_archivo',
        'ruta_archivo',
        'tamaño_archivo',
        'tipo_mime',
        'descripcion',
        'subido_por',
        'activo'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Obtener documentos por cliente
     */
    public function getDocumentosPorCliente($clienteId)
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener documento por tipo y cliente
     */
    public function getDocumentoPorTipo($clienteId, $tipoDocumento)
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('tipo_documento', $tipoDocumento)
                   ->where('activo', 1)
                   ->first();
    }

    /**
     * Verificar si un cliente tiene un documento específico
     */
    public function tieneDocumento($clienteId, $tipoDocumento)
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('tipo_documento', $tipoDocumento)
                   ->where('activo', 1)
                   ->countAllResults() > 0;
    }

    /**
     * Desactivar documento anterior del mismo tipo
     */
    public function desactivarDocumentosAnteriores($clienteId, $tipoDocumento)
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('tipo_documento', $tipoDocumento)
                   ->set('activo', 0)
                   ->update();
    }

    /**
     * Obtener estadísticas de documentos por cliente
     */
    public function getEstadisticasDocumentos($clienteId)
    {
        $tiposDocumentos = ['ine', 'acta_nacimiento', 'comprobante_domicilio', 'comprobante_ingresos', 'estado_cuenta', 'rfc', 'curp'];
        
        $estadisticas = [
            'total_documentos' => 0,
            'documentos_requeridos' => count($tiposDocumentos),
            'completado' => 0,
            'faltantes' => []
        ];

        $documentosExistentes = $this->where('cliente_id', $clienteId)
                                   ->where('activo', 1)
                                   ->findAll();

        $estadisticas['total_documentos'] = count($documentosExistentes);
        
        $tiposExistentes = array_column($documentosExistentes, 'tipo_documento');
        $estadisticas['faltantes'] = array_diff($tiposDocumentos, $tiposExistentes);
        
        $estadisticas['completado'] = round(($estadisticas['total_documentos'] / $estadisticas['documentos_requeridos']) * 100, 2);

        return $estadisticas;
    }
    
    /**
     * Obtener documentos por cliente (alias para compatibilidad)
     */
    public function getDocumentosByCliente($clienteId)
    {
        return $this->getDocumentosPorCliente($clienteId);
    }
    
    /**
     * Obtener tipos de documento disponibles
     */
    public function getTiposDocumento()
    {
        // Por ahora retornamos un array estático, más adelante puede venir de BD
        return [
            'ine' => 'INE/IFE',
            'comprobante_domicilio' => 'Comprobante de Domicilio',
            'comprobante_ingresos' => 'Comprobante de Ingresos',
            'acta_nacimiento' => 'Acta de Nacimiento',
            'curp' => 'CURP',
            'rfc' => 'RFC',
            'estado_cuenta' => 'Estado de Cuenta Bancario',
            'otro' => 'Otro'
        ];
    }
}