<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Facility extends Model
{
    protected $primaryKey = 'facility_id';
    protected $table = 'facilities';

    protected $fillable = [
        'facility_name',
        'picture',
        'status',
        'facility_condition'
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(ReservationRequest::class, 'facility_id', 'facility_id');
    }

    public function details(): HasOne
    {
        return $this->hasOne(FacilityDetails::class, 'facility_id', 'facility_id');
    }
}
