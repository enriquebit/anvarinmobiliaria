<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApartadoModel;
use App\Models\ClienteModel;
use App\Models\LoteModel;
use App\Models\UserModel;
use App\Models\PerfilFinanciamientoModel;
use App\Models\StaffModel;
use App\Entities\Apartado;

class AdminApartadosController extends BaseController
{
    protected $apartadoModel;
    protected $clienteModel;
    protected $loteModel;
    protected $userModel;
    protected $perfilFinanciamientoModel;
    protected $staffModel;

    public function __construct()
    {
        $this->apartadoModel = new ApartadoModel();
        $this->clienteModel = new ClienteModel();
        $this->loteModel = new LoteModel();
        $this->userModel = new UserModel();
        $this->perfilFinanciamientoModel = new PerfilFinanciamientoModel();
        $this->staffModel = new StaffModel();
    }

    public function index()
    {
        $estatus = $this->request->getGet('estatus');
        $vendedorId = $this->request->getGet('vendedor_id');

        $apartados = $this->apartadoModel->getApartadosConRelaciones(50);

        $data = [
            'title' => 'Gestión de Apartados',
            'apartados' => $apartados,
            'vendedores' => $this->userModel->getVendedores(),
            'apartados_vencidos' => $this->apartadoModel->getApartadosVencidosSinProcesar(),
            'filtros' => [
                'estatus' => $estatus,
                'vendedor_id' => $vendedorId
            ]
        ];

        return view('admin/apartados/index', $data);
    }

    public function create()
    {
        // Verificar si viene desde ventas
        $fromVenta = $this->request->getGet('from_venta');
        $loteId = $this->request->getGet('lote_id');
        
        // Datos precargados por defecto
        $datosPreCargados = [
            'cliente_id' => null,
            'lote_id' => $loteId,
            'perfil_financiamiento_id' => null,
            'monto_apartado' => null,
            'vendedor_id' => auth()->id() // Por defecto el usuario actual
        ];
        
        // Si viene desde ventas, los datos deberían venir por JavaScript/sessionStorage
        
        $data = [
            'title' => 'Nuevo Apartado',
            'clientes' => $this->clienteModel->findAll(),
            'lotes' => $this->loteModel->getLotesDisponibles(),
            'vendedores' => $this->staffModel->findAll(), // Usar staff en lugar de users
            'planes' => $this->perfilFinanciamientoModel->getConfiguracionesParaApartados(), // Filtrar configuraciones para apartados (sin cero enganche)
            'from_venta' => $fromVenta,
            'datos_precargados' => $datosPreCargados,
        ];

        return view('admin/apartados/create', $data);
    }

    public function store()
    {
        
        $rules = [
            'lote_id' => 'required|integer',
            'cliente_id' => 'required|integer',
            'vendedor_id' => 'required|integer',
            'perfil_financiamiento_id' => 'required|integer',
            'fecha_apartado' => 'required', // Temporalmente relajar validación de fecha
            'monto_apartado' => 'required|decimal|greater_than[0]',
            'monto_enganche_requerido' => 'required|decimal|greater_than[0]',
            'fecha_limite_enganche' => 'required', // Temporalmente relajar validación de fecha
            'forma_pago' => 'required|in_list[efectivo,transferencia,cheque,tarjeta,deposito]'
        ];

        if (!$this->validate($rules)) {
            $erroresValidacion = $this->validator->getErrors();
            
            return redirect()->back()->withInput()->with('errors', $erroresValidacion);
        }

        // Verificar disponibilidad del lote
        if ($this->apartadoModel->lotetieneApartadoVigente($this->request->getPost('lote_id'))) {
            return redirect()->back()->withInput()->with('error', 'El lote seleccionado ya tiene un apartado vigente');
        }

        // Generar folio temporal (se actualizará después con el ID real)
        $folioTemporal = 'AP-TEMP-' . time();
        
        // Obtener user_id del staff seleccionado
        $staffId = $this->request->getPost('vendedor_id');
        $staff = $this->staffModel->find($staffId);
        $userId = $staff ? $staff->user_id : auth()->id(); // Fallback al usuario actual
        
        $data = [
            'folio_apartado' => $folioTemporal, // Agregar folio temporal requerido
            'lote_id' => $this->request->getPost('lote_id'),
            'cliente_id' => $this->request->getPost('cliente_id'),
            'user_id' => $userId, // Usar el user_id del staff, no el staff.id
            'perfil_financiamiento_id' => $this->request->getPost('perfil_financiamiento_id'),
            'fecha_apartado' => $this->request->getPost('fecha_apartado'),
            'monto_apartado' => $this->request->getPost('monto_apartado'),
            'monto_enganche_requerido' => $this->request->getPost('monto_enganche_requerido'),
            'fecha_limite_enganche' => $this->request->getPost('fecha_limite_enganche'),
            'forma_pago' => $this->request->getPost('forma_pago'),
            'referencia_pago' => $this->request->getPost('referencia_pago'),
            'observaciones' => $this->request->getPost('observaciones'),
            'estatus_apartado' => 'vigente', // Agregar estatus inicial
            'created_by' => auth()->id() // Registrar el usuario que está creando el apartado
        ];

        
        $apartadoId = $this->apartadoModel->insert($data);
        
        if ($apartadoId) {
            
            // **GENERAR FOLIO DINÁMICO AP-ID y actualizar en BD**
            $folioFinal = Apartado::generarFolioDesdeId($apartadoId);
            $this->apartadoModel->update($apartadoId, ['folio_apartado' => $folioFinal]);
            
            // Registrar ingreso automáticamente usando helper
            $datosIngreso = [
                'tipo_ingreso' => 'apartado',
                'monto' => $this->request->getPost('monto_apartado'), // Para apartados siempre es el monto del apartado
                'metodo_pago' => $this->request->getPost('forma_pago'),
                'referencia' => $this->request->getPost('referencia_pago'),
                'cliente_id' => $this->request->getPost('cliente_id'),
                'apartado_id' => $apartadoId,
                'user_id' => auth()->id()
            ];
            
            $resultadoIngreso = crear_ingreso_automatico($datosIngreso);
            
            // BLOQUEAR LOTE: Cambiar estado de "Disponible" (1) a "Apartado" (2)
            try {
                $this->loteModel->cambiarEstado($this->request->getPost('lote_id'), 2);
            } catch (\Exception $e) {
                log_message('error', "Error bloqueando lote en apartado {$folioFinal}: " . $e->getMessage());
            }
            
            if ($resultadoIngreso['success']) {
                // Redirigir al recibo en nueva pestaña
                $reciboUrl = site_url('/admin/apartados/recibo/' . $apartadoId);
                
                return redirect()->to('/admin/apartados')
                    ->with('success', "Apartado {$folioFinal} registrado exitosamente")
                    ->with('apartado_id', $apartadoId)
                    ->with('folio_apartado', $folioFinal)
                    ->with('ingreso_folio', $resultadoIngreso['folio'])
                    ->with('recibo_url', $reciboUrl)
                    ->with('open_recibo', true);
            } else {
                // Si falla el ingreso, logear pero no fallar el apartado
                log_message('warning', "Apartado {$folioFinal} creado pero error en ingreso: " . $resultadoIngreso['error']);
                return redirect()->to('/admin/apartados')->with('warning', "Apartado {$folioFinal} registrado, pero error al registrar ingreso");
            }
        }

        return redirect()->back()->withInput()->with('error', 'Error al registrar el apartado');
    }

    public function show($id)
    {
        $apartado = $this->apartadoModel->find($id);
        
        if (!$apartado) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Detalle de Apartado',
            'apartado' => $apartado,
            'cliente' => $this->clienteModel->find($apartado->cliente_id),
            'lote' => $this->loteModel->find($apartado->lote_id),
            'vendedor' => $this->userModel->find($apartado->user_id),
            'plan' => $this->perfilFinanciamientoModel->find($apartado->perfil_financiamiento_id)
        ];

        return view('admin/apartados/show', $data);
    }
    
    /**
     * Mostrar recibo de apartado
     */
    public function recibo($id)
    {
        helper(['recibo', 'format', 'receipt_templates']);
        
        $apartado = $this->apartadoModel->find($id);
        
        if (!$apartado) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Obtener ingreso relacionado con información completa
        $db = \Config\Database::connect();
        $ingreso = $db->table('ingresos i')
            ->select('
                i.*,
                c.nombres as cliente_nombre,
                c.apellido_paterno,
                c.apellido_materno,
                c.email as cliente_email,
                CONCAT(s.nombres, " ", s.apellido_paterno, " ", COALESCE(s.apellido_materno, "")) as usuario_nombre,
                a.fecha_apartado,
                a.dias_plazo,
                a.fecha_vencimiento,
                l.clave as lote_clave,
                l.area as lote_area,
                p.nombre as proyecto_nombre
            ')
            ->join('clientes c', 'c.id = i.cliente_id', 'left')
            ->join('staff s', 's.user_id = i.user_id', 'left')
            ->join('apartados a', 'a.id = i.apartado_id', 'left')
            ->join('lotes l', 'l.id = a.lote_id', 'left')
            ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
            ->where('i.apartado_id', $id)
            ->where('i.tipo_ingreso', 'apartado')
            ->get()
            ->getRow();
        
        if (!$ingreso) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Ingreso de apartado no encontrado');
        }
        
        // Usar el nuevo sistema de templates de recibos
        $tipoRecibo = determinar_tipo_recibo($ingreso);
        $templateRecibo = obtener_template_recibo($tipoRecibo);
        
        // Crear objetos de datos compatibles con el sistema de templates
        $cliente = (object)[
            'nombres' => $ingreso->cliente_nombre,
            'apellido_paterno' => $ingreso->apellido_paterno,
            'apellido_materno' => $ingreso->apellido_materno,
            'email' => $ingreso->cliente_email,
            'id' => $ingreso->cliente_id
        ];
        
        $lote = (object)[
            'clave' => $ingreso->lote_clave,
            'area' => $ingreso->lote_area,
            'id' => $apartado->lote_id,
            'precio_total' => 0 // Se puede obtener si es necesario
        ];
        
        // Obtener vendedor con nombres completos
        $vendedorQuery = $db->table('users u')
                           ->select('u.id, u.username, s.nombres, s.apellido_paterno, s.apellido_materno')
                           ->join('staff s', 's.user_id = u.id', 'left')
                           ->where('u.id', $ingreso->user_id)
                           ->get()
                           ->getRow();
        
        $vendedor = (object)[
            'id' => $vendedorQuery->id ?? $ingreso->user_id,
            'username' => $vendedorQuery->username ?? 'Usuario',
            'nombres' => $vendedorQuery->nombres ?? '',
            'apellido_paterno' => $vendedorQuery->apellido_paterno ?? '',
            'apellido_materno' => $vendedorQuery->apellido_materno ?? '',
            'nombre_completo' => trim(($vendedorQuery->nombres ?? '') . ' ' . 
                                    ($vendedorQuery->apellido_paterno ?? '') . ' ' . 
                                    ($vendedorQuery->apellido_materno ?? '')) ?: ($vendedorQuery->username ?? 'Usuario')
        ];
        
        // Preparar datos específicos para el tipo de recibo
        $datosEspecializados = preparar_datos_recibo_especializado($ingreso, $tipoRecibo);
        
        // Combinar todos los datos
        $datosRecibo = array_merge($datosEspecializados, [
            'folio' => $ingreso->folio,
            'monto' => $ingreso->monto,
            'referencia' => $ingreso->referencia ?? '',
            'metodo_pago' => ucfirst($ingreso->metodo_pago ?? 'efectivo'),
            'tipo_ingreso' => $ingreso->tipo_ingreso,
            'ingreso' => $ingreso,
            'cliente' => $cliente,
            'lote' => $lote,
            'vendedor' => $vendedor,
            'apartado' => $apartado
        ]);
        
        return view($templateRecibo, $datosRecibo);
    }

    public function edit($id)
    {
        $apartado = $this->apartadoModel->find($id);
        
        if (!$apartado) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($apartado->estatus_apartado !== 'vigente') {
            return redirect()->back()->with('error', 'Solo se pueden editar apartados vigentes');
        }

        $data = [
            'title' => 'Editar Apartado',
            'apartado' => $apartado,
            'clientes' => $this->clienteModel->findAll(),
            'lotes' => $this->loteModel->getLotesDisponibles(),
            'vendedores' => $this->userModel->getVendedores(),
            'planes' => $this->perfilFinanciamientoModel->getConfiguracionesActivas()
        ];

        return view('admin/apartados/edit', $data);
    }

    public function update($id)
    {
        $apartado = $this->apartadoModel->find($id);
        
        if (!$apartado) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'monto_apartado' => 'required|decimal|greater_than[0]',
            'monto_enganche_requerido' => 'required|decimal|greater_than[0]',
            'fecha_limite_enganche' => 'required|valid_date',
            'forma_pago' => 'required|in_list[efectivo,transferencia,cheque,tarjeta,deposito]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'monto_apartado' => $this->request->getPost('monto_apartado'),
            'monto_enganche_requerido' => $this->request->getPost('monto_enganche_requerido'),
            'fecha_limite_enganche' => $this->request->getPost('fecha_limite_enganche'),
            'forma_pago' => $this->request->getPost('forma_pago'),
            'referencia_pago' => $this->request->getPost('referencia_pago'),
            'observaciones' => $this->request->getPost('observaciones')
        ];

        if ($this->apartadoModel->update($id, $data)) {
            return redirect()->to('/admin/apartados/' . $id)->with('success', 'Apartado actualizado exitosamente');
        }

        return redirect()->back()->withInput()->with('error', 'Error al actualizar el apartado');
    }

    public function cancelar($id)
    {
        $apartado = $this->apartadoModel->find($id);
        
        if (!$apartado) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($apartado->estatus_apartado !== 'vigente') {
            return redirect()->back()->with('error', 'Solo se pueden cancelar apartados vigentes');
        }

        if ($this->request->getMethod() === 'post') {
            $motivo = $this->request->getPost('motivo_cancelacion');
            
            if (empty($motivo)) {
                return redirect()->back()->with('error', 'El motivo de cancelación es obligatorio');
            }

            if ($this->apartadoModel->actualizarEstatus($id, 'cancelado', $motivo)) {
                return redirect()->to('/admin/apartados')->with('success', 'Apartado cancelado exitosamente');
            }

            return redirect()->back()->with('error', 'Error al cancelar el apartado');
        }

        $data = [
            'title' => 'Cancelar Apartado',
            'apartado' => $apartado
        ];

        return view('admin/apartados/cancelar', $data);
    }

    public function procesar_vencidos()
    {
        $apartadosVencidos = $this->apartadoModel->getApartadosVencidosSinProcesar();
        $procesados = 0;

        foreach ($apartadosVencidos as $apartado) {
            $config = $this->perfilFinanciamientoModel->find($apartado->perfil_financiamiento_id);
            
            if ($config && $config->accion_anticipo_incompleto === 'aplicar_penalizacion') {
                // Usar la nueva lógica de configuraciones financieras
                $penalizacion = $config->penalizacion_apartado > 0 ? 
                    ($apartado->monto_apartado * $config->penalizacion_apartado / 100) : 0;
                $montoDevuelto = $apartado->monto_apartado - $penalizacion;
                
                $this->apartadoModel->aplicarPenalizacion(
                    $apartado->id,
                    $config->tipo_penalizacion ?? 'porcentual',
                    $penalizacion,
                    $montoDevuelto
                );
                
                $procesados++;
            }
        }

        return redirect()->to('/admin/apartados')
                        ->with('success', "Se procesaron {$procesados} apartados vencidos");
    }


    public function subirComprobante($id)
    {
        $apartado = $this->apartadoModel->find($id);
        
        if (!$apartado) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'post') {
            $validationRule = [
                'comprobante' => [
                    'label' => 'Comprobante',
                    'rules' => 'uploaded[comprobante]|max_size[comprobante,2048]|ext_in[comprobante,jpg,jpeg,png,pdf]'
                ]
            ];

            if (!$this->validate($validationRule)) {
                return redirect()->back()->with('errors', $this->validator->getErrors());
            }

            $file = $this->request->getFile('comprobante');
            
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads/apartados/', $newName);

                $data = [
                    'comprobante_url' => 'uploads/apartados/' . $newName
                ];

                if ($this->apartadoModel->update($id, $data)) {
                    return redirect()->to('/admin/apartados/' . $id)
                                    ->with('success', 'Comprobante subido exitosamente');
                }
            }

            return redirect()->back()->with('error', 'Error al subir el comprobante');
        }

        $data = [
            'title' => 'Subir Comprobante',
            'apartado' => $apartado
        ];

        return view('admin/apartados/comprobante', $data);
    }

    /**
     * Obtener perfiles de financiamiento filtrados por lote
     */
    public function getPerfilesPorLote()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso no permitido'
            ]);
        }

        try {
            $loteId = $this->request->getGet('lote_id');
            
            if (!$loteId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID de lote no proporcionado'
                ]);
            }

            // Obtener configuraciones filtradas para el lote
            $configuraciones = $this->perfilFinanciamientoModel->getConfiguracionesParaApartadosPorLote($loteId);

            return $this->response->setJSON([
                'success' => true,
                'configuraciones' => $configuraciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener perfiles: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar simulación de tabla de amortización
     * Similar al módulo de ventas pero adaptado para apartados
     */
    public function simularAmortizacion()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso no permitido'
            ]);
        }

        try {
            // Obtener datos del request
            $loteId = $this->request->getPost('lote_id');
            $tipoFinanciamientoId = $this->request->getPost('perfil_financiamiento_id');
            $fechaInicio = $this->request->getPost('fecha_inicio') ?: date('Y-m-d');
            
            // Obtener el lote
            $lote = $this->loteModel->find($loteId);
            if (!$lote) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Lote no encontrado'
                ]);
            }

            // Obtener configuración financiera
            $configuracion = $this->perfilFinanciamientoModel->find($tipoFinanciamientoId);
            if (!$configuracion) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Configuración financiera no encontrada'
                ]);
            }

            // Calcular montos
            $precioTotal = $lote->precio_total;
            
            // Calcular enganche según configuración
            if ($configuracion->tipo_anticipo === 'porcentaje') {
                $montoEnganche = $precioTotal * ($configuracion->porcentaje_anticipo / 100);
            } else {
                $montoEnganche = $configuracion->anticipo_fijo;
            }
            
            // Validar enganche mínimo
            if ($configuracion->enganche_minimo > 0 && $montoEnganche < $configuracion->enganche_minimo) {
                $montoEnganche = $configuracion->enganche_minimo;
            }

            // Calcular financiamiento
            $montoFinanciar = $precioTotal - $montoEnganche;
            
            // Obtener plazo de meses
            $plazoMeses = $configuracion->meses_con_intereses > 0 ? 
                          $configuracion->meses_con_intereses : 
                          $configuracion->meses_sin_intereses;

            if ($plazoMeses <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La configuración no tiene un plazo válido'
                ]);
            }

            // Generar tabla de amortización
            $tablaAmortizacion = $this->generarTablaAmortizacion(
                $montoFinanciar,
                $plazoMeses,
                $configuracion->porcentaje_interes_anual,
                $fechaInicio,
                $configuracion->meses_con_intereses > 0 ? 'con_intereses' : 'sin_intereses'
            );

            // Preparar respuesta
            $response = [
                'success' => true,
                'datos' => [
                    'precio_total' => $precioTotal,
                    'monto_enganche' => $montoEnganche,
                    'monto_financiar' => $montoFinanciar,
                    'plazo_meses' => $plazoMeses,
                    'tasa_anual' => $configuracion->porcentaje_interes_anual,
                    'tipo_financiamiento' => $configuracion->meses_con_intereses > 0 ? 'con_intereses' : 'sin_intereses',
                    'monto_apartado' => $configuracion->apartado_minimo,
                    'fecha_limite_enganche' => date('Y-m-d', strtotime($fechaInicio . ' + ' . $configuracion->plazo_liquidar_enganche . ' days'))
                ],
                'tabla' => $tablaAmortizacion,
                'configuracion' => [
                    'nombre' => $configuracion->nombre,
                    'accion_incompleto' => $configuracion->accion_anticipo_incompleto,
                    'penalizacion' => $configuracion->penalizacion_apartado,
                    'penalizacion_tardio' => $configuracion->penalizacion_enganche_tardio
                ]
            ];

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error en simulación de amortización: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar la simulación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar tabla de amortización
     * Reutiliza la lógica del módulo de ventas
     */
    private function generarTablaAmortizacion($montoFinanciar, $plazoMeses, $tasaAnual, $fechaInicio, $tipoFinanciamiento)
    {
        $pagos = [];
        $saldoInicial = $montoFinanciar;
        $fechaPago = new \DateTime($fechaInicio);
        
        if ($tipoFinanciamiento === 'sin_intereses' || $tasaAnual == 0) {
            // Sin intereses
            $pagoMensual = $montoFinanciar / $plazoMeses;
            
            for ($i = 1; $i <= $plazoMeses; $i++) {
                $fechaPago->modify('+1 month');
                
                $pagos[] = [
                    'periodo' => $i,
                    'fecha' => $fechaPago->format('d/m/Y'),
                    'saldo_inicial' => number_format($saldoInicial, 2),
                    'pago' => number_format($pagoMensual, 2),
                    'interes' => number_format(0, 2),
                    'capital' => number_format($pagoMensual, 2),
                    'saldo_final' => number_format($saldoInicial - $pagoMensual, 2),
                    'tipo' => 'MENSUALIDAD'
                ];
                
                $saldoInicial -= $pagoMensual;
            }
        } else {
            // Con intereses
            $tasaMensual = ($tasaAnual / 100) / 12;
            $pagoMensual = $montoFinanciar * ($tasaMensual * pow(1 + $tasaMensual, $plazoMeses)) / 
                          (pow(1 + $tasaMensual, $plazoMeses) - 1);
            
            for ($i = 1; $i <= $plazoMeses; $i++) {
                $fechaPago->modify('+1 month');
                
                $interesPeriodo = $saldoInicial * $tasaMensual;
                $capitalPeriodo = $pagoMensual - $interesPeriodo;
                $saldoFinal = $saldoInicial - $capitalPeriodo;
                
                $pagos[] = [
                    'periodo' => $i,
                    'fecha' => $fechaPago->format('d/m/Y'),
                    'saldo_inicial' => number_format($saldoInicial, 2),
                    'pago' => number_format($pagoMensual, 2),
                    'interes' => number_format($interesPeriodo, 2),
                    'capital' => number_format($capitalPeriodo, 2),
                    'saldo_final' => number_format(max(0, $saldoFinal), 2),
                    'tipo' => 'MENSUALIDAD'
                ];
                
                $saldoInicial = $saldoFinal;
            }
        }
        
        return $pagos;
    }

    /**
     * Obtener lotes para el modal de selección
     * Devuelve información completa de lotes disponibles para apartados
     */
    public function obtenerLotesModal()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso no permitido'
            ]);
        }

        try {
            // Verificar si es búsqueda de lote específico por ID
            $loteId = $this->request->getPost('lote_id');
            
            if ($loteId) {
                // Búsqueda específica de un lote por ID
                $lote = $this->loteModel->getLoteCompleto($loteId);
                $lotes = $lote ? [$lote] : [];
            } else {
                // Obtener filtros para búsqueda general
                $filtros = [
                    'proyecto_id' => $this->request->getPost('proyecto_id'),
                    'estado_id' => $this->request->getPost('estado_id') ?: 0, // Por defecto solo disponibles (código 0)
                    'search' => $this->request->getPost('search')
                ];

                // Obtener lotes con información completa
                $lotes = $this->loteModel->getLotesParaModal($filtros);
            }

            // Formatear datos para DataTables
            $data = [];
            foreach ($lotes as $lote) {
                $data[] = [
                    'id' => $lote->id,
                    'clave' => $lote->clave,
                    'proyecto' => $lote->proyecto_nombre ?? 'N/A',
                    'manzana' => $lote->manzana_nombre ?? 'N/A',
                    'area' => number_format($lote->area, 2) . ' m²',
                    'precio_m2' => '$' . number_format($lote->precio_m2, 2),
                    'precio_total' => '$' . number_format($lote->precio_total, 2),
                    'estado' => '<span class="badge badge-success">' . ($lote->estado_nombre ?? 'Disponible') . '</span>',
                    'tipo' => $lote->tipo_nombre ?? 'Lote',
                    'categoria' => $lote->categoria_nombre ?? '',
                    // Datos adicionales para JavaScript
                    'data_precio' => $lote->precio_total,
                    'data_area' => $lote->area,
                    'data_precio_m2' => $lote->precio_m2,
                    'data_proyecto_id' => $lote->proyecto_id,
                    'data_manzana_id' => $lote->manzana_id,
                    'accion' => '<button type="button" class="btn btn-success btn-sm btn-seleccionar-lote" 
                                        data-id="' . $lote->id . '" 
                                        data-clave="' . $lote->clave . '"
                                        data-precio="' . $lote->precio_total . '"
                                        data-area="' . $lote->area . '"
                                        data-precio-m2="' . $lote->precio_m2 . '"
                                        data-proyecto="' . ($lote->proyecto_nombre ?? '') . '"
                                        data-manzana="' . ($lote->manzana_nombre ?? '') . '"
                                        data-tipo="' . ($lote->tipo_nombre ?? 'Lote') . '"
                                        data-categoria="' . ($lote->categoria_nombre ?? '') . '">
                                 <i class="fas fa-check mr-1"></i>Seleccionar
                               </button>'
                ];
            }

            return $this->response->setJSON([
                'data' => $data,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en obtenerLotesModal: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener los lotes: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Imprimir simulación de amortización para apartados
     * Usa el mismo template que el módulo de ventas pero adaptado para apartados
     */
    public function imprimirAmortizacion()
    {
        if ($this->request->getMethod() !== 'POST') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        try {
            // Obtener datos del POST
            $pagos = json_decode($this->request->getPost('pagos'), true);
            $datos = json_decode($this->request->getPost('datos'), true);

            if (!$pagos || !$datos) {
                throw new \Exception('Datos de simulación no válidos');
            }

            // Preparar datos para la vista de impresión
            $datosImpresion = [
                'pagos' => $pagos,
                'datos' => $datos,
                'tipo_documento' => 'SIMULACIÓN DE APARTADO',
                'fecha_generacion' => date('d/m/Y H:i:s'),
                'usuario_genero' => auth()->user()->email ?? 'Usuario no identificado'
            ];

            // Usar la misma vista de impresión que ventas pero adaptada
            return view('admin/apartados/partials/imprimir_amortizacion', $datosImpresion);

        } catch (\Exception $e) {
            log_message('error', 'Error en impresión de apartado: ' . $e->getMessage());
            return view('errors/html/error_500');
        }
    }
}