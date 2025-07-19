<?php

namespace App\Models;

use CodeIgniter\Model;

// =====================================================================
// MODELO ESTADO CIVIL
// =====================================================================
class EstadoCivilModel extends Model
{
    protected $table = 'estados_civiles';
    protected $primaryKey = 'id';
    protected $returnType = \App\Entities\EstadoCivil::class;
    protected $allowedFields = ['nombre', 'valor', 'activo'];
    protected $useTimestamps = true;

    public function obtenerTodosActivos(): array
    {
        return $this->where('activo', 1)
                   ->orderBy('id', 'ASC')
                   ->findAll();
    }

    public function obtenerOpcionesSelect(): array
    {
        $estados = $this->obtenerTodosActivos();
        $opciones = [];
        
        foreach ($estados as $estado) {
            $opciones[$estado->valor] = $estado->nombre;
        }
        
        return $opciones;
    }
}

// =====================================================================
// MODELO FUENTE INFORMACIÓN
// =====================================================================
class FuenteInformacionModel extends Model
{
    protected $table = 'fuentes_informacion';
    protected $primaryKey = 'id';
    protected $returnType = \App\Entities\FuenteInformacion::class;
    protected $allowedFields = ['nombre', 'valor', 'activo'];
    protected $useTimestamps = true;

    public function obtenerTodosActivos(): array
    {
        return $this->where('activo', 1)
                   ->orderBy('id', 'ASC')
                   ->findAll();
    }

    public function obtenerOpcionesSelect(): array
    {
        $fuentes = $this->obtenerTodosActivos();
        $opciones = [];
        
        foreach ($fuentes as $fuente) {
            $opciones[$fuente->valor] = $fuente->nombre;
        }
        
        return $opciones;
    }
}

// =====================================================================
// MODELO INFORMACIÓN CÓNYUGE
// =====================================================================
class InformacionConyugeModel extends Model
{
    protected $table = 'informacion_conyuge_clientes';
    protected $primaryKey = 'id';
    protected $returnType = \App\Entities\InformacionConyuge::class;
    protected $allowedFields = [
        'cliente_id', 'nombre_completo', 'profesion', 'email', 'telefono', 'activo'
    ];
    protected $useTimestamps = true;

    public function obtenerPorCliente(int $clienteId): ?\App\Entities\InformacionConyuge
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->first();
    }

    public function guardarInformacion(int $clienteId, array $datos): bool
    {
        $existente = $this->obtenerPorCliente($clienteId);
        
        $datos['cliente_id'] = $clienteId;
        $datos['activo'] = 1;
        
        // Limpiar datos
        if (isset($datos['telefono'])) {
            $datos['telefono'] = preg_replace('/\D/', '', $datos['telefono']);
        }
        if (isset($datos['email'])) {
            $datos['email'] = strtolower(trim($datos['email']));
        }
        if (isset($datos['nombre_completo'])) {
            $datos['nombre_completo'] = ucwords(strtolower(trim($datos['nombre_completo'])));
        }

        if ($existente) {
            return $this->update($existente->id, $datos);
        } else {
            $entity = new \App\Entities\InformacionConyuge($datos);
            return $this->save($entity);
        }
    }
}

// =====================================================================
// MODELO INFORMACIÓN LABORAL
// =====================================================================
class InformacionLaboralModel extends Model
{
    protected $table = 'informacion_laboral_clientes';
    protected $primaryKey = 'id';
    protected $returnType = \App\Entities\InformacionLaboral::class;
    protected $allowedFields = [
        'cliente_id', 'nombre_empresa', 'puesto_cargo', 'antiguedad', 
        'telefono_trabajo', 'direccion_trabajo', 'activo'
    ];
    protected $useTimestamps = true;

    public function obtenerPorCliente(int $clienteId): ?\App\Entities\InformacionLaboral
    {
        return $this->where('cliente_id', $clienteId)
                   ->where('activo', 1)
                   ->first();
    }

    public function guardarInformacion(int $clienteId, array $datos): bool
    {
        $existente = $this->obtenerPorCliente($clienteId);
        
        $datos['cliente_id'] = $clienteId;
        $datos['activo'] = 1;
        
        // Limpiar datos
        if (isset($datos['telefono_trabajo'])) {
            $datos['telefono_trabajo'] = preg_replace('/\D/', '', $datos['telefono_trabajo']);
        }
        if (isset($datos['nombre_empresa'])) {
            $datos['nombre_empresa'] = ucwords(strtolower(trim($datos['nombre_empresa'])));
        }
        if (isset($datos['puesto_cargo'])) {
            $datos['puesto_cargo'] = ucwords(strtolower(trim($datos['puesto_cargo'])));
        }

        if ($existente) {
            return $this->update($existente->id, $datos);
        } else {
            $entity = new \App\Entities\InformacionLaboral($datos);
            return $this->save($entity);
        }
    }
}