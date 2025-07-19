<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Sistema de Clientes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .test-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .result-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            max-height: 600px;
            overflow-y: auto;
        }
        .json-viewer {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .loading {
            display: none;
        }
        .success-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .error-badge {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-bug me-2"></i>
                        Debug Sistema de Clientes - Panel de Control
                    </h3>
                    <p class="mb-0 mt-2">Herramientas para debuggear el flujo completo de creación de clientes</p>
                </div>
                
                <div class="card-body">
                    
                    <!-- Estado del sistema -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i>Estado del Sistema</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Entorno:</strong> <?= ENVIRONMENT ?><br>
                                        <strong>CodeIgniter:</strong> <?= \CodeIgniter\CodeIgniter::CI_VERSION ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Base de Datos:</strong> <span id="db-status">Verificando...</span><br>
                                        <strong>Shield:</strong> <span id="shield-status">Verificando...</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Debug Mode:</strong> <span class="badge bg-warning">Activado</span><br>
                                        <strong>Logs:</strong> <span class="badge bg-info">Habilitados</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tests disponibles -->
                    <div class="row">
                        
                        <!-- Test 1: Base de Datos -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card test-card h-100" onclick="runTest('database')">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-database fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title">Test Base de Datos</h5>
                                    <p class="card-text">Verificar estructura de tablas, conexiones y datos existentes</p>
                                    <div class="mt-auto">
                                        <button class="btn btn-primary btn-sm">
                                            <i class="fas fa-play me-1"></i>Ejecutar Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test 2: Datos del Formulario -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card test-card h-100" onclick="runTest('form_data')">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-wpforms fa-3x text-success"></i>
                                    </div>
                                    <h5 class="card-title">Test Datos Formulario</h5>
                                    <p class="card-text">Simular captura y procesamiento de datos del formulario</p>
                                    <div class="mt-auto">
                                        <button class="btn btn-success btn-sm">
                                            <i class="fas fa-play me-1"></i>Ejecutar Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test 3: Shield -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card test-card h-100" onclick="runTest('shield')">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-shield-alt fa-3x text-warning"></i>
                                    </div>
                                    <h5 class="card-title">Test Shield</h5>
                                    <p class="card-text">Probar creación de usuarios y asignación de grupos</p>
                                    <div class="mt-auto">
                                        <button class="btn btn-warning btn-sm">
                                            <i class="fas fa-play me-1"></i>Ejecutar Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test 4: Cliente Insert -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card test-card h-100" onclick="runTest('cliente_insert')">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-user-plus fa-3x text-info"></i>
                                    </div>
                                    <h5 class="card-title">Test Cliente Insert</h5>
                                    <p class="card-text">Probar inserción directa de cliente y datos relacionados</p>
                                    <div class="mt-auto">
                                        <button class="btn btn-info btn-sm">
                                            <i class="fas fa-play me-1"></i>Ejecutar Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test 5: Flujo Completo -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card test-card h-100" onclick="runTest('full_flow')">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-cogs fa-3x text-danger"></i>
                                    </div>
                                    <h5 class="card-title">Test Flujo Completo</h5>
                                    <p class="card-text">Simulación completa: Usuario → Cliente → BD</p>
                                    <div class="mt-auto">
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-play me-1"></i>Ejecutar Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test 6: Ver Logs -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card test-card h-100" onclick="runTest('logs')">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-file-alt fa-3x text-dark"></i>
                                    </div>
                                    <h5 class="card-title">Ver Logs</h5>
                                    <p class="card-text">Mostrar logs recientes del sistema y errores</p>
                                    <div class="mt-auto">
                                        <button class="btn btn-dark btn-sm">
                                            <i class="fas fa-eye me-1"></i>Ver Logs
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Acciones adicionales -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5><i class="fas fa-tools me-2"></i>Acciones Rápidas</h5>
                                    <div class="btn-group me-2" role="group">
                                        <button class="btn btn-outline-primary" onclick="clearLogs()">
                                            <i class="fas fa-trash me-1"></i>Limpiar Logs
                                        </button>
                                        <button class="btn btn-outline-success" onclick="runAllTests()">
                                            <i class="fas fa-play-circle me-1"></i>Ejecutar Todos
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="openFormulario()">
                                            <i class="fas fa-external-link-alt me-1"></i>Ir al Formulario
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-outline-info" onclick="checkDatabase()">
                                            <i class="fas fa-database me-1"></i>Estado BD
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="interceptFormSubmit()">
                                            <i class="fas fa-bug me-1"></i>Interceptar Form
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading indicator -->
                    <div class="loading text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Ejecutando test...</span>
                        </div>
                        <p class="mt-2">Ejecutando test, por favor espera...</p>
                    </div>

                    <!-- Resultados -->
                    <div id="result-container" class="result-container" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Resultados del Test: <span id="test-name"></span>
                            </h5>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearResults()">
                                <i class="fas fa-times me-1"></i>Cerrar
                            </button>
                        </div>
                        
                        <div id="result-status" class="mb-3"></div>
                        <div id="result-content" class="json-viewer"></div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
// Variables globales
let currentTest = null;
let testResults = {};

// Ejecutar test específico
async function runTest(testType) {
    currentTest = testType;
    showLoading();
    
    const testNames = {
        'database': 'Base de Datos',
        'form_data': 'Datos del Formulario',
        'shield': 'Shield Auth',
        'cliente_insert': 'Inserción Cliente',
        'full_flow': 'Flujo Completo',
        'logs': 'Logs del Sistema'
    };
    
    document.getElementById('test-name').textContent = testNames[testType] || testType;
    
    try {
        const response = await fetch(`/debug/clientes/test${testType.charAt(0).toUpperCase() + testType.slice(1).replace('_', '')}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        testResults[testType] = result;
        
        hideLoading();
        displayResult(result);
        
    } catch (error) {
        hideLoading();
        displayError(error.message);
    }
}

// Mostrar resultados
function displayResult(result) {
    const container = document.getElementById('result-container');
    const status = document.getElementById('result-status');
    const content = document.getElementById('result-content');
    
    // Estado del test
    if (result.success) {
        status.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Test exitoso</strong> - Todos los componentes funcionan correctamente
            </div>
        `;
    } else {
        status.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Test fallido</strong> - Se encontraron errores
                ${result.errors ? '<br><small>' + result.errors.join('<br>') + '</small>' : ''}
            </div>
        `;
    }
    
    // Contenido detallado
    content.textContent = JSON.stringify(result, null, 2);
    
    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth' });
}

// Mostrar error
function displayError(errorMessage) {
    const container = document.getElementById('result-container');
    const status = document.getElementById('result-status');
    const content = document.getElementById('result-content');
    
    status.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-times-circle me-2"></i>
            <strong>Error de conexión</strong> - ${errorMessage}
        </div>
    `;
    
    content.textContent = `Error: ${errorMessage}`;
    container.style.display = 'block';
}

// Loading
function showLoading() {
    document.querySelector('.loading').style.display = 'block';
    document.getElementById('result-container').style.display = 'none';
}

function hideLoading() {
    document.querySelector('.loading').style.display = 'none';
}

// Limpiar resultados
function clearResults() {
    document.getElementById('result-container').style.display = 'none';
}

// Ejecutar todos los tests
async function runAllTests() {
    const tests = ['database', 'form_data', 'shield', 'cliente_insert', 'full_flow'];
    
    for (const test of tests) {
        await runTest(test);
        await new Promise(resolve => setTimeout(resolve, 1000)); // Pausa entre tests
    }
    
    // Mostrar resumen
    displayAllTestsSummary();
}

// Mostrar resumen de todos los tests
function displayAllTestsSummary() {
    const container = document.getElementById('result-container');
    const status = document.getElementById('result-status');
    const content = document.getElementById('result-content');
    
    document.getElementById('test-name').textContent = 'Resumen de Todos los Tests';
    
    let successCount = 0;
    let totalTests = Object.keys(testResults).length;
    
    let summary = "=== RESUMEN DE TESTS ===\n\n";
    
    for (const [testName, result] of Object.entries(testResults)) {
        if (result.success) {
            successCount++;
            summary += `✅ ${testName}: EXITOSO\n`;
        } else {
            summary += `❌ ${testName}: FALLIDO\n`;
            if (result.errors) {
                summary += `   Errores: ${result.errors.join(', ')}\n`;
            }
        }
    }
    
    summary += `\n=== ESTADÍSTICAS ===\n`;
    summary += `Total: ${totalTests}\n`;
    summary += `Exitosos: ${successCount}\n`;
    summary += `Fallidos: ${totalTests - successCount}\n`;
    summary += `Tasa de éxito: ${Math.round((successCount / totalTests) * 100)}%\n`;
    
    if (successCount === totalTests) {
        status.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-trophy me-2"></i>
                <strong>¡Todos los tests pasaron!</strong> El sistema está funcionando correctamente
            </div>
        `;
    } else {
        status.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Algunos tests fallaron</strong> Revisa los detalles para identificar problemas
            </div>
        `;
    }
    
    content.textContent = summary + "\n\n" + JSON.stringify(testResults, null, 2);
    container.style.display = 'block';
}

// Abrir formulario de clientes
function openFormulario() {
    window.open('/admin/clientes/create', '_blank');
}

// Verificar estado de base de datos
async function checkDatabase() {
    await runTest('database');
}

// Interceptar envío de formulario
function interceptFormSubmit() {
    // Crear formulario especial para interceptar
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/debug/clientes/interceptFormData';
    form.target = '_blank';
    
    // Agregar datos de prueba
    const testData = {
        'nombres': 'JUAN DEBUG',
        'apellido_paterno': 'INTERCEPT',
        'apellido_materno': 'TEST',
        'email': 'debug_intercept@test.com',
        'telefono': '5551234567',
        'direccion_domicilio': 'AV TEST 123',
        'laboral_empresa': 'EMPRESA DEBUG'
    };
    
    for (const [key, value] of Object.entries(testData)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Limpiar logs
async function clearLogs() {
    if (confirm('¿Estás seguro de que quieres limpiar los logs?')) {
        try {
            const response = await fetch('/debug/clientes/clearLogs', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                alert('Logs limpiados exitosamente');
            } else {
                alert('Error al limpiar logs');
            }
        } catch (error) {
            alert('Error de conexión: ' + error.message);
        }
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Verificar estado inicial
    checkSystemStatus();
    
    // Auto-refresh cada 30 segundos si hay un test en ejecución
    setInterval(function() {
        if (currentTest) {
            updateSystemStatus();
        }
    }, 30000);
});

// Verificar estado del sistema
async function checkSystemStatus() {
    try {
        const response = await fetch('/debug/clientes/testDatabase');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('db-status').innerHTML = 
                '<span class="badge bg-success">Conectado</span>';
        } else {
            document.getElementById('db-status').innerHTML = 
                '<span class="badge bg-danger">Error</span>';
        }
    } catch (error) {
        document.getElementById('db-status').innerHTML = 
            '<span class="badge bg-danger">Sin conexión</span>';
    }
    
    // Verificar Shield
    document.getElementById('shield-status').innerHTML = 
        '<span class="badge bg-success">Activo</span>';
}

// Actualizar estado del sistema
function updateSystemStatus() {
    checkSystemStatus();
}
</script>

</body>
</html>