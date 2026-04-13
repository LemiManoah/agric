<?php

namespace App\Livewire\Admin\AgribusinessProfiles;

use App\Enums\AgribusinessEntityType;
use App\Models\AgribusinessProfile;
use App\Models\District;
use App\Services\AgribusinessProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Agribusiness Profile Form')]
class Form extends Component
{
    protected AgribusinessProfileService $agribusinessProfileService;

    public ?AgribusinessProfile $profile = null;

    public string $entity_type = '';

    public string $organization_name = '';

    public string $registration_number = '';

    public string $membership_size = '';

    public string $fleet_size = '';

    public string $service_rates = '';

    public string $product_range = '';

    public string $processing_capacity_tonnes_per_day = '';

    public string $export_markets = '';

    public string $buyer_criteria = '';

    public string $contact_person = '';

    public string $contact_phone = '';

    public array $district_ids = [];

    public function boot(AgribusinessProfileService $agribusinessProfileService): void
    {
        $this->agribusinessProfileService = $agribusinessProfileService;
    }

    public function mount(?AgribusinessProfile $agribusinessProfile = null): void
    {
        $this->profile = $agribusinessProfile?->exists ? $agribusinessProfile->load('districts') : null;

        if ($this->profile) {
            $this->authorize('update', $this->profile);

            $this->entity_type = $this->profile->entity_type->value;
            $this->organization_name = $this->profile->organization_name;
            $this->registration_number = $this->profile->registration_number ?? '';
            $this->membership_size = (string) ($this->profile->membership_size ?? '');
            $this->fleet_size = (string) ($this->profile->fleet_size ?? '');
            $this->service_rates = $this->profile->service_rates ?? '';
            $this->product_range = $this->profile->product_range ?? '';
            $this->processing_capacity_tonnes_per_day = (string) ($this->profile->processing_capacity_tonnes_per_day ?? '');
            $this->export_markets = $this->profile->export_markets ?? '';
            $this->buyer_criteria = $this->profile->buyer_criteria ?? '';
            $this->contact_person = $this->profile->contact_person;
            $this->contact_phone = $this->profile->contact_phone;
            $this->district_ids = $this->profile->districts->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            $this->authorize('create', AgribusinessProfile::class);
            $this->entity_type = AgribusinessEntityType::Cooperative->value;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'entity_type' => ['required', Rule::in(array_column(AgribusinessEntityType::cases(), 'value'))],
            'organization_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'membership_size' => ['nullable', 'integer', 'min:0'],
            'fleet_size' => ['nullable', 'integer', 'min:0'],
            'service_rates' => ['nullable', 'string'],
            'product_range' => ['nullable', 'string'],
            'processing_capacity_tonnes_per_day' => ['nullable', 'numeric', 'min:0'],
            'export_markets' => ['nullable', 'string'],
            'buyer_criteria' => ['nullable', 'string'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'district_ids' => ['required', 'array', 'min:1'],
            'district_ids.*' => ['exists:districts,id'],
        ]);

        $profile = $this->profile
            ? $this->agribusinessProfileService->updateProfile($this->profile, $validated, auth()->user())
            : $this->agribusinessProfileService->createProfile($validated, auth()->user());

        session()->flash('status', 'Agribusiness profile saved successfully.');

        return redirect()->route('admin.agribusiness-profiles.index', ['highlight' => $profile->id]);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.admin.agribusiness-profiles.form', [
            'districts' => District::query()
                ->when($user?->region_id, fn (Builder $query) => $query->where('region_id', $user->region_id))
                ->orderBy('name')
                ->get(),
            'entityTypes' => AgribusinessEntityType::cases(),
            'profile' => $this->profile,
        ])->layout('components.layouts.app');
    }
}
