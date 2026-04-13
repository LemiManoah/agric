<div class="flex flex-wrap gap-3">
    @if ($buyer->verification_status !== \App\Enums\VerificationStatus::Verified)
        <button type="button" wire:click="verify" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
            Verify buyer
        </button>
    @endif

    @if ($buyer->verification_status !== \App\Enums\VerificationStatus::Suspended)
        <button type="button" wire:click="suspend" class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60">
            Suspend buyer
        </button>
    @endif
</div>
