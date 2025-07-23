<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ReservationRequest;
use App\Models\AdminFacilityBlock;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::now();
        $month = $request->get('month', $today->month);
        $year = $request->get('year', $today->year);

        // Determine if we're in admin context based on referrer or explicit parameter
        $isAdmin = $request->get('admin', false) ||
            (str_contains($request->headers->get('referer', ''), '/admin'));

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $monthName = $currentDate->format('F');
        $daysInMonth = $currentDate->daysInMonth;
        $firstDayOfMonth = $currentDate->dayOfWeek;

        // Get all accepted reservations with user relationship
        $allAcceptedReservations = ReservationRequest::where('status', 'accepted')
            ->with(['facility', 'equipment', 'single', 'consecutive', 'multiple'])
            ->get();

        // Get admin facility blocks for the current month
        $adminBlocks = AdminFacilityBlock::active()
            ->with('facility')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        Log::info('Total accepted reservations: ' . $allAcceptedReservations->count());
        Log::info('Total admin blocks: ' . $adminBlocks->count());

        // Group reservations and admin blocks by date
        $reservationsByDate = collect();

        // Process regular reservations
        foreach ($allAcceptedReservations as $reservation) {
            $dates = $this->getReservationDates($reservation);
            $reservation->formatted_times = $this->getFormattedReservationTime($reservation);
            $reservation->is_admin_block = false; // Mark as regular reservation

            foreach ($dates as $date) {
                if (!$date) continue;
                try {
                    $carbonDate = Carbon::parse($date);
                    if ($carbonDate->year == $year && $carbonDate->month == $month) {
                        $dateKey = $carbonDate->format('Y-m-d');
                        if (!$reservationsByDate->has($dateKey)) {
                            $reservationsByDate[$dateKey] = collect();
                        }
                        $reservationsByDate[$dateKey]->push($reservation);

                        // Special debug for July 23rd and 25th with correct year
                        $july23Key = $year . '-07-23';
                        $july25Key = $year . '-07-25';
                        if ($dateKey === $july23Key || $dateKey === $july25Key) {
                            Log::info('Added reservation to ' . $dateKey . ': ID ' . $reservation->reservation_id);
                            Log::info('Total reservations for ' . $dateKey . ': ' . $reservationsByDate[$dateKey]->count());
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error grouping reservation by date: ' . $date . ' - ' . $e->getMessage());
                }
            }
        }

        // Process admin blocks
        foreach ($adminBlocks as $block) {
            $dateKey = $block->date->format('Y-m-d');

            // Create a pseudo-reservation object for admin blocks
            $adminBlockReservation = (object) [
                'block_id' => $block->block_id,
                'purpose' => $block->purpose,
                'facility' => $block->facility,
                'notes' => $block->notes,
                'is_admin_block' => true,
                'formatted_times' => [
                    'start_time' => Carbon::parse($block->start_time)->format('g:i A'),
                    'end_time' => Carbon::parse($block->end_time)->format('g:i A'),
                    'raw_start' => $block->start_time,
                    'raw_end' => $block->end_time
                ]
            ];

            if (!$reservationsByDate->has($dateKey)) {
                $reservationsByDate[$dateKey] = collect();
            }
            $reservationsByDate[$dateKey]->push($adminBlockReservation);
        }

        Log::info('Final reservationsByDate keys: ' . $reservationsByDate->keys()->implode(', '));

        // Show detailed info about what we found
        foreach ($reservationsByDate as $date => $dayReservations) {
            Log::info('Date ' . $date . ' has ' . $dayReservations->count() . ' items:');
            foreach ($dayReservations as $res) {
                if (isset($res->is_admin_block) && $res->is_admin_block) {
                    Log::info('  - Admin Block: ' . $res->purpose .
                        ', Facility: ' . ($res->facility->facility_name ?? 'N/A') .
                        ', Time: ' . $res->formatted_times['start_time'] . ' - ' . $res->formatted_times['end_time']);
                } else {
                    $times = $this->getFormattedReservationTime($res);
                    Log::info('  - Reservation ID: ' . $res->reservation_id .
                        ', Type: ' . $res->reservation_type .
                        ', Time: ' . $times['start_time'] . ' - ' . $times['end_time']);
                }
            }
        }

        // Sort reservations by time within each date
        $reservationsByDate = $reservationsByDate->map(function($dayReservations) {
            return $dayReservations->sortBy(function($reservation) {
                if (isset($reservation->is_admin_block) && $reservation->is_admin_block) {
                    return $reservation->formatted_times['raw_start'];
                }
                return $reservation->formatted_times['raw_start'] ?? '00:00:00';
            });
        });

        // Filter reservations for the view (for backward compatibility)
        $reservations = $reservationsByDate->flatten();

        return view('calendar-activities', compact(
            'today', 'currentDate', 'monthName', 'year', 'month',
            'daysInMonth', 'firstDayOfMonth', 'reservations', 'reservationsByDate', 'isAdmin'
        ));
    }

    private function getReservationDates($reservation)
    {
        $dates = [];

        Log::info('=== Processing reservation ID: ' . $reservation->reservation_id . ' ===');
        Log::info('Reservation type: ' . $reservation->reservation_type);

        // Check single reservation
        if ($reservation->single) {
            $singleData = $reservation->single->getAttributes();
            Log::info('Single reservation fields: ' . json_encode($singleData));

            // For single reservations, look for common date fields
            if (isset($singleData['date']) && $singleData['date']) {
                $dates[] = $singleData['date'];
                Log::info('✓ Found single date: ' . $singleData['date']);
            } elseif (isset($singleData['start_date']) && $singleData['start_date']) {
                $dates[] = $singleData['start_date'];
                Log::info('✓ Found single start_date: ' . $singleData['start_date']);
            }
        }

        // Check consecutive reservation
        if ($reservation->consecutive) {
            $consecutiveData = $reservation->consecutive->getAttributes();
            Log::info('Consecutive reservation fields: ' . json_encode($consecutiveData));

            $startDate = $consecutiveData['start_date'] ?? null;
            $endDate = $consecutiveData['end_date'] ?? null;

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);

                while ($start->lte($end)) {
                    $dates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
                Log::info('✓ Consecutive dates: ' . $startDate . ' to ' . $endDate);
            }
        }

        // Check multiple reservation
        if ($reservation->multiple) {
            $multipleData = $reservation->multiple->getAttributes();
            Log::info('Multiple reservation fields: ' . json_encode($multipleData));

            // Check for JSON dates field
            if (isset($multipleData['dates']) && $multipleData['dates']) {
                $multipleDates = json_decode($multipleData['dates'], true);
                if (is_array($multipleDates)) {
                    $dates = array_merge($dates, $multipleDates);
                    Log::info('✓ Multiple dates from JSON: ' . json_encode($multipleDates));
                }
            }

            // Check for individual date fields
            if (isset($multipleData['start_date']) && $multipleData['start_date']) {
                $dates[] = $multipleData['start_date'];
                Log::info('✓ Multiple start_date: ' . $multipleData['start_date']);
            }
        }

        $dates = array_filter(array_unique($dates));
        Log::info('Final dates: ' . json_encode($dates));

        return $dates;
    }

    private function getFormattedReservationTime($reservation)
    {
        $startTime = 'N/A';
        $endTime = 'N/A';

        if ($reservation->single) {
            // For single reservations, use time_from and time_to fields
            $startTime = $reservation->single->time_from ?? 'N/A';
            $endTime = $reservation->single->time_to ?? 'N/A';

            Log::info('Single extracted times - Start: ' . $startTime . ', End: ' . $endTime);
        } elseif ($reservation->consecutive) {
            // For consecutive reservations, use start_time_from and start_time_to fields
            $startTime = $reservation->consecutive->start_time_from ?? 'N/A';
            $endTime = $reservation->consecutive->start_time_to ?? 'N/A';

            Log::info('Consecutive extracted times - Start: ' . $startTime . ', End: ' . $endTime);
        } elseif ($reservation->multiple) {
            // For multiple reservations, use start_time_from and start_time_to fields
            $startTime = $reservation->multiple->start_time_from ?? 'N/A';
            $endTime = $reservation->multiple->start_time_to ?? 'N/A';

            Log::info('Multiple extracted times - Start: ' . $startTime . ', End: ' . $endTime);
        }

        Log::info('Final formatted times for reservation ' . $reservation->reservation_id . ' - Start: ' . $startTime . ', End: ' . $endTime);

        return [
            'start_time' => $startTime !== 'N/A' ? Carbon::parse($startTime)->format('g:i A') : 'N/A',
            'end_time' => $endTime !== 'N/A' ? Carbon::parse($endTime)->format('g:i A') : 'N/A',
            'raw_start' => $startTime,
            'raw_end' => $endTime
        ];
    }

    public function calendar(Request $request)
    {
        // Redirect to index method or return calendar view
        return $this->index($request);
    }
}
