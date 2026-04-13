<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Policies\OrderPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[UsePolicy(OrderPolicy::class)]
class Order extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'order_number',
        'buyer_id',
        'placed_by_agent_id',
        'status',
        'subtotal',
        'discount_applied',
        'order_total',
        'payment_method',
        'payment_status',
        'payment_reference',
        'delivery_address',
        'buyer_notes',
        'ordered_at',
        'confirmed_at',
        'dispatched_at',
        'delivered_at',
        'cancelled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_applied' => 'decimal:2',
            'order_total' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'ordered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'placed_by_agent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        if ($user->hasRole('buyer')) {
            return $query->whereHas('buyer', function (Builder $buyerQuery) use ($user): void {
                $buyerQuery->where('user_id', $user->id);
            });
        }

        if ($user->hasRole('agent')) {
            return $query->whereHas('agent', function (Builder $agentQuery) use ($user): void {
                $agentQuery->where('user_id', $user->id);
            });
        }

        if ($user->isRegionalAdmin()) {
            return $query->whereHas('items.supplier', function (Builder $supplierQuery) use ($user): void {
                $supplierQuery->visibleTo($user);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
