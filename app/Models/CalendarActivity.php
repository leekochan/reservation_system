<?php

namespace App\Models;
use App\Models\Single;
use App\Models\Consecutive;
use App\Models\Multiple;
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
                            $q->where('start_date', $this->date);
                        } else {
                            $q->where('start_date', $this->date)
                                ->orWhere('intermediate_date', $this->date)
                                ->orWhere('end_date', $this->date);
                        }
                    }
                );
            })
            ->with(['facility', 'equipment', 'reservationDetail']);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(ReservationRequest::class, 'transaction_date', 'date')
            ->with(['facility', 'equipment', 'reservationDetail']);
    }
}
