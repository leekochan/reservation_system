<?php

namespace App\Models;
use App\Models\Single;
use App\Models\Consecutive;
use App\Models\Multiple;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReservationRequest extends Model
{
    protected $primaryKey = 'reservation_id';

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'facility_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'equipment_id');
    }

    public function reservationDetail(): MorphTo
    {
        return $this->morphTo(
            'reservationDetail',
            'reservation_type',  // This matches your DB column
            'reservation_id',    // This matches your DB column
            'reservation_id'     // Local key
        );
    }

//    protected static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope(function ($builder) {
//            Relation::morphMap([
//                'Single' => Single::class,
//                'Consecutive' => Consecutive::class,
//                'Multiple' => Multiple::class,
//            ]);
//        });
//    }

    public function single(): hasOne
    {
        return $this->hasOne(\App\Models\Single::class, 'reservation_id', 'reservation_id');
    }

    public function consecutive(): hasOne
    {
        return $this->hasOne(\App\Models\Consecutive::class, 'reservation_id', 'reservation_id');
    }

    public function multiple(): hasOne
    {
        return $this->hasOne(\App\Models\Multiple::class, 'reservation_id', 'reservation_id');
    }
}
