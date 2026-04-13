<?php

namespace App\Livewire\Admin\Suppliers;

use App\Enums\SupplyFrequency;
use App\Models\District;
use App\Models\Farmer;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Models\ValueChain;
use App\Services\SupplierService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Supplier Form')]
class Form extends Component
{
    protected SupplierService $supplierService;

    public ?Supplier $supplier = null;

    public ?int $farmer_id = null;

    public string $business_name = '';

    public string $contact_person = '';

    public string $phone = '';

    public string $email = '';

    public ?int $operating_district_id = null;

    public string $typical_supply_volume_kg_per_month = '';

    public string $supply_frequency = '';

    public array $value_chain_ids = [];

    public array $quality_grade_ids = [];

    public function boot(SupplierService $supplierService): void
    {
        $this->supplierService = $supplierService;
    }

    public function mount(?Supplier $supplier = null): void
    {
        $this->supplier = $supplier?->exists ? $supplier->load(['qualityGrades', 'valueChains']) : null;

        if ($this->supplier) {
            $this->authorize('update', $this->supplier);

            $this->farmer_id = $this->supplier->farmer_id;
            $this->business_name = $this->supplier->business_name;
            $this->contact_person = $this->supplier->contact_person;
            $this->phone = $this->supplier->phone;
            $this->email = $this->supplier->email ?? '';
            $this->operating_district_id = $this->supplier->operating_district_id;
            $this->typical_supply_volume_kg_per_month = (string) ($this->supplier->typical_supply_volume_kg_per_month ?? '');
            $this->supply_frequency = $this->supplier->supply_frequency?->value ?? '';
            $this->value_chain_ids = $this->supplier->valueChains->pluck('id')->map(fn ($id) => (int) $id)->all();
            $this->quality_grade_ids = $this->supplier->qualityGrades->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            $this->authorize('create', Supplier::class);
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'farmer_id' => ['nullable', 'exists:farmers,id'],
            'business_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'operating_district_id' => ['nullable', 'exists:districts,id'],
            'typical_supply_volume_kg_per_month' => ['nullable', 'numeric', 'min:0'],
            'supply_frequency' => ['required', Rule::in(array_column(SupplyFrequency::cases(), 'value'))],
            'value_chain_ids' => ['array'],
            'value_chain_ids.*' => ['exists:value_chains,id'],
            'quality_grade_ids' => ['array'],
            'quality_grade_ids.*' => ['exists:quality_grades,id'],
        ]);

        $supplier = $this->supplier
            ? $this->supplierService->updateSupplier($this->supplier, $validated, auth()->user())
            : $this->supplierService->createSupplier($validated, auth()->user());

        session()->flash('status', 'Supplier profile saved successfully.');

        return redirect()->route('admin.suppliers.show', $supplier);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.admin.suppliers.form', [
            'districts' => District::query()
                ->when($user?->region_id, fn ($query) => $query->where('region_id', $user->region_id))
                ->orderBy('name')
                ->get(),
            'farmers' => Farmer::query()->visibleTo($user)->orderBy('full_name')->get(),
            'qualityGrades' => QualityGrade::query()->where('is_active', true)->orderBy('name')->get(),
            'supplier' => $this->supplier,
            'supplyFrequencies' => SupplyFrequency::cases(),
            'valueChains' => ValueChain::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
