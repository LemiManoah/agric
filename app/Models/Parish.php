<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parish extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcounty_id',
        'name',
    ];

    public function subcounty(): BelongsTo
    {
        return $this->belongsTo(Subcounty::class);
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    public function farmerLocations(): HasMany
    {
        return $this->hasMany(FarmerLocation::class);
    }
}
