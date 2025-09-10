<?php
use App\Http\Controllers\EdificioController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FacturaDepartamentoController;
use App\Http\Controllers\FacturaEdificioController;
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
require __DIR__.'/auth.php';
