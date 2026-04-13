<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class VerificationAction extends Component
{
    protected SupplierService $supplierService;

    public Supplier $supplier;

    public function boot(SupplierService $supplierService): void
    {
        $this->supplierService = $supplierService;
    }

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function verify(): void
    {
        $this->authorize('verify', $this->supplier);

        $this->supplierService->verifySupplier($this->supplier, auth()->user());
        $this->afterAction('Supplier verified successfully.');
    }

    public function suspend(): void
    {
        $this->authorize('verify', $this->supplier);

        $this->supplierService->suspendSupplier($this->supplier, auth()->user());
        $this->afterAction('Supplier suspended successfully.');
    }

    public function toggleWarehouseLinked(): void
    {
        $this->authorize('toggleWarehouseLinked', $this->supplier);

        $this->supplierService->setWarehouseLinked(
            $this->supplier,
            ! $this->supplier->warehouse_linked,
            auth()->user(),
        );

        $this->afterAction('Supplier warehouse linkage updated.');
    }

    public function render(): View
    {
        return view('livewire.admin.suppliers.verification-action', [
            'supplier' => $this->supplier,
        ]);
    }

    private function afterAction(string $message): void
    {
        $this->supplier->refresh();

        session()->flash('status', $message);
        $this->dispatch('supplier-status-updated');
    }
}
