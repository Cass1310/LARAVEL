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
use App\Http\Controllers\SimuladorLoRaController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AuditoriaController;

Route::get('/', function () {
    return view('welcome');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auditoria.logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        return view('dashboard', compact('user'));
    })->name('dashboard');

    Route::middleware(['role:administrador'])->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware(['role:administrador,propietario'])->group(function () {
        Route::resource('edificios', EdificioController::class);
    });

    Route::resource('departamentos', DepartamentoController::class)->middleware('can:viewAny,App\Models\Departamento');
});

// Rutas de consumos
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('consumos-edificio', ConsumoEdificioController::class)
        ->middleware('can:viewAny,App\Models\ConsumoEdificio');
    
    Route::post('consumos-edificio/{consumoEdificio}/pay', [ConsumoEdificioController::class, 'markAsPaid'])
        ->name('consumos-edificio.pay');

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
    // En rutas de residente
    Route::get('/consumo/{id}/imprimir', [ResidenteController::class, 'imprimirConsumo'])->name('consumo.imprimir');
    Route::get('/reportes/exportar-pdf', [ResidenteController::class, 'exportarReportePdf'])->name('reportes.exportar-pdf');
    Route::get('/reportes/exportar-excel', [ResidenteController::class, 'exportarReporteExcel'])->name('reportes.exportar-excel');
    Route::get('/historico-consumos', [ResidenteController::class, 'historicoConsumos'])->name('historico.consumos');
    Route::get('/historico-consumos/excel', [ResidenteController::class, 'exportarHistoricoExcel'])->name('historico.excel');
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
    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/', [PropietarioController::class, 'pagos'])->name('index');
        Route::get('/{consumo}/pagar', [PropietarioController::class, 'mostrarPago'])->name('mostrar');
        Route::post('/{consumo}/procesar', [PropietarioController::class, 'procesarPago'])->name('procesar');
        Route::get('/{consumo}/detalle', [PropietarioController::class, 'detallePago'])->name('detalle');
    });
    Route::prefix('mantenimientos')->name('mantenimientos.')->group(function () {
        Route::get('/crear', [PropietarioController::class, 'crearMantenimiento'])->name('crear');
        Route::post('/', [PropietarioController::class, 'guardarMantenimiento'])->name('guardar');
        Route::get('/{mantenimiento}/editar', [PropietarioController::class, 'editarMantenimiento'])->name('editar');
        Route::put('/{mantenimiento}', [PropietarioController::class, 'actualizarMantenimiento'])->name('actualizar');
        Route::delete('/{mantenimiento}', [PropietarioController::class, 'eliminarMantenimiento'])->name('eliminar');
    });
    // Reportes de notas de consumo
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/edificios/{edificio}/notas-consumo', [PropietarioController::class, 'reporteNotasConsumo'])->name('notas-consumo');
        Route::get('/edificios/{edificio}/notas-consumo/exportar-pdf', [PropietarioController::class, 'exportarNotasConsumoPdf'])->name('notas-consumo.exportar-pdf');
        Route::get('/edificios/{edificio}/notas-consumo/exportar-excel', [PropietarioController::class, 'exportarNotasConsumoExcel'])->name('notas-consumo.exportar-excel');
        Route::get('/todas-notas-consumo', [PropietarioController::class, 'reporteTodasNotasConsumo'])->name('todas-notas-consumo');
        Route::get('/todas-notas-consumo/exportar-pdf', [PropietarioController::class, 'exportarTodasNotasConsumoPdf'])->name('todas-notas-consumo.exportar-pdf');
        Route::get('/todas-notas-consumo/exportar-excel', [PropietarioController::class, 'exportarTodasNotasConsumoExcel'])->name('todas-notas-consumo.exportar-excel');
    });
    // En rutas de propietario
    Route::get('/reportes/exportar-pdf', [PropietarioController::class, 'exportarReportePdf'])->name('reportes.exportar-pdf');
    Route::get('/reportes/exportar-excel', [PropietarioController::class, 'exportarReporteExcel'])->name('reportes.exportar-excel');
    Route::get('/reportes/exportar-detallado-excel', [PropietarioController::class, 'exportarReporteDetalladoExcel'])->name('reportes.exportar-detallado-excel');
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
// Rutas para Administrador
Route::middleware(['auth', 'verified', 'role:administrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
        Route::get('/usuarios/crear', [AdminController::class, 'crearUsuario'])->name('usuarios.crear');
        Route::post('/usuarios', [AdminController::class, 'guardarUsuario'])->name('usuarios.guardar');
        Route::get('/usuarios/{user}/editar', [AdminController::class, 'editarUsuario'])->name('usuarios.editar');
        Route::put('/usuarios/{user}', [AdminController::class, 'actualizarUsuario'])->name('usuarios.actualizar');
        Route::delete('/usuarios/{user}', [AdminController::class, 'eliminarUsuario'])->name('usuarios.eliminar');
        Route::post('/usuarios/{user}/desvincular', [AdminController::class, 'desvincularDepartamentos'])->name('usuarios.desvincular');

        Route::get('/propietarios', [AdminController::class, 'propietarios'])->name('propietarios');
        Route::get('/propietarios/{user}', [AdminController::class, 'propietarioShow'])->name('propietarios.show');
        Route::post('/propietarios/{user}/edificios', [AdminController::class, 'crearEdificio'])->name('edificios.crear');
        Route::delete('/edificios/{edificio}', [AdminController::class, 'eliminarEdificio'])->name('edificios.eliminar');

        Route::post('/edificios/{edificio}/departamentos', [AdminController::class, 'crearDepartamento'])->name('departamentos.crear');
        Route::delete('/departamentos/{departamento}', [AdminController::class, 'eliminarDepartamento'])->name('departamentos.eliminar');
        Route::post('/departamentos/{departamento}/residentes', [AdminController::class, 'asignarResidente'])->name('residentes.asignar');
        Route::post('/departamentos/{departamento}/residentes/{residente}/desvincular', [AdminController::class, 'desvincularResidente'])->name('residentes.desvincular');

        Route::get('/equipos', [AdminController::class, 'equipos'])->name('equipos');

        Route::get('/medidores', [AdminController::class, 'medidores'])->name('medidores');
        Route::get('/medidores/crear', [AdminController::class, 'crearMedidor'])->name('medidores.crear');
        Route::post('/medidores', [AdminController::class, 'guardarMedidor'])->name('medidores.guardar');
        Route::get('/medidores/{medidor}/editar', [AdminController::class, 'editarMedidor'])->name('medidores.editar');
        Route::put('/medidores/{medidor}', [AdminController::class, 'actualizarMedidor'])->name('medidores.actualizar');
        Route::delete('/medidores/{medidor}', [AdminController::class, 'eliminarMedidor'])->name('medidores.eliminar');
        Route::post('/medidores/{medidor}/asignar-gateway', [AdminController::class, 'asignarGateway'])->name('medidores.asignar-gateway');

        Route::get('/gateways', [AdminController::class, 'gateways'])->name('gateways');
        Route::get('/gateways/crear', [AdminController::class, 'crearGateway'])->name('gateways.crear');
        Route::post('/gateways', [AdminController::class, 'guardarGateway'])->name('gateways.guardar');
        Route::get('/gateways/{gateway}/editar', [AdminController::class, 'editarGateway'])->name('gateways.editar');
        Route::put('/gateways/{gateway}', [AdminController::class, 'actualizarGateway'])->name('gateways.actualizar');
        Route::delete('/gateways/{gateway}', [AdminController::class, 'eliminarGateway'])->name('gateways.eliminar');

        Route::get('/mantenimientos', [AdminController::class, 'mantenimientos'])->name('mantenimientos');
        Route::post('/mantenimientos/{mantenimiento}/atender', [AdminController::class, 'atenderMantenimiento'])->name('mantenimientos.atender');
        Route::post('/mantenimientos/{mantenimiento}/completar', [AdminController::class, 'completarMantenimiento'])->name('mantenimientos.completar');

        Route::get('/alertas', [AdminController::class, 'alertas'])->name('alertas');
        Route::post('/alertas/{alerta}/atender', [AdminController::class, 'atenderAlerta'])->name('alertas.atender');
        Route::post('/alertas/{alerta}/resolver', [AdminController::class, 'resolverAlerta'])->name('alertas.resolver');

        Route::get('/suscripciones', [AdminController::class, 'suscripciones'])->name('suscripciones');
        Route::get('/suscripciones/{suscripcion}/pagos', [AdminController::class, 'gestionarPagosSuscripcion'])->name('suscripciones.pagos');
        Route::post('/suscripciones/{suscripcion}/pagos', [AdminController::class, 'registrarPagoSuscripcion'])->name('suscripciones.pagos.registrar');

        Route::get('/reportes', [AdminController::class, 'reportes'])->name('reportes');
        // Alertas
        Route::get('/alertas/exportar-pdf', [AdminController::class, 'exportarAlertasPdf'])->name('alertas.exportar-pdf');
        Route::get('/alertas/exportar-excel', [AdminController::class, 'exportarAlertasExcel'])->name('alertas.exportar-excel');

        // Mantenimientos
        Route::get('/mantenimientos/exportar-pdf', [AdminController::class, 'exportarMantenimientosPdf'])->name('mantenimientos.exportar-pdf');
        Route::get('/mantenimientos/exportar-excel', [AdminController::class, 'exportarMantenimientosExcel'])->name('mantenimientos.exportar-excel');

        // Medidores
        Route::get('/medidores/exportar-pdf', [AdminController::class, 'exportarMedidoresPdf'])->name('medidores.exportar-pdf');
        Route::get('/medidores/exportar-excel', [AdminController::class, 'exportarMedidoresExcel'])->name('medidores.exportar-excel');

        // Gateways
        Route::get('/gateways/exportar-pdf', [AdminController::class, 'exportarGatewaysPdf'])->name('gateways.exportar-pdf');
        Route::get('/gateways/exportar-excel', [AdminController::class, 'exportarGatewaysExcel'])->name('gateways.exportar-excel');
        // En rutas de admin
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::post('/create', [BackupController::class, 'create'])->name('create');
            Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
            Route::delete('/delete/{filename}', [BackupController::class, 'delete'])->name('delete');
            Route::post('/clean', [BackupController::class, 'clean'])->name('clean');
        });
        Route::prefix('simular')->group(function () {
            Route::post('/lectura', [SimuladorLoRaController::class, 'simularLectura']);
            Route::post('/lecturas-masivas', [SimuladorLoRaController::class, 'generarLecturasMasivas']);
        });
        // En rutas de admin
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{id}', [AuditoriaController::class, 'show'])->name('auditoria.show');
        Route::get('/auditoria/usuario/{userId}', [AuditoriaController::class, 'usuario'])->name('auditoria.usuario');
    });
require __DIR__.'/auth.php';
