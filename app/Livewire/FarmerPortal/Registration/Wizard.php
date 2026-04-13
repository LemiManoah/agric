<?php

namespace App\Livewire\FarmerPortal\Registration;

use App\Enums\InternetAccessLevel;
use App\Enums\RegistrationSource;
use App\Models\District;
use App\Models\Farmer;
use App\Models\Parish;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\Village;
use App\Services\FarmerRegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Farmer Registration Wizard')]
class Wizard extends Component
{
    protected FarmerRegistrationService $registrationService;

    public bool $managedRegistration = false;

    public bool $showHeader = true;

    public int $step = 1;

    public string $full_name = '';

    public string $phone = '';

    public string $national_id_number = '';

    public string $gender = '';

    public string $date_of_birth = '';

    public string $education_level = '';

    public string $profession = '';

    public ?int $household_size = null;

    public ?int $number_of_dependants = null;

    public string $languages_spoken = '';

    public string $registration_source = '';

    public ?int $region_id = null;

    public ?int $district_id = null;

    public ?int $subcounty_id = null;

    public ?int $parish_id = null;

    public ?int $village_id = null;

    public string $latitude = '';

    public string $longitude = '';

    public string $nearest_trading_centre = '';

    public string $distance_to_tarmac_road_km = '';

    public string $internet_access_level = '';

    public string $farm_boundary_geojson = '';

    public function boot(FarmerRegistrationService $registrationService): void
    {
        $this->registrationService = $registrationService;
    }

    public function mount(): void
    {
        $user = auth()->user();

        $this->registration_source = ($this->managedRegistration || $user?->can('create', Farmer::class))
            ? RegistrationSource::FieldOfficer->value
            : RegistrationSource::SelfRegistered->value;

        if ($user?->region_id) {
            $this->region_id = (int) $user->region_id;
        }

        if ($user?->district_id) {
            $this->district_id = (int) $user->district_id;
        }
    }

    public function updatedRegionId(): void
    {
        $this->district_id = auth()->user()?->district_id && auth()->user()?->region_id === $this->region_id
            ? (int) auth()->user()->district_id
            : null;
        $this->subcounty_id = null;
        $this->parish_id = null;
        $this->village_id = null;
    }

    public function updatedDistrictId(): void
    {
        $this->subcounty_id = null;
        $this->parish_id = null;
        $this->village_id = null;
    }

    public function updatedSubcountyId(): void
    {
        $this->parish_id = null;
        $this->village_id = null;
    }

    public function updatedParishId(): void
    {
        $this->village_id = null;
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step));

        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submit()
    {
        $this->validate($this->allRules());

        $this->registrationService->createFarmer($this->payload(), auth()->user());

        session()->flash('status', 'Farmer registration submitted successfully.');

        return redirect()->route(
            auth()->user()?->can('create', Farmer::class)
                ? 'admin.farmers.index'
                : 'home'
        );
    }

    public function render(): View
    {
        return view('livewire.farmer-portal.registration.wizard', [
            'districts' => $this->districts(),
            'internetAccessLevels' => InternetAccessLevel::cases(),
            'parishes' => $this->parishes(),
            'regions' => $this->regions(),
            'subcounties' => $this->subcounties(),
            'villages' => $this->villages(),
        ])->layout(auth()->check() ? 'components.layouts.app' : 'components.layouts.auth');
    }

    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'full_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:30'],
                'national_id_number' => ['nullable', 'string', 'max:255'],
                'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'date_of_birth' => ['nullable', 'date'],
                'education_level' => ['nullable', 'string', 'max:255'],
                'profession' => ['nullable', 'string', 'max:255'],
                'household_size' => ['nullable', 'integer', 'min:1'],
                'number_of_dependants' => ['nullable', 'integer', 'min:0'],
                'languages_spoken' => ['nullable', 'string', 'max:1000'],
                'registration_source' => [
                    'required',
                    Rule::in($this->managedRegistration ? [RegistrationSource::FieldOfficer->value] : array_column(RegistrationSource::cases(), 'value')),
                ],
            ],
            2 => [
                'region_id' => ['required', 'exists:regions,id'],
                'district_id' => ['required', 'exists:districts,id'],
                'subcounty_id' => ['required', 'exists:subcounties,id'],
                'parish_id' => ['nullable', 'exists:parishes,id'],
                'village_id' => ['required', 'exists:villages,id'],
            ],
            3 => [
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'nearest_trading_centre' => ['nullable', 'string', 'max:255'],
                'distance_to_tarmac_road_km' => ['nullable', 'numeric', 'min:0'],
                'internet_access_level' => ['nullable', Rule::in(array_column(InternetAccessLevel::cases(), 'value'))],
                'farm_boundary_geojson' => ['nullable', 'string'],
            ],
            default => [],
        };
    }

    private function allRules(): array
    {
        return array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
            $this->rulesForStep(3),
        );
    }

    private function payload(): array
    {
        return [
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'national_id_number' => $this->national_id_number,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'education_level' => $this->education_level,
            'profession' => $this->profession,
            'household_size' => $this->household_size,
            'number_of_dependants' => $this->number_of_dependants,
            'languages_spoken' => $this->languages_spoken,
            'registration_source' => $this->registration_source,
            'region_id' => $this->region_id,
            'district_id' => $this->district_id,
            'subcounty_id' => $this->subcounty_id,
            'parish_id' => $this->parish_id,
            'village_id' => $this->village_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'nearest_trading_centre' => $this->nearest_trading_centre,
            'distance_to_tarmac_road_km' => $this->distance_to_tarmac_road_km,
            'internet_access_level' => $this->internet_access_level,
            'farm_boundary_geojson' => $this->farm_boundary_geojson,
        ];
    }

    private function regions(): Collection
    {
        return Region::query()->orderBy('name')->get();
    }

    private function districts(): Collection
    {
        return District::query()
            ->when($this->region_id, fn ($query) => $query->where('region_id', $this->region_id))
            ->orderBy('name')
            ->get();
    }

    private function subcounties(): Collection
    {
        return Subcounty::query()
            ->when($this->district_id, fn ($query) => $query->where('district_id', $this->district_id))
            ->orderBy('name')
            ->get();
    }

    private function parishes(): Collection
    {
        return Parish::query()
            ->when($this->subcounty_id, fn ($query) => $query->where('subcounty_id', $this->subcounty_id))
            ->orderBy('name')
            ->get();
    }

    private function villages(): Collection
    {
        return Village::query()
            ->when($this->parish_id, fn ($query) => $query->where('parish_id', $this->parish_id))
            ->orderBy('name')
            ->get();
    }
}
