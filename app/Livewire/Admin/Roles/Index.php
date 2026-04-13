<?php

namespace App\Livewire\Admin\Roles;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Roles')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('roles.view');
    }

    public function updating(string $name, mixed $value): void
    {
        if ($name === 'search') {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
        ])->layout('components.layouts.app');
    }
}
