<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcounty extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name',
        'code',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function parishes(): HasMany
    {
        return $this->hasMany(Parish::class);
    }

    public function farmerLocations(): HasMany
    {
        return $this->hasMany(FarmerLocation::class);
    }
}
