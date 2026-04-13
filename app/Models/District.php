<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'name',
        'code',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function subcounties(): HasMany
    {
        return $this->hasMany(Subcounty::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function farmerLocations(): HasMany
    {
        return $this->hasMany(FarmerLocation::class);
    }
}
