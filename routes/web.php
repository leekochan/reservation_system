<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\admin\AdminEquipmentsController;
use App\Http\Controllers\Admin\AdminFacilityBlockController;
use App\Http\Controllers\admin\AdminFacilityController;
use App\Http\Controllers\Admin\AdminReservationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;
use App\Models\CalendarActivity;
use App\Models\Consecutive;
use App\Models\Facility;
use App\Models\Multiple;
use App\Models\ReservationRequest;
use App\Models\Single;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/user');
});

Route::prefix('user')->group(function () {
    Route::get('/', [DashboardController::class, 'userDashboard']);
    Route::get('/facilities', [FacilityController::class, 'userFacilities']);
    Route::get('/equipments', [EquipmentsController::class, 'userEquipments']);
    Route::get('/reservation', [ReservationController::class, 'userReservation']);
    Route::post('/reservation', [ReservationController::class, 'storeReservation'])->name('reservation.store');
});

Route::get('/calendar_of_activities', [CalendarController::class, 'calendar']);
Route::get('/calendar-activities', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar.activities');

//Route::get('/admin', [AdminDashboardController::class, 'adminDashboard']);
//Route::get('/admin/facilities', [AdminFacilityController::class, 'adminFacilities']);
//Route::get('/admin/equipments', [AdminEquipmentsController::class, 'adminEquipments']);
//Route::get('/admin/facilities/manage-facilities', [AdminFacilityController::class, 'adminManageFacilities']);
//Route::get('/admin/equipments/manage-equipments', [AdminEquipmentsController::class, 'adminManageEquipments']);
Route::prefix('admin')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'adminDashboard']);

    // Dashboard specific reservation actions (for quick actions on dashboard)
    Route::get('/dashboard/reservation/{id}/details', [AdminDashboardController::class, 'getReservationDetails'])->name('admin.dashboard.reservation.details');
    Route::post('/dashboard/reservation/{id}/accept', [AdminDashboardController::class, 'acceptReservation'])->name('admin.dashboard.reservation.accept');
    Route::post('/dashboard/reservation/{id}/decline', [AdminDashboardController::class, 'declineReservation'])->name('admin.dashboard.reservation.decline');

    // Calendar route for admin
    Route::get('/calendar', function() {
        return app(CalendarController::class)->index(request()->merge(['admin' => true]));
    })->name('admin.calendar');

    // Reservation routes - using dedicated AdminReservationController
    Route::prefix('reservations')->name('admin.reservations.')->group(function () {
        Route::get('/', [AdminReservationController::class, 'index'])->name('index');
        Route::get('/{id}/details', [AdminReservationController::class, 'show'])->name('show');
        Route::post('/{id}/accept', [AdminReservationController::class, 'accept'])->name('accept');
        Route::post('/{id}/decline', [AdminReservationController::class, 'decline'])->name('decline');
        Route::post('/{id}/complete', [AdminReservationController::class, 'complete'])->name('complete');
        Route::post('/{id}/cancel', [AdminReservationController::class, 'cancel'])->name('cancel');
        Route::delete('/{id}', [AdminReservationController::class, 'destroy'])->name('destroy');
        Route::get('/status/{status}', [AdminReservationController::class, 'getByStatus'])->name('by-status');
    });

    // Legacy route for backward compatibility (redirect to new reservations page)
    Route::get('/reservation', function() {
        return redirect()->route('admin.reservations.index');
    })->name('admin.reservation');

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

    // Facility block routes
    Route::prefix('facility-blocks')->group(function () {
        Route::get('/manage', [AdminFacilityBlockController::class, 'manage'])->name('admin.facility-blocks.manage');
        Route::get('/blocks', [AdminFacilityBlockController::class, 'getBlocks'])->name('admin.facility-blocks.get');
        Route::delete('/{id}', [AdminFacilityBlockController::class, 'destroy'])->name('admin.facility-blocks.destroy');
    });

    // Separate route for storing facility blocks (to match the AJAX call)
    Route::post('/facility-blocks', [AdminFacilityBlockController::class, 'store'])->name('admin.facility-blocks.store');
});



//Route::get('/api/availability/{facilityId}', function($facilityId) {
//    $type = request()->query('type', 'single');
//    $month = request()->query('month', date('m'));
//    $year = request()->query('year', date('Y'));
//    $days = request()->query('days', 2);
//    $startDate = request()->query('start_date');
//
//
//    if ($type === 'consecutive' && $startDate) {
//        // Check if specific consecutive range is available
//        $range = [];
//        $currentDate = Carbon::parse($startDate);
//        $allAvailable = true;
//
//        for ($i = 0; $i < $days; $i++) {
//            $dateStr = $currentDate->format('Y-m-d');
//            $range[] = $dateStr;
//
//            // Check if date is available
//            $isAvailable = !ReservationRequest::where('facility_id', $facilityId)
//                ->where('status', 'accepted')
//                ->where(function($query) use ($dateStr) {
//                    $query->whereHasMorph(
//                        'reservationDetail',
//                        [Single::class, Consecutive::class, Multiple::class],
//                        function($q, $type) use ($dateStr) {
//                            if ($type === Single::class) {
//                                $q->where('start_date', $dateStr);
//                            } else {
//                                $q->where('start_date', $dateStr)
//                                    ->orWhere('intermediate_date', $dateStr)
//                                    ->orWhere('end_date', $dateStr);
//                            }
//                        }
//                    );
//                })
//                ->exists();
//
//            if (!$isAvailable) {
//                $allAvailable = false;
//                break;
//            }
//
//            $currentDate->addDay();
//        }
//
//        return response()->json([
//            'consecutive' => $allAvailable ? [$range] : []
//        ]);
//    }
//    // Get all dates in the month
//    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
//    $dates = [];
//    for ($day = 1; $day <= $daysInMonth; $day++) {
//        $dates[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
//    }
//
//    // Check availability for each date
//    $unavailableDates = [];
//    foreach ($dates as $date) {
//        if (CalendarActivity::checkAvailability($facilityId, [$date])) {
//            $unavailableDates[] = $date;
//        }
//    }
//
//    // For consecutive dates, we need to check ranges
//    $consecutiveRanges = [];
//    if ($type === 'consecutive') {
//        $days = request()->query('days', 2);
//        // This is a simplified example - you might want to implement a more efficient way
//        for ($i = 0; $i <= count($dates) - $days; $i++) {
//            $range = array_slice($dates, $i, $days);
//            $isAvailable = !CalendarActivity::checkAvailability($facilityId, $range);
//            if ($isAvailable) {
//                $consecutiveRanges[] = $range;
//            }
//        }
//    }
//
//    return response()->json([
//        'unavailable' => $unavailableDates,
//        'consecutive' => $consecutiveRanges,
//        'month' => $month,
//        'year' => $year
//    ]);
//});
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

            // Check if date is available (reservations)
            $hasReservation = ReservationRequest::where('facility_id', $facilityId)
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

            // Check if date is blocked by admin
            $hasAdminBlock = \App\Models\AdminFacilityBlock::active()
                ->forFacility($facilityId)
                ->forDate($dateStr)
                ->exists();

            if ($hasReservation || $hasAdminBlock) {
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
        // Check regular reservations
        $hasReservation = CalendarActivity::checkAvailability($facilityId, [$date]);

        // Check admin blocks
        $hasAdminBlock = \App\Models\AdminFacilityBlock::active()
            ->forFacility($facilityId)
            ->forDate($date)
            ->exists();

        if ($hasReservation || $hasAdminBlock) {
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
            $isAvailable = true;

            foreach ($range as $rangeDate) {
                $hasReservation = CalendarActivity::checkAvailability($facilityId, [$rangeDate]);
                $hasAdminBlock = \App\Models\AdminFacilityBlock::active()
                    ->forFacility($facilityId)
                    ->forDate($rangeDate)
                    ->exists();

                if ($hasReservation || $hasAdminBlock) {
                    $isAvailable = false;
                    break;
                }
            }

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

// API route for fetching facilities
Route::get('/api/facilities', function() {
    $facilities = Facility::where('status', 'available')->get(['facility_id', 'facility_name']);
    return response()->json([
        'success' => true,
        'facilities' => $facilities
    ]);
});
