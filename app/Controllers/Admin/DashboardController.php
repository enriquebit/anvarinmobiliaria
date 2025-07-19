<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ClienteModel;
use App\Models\ProyectoModel;
use App\Models\LoteModel;
class DashboardController extends BaseController
{
    protected $userModel;
    protected $clienteModel;
    protected $proyectoModel;
    protected $loteModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->clienteModel = new ClienteModel();
        $this->proyectoModel = new ProyectoModel();
        $this->loteModel = new LoteModel();
    }

    public function index()
    {
        // ✅ NO NECESITAMOS VERIFICAR ROLES AQUÍ
        // El AdminFilter ya se encargó de verificar que sea admin/superadmin
        
        $data = [
            'titulo' => 'Panel Administrativo',
            'userName' => userName(),
            'userRole' => userRole(),
            'metricas' => $this->obtenerMetricas(),
            'ventasChart' => $this->obtenerDatosVentasChart(),
            'clientesRecientes' => $this->obtenerClientesRecientes(),
            'actividadReciente' => $this->obtenerActividadReciente(),
            'proyectosEstadisticas' => $this->obtenerEstadisticasProyectos(),
            'resumenFinanciero' => $this->obtenerResumenFinanciero()
        ];
        
        return view('admin/dashboard', $data);
    }

    private function obtenerMetricas(): array
    {
        $db = \Config\Database::connect();
        
        // Total de prospectos (leads) + clientes oficiales
        $totalProspectos = 0;
        try {
            // Usar tabla leads en lugar de registro_clientes
            $leadsModel = new \App\Models\RegistroLeadModel();
            $totalProspectos = $leadsModel->countAll();
        } catch (\Exception $e) {
            // Tabla leads no existe aún o hay error
            $totalProspectos = 0;
        }
        
        // Clientes oficiales con fallback
        $totalClientesOficiales = 0;
        try {
            $totalClientesOficiales = $this->clienteModel->countAll();
        } catch (\Exception $e) {
            $totalClientesOficiales = 0;
        }
        $totalContactos = $totalProspectos + $totalClientesOficiales;
        
        // Lotes disponibles con fallback (lotes activos)
        $lotesDisponibles = 0;
        try {
            $lotesDisponibles = $this->loteModel->where('activo', 1)
                                              ->countAllResults();
        } catch (\Exception $e) {
            $lotesDisponibles = 0;
        }
        
        // Proyectos activos con fallback
        $proyectosActivos = 0;
        try {
            $proyectosActivos = $this->proyectoModel->where('estatus', 'activo')
                                                  ->orWhere('estatus', null)
                                                  ->countAllResults();
        } catch (\Exception $e) {
            $proyectosActivos = 0;
        }
        
        // Temporalmente sin ingresos (tabla eliminada)
        $ingresosMes = 0;
        
        return [
            'totalContactos' => $totalContactos,
            'totalProspectos' => $totalProspectos,
            'totalClientesOficiales' => $totalClientesOficiales,
            'lotesDisponibles' => $lotesDisponibles,
            'ventasMes' => $lotesDisponibles, // Compatibilidad con vista (ahora muestra lotes disponibles)
            'proyectosActivos' => $proyectosActivos,
            'ingresosMes' => $ingresosMes
        ];
    }

    private function obtenerDatosVentasChart(): array
    {
        $anioActual = date('Y');
        $anioAnterior = $anioActual - 1;
        
        $ingresosActual = [];
        $ingresosAnterior = [];
        
        try {
            for ($mes = 1; $mes <= 12; $mes++) {
                $inicioMes = sprintf('%s-%02d-01', $anioActual, $mes);
                $finMes = sprintf('%s-%02d-%s', $anioActual, $mes, date('t', strtotime($inicioMes)));
                
                // Temporalmente sin ingresos (tabla eliminada)
                $ingresosActual[] = 0;
                
                // Ingresos año anterior con fallback
                $inicioMesAnterior = sprintf('%s-%02d-01', $anioAnterior, $mes);
                $finMesAnterior = sprintf('%s-%02d-%s', $anioAnterior, $mes, date('t', strtotime($inicioMesAnterior)));
                
                // Temporalmente sin ingresos (tabla eliminada)
                $ingresosAnterior[] = 0;
            }
        } catch (\Exception $e) {
            // Si hay error general, llenar con ceros
            $ingresosActual = array_fill(0, 12, 0);
            $ingresosAnterior = array_fill(0, 12, 0);
        }
        
        return [
            'anioActual' => $anioActual,
            'anioAnterior' => $anioAnterior,
            'ventasActual' => $ingresosActual,    // Mantenemos el nombre para compatibilidad con la vista
            'ventasAnterior' => $ingresosAnterior // Mantenemos el nombre para compatibilidad con la vista
        ];
    }

    private function obtenerClientesRecientes(): array
    {
        $recientes = [];
        
        // Obtener prospectos recientes (leads)
        $prospectos = [];
        try {
            $leadsModel = new \App\Models\RegistroLeadModel();
            $prospectos = $leadsModel->select('id, firstname, lastname, email, fecha_registro, etapa_proceso')
                                     ->orderBy('fecha_registro', 'DESC')
                                     ->limit(2)
                                     ->findAll();
        } catch (\Exception $e) {
            // Tabla leads no existe aún
            $prospectos = [];
        }
        
        foreach ($prospectos as $prospecto) {
            $recientes[] = (object)[
                'id' => $prospecto->id,
                'nombre_mostrar' => $prospecto->firstname,
                'apellido_mostrar' => $prospecto->lastname,
                'email' => $prospecto->email,
                'fecha_registro' => $prospecto->fecha_registro,
                'tipo' => 'prospecto',
                'etapa' => $prospecto->etapa_proceso
            ];
        }
        
        // Obtener clientes oficiales recientes
        $clientesOficiales = $this->clienteModel->select('id, nombres, apellido_paterno, email, created_at')
                                               ->orderBy('created_at', 'DESC')
                                               ->limit(2)
                                               ->findAll();
        
        foreach ($clientesOficiales as $cliente) {
            $recientes[] = (object)[
                'id' => $cliente->id,
                'nombre_mostrar' => $cliente->nombres,
                'apellido_mostrar' => $cliente->apellido_paterno,
                'email' => $cliente->email,
                'fecha_registro' => $cliente->created_at,
                'tipo' => 'cliente_oficial',
                'etapa' => 'cliente'
            ];
        }
        
        // Ordenar por fecha y limitar a 4
        usort($recientes, function($a, $b) {
            return strtotime($b->fecha_registro) - strtotime($a->fecha_registro);
        });
        
        return array_slice($recientes, 0, 4);
    }

    private function obtenerActividadReciente(): array
    {
        $db = \Config\Database::connect();
        
        $actividades = [];
        
        // Últimos registros de prospectos (leads)
        $prospectosRecientes = [];
        try {
            $leadsModel = new \App\Models\RegistroLeadModel();
            $prospectosRecientes = $leadsModel->select('firstname, lastname, fecha_registro, etapa_proceso')
                                              ->orderBy('fecha_registro', 'DESC')
                                              ->limit(2)
                                              ->findAll();
        } catch (\Exception $e) {
            // Tabla leads no existe aún
            $prospectosRecientes = [];
        }
        
        foreach ($prospectosRecientes as $prospecto) {
            $actividades[] = [
                'tipo' => 'prospecto_registro',
                'icono' => 'fas fa-user-plus',
                'color' => 'info',
                'titulo' => $prospecto->firstname . ' ' . $prospecto->lastname . ' se registró',
                'descripcion' => 'Nuevo prospecto en etapa: ' . ucfirst($prospecto->etapa_proceso),
                'tiempo' => $this->tiempoRelativo($prospecto->fecha_registro),
                'fecha' => $prospecto->fecha_registro
            ];
        }
        
        // Últimos clientes oficiales (clientes)
        try {
            $clientesOficiales = $this->clienteModel->select('nombres, apellido_paterno, created_at')
                                                   ->orderBy('created_at', 'DESC')
                                                   ->limit(2)
                                                   ->findAll();
            
            foreach ($clientesOficiales as $cliente) {
                $actividades[] = [
                    'tipo' => 'cliente_oficial',
                    'icono' => 'fas fa-handshake',
                    'color' => 'success',
                    'titulo' => $cliente->nombres . ' ' . $cliente->apellido_paterno . ' se convirtió en cliente',
                    'descripcion' => 'Cliente oficial registrado - listo para venta',
                    'tiempo' => $this->tiempoRelativo($cliente->created_at),
                    'fecha' => $cliente->created_at
                ];
            }
        } catch (\Exception $e) {
            // Si hay error con clientes oficiales, solo continuar
        }
        
        // Temporalmente sin ingresos (tabla eliminada)
        
        // Ordenar por fecha
        usort($actividades, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
        
        return array_slice($actividades, 0, 4);
    }

    private function obtenerEstadisticasProyectos(): array
    {
        // Obtener estadísticas básicas de lotes
        $totalLotes = $this->loteModel->countAll();
        
        // Para evitar errores si no existe la columna 'estado', usar consulta segura
        $db = \Config\Database::connect();
        
        // Consulta segura para lotes disponibles
        $lotesDisponibles = 0;
        $lotesVendidos = 0;
        
        try {
            // Usar campo 'activo' que sí existe en la tabla lotes
            $lotesDisponibles = $this->loteModel->where('activo', 1)->countAllResults();
            $lotesVendidos = 0; // Por ahora asumir 0 hasta que se implemente nueva arquitectura de ventas
        } catch (\Exception $e) {
            // Si hay error, usar valores por defecto
            $lotesDisponibles = $totalLotes;
            $lotesVendidos = 0;
        }
        
        return [
            'totalLotes' => $totalLotes,
            'lotesDisponibles' => $lotesDisponibles,
            'lotesVendidos' => $lotesVendidos,
            'porcentajeDisponible' => $totalLotes > 0 ? round(($lotesDisponibles / $totalLotes) * 100, 1) : 0,
            'porcentajeVendido' => $totalLotes > 0 ? round(($lotesVendidos / $totalLotes) * 100, 1) : 0
        ];
    }

    private function obtenerResumenFinanciero(): array
    {
        $inicioMes = date('Y-m-01');
        $finMes = date('Y-m-t');
        $inicioMesAnterior = date('Y-m-01', strtotime('-1 month'));
        $finMesAnterior = date('Y-m-t', strtotime('-1 month'));
        
        // Temporalmente sin ingresos (tabla eliminada)
        $ingresosActual = 0;
        $ingresosAnterior = 0;
        
        // Calcular porcentaje de cambio
        $cambioIngresos = 0;
        if ($ingresosAnterior > 0) {
            $cambioIngresos = round((($ingresosActual - $ingresosAnterior) / $ingresosAnterior) * 100, 1);
        }
        
        // Comisiones (estimado al 10% de ingresos)
        $comisiones = $ingresosActual * 0.10;
        
        // Gastos (estimado)
        $gastos = $ingresosActual * 0.15;
        
        // Ganancia neta
        $gananciaNeta = $ingresosActual - $comisiones - $gastos;
        
        return [
            'ingresos' => $ingresosActual,
            'comisiones' => $comisiones,
            'gastos' => $gastos,
            'gananciaNeta' => $gananciaNeta,
            'cambioIngresos' => $cambioIngresos,
            'porcentajeComisiones' => $ingresosActual > 0 ? round(($comisiones / $ingresosActual) * 100, 1) : 0
        ];
    }

    private function tiempoRelativo(string $fecha): string
    {
        $ahora = time();
        $tiempo = strtotime($fecha);
        $diferencia = $ahora - $tiempo;
        
        if ($diferencia < 60) {
            return 'hace unos segundos';
        } elseif ($diferencia < 3600) {
            $minutos = floor($diferencia / 60);
            return "hace {$minutos} minuto" . ($minutos > 1 ? 's' : '');
        } elseif ($diferencia < 86400) {
            $horas = floor($diferencia / 3600);
            return "hace {$horas} hora" . ($horas > 1 ? 's' : '');
        } elseif ($diferencia < 604800) {
            $dias = floor($diferencia / 86400);
            return "hace {$dias} día" . ($dias > 1 ? 's' : '');
        } else {
            return date('d/m/Y', $tiempo);
        }
    }
}