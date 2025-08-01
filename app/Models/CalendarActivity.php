<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarActivity extends Model
{
    protected $primaryKey = 'calendar_activity_id';

    protected $fillable = [
        'date',
        'day_of_week',
    ];

    /**
     * Get accepted reservations for this date
     */
    public function acceptedReservations()
    {
        return ReservationRequest::where('status', 'accepted')
            ->where(function($query) {
                $query->whereHasMorph(
                    'reservationDetail',
                    [Single::class, Consecutive::class, Multiple::class],
                    function($q, $type) {
                        if ($type === Single::class) {
                            // For single reservations, check start_date
                            $q->where('start_date', $this->date);
                        } elseif ($type === Consecutive::class) {
                            // For consecutive reservations, check if date falls between start and end dates
                            $q->where(function($subQuery) {
                                $subQuery->where('start_date', '<=', $this->date)
                                    ->where('end_date', '>=', $this->date);
                            });
                        } elseif ($type === Multiple::class) {
                            // For multiple reservations, check start_date, intermediate_date, and end_date
                            $q->where(function($subQuery) {
                                $subQuery->where('start_date', $this->date)
                                    ->orWhere('intermediate_date', $this->date)
                                    ->orWhere('end_date', $this->date);
                            });
                        }
                    }
                );
            })
            ->with(['facility', 'equipment', 'reservationDetail']);
    }

    public static function checkAvailability($facilityId, $dates)
    {
        // Check for regular reservations
        $hasReservations = ReservationRequest::where('facility_id', $facilityId)
            ->where('status', 'accepted')
            ->where(function($query) use ($dates) {
                $query->whereHasMorph(
                    'reservationDetail',
                    [Single::class, Consecutive::class, Multiple::class],
                    function($q, $type) use ($dates) {
                        if ($type === Single::class) {
                            $q->whereIn('start_date', $dates);
                        } else {
                            $q->whereIn('start_date', $dates)
                                ->orWhereIn('intermediate_date', $dates)
                                ->orWhereIn('end_date', $dates);
                        }
                    }
                );
            })
            ->exists();

        // Check for admin blocks
        $hasAdminBlocks = AdminFacilityBlock::active()
            ->forFacility($facilityId)
            ->whereIn('date', $dates)
            ->exists();

        return $hasReservations || $hasAdminBlocks;
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(ReservationRequest::class, 'transaction_date', 'date')
            ->with(['facility', 'equipment', 'reservationDetail']);
    }
}
