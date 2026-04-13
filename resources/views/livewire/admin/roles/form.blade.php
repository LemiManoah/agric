<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->role ? 'Edit role' : 'Create role' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Name the role and attach the permission bundle that should travel with it across the current M1 admin experience.
            </p>
        </div>

        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-forms.input label="Role name" name="name" wire:model.live="name" />
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <div class="font-medium text-gray-900 dark:text-gray-100">Guard</div>
                    <div class="mt-1">All roles in this module are saved on the <span class="font-semibold">web</span> guard.</div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Attached permissions</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Select the permissions this role should grant by default.</p>
                </div>
                <div class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    {{ count($permission_names) }} selected
                </div>
            </div>

            <div class="mt-6 space-y-5">
                @foreach ($permissions as $group => $groupPermissions)
                    <div wire:key="permission-group-{{ $group }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">{{ str($group)->replace('_', ' ')->title() }}</h3>
                        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($groupPermissions as $permission)
                                <label wire:key="permission-{{ $permission->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300">
                                    <input type="checkbox" value="{{ $permission->name }}" wire:model.live="permission_names" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @error('permission_names.*')
                <span class="mt-2 block text-red-500">{{ $message }}</span>
            @enderror
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save role
            </button>
        </div>
    </form>
</div>
