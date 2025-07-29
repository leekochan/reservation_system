<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Equipment extends Model
{
    protected $primaryKey = 'equipment_id';
    protected $table = 'equipments';

    protected $fillable = [
        'equipment_name',
        'picture',
        'units',
        'cost_per_unit',
        'status'
    ];

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(ReservationRequest::class, 'equipment_reservation', 'equipment_id', 'reservation_id')
                    ->withPivot('quantity', 'reservation_date')
                    ->withTimestamps();
    }

    public function details(): HasOne
    {
        return $this->hasOne(EquipmentDetails::class, 'equipment_id', 'equipment_id');
    }

    /**
     * Get available units for a specific date
     */
    public function getAvailableUnitsForDate($date)
    {
        $reservedUnits = $this->reservations()
            ->where('status', 'accepted')
            ->wherePivot('reservation_date', $date)
            ->sum('equipment_reservation.quantity');

        return $this->units - $reservedUnits;
    }

    /**
     * Check if enough units are available for reservation
     */
    public function hasAvailableUnits($date, $requestedQuantity)
    {
        return $this->getAvailableUnitsForDate($date) >= $requestedQuantity;
    }
}
