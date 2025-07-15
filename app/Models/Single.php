<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Single extends Model
{
    protected $table = 'singles';

    public function reservationRequest(): MorphOne
    {
        return $this->morphOne(ReservationRequest::class, 'reservationDetail');
    }
}
