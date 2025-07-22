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

// Redirect to user dashboard
Route::get('/', function () {
    return redirect('/user');
});

// User routes
Route::prefix('user')->group(function () {
    Route::get('/', [DashboardController::class, 'userDashboard']);
    Route::get('/facilities', [FacilityController::class, 'userFacilities']);
    Route::get('/equipments', [EquipmentsController::class, 'userEquipments']);
    Route::get('/reservation', [ReservationController::class, 'userReservation']);
});

// Calendar route
Route::get('/calendar_of_activities', [CalendarController::class, 'calendar']);

// Admin routes
Route::prefix('admin')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'adminDashboard']);

    // Facilities routes
    Route::prefix('facilities')->group(function () {
        Route::get('/', [AdminFacilityController::class, 'adminFacilities']);
        Route::get('/manage-facilities', [AdminFacilityController::class, 'adminManageFacilities'])
            ->name('admin.facilities.manage');

        // CRUD operations
        Route::post('/', [AdminFacilityController::class, 'store'])->name('facilities.store');
        Route::put('/{id}', [AdminFacilityController::class, 'update'])->name('facilities.update');
        Route::delete('/{id}', [AdminFacilityController::class, 'destroy'])->name('facilities.destroy');
    });

    // Equipment routes
    Route::prefix('equipments')->group(function () {
        Route::get('/', [AdminEquipmentsController::class, 'adminEquipments']);
        Route::get('/manage-equipments', [AdminEquipmentsController::class, 'adminManageEquipments'])
            ->name('admin.equipments.manage');

        // CRUD operations
        Route::post('/', [AdminEquipmentsController::class, 'store'])->name('equipments.store');
        Route::put('/{id}', [AdminEquipmentsController::class, 'update'])->name('equipments.update');
        Route::delete('/{id}', [AdminEquipmentsController::class, 'destroy'])->name('equipments.destroy');
    });
});

Route::get('/api/availability/{facilityId}', function($facilityId) {
    $type = request()->query('type', 'single');
    $month = request()->query('month', date('m'));
    $year = request()->query('year', date('Y'));
    $days = request()->query('days', 2);
    $startDate = request()->query('start_date');


    if ($type === 'consecutive' && $startDate) {
        // Check if specific consecutive range is available
        $range = [];
        $currentDate = Carbon::parse($startDate);
        $allAvailable = true;

        for ($i = 0; $i < $days; $i++) {
            $dateStr = $currentDate->format('Y-m-d');
            $range[] = $dateStr;

            // Check if date is available
            $isAvailable = !ReservationRequest::where('facility_id', $facilityId)
                ->where('status', 'accepted')
                ->where(function($query) use ($dateStr) {
                    $query->whereHasMorph(
                        'reservationDetail',
                        [Single::class, Consecutive::class, Multiple::class],
                        function($q, $type) use ($dateStr) {
                            if ($type === Single::class) {
                                $q->where('start_date', $dateStr);
                            } else {
                                $q->where('start_date', $dateStr)
                                    ->orWhere('intermediate_date', $dateStr)
                                    ->orWhere('end_date', $dateStr);
                            }
                        }
                    );
                })
                ->exists();

            if (!$isAvailable) {
                $allAvailable = false;
                break;
            }

            $currentDate->addDay();
        }

        return response()->json([
            'consecutive' => $allAvailable ? [$range] : []
        ]);
    }
    // Get all dates in the month
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $dates = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $dates[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    // Check availability for each date
    $unavailableDates = [];
    foreach ($dates as $date) {
        if (CalendarActivity::checkAvailability($facilityId, [$date])) {
            $unavailableDates[] = $date;
        }
    }

    // For consecutive dates, we need to check ranges
    $consecutiveRanges = [];
    if ($type === 'consecutive') {
        $days = request()->query('days', 2);
        // This is a simplified example - you might want to implement a more efficient way
        for ($i = 0; $i <= count($dates) - $days; $i++) {
            $range = array_slice($dates, $i, $days);
            $isAvailable = !CalendarActivity::checkAvailability($facilityId, $range);
            if ($isAvailable) {
                $consecutiveRanges[] = $range;
            }
        }
    }

    return response()->json([
        'unavailable' => $unavailableDates,
        'consecutive' => $consecutiveRanges,
        'month' => $month,
        'year' => $year
    ]);
});
