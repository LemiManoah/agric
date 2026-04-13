<?php

namespace App\Models;

use App\Enums\AgentOnboardingStatus;
use App\Policies\AgentPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[UsePolicy(AgentPolicy::class)]
class Agent extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'full_name',
        'agent_code',
        'phone',
        'email',
        'primary_district_id',
        'commission_rate',
        'total_orders_placed',
        'total_commission_earned',
        'onboarding_status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'total_orders_placed' => 'integer',
            'total_commission_earned' => 'decimal:2',
            'onboarding_status' => AgentOnboardingStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function primaryDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'primary_district_id');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'agent_regions')->withTimestamps();
    }

    public function valueChains(): BelongsToMany
    {
        return $this->belongsToMany(ValueChain::class, 'agent_value_chains')->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function placedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'placed_by_agent_id');
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
                ->whereHas('primaryDistrict', function (Builder $districtQuery) use ($user): void {
                    $districtQuery->where('region_id', $user->region_id);
                })
                ->orWhereHas('regions', function (Builder $regionQuery) use ($user): void {
                    $regionQuery->whereKey($user->region_id);
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
