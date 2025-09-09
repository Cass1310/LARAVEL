<?php
use App\Http\Controllers\EdificioController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\MedidorController;
use App\Http\Controllers\ConsumoAguaController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('edificios', EdificioController::class);
Route::resource('departamentos', DepartamentoController::class);
Route::resource('medidores', MedidorController::class);
Route::resource('consumos', ConsumoAguaController::class);
Route::resource('alertas', AlertaController::class);
Route::resource('usuarios', UsuarioController::class);
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::post('/alertas/{alerta}/resolver', [AlertaController::class, 'resolver'])->name('alertas.resolver');
Route::get('/consumo-mensual', [ConsumoAguaController::class, 'mensual'])->name('consumos.mensual');
Route::get('/comparativa', [ConsumoAguaController::class, 'comparativa'])->name('consumos.comparativa');
