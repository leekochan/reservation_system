<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\FacilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/Reservation');
});

Route::get('/Reservation', [FacilityController::class, 'userDashboard']);

Route::get('/Reservation/Admin', function () {
    return view('dashboard');
});

Route::prefix('Reservation')->group(function () {
    Route::get('/Admin', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Add other admin routes under this prefix if needed
    Route::prefix('Admin')->group(function () {
        Route::get('/reservations', [ReservationController::class, 'index'])->name('admin.reservations.index');
        Route::get('/requests/pending', [RequestController::class, 'pending'])->name('admin.requests.pending');
        // Add more admin routes here
    });
});
