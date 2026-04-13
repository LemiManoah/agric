<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
    class="bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition overflow-hidden">
    <div class="flex h-full flex-col">
        <nav class="custom-scrollbar flex-1 overflow-y-auto py-4">
            <ul class="space-y-1 px-2">
                <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon="fas-house" :active="request()->routeIs('dashboard*')">
                    Dashboard
                </x-layouts.sidebar-link>

                @if (auth()->user()?->can('viewAny', \App\Models\Farmer::class) || auth()->user()?->can('create', \App\Models\Farmer::class))
                    <x-layouts.sidebar-two-level-link-parent
                        title="Farmers"
                        icon="fas-seedling"
                        :active="request()->routeIs('admin.farmers.*') || request()->routeIs('field-officer.farmers.*') || request()->routeIs('farmer-portal.registration.*')"
                    >
                        @can('viewAny', \App\Models\Farmer::class)
                            <x-layouts.sidebar-two-level-link href="{{ route('admin.farmers.index') }}" icon="fas-list" :active="request()->routeIs('admin.farmers.index')">
                                Registry
                            </x-layouts.sidebar-two-level-link>
                        @endcan

                        @can('viewMap', \App\Models\Farmer::class)
                            <x-layouts.sidebar-two-level-link href="{{ route('admin.farmers.map') }}" icon="fas-map-location-dot" :active="request()->routeIs('admin.farmers.map')">
                                Farm map
                            </x-layouts.sidebar-two-level-link>
                        @endcan

                        @if (auth()->user()?->can('create', \App\Models\Farmer::class))
                            <x-layouts.sidebar-two-level-link href="{{ route('field-officer.farmers.create') }}" icon="fas-user-plus" :active="request()->routeIs('field-officer.farmers.create')">
                                Register farmer
                            </x-layouts.sidebar-two-level-link>
                        @endif
                    </x-layouts.sidebar-two-level-link-parent>
                @endif

                @can('viewAny', \App\Models\Supplier::class)
                    <x-layouts.sidebar-two-level-link-parent title="Suppliers" icon="fas-truck-field" :active="request()->routeIs('admin.suppliers.*')">
                        <x-layouts.sidebar-two-level-link href="{{ route('admin.suppliers.index') }}" icon="fas-list" :active="request()->routeIs('admin.suppliers.index')">
                            Registry
                        </x-layouts.sidebar-two-level-link>

                        @can('create', \App\Models\Supplier::class)
                            <x-layouts.sidebar-two-level-link href="{{ route('admin.suppliers.create') }}" icon="fas-plus" :active="request()->routeIs('admin.suppliers.create')">
                                New supplier
                            </x-layouts.sidebar-two-level-link>
                        @endcan
                    </x-layouts.sidebar-two-level-link-parent>
                @endcan

                @can('viewAny', \App\Models\Agent::class)
                    <x-layouts.sidebar-two-level-link-parent title="Agents" icon="fas-user-tie" :active="request()->routeIs('admin.agents.*')">
                        <x-layouts.sidebar-two-level-link href="{{ route('admin.agents.index') }}" icon="fas-list" :active="request()->routeIs('admin.agents.index')">
                            Registry
                        </x-layouts.sidebar-two-level-link>

                        @can('create', \App\Models\Agent::class)
                            <x-layouts.sidebar-two-level-link href="{{ route('admin.agents.create') }}" icon="fas-plus" :active="request()->routeIs('admin.agents.create')">
                                New agent
                            </x-layouts.sidebar-two-level-link>
                        @endcan
                    </x-layouts.sidebar-two-level-link-parent>
                @endcan

                @can('viewAny', \App\Models\AgribusinessProfile::class)
                    <x-layouts.sidebar-two-level-link-parent title="Agribusiness" icon="fas-building-wheat" :active="request()->routeIs('admin.agribusiness-profiles.*')">
                        <x-layouts.sidebar-two-level-link href="{{ route('admin.agribusiness-profiles.index') }}" icon="fas-list" :active="request()->routeIs('admin.agribusiness-profiles.index')">
                            Profiles
                        </x-layouts.sidebar-two-level-link>

                        @can('create', \App\Models\AgribusinessProfile::class)
                            <x-layouts.sidebar-two-level-link href="{{ route('admin.agribusiness-profiles.create') }}" icon="fas-plus" :active="request()->routeIs('admin.agribusiness-profiles.create')">
                                New profile
                            </x-layouts.sidebar-two-level-link>
                        @endcan
                    </x-layouts.sidebar-two-level-link-parent>
                @endcan

                @if (auth()->user()?->can('reports.view') || auth()->user()?->can('reports.view.region'))
                    <x-layouts.sidebar-two-level-link-parent title="Reports" icon="fas-chart-column" :active="request()->routeIs('admin.reports.*')">
                        <x-layouts.sidebar-two-level-link href="{{ route('admin.reports.farmers.overview') }}" icon="fas-users" :active="request()->routeIs('admin.reports.farmers.overview')">
                            Farmer overview
                        </x-layouts.sidebar-two-level-link>

                        <x-layouts.sidebar-two-level-link href="{{ route('admin.reports.m1-profile-summary') }}" icon="fas-table-cells-large" :active="request()->routeIs('admin.reports.m1-profile-summary')">
                            M1 profile summary
                        </x-layouts.sidebar-two-level-link>
                    </x-layouts.sidebar-two-level-link-parent>
                @endif

                @if (auth()->user()?->can('roles.view') || auth()->user()?->can('roles.create'))
                    <x-layouts.sidebar-two-level-link-parent title="Access" icon="fas-user-shield" :active="request()->routeIs('admin.roles.*')">
                        @can('roles.view')
                            <x-layouts.sidebar-two-level-link href="{{ route('admin.roles.index') }}" icon="fas-lock" :active="request()->routeIs('admin.roles.index')">
                                Roles
                            </x-layouts.sidebar-two-level-link>
                        @endcan

                        @can('roles.create')
                            <x-layouts.sidebar-two-level-link href="{{ route('admin.roles.create') }}" icon="fas-user-plus" :active="request()->routeIs('admin.roles.create')">
                                New role
                            </x-layouts.sidebar-two-level-link>
                        @endcan
                    </x-layouts.sidebar-two-level-link-parent>
                @endif
            </ul>
        </nav>
    </div>
</aside>
