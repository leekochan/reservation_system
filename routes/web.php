<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\admin\AdminEquipmentsController;
use App\Http\Controllers\admin\AdminFacilityController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});

Route::get('/user', [DashboardController::class, 'userDashboard']);
Route::get('/facilities', [FacilityController::class, 'userFacilities']);
Route::get('/equipments', [EquipmentsController::class, 'userEquipments']);
Route::get('/reservation', [ReservationController::class, 'userReservation']);

Route::get('/calendar_of_activities', [CalendarController::class, 'calendar']);

Route::get('/admin', [AdminDashboardController::class, 'adminDashboard']);
Route::get('/admin/facilities', [AdminFacilityController::class, 'adminFacilities']);
Route::get('/admin/equipments', [AdminEquipmentsController::class, 'adminEquipments']);
Route::get('/admin/facilities/manage-facilities', [AdminFacilityController::class, 'adminManageFacilities']);
Route::get('/admin/equipments/manage-equipments', [AdminEquipmentsController::class, 'adminManageEquipments']);


Route::get('/admin/facilities/manage-facilities', [AdminFacilityController::class, 'adminManageFacilities'])->name('admin.facilities.manage');
// Add a new facility
Route::post('/admin/facilities', [AdminFacilityController::class, 'store'])->name('facilities.store');
// Update an existing facility
Route::put('/admin/facilities/{id}', [AdminFacilityController::class, 'update'])->name('facilities.update');
// Delete an existing facility
Route::delete('/admin/facilities/{id}', [AdminFacilityController::class, 'destroy'])->name('facilities.destroy');
