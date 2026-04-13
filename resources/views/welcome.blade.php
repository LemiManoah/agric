<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-stone-100 text-stone-900 antialiased dark:bg-stone-950 dark:text-stone-100">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_30%),linear-gradient(180deg,_rgba(255,255,255,0.7),_rgba(245,245,244,0.95))] dark:bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.2),_transparent_24%),linear-gradient(180deg,_rgba(28,25,23,0.96),_rgba(12,10,9,1))]">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-8 lg:px-10">
            <header class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">AgroFresh AgriConnect</div>
                    <div class="mt-2 text-sm text-stone-600 dark:text-stone-400">Traceable farmer, supplier, buyer, and catalogue foundation</div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('catalogue.index') }}" class="inline-flex items-center rounded-full border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:bg-white dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-900">
                        Browse catalogue
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                            Go to dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-stone-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-stone-800 dark:bg-emerald-600 dark:hover:bg-emerald-700">
                            Sign in
                        </a>
                    @endauth
                </div>
            </header>

            <main class="flex flex-1 items-center py-12 lg:py-16">
                <div class="grid w-full gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.95fr)] lg:items-center">
                    <section class="space-y-6">
                        <span class="inline-flex rounded-full border border-emerald-300/70 bg-emerald-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
                            Self-Onboarding Ready
                        </span>
                        <h1 class="max-w-4xl font-serif text-5xl leading-tight text-stone-950 dark:text-white md:text-6xl">
                            The onboarding path now matches how AgroFresh is supposed to work.
                        </h1>
                        <p class="max-w-3xl text-base leading-8 text-stone-600 dark:text-stone-300">
                            Farmers and buyers no longer land on a generic starter-kit registration form. Visitors can start the journey that fits their role, while internal teams continue managing suppliers, agents, agribusiness profiles, and catalogue data from the admin side.
                        </p>

                        <div class="grid gap-4 md:grid-cols-2">
                            <a href="{{ route('farmer-portal.registration.create') }}" class="group rounded-[1.75rem] border border-emerald-300 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-emerald-900 dark:bg-stone-900">
                                <div class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700 dark:text-emerald-300">Farmer self-signup</div>
                                <h2 class="mt-3 text-2xl font-semibold text-stone-950 dark:text-white">Register as a farmer</h2>
                                <p class="mt-3 text-sm leading-7 text-stone-600 dark:text-stone-400">
                                    Complete the guided farmer registration wizard with normalized location data and farm mapping.
                                </p>
                                <div class="mt-5 text-sm font-medium text-emerald-700 dark:text-emerald-300">Start farmer onboarding</div>
                            </a>

                            <a href="{{ route('buyer-portal.registration.create') }}" class="group rounded-[1.75rem] border border-amber-300 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-amber-900 dark:bg-stone-900">
                                <div class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-700 dark:text-amber-300">Buyer self-signup</div>
                                <h2 class="mt-3 text-2xl font-semibold text-stone-950 dark:text-white">Register as a buyer</h2>
                                <p class="mt-3 text-sm leading-7 text-stone-600 dark:text-stone-400">
                                    Create a buyer account, add your sourcing profile, and prepare for catalogue and future order flows.
                                </p>
                                <div class="mt-5 text-sm font-medium text-amber-700 dark:text-amber-300">Start buyer onboarding</div>
                            </a>
                        </div>
                    </section>

                    <aside class="space-y-6 rounded-[2rem] border border-stone-200 bg-white/90 p-7 shadow-xl backdrop-blur dark:border-stone-800 dark:bg-stone-900/90">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500 dark:text-stone-400">What visitors can do now</div>
                            <div class="mt-4 space-y-4">
                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-950">
                                    <div class="font-medium text-stone-900 dark:text-stone-100">Browse the public catalogue</div>
                                    <div class="mt-2 text-sm leading-7 text-stone-600 dark:text-stone-400">Inspect supplier-linked products, pricing, grades, and stock visibility without signing in.</div>
                                </div>
                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-950">
                                    <div class="font-medium text-stone-900 dark:text-stone-100">Start the correct self-onboarding path</div>
                                    <div class="mt-2 text-sm leading-7 text-stone-600 dark:text-stone-400">Farmers and buyers now enter through role-specific onboarding instead of a generic register form.</div>
                                </div>
                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-950">
                                    <div class="font-medium text-stone-900 dark:text-stone-100">Sign in if you already have access</div>
                                    <div class="mt-2 text-sm leading-7 text-stone-600 dark:text-stone-400">Internal teams and previously onboarded accounts can continue in the dashboard.</div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
