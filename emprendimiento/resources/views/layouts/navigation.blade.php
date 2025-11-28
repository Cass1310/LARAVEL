<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="#">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">                    
                    @auth
                        {{-- RESIDENTE --}}
                        @if(Auth::user()->rol === 'residente')
                            <x-nav-link :href="route('residente.dashboard')" :active="request()->routeIs('residente.dashboard')">
                                <i class="bi bi-speedometer2 me-1"></i>{{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('residente.departamento')" :active="request()->routeIs('residente.departamento')">
                                <i class="bi bi-house-door me-1"></i>{{ __('Mi Departamento') }}
                            </x-nav-link>
                            <x-nav-link :href="route('residente.alertas')" :active="request()->routeIs('residente.alertas')">
                                <i class="bi bi-bell me-1"></i>{{ __('Alertas') }}
                            </x-nav-link>
                            <x-nav-link :href="route('residente.mantenimientos')" :active="request()->routeIs('residente.mantenimientos')">
                                <i class="bi bi-tools me-1"></i>{{ __('Mantenimiento') }}
                            </x-nav-link>
                            <x-nav-link :href="route('residente.reportes')" :active="request()->routeIs('residente.reportes')">
                                <i class="bi bi-graph-up me-1"></i>{{ __('Reportes') }}
                            </x-nav-link>
                        @endif

                        {{-- PROPIETARIO --}}
                        @if(Auth::user()->rol === 'propietario')
                            <x-nav-link :href="route('propietario.dashboard')" :active="request()->routeIs('propietario.dashboard')">
                                <i class="bi bi-speedometer2 me-1"></i>{{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('propietario.edificios')" :active="request()->routeIs('propietario.edificios')">
                                <i class="bi bi-building me-1"></i>{{ __('Mis Edificios') }}
                            </x-nav-link>
                            <x-nav-link :href="route('propietario.consumos')" :active="request()->routeIs('propietario.consumos*')">
                                <i class="bi bi-droplet-half me-1"></i>{{ __('Notas de consumo') }}
                            </x-nav-link>
                            <x-nav-link :href="route('propietario.residentes')" :active="request()->routeIs('propietario.residentes*')">
                                <i class="bi bi-people me-1"></i>{{ __('Residentes') }}
                            </x-nav-link>
                            <x-nav-link :href="route('propietario.alertas')" :active="request()->routeIs('propietario.alertas')">
                                <i class="bi bi-bell me-1"></i>{{ __('Alertas') }}
                            </x-nav-link>
                            <x-nav-link :href="route('propietario.mantenimientos')" :active="request()->routeIs('propietario.mantenimientos*')">
                                <i class="bi bi-tools me-1"></i>{{ __('Mantenimiento') }}
                            </x-nav-link>
                            <x-nav-link :href="route('propietario.reportes')" :active="request()->routeIs('propietario.reportes')">
                                <i class="bi bi-graph-up me-1"></i>{{ __('Reportes') }}
                            </x-nav-link>
                            <x-nav-link :href="route('propietario.pagos.index')" :active="request()->routeIs('propietario.pagos*')">
                                <i class="bi bi-credit-card me-1"></i>{{ __('Pagos') }}
                            </x-nav-link>
                        @endif

                        {{-- ADMINISTRADOR (igual que ya tenías) --}}
                        @if(Auth::user()->rol === 'administrador')
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                                    <i class="bi bi-speedometer2 me-1"></i>{{ __('Dashboard') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.usuarios')" :active="request()->routeIs('admin.usuarios*')">
                                    <i class="bi bi-people me-1"></i>{{ __('Usuarios') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.propietarios')" :active="request()->routeIs('admin.propietarios*')">
                                    <i class="bi bi-building me-1"></i>{{ __('Propietarios') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.equipos')" :active="request()->routeIs('admin.equipos*')">
                                    <i class="bi bi-cpu me-1"></i>{{ __('Equipos') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.mantenimientos')" :active="request()->routeIs('admin.mantenimientos*')">
                                    <i class="bi bi-tools me-1"></i>{{ __('Mantenimientos') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.alertas')" :active="request()->routeIs('admin.alertas*')">
                                    <i class="bi bi-bell me-1"></i>{{ __('Alertas') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.suscripciones')" :active="request()->routeIs('admin.suscripciones*')">
                                    <i class="bi bi-credit-card me-1"></i>{{ __('Suscripciones') }}
                                </x-nav-link>
                                <x-nav-link :href="route('admin.reportes')" :active="request()->routeIs('admin.reportes*')">
                                    <i class="bi bi-graph-up me-1"></i>{{ __('Reportes') }}
                                </x-nav-link>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right Side Of Navbar -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- User Info -->
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                            <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                            <div class="font-medium text-sm text-gray-500 capitalize">{{ Auth::user()->rol }}</div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="bi bi-person me-2"></i>{{ __('Perfil') }}
                        </x-dropdown-link>

                        @if(in_array(Auth::user()->rol, ['propietario']))
                            <x-dropdown-link :href="route('suscripcion.index')">
                                <i class="bi bi-credit-card me-2"></i>{{ __('Mi Suscripción') }}
                            </x-dropdown-link>
                        @endif
                        @if(in_array(Auth::user()->rol, ['administrador']))
                                <x-dropdown-link :href="route('admin.backups.index')" :active="request()->routeIs('admin.backups*')">
                                    <i class="bi bi-archive me-1"></i>{{ __('Backups') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.auditoria.index')" :active="request()->routeIs('admin.auditoria*')">
                                    <i class="bi bi-shield-check me-1"></i>{{ __('Auditoría') }}
                                </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>{{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger Menu -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @if(Auth::user()->rol === 'residente')
                    <x-responsive-nav-link :href="route('residente.dashboard')" :active="request()->routeIs('residente.dashboard')">
                        {{ __('Dashboard Residente') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('residente.departamento')" :active="request()->routeIs('residente.departamento')">
                        {{ __('Mi Departamento') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('residente.alertas')" :active="request()->routeIs('residente.alertas')">
                        {{ __('Alertas') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('residente.mantenimientos')" :active="request()->routeIs('residente.mantenimientos')">
                        {{ __('Mantenimiento') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('residente.reportes')" :active="request()->routeIs('residente.reportes')">
                        {{ __('Reportes') }}
                    </x-responsive-nav-link>
                @endif

                @if(Auth::user()->rol === 'propietario')
                    <x-responsive-nav-link :href="route('propietario.dashboard')" :active="request()->routeIs('propietario.dashboard')">
                        {{ __('Dashboard Propietario') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('propietario.edificios')" :active="request()->routeIs('propietario.edificios')">
                        {{ __('Mis Edificios') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('propietario.consumos')" :active="request()->routeIs('propietario.consumos*')">
                        {{ __('Notas de Consumo') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('propietario.residentes')" :active="request()->routeIs('propietario.residentes*')">
                        {{ __('Residentes') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('propietario.alertas')" :active="request()->routeIs('propietario.alertas')">
                        {{ __('Alertas') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('propietario.mantenimientos')" :active="request()->routeIs('propietario.mantenimientos*')">
                        {{ __('Mantenimiento') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('propietario.reportes')" :active="request()->routeIs('propietario.reportes')">
                        {{ __('Reportes') }}
                    </x-responsive-nav-link>
                @endif
                @if(Auth::user()->rol === 'administrador')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        <i class="bi bi-speedometer2 me-2"></i>{{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.usuarios')" :active="request()->routeIs('admin.usuarios*')">
                        <i class="bi bi-people me-2"></i>{{ __('Usuarios') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.propietarios')" :active="request()->routeIs('admin.propietarios*')">
                        <i class="bi bi-building me-2"></i>{{ __('Propietarios') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.equipos')" :active="request()->routeIs('admin.equipos*')">
                        <i class="bi bi-cpu me-2"></i>{{ __('Equipos') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.mantenimientos')" :active="request()->routeIs('admin.mantenimientos*')">
                        <i class="bi bi-tools me-2"></i>{{ __('Mantenimientos') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.alertas')" :active="request()->routeIs('admin.alertas*')">
                        <i class="bi bi-bell me-2"></i>{{ __('Alertas') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.suscripciones')" :active="request()->routeIs('admin.suscripciones*')">
                        <i class="bi bi-credit-card me-2"></i>{{ __('Suscripciones') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reportes')" :active="request()->routeIs('admin.reportes*')">
                        <i class="bi bi-graph-up me-2"></i>{{ __('Reportes') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.backups.index')" :active="request()->routeIs('admin.backups*')">
                        <i class="bi bi-archive me-2"></i>{{ __('Backups') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 capitalize">{{ Auth::user()->email }} • {{ Auth::user()->rol }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="bi bi-person me-2"></i>{{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <i class="bi bi-box-arrow-right me-2"></i>{{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav