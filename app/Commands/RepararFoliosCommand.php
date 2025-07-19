<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\RegistroClientesModel;

class RepararFoliosCommand extends BaseCommand
{
    /**
     * Grupo del comando
     *
     * @var string
     */
    protected $group = 'mantenimiento';

    /**
     * Nombre del comando
     *
     * @var string
     */
    protected $name = 'folio:reparar';

    /**
     * Descripción del comando
     *
     * @var string
     */
    protected $description = 'Repara folios faltantes o incorrectos en registro_clientes';

    /**
     * Uso del comando
     *
     * @var string
     */
    protected $usage = 'folio:reparar [opciones]';

    /**
     * Argumentos del comando
     *
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * Opciones del comando
     *
     * @var array<string, string>
     */
    protected $options = [
        '--dry-run' => 'Solo mostrar que se haría sin ejecutar cambios',
        '--force'   => 'Forzar reparación sin confirmación'
    ];

    /**
     * Ejecutar el comando
     *
     * @param array<string, mixed> $params
     */
    public function run(array $params)
    {
        CLI::write('=== REPARADOR DE FOLIOS DE REGISTRO ===', 'green');
        CLI::write('Reemplaza la funcionalidad de triggers MySQL');
        CLI::newLine();

        $registroModel = new RegistroClientesModel();
        
        // Mostrar estadísticas antes
        $this->mostrarEstadisticas($registroModel);
        
        $dryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');
        $force = array_key_exists('force', $params) || CLI::getOption('force');
        
        if ($dryRun) {
            CLI::write('🔍 MODO DRY-RUN: Solo se mostrarán los cambios sin ejecutarlos', 'yellow');
            CLI::newLine();
        }
        
        // Confirmar ejecución
        if (!$dryRun && !$force) {
            $confirmar = CLI::prompt('¿Continuar con la reparación de folios?', ['y', 'n']);
            if ($confirmar !== 'y') {
                CLI::write('Operación cancelada.', 'yellow');
                return;
            }
        }
        
        CLI::write('📄 Iniciando reparación de folios...', 'blue');
        
        if ($dryRun) {
            $this->simularReparacion($registroModel);
        } else {
            $this->ejecutarReparacion($registroModel);
        }
        
        CLI::newLine();
        CLI::write('✅ Proceso completado', 'green');
    }
    
    /**
     * Mostrar estadísticas actuales
     */
    private function mostrarEstadisticas(RegistroClientesModel $model): void
    {
        $stats = $model->getEstadisticasPorAno();
        
        CLI::write('📊 ESTADÍSTICAS ACTUALES:', 'blue');
        CLI::write("   Total registros {$stats['ano']}: {$stats['total_registros']}");
        CLI::write("   Con folio válido: {$stats['con_folio']}");
        CLI::write("   Pendientes reparación: {$stats['pendientes_folio']}", 'yellow');
        CLI::newLine();
    }
    
    /**
     * Simular reparación (dry-run)
     */
    private function simularReparacion(RegistroClientesModel $model): void
    {
        helper('folio');
        
        $registrosSinFolio = $model->groupStart()
                                  ->where('folio IS NULL')
                                  ->orWhere('folio', '')
                                  ->orWhere('folio', 'REG-2025-000000')
                              ->groupEnd()
                              ->orderBy('id', 'ASC')
                              ->findAll();
        
        CLI::write("🔍 Se encontraron " . count($registrosSinFolio) . " registros para reparar:", 'yellow');
        CLI::newLine();
        
        foreach ($registrosSinFolio as $registro) {
            $id = $registro->id ?? $registro['id'];
            $email = $registro->email ?? $registro['email'] ?? 'N/A';
            $folioActual = $registro->folio ?? $registro['folio'] ?? 'NULL';
            $folioNuevo = generarFolioConId($id, 'REG');
            
            CLI::write("   ID {$id}: {$email}");
            CLI::write("     Folio actual: {$folioActual}", 'red');
            CLI::write("     Folio nuevo:  {$folioNuevo}", 'green');
            CLI::newLine();
        }
    }
    
    /**
     * Ejecutar reparación real
     */
    private function ejecutarReparacion(RegistroClientesModel $model): void
    {
        $resultado = $model->repararFolios();
        
        CLI::write("📈 RESULTADOS:", 'blue');
        CLI::write("   Registros procesados: {$resultado['total_procesados']}");
        CLI::write("   Folios reparados: {$resultado['reparados']}", 'green');
        CLI::write("   Errores: " . count($resultado['errores']), count($resultado['errores']) > 0 ? 'red' : 'green');
        
        if (!empty($resultado['errores'])) {
            CLI::newLine();
            CLI::write("❌ ERRORES ENCONTRADOS:", 'red');
            foreach ($resultado['errores'] as $error) {
                CLI::write("   • {$error}", 'red');
            }
        }
        
        CLI::newLine();
        
        // Mostrar estadísticas después
        CLI::write('📊 ESTADÍSTICAS DESPUÉS DE LA REPARACIÓN:', 'blue');
        $this->mostrarEstadisticas($model);
    }
}