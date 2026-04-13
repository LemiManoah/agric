<?php

namespace App\Models;

use App\Enums\AgribusinessEntityType;
use App\Policies\AgribusinessProfilePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[UsePolicy(AgribusinessProfilePolicy::class)]
class AgribusinessProfile extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'entity_type',
        'organization_name',
        'registration_number',
        'membership_size',
        'fleet_size',
        'service_rates',
        'product_range',
        'processing_capacity_tonnes_per_day',
        'export_markets',
        'buyer_criteria',
        'contact_person',
        'contact_phone',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entity_type' => AgribusinessEntityType::class,
            'membership_size' => 'integer',
            'fleet_size' => 'integer',
            'processing_capacity_tonnes_per_day' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'agribusiness_districts')->withTimestamps();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isRegionalAdmin()) {
            return $query;
        }

        if (! $user->region_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('districts', function (Builder $districtQuery) use ($user): void {
            $districtQuery->where('region_id', $user->region_id);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
