<?php

namespace App\Models;

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Policies\FarmerPolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[UsePolicy(FarmerPolicy::class)]
class Farmer extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'national_id_number',
        'passport_photo_path',
        'gender',
        'date_of_birth',
        'education_level',
        'profession',
        'household_size',
        'number_of_dependants',
        'languages_spoken',
        'registration_source',
        'registered_by_user_id',
        'verification_status',
        'verified_at',
        'verified_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'verified_at' => 'datetime',
            'languages_spoken' => 'array',
            'registration_source' => RegistrationSource::class,
            'verification_status' => VerificationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function location(): HasOne
    {
        return $this->hasOne(FarmerLocation::class);
    }

    public function businessProfile(): HasOne
    {
        return $this->hasOne(FarmerBusinessProfile::class);
    }

    public function valueChainEntries(): HasMany
    {
        return $this->hasMany(FarmerValueChain::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isRegionalAdmin()) {
            return $query;
        }

        if (! $user->region_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('location', function (Builder $locationQuery) use ($user): void {
            $locationQuery->where('region_id', $user->region_id);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    protected function passportPhotoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->passport_photo_path) {
                return null;
            }

            return Storage::disk(config('filesystems.default'))->url($this->passport_photo_path);
        });
    }
}
