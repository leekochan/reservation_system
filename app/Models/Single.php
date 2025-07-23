<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Single extends Model
{
    protected $table = 'singles';
    
    protected $fillable = [
        'reservation_id',
        'start_date',
        'time_from',
        'time_to'
    ];

    public function reservationRequest(): MorphOne
    {
        return $this->morphOne(ReservationRequest::class, 'reservationDetail');
    }
}
