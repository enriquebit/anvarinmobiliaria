<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\EstadoCuentaNotificacionService;
use App\Models\TablaAmortizacionModel;

/**
 * Comando para automatizar tareas del módulo Estado de Cuenta
 * Diseñado para ejecutarse via cron jobs
 */
class EstadoCuentaCommand extends BaseCommand
{
    protected $group = 'EstadoCuenta';
    protected $name = 'estado-cuenta:procesar';
    protected $description = 'Procesa tareas automáticas del módulo Estado de Cuenta';

    protected $usage = 'estado-cuenta:procesar [opciones]';
    protected $arguments = [];
    protected $options = [
        '--task' => 'Tarea específica a ejecutar (actualizar-atrasos, notificar-vencimientos, alertas-admin, todo)',
        '--dias' => 'Días de anticipación para notificaciones (default: 7)',
        '--dry-run' => 'Ejecutar sin hacer cambios reales',
        '--verbose' => 'Mostrar información detallada',
    ];

    private EstadoCuentaNotificacionService $notificacionService;
    private TablaAmortizacionModel $tablaAmortizacionModel;

    public function run(array $params)
    {
        $this->notificacionService = new EstadoCuentaNotificacionService();
        $this->tablaAmortizacionModel = new TablaAmortizacionModel();

        $task = CLI::getOption('task') ?? 'todo';
        $dias = (int)(CLI::getOption('dias') ?? 7);
        $dryRun = CLI::getOption('dry-run') !== null;
        $verbose = CLI::getOption('verbose') !== null;

        if ($verbose) {
            CLI::write('Estado de Cuenta - Procesamiento Automático', 'green');
            CLI::write('================================================', 'yellow');
            CLI::write('Tarea: ' . $task);
            CLI::write('Días anticipación: ' . $dias);
            CLI::write('Modo: ' . ($dryRun ? 'DRY RUN' : 'EJECUCIÓN REAL'));
            CLI::write('');
        }

        try {
            switch ($task) {
                case 'actualizar-atrasos':
                    $this->actualizarAtrasos($dryRun, $verbose);
                    break;
                    
                case 'notificar-vencimientos':
                    $this->notificarVencimientos($dias, $dryRun, $verbose);
                    break;
                    
                case 'alertas-admin':
                    $this->enviarAlertasAdmin($dryRun, $verbose);
                    break;
                    
                case 'todo':
                    $this->ejecutarTodasLasTareas($dias, $dryRun, $verbose);
                    break;
                    
                default:
                    CLI::error('Tarea no reconocida: ' . $task);
                    CLI::write('Tareas disponibles: actualizar-atrasos, notificar-vencimientos, alertas-admin, todo');
                    return;
            }

            CLI::write('✅ Procesamiento completado exitosamente', 'green');

        } catch (\Exception $e) {
            CLI::error('❌ Error durante el procesamiento: ' . $e->getMessage());
            log_message('error', 'Error en EstadoCuentaCommand: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar estados de atraso en las mensualidades
     */
    private function actualizarAtrasos(bool $dryRun, bool $verbose): void
    {
        if ($verbose) {
            CLI::write('🔄 Actualizando estados de atraso...', 'blue');
        }

        if ($dryRun) {
            CLI::write('⚠️ DRY RUN: Simulando actualización de atrasos', 'yellow');
            
            // Mostrar qué se actualizaría
            $mensualidadesVencidas = $this->tablaAmortizacionModel
                ->where('estatus', 'pendiente')
                ->where('fecha_vencimiento <', date('Y-m-d'))
                ->findAll();

            CLI::write('Se actualizarían ' . count($mensualidadesVencidas) . ' mensualidades vencidas');
            
            if ($verbose && count($mensualidadesVencidas) > 0) {
                CLI::write('Detalle:');
                foreach (array_slice($mensualidadesVencidas, 0, 5) as $mensualidad) {
                    $diasAtraso = (time() - strtotime($mensualidad->fecha_vencimiento)) / (60 * 60 * 24);
                    CLI::write("  - Mensualidad #{$mensualidad->numero_pago} - {$diasAtraso} días de atraso");
                }
                if (count($mensualidadesVencidas) > 5) {
                    CLI::write('  ... y ' . (count($mensualidadesVencidas) - 5) . ' más');
                }
            }
            return;
        }

        // Ejecutar actualización real usando Model
        $resultado = $this->tablaAmortizacionModel->actualizarAtrasos();

        if ($verbose) {
            CLI::write("✅ Actualizadas {$resultado['actualizadas']} mensualidades");
            CLI::write("💰 Total mora generada: $" . number_format($resultado['total_mora'], 2));
        }

        // Log del resultado
        log_message('info', 'Atrasos actualizados: ' . json_encode($resultado));
    }

    /**
     * Enviar notificaciones de vencimientos próximos
     */
    private function notificarVencimientos(int $dias, bool $dryRun, bool $verbose): void
    {
        if ($verbose) {
            CLI::write("📧 Enviando notificaciones de vencimientos (próximos {$dias} días)...", 'blue');
        }

        if ($dryRun) {
            CLI::write('⚠️ DRY RUN: Simulando envío de notificaciones', 'yellow');
            
            // Mostrar qué notificaciones se enviarían
            $alertas = generar_alertas_vencimiento_global($dias);
            CLI::write('Se enviarían notificaciones a ' . count($alertas) . ' clientes');
            
            if ($verbose && count($alertas) > 0) {
                CLI::write('Detalle por cliente:');
                foreach (array_slice($alertas, 0, 3, true) as $clienteId => $mensualidades) {
                    CLI::write("  - Cliente ID {$clienteId}: " . count($mensualidades) . ' mensualidades');
                }
                if (count($alertas) > 3) {
                    CLI::write('  ... y ' . (count($alertas) - 3) . ' clientes más');
                }
            }
            return;
        }

        // Envío real de notificaciones
        $resultado = $this->notificacionService->enviarRecordatoriosVencimiento($dias);

        if ($resultado['success']) {
            if ($verbose) {
                CLI::write("✅ Enviados: {$resultado['resumen']['enviados']} emails");
                CLI::write("❌ Errores: {$resultado['resumen']['errores']} emails");
            }
        } else {
            CLI::error('Error enviando notificaciones: ' . $resultado['error']);
        }

        // Log del resultado
        log_message('info', 'Notificaciones vencimientos enviadas: ' . json_encode($resultado['resumen'] ?? []));
    }

    /**
     * Enviar alertas al equipo administrativo
     */
    private function enviarAlertasAdmin(bool $dryRun, bool $verbose): void
    {
        if ($verbose) {
            CLI::write('🚨 Enviando alertas administrativas...', 'blue');
        }

        if ($dryRun) {
            CLI::write('⚠️ DRY RUN: Simulando envío de alertas administrativas', 'yellow');
            
            // Mostrar qué alertas se enviarían
            $mensualidadesCriticas = $this->tablaAmortizacionModel->getMensualidadesCriticas();
            CLI::write('Se enviarían alertas por ' . count($mensualidadesCriticas) . ' mensualidades críticas');
            return;
        }

        // Envío real de alertas
        $resultado = $this->notificacionService->enviarAlertasPagosVencidos();

        if ($resultado['success']) {
            if ($verbose) {
                CLI::write("✅ Alertas enviadas a {$resultado['enviados']} administradores");
            }
        } else {
            CLI::error('Error enviando alertas admin: ' . $resultado['error']);
        }

        // Log del resultado
        log_message('info', 'Alertas admin enviadas: ' . json_encode($resultado));
    }

    /**
     * Ejecutar todas las tareas en secuencia
     */
    private function ejecutarTodasLasTareas(int $dias, bool $dryRun, bool $verbose): void
    {
        if ($verbose) {
            CLI::write('🔄 Ejecutando todas las tareas automáticas...', 'green');
            CLI::write('');
        }

        // 1. Actualizar atrasos
        $this->actualizarAtrasos($dryRun, $verbose);
        
        if ($verbose) CLI::write('');

        // 2. Notificar vencimientos
        $this->notificarVencimientos($dias, $dryRun, $verbose);
        
        if ($verbose) CLI::write('');

        // 3. Alertas administrativas
        $this->enviarAlertasAdmin($dryRun, $verbose);

        if ($verbose) {
            CLI::write('');
            CLI::write('🎉 Todas las tareas completadas', 'green');
        }
    }

    /**
     * Mostrar estadísticas del sistema
     */
    public function estadisticas(array $params)
    {
        CLI::write('📊 Estadísticas del Sistema Estado de Cuenta', 'green');
        CLI::write('============================================', 'yellow');

        try {
            // Mensualidades por estado
            $stats = $this->tablaAmortizacionModel
                ->select('estatus, COUNT(*) as total, SUM(monto_total) as monto_total')
                ->groupBy('estatus')
                ->findAll();

            CLI::write('📈 Mensualidades por Estado:');
            foreach ($stats as $stat) {
                CLI::write("  {$stat->estatus}: {$stat->total} mensualidades - $" . number_format($stat->monto_total, 2));
            }

            CLI::write('');

            // Mensualidades vencidas por días de atraso
            $vencidas = $this->tablaAmortizacionModel
                ->where('estatus', 'vencida')
                ->where('dias_atraso >', 0)
                ->findAll();

            if (!empty($vencidas)) {
                $rangos = ['1-7' => 0, '8-30' => 0, '31-60' => 0, '60+' => 0];
                
                foreach ($vencidas as $vencida) {
                    $dias = $vencida->dias_atraso;
                    if ($dias <= 7) $rangos['1-7']++;
                    elseif ($dias <= 30) $rangos['8-30']++;
                    elseif ($dias <= 60) $rangos['31-60']++;
                    else $rangos['60+']++;
                }

                CLI::write('⚠️ Mensualidades Vencidas por Días de Atraso:');
                foreach ($rangos as $rango => $cantidad) {
                    CLI::write("  {$rango} días: {$cantidad} mensualidades");
                }
            }

            CLI::write('');

            // Próximos vencimientos
            $proximos = $this->tablaAmortizacionModel
                ->where('estatus', 'pendiente')
                ->where('fecha_vencimiento >=', date('Y-m-d'))
                ->where('fecha_vencimiento <=', date('Y-m-d', strtotime('+7 days')))
                ->countAllResults();

            CLI::write("🔔 Próximos Vencimientos (7 días): {$proximos} mensualidades");

        } catch (\Exception $e) {
            CLI::error('Error obteniendo estadísticas: ' . $e->getMessage());
        }
    }
}

// Comando adicional para estadísticas
class EstadoCuentaStatsCommand extends BaseCommand
{
    protected $group = 'EstadoCuenta';
    protected $name = 'estado-cuenta:stats';
    protected $description = 'Muestra estadísticas del módulo Estado de Cuenta';

    public function run(array $params)
    {
        $mainCommand = new EstadoCuentaCommand();
        $mainCommand->estadisticas($params);
    }
}