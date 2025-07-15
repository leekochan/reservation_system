<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Consecutive extends Model
{
    protected $table = 'consecutive';

    public function reservationRequest(): MorphOne
    {
        return $this->morphOne(ReservationRequest::class, 'reservationDetail');
    }
}
