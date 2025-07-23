<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Consecutive extends Model
{
    protected $table = 'consecutive';
    
    protected $fillable = [
        'reservation_id',
        'start_date',
        'start_time_from',
        'start_time_to',
        'end_date',
        'end_time_from',
        'end_time_to',
        'intermediate_date',
        'intermediate_time_from',
        'intermediate_time_to'
    ];

    public function reservationRequest(): MorphOne
    {
        return $this->morphOne(ReservationRequest::class, 'reservationDetail');
    }
}
