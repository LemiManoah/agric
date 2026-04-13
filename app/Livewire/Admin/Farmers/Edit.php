<?php

namespace App\Livewire\Admin\Farmers;

use App\Enums\InternetAccessLevel;
use App\Enums\IrrigationAvailability;
use App\Enums\MarketDestination;
use App\Enums\ProductionScale;
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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Title('Edit Farmer')]
class Edit extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    protected FarmerRegistrationService $registrationService;

    protected FarmerPhotoService $photoService;

    public Farmer $farmer;

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

    public bool $removePassportPhoto = false;

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

    public function mount(Farmer $farmer): void
    {
        $this->authorize('update', $farmer);

        $this->farmer = $farmer;
        $this->farmer->load([
            'businessProfile',
            'location',
            'valueChainEntries',
        ]);

        $this->fillFromFarmer();
    }

    public function updatedPassportPhoto(): void
    {
        $this->removePassportPhoto = false;
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

    public function save()
    {
        $validated = $this->validate($this->rules());

        $passportPhotoPath = $this->farmer->passport_photo_path;

        if ($this->removePassportPhoto && $passportPhotoPath) {
            $this->photoService->deletePhoto($passportPhotoPath);
            $passportPhotoPath = null;
        }

        $passportPhotoPath = $this->photoService->replacePhoto($this->passport_photo, $passportPhotoPath);

        $this->registrationService->updateFarmer(
            $this->farmer,
            array_merge($validated, [
                'passport_photo_path' => $passportPhotoPath,
            ]),
            auth()->user(),
        );

        session()->flash('status', 'Farmer profile updated successfully.');

        return redirect()->route('admin.farmers.show', $this->farmer);
    }

    public function render(): View
    {
        return view('livewire.admin.farmers.edit', [
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
        ])->layout('components.layouts.app');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'national_id_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('farmers', 'national_id_number')->ignore($this->farmer->id),
            ],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
            'education_level' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'household_size' => ['nullable', 'integer', 'min:1'],
            'number_of_dependants' => ['nullable', 'integer', 'min:0'],
            'languages_spoken' => ['nullable', 'string', 'max:1000'],
            'region_id' => ['required', 'exists:regions,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'subcounty_id' => ['required', 'exists:subcounties,id'],
            'parish_id' => ['nullable', 'exists:parishes,id'],
            'village_id' => ['required', 'exists:villages,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'nearest_trading_centre' => ['nullable', 'string', 'max:255'],
            'distance_to_tarmac_road_km' => ['nullable', 'numeric', 'min:0'],
            'internet_access_level' => ['nullable', Rule::in(array_column(InternetAccessLevel::cases(), 'value'))],
            'farm_boundary_geojson' => ['nullable', 'string'],
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
            'value_chains' => ['array'],
            'value_chains.*.value_chain_id' => ['nullable', 'distinct', 'exists:value_chains,id'],
            'value_chains.*.production_scale' => ['nullable', Rule::in(array_column(ProductionScale::cases(), 'value'))],
            'value_chains.*.estimated_seasonal_harvest_kg' => ['nullable', 'numeric', 'min:0'],
            'value_chains.*.current_market_destination' => ['nullable', Rule::in(array_column(MarketDestination::cases(), 'value'))],
            'value_chains.*.input_access_details' => ['nullable', 'string'],
        ];
    }

    private function fillFromFarmer(): void
    {
        $location = $this->farmer->location;
        $businessProfile = $this->farmer->businessProfile;

        $this->full_name = $this->farmer->full_name;
        $this->phone = $this->farmer->phone;
        $this->national_id_number = $this->farmer->national_id_number ?? '';
        $this->gender = $this->farmer->gender ?? '';
        $this->date_of_birth = $this->farmer->date_of_birth?->format('Y-m-d') ?? '';
        $this->education_level = $this->farmer->education_level ?? '';
        $this->profession = $this->farmer->profession ?? '';
        $this->household_size = $this->farmer->household_size;
        $this->number_of_dependants = $this->farmer->number_of_dependants;
        $this->languages_spoken = implode(', ', $this->farmer->languages_spoken ?? []);
        $this->region_id = $location?->region_id;
        $this->district_id = $location?->district_id;
        $this->subcounty_id = $location?->subcounty_id;
        $this->parish_id = $location?->parish_id;
        $this->village_id = $location?->village_id;
        $this->latitude = (string) ($location?->latitude ?? '');
        $this->longitude = (string) ($location?->longitude ?? '');
        $this->nearest_trading_centre = $location?->nearest_trading_centre ?? '';
        $this->distance_to_tarmac_road_km = (string) ($location?->distance_to_tarmac_road_km ?? '');
        $this->internet_access_level = $location?->internet_access_level?->value ?? '';
        $this->farm_boundary_geojson = $location?->farm_boundary_geojson ?? '';
        $this->business_profile = [
            'farm_name' => $businessProfile?->farm_name ?? '',
            'ursb_registration_number' => $businessProfile?->ursb_registration_number ?? '',
            'farm_size_acres' => (string) ($businessProfile?->farm_size_acres ?? ''),
            'number_of_plots' => (string) ($businessProfile?->number_of_plots ?? ''),
            'irrigation_availability' => $businessProfile?->irrigation_availability?->value ?? '',
            'post_harvest_storage_capacity_tonnes' => (string) ($businessProfile?->post_harvest_storage_capacity_tonnes ?? ''),
            'has_warehouse_access' => (bool) ($businessProfile?->has_warehouse_access ?? false),
            'cooperative_member' => (bool) ($businessProfile?->cooperative_member ?? false),
            'cooperative_name' => $businessProfile?->cooperative_name ?? '',
            'cooperative_role' => $businessProfile?->cooperative_role ?? '',
            'average_annual_income_bracket' => $businessProfile?->average_annual_income_bracket ?? '',
        ];
        $this->value_chains = $this->farmer->valueChainEntries
            ->map(fn ($valueChain) => [
                'value_chain_id' => $valueChain->value_chain_id,
                'production_scale' => $valueChain->production_scale?->value ?? '',
                'estimated_seasonal_harvest_kg' => (string) ($valueChain->estimated_seasonal_harvest_kg ?? ''),
                'current_market_destination' => $valueChain->current_market_destination?->value ?? '',
                'input_access_details' => $valueChain->input_access_details
                    ? json_encode($valueChain->input_access_details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    : '',
            ])
            ->values()
            ->all();

        if ($this->value_chains === []) {
            $this->value_chains[] = $this->emptyValueChainRow();
        }
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

    private function regions(): Collection
    {
        $user = auth()->user();

        return Region::query()
            ->when($user?->region_id, fn ($query) => $query->whereKey($user->region_id))
            ->orderBy('name')
            ->get();
    }

    private function districts(): Collection
    {
        $user = auth()->user();

        return District::query()
            ->when($this->region_id, fn ($query) => $query->where('region_id', $this->region_id))
            ->when($user?->district_id, fn ($query) => $query->whereKey($user->district_id))
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
