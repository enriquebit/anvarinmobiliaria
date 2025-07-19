<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\LoteModel;
use App\Models\ApartadoModel;
use App\Models\VentaModel;
use App\Models\UserModel;
use App\Models\EmpresaModel;

class AdminIngresosController extends BaseController
{
    protected $db;
    protected $clienteModel;
    protected $loteModel;
    protected $apartadoModel;
    protected $ventaModel;
    protected $userModel;
    protected $empresaModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->clienteModel = new ClienteModel();
        $this->loteModel = new LoteModel();
        $this->apartadoModel = new ApartadoModel();
        $this->ventaModel = new VentaModel();
        $this->userModel = new UserModel();
        $this->empresaModel = new EmpresaModel();
    }

    public function index()
    {
        // Obtener filtros
        $tipoIngreso = $this->request->getGet('tipo_ingreso');
        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin = $this->request->getGet('fecha_fin');
        $clienteId = $this->request->getGet('cliente_id');

        // Query base para ingresos con joins
        $builder = $this->db->table('ingresos i');
        $builder->select('
            i.id,
            i.folio,
            i.tipo_ingreso,
            i.monto,
            i.metodo_pago,
            i.referencia,
            i.fecha_ingreso,
            i.apartado_id,
            c.nombres as cliente_nombre,
            c.apellido_paterno,
            c.apellido_materno,
            CONCAT(s.nombres, " ", s.apellido_paterno, " ", COALESCE(s.apellido_materno, "")) as usuario_nombre,
            CASE 
                WHEN i.apartado_id IS NOT NULL THEN CONCAT("AP-", i.apartado_id)
                ELSE NULL
            END as operacion_folio
        ');
        
        $builder->join('clientes c', 'c.id = i.cliente_id', 'left');
        $builder->join('staff s', 's.user_id = i.user_id', 'left');
        $builder->join('users u', 'u.id = i.user_id', 'left');

        // Aplicar filtros
        if ($tipoIngreso) {
            $builder->where('i.tipo_ingreso', $tipoIngreso);
        }
        
        if ($fechaInicio) {
            $builder->where('DATE(i.fecha_ingreso) >=', $fechaInicio);
        }
        
        if ($fechaFin) {
            $builder->where('DATE(i.fecha_ingreso) <=', $fechaFin);
        }
        
        if ($clienteId) {
            $builder->where('i.cliente_id', $clienteId);
        }

        $builder->orderBy('i.fecha_ingreso', 'DESC');
        $ingresos = $builder->get()->getResult();

        // Obtener estadísticas
        $estadisticas = $this->obtenerEstadisticas($tipoIngreso, $fechaInicio, $fechaFin);

        $data = [
            'title' => 'Gestión de Ingresos',
            'ingresos' => $ingresos,
            'clientes' => $this->clienteModel->findAll(),
            'estadisticas' => $estadisticas,
            'filtros' => [
                'tipo_ingreso' => $tipoIngreso,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'cliente_id' => $clienteId
            ]
        ];

        return view('admin/ingresos/index', $data);
    }

    public function show($id)
    {
        $ingreso = $this->db->table('ingresos i')
            ->select('
                i.*,
                c.id as cliente_id,
                c.nombres as cliente_nombre,
                c.apellido_paterno,
                c.apellido_materno,
                c.email as cliente_email,
                c.telefono as cliente_telefono,
                CONCAT(s.nombres, " ", s.apellido_paterno, " ", COALESCE(s.apellido_materno, "")) as usuario_nombre,
                a.fecha_apartado,
                l.clave as lote_clave,
                l.area as lote_area,
                p.nombre as proyecto_nombre
            ')
            ->join('clientes c', 'c.id = i.cliente_id', 'left')
            ->join('staff s', 's.user_id = i.user_id', 'left')
            ->join('users u', 'u.id = i.user_id', 'left')
            ->join('apartados a', 'a.id = i.apartado_id', 'left')
            ->join('lotes l', 'l.id = a.lote_id', 'left')
            ->join('proyectos p', 'p.id = l.proyectos_id', 'left')
            ->where('i.id', $id)
            ->get()
            ->getRow();

        if (!$ingreso) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Detalle de Ingreso - ' . $ingreso->folio,
            'ingreso' => $ingreso
        ];

        return view('admin/ingresos/show', $data);
    }

    public function recibo($id)
    {
        $ingreso = $this->db->table('ingresos i')
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
                COALESCE(lv.clave, la.clave) as lote_clave,
                COALESCE(lv.area, la.area) as lote_area,
                COALESCE(pv.nombre, pa.nombre) as proyecto_nombre,
                COALESCE(lv.id, la.id) as lote_id
            ')
            ->join('clientes c', 'c.id = i.cliente_id', 'left')
            ->join('staff s', 's.user_id = i.user_id', 'left')
            ->join('users u', 'u.id = i.user_id', 'left')
            // Joins para apartados
            ->join('apartados a', 'a.id = i.apartado_id', 'left')
            ->join('lotes la', 'la.id = a.lote_id', 'left')
            ->join('proyectos pa', 'pa.id = la.proyectos_id', 'left')
            // Joins para ventas
            ->join('ventas v', 'v.id = i.venta_id', 'left')
            ->join('lotes lv', 'lv.id = v.lote_id', 'left')
            ->join('proyectos pv', 'pv.id = lv.proyectos_id', 'left')
            ->where('i.id', $id)
            ->get()
            ->getRow();

        if (!$ingreso) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Obtener información de la empresa (usar la primera empresa activa si no hay relación específica)
        $empresa = $this->empresaModel->where('activo', 1)->first();
        if (!$empresa) {
            // Fallback a cualquier empresa disponible
            $empresa = $this->empresaModel->first();
        }


        // Cargar helpers para el nuevo sistema de recibos
        helper(['recibo', 'format', 'receipt_templates']);
        
        // Determinar el tipo de recibo basándose en los datos del ingreso
        $tipoRecibo = determinar_tipo_recibo($ingreso);
        $templateRecibo = obtener_template_recibo($tipoRecibo);
        
        // Crear objetos de datos que necesita el recibo
        $cliente = (object)[
            'nombres' => $ingreso->cliente_nombre,
            'apellido_paterno' => $ingreso->apellido_paterno,
            'apellido_materno' => $ingreso->apellido_materno,
            'email' => $ingreso->cliente_email,
            'telefono' => '', // No disponible en esta consulta
            'id' => $ingreso->cliente_id
        ];
        
        $lote = (object)[
            'clave' => $ingreso->lote_clave,
            'area' => $ingreso->lote_area,
            'id' => $ingreso->lote_id,
            'precio_total' => 0
        ];
        
        
        // Obtener vendedor con nombres completos de staff
        $db = \Config\Database::connect();
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
        
        // Obtener datos relacionados si existen
        $venta = null;
        $apartado = null;
        
        if (!empty($ingreso->venta_id)) {
            $venta = $this->ventaModel->find($ingreso->venta_id);
        }
        
        if (!empty($ingreso->apartado_id)) {
            $apartado = $this->apartadoModel->find($ingreso->apartado_id);
        }
        
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
            'venta' => $venta,
            'apartado' => $apartado
        ]);
        
        return view($templateRecibo, $datosRecibo);
    }

    private function obtenerEstadisticas($tipoIngreso = null, $fechaInicio = null, $fechaFin = null)
    {
        $builder = $this->db->table('ingresos');
        
        // Aplicar mismos filtros
        if ($tipoIngreso) {
            $builder->where('tipo_ingreso', $tipoIngreso);
        }
        if ($fechaInicio) {
            $builder->where('DATE(fecha_ingreso) >=', $fechaInicio);
        }
        if ($fechaFin) {
            $builder->where('DATE(fecha_ingreso) <=', $fechaFin);
        }

        // Total general
        $totalGeneral = $builder->selectSum('monto')->get()->getRow()->monto ?? 0;
        
        // Por tipo de ingreso
        $builderTipos = clone $builder;
        $porTipo = $builderTipos->select('tipo_ingreso, SUM(monto) as total, COUNT(*) as cantidad')
                                ->groupBy('tipo_ingreso')
                                ->get()
                                ->getResult();

        // Últimos 30 días
        $builder30 = clone $builder;
        $ultimos30 = $builder30->where('fecha_ingreso >=', date('Y-m-d', strtotime('-30 days')))
                              ->selectSum('monto')
                              ->get()
                              ->getRow()->monto ?? 0;

        return [
            'total_general' => $totalGeneral,
            'por_tipo' => $porTipo,
            'ultimos_30_dias' => $ultimos30,
            'total_operaciones' => $builder->countAllResults()
        ];
    }

    /**
     * API para DataTables
     */
    public function getData()
    {
        $request = $this->request->getPost();
        
        // Configuración DataTables
        $draw = $request['draw'] ?? 1;
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 25;
        $searchValue = $request['search']['value'] ?? '';

        // Query base
        $builder = $this->db->table('ingresos i');
        $builder->select('
            i.id,
            i.folio,
            i.tipo_ingreso,
            i.monto,
            i.metodo_pago,
            i.referencia,
            i.fecha_ingreso,
            CONCAT(c.nombres, " ", c.apellido_paterno, " ", c.apellido_materno) as cliente_completo,
            u.username as usuario_nombre
        ');
        
        $builder->join('clientes c', 'c.id = i.cliente_id', 'left');
        $builder->join('staff s', 's.user_id = i.user_id', 'left');
        $builder->join('users u', 'u.id = i.user_id', 'left');

        // Filtrar por búsqueda
        if ($searchValue) {
            $builder->groupStart()
                    ->like('i.folio', $searchValue)
                    ->orLike('CONCAT(c.nombres, " ", c.apellido_paterno)', $searchValue)
                    ->orLike('i.tipo_ingreso', $searchValue)
                    ->orLike('i.referencia', $searchValue)
                    ->groupEnd();
        }

        // Contar total
        $totalRecords = $builder->countAllResults(false);

        // Ordenar y paginar
        $builder->orderBy('i.fecha_ingreso', 'DESC');
        $builder->limit($length, $start);
        
        $data = $builder->get()->getResult();

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Generar comprobante de pago
     */
    public function comprobante(int $pagoId)
    {
        try {
            // Cargar helper de recibos que contiene num_to_words()
            helper('recibo');
            // Obtener información del pago
            $pago = $this->db->table('pagos_ventas pv')
                ->select('
                    pv.*,
                    v.folio_venta,
                    v.precio_venta_final,
                    c.nombres,
                    c.apellido_paterno,
                    c.apellido_materno,
                    c.email,
                    c.telefono,
                    l.numero as lote_numero,
                    l.area as lote_superficie,
                    m.nombre as manzana_nombre,
                    p.nombre as proyecto_nombre,
                    e.nombre as empresa_nombre,
                    e.razon_social as empresa_razon_social
                ')
                ->join('ventas v', 'pv.venta_id = v.id')
                ->join('clientes c', 'v.cliente_id = c.id')
                ->join('lotes l', 'v.lote_id = l.id')
                ->join('manzanas m', 'l.manzanas_id = m.id', 'left')
                ->join('proyectos p', 'l.proyectos_id = p.id')
                ->join('empresas e', 'p.empresas_id = e.id')
                ->where('pv.id', $pagoId)
                ->get()
                ->getRow();

            if (!$pago) {
                return redirect()->back()->with('error', 'Pago no encontrado');
            }

            $data = [
                'title' => 'Comprobante de Pago - ' . $pago->folio_pago,
                'pago' => $pago,
                'cliente_nombre' => trim($pago->nombres . ' ' . $pago->apellido_paterno . ' ' . $pago->apellido_materno),
                'fecha_actual' => date('d/m/Y H:i:s')
            ];

            // Usar template de recibo existente
            return view('documentos/comprobante_pago', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error generando comprobante: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generando comprobante: ' . $e->getMessage());
        }
    }
}