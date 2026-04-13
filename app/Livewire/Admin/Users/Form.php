<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserStatus;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('User Form')]
class Form extends Component
{
    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $status = '';

    public ?int $region_id = null;

    public ?int $district_id = null;

    public array $role_names = [];

    public function mount(?User $user = null): void
    {
        $this->user = $user?->exists ? $user->load('roles') : null;

        abort_unless(auth()->user()?->can($this->user ? 'users.update' : 'users.create'), 403);

        if ($this->user) {
            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->phone = $this->user->phone ?? '';
            $this->status = $this->user->status->value;
            $this->region_id = $this->user->region_id;
            $this->district_id = $this->user->district_id;
            $this->role_names = $this->user->roles->pluck('name')->all();
        } else {
            $this->status = UserStatus::Active->value;
        }
    }

    public function updatedRegionId(): void
    {
        $this->district_id = null;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($this->user?->id)],
            'password' => [$this->user ? 'nullable' : 'required', 'string', 'min:8'],
            'status' => ['required', Rule::in(array_column(UserStatus::cases(), 'value'))],
            'region_id' => ['nullable', 'exists:regions,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'role_names' => ['array'],
            'role_names.*' => ['exists:roles,name'],
        ]);

        $payload = collect($validated)->except(['password', 'role_names'])->all();

        if ($this->user) {
            if ($validated['password'] !== '') {
                $payload['password'] = Hash::make($validated['password']);
            }

            $this->user->update($payload);
        } else {
            $payload['password'] = Hash::make($validated['password']);
            $payload['email_verified_at'] = now();
            $payload['created_by'] = auth()->id();

            $this->user = User::query()->create($payload);
        }

        if (auth()->user()?->can('roles.assign')) {
            $this->user->syncRoles($validated['role_names']);
        }

        session()->flash('status', 'User saved successfully.');

        return redirect()->route('admin.users.index');
    }

    public function render(): View
    {
        return view('livewire.admin.users.form', [
            'districts' => District::query()
                ->when($this->region_id, fn ($query) => $query->where('region_id', $this->region_id))
                ->orderBy('name')
                ->get(),
            'regions' => Region::query()->orderBy('name')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
        ])->layout('components.layouts.app');
    }
}
