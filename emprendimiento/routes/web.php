<?php
use App\Http\Controllers\EdificioController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FacturaDepartamentoController;
use App\Http\Controllers\FacturaEdificioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

// Rutas de facturas
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Facturas de edificio
    Route::resource('facturas-edificio', FacturaEdificioController::class)
        ->middleware('can:viewAny,App\Models\FacturaEdificio');
    
    Route::post('facturas-edificio/{facturaEdificio}/pay', [FacturaEdificioController::class, 'markAsPaid'])
        ->name('facturas-edificio.pay');

    // Facturas de departamento
    Route::get('facturas-departamento', [FacturaDepartamentoController::class, 'index'])
        ->name('facturas-departamento.index')
        ->middleware('can:viewAny,App\Models\FacturaDepartamento');
    
    Route::get('facturas-departamento/{facturaDepartamento}', [FacturaDepartamentoController::class, 'show'])
        ->name('facturas-departamento.show');
    
    Route::post('facturas-departamento/{facturaDepartamento}/pay', [FacturaDepartamentoController::class, 'pay'])
        ->name('facturas-departamento.pay');
});


// Rutas para residentes
Route::middleware(['auth', 'verified', 'role:residente'])->prefix('residente')->name('residente.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/departamento', [ResidenteController::class, 'departamento'])->name('departamento');
    Route::get('/alertas', [ResidenteController::class, 'alertas'])->name('alertas');
    Route::get('/mantenimientos', [ResidenteController::class, 'mantenimientos'])->name('mantenimientos');
    Route::get('/reportes', [ResidenteController::class, 'reportes'])->name('reportes');
});

// Rutas para propietarios
Route::middleware(['auth', 'verified', 'role:propietario'])->prefix('propietario')->name('propietario.')->group(function () {
    Route::get('/dashboard', [PropietarioController::class, 'dashboard'])->name('dashboard');
    Route::get('/edificios', [PropietarioController::class, 'edificios'])->name('edificios');
    Route::get('/edificios/{edificio}', [PropietarioController::class, 'edificioShow'])->name('edificios.show');
    Route::get('/facturas', [PropietarioController::class, 'facturas'])->name('facturas');
    Route::get('/facturas/crear', [PropietarioController::class, 'crearFactura'])->name('facturas.crear');
    Route::post('/facturas', [PropietarioController::class, 'guardarFactura'])->name('facturas.guardar');
    Route::post('/facturas/{factura}/pagar', [PropietarioController::class, 'pagarFactura'])->name('facturas.pagar');
    Route::get('/residentes', [PropietarioController::class, 'residentes'])->name('residentes');
    Route::get('/residentes/crear', [PropietarioController::class, 'crearResidente'])->name('residentes.crear');
    Route::post('/residentes', [PropietarioController::class, 'guardarResidente'])->name('residentes.guardar');
    Route::get('/alertas', [PropietarioController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{alerta}/resolver', action: [PropietarioController::class, 'resolverAlerta'])->name('alertas.resolver');
    Route::get('/mantenimientos', [PropietarioController::class, 'mantenimientos'])->name('mantenimientos');
    Route::get('/mantenimientos/crear', [PropietarioController::class, 'crearMantenimiento'])->name('mantenimientos.crear');
    Route::post('/mantenimientos', [PropietarioController::class, 'guardarMantenimiento'])->name('mantenimientos.guardar');
    Route::get('/reportes', [PropietarioController::class, 'reportes'])->name('reportes');
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
});
require __DIR__.'/auth.php';
