Update Todos
  ⎿  ☒ Analizar documentación existente en ./docs/*.md
     ☒ Investigar sistema legacy en ./administracion/** para entender módulo de ventas
     ☒ Buscar y analizar archivos *funciones*.php para ingeniería inversa
     ☐ Analizar BD legacy ./administracion/anvarinm_web.sql usando fragmentación por offset
     ☐ Comparar con BD actual ./Database/nuevoanvar.sql
     ☐ Diseñar módulo de ventas relacionado con gestión de lotes
     ☐ Generar scripts SQL para implementación (no migraciones CI4)

● Basado en mi análisis completo del sistema legacy y la documentación existente, aquí está mi evaluación para implementar el      
  módulo de ventas:

  📊 ANÁLISIS DEL MÓDULO DE VENTAS LEGACY

  🔍 Hallazgos Principales

  Del análisis de ingeniería inversa he identificado:

  Tablas Clave del Sistema de Ventas:
  - tb_ventas - Núcleo del proceso de venta/apartado
  - tb_cobranza - Plan de pagos y estado de cuenta
  - tb_lotes - Inventario vendible (ya analizado)
  - tb_ingresos - Registro de pagos recibidos

  Función 317 (Apartado/Venta):
  - Ubicación: ./administracion/comandos/funciones.php:13949
  - Maneja pago de liquidación/adelanto a capital
  - Procesamiento de múltiples formas de pago
  - Actualización de cobranzas pendientes

  🏗️ ARQUITECTURA PROPUESTA PARA CI4

  1. Entidades Core

  -- Tabla Ventas (basada en tb_ventas legacy)
  CREATE TABLE ventas (
      id INT PRIMARY KEY AUTO_INCREMENT,
      folio VARCHAR(50) UNIQUE,
      cliente_id INT,
      lote_id INT,
      vendedor_id INT,
      proyecto_id INT,

      -- Montos
      total DECIMAL(15,2),
      total_pagado DECIMAL(15,2) DEFAULT 0,
      anticipo DECIMAL(15,2),

      -- Estados
      estado ENUM('proceso', 'apartado', 'vendido', 'cancelado') DEFAULT 'proceso',

      -- Control temporal apartados
      dias_cancelacion INT DEFAULT 15,
      fecha_limite_apartado DATETIME,

      -- Comisiones
      comision_apartado DECIMAL(15,2) DEFAULT 0,
      comision_total DECIMAL(15,2) DEFAULT 0,

      -- Timestamps
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

      FOREIGN KEY (cliente_id) REFERENCES clientes(id),
      FOREIGN KEY (lote_id) REFERENCES lotes(id),  -- A implementar
      FOREIGN KEY (vendedor_id) REFERENCES users(id),
      FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
  );

  -- Tabla Plan de Pagos (basada en tb_cobranza legacy)
  CREATE TABLE plan_pagos (
      id INT PRIMARY KEY AUTO_INCREMENT,
      venta_id INT,

      -- Tipo de pago
      tipo_credito ENUM('apartado', 'mensualidad', 'capital', 'interes') DEFAULT 'mensualidad',
      plazo VARCHAR(10), -- 'A' para apartado, '1', '2', '3'... para mensualidades

      -- Montos
      total DECIMAL(15,2),
      total_pagado DECIMAL(15,2) DEFAULT 0,
      total_sin_intereses DECIMAL(15,2), -- Para cálculo de moratorios

      -- Fechas
      fecha_vencimiento DATE,
      fecha_pago DATETIME NULL,

      -- Control
      cobrado BOOLEAN DEFAULT FALSE,
      dias_credito INT DEFAULT 30,
      intereses_moratorios DECIMAL(15,2) DEFAULT 0,

      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

      FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
  );

  -- Tabla Pagos Recibidos (basada en tb_ingresos legacy)
  CREATE TABLE pagos (
      id INT PRIMARY KEY AUTO_INCREMENT,
      venta_id INT,
      plan_pago_id INT NULL, -- Puede ser pago libre

      -- Formas de pago
      total DECIMAL(15,2),
      efectivo DECIMAL(15,2) DEFAULT 0,
      transferencia DECIMAL(15,2) DEFAULT 0,
      cheque DECIMAL(15,2) DEFAULT 0,
      tarjeta DECIMAL(15,2) DEFAULT 0,

      -- Referencias bancarias
      referencia_transferencia VARCHAR(100),
      referencia_cheque VARCHAR(100),

      -- Concepto
      concepto ENUM('apartado', 'mensualidad', 'capital', 'interes', 'liquidacion'),

      -- Cuentas destino (FK a cuentas bancarias)
      cuenta_transferencia_id INT NULL,
      cuenta_efectivo_id INT NULL,
      cuenta_cheque_id INT NULL,
      cuenta_tarjeta_id INT NULL,

      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

      FOREIGN KEY (venta_id) REFERENCES ventas(id),
      FOREIGN KEY (plan_pago_id) REFERENCES plan_pagos(id)
  );

  2. Entities con Business Logic

  <?php
  namespace App\Entities;

  class Venta extends Entity
  {
      const ESTADO_PROCESO = 'proceso';
      const ESTADO_APARTADO = 'apartado';
      const ESTADO_VENDIDO = 'vendido';
      const ESTADO_CANCELADO = 'cancelado';

      // Cálculos automáticos
      public function calcularSaldoPendiente(): float
      {
          return $this->total - $this->total_pagado;
      }

      public function estaVencidoApartado(): bool
      {
          if ($this->estado !== self::ESTADO_APARTADO) return false;
          return now() > $this->fecha_limite_apartado;
      }

      public function puedeSerCancelada(): bool
      {
          return in_array($this->estado, [self::ESTADO_PROCESO, self::ESTADO_APARTADO]);
      }

      // Flujo de estados
      public function apartar(int $diasLimite = 15): void
      {
          $this->estado = self::ESTADO_APARTADO;
          $this->fecha_limite_apartado = now()->addDays($diasLimite);
      }

      public function confirmarVenta(): void
      {
          $this->estado = self::ESTADO_VENDIDO;
          $this->fecha_limite_apartado = null;
      }
  }

  class PlanPago extends Entity
  {
      public function estaVencido(): bool
      {
          return !$this->cobrado && now() > $this->fecha_vencimiento;
      }

      public function diasVencimiento(): int
      {
          if (!$this->estaVencido()) return 0;
          return now()->diffInDays($this->fecha_vencimiento);
      }

      public function calcularInteresMoratorio(float $tasaPenalizacion): float
      {
          if (!$this->estaVencido()) return 0;

          $saldoPendiente = $this->total - $this->total_pagado;
          $diasVencido = $this->diasVencimiento();

          return $saldoPendiente * ($tasaPenalizacion / 100) * ($diasVencido / 30);
      }
  }

  3. Controlador de Ventas

  <?php
  namespace App\Controllers\Admin;

  class AdminVentasController extends BaseController
  {
      // Listar ventas con filtros
      public function index()
      {
          $filtros = $this->request->getGet();

          $data = [
              'title' => 'Gestión de Ventas',
              'ventas' => $this->ventaModel->getWithFilters($filtros),
              'proyectos' => $this->proyectoModel->findAll(),
              'vendedores' => $this->userModel->getVendedores(),
              'filtros' => $filtros
          ];

          return view('admin/ventas/index', $data);
      }

      // Proceso de apartado
      public function apartar()
      {
          $data = $this->request->getPost();

          try {
              $this->db->transStart();

              // 1. Crear venta
              $venta = new Venta($data);
              $venta->apartar($data['dias_cancelacion'] ?? 15);
              $ventaId = $this->ventaModel->insert($venta);

              // 2. Actualizar estado del lote
              $this->loteModel->update($data['lote_id'], [
                  'estado' => Lote::ESTADO_APARTADO
              ]);

              // 3. Procesar pago inicial si existe
              if ($data['pago_inicial'] > 0) {
                  $this->procesarPago($ventaId, $data);
              }

              $this->db->transComplete();

              return redirect()->to('/admin/ventas')
                  ->with('success', 'Lote apartado exitosamente');

          } catch (\Exception $e) {
              return redirect()->back()
                  ->with('error', 'Error: ' . $e->getMessage());
          }
      }

      // Confirmar venta (después de enganche completo)
      public function confirmar($ventaId)
      {
          try {
              $venta = $this->ventaModel->find($ventaId);

              // Validar que se haya pagado el enganche completo
              if ($venta->total_pagado < $venta->anticipo) {
                  throw new \Exception('Enganche incompleto');
              }

              $this->db->transStart();

              // 1. Confirmar venta
              $venta->confirmarVenta();
              $this->ventaModel->save($venta);

              // 2. Actualizar lote
              $this->loteModel->update($venta->lote_id, [
                  'estado' => Lote::ESTADO_VENDIDO
              ]);

              // 3. Generar plan de pagos para financiamiento
              if ($venta->calcularSaldoPendiente() > 0) {
                  $this->generarPlanPagos($venta);
              }

              $this->db->transComplete();

              return $this->response->setJSON(['success' => true]);

          } catch (\Exception $e) {
              return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
          }
      }

      private function generarPlanPagos(Venta $venta): void
      {
          $saldoPendiente = $venta->calcularSaldoPendiente();
          $meses = $this->request->getPost('meses_financiamiento') ?? 12;
          $cuotaMensual = $saldoPendiente / $meses;

          for ($i = 1; $i <= $meses; $i++) {
              $planPago = new PlanPago([
                  'venta_id' => $venta->id,
                  'tipo_credito' => 'mensualidad',
                  'plazo' => (string)$i,
                  'total' => $cuotaMensual,
                  'total_sin_intereses' => $cuotaMensual,
                  'fecha_vencimiento' => now()->addMonths($i)->endOfMonth()
              ]);

              $this->planPagoModel->insert($planPago);
          }
      }
  }

  🎯 PRIORIDADES DE IMPLEMENTACIÓN

  Fase 1: MVP Ventas (Sprint 1-2)

  1. Entidad Lote completa (prerequisito)
  2. Tablas ventas, plan_pagos, pagos
  3. CRUD básico de ventas
  4. Proceso apartado simple

  Fase 2: Flujo Completo (Sprint 3-4)

  1. Confirmación de ventas
  2. Plan de pagos automático
  3. Procesamiento de pagos múltiples
  4. Estados de cuenta

  Fase 3: Features Avanzadas (Sprint 5+)

  1. Cancelación automática apartados
  2. Intereses moratorios
  3. Comisiones vendedores
  4. Reportes financieros

  ⚠️ CONSIDERACIONES CRÍTICAS

  1. Dependencia de Lotes: Requiere implementar módulo de lotes primero
  2. Cuentas Bancarias: Necesario para registro de pagos
  3. Automatización: Commands para procesos automáticos (cancelaciones, intereses)
  4. Integridad: Transacciones para operaciones críticas

  ¿Te gustaría que proceda con la implementación del módulo de lotes como prerequisito, o prefieres que genere los scripts SQL     
   completos del módulo de ventas?