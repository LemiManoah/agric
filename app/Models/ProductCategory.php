<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'linked_value_chain_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function linkedValueChain(): BelongsTo
    {
        return $this->belongsTo(ValueChain::class, 'linked_value_chain_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
