<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\VentasService;
use App\Models\VentaModel;
use App\Models\ConfiguracionCobranzaModel;

/**
 * CancelarApartadosVencidosCommand
 * 
 * Comando para cancelar automáticamente apartados que han vencido
 * según los días de cancelación configurados por proyecto
 */
class CancelarApartadosVencidosCommand extends BaseCommand
{
    protected $group       = 'Automatizaciones';
    protected $name        = 'cobranza:cancelar-apartados';
    protected $description = 'Cancela automáticamente apartados vencidos según configuración';
    
    protected $usage = 'cobranza:cancelar-apartados [opciones]';
    
    protected $options = [
        '--dry-run'     => 'Mostrar apartados que serían cancelados sin ejecutar la acción',
        '--proyecto'    => 'ID del proyecto específico a procesar',
        '--force'       => 'Forzar cancelación incluso si ya se procesó hoy',
        '--dias-extra'  => 'Días adicionales de gracia antes de cancelar',
    ];

    protected VentasService $ventasService;
    protected VentaModel $ventaModel;
    protected ConfiguracionCobranzaModel $configuracionModel;

    public function __construct($logger = null, $commands = null)
    {
        parent::__construct($logger, $commands);
        $this->ventasService = new VentasService();
        $this->ventaModel = new VentaModel();
        $this->configuracionModel = new ConfiguracionCobranzaModel();
    }

    public function run(array $params)
    {
        CLI::write('🚀 Iniciando proceso de cancelación automática de apartados vencidos...', 'green');
        CLI::newLine();

        try {
            // Obtener opciones
            $dryRun = CLI::getOption('dry-run');
            $proyectoId = CLI::getOption('proyecto');
            $force = CLI::getOption('force');
            $diasExtra = (int) (CLI::getOption('dias-extra') ?? 0);

            if ($dryRun) {
                CLI::write('⚠️  MODO DRY RUN ACTIVADO - No se realizarán cambios', 'yellow');
                CLI::newLine();
            }

            // Verificar si ya se ejecutó hoy (a menos que sea forzado)
            if (!$force && !$dryRun && $this->yaSeEjecutoHoy()) {
                CLI::write('ℹ️  El proceso ya se ejecutó hoy. Use --force para ejecutar nuevamente.', 'yellow');
                return;
            }

            // Obtener apartados vencidos
            $apartadosVencidos = $this->obtenerApartadosVencidos($proyectoId, $diasExtra);

            if (empty($apartadosVencidos)) {
                CLI::write('✅ No se encontraron apartados vencidos para cancelar.', 'green');
                return;
            }

            CLI::write("📋 Encontrados " . count($apartadosVencidos) . " apartados vencidos:", 'blue');
            CLI::newLine();

            $cancelados = 0;
            $errores = 0;

            foreach ($apartadosVencidos as $apartado) {
                $this->mostrarDetalleApartado($apartado);

                if (!$dryRun) {
                    $resultado = $this->cancelarApartado($apartado, $diasExtra);
                    
                    if ($resultado['success']) {
                        CLI::write("  ✅ Cancelado exitosamente", 'green');
                        $cancelados++;
                    } else {
                        CLI::write("  ❌ Error: " . $resultado['message'], 'red');
                        $errores++;
                    }
                } else {
                    CLI::write("  🔍 Sería cancelado", 'yellow');
                    $cancelados++;
                }

                CLI::newLine();
            }

            // Mostrar resumen
            $this->mostrarResumen($cancelados, $errores, $dryRun);

            // Registrar ejecución
            if (!$dryRun && $cancelados > 0) {
                $this->registrarEjecucion($cancelados, $errores);
            }

        } catch (\Exception $e) {
            CLI::write('❌ Error en el proceso: ' . $e->getMessage(), 'red');
            log_message('error', 'Error en cancelación automática: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Obtener apartados vencidos según configuración
     */
    private function obtenerApartadosVencidos(?int $proyectoId = null, int $diasExtra = 0): array
    {
        $builder = $this->ventaModel->builder();
        
        $builder->select('
                ventas.*,
                clientes.nombres as cliente_nombres,
                clientes.apellido_paterno as cliente_apellido_paterno,
                lotes.clave as lote_clave,
                proyectos.nombre as proyecto_nombre,
                configuracion_cobranza.dias_cancelacion_apartado
            ')
            ->join('clientes', 'clientes.id = ventas.clientes_id', 'left')
            ->join('lotes', 'lotes.id = ventas.lotes_id', 'left')
            ->join('proyectos', 'proyectos.id = ventas.proyectos_id', 'left')
            ->join('configuracion_cobranza', 'configuracion_cobranza.proyectos_id = ventas.proyectos_id', 'left')
            ->where('ventas.estado', 'apartado')
            ->where('ventas.estatus', 1);

        // Filtrar por proyecto específico si se proporciona
        if ($proyectoId) {
            $builder->where('ventas.proyectos_id', $proyectoId);
        }

        $apartados = $builder->get()->getResult();

        // Filtrar por fecha de vencimiento
        $apartadosVencidos = [];
        $fechaActual = new \DateTime();

        foreach ($apartados as $apartado) {
            $diasCancelacion = $apartado->dias_cancelacion_apartado ?? 15; // Default 15 días
            $diasCancelacion += $diasExtra; // Agregar días extra de gracia
            
            $fechaLimite = new \DateTime($apartado->fecha_venta);
            $fechaLimite->modify("+{$diasCancelacion} days");

            if ($fechaActual > $fechaLimite) {
                $apartado->dias_vencido = $fechaActual->diff($fechaLimite)->days;
                $apartado->fecha_limite_original = $fechaLimite->format('Y-m-d');
                $apartadosVencidos[] = $apartado;
            }
        }

        return $apartadosVencidos;
    }

    /**
     * Mostrar detalle del apartado
     */
    private function mostrarDetalleApartado($apartado): void
    {
        CLI::write("  📄 Folio: {$apartado->folio}", 'white');
        CLI::write("     Cliente: {$apartado->cliente_nombres} {$apartado->cliente_apellido_paterno}", 'white');
        CLI::write("     Lote: {$apartado->lote_clave} - Proyecto: {$apartado->proyecto_nombre}", 'white');
        CLI::write("     Fecha venta: {$apartado->fecha_venta}", 'white');
        CLI::write("     Fecha límite: {$apartado->fecha_limite_original}", 'white');
        CLI::write("     Días vencido: {$apartado->dias_vencido}", 'red');
        CLI::write("     Total: $" . number_format($apartado->total, 2), 'white');
    }

    /**
     * Cancelar apartado individual
     */
    private function cancelarApartado($apartado, int $diasExtra): array
    {
        try {
            $motivo = sprintf(
                "Cancelación automática por vencimiento. Límite: %s, Días vencido: %d, Días extra aplicados: %d",
                $apartado->fecha_limite_original,
                $apartado->dias_vencido,
                $diasExtra
            );

            $resultado = $this->ventasService->cancelarVenta($apartado->id, $motivo);
            
            if ($resultado['success']) {
                log_message('info', "Apartado cancelado automáticamente: Folio {$apartado->folio}, Cliente: {$apartado->cliente_nombres} {$apartado->cliente_apellido_paterno}");
            }

            return $resultado;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Mostrar resumen de la ejecución
     */
    private function mostrarResumen(int $cancelados, int $errores, bool $dryRun): void
    {
        CLI::newLine();
        CLI::write('📊 RESUMEN DE EJECUCIÓN:', 'blue');
        CLI::write('═══════════════════════', 'blue');
        
        if ($dryRun) {
            CLI::write("🔍 Apartados que serían cancelados: {$cancelados}", 'yellow');
        } else {
            CLI::write("✅ Apartados cancelados exitosamente: {$cancelados}", 'green');
            
            if ($errores > 0) {
                CLI::write("❌ Errores encontrados: {$errores}", 'red');
            }
        }
        
        CLI::write("📅 Fecha de ejecución: " . date('Y-m-d H:i:s'), 'white');
        CLI::newLine();
    }

    /**
     * Verificar si ya se ejecutó hoy
     */
    private function yaSeEjecutoHoy(): bool
    {
        $logFile = WRITEPATH . 'logs/cancelacion_automatica.log';
        
        if (!file_exists($logFile)) {
            return false;
        }

        $fechaHoy = date('Y-m-d');
        $ultimasLineas = $this->obtenerUltimasLineas($logFile, 10);

        foreach ($ultimasLineas as $linea) {
            if (strpos($linea, $fechaHoy) !== false && strpos($linea, 'EJECUCION_COMPLETADA') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registrar ejecución en log
     */
    private function registrarEjecucion(int $cancelados, int $errores): void
    {
        $logFile = WRITEPATH . 'logs/cancelacion_automatica.log';
        $mensaje = sprintf(
            "[%s] EJECUCION_COMPLETADA - Cancelados: %d, Errores: %d\n",
            date('Y-m-d H:i:s'),
            $cancelados,
            $errores
        );

        // Crear directorio si no existe
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logFile, $mensaje, FILE_APPEND | LOCK_EX);
    }

    /**
     * Obtener últimas líneas de un archivo
     */
    private function obtenerUltimasLineas(string $archivo, int $cantidad): array
    {
        if (!file_exists($archivo)) {
            return [];
        }

        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($lineas, -$cantidad);
    }

    /**
     * Mostrar ayuda extendida
     */
    public function showHelp(): void
    {
        CLI::write('📖 COMANDO: Cancelar Apartados Vencidos', 'blue');
        CLI::write('════════════════════════════════════════', 'blue');
        CLI::newLine();
        
        CLI::write('DESCRIPCIÓN:', 'yellow');
        CLI::write('Este comando cancela automáticamente los apartados que han vencido');
        CLI::write('según los días de cancelación configurados para cada proyecto.');
        CLI::newLine();
        
        CLI::write('USO:', 'yellow');
        CLI::write('php spark cobranza:cancelar-apartados [opciones]');
        CLI::newLine();
        
        CLI::write('OPCIONES:', 'yellow');
        CLI::write('--dry-run          Mostrar apartados que serían cancelados sin ejecutar');
        CLI::write('--proyecto=ID      Procesar solo un proyecto específico');
        CLI::write('--force            Forzar ejecución aunque ya se haya ejecutado hoy');
        CLI::write('--dias-extra=N     Días adicionales de gracia antes de cancelar');
        CLI::newLine();
        
        CLI::write('EJEMPLOS:', 'yellow');
        CLI::write('php spark cobranza:cancelar-apartados --dry-run');
        CLI::write('php spark cobranza:cancelar-apartados --proyecto=1');
        CLI::write('php spark cobranza:cancelar-apartados --dias-extra=5');
        CLI::write('php spark cobranza:cancelar-apartados --force');
        CLI::newLine();
        
        CLI::write('PROGRAMACIÓN RECOMENDADA:', 'yellow');
        CLI::write('Ejecutar diariamente a las 6:00 AM mediante cron:');
        CLI::write('0 6 * * * /usr/bin/php /path/to/spark cobranza:cancelar-apartados');
        CLI::newLine();
    }
}