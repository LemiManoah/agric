<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'parish_id',
        'name',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function farmerLocations(): HasMany
    {
        return $this->hasMany(FarmerLocation::class);
    }
}
