<?php

namespace App\Services;

use App\Models\LoteModel;
use App\Models\EstadoLoteModel;
use App\Entities\Lote;

/**
 * EstadoLoteService
 * 
 * Servicio para gestión de estados de lotes con validaciones y transiciones
 * Basado en Entity-First, MVP, DRY del PLAN_DE_TRABAJO_VENTAS.md
 */
class EstadoLoteService
{
    protected LoteModel $loteModel;
    protected EstadoLoteModel $estadoModel;
    // VentaModel eliminado - módulo de ventas deshabilitado
    
    // Estados según códigos de la BD - ahora usar los mismos que en Lote Entity
    public const ESTADO_DISPONIBLE = 0;
    public const ESTADO_APARTADO   = 1;
    public const ESTADO_VENDIDO    = 2;
    public const ESTADO_SUSPENDIDO = 3;
    
    // IDs de estado basados en códigos (actualizados para coincidir con Entity)
    public const ID_DISPONIBLE = 0;
    public const ID_APARTADO   = 1;
    public const ID_VENDIDO    = 2;
    public const ID_SUSPENDIDO = 3;

    public function __construct()
    {
        $this->loteModel = new LoteModel();
        $this->estadoModel = new EstadoLoteModel();
        // VentaModel eliminado - módulo de ventas deshabilitado
    }

    /**
     * Apartar lote - Disponible → Apartado
     */
    public function apartarLote(int $loteId, array $datosApartado = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $lote = $this->loteModel->find($loteId);
            
            if (!$lote) {
                throw new \Exception('Lote no encontrado');
            }

            // Validar transición
            if (!$this->validarTransicion($lote, self::ID_APARTADO, 'apartar')) {
                throw new \Exception('El lote no puede ser apartado en su estado actual');
            }

            // Cambiar estado
            $estadoAnterior = $lote->estados_lotes_id;
            $lote->estados_lotes_id = self::ID_APARTADO;
            
            if (!$this->loteModel->save($lote)) {
                throw new \Exception('Error al actualizar estado del lote: ' . implode(', ', $this->loteModel->errors()));
            }

            // Registrar en historial
            $this->registrarCambioEstado(
                $loteId,
                $estadoAnterior,
                self::ID_APARTADO,
                'apartar',
                $datosApartado['motivo'] ?? null,
                $datosApartado['venta_id'] ?? null
            );

            $db->transComplete();

            return [
                'success' => true,
                'lote_id' => $loteId,
                'estado_anterior' => $this->getNombreEstado($estadoAnterior),
                'estado_nuevo' => 'Apartado',
                'message' => 'Lote apartado exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Confirmar venta - Apartado → Vendido
     */
    public function confirmarVenta(int $loteId, int $ventaId, array $datos = []): array
    {
        // MÉTODO DESHABILITADO - Módulo de ventas eliminado
        throw new \Exception('Método deshabilitado: El módulo de ventas ha sido eliminado del sistema');
    }

    /**
     * Liberar lote apartado - Apartado → Disponible
     */
    public function liberarApartado(int $loteId, string $motivo): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $lote = $this->loteModel->find($loteId);
            
            if (!$lote) {
                throw new \Exception('Lote no encontrado');
            }

            // Validar transición
            if (!$this->validarTransicion($lote, self::ID_DISPONIBLE, 'liberar_apartado')) {
                throw new \Exception('El lote no puede ser liberado en su estado actual');
            }

            // Cambiar estado
            $estadoAnterior = $lote->estados_lotes_id;
            $lote->estados_lotes_id = self::ID_DISPONIBLE;
            
            if (!$this->loteModel->save($lote)) {
                throw new \Exception('Error al actualizar estado del lote');
            }

            // NOTA: Cancelación de ventas eliminada - módulo de ventas deshabilitado

            // Registrar en historial
            $this->registrarCambioEstado(
                $loteId,
                $estadoAnterior,
                self::ID_DISPONIBLE,
                'liberar_apartado',
                $motivo
            );

            $db->transComplete();

            return [
                'success' => true,
                'lote_id' => $loteId,
                'estado_anterior' => $this->getNombreEstado($estadoAnterior),
                'estado_nuevo' => 'Disponible',
                'message' => 'Apartado liberado exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Suspender lote - Cualquier estado → Suspendido
     */
    public function suspenderLote(int $loteId, string $motivo): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $lote = $this->loteModel->find($loteId);
            
            if (!$lote) {
                throw new \Exception('Lote no encontrado');
            }

            // Validar transición
            if (!$this->validarTransicion($lote, self::ID_SUSPENDIDO, 'suspender')) {
                throw new \Exception('El lote no puede ser suspendido en su estado actual');
            }

            // Cambiar estado
            $estadoAnterior = $lote->estados_lotes_id;
            $lote->estados_lotes_id = self::ID_SUSPENDIDO;
            
            if (!$this->loteModel->save($lote)) {
                throw new \Exception('Error al actualizar estado del lote');
            }

            // Registrar en historial
            $this->registrarCambioEstado(
                $loteId,
                $estadoAnterior,
                self::ID_SUSPENDIDO,
                'suspender',
                $motivo
            );

            $db->transComplete();

            return [
                'success' => true,
                'lote_id' => $loteId,
                'estado_anterior' => $this->getNombreEstado($estadoAnterior),
                'estado_nuevo' => 'Suspendido',
                'message' => 'Lote suspendido exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Reactivar lote suspendido - Suspendido → Disponible
     */
    public function reactivarLote(int $loteId): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $lote = $this->loteModel->find($loteId);
            
            if (!$lote) {
                throw new \Exception('Lote no encontrado');
            }

            // Validar transición
            if (!$this->validarTransicion($lote, self::ID_DISPONIBLE, 'reactivar')) {
                throw new \Exception('El lote no puede ser reactivado en su estado actual');
            }

            // Cambiar estado
            $estadoAnterior = $lote->estados_lotes_id;
            $lote->estados_lotes_id = self::ID_DISPONIBLE;
            
            if (!$this->loteModel->save($lote)) {
                throw new \Exception('Error al actualizar estado del lote');
            }

            // Registrar en historial
            $this->registrarCambioEstado(
                $loteId,
                $estadoAnterior,
                self::ID_DISPONIBLE,
                'reactivar',
                'Lote reactivado'
            );

            $db->transComplete();

            return [
                'success' => true,
                'lote_id' => $loteId,
                'estado_anterior' => $this->getNombreEstado($estadoAnterior),
                'estado_nuevo' => 'Disponible',
                'message' => 'Lote reactivado exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Procesar devolución excepcional - Vendido → Disponible
     */
    public function procesarDevolucion(int $loteId, string $motivo, array $validaciones = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $lote = $this->loteModel->find($loteId);
            
            if (!$lote) {
                throw new \Exception('Lote no encontrado');
            }

            // Validar transición
            if (!$this->validarTransicion($lote, self::ID_DISPONIBLE, 'devolucion')) {
                throw new \Exception('El lote no puede ser devuelto en su estado actual');
            }

            // Validaciones adicionales para devolución
            if (empty($validaciones['autorizado_por'])) {
                throw new \Exception('La devolución requiere autorización administrativa');
            }

            // Cambiar estado
            $estadoAnterior = $lote->estados_lotes_id;
            $lote->estados_lotes_id = self::ID_DISPONIBLE;
            
            if (!$this->loteModel->save($lote)) {
                throw new \Exception('Error al actualizar estado del lote');
            }

            // Cancelar venta asociada
            if (!empty($validaciones['venta_id'])) {
                $this->ventaModel->update($validaciones['venta_id'], [
                    'estado' => 'cancelado',
                    'observaciones' => 'Cancelado por devolución: ' . $motivo
                ]);
            }

            // Registrar en historial con metadatos
            $this->registrarCambioEstado(
                $loteId,
                $estadoAnterior,
                self::ID_DISPONIBLE,
                'devolucion',
                $motivo,
                $validaciones['venta_id'] ?? null,
                [
                    'autorizado_por' => $validaciones['autorizado_por'],
                    'fecha_autorizacion' => date('Y-m-d H:i:s'),
                    'tipo_devolucion' => $validaciones['tipo_devolucion'] ?? 'administrativa'
                ]
            );

            $db->transComplete();

            return [
                'success' => true,
                'lote_id' => $loteId,
                'estado_anterior' => $this->getNombreEstado($estadoAnterior),
                'estado_nuevo' => 'Disponible',
                'message' => 'Devolución procesada exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar si una transición es posible
     */
    public function validarTransicion(Lote $lote, int $estadoDestinoId, string $accion): bool
    {
        $db = \Config\Database::connect();
        
        $transicion = $db->table('estados_lotes_transiciones')
                         ->where('estado_origen_id', $lote->estados_lotes_id)
                         ->where('estado_destino_id', $estadoDestinoId)
                         ->where('accion', $accion)
                         ->where('activo', 1)
                         ->get()
                         ->getRowArray();
                         
        return !empty($transicion);
    }

    /**
     * Obtener transiciones disponibles para un lote
     */
    public function getTransicionesDisponibles(int $loteId): array
    {
        $lote = $this->loteModel->find($loteId);
        
        if (!$lote) {
            return [];
        }

        $db = \Config\Database::connect();
        
        return $db->table('estados_lotes_transiciones et')
                  ->select('et.*, ed.nombre as estado_destino_nombre, ed.color as estado_destino_color')
                  ->join('estados_lotes ed', 'ed.id = et.estado_destino_id')
                  ->where('et.estado_origen_id', $lote->estados_lotes_id)
                  ->where('et.activo', 1)
                  ->where('ed.activo', 1)
                  ->orderBy('et.accion')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Obtener historial de estados de un lote
     */
    public function getHistorialEstados(int $loteId): array
    {
        $db = \Config\Database::connect();
        
        return $db->table('historial_estados_lotes h')
                  ->select('h.*, ea.nombre as estado_anterior_nombre, en.nombre as estado_nuevo_nombre, u.email as usuario_email')
                  ->join('estados_lotes ea', 'ea.id = h.estado_anterior_id', 'left')
                  ->join('estados_lotes en', 'en.id = h.estado_nuevo_id')
                  ->join('users u', 'u.id = h.realizado_por', 'left')
                  ->where('h.lotes_id', $loteId)
                  ->orderBy('h.created_at', 'DESC')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Obtener estadísticas de estados
     */
    public function getEstadisticasEstados(): array
    {
        $db = \Config\Database::connect();
        
        return $db->table('vista_estadisticas_estados')
                  ->get()
                  ->getResultArray();
    }

    /**
     * Registrar cambio de estado en historial
     */
    private function registrarCambioEstado(
        int $loteId, 
        ?int $estadoAnterior, 
        int $estadoNuevo, 
        string $accion, 
        ?string $motivo = null, 
        ?int $ventaId = null,
        ?array $metadatos = null
    ): void {
        $db = \Config\Database::connect();
        
        $data = [
            'lotes_id' => $loteId,
            'estado_anterior_id' => $estadoAnterior,
            'estado_nuevo_id' => $estadoNuevo,
            'accion' => $accion,
            'motivo' => $motivo,
            'ventas_id' => $ventaId,
            'realizado_por' => auth()->id() ?? null,
            'ip_address' => \Config\Services::request()->getIPAddress(),
            'metadatos' => $metadatos ? json_encode($metadatos) : null
        ];
        
        $db->table('historial_estados_lotes')->insert($data);
    }

    /**
     * Obtener nombre de estado por ID
     */
    private function getNombreEstado(int $estadoId): string
    {
        $estado = $this->estadoModel->find($estadoId);
        return $estado ? $estado->nombre : 'Desconocido';
    }

    /**
     * Verificar consistencia de estados lote vs venta
     */
    public function verificarConsistenciaEstados(): array
    {
        $db = \Config\Database::connect();
        
        // Buscar inconsistencias
        $inconsistencias = $db->query("
            SELECT 
                l.id as lote_id,
                l.numero,
                l.clave,
                el.nombre as estado_lote,
                v.id as venta_id,
                v.folio,
                v.estado as estado_venta
            FROM lotes l
            JOIN estados_lotes el ON el.id = l.estados_lotes_id
            LEFT JOIN ventas v ON v.lotes_id = l.id AND v.estado IN ('apartado', 'venta_credito', 'venta_contado')
            WHERE l.activo = 1
            AND (
                (el.codigo = 2 AND (v.estado IS NULL OR v.estado NOT IN ('venta_credito', 'venta_contado'))) OR
                (el.codigo = 1 AND (v.estado IS NULL OR v.estado != 'apartado')) OR
                (el.codigo = 0 AND v.estado IS NOT NULL AND v.estado IN ('apartado', 'venta_credito', 'venta_contado'))
            )
            ORDER BY l.numero
        ")->getResultArray();
        
        return [
            'total_inconsistencias' => count($inconsistencias),
            'inconsistencias' => $inconsistencias
        ];
    }

    /**
     * Marcar lote como vendido directamente - Disponible → Vendido
     * Para ventas de cero enganche o contado
     */
    public function marcarComoVendidoDirecto(int $loteId, int $ventaId, array $datos = []): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $lote = $this->loteModel->find($loteId);
            
            if (!$lote) {
                throw new \Exception('Lote no encontrado');
            }

            // Validar que el lote esté disponible
            if ($lote->estados_lotes_id != self::ID_DISPONIBLE) {
                throw new \Exception('El lote debe estar disponible para ser vendido directamente');
            }

            // Validar que existe la venta
            $venta = $this->ventaModel->find($ventaId);
            if (!$venta || $venta->lotes_id != $loteId) {
                throw new \Exception('Venta no válida para este lote');
            }

            // Cambiar estado directamente a vendido
            $estadoAnterior = $lote->estados_lotes_id;
            $lote->estados_lotes_id = self::ID_VENDIDO;
            
            if (!$this->loteModel->save($lote)) {
                throw new \Exception('Error al actualizar estado del lote');
            }

            // Registrar en historial
            $this->registrarCambioEstado(
                $loteId,
                $estadoAnterior,
                self::ID_VENDIDO,
                'venta_directa',
                $datos['motivo'] ?? 'Venta directa (cero enganche)',
                $ventaId,
                $datos['metadatos'] ?? null
            );

            $db->transComplete();

            log_message('info', "✅ Lote {$loteId} marcado como vendido directamente (Venta ID: {$ventaId})");

            return [
                'success' => true,
                'lote_id' => $loteId,
                'venta_id' => $ventaId,
                'estado_anterior' => $this->getNombreEstado($estadoAnterior),
                'estado_nuevo' => 'Vendido',
                'message' => 'Lote vendido directamente exitosamente'
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            
            log_message('error', "❌ Error al marcar lote {$loteId} como vendido: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}