<?php

namespace App\Livewire\Admin\Roles;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('Role Form')]
class Form extends Component
{
    public ?Role $role = null;

    public string $name = '';

    public array $permission_names = [];

    public function mount(?Role $role = null): void
    {
        $this->role = $role?->exists ? $role->load('permissions') : null;

        if ($this->role) {
            $this->authorize('roles.update');

            $this->name = $this->role->name;
            $this->permission_names = $this->role->permissions->pluck('name')->all();
        } else {
            $this->authorize('roles.create');
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->role?->id),
            ],
            'permission_names' => ['array'],
            'permission_names.*' => ['string', 'exists:permissions,name'],
        ]);

        if ($this->role) {
            $this->role->forceFill([
                'name' => $validated['name'],
            ])->save();
        } else {
            $this->role = Role::query()->create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);
        }

        if (auth()->user()?->can('roles.assign')) {
            $this->role->syncPermissions($validated['permission_names'] ?? []);
        }

        session()->flash('status', 'Role saved successfully.');

        return redirect()->route('admin.roles.index');
    }

    public function render(): View
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => Str::before($permission->name, '.'));

        return view('livewire.admin.roles.form', [
            'permissions' => $permissions,
        ])->layout('components.layouts.app');
    }
}
