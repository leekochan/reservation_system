<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ReservationRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function userDashboard(Request $request)
    {
        // Get 6 latest facilities for the facilities section
        $facilities = Facility::latest()->take(6)->get();

        // Get all facilities for filter dropdown
        $allFacilities = Facility::orderBy('facility_name')->get();

        // Get filter parameters
        $facilityFilter = $request->get('facility_filter', 'all');
        $typeFilter = $request->get('type_filter', 'all');

        // Get accepted reservations for the next 30 days to show as events
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        $query = ReservationRequest::where('status', 'accepted')
            ->with(['facility', 'single', 'consecutive', 'multiple']);

        // Apply facility filter if provided
        if ($facilityFilter && $facilityFilter !== 'all') {
            $query->where('facility_id', $facilityFilter);
        }

        // Apply reservation type filter if provided
        if ($typeFilter && $typeFilter !== 'all') {
            $query->where('reservation_type', ucfirst($typeFilter));
        }

        $events = $query->whereHasMorph(
            'reservationDetail',
            [\App\Models\Single::class, \App\Models\Consecutive::class, \App\Models\Multiple::class],
            function($query, $type) use ($startDate, $endDate) {
                if ($type === \App\Models\Single::class) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } elseif ($type === \App\Models\Consecutive::class) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhere(function($subQ) use ($startDate, $endDate) {
                                $subQ->where('start_date', '<=', $startDate->format('Y-m-d'))
                                    ->where('end_date', '>=', $endDate->format('Y-m-d'));
                            });
                    });
                } elseif ($type === \App\Models\Multiple::class) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('intermediate_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                    });
                }
            }
        )
            ->get()
            ->map(function($reservation) {
                // Transform each reservation into the format needed for the view
                $events = [];
                $dates = $this->getReservationDates($reservation);

                foreach ($dates as $date) {
                    if (!$date) continue;

                    $time = $this->getReservationTime($reservation);

                    $events[] = [
                        'date' => $date,
                        'title' => $reservation->purpose ?? 'Reservation',
                        'time' => $time,
                        'venue' => $reservation->facility->facility_name ?? 'No Facility',
                        'reservation_id' => $reservation->reservation_id,
                        'reservation_type' => $reservation->reservation_type
                    ];
                }

                return $events;
            })
            ->flatten(1)
            ->sortBy('date')
            ->values()
            ->all();

        return view('user-dashboard', compact('facilities', 'events', 'allFacilities', 'facilityFilter', 'typeFilter'));
    }

    /**
     * AJAX endpoint for filtering events
     */
    public function filterEvents(Request $request)
    {
        $facilityFilter = $request->get('facility_filter', 'all');
        $typeFilter = $request->get('type_filter', 'all');

        // Get accepted reservations for the next 30 days
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        $query = ReservationRequest::where('status', 'accepted')
            ->with(['facility', 'single', 'consecutive', 'multiple']);

        // Apply facility filter if provided
        if ($facilityFilter && $facilityFilter !== 'all') {
            $query->where('facility_id', $facilityFilter);
        }

        // Apply reservation type filter if provided
        if ($typeFilter && $typeFilter !== 'all') {
            $query->where('reservation_type', ucfirst($typeFilter));
        }

        $events = $query->whereHasMorph(
            'reservationDetail',
            [\App\Models\Single::class, \App\Models\Consecutive::class, \App\Models\Multiple::class],
            function($query, $type) use ($startDate, $endDate) {
                if ($type === \App\Models\Single::class) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } elseif ($type === \App\Models\Consecutive::class) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhere(function($subQ) use ($startDate, $endDate) {
                                $subQ->where('start_date', '<=', $startDate->format('Y-m-d'))
                                    ->where('end_date', '>=', $endDate->format('Y-m-d'));
                            });
                    });
                } elseif ($type === \App\Models\Multiple::class) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('intermediate_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                    });
                }
            }
        )
            ->get()
            ->map(function($reservation) {
                // Transform each reservation into the format needed for the view
                $events = [];
                $dates = $this->getReservationDates($reservation);

                foreach ($dates as $date) {
                    if (!$date) continue;

                    $time = $this->getReservationTime($reservation);

                    $events[] = [
                        'date' => $date,
                        'title' => $reservation->purpose ?? 'Reservation',
                        'time' => $time,
                        'venue' => $reservation->facility->facility_name ?? 'No Facility',
                        'reservation_id' => $reservation->reservation_id,
                        'reservation_type' => $reservation->reservation_type
                    ];
                }

                return $events;
            })
            ->flatten(1)
            ->sortBy('date')
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'events' => $events
        ]);
    }

    private function getReservationDates($reservation)
    {
        $dates = [];

        if ($reservation->single) {
            $dates[] = $reservation->single->start_date;
        } elseif ($reservation->consecutive) {
            $startDate = Carbon::parse($reservation->consecutive->start_date);
            $endDate = Carbon::parse($reservation->consecutive->end_date);

            while ($startDate->lte($endDate)) {
                $dates[] = $startDate->format('Y-m-d');
                $startDate->addDay();
            }
        } elseif ($reservation->multiple) {
            if ($reservation->multiple->start_date) {
                $dates[] = $reservation->multiple->start_date;
            }
            if ($reservation->multiple->intermediate_date) {
                $dates[] = $reservation->multiple->intermediate_date;
            }
            if ($reservation->multiple->end_date) {
                $dates[] = $reservation->multiple->end_date;
            }
        }

        return array_filter($dates);
    }

    private function getReservationTime($reservation)
    {
        $startTime = null;
        $endTime = null;

        if ($reservation->single) {
            $startTime = $reservation->single->time_from;
            $endTime = $reservation->single->time_to;
        } elseif ($reservation->consecutive) {
            $startTime = $reservation->consecutive->start_time_from;
            $endTime = $reservation->consecutive->start_time_to;
        } elseif ($reservation->multiple) {
            $startTime = $reservation->multiple->start_time_from;
            $endTime = $reservation->multiple->start_time_to;
        }

        if ($startTime && $endTime) {
            return Carbon::parse($startTime)->format('g:i A') . ' - ' . Carbon::parse($endTime)->format('g:i A');
        }

        return 'All Day';
    }
}
