<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProyectoModel;
use App\Models\EmpresaModel;
use App\Models\ManzanaModel;

class AdminProyectosController extends BaseController
{
    protected $proyectoModel;
    protected $empresaModel;
    protected $manzanaModel;

    public function __construct()
    {
        $this->proyectoModel = new ProyectoModel();
        $this->empresaModel = new EmpresaModel();
        $this->manzanaModel = new ManzanaModel();
    }

    public function index()
    {
        $data = [
            'titulo' => 'Gestión de Proyectos',
            'proyectos' => $this->proyectoModel->getProyectosConEstadisticas(),
            'empresas' => $this->empresaModel->findAll()
        ];

        return view('admin/proyectos/index', $data);
    }

    public function create()
    {
        $data = [
            'titulo' => 'Crear Proyecto',
            'empresas' => $this->empresaModel->where('activo', 1)->findAll()
        ];

        return view('admin/proyectos/create', $data);
    }

    public function store()
    {
        $validation = $this->validate([
            'nombre' => 'required|max_length[255]',
            'clave' => 'required|max_length[100]',
            'empresas_id' => 'required|is_natural_no_zero',
            'archivo' => 'permit_empty|max_size[archivo,20480]|ext_in[archivo,pdf,jpg,jpeg,png]'
        ]);

        if (!$validation) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        $proyecto = $this->proyectoModel->insert($data);
        
        if (!$proyecto) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al crear el proyecto');
        }

        $proyectoId = $this->proyectoModel->getInsertID();
        
        $archivo = $this->request->getFile('archivo');
        if ($archivo && $archivo->isValid()) {
            $this->subirArchivo($archivo, $proyectoId);
        }

        return redirect()->to('/admin/proyectos')
                       ->with('success', 'Proyecto creado exitosamente');
    }

    public function show($id)
    {
        $proyecto = $this->proyectoModel->getProyectoConEmpresa($id);
        
        if (!$proyecto) {
            return redirect()->to('/admin/proyectos')
                           ->with('error', 'Proyecto no encontrado');
        }

        $data = [
            'titulo' => 'Ver Proyecto',
            'proyecto' => $proyecto,
            // 'documentos' => $this->documentoModel->getDocumentosPorProyecto($id) // TODO: Reimplement when DocumentoModel is recreated
            'documentos' => [] // Fallback empty array
        ];

        return view('admin/proyectos/show', $data);
    }

    public function edit($id)
    {
        $proyecto = $this->proyectoModel->find($id);
        
        if (!$proyecto) {
            return redirect()->to('/admin/proyectos')
                           ->with('error', 'Proyecto no encontrado');
        }

        $data = [
            'titulo' => 'Editar Proyecto',
            'proyecto' => $proyecto,
            'empresas' => $this->empresaModel->where('activo', 1)->findAll(),
            // 'documentos' => $this->documentoModel->getDocumentosPorProyecto($id) // TODO: Reimplement when DocumentoModel is recreated
            'documentos' => [] // Fallback empty array
        ];

        return view('admin/proyectos/edit', $data);
    }

    public function update($id)
    {
        $proyecto = $this->proyectoModel->find($id);
        
        if (!$proyecto) {
            return redirect()->to('/admin/proyectos')
                           ->with('error', 'Proyecto no encontrado');
        }

        // Validación con archivo opcional
        $validation = $this->validate([
            'nombre' => 'required|max_length[255]',
            'clave' => 'required|max_length[100]',
            'empresas_id' => 'required|is_natural_no_zero',
            'archivo' => 'permit_empty|max_size[archivo,20480]|ext_in[archivo,pdf,jpg,jpeg,png]'
        ]);

        if (!$validation) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        if (!$this->proyectoModel->update($id, $data)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al actualizar el proyecto');
        }

        // Procesar archivo si se subió uno nuevo
        $archivo = $this->request->getFile('archivo');
        if ($archivo && $archivo->isValid()) {
            $this->subirArchivo($archivo, $id);
        }

        return redirect()->to('/admin/proyectos')
                       ->with('success', 'Proyecto actualizado exitosamente');
    }

    /**
     * Vista de confirmación para eliminar proyecto
     */
    public function confirmDelete($id)
    {
        $proyecto = $this->proyectoModel->find($id);
        
        if (!$proyecto) {
            return redirect()->to('/admin/proyectos')
                           ->with('error', 'Proyecto no encontrado');
        }

        // Verificar si tiene manzanas asociadas (restricción)
        $totalManzanas = $this->manzanaModel->where('proyectos_id', $id)
                                           ->where('activo', 1)
                                           ->countAllResults();

        $data = [
            'titulo' => 'Eliminar Proyecto',
            'proyecto' => $proyecto,
            'total_manzanas' => $totalManzanas,
            'puede_eliminar' => $totalManzanas == 0
        ];

        return view('admin/proyectos/confirm_delete', $data);
    }

    public function delete($id)
    {
        $proyecto = $this->proyectoModel->find($id);
        
        if (!$proyecto) {
            return redirect()->to('/admin/proyectos')
                           ->with('error', 'Proyecto no encontrado');
        }

        // Verificar si tiene manzanas asociadas antes de eliminar
        $totalManzanas = $this->manzanaModel->where('proyectos_id', $id)
                                           ->where('activo', 1)
                                           ->countAllResults();

        if ($totalManzanas > 0) {
            return redirect()->to('/admin/proyectos')
                           ->with('error', "No se puede eliminar el proyecto porque tiene {$totalManzanas} manzana(s) asociada(s)");
        }

        // TODO: Reimplement document cleanup when DocumentoModel is recreated
        /*
        $documentos = $this->documentoModel->getDocumentosPorProyecto($id);
        foreach ($documentos as $documento) {
            if (file_exists($documento->ruta_archivo)) {
                unlink($documento->ruta_archivo);
            }
        }
        */

        $carpetaProyecto = FCPATH . 'uploads/proyectos/' . $id;
        if (is_dir($carpetaProyecto)) {
            rmdir($carpetaProyecto);
        }

        $this->proyectoModel->delete($id);

        return redirect()->to('/admin/proyectos')
                       ->with('success', 'Proyecto eliminado exitosamente');
    }

    /**
     * Eliminar documento específico
     */
    public function eliminarDocumento($documentoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/proyectos');
        }

        try {
            // TODO: Reimplement document deletion when DocumentoModel is recreated
            /*
            $documento = $this->documentoModel->find($documentoId);
            
            if (!$documento) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ]);
            }

            // Eliminar archivo físico
            if (file_exists($documento->ruta_archivo)) {
                unlink($documento->ruta_archivo);
            }

            // Eliminar registro de base de datos
            $this->documentoModel->delete($documentoId);
            */
            
            // Fallback: Document module was removed
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Módulo de documentos temporalmente deshabilitado'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Documento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar documento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Descargar documento
     */
    public function descargarDocumento($documentoId)
    {
        // TODO: Reimplement document download when DocumentoModel is recreated
        /*
        $documento = $this->documentoModel->find($documentoId);
        
        if (!$documento || !file_exists($documento->ruta_archivo)) {
            return redirect()->back()->with('error', 'Documento no encontrado');
        }

        return $this->response->download($documento->ruta_archivo, null)
                             ->setFileName($documento->nombre_archivo);
        */
        return redirect()->back()->with('error', 'Módulo de documentos temporalmente deshabilitado');
    }

    /**
     * Buscar proyectos con filtros AJAX
     */
    public function buscarProyectos()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $filtros = [
                'empresa_id' => $this->request->getPost('empresa_id'),
                'busqueda' => $this->request->getPost('busqueda'),
                'estatus' => $this->request->getPost('estatus')
            ];

            $proyectos = $this->proyectoModel->buscarProyectos($filtros);
            
            $data = [];
            foreach ($proyectos as $proyecto) {
                $estadisticas = $this->proyectoModel->getEstadisticasProyecto($proyecto->id);
                
                $data[] = [
                    'id' => $proyecto->id,
                    'nombre' => $proyecto->nombre,
                    'clave' => $proyecto->clave,
                    'empresa' => $proyecto->nombre_empresa,
                    'color' => $proyecto->color,
                    'estadisticas' => $estadisticas,
                    'avance_ventas' => $estadisticas['avance_ventas'],
                    'created_at' => $proyecto->created_at
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'proyectos' => $data
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al buscar proyectos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener estadísticas de un proyecto específico
     */
    public function obtenerEstadisticas($proyectoId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Solicitud no válida']);
        }

        try {
            $estadisticas = $this->proyectoModel->getEstadisticasProyecto($proyectoId);
            
            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    private function subirArchivo($archivo, $proyectoId)
    {
        $carpetaDestino = FCPATH . 'uploads/proyectos/' . $proyectoId;
        
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $nombreArchivo = $archivo->getRandomName();
        $rutaCompleta = $carpetaDestino . '/' . $nombreArchivo;
        
        if ($archivo->move($carpetaDestino, $nombreArchivo)) {
            // TODO: Reimplement document insertion when DocumentoModel is recreated
            /*
            $this->documentoModel->insert([
                'proyecto_id' => $proyectoId,
                'tipo_documento' => 'documento_general',
                'nombre_archivo' => $archivo->getClientName(),
                'ruta_archivo' => $rutaCompleta,
                'tamaño_archivo' => $archivo->getSize()
            ]);
            */
        }
    }
}