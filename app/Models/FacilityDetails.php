<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityDetails extends Model
{
    protected $primaryKey = 'facility_details_id';
    protected $table = 'facility_details';

    protected $fillable = [
        'facility_id',
        'facility_per_hour_rate',
        'facility_package_rate1',
        'facility_package_rate2'
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'facility_id');
    }
}
