<div class="space-y-6">
    <div class="space-y-2">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Product categories</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Organize the public catalogue and optionally align categories to value chains already used across the platform.
        </p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(340px,420px)]">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-4">Category</th>
                            <th class="px-5 py-4">Value chain</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                        @forelse ($categories as $category)
                            <tr class="align-top text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $category->slug }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $category->linkedValueChain?->name ?? 'Standalone' }}</td>
                                <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $category->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-5 py-4">
                                    <button type="button" wire:click="edit({{ $category->id }})" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No product categories created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editingCategory ? 'Edit category' : 'Create category' }}</h2>

            <form wire:submit="save" class="mt-5 space-y-5">
                <x-forms.input label="Category name" name="name" wire:model.live="name" />
                <x-forms.input label="Slug" name="slug" wire:model.live="slug" />

                <div class="space-y-1">
                    <label for="linked_value_chain_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Linked value chain</label>
                    <select id="linked_value_chain_id" wire:model.live="linked_value_chain_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">None</option>
                        @foreach ($valueChains as $valueChain)
                            <option value="{{ $valueChain->id }}">{{ $valueChain->name }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="inline-flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>Category is active</span>
                </label>

                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                    Save category
                </button>
            </form>
        </section>
    </div>
</div>
