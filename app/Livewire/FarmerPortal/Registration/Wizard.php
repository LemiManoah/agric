<?php

namespace App\Livewire\FarmerPortal\Registration;

use App\Enums\InternetAccessLevel;
use App\Enums\IrrigationAvailability;
use App\Enums\MarketDestination;
use App\Enums\ProductionScale;
use App\Enums\RegistrationSource;
use App\Models\District;
use App\Models\Farmer;
use App\Models\Parish;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\ValueChain;
use App\Models\Village;
use App\Services\FarmerPhotoService;
use App\Services\FarmerRegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Title('Farmer Registration Wizard')]
class Wizard extends Component
{
    use WithFileUploads;

    protected FarmerRegistrationService $registrationService;

    protected FarmerPhotoService $photoService;

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

    public mixed $passport_photo = null;

    /**
     * @var array<string, mixed>
     */
    public array $business_profile = [
        'farm_name' => '',
        'ursb_registration_number' => '',
        'farm_size_acres' => '',
        'number_of_plots' => '',
        'irrigation_availability' => '',
        'post_harvest_storage_capacity_tonnes' => '',
        'has_warehouse_access' => false,
        'cooperative_member' => false,
        'cooperative_name' => '',
        'cooperative_role' => '',
        'average_annual_income_bracket' => '',
    ];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $value_chains = [];

    public function boot(FarmerRegistrationService $registrationService, FarmerPhotoService $photoService): void
    {
        $this->registrationService = $registrationService;
        $this->photoService = $photoService;
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

        if ($this->value_chains === []) {
            $this->value_chains[] = $this->emptyValueChainRow();
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

        if ($this->step < $this->totalSteps()) {
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

        $this->registrationService->createFarmer(
            array_merge($this->payload(), [
                'passport_photo_path' => $this->photoService->replacePhoto($this->passport_photo),
            ]),
            auth()->user(),
        );

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
            'irrigationAvailabilityOptions' => IrrigationAvailability::cases(),
            'marketDestinationOptions' => MarketDestination::cases(),
            'parishes' => $this->parishes(),
            'productionScaleOptions' => ProductionScale::cases(),
            'regions' => $this->regions(),
            'subcounties' => $this->subcounties(),
            'valueChainOptions' => ValueChain::query()->where('is_active', true)->orderBy('name')->get(),
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
            4 => [
                'passport_photo' => ['nullable', 'image', 'max:2048'],
                'business_profile.farm_name' => ['nullable', 'string', 'max:255'],
                'business_profile.ursb_registration_number' => ['nullable', 'string', 'max:255'],
                'business_profile.farm_size_acres' => ['nullable', 'numeric', 'min:0'],
                'business_profile.number_of_plots' => ['nullable', 'integer', 'min:0'],
                'business_profile.irrigation_availability' => ['nullable', Rule::in(array_column(IrrigationAvailability::cases(), 'value'))],
                'business_profile.post_harvest_storage_capacity_tonnes' => ['nullable', 'numeric', 'min:0'],
                'business_profile.has_warehouse_access' => ['nullable', 'boolean'],
                'business_profile.cooperative_member' => ['nullable', 'boolean'],
                'business_profile.cooperative_name' => ['nullable', 'string', 'max:255'],
                'business_profile.cooperative_role' => ['nullable', 'string', 'max:255'],
                'business_profile.average_annual_income_bracket' => ['nullable', 'string', 'max:255'],
            ],
            5 => [
                'value_chains' => ['array'],
                'value_chains.*.value_chain_id' => ['nullable', 'distinct', 'exists:value_chains,id'],
                'value_chains.*.production_scale' => ['nullable', Rule::in(array_column(ProductionScale::cases(), 'value'))],
                'value_chains.*.estimated_seasonal_harvest_kg' => ['nullable', 'numeric', 'min:0'],
                'value_chains.*.current_market_destination' => ['nullable', Rule::in(array_column(MarketDestination::cases(), 'value'))],
                'value_chains.*.input_access_details' => ['nullable', 'string'],
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
            $this->rulesForStep(4),
            $this->rulesForStep(5),
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
            'business_profile' => $this->business_profile,
            'value_chains' => $this->value_chains,
        ];
    }

    public function addValueChain(): void
    {
        $this->value_chains[] = $this->emptyValueChainRow();
    }

    public function removeValueChain(int $index): void
    {
        unset($this->value_chains[$index]);

        $this->value_chains = array_values($this->value_chains);

        if ($this->value_chains === []) {
            $this->value_chains[] = $this->emptyValueChainRow();
        }
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

    private function totalSteps(): int
    {
        return 6;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyValueChainRow(): array
    {
        return [
            'value_chain_id' => '',
            'production_scale' => '',
            'estimated_seasonal_harvest_kg' => '',
            'current_market_destination' => '',
            'input_access_details' => '',
        ];
    }
}
