<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function reservations(): HasMany
    {
        return $this->hasMany(ReservationRequest::class, 'equipment_id', 'equipment_id');
    }

    public function details(): HasOne
    {
        return $this->hasOne(EquipmentDetails::class, 'equipment_id', 'equipment_id');
    }
}
