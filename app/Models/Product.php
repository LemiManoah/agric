<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\VerificationStatus;
use App\Policies\ProductPolicy;
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

#[UsePolicy(ProductPolicy::class)]
class Product extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'product_category_id',
        'linked_supplier_id',
        'description',
        'quality_grade_id',
        'unit_of_measure',
        'price_per_unit_usd',
        'minimum_order_quantity',
        'stock_available',
        'listing_status',
        'warehouse_sku',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit_usd' => 'decimal:2',
            'minimum_order_quantity' => 'decimal:2',
            'stock_available' => 'decimal:2',
            'listing_status' => ListingStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'linked_supplier_id');
    }

    public function qualityGrade(): BelongsTo
    {
        return $this->belongsTo(QualityGrade::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class)->latest();
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

        if ($user->isRegionalAdmin()) {
            return $query->whereHas('supplier', function (Builder $supplierQuery) use ($user): void {
                $supplierQuery->visibleTo($user);
            });
        }

        return $query;
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->whereIn('listing_status', [ListingStatus::Active->value, ListingStatus::OutOfStock->value])
            ->whereHas('supplier', fn (Builder $supplierQuery) => $supplierQuery->where('verification_status', VerificationStatus::Verified->value));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
