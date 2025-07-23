<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AdminFacilityBlock extends Model
{
    protected $primaryKey = 'block_id';

    protected $fillable = [
        'facility_id',
        'date',
        'start_time',
        'end_time',
        'purpose',
        'notes',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'facility_id');
    }

    /**
     * Check if this block conflicts with a given time range
     */
    public function conflictsWith($startTime, $endTime): bool
    {
        return !($this->end_time <= $startTime || $this->start_time >= $endTime);
    }

    /**
     * Scope for active blocks
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for blocks on a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope for blocks on a specific facility
     */
    public function scopeForFacility($query, $facilityId)
    {
        return $query->where('facility_id', $facilityId);
    }

    /**
     * Check if a facility has any time conflicts on a specific date and time range
     */
    public static function hasTimeConflict($facilityId, $date, $startTime, $endTime)
    {
        return self::active()
            ->forFacility($facilityId)
            ->forDate($date)
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    // Check if new time range overlaps with existing blocks
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->exists();
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return Carbon::parse($this->start_time)->format('g:i A') . ' - ' .
            Carbon::parse($this->end_time)->format('g:i A');
    }
}
