<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Equipment;
use App\Models\FacilityDetails;
use App\Models\EquipmentDetails;
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
        $facilities = Facility::with('details')->where('status', 'available')->get();
        $equipments = Equipment::with('details')->where('status', 'available')
            ->get(['equipment_id', 'equipment_name', 'units']);
        $calendarActivities = CalendarActivity::all();

        return view('user-reservation', [
            'facilities' => $facilities,
            'equipments' => $equipments,
            'calendarActivities' => $calendarActivities
        ]);
    }

    /**
     * Get available units for specific equipment on a specific date
     */
    public function getAvailableUnits(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipments,equipment_id',
            'date' => 'required|date_format:Y-m-d'
        ]);

        $equipment = Equipment::findOrFail($request->equipment_id);
        $availableUnits = $equipment->getAvailableUnitsForDate($request->date);

        return response()->json([
            'available_units' => $availableUnits,
            'total_units' => $equipment->units
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

        // Custom validation for time ranges
        $timeValidationErrors = $this->validateTimeRanges($request);
        if (!empty($timeValidationErrors)) {
            return redirect()->back()->withErrors($timeValidationErrors)->withInput();
        }

        // Add equipment data to validated array for payment calculation if equipment is needed
        if ($request->need_equipment === 'yes' && $request->has('equipment') && is_array($request->equipment)) {
            $validated['equipment'] = $request->equipment;
        }

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

            // Calculate payment estimation
            $paymentEstimation = $this->calculatePaymentEstimation($reservation, $validated);
            
            // Update the reservation with the calculated total payment
            $reservation->update(['total_payment' => $paymentEstimation['total_payment']]);

            DB::commit();

            // Prepare detailed success message with payment estimation
            $successMessage = $this->prepareSuccessMessage($validated, $paymentEstimation);

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit reservation: ' . $e->getMessage());
        }
    }

    protected function calculatePaymentEstimation($reservation, $validated)
    {
        $facilityPayment = $this->calculateFacilityPayment($reservation, $validated);
        $equipmentPayment = $this->calculateEquipmentPayment($reservation, $validated);
        
        return [
            'facility_payment' => $facilityPayment,
            'equipment_payment' => $equipmentPayment,
            'total_payment' => $facilityPayment + $equipmentPayment
        ];
    }

    protected function calculateFacilityPayment($reservation, $validated)
    {
        $facility = Facility::with('details')->find($validated['facility_id']);
        
        if (!$facility || !$facility->details) {
            return 0;
        }

        $totalHours = $this->calculateTotalReservationHours($validated);
        $facilityDetails = $facility->details;

        // Package 1: More than 8 hours (whole day)
        if ($totalHours > 8 && !is_null($facilityDetails->facility_package_rate1) && $facilityDetails->facility_package_rate1 > 0) {
            return floatval($facilityDetails->facility_package_rate1);
        }
        
        // Package 2: More than 4 hours but less than or equal to 8 hours (half day)
        if ($totalHours > 4 && $totalHours <= 8 && !is_null($facilityDetails->facility_package_rate2) && $facilityDetails->facility_package_rate2 > 0) {
            return floatval($facilityDetails->facility_package_rate2);
        }
        
        // Hourly rate: Less than or equal to 4 hours OR fallback when packages not available
        if (!is_null($facilityDetails->facility_per_hour_rate) && $facilityDetails->facility_per_hour_rate > 0) {
            return floatval($facilityDetails->facility_per_hour_rate) * $totalHours;
        }
        
        // Fallback: Use Package 2 for any duration if no hourly rate
        if (!is_null($facilityDetails->facility_package_rate2) && $facilityDetails->facility_package_rate2 > 0) {
            return floatval($facilityDetails->facility_package_rate2);
        }
        
        // Last resort: Use Package 1 for any duration if no other pricing available
        if (!is_null($facilityDetails->facility_package_rate1) && $facilityDetails->facility_package_rate1 > 0) {
            return floatval($facilityDetails->facility_package_rate1);
        }

        return 0;
    }

    protected function calculateEquipmentPayment($reservation, $validated)
    {
        if (!isset($validated['equipment']) || empty($validated['equipment'])) {
            return 0;
        }

        $totalEquipmentPayment = 0;
        $totalHours = $this->calculateTotalReservationHours($validated);

        foreach ($validated['equipment'] as $equipmentData) {
            $equipment = Equipment::with('details')->find($equipmentData['equipment_id']);
            
            if (!$equipment || !$equipment->details) {
                continue;
            }

            $equipmentDetails = $equipment->details;
            $quantity = intval($equipmentData['quantity']);
            $equipmentBasePayment = 0;

            // Calculate base payment per equipment unit based on total reservation hours
            // Package 1: More than 8 hours (whole day)
            if ($totalHours > 8 && !is_null($equipmentDetails->equipment_package_rate1) && $equipmentDetails->equipment_package_rate1 > 0) {
                $equipmentBasePayment = floatval($equipmentDetails->equipment_package_rate1);
            }
            // Package 2: More than 4 hours but less than or equal to 8 hours (half day)
            elseif ($totalHours > 4 && $totalHours <= 8 && !is_null($equipmentDetails->equipment_package_rate2) && $equipmentDetails->equipment_package_rate2 > 0) {
                $equipmentBasePayment = floatval($equipmentDetails->equipment_package_rate2);
            }
            // Hourly rate: Less than or equal to 4 hours OR fallback when packages not available
            elseif (!is_null($equipmentDetails->equipment_per_hour_rate) && $equipmentDetails->equipment_per_hour_rate > 0) {
                $equipmentBasePayment = floatval($equipmentDetails->equipment_per_hour_rate) * $totalHours;
            }
            // Fallback: Use Package 2 for any duration if no hourly rate
            elseif (!is_null($equipmentDetails->equipment_package_rate2) && $equipmentDetails->equipment_package_rate2 > 0) {
                $equipmentBasePayment = floatval($equipmentDetails->equipment_package_rate2);
            }
            // Last resort: Use Package 1 for any duration if no other pricing available
            elseif (!is_null($equipmentDetails->equipment_package_rate1) && $equipmentDetails->equipment_package_rate1 > 0) {
                $equipmentBasePayment = floatval($equipmentDetails->equipment_package_rate1);
            }

            // Multiply base payment by quantity (number of units)
            $totalEquipmentPayment += $equipmentBasePayment * $quantity;
        }

        return $totalEquipmentPayment;
    }

    protected function calculateTotalReservationHours($validated)
    {
        $totalHours = 0;
        $dates = $validated['dates'] ?? [];

        foreach ($dates as $dateData) {
            $timeFrom = $dateData['time_from'] ?? '';
            $timeTo = $dateData['time_to'] ?? '';
            
            if (empty($timeFrom) || empty($timeTo)) {
                continue;
            }
            
            $fromTime = \DateTime::createFromFormat('H:i', $timeFrom);
            $toTime = \DateTime::createFromFormat('H:i', $timeTo);
            
            if ($fromTime && $toTime) {
                // Handle case where end time is same as start time (treat as minimum 1 hour)
                if ($timeFrom === $timeTo) {
                    $totalHours += 1; // Minimum 1 hour for same start/end time
                    continue;
                }
                
                // Handle case where end time is before start time (crosses midnight)
                if ($toTime < $fromTime) {
                    $toTime->add(new \DateInterval('P1D'));
                }
                
                $interval = $fromTime->diff($toTime);
                $hours = $interval->h + ($interval->i / 60);
                $totalHours += $hours;
            }
        }

        return $totalHours;
    }

    protected function validateTimeRanges($request)
    {
        $errors = [];
        $dates = $request->dates ?? [];
        
        foreach ($dates as $index => $dateData) {
            $timeFrom = $dateData['time_from'] ?? '';
            $timeTo = $dateData['time_to'] ?? '';
            
            if (empty($timeFrom) || empty($timeTo)) {
                continue;
            }
            
            // Check if start time equals end time
            if ($timeFrom === $timeTo) {
                $errors["dates.{$index}.time_range"] = "End time must be different from start time for date entry " . ($index + 1);
                continue;
            }
            
            // Parse times
            $fromTime = \DateTime::createFromFormat('H:i', $timeFrom);
            $toTime = \DateTime::createFromFormat('H:i', $timeTo);
            
            if ($fromTime && $toTime) {
                // Check if end time is before start time (and not crossing midnight intentionally)
                if ($toTime <= $fromTime) {
                    // Allow overnight reservations only if they make sense (e.g., start at 22:00, end at 06:00)
                    // But reject clearly wrong times like 14:00 to 08:00
                    if ($fromTime->format('H') < 20 && $toTime->format('H') > 6) {
                        $errors["dates.{$index}.time_range"] = "End time must be after start time for date entry " . ($index + 1);
                    }
                }
                
                // Calculate duration and ensure minimum 1 hour
                $interval = $fromTime->diff($toTime);
                $hours = $interval->h + ($interval->i / 60);
                
                if ($toTime < $fromTime) {
                    // Overnight - add 24 hours
                    $hours += 24;
                }
                
                if ($hours < 1) {
                    $errors["dates.{$index}.time_range"] = "Reservation must be at least 1 hour long for date entry " . ($index + 1);
                }
            }
        }
        
        return $errors;
    }

    protected function prepareSuccessMessage($validated, $paymentEstimation)
    {
        $facility = Facility::find($validated['facility_id']);
        $facilityName = $facility ? $facility->facility_name : 'Unknown Facility';

        $message = "<div class='reservation-success-details'>";
        $message .= "<div class='grid grid-cols-1 md:grid-cols-2 gap-4 text-sm'>";
        
        // Personal Information
        $message .= "<div>";
        $message .= "<h4 class='font-semibold text-blue-600 mb-2'>Personal Information</h4>";
        $message .= "<p><strong>Name:</strong> " . htmlspecialchars($validated['name']) . "</p>";
        $message .= "<p><strong>Email:</strong> " . htmlspecialchars($validated['email']) . "</p>";
        $message .= "<p><strong>Organization:</strong> " . htmlspecialchars($validated['organization']) . "</p>";
        $message .= "<p><strong>Purpose:</strong> " . htmlspecialchars($validated['purpose']) . "</p>";
        $message .= "</div>";

        // Reservation Information
        $message .= "<div>";
        $message .= "<h4 class='font-semibold text-blue-600 mb-2'>Reservation Information</h4>";
        $message .= "<p><strong>Facility:</strong> " . htmlspecialchars($facilityName) . "</p>";
        $message .= "<p><strong>Reservation Type:</strong> " . ucfirst($validated['reservation_type']) . "</p>";
        $message .= "<p><strong>Transaction Date:</strong> " . htmlspecialchars($validated['transaction_date']) . "</p>";
        
        // Dates and Times
        if (isset($validated['dates'])) {
            $message .= "<p><strong>Scheduled Dates:</strong></p>";
            $message .= "<ul class='ml-4 list-disc'>";
            foreach ($validated['dates'] as $index => $dateData) {
                $message .= "<li>" . htmlspecialchars($dateData['date']) . " from " . 
                           htmlspecialchars($dateData['time_from']) . " to " . 
                           htmlspecialchars($dateData['time_to']) . "</li>";
            }
            $message .= "</ul>";
        }
        $message .= "</div>";

        // Equipment Information
        if (isset($validated['equipment']) && !empty($validated['equipment'])) {
            $message .= "<div class='md:col-span-2'>";
            $message .= "<h4 class='font-semibold text-blue-600 mb-2'>🔧 Equipment Requested</h4>";
            $message .= "<ul class='ml-4 list-disc'>";
            
            $totalHours = $this->calculateTotalReservationHours($validated);
            
            foreach ($validated['equipment'] as $equipmentData) {
                $equipment = Equipment::with('details')->find($equipmentData['equipment_id']);
                if ($equipment) {
                    $quantity = intval($equipmentData['quantity']);
                    $message .= "<li>" . htmlspecialchars($equipment->equipment_name) . 
                               " - Quantity: " . htmlspecialchars($equipmentData['quantity']) . " units";
                    
                    // Show pricing calculation for this equipment
                    if ($equipment->details) {
                        $equipmentDetails = $equipment->details;
                        $basePayment = 0;
                        $rateType = '';
                        
                        if ($totalHours > 8 && !is_null($equipmentDetails->equipment_package_rate1) && $equipmentDetails->equipment_package_rate1 > 0) {
                            $basePayment = floatval($equipmentDetails->equipment_package_rate1);
                            $rateType = 'Package 1 (Whole Day)';
                        } elseif ($totalHours > 4 && $totalHours <= 8 && !is_null($equipmentDetails->equipment_package_rate2) && $equipmentDetails->equipment_package_rate2 > 0) {
                            $basePayment = floatval($equipmentDetails->equipment_package_rate2);
                            $rateType = 'Package 2 (Half Day)';
                        } elseif (!is_null($equipmentDetails->equipment_per_hour_rate) && $equipmentDetails->equipment_per_hour_rate > 0) {
                            $basePayment = floatval($equipmentDetails->equipment_per_hour_rate) * $totalHours;
                            $rateType = 'Hourly Rate (' . number_format($totalHours, 1) . ' hrs × ₱' . number_format($equipmentDetails->equipment_per_hour_rate, 2) . ')';
                        } elseif (!is_null($equipmentDetails->equipment_package_rate2) && $equipmentDetails->equipment_package_rate2 > 0) {
                            $basePayment = floatval($equipmentDetails->equipment_package_rate2);
                            $rateType = 'Package 2 (Half Day - Fallback)';
                        } elseif (!is_null($equipmentDetails->equipment_package_rate1) && $equipmentDetails->equipment_package_rate1 > 0) {
                            $basePayment = floatval($equipmentDetails->equipment_package_rate1);
                            $rateType = 'Package 1 (Whole Day - Fallback)';
                        }
                        
                        if ($basePayment > 0) {
                            $totalEquipmentCost = $basePayment * $quantity;
                            $message .= "<br><span class='text-sm text-gray-600 ml-2'>└ " . $rateType . ": ₱" . number_format($basePayment, 2) . " × " . $quantity . " units = ₱" . number_format($totalEquipmentCost, 2) . "</span>";
                        }
                    }
                    
                    $message .= "</li>";
                }
            }
            $message .= "</ul>";
            $message .= "</div>";
        }

        // Payment Estimation
        $totalHours = $this->calculateTotalReservationHours($validated);
        $message .= "<div class='md:col-span-2 mt-4 p-4 bg-green-50 rounded-lg border border-green-200'>";
        $message .= "<h4 class='font-semibold text-green-700 mb-2'>💰 Payment Estimation</h4>";
        
        $message .= "<p class='text-sm text-gray-600 mb-3'>📊 Total Reservation Hours: " . number_format($totalHours, 1) . " hours</p>";
        
        if ($paymentEstimation['facility_payment'] > 0) {
            // Show facility calculation details
            $facility = Facility::with('details')->find($validated['facility_id']);
            if ($facility && $facility->details) {
                $facilityDetails = $facility->details;
                $rateType = '';
                
                if ($totalHours > 8 && !is_null($facilityDetails->facility_package_rate1) && $facilityDetails->facility_package_rate1 > 0) {
                    $rateType = 'Package 1 (Whole Day)';
                } elseif ($totalHours > 4 && $totalHours <= 8 && !is_null($facilityDetails->facility_package_rate2) && $facilityDetails->facility_package_rate2 > 0) {
                    $rateType = 'Package 2 (Half Day)';
                } elseif (!is_null($facilityDetails->facility_per_hour_rate) && $facilityDetails->facility_per_hour_rate > 0) {
                    $rateType = 'Hourly Rate (' . number_format($totalHours, 1) . ' hrs × ₱' . number_format($facilityDetails->facility_per_hour_rate, 2) . ')';
                } elseif (!is_null($facilityDetails->facility_package_rate2) && $facilityDetails->facility_package_rate2 > 0) {
                    $rateType = 'Package 2 (Half Day - Fallback)';
                } elseif (!is_null($facilityDetails->facility_package_rate1) && $facilityDetails->facility_package_rate1 > 0) {
                    $rateType = 'Package 1 (Whole Day - Fallback)';
                } else {
                    $rateType = 'Unknown Rate';
                }
                
                $message .= "<p><strong>🏢 Facility Fee (" . $rateType . "):</strong> ₱" . number_format($paymentEstimation['facility_payment'], 2) . "</p>";
            } else {
                $message .= "<p><strong>🏢 Facility Fee:</strong> ₱" . number_format($paymentEstimation['facility_payment'], 2) . "</p>";
            }
        }
        
        if ($paymentEstimation['equipment_payment'] > 0) {
            $message .= "<p><strong>🔧 Equipment Fee (Total for all units):</strong> ₱" . number_format($paymentEstimation['equipment_payment'], 2) . "</p>";
        }
        
        $message .= "<div class='border-t border-green-300 mt-2 pt-2'>";
        $message .= "<p class='text-lg font-bold text-green-800'><strong>💳 Total Estimated Payment:</strong> ₱" . 
                   number_format($paymentEstimation['total_payment'], 2) . "</p>";
        $message .= "</div>";
        $message .= "<p class='text-sm text-gray-600 mt-2'><em>ℹ️ Note: This is an estimated amount. Final payment may vary based on additional charges or adjustments.</em></p>";
        $message .= "</div>";

        $message .= "</div>";
        $message .= "</div>";

        return $message;
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
