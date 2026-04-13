<?php

namespace App\Models;

use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Policies\SupplierPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[UsePolicy(SupplierPolicy::class)]
class Supplier extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'farmer_id',
        'business_name',
        'contact_person',
        'phone',
        'email',
        'operating_district_id',
        'typical_supply_volume_kg_per_month',
        'supply_frequency',
        'warehouse_linked',
        'verification_status',
        'verified_at',
        'verified_by_user_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'typical_supply_volume_kg_per_month' => 'decimal:2',
            'supply_frequency' => SupplyFrequency::class,
            'warehouse_linked' => 'boolean',
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'operating_district_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function valueChains(): BelongsToMany
    {
        return $this->belongsToMany(ValueChain::class, 'supplier_value_chains')
            ->withTimestamps();
    }

    public function qualityGrades(): BelongsToMany
    {
        return $this->belongsToMany(QualityGrade::class, 'supplier_quality_grades')
            ->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isRegionalAdmin()) {
            return $query;
        }

        if (! $user->region_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $scopeQuery) use ($user): void {
            $scopeQuery
                ->whereHas('district', function (Builder $districtQuery) use ($user): void {
                    $districtQuery->where('region_id', $user->region_id);
                })
                ->orWhereHas('farmer.location', function (Builder $locationQuery) use ($user): void {
                    $locationQuery->where('region_id', $user->region_id);
                });
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
