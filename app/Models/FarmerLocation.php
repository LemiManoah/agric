<?php

namespace App\Models;

use App\Enums\InternetAccessLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'region_id',
        'district_id',
        'subcounty_id',
        'parish_id',
        'village_id',
        'latitude',
        'longitude',
        'farm_boundary_geojson',
        'nearest_trading_centre',
        'distance_to_tarmac_road_km',
        'internet_access_level',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'distance_to_tarmac_road_km' => 'decimal:2',
            'internet_access_level' => InternetAccessLevel::class,
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function subcounty(): BelongsTo
    {
        return $this->belongsTo(Subcounty::class);
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
