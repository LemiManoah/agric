<?php

namespace App\Models;

use App\Enums\PaymentLifecycleStatus;
use App\Enums\PaymentMethod;
use App\Policies\PaymentPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[UsePolicy(PaymentPolicy::class)]
class Payment extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'order_id',
        'method',
        'gateway_transaction_reference',
        'gateway_reference_payload',
        'amount',
        'currency',
        'exchange_rate_to_ugx',
        'status',
        'paid_at',
        'confirmed_by_user_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'gateway_reference_payload' => 'array',
            'amount' => 'decimal:2',
            'exchange_rate_to_ugx' => 'decimal:4',
            'status' => PaymentLifecycleStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class)->latestOfMany('generated_at');
    }

    public function callbacks(): HasMany
    {
        return $this->hasMany(PaymentCallback::class, 'reference', 'gateway_transaction_reference');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->visibleTo($user));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
