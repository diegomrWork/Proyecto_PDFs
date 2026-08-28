<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Inertia;
Route::get('/', function () {
    return Inertia::render('Inmatriculacion', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\InmatriculacionController;

// Esta es la ruta para ver el formulario
Route::get('/inmatriculacion', [InmatriculacionController::class, 'index'])->name('inmatriculacion.index');
// Ruta POST para recibir los datos y crear el PDF
Route::post('/inmatriculacion/generar', [InmatriculacionController::class, 'generarPdf']);

Route::get('/consultar-dni/{dni}', [InmatriculacionController::class, 'consultarDni']);
Route::get('/consultar-ruc/{ruc}', [InmatriculacionController::class, 'consultarRuc']);