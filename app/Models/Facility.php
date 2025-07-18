<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    protected $primaryKey = 'facility_id';
    protected $table = 'facilities';
    public function reservations(): HasMany
    {
        return $this->hasMany(ReservationRequest::class, 'facility_id', 'facility_id');
    }
}
