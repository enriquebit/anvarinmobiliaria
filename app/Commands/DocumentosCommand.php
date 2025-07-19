<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\DocumentoAutoService;
use App\Models\DocumentoGeneradoModel;

/**
 * Comando para gestión de documentos automáticos
 * Sustituye funciones y procedimientos de MySQL
 */
class DocumentosCommand extends BaseCommand
{
    protected $group       = 'Documentos';
    protected $name        = 'documentos:mantenimiento';
    protected $description = 'Ejecuta mantenimiento de documentos automáticos';

    protected $usage = 'documentos:mantenimiento [opciones]';
    protected $arguments = [];
    protected $options = [
        '--limpiar' => 'Limpiar documentos antiguos',
        '--dias'    => 'Días de antigüedad (default: 90)',
        '--generar' => 'Generar documentos pendientes'
    ];

    public function run(array $params)
    {
        CLI::write('=== Mantenimiento de Documentos Automáticos ===', 'green');

        $limpiar = CLI::getOption('limpiar');
        $generar = CLI::getOption('generar');
        $dias = CLI::getOption('dias') ?? 90;

        if ($limpiar) {
            $this->limpiarDocumentosAntiguos($dias);
        }

        if ($generar) {
            $this->generarDocumentosPendientes();
        }

        if (!$limpiar && !$generar) {
            CLI::write('Opciones disponibles:', 'yellow');
            CLI::write('  --limpiar     Limpiar documentos antiguos');
            CLI::write('  --generar     Generar documentos pendientes');
            CLI::write('  --dias=90     Especificar días de antigüedad');
        }
    }

    /**
     * Limpiar documentos antiguos (sustituye procedimiento MySQL)
     */
    private function limpiarDocumentosAntiguos(int $dias): void
    {
        CLI::write("Limpiando documentos con más de {$dias} días...", 'cyan');

        $documentoModel = new DocumentoGeneradoModel();
        
        // Obtener documentos antiguos
        $fechaLimite = date('Y-m-d H:i:s', strtotime("-{$dias} days"));
        
        $documentosAntiguos = $documentoModel->where('fecha_generacion <', $fechaLimite)
                                           ->where('estado', 'completado')
                                           ->findAll();

        $archivados = 0;
        $errores = 0;

        foreach ($documentosAntiguos as $documento) {
            try {
                // Verificar si el archivo existe
                if (file_exists($documento['ruta_archivo'])) {
                    // Opcional: mover archivo a carpeta de archivo
                    $rutaArchivo = str_replace('/documentos_generados/', '/documentos_archivados/', $documento['ruta_archivo']);
                    $directorioArchivo = dirname($rutaArchivo);
                    
                    if (!is_dir($directorioArchivo)) {
                        mkdir($directorioArchivo, 0755, true);
                    }

                    if (rename($documento['ruta_archivo'], $rutaArchivo)) {
                        // Actualizar ruta en base de datos
                        $documentoModel->update($documento['id'], [
                            'estado' => 'archivado',
                            'ruta_archivo' => $rutaArchivo,
                            'observaciones' => 'Archivado automáticamente el ' . date('Y-m-d H:i:s')
                        ]);
                        $archivados++;
                    } else {
                        $errores++;
                        CLI::write("Error moviendo archivo: {$documento['ruta_archivo']}", 'red');
                    }
                } else {
                    // Archivo no existe, solo marcar como archivado
                    $documentoModel->update($documento['id'], [
                        'estado' => 'archivado',
                        'observaciones' => 'Archivo no encontrado al archivar el ' . date('Y-m-d H:i:s')
                    ]);
                    $archivados++;
                }

            } catch (\Exception $e) {
                $errores++;
                CLI::write("Error procesando documento {$documento['id']}: " . $e->getMessage(), 'red');
            }
        }

        CLI::write("Documentos archivados: {$archivados}", 'green');
        if ($errores > 0) {
            CLI::write("Errores encontrados: {$errores}", 'red');
        }
    }

    /**
     * Generar documentos pendientes para ventas
     */
    private function generarDocumentosPendientes(): void
    {
        CLI::write('Generando documentos pendientes...', 'cyan');

        $db = \Config\Database::connect();
        
        // Obtener ventas sin documentos generados
        $ventasSinDocumentos = $db->query("
            SELECT id, folio, fecha_venta 
            FROM ventas 
            WHERE documentos_generados = 0 
            AND estado IN ('venta_credito', 'venta_contado')
            AND fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY fecha_venta DESC
            LIMIT 50
        ")->getResultArray();

        if (empty($ventasSinDocumentos)) {
            CLI::write('No hay ventas pendientes de generar documentos.', 'yellow');
            return;
        }

        $documentoService = new DocumentoAutoService();
        $procesadas = 0;
        $errores = 0;

        foreach ($ventasSinDocumentos as $venta) {
            try {
                CLI::write("Procesando venta {$venta['folio']}...", 'light_gray');
                
                $resultado = $documentoService->generarDocumentosVenta($venta['id']);
                
                if ($resultado['success']) {
                    $procesadas++;
                    CLI::write("  ✓ Generados {$resultado['documentos_generados']} documentos", 'green');
                } else {
                    $errores++;
                    CLI::write("  ✗ Error: {$resultado['message']}", 'red');
                }

            } catch (\Exception $e) {
                $errores++;
                CLI::write("  ✗ Excepción: " . $e->getMessage(), 'red');
            }
        }

        CLI::write("Ventas procesadas: {$procesadas}", 'green');
        if ($errores > 0) {
            CLI::write("Errores encontrados: {$errores}", 'red');
        }
    }
}