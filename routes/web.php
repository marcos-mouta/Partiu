<?php

use App\Http\Controllers\OwnerVehicleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Público
Route::get('/', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/veiculos/{vehicle}', [VehicleController::class, 'show'])->whereNumber('vehicle')->name('vehicles.show');

// Autenticado
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('vehicles.index'))->name('dashboard');

    // Dono: anúncios
    Route::resource('meus-veiculos', OwnerVehicleController::class)
        ->except('show')
        ->parameters(['meus-veiculos' => 'vehicle'])
        ->names('owner.vehicles');

    // Dono: solicitações recebidas
    Route::get('/solicitacoes', [RentalController::class, 'incoming'])->name('rentals.incoming');
    Route::patch('/reservas/{rental}/aprovar', [RentalController::class, 'approve'])->name('rentals.approve');
    Route::patch('/reservas/{rental}/recusar', [RentalController::class, 'reject'])->name('rentals.reject');

    // Motorista
    Route::post('/veiculos/{vehicle}/alugar', [RentalController::class, 'store'])->name('rentals.store');
    Route::get('/minhas-reservas', [RentalController::class, 'index'])->name('rentals.index');
    Route::patch('/reservas/{rental}/cancelar', [RentalController::class, 'cancel'])->name('rentals.cancel');

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
