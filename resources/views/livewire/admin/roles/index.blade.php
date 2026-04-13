<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Roles & permissions</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage application roles and keep permission bundles aligned with the M1 workflows already in the system.
            </p>
        </div>

        @can('roles.create')
            <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                Create role
            </a>
        @endcan
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <label class="space-y-2 text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">Search roles</span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Role name" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        </label>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-4">Role</th>
                        <th class="px-5 py-4">Users</th>
                        <th class="px-5 py-4">Permissions</th>
                        <th class="px-5 py-4">Updated</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($roles as $role)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ str($role->name)->replace('_', ' ')->title() }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $role->name }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ number_format($role->users_count) }}
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ number_format($role->permissions_count) }}
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                {{ $role->updated_at?->diffForHumans() ?? 'Just created' }}
                            </td>
                            <td class="px-5 py-4">
                                @can('roles.update')
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                        Edit
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No roles match the current search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $roles->links() }}
        </div>
    </section>
</div>
