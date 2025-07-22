<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentDetails extends Model
{
    protected $primaryKey = 'equipment_details_id';
    protected $table = 'equipment_details';

    protected $fillable = [
        'equipment_id',
        'equipment_per_hour_rate',
        'equipment_package_rate1',
        'equipment_package_rate2'
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'equipment_id');
    }
}
