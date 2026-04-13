<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $user ? 'Edit user' : 'Create user' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage access, assign roles, and optionally scope the user to a region or district.
            </p>
        </div>

        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <x-forms.input label="Full name" name="name" wire:model.live="name" />
                </div>

                <div class="space-y-1">
                    <x-forms.input label="Email" name="email" type="email" wire:model.live="email" />
                </div>

                <div class="space-y-1">
                    <x-forms.input label="Phone" name="phone" wire:model.live="phone" />
                </div>

                <div class="space-y-1">
                    <x-forms.input label="{{ $user ? 'New password (optional)' : 'Password' }}" name="password" type="password" wire:model.live="password" />
                </div>

                <div class="space-y-1">
                    <label for="status" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select id="status" wire:model.live="status" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption->value }}">{{ str($statusOption->value)->title() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="region_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                    <select id="region_id" wire:model.live="region_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">None</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="district_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">District</label>
                    <select id="district_id" wire:model.live="district_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">None</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Roles</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($roles as $role)
                    <label wire:key="user-role-{{ $role->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <input type="checkbox" value="{{ $role->name }}" wire:model.live="role_names" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ str($role->name)->replace('_', ' ')->title() }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save user
            </button>
        </div>
    </form>
</div>
