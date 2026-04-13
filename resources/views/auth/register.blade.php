<x-layouts.auth>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="rounded-[2rem] border border-emerald-200 bg-gradient-to-br from-emerald-950 via-emerald-900 to-lime-900 p-8 text-white shadow-xl">
            <div class="space-y-3">
                <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em]">Self-Onboarding</span>
                <h1 class="font-serif text-4xl leading-tight">Choose the registration path that matches your role.</h1>
                <p class="max-w-3xl text-sm leading-7 text-emerald-50/85">
                    AgroFresh now routes self-registration through the real business workflows instead of a generic starter account form.
                </p>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <a href="{{ route('farmer-portal.registration.create') }}" class="rounded-[1.75rem] border border-emerald-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-emerald-900 dark:bg-gray-950">
                <div class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700 dark:text-emerald-300">Farmer</div>
                <h2 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-gray-100">Farmer self-registration</h2>
                <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                    Capture personal details, normalized location, and farm mapping information in the guided farmer wizard.
                </p>
            </a>

            <a href="{{ route('buyer-portal.registration.create') }}" class="rounded-[1.75rem] border border-amber-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-amber-900 dark:bg-gray-950">
                <div class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700 dark:text-amber-300">Buyer</div>
                <h2 class="mt-3 text-2xl font-semibold text-gray-900 dark:text-gray-100">Buyer self-registration</h2>
                <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-400">
                    Create a buyer account and submit sourcing interests that will feed the buyer portal and future ordering flows.
                </p>
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:underline dark:text-emerald-300">Sign in</a>
            or
            <a href="{{ route('catalogue.index') }}" class="font-medium text-emerald-700 hover:underline dark:text-emerald-300">browse the public catalogue</a>.
        </div>
    </div>
</x-layouts.auth>
