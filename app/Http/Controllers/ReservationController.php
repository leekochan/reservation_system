<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Equipment;
use App\Models\CalendarActivity;
use App\Models\ReservationRequest;
use App\Models\Single;
use App\Models\Consecutive;
use App\Models\Multiple;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function userReservation()
    {
        $facilities = Facility::where('status', 'available')->get();
        $equipments = Equipment::where('status', 'available')
            ->get(['equipment_id', 'equipment_name', 'units']);
        $calendarActivities = CalendarActivity::all();

        return view('user-reservation', [
            'facilities' => $facilities,
            'equipments' => $equipments,
            'calendarActivities' => $calendarActivities
        ]);
    }

    public function storeReservation(Request $request)
    {
        // Basic validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'organization' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'other_details' => 'required|string',
            'personal_equipment' => 'required|in:yes,no',
            'transaction_date' => 'required|date_format:m/d/Y',
            'reservation_type' => 'required|in:single,consecutive,multiple',
            'facility_id' => 'required|integer|min:1|exists:facilities,facility_id',
            'need_equipment' => 'required|in:yes,no',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ];

        // Add validation rules based on reservation type
        switch ($request->reservation_type) {
            case 'single':
                $rules['dates.0.date'] = 'required|date_format:Y-m-d';
                $rules['dates.0.time_from'] = 'required|date_format:H:i';
                $rules['dates.0.time_to'] = 'required|date_format:H:i';
                break;

            case 'consecutive':
                $rules['days_count'] = 'required|integer|min:2|max:3';
                for ($i = 0; $i < ($request->days_count ?? 2); $i++) {
                    $rules["dates.$i.date"] = 'required|date_format:Y-m-d';
                    $rules["dates.$i.time_from"] = 'required|date_format:H:i';
                    $rules["dates.$i.time_to"] = 'required|date_format:H:i';
                }
                break;

            case 'multiple':
                $rules['dates'] = 'required|array|min:2|max:3';
                foreach (($request->dates ?? []) as $index => $date) {
                    $rules["dates.$index.date"] = 'required|date_format:Y-m-d';
                    $rules["dates.$index.time_from"] = 'required|date_format:H:i';
                    $rules["dates.$index.time_to"] = 'required|date_format:H:i';
                }
                break;
        }

        // Equipment validation if needed
        if ($request->need_equipment === 'yes' && $request->has('equipment')) {
            $rules['equipment.*.equipment_id'] = 'required|exists:equipments,equipment_id';
            $rules['equipment.*.quantity'] = 'required|integer|min:1';
            $rules['equipment.*.date'] = 'required|date_format:Y-m-d';
        }

        $validated = $request->validate($rules);

        // Convert transaction_date from MM/DD/YYYY to YYYY-MM-DD format
        if (isset($validated['transaction_date'])) {
            $date = \DateTime::createFromFormat('m/d/Y', $validated['transaction_date']);
            if ($date) {
                $validated['transaction_date'] = $date->format('Y-m-d');
            }
        }

        // Check for overlapping reservations (only for single reservations)
        if ($validated['reservation_type'] === 'single') {
            $overlapError = $this->checkForOverlappingReservations($validated);
            if ($overlapError) {
                return redirect()->back()->withErrors(['overlap' => $overlapError])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Handle signature file upload
            $signaturePath = '';
            if ($request->hasFile('signature')) {
                $signatureFile = $request->file('signature');
                if ($signatureFile->isValid()) {
                    // Store in public/signatures directory
                    $signaturePath = $signatureFile->store('signatures', 'public');
                }
            }

                        // Create the reservation request
            $reservation = ReservationRequest::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'organization' => $validated['organization'],
                'contact_no' => $validated['email'], // Using email as contact for now
                'purpose' => $validated['purpose'],
                'instruction' => $validated['other_details'],
                'electric_equipment' => $validated['personal_equipment'] === 'yes' ? 
                    ($request->personal_equipment_details ?? 'Personal equipment needed') : 'none',
                'transaction_date' => $validated['transaction_date'],
                'reservation_type' => ucfirst($validated['reservation_type']),
                'facility_id' => $validated['facility_id'],
                'equipment_id' => null, // No default equipment - use pivot table for equipment relationships
                'signature' => $signaturePath,
                'status' => 'pending',
                'total_payment' => '0', // You'll calculate this later
            ]);

            // Handle different reservation types
            switch ($validated['reservation_type']) {
                case 'single':
                    if (isset($validated['dates'][0])) {
                        $this->handleSingleReservation($reservation, $validated['dates'][0]);
                    }
                    break;

                case 'consecutive':
                    if (isset($validated['dates'])) {
                        $this->handleConsecutiveReservation($reservation, $validated['dates'], $validated['days_count'] ?? 2);
                    }
                    break;

                case 'multiple':
                    if (isset($validated['dates'])) {
                        $this->handleMultipleReservation($reservation, $validated['dates']);
                    }
                    break;
            }

            // Handle equipment if needed
            if ($request->need_equipment === 'yes' && $request->has('equipment')) {
                $this->handleEquipment($reservation, $request->equipment);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Reservation submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit reservation: ' . $e->getMessage());
        }
    }

    protected function handleSingleReservation($reservation, $dateData)
    {
        Single::create([
            'reservation_id' => $reservation->reservation_id,
            'start_date' => $dateData['date'],
            'time_from' => $dateData['time_from'],
            'time_to' => $dateData['time_to'],
        ]);
    }

    protected function handleConsecutiveReservation($reservation, $dates, $daysCount)
    {
        $data = [
            'reservation_id' => $reservation->reservation_id,
            'start_date' => $dates[0]['date'],
            'start_time_from' => $dates[0]['time_from'],
            'start_time_to' => $dates[0]['time_to'],
            'end_date' => $dates[$daysCount-1]['date'],
            'end_time_from' => $dates[$daysCount-1]['time_from'],
            'end_time_to' => $dates[$daysCount-1]['time_to'],
        ];

        // For 3-day consecutive, add intermediate date
        if ($daysCount === 3) {
            $data['intermediate_date'] = $dates[1]['date'];
            $data['intermediate_time_from'] = $dates[1]['time_from'];
            $data['intermediate_time_to'] = $dates[1]['time_to'];
        }

        Consecutive::create($data);
    }

    protected function handleMultipleReservation($reservation, $dates)
    {
        $data = [
            'reservation_id' => $reservation->reservation_id,
            'start_date' => $dates[0]['date'],
            'start_time_from' => $dates[0]['time_from'],
            'start_time_to' => $dates[0]['time_to'],
            'end_date' => $dates[count($dates)-1]['date'],
            'end_time_from' => $dates[count($dates)-1]['time_from'],
            'end_time_to' => $dates[count($dates)-1]['time_to'],
        ];

        // For 3-day multiple, add intermediate date
        if (count($dates) === 3) {
            $data['intermediate_date'] = $dates[1]['date'];
            $data['intermediate_time_from'] = $dates[1]['time_from'];
            $data['intermediate_time_to'] = $dates[1]['time_to'];
        }

        Multiple::create($data);
    }

    protected function handleEquipment($reservation, $equipmentData)
    {
        foreach ($equipmentData as $equipment) {
            $reservation->equipments()->attach($equipment['equipment_id'], [
                'quantity' => $equipment['quantity'],
                'reservation_date' => $equipment['date'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Check for overlapping reservations before creating a new one
     */
    protected function checkForOverlappingReservations($validated)
    {
        $facilityId = $validated['facility_id'];
        $reservationType = $validated['reservation_type'];
        $dates = $validated['dates'] ?? [];

        foreach ($dates as $dateData) {
            $date = $dateData['date'];
            $timeFrom = $dateData['time_from'];
            $timeTo = $dateData['time_to'];

            // Get all existing accepted reservations for this facility and date
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
                                        ->orWhere('end_date', $date)
                                        ->orWhere('intermediate_date', $date);
                                });
                            } elseif ($type === Multiple::class) {
                                $q->where(function($subQuery) use ($date) {
                                    $subQuery->where('start_date', $date)
                                        ->orWhere('end_date', $date)
                                        ->orWhere('intermediate_date', $date);
                                });
                            }
                        }
                    );
                })
                ->with('reservationDetail')
                ->get();

            // Check each existing reservation for time overlap
            foreach ($existingReservations as $existingReservation) {
                $detail = $existingReservation->reservationDetail;
                $existingTimes = [];

                if ($detail instanceof Single) {
                    if ($detail->start_date === $date) {
                        $existingTimes[] = [
                            'from' => $detail->time_from,
                            'to' => $detail->time_to
                        ];
                    }
                } elseif ($detail instanceof Consecutive) {
                    if ($detail->start_date === $date) {
                        $existingTimes[] = [
                            'from' => $detail->start_time_from,
                            'to' => $detail->start_time_to
                        ];
                    }
                    if ($detail->end_date === $date) {
                        $existingTimes[] = [
                            'from' => $detail->end_time_from,
                            'to' => $detail->end_time_to
                        ];
                    }
                    if ($detail->intermediate_date === $date) {
                        $existingTimes[] = [
                            'from' => $detail->intermediate_time_from,
                            'to' => $detail->intermediate_time_to
                        ];
                    }
                } elseif ($detail instanceof Multiple) {
                    if ($detail->start_date === $date) {
                        $existingTimes[] = [
                            'from' => $detail->start_time_from,
                            'to' => $detail->start_time_to
                        ];
                    }
                    if ($detail->end_date === $date) {
                        $existingTimes[] = [
                            'from' => $detail->end_time_from,
                            'to' => $detail->end_time_to
                        ];
                    }
                    if ($detail->intermediate_date === $date) {
                        $existingTimes[] = [
                            'from' => $detail->intermediate_time_from,
                            'to' => $detail->intermediate_time_to
                        ];
                    }
                }

                // Check for time overlaps with buffer
                foreach ($existingTimes as $existingTime) {
                    if ($this->timesOverlap($timeFrom, $timeTo, $existingTime['from'], $existingTime['to'])) {
                        return "Time conflict detected on {$date} from {$timeFrom} to {$timeTo}. This time overlaps with an existing reservation from {$existingTime['from']} to {$existingTime['to']}.";
                    }
                }
            }
        }

        return null; // No overlaps found
    }

    /**
     * Check if two time ranges overlap (including 1-hour buffer)
     */
    protected function timesOverlap($newFrom, $newTo, $existingFrom, $existingTo)
    {
        // Add 1-hour buffer around existing reservation
        $bufferedStart = date('H:i', strtotime($existingFrom . ' -1 hour'));
        $bufferedEnd = date('H:i', strtotime($existingTo . ' +1 hour'));
        
        // Ensure buffer doesn't go outside business hours
        if ($bufferedStart < '08:00') $bufferedStart = '08:00';
        if ($bufferedEnd > '17:30') $bufferedEnd = '17:30';

        // Check if new reservation overlaps with buffered existing reservation
        return !($newTo <= $bufferedStart || $newFrom >= $bufferedEnd);
    }
}
