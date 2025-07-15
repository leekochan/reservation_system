<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $primaryKey = 'equipment_id';
    protected $table = 'equipments';

    public function reservations(): HasMany
    {
        return $this->hasMany(ReservationRequest::class, 'equipment_id', 'equipment_id');
    }
}
