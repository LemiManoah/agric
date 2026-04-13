<?php

namespace App\Models;

use App\Enums\MarketDestination;
use App\Enums\ProductionScale;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerValueChain extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'value_chain_id',
        'production_scale',
        'estimated_seasonal_harvest_kg',
        'current_market_destination',
        'input_access_details',
    ];

    protected function casts(): array
    {
        return [
            'production_scale' => ProductionScale::class,
            'estimated_seasonal_harvest_kg' => 'decimal:2',
            'current_market_destination' => MarketDestination::class,
            'input_access_details' => 'array',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function valueChain(): BelongsTo
    {
        return $this->belongsTo(ValueChain::class);
    }
}
