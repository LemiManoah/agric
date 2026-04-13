<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FarmerReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function summary(array $filters, User $user): array
    {
        $farmers = $this->filteredQuery($filters, $user)
            ->with(['location.region', 'location.district'])
            ->get();

        $pendingStatuses = [
            VerificationStatus::Submitted->value,
            VerificationStatus::PendingReview->value,
        ];

        return [
            'total_farmers' => $farmers->count(),
            'verified_farmers' => $farmers->where('verification_status', VerificationStatus::Verified)->count(),
            'pending_farmers' => $farmers
                ->filter(fn (Farmer $farmer): bool => in_array($farmer->verification_status->value, $pendingStatuses, true))
                ->count(),
            'registrations_by_region' => $farmers
                ->groupBy(fn (Farmer $farmer): string => $farmer->location?->region?->name ?? 'Unassigned region')
                ->map(fn (Collection $group): int => $group->count())
                ->sortDesc()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function exportRows(array $filters, User $user): Collection
    {
        return $this->filteredQuery($filters, $user)
            ->with(['location.region', 'location.district', 'businessProfile', 'valueChainEntries.valueChain'])
            ->orderBy('full_name')
            ->get()
            ->map(function (Farmer $farmer): array {
                return [
                    'full_name' => $farmer->full_name,
                    'phone' => $farmer->phone,
                    'verification_status' => $farmer->verification_status->value,
                    'registration_source' => $farmer->registration_source->value,
                    'region' => $farmer->location?->region?->name,
                    'district' => $farmer->location?->district?->name,
                    'farm_name' => $farmer->businessProfile?->farm_name,
                    'value_chains' => $farmer->valueChainEntries
                        ->pluck('valueChain.name')
                        ->filter()
                        ->implode(', '),
                ];
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filteredQuery(array $filters, User $user): Builder
    {
        return Farmer::query()
            ->visibleTo($user)
            ->when(! empty($filters['verification_status']), function (Builder $query) use ($filters): void {
                $query->where('verification_status', $filters['verification_status']);
            })
            ->when(! empty($filters['registration_source']), function (Builder $query) use ($filters): void {
                $query->where('registration_source', $filters['registration_source']);
            })
            ->when(! empty($filters['region_id']), function (Builder $query) use ($filters): void {
                $query->whereHas('location', function (Builder $locationQuery) use ($filters): void {
                    $locationQuery->where('region_id', $filters['region_id']);
                });
            })
            ->when(! empty($filters['district_id']), function (Builder $query) use ($filters): void {
                $query->whereHas('location', function (Builder $locationQuery) use ($filters): void {
                    $locationQuery->where('district_id', $filters['district_id']);
                });
            })
            ->when(! empty($filters['value_chain_id']), function (Builder $query) use ($filters): void {
                $query->whereHas('valueChainEntries', function (Builder $valueChainQuery) use ($filters): void {
                    $valueChainQuery->where('value_chain_id', $filters['value_chain_id']);
                });
            });
    }
}
