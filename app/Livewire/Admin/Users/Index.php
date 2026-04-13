<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Users')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('users.view'), 403);
    }

    public function updating(string $name, mixed $value): void
    {
        if ($name === 'search') {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        return view('livewire.admin.users.index', [
            'users' => User::query()
                ->with('roles', 'region', 'district')
                ->when($this->search !== '', function ($query): void {
                    $query->where(function ($searchQuery): void {
                        $searchQuery
                            ->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%')
                            ->orWhere('phone', 'like', '%'.$this->search.'%');
                    });
                })
                ->orderBy('name')
                ->paginate(12),
        ])->layout('components.layouts.app');
    }
}
