<div class="space-y-6">
    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Field officer registration</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Use the guided workflow below to register a farmer within your assigned scope. The registration source and registering officer are captured automatically.
            </p>
        </div>
    </div>

    <livewire:farmer-portal.registration.wizard :managed-registration="true" :show-header="false" />
</div>
