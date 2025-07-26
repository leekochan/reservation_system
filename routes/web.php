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
use DateTime;
use DateInterval;
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
    $days = (int) request()->query('days', 2); // Cast to integer
    $startDate = request()->query('start_date');

    // Helper function to calculate time difference in hours
    $calculateTimeDifference = function($startTime, $endTime) {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $diff = $start->diff($end);
        return $diff->h + ($diff->i / 60);
    };

    // Function to check if a date has at least 2 hours of continuous available time
    $checkMinimumAvailableTime = function($facilityId, $date) use ($calculateTimeDifference) {
        // Get all existing reservations for this facility and date (accepted only)
        $existingReservations = ReservationRequest::where('facility_id', $facilityId)
            ->where('status', 'accepted')
            ->where(function($query) use ($date) {
                $query->whereHasMorph(
                    'reservationDetail',
                    [Single::class, Consecutive::class, Multiple::class],
                    function($q, $type) use ($date) {
                        if ($type === Single::class) {
                            $q->where('start_date', $date);
                        } elseif ($type === Consecutive::class) {
                            $q->where(function($subQuery) use ($date) {
                                $subQuery->where('start_date', $date)
                                    ->orWhere('end_date', $date);
                            });
                        } elseif ($type === Multiple::class) {
                            $q->where(function($subQuery) use ($date) {
                                $subQuery->where('start_date', $date)
                                    ->orWhere('intermediate_date', $date)
                                    ->orWhere('end_date', $date);
                            });
                        }
                    }
                );
            })
            ->with('reservationDetail')
            ->get();

        // Get admin blocks for this facility and date
        $adminBlocks = \App\Models\AdminFacilityBlock::active()
            ->forFacility($facilityId)
            ->forDate($date)
            ->get();

        // If there are admin blocks, date is unavailable
        if ($adminBlocks->count() > 0) {
            return false;
        }

        // If no reservations, full day is available
        if ($existingReservations->count() === 0) {
            return true;
        }

        // Collect all blocked time ranges with buffers
        $blockedRanges = [];
        
        foreach ($existingReservations as $reservation) {
            $detail = $reservation->reservationDetail;
            
            if ($detail instanceof Single) {
                if ($detail->start_date === $date) {
                    $blockedRanges[] = [
                        'start' => $detail->time_from,
                        'end' => $detail->time_to
                    ];
                }
            } elseif ($detail instanceof Consecutive) {
                if ($detail->start_date === $date) {
                    $blockedRanges[] = [
                        'start' => $detail->start_time_from,
                        'end' => $detail->start_time_to
                    ];
                }
                if ($detail->end_date === $date) {
                    $blockedRanges[] = [
                        'start' => $detail->end_time_from,
                        'end' => $detail->end_time_to
                    ];
                }
            } elseif ($detail instanceof Multiple) {
                if ($detail->start_date === $date) {
                    $blockedRanges[] = [
                        'start' => $detail->start_time_from,
                        'end' => $detail->start_time_to
                    ];
                }
                if ($detail->intermediate_date === $date) {
                    $blockedRanges[] = [
                        'start' => $detail->intermediate_time_from,
                        'end' => $detail->intermediate_time_to
                    ];
                }
                if ($detail->end_date === $date) {
                    $blockedRanges[] = [
                        'start' => $detail->end_time_from,
                        'end' => $detail->end_time_to
                    ];
                }
            }
        }

        // Add 1-hour buffer to each blocked range
        $bufferedRanges = [];
        foreach ($blockedRanges as $range) {
            $startTime = new DateTime($range['start']);
            $startTime->sub(new DateInterval('PT1H'));
            $bufferedStart = max('08:00', $startTime->format('H:i'));
            
            $endTime = new DateTime($range['end']);
            $endTime->add(new DateInterval('PT1H'));
            $bufferedEnd = min('17:30', $endTime->format('H:i'));
            
            $bufferedRanges[] = [
                'start' => $bufferedStart,
                'end' => $bufferedEnd
            ];
        }

        // Sort ranges by start time
        usort($bufferedRanges, function($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        // Merge overlapping ranges
        $mergedRanges = [];
        foreach ($bufferedRanges as $range) {
            if (empty($mergedRanges)) {
                $mergedRanges[] = $range;
            } else {
                $lastRange = end($mergedRanges);
                if ($range['start'] <= $lastRange['end']) {
                    // Ranges overlap, merge them
                    $mergedRanges[count($mergedRanges) - 1]['end'] = max($lastRange['end'], $range['end']);
                } else {
                    $mergedRanges[] = $range;
                }
            }
        }

        // Check for available time gaps of at least 2 hours
        $businessStart = '08:00';
        $businessEnd = '17:30';
        
        // Check gap before first blocked range
        if (!empty($mergedRanges)) {
            $firstBlockStart = $mergedRanges[0]['start'];
            if ($calculateTimeDifference($businessStart, $firstBlockStart) >= 2) {
                return true;
            }
        } else {
            // No blocked ranges, full day available
            return true;
        }
        
        // Check gaps between blocked ranges
        for ($i = 0; $i < count($mergedRanges) - 1; $i++) {
            $currentEnd = $mergedRanges[$i]['end'];
            $nextStart = $mergedRanges[$i + 1]['start'];
            
            if ($calculateTimeDifference($currentEnd, $nextStart) >= 2) {
                return true;
            }
        }
        
        // Check gap after last blocked range
        if (!empty($mergedRanges)) {
            $lastBlockEnd = end($mergedRanges)['end'];
            if ($calculateTimeDifference($lastBlockEnd, $businessEnd) >= 2) {
                return true;
            }
        }
        
        return false;
    };

    if ($type === 'consecutive' && $startDate) {
        // Check if specific consecutive range is available
        $range = [];
        $currentDate = Carbon::parse($startDate);
        $allAvailable = true;
        $hasPending = false;

        for ($i = 0; $i < $days; $i++) {
            $dateStr = $currentDate->format('Y-m-d');
            $range[] = $dateStr;

            // Check admin blocks - these completely block the date
            $hasAdminBlock = \App\Models\AdminFacilityBlock::active()
                ->forFacility($facilityId)
                ->forDate($dateStr)
                ->exists();

            // For consecutive: only block if there's ANY accepted reservation
            $hasAnyAcceptedReservation = ReservationRequest::where('facility_id', $facilityId)
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

            // Check for pending reservations
            $hasPendingReservation = ReservationRequest::where('facility_id', $facilityId)
                ->where('status', 'pending')
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

            if ($hasAdminBlock || $hasAnyAcceptedReservation) {
                $allAvailable = false;
                break;
            } elseif ($hasPendingReservation) {
                $hasPending = true;
            }

            $currentDate->addDay();
        }

        return response()->json([
            'consecutive' => $allAvailable ? [[
                'dates' => $range,
                'has_pending' => $hasPending
            ]] : []
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
    $pendingDates = [];
    foreach ($dates as $date) {
        // Check pending reservations
        $hasPendingReservation = ReservationRequest::where('facility_id', $facilityId)
            ->where('status', 'pending')
            ->where(function($query) use ($date) {
                $query->whereHasMorph(
                    'reservationDetail',
                    [Single::class, Consecutive::class, Multiple::class],
                    function($q, $type) use ($date) {
                        if ($type === Single::class) {
                            $q->where('start_date', $date);
                        } else {
                            $q->where('start_date', $date)
                                ->orWhere('intermediate_date', $date)
                                ->orWhere('end_date', $date);
                        }
                    }
                );
            })
            ->exists();

        // Check admin blocks - these completely block the date
        $hasAdminBlock = \App\Models\AdminFacilityBlock::active()
            ->forFacility($facilityId)
            ->forDate($date)
            ->exists();

        if ($type === 'single') {
            // For single reservations: use 2-hour minimum availability logic
            $hasMinimumAvailableTime = $checkMinimumAvailableTime($facilityId, $date);

            if ($hasAdminBlock || !$hasMinimumAvailableTime) {
                $unavailableDates[] = $date;
            } elseif ($hasPendingReservation) {
                $pendingDates[] = $date;
            }
        } else {
            // For consecutive/multiple reservations: only block if there's ANY accepted reservation
            $hasAnyAcceptedReservation = ReservationRequest::where('facility_id', $facilityId)
                ->where('status', 'accepted')
                ->where(function($query) use ($date) {
                    $query->whereHasMorph(
                        'reservationDetail',
                        [Single::class, Consecutive::class, Multiple::class],
                        function($q, $type) use ($date) {
                            if ($type === Single::class) {
                                $q->where('start_date', $date);
                            } else {
                                $q->where('start_date', $date)
                                    ->orWhere('intermediate_date', $date)
                                    ->orWhere('end_date', $date);
                            }
                        }
                    );
                })
                ->exists();

            if ($hasAdminBlock || $hasAnyAcceptedReservation) {
                $unavailableDates[] = $date;
            } elseif ($hasPendingReservation) {
                $pendingDates[] = $date;
            }
        }
    }

    // For consecutive dates, we need to check ranges
    $consecutiveRanges = [];
    if ($type === 'consecutive') {
        $days = (int) request()->query('days', 2); // Cast to integer
        // This is a simplified example - you might want to implement a more efficient way
        for ($i = 0; $i <= count($dates) - $days; $i++) {
            $range = array_slice($dates, $i, $days);
            $isAvailable = true;
            $hasPending = false;

            foreach ($range as $rangeDate) {
                $hasAdminBlock = \App\Models\AdminFacilityBlock::active()
                    ->forFacility($facilityId)
                    ->forDate($rangeDate)
                    ->exists();

                // For consecutive: only block if there's ANY accepted reservation
                $hasAnyAcceptedReservation = ReservationRequest::where('facility_id', $facilityId)
                    ->where('status', 'accepted')
                    ->where(function($query) use ($rangeDate) {
                        $query->whereHasMorph(
                            'reservationDetail',
                            [Single::class, Consecutive::class, Multiple::class],
                            function($q, $type) use ($rangeDate) {
                                if ($type === Single::class) {
                                    $q->where('start_date', $rangeDate);
                                } else {
                                    $q->where('start_date', $rangeDate)
                                        ->orWhere('intermediate_date', $rangeDate)
                                        ->orWhere('end_date', $rangeDate);
                                }
                            }
                        );
                    })
                    ->exists();

                // Check for pending reservations in this range
                $hasPendingReservation = ReservationRequest::where('facility_id', $facilityId)
                    ->where('status', 'pending')
                    ->where(function($query) use ($rangeDate) {
                        $query->whereHasMorph(
                            'reservationDetail',
                            [Single::class, Consecutive::class, Multiple::class],
                            function($q, $type) use ($rangeDate) {
                                if ($type === Single::class) {
                                    $q->where('start_date', $rangeDate);
                                } else {
                                    $q->where('start_date', $rangeDate)
                                        ->orWhere('intermediate_date', $rangeDate)
                                        ->orWhere('end_date', $rangeDate);
                                }
                            }
                        );
                    })
                    ->exists();

                if ($hasAdminBlock || $hasAnyAcceptedReservation) {
                    $isAvailable = false;
                    break;
                } elseif ($hasPendingReservation) {
                    $hasPending = true;
                }
            }

            if ($isAvailable) {
                $consecutiveRanges[] = [
                    'dates' => $range,
                    'has_pending' => $hasPending
                ];
            }
        }
    }

    return response()->json([
        'unavailable' => $unavailableDates,
        'pending' => $pendingDates,
        'consecutive' => $consecutiveRanges,
        'month' => $month,
        'year' => $year
    ]);
});

// API route for fetching available times for a specific date and facility
Route::get('/api/time-availability/{facilityId}', function($facilityId) {
    $date = request()->query('date');
    
    if (!$date) {
        return response()->json(['error' => 'Date parameter is required'], 400);
    }

    // Get all existing reservations for this facility and date (accepted only)
    $existingReservations = ReservationRequest::where('facility_id', $facilityId)
        ->where('status', 'accepted')
        ->where(function($query) use ($date) {
            $query->whereHasMorph(
                'reservationDetail',
                [Single::class, Consecutive::class, Multiple::class],
                function($q, $type) use ($date) {
                    if ($type === Single::class) {
                        $q->where('start_date', $date);
                    } elseif ($type === Consecutive::class) {
                        $q->where(function($subQuery) use ($date) {
                            $subQuery->where('start_date', $date)
                                ->orWhere('end_date', $date);
                        });
                    } elseif ($type === Multiple::class) {
                        $q->where(function($subQuery) use ($date) {
                            $subQuery->where('start_date', $date)
                                ->orWhere('intermediate_date', $date)
                                ->orWhere('end_date', $date);
                        });
                    }
                }
            );
        })
        ->with('reservationDetail')
        ->get();

    // Get admin blocks for this facility and date
    $adminBlocks = \App\Models\AdminFacilityBlock::active()
        ->forFacility($facilityId)
        ->forDate($date)
        ->get();

    // Collect all blocked time ranges
    $blockedRanges = [];
    
    // Add reservation time ranges
    foreach ($existingReservations as $reservation) {
        $detail = $reservation->reservationDetail;
        
        if ($detail instanceof Single) {
            if ($detail->start_date === $date) {
                $blockedRanges[] = [
                    'start' => $detail->time_from,
                    'end' => $detail->time_to
                ];
            }
        } elseif ($detail instanceof Consecutive) {
            if ($detail->start_date === $date) {
                $blockedRanges[] = [
                    'start' => $detail->start_time_from,
                    'end' => $detail->start_time_to
                ];
            }
            if ($detail->end_date === $date) {
                $blockedRanges[] = [
                    'start' => $detail->end_time_from,
                    'end' => $detail->end_time_to
                ];
            }
        } elseif ($detail instanceof Multiple) {
            if ($detail->start_date === $date) {
                $blockedRanges[] = [
                    'start' => $detail->start_time_from,
                    'end' => $detail->start_time_to
                ];
            }
            if ($detail->intermediate_date === $date) {
                $blockedRanges[] = [
                    'start' => $detail->intermediate_time_from,
                    'end' => $detail->intermediate_time_to
                ];
            }
            if ($detail->end_date === $date) {
                $blockedRanges[] = [
                    'start' => $detail->end_time_from,
                    'end' => $detail->end_time_to
                ];
            }
        }
    }
    
    // Add admin block time ranges
    foreach ($adminBlocks as $block) {
        $blockedRanges[] = [
            'start' => $block->start_time,
            'end' => $block->end_time
        ];
    }

    // Generate all possible time slots (30-minute intervals from 08:00 to 17:30)
    $allTimeSlots = [];
    for ($hour = 8; $hour <= 17; $hour++) {
        for ($minute = 0; $minute < 60; $minute += 30) {
            $time = sprintf('%02d:%02d', $hour, $minute);
            $allTimeSlots[] = $time;
        }
    }

    // Function to check if a time is blocked
    function isTimeBlocked($time, $blockedRanges) {
        foreach ($blockedRanges as $range) {
            if ($time >= $range['start'] && $time <= $range['end']) {
                return true;
            }
        }
        return false;
    }

    // Function to add buffer time (1 hour) around blocked ranges
    function addBufferToBlockedRanges($blockedRanges) {
        $bufferedRanges = [];
        foreach ($blockedRanges as $range) {
            // Subtract 1 hour from start time
            $startTime = new DateTime($range['start']);
            $startTime->sub(new DateInterval('PT1H'));
            $bufferedStart = $startTime->format('H:i');
            
            // Add 1 hour to end time
            $endTime = new DateTime($range['end']);
            $endTime->add(new DateInterval('PT1H'));
            $bufferedEnd = $endTime->format('H:i');
            
            // Ensure we don't go outside business hours
            if ($bufferedStart < '08:00') $bufferedStart = '08:00';
            if ($bufferedEnd > '17:30') $bufferedEnd = '17:30';
            
            $bufferedRanges[] = [
                'start' => $bufferedStart,
                'end' => $bufferedEnd
            ];
        }
        return $bufferedRanges;
    }

    $bufferedBlockedRanges = addBufferToBlockedRanges($blockedRanges);

    // Filter available times
    $availableTimeSlots = array_filter($allTimeSlots, function($time) use ($bufferedBlockedRanges) {
        return !isTimeBlocked($time, $bufferedBlockedRanges);
    });

    return response()->json([
        'available_times' => array_values($availableTimeSlots),
        'blocked_ranges' => $blockedRanges,
        'buffered_ranges' => $bufferedBlockedRanges,
        'date' => $date
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
