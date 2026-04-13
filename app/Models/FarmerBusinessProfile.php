<?php

namespace App\Models;

use App\Enums\IrrigationAvailability;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerBusinessProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'farm_name',
        'ursb_registration_number',
        'farm_size_acres',
        'number_of_plots',
        'irrigation_availability',
        'post_harvest_storage_capacity_tonnes',
        'has_warehouse_access',
        'cooperative_member',
        'cooperative_name',
        'cooperative_role',
        'average_annual_income_bracket',
    ];

    protected function casts(): array
    {
        return [
            'farm_size_acres' => 'decimal:2',
            'number_of_plots' => 'integer',
            'irrigation_availability' => IrrigationAvailability::class,
            'post_harvest_storage_capacity_tonnes' => 'decimal:2',
            'has_warehouse_access' => 'boolean',
            'cooperative_member' => 'boolean',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }
}
