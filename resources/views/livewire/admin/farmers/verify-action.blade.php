<div class="space-y-4">
    <div class="space-y-1">
        <label for="verification_note" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Review note</label>
        <textarea
            id="verification_note"
            wire:model.live="note"
            rows="4"
            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
            placeholder="Optional note for the audit trail"
        ></textarea>
        @error('note')
            <span class="text-red-500">{{ $message }}</span>
        @enderror
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <button type="button" wire:click="approve" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
            Approve
        </button>
        <button type="button" wire:click="reject" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700">
            Reject
        </button>
        <button type="button" wire:click="suspend" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">
            Suspend
        </button>
    </div>
</div>
