<?php
use App\Http\Controllers\EdificioController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ConsumoDepartamentoController;
use App\Http\Controllers\ConsumoEdificioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard según rol
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        return view('dashboard', compact('user'));
    })->name('dashboard');

    // Rutas de administrador
    Route::middleware(['role:administrador'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Rutas de propietario y admin
    Route::middleware(['role:administrador,propietario'])->group(function () {
        Route::resource('edificios', EdificioController::class);
    });

    // Rutas para todos los autenticados pero con policies
    Route::resource('departamentos', DepartamentoController::class)->middleware('can:viewAny,App\Models\Departamento');
});

// Rutas de consumos
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Consumos de edificio
    Route::resource('consumos-edificio', ConsumoEdificioController::class)
        ->middleware('can:viewAny,App\Models\ConsumoEdificio');
    
    Route::post('consumos-edificio/{consumoEdificio}/pay', [ConsumoEdificioController::class, 'markAsPaid'])
        ->name('consumos-edificio.pay');

    // Consumos de departamento
    Route::get('consumos-departamento', [ConsumoDepartamentoController::class, 'index'])
        ->name('consumos-departamento.index')
        ->middleware('can:viewAny,App\Models\ConsumoDepartamento');
    
    Route::get('consumos-departamento/{consumoDepartamento}', [ConsumoDepartamentoController::class, 'show'])
        ->name('consumos-departamento.show');
    
    Route::post('consumos-departamento/{consumoDepartamento}/pay', [ConsumoDepartamentoController::class, 'pay'])
        ->name('consumos-departamento.pay');
});


// Rutas para residentes
Route::middleware(['auth', 'verified', 'role:residente'])->prefix('residente')->name('residente.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/departamento', [ResidenteController::class, 'departamento'])->name('departamento');
    Route::get('/alertas', [ResidenteController::class, 'alertas'])->name('alertas');
    Route::get('/mantenimientos', [ResidenteController::class, 'mantenimientos'])->name('mantenimientos');
    Route::get('/reportes', [ResidenteController::class, 'reportes'])->name('reportes');
    Route::post('/mantenimiento', [ResidenteController::class, 'solicitarMantenimiento'])
    ->name('solicitar.mantenimiento');
});

// Rutas para propietarios
Route::middleware(['auth', 'verified', 'role:propietario'])->prefix('propietario')->name('propietario.')->group(function () {
    Route::get('/dashboard', [PropietarioController::class, 'dashboard'])->name('dashboard');
    Route::get('/edificios', [PropietarioController::class, 'edificios'])->name('edificios');
    Route::get('/edificios/{edificio}', [PropietarioController::class, 'edificioShow'])->name('edificios.show');
    Route::get('/consumos', [PropietarioController::class, 'consumos'])->name('consumos');
    Route::get('/consumos/crear', [PropietarioController::class, 'crearConsumo'])->name('consumos.crear');
    Route::post('/consumos', [PropietarioController::class, 'guardarConsumo'])->name('consumos.guardar');
    Route::post('/consumos/{consumo}/pagar', [PropietarioController::class, 'pagarConsumo'])->name('consumos.pagar');
    Route::get('/residentes', [PropietarioController::class, 'residentes'])->name('residentes');
    Route::get('/residentes/crear', [PropietarioController::class, 'crearResidente'])->name('residentes.crear');
    Route::post('/residentes', [PropietarioController::class, 'guardarResidente'])->name('residentes.guardar');
    Route::get('/alertas', [PropietarioController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{alerta}/resolver', action: [PropietarioController::class, 'resolverAlerta'])->name('alertas.resolver');
    Route::get('/mantenimientos', [PropietarioController::class, 'mantenimientos'])->name('mantenimientos');
    Route::get('/mantenimientos/crear', [PropietarioController::class, 'crearMantenimiento'])->name('mantenimientos.crear');
    Route::post('/mantenimientos', [PropietarioController::class, 'guardarMantenimiento'])->name('mantenimientos.guardar');
    Route::get('/reportes', [PropietarioController::class, 'reportes'])->name('reportes');
    // Rutas de pagos para propietarios
    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/', [PropietarioController::class, 'pagos'])->name('index');
        Route::get('/{consumo}/pagar', [PropietarioController::class, 'mostrarPago'])->name('mostrar');
        Route::post('/{consumo}/procesar', [PropietarioController::class, 'procesarPago'])->name('procesar');
        Route::get('/{consumo}/detalle', [PropietarioController::class, 'detallePago'])->name('detalle');
    });
    // Rutas de mantenimientos
    Route::prefix('mantenimientos')->name('mantenimientos.')->group(function () {
        Route::get('/crear', [PropietarioController::class, 'crearMantenimiento'])->name('crear');
        Route::post('/', [PropietarioController::class, 'guardarMantenimiento'])->name('guardar');
        Route::get('/{mantenimiento}/editar', [PropietarioController::class, 'editarMantenimiento'])->name('editar');
        Route::put('/{mantenimiento}', [PropietarioController::class, 'actualizarMantenimiento'])->name('actualizar');
        Route::delete('/{mantenimiento}', [PropietarioController::class, 'eliminarMantenimiento'])->name('eliminar');
    });

    // Rutas para cargar datos dinámicos
    Route::get('/edificios/{edificio}/departamentos', [PropietarioController::class, 'departamentosPorEdificio']);
    Route::get('/departamentos/{departamento}/medidores', [PropietarioController::class, 'medidoresPorDepartamento']);
});

// Rutas de suscripción
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/suscripcion', [SuscripcionController::class, 'index'])->name('suscripcion.index');
    Route::get('/suscripcion/crear', [SuscripcionController::class, 'crear'])->name('suscripcion.crear');
    Route::post('/suscripcion', [SuscripcionController::class, 'store'])->name('suscripcion.store');
    Route::post('/suscripcion/{suscripcion}/renovar', [SuscripcionController::class, 'renovar'])->name('suscripcion.renovar');
    Route::delete('/suscripcion/{suscripcion}/cancelar', [SuscripcionController::class, 'cancelar'])->name('suscripcion.cancelar');
    Route::get('/suscripcion/{suscripcion}/pagos', [SuscripcionController::class, 'pagos'])->name('suscripcion.pagos');
    Route::post('/suscripcion/{suscripcion}/pagos/{pago}/pagar', [SuscripcionController::class, 'pagarPago'])->name('suscripcion.pagos.pagar');
});
// Rutas para administrador
Route::middleware(['auth', 'verified', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Usuarios
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
    Route::get('/usuarios/crear', [AdminController::class, 'crearUsuario'])->name('usuarios.crear');
    Route::post('/usuarios', [AdminController::class, 'guardarUsuario'])->name('usuarios.guardar');
    Route::get('/usuarios/{user}/editar', [AdminController::class, 'editarUsuario'])->name('usuarios.editar');
    Route::put('/usuarios/{user}', [AdminController::class, 'actualizarUsuario'])->name('usuarios.actualizar');
    Route::delete('/usuarios/{user}', [AdminController::class, 'eliminarUsuario'])->name('usuarios.eliminar');
    
    // Edificios
    Route::get('/edificios', [AdminController::class, 'edificios'])->name('edificios');
    Route::get('/edificios/crear', [AdminController::class, 'crearEdificio'])->name('edificios.crear');
    Route::post('/edificios', [AdminController::class, 'guardarEdificio'])->name('edificios.guardar');
    
    // Departamentos
    Route::get('/departamentos/crear', [AdminController::class, 'crearDepartamento'])->name('departamentos.crear');
    Route::post('/departamentos', [AdminController::class, 'guardarDepartamento'])->name('departamentos.guardar');
    
    // Medidores
    Route::get('/medidores/crear', [AdminController::class, 'crearMedidor'])->name('medidores.crear');
    Route::post('/medidores', [AdminController::class, 'guardarMedidor'])->name('medidores.guardar');
    
    // Gateways
    Route::get('/gateways/crear', [AdminController::class, 'crearGateway'])->name('gateways.crear');
    Route::post('/gateways', [AdminController::class, 'guardarGateway'])->name('gateways.guardar');
    
    // Suscripciones
    Route::get('/suscripciones', [AdminController::class, 'suscripciones'])->name('suscripciones');
    Route::get('/suscripciones/{suscripcion}/pagos', [AdminController::class, 'gestionarPagosSuscripcion'])->name('suscripciones.pagos');
    Route::post('/suscripciones/{suscripcion}/pagos', [AdminController::class, 'registrarPagoSuscripcion'])->name('suscripciones.pagos.registrar');
    
    // Alertas
    Route::get('/alertas', [AdminController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{alerta}/resolver', [AdminController::class, 'resolverAlerta'])->name('alertas.resolver');
    
    // Mantenimientos
    Route::get('/mantenimientos', [AdminController::class, 'mantenimientos'])->name('mantenimientos');
    Route::post('/mantenimientos/{mantenimiento}/completar', [AdminController::class, 'completarMantenimiento'])->name('mantenimientos.completar');
    
    // Reportes
    Route::get('/reportes', [AdminController::class, 'reportes'])->name('reportes');
});// Rutas para administrador
Route::middleware(['auth', 'verified', 'role:administrador'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Gestión de Usuarios
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
    Route::get('/usuarios/crear', [AdminController::class, 'crearUsuario'])->name('usuarios.crear');
    Route::post('/usuarios', [AdminController::class, 'guardarUsuario'])->name('usuarios.guardar');
    Route::get('/usuarios/{user}/editar', [AdminController::class, 'editarUsuario'])->name('usuarios.editar');
    Route::put('/usuarios/{user}', [AdminController::class, 'actualizarUsuario'])->name('usuarios.actualizar');
    Route::delete('/usuarios/{user}', [AdminController::class, 'eliminarUsuario'])->name('usuarios.eliminar');
    Route::post('/usuarios/{user}/desvincular', [AdminController::class, 'desvincularDepartamentos'])->name('usuarios.desvincular');
    
    // Gestión de Propietarios y Edificios
    Route::get('/propietarios', [AdminController::class, 'propietarios'])->name('propietarios');
    Route::get('/propietarios/{user}', [AdminController::class, 'propietarioShow'])->name('propietarios.show');
    Route::post('/propietarios/{user}/edificios', [AdminController::class, 'crearEdificio'])->name('edificios.crear');
    Route::post('/edificios/{edificio}/departamentos', [AdminController::class, 'crearDepartamento'])->name('departamentos.crear');
    Route::post('/departamentos/{departamento}/residentes', [AdminController::class, 'asignarResidente'])->name('residentes.asignar');
    Route::delete('/edificios/{edificio}', [AdminController::class, 'eliminarEdificio'])->name('edificios.eliminar');
    Route::delete('/departamentos/{departamento}', [AdminController::class, 'eliminarDepartamento'])->name('departamentos.eliminar');
    Route::post('/departamentos/{departamento}/residentes/{residente}/desvincular', [AdminController::class, 'desvincularResidente'])->name('residentes.desvincular');
    // Gestión de Equipos
    Route::get('/equipos', [AdminController::class, 'equipos'])->name('equipos');
    Route::get('/medidores', [AdminController::class, 'medidores'])->name('medidores');
    Route::get('/medidores/crear', [AdminController::class, 'crearMedidor'])->name('medidores.crear');
    Route::post('/medidores', [AdminController::class, 'guardarMedidor'])->name('medidores.guardar');
    Route::post('/medidores/{medidor}/asignar-gateway', [AdminController::class, 'asignarGateway'])->name('medidores.asignar-gateway');
    Route::get('/gateways', [AdminController::class, 'gateways'])->name('gateways');
    Route::get('/gateways/crear', [AdminController::class, 'crearGateway'])->name('gateways.crear');
    Route::post('/gateways', [AdminController::class, 'guardarGateway'])->name('gateways.guardar');
    Route::get('/medidores/{medidor}/editar', [AdminController::class, 'editarMedidor'])->name('medidores.editar');
    Route::put('/medidores/{medidor}', [AdminController::class, 'actualizarMedidor'])->name('medidores.actualizar');
    Route::delete('/medidores/{medidor}', [AdminController::class, 'eliminarMedidor'])->name('medidores.eliminar');
    Route::get('/gateways/{gateway}/editar', [AdminController::class, 'editarGateway'])->name('gateways.editar');
    Route::put('/gateways/{gateway}', [AdminController::class, 'actualizarGateway'])->name('gateways.actualizar');
    Route::delete('/gateways/{gateway}', [AdminController::class, 'eliminarGateway'])->name('gateways.eliminar');
    // Gestión de Mantenimientos
    Route::get('/mantenimientos', [AdminController::class, 'mantenimientos'])->name('mantenimientos');
    Route::post('/mantenimientos/{mantenimiento}/atender', [AdminController::class, 'atenderMantenimiento'])->name('mantenimientos.atender');
    
    // Gestión de Alertas
    Route::get('/alertas', [AdminController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{alerta}/atender', [AdminController::class, 'atenderAlerta'])->name('alertas.atender');
    
    // Suscripciones
    Route::get('/suscripciones', [AdminController::class, 'suscripciones'])->name('suscripciones');
    
    // Reportes
    Route::get('/reportes', [AdminController::class, 'reportes'])->name('reportes');
});
require __DIR__.'/auth.php';
