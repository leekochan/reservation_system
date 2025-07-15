<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Multiple extends Model
{
    protected $table = 'multiples';

    public function reservationRequest(): MorphOne
    {
        return $this->morphOne(ReservationRequest::class, 'reservationDetail');
    }
}
