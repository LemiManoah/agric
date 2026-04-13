<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Users</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage internal access and assign the roles that power the admin and portal experiences.
            </p>
        </div>

        @can('users.create')
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                Create user
            </a>
        @endcan
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <label class="space-y-2 text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">Search users</span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Name, email, phone" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        </label>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-4">User</th>
                        <th class="px-5 py-4">Roles</th>
                        <th class="px-5 py-4">Geography</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($users as $user)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}{{ $user->phone ? ' / '.$user->phone : '' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $user->roles->pluck('name')->implode(', ') ?: 'No roles' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ collect([$user->region?->name, $user->district?->name])->filter()->implode(' / ') ?: 'Unassigned' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ str($user->status->value)->title() }}</td>
                            <td class="px-5 py-4">
                                @can('users.update')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No users match the current search.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $users->links() }}
        </div>
    </section>
</div>
