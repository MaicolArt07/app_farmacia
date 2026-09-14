<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            function themeHandler() {
                return {
                    theme: localStorage.getItem('theme') || 'light',
                    pinned: localStorage.getItem('sidebarPinned') !== 'false',

                    init() {
                        this.theme = localStorage.getItem('theme') || 'light';
                        this.apply();

                        document.addEventListener('livewire:navigated', () => {
                            this.theme = localStorage.getItem('theme') || 'light';
                            this.apply();
                        });
                    },

                    apply() {
                        document.documentElement.classList.toggle('dark', this.theme === 'dark');
                    },

                    toggle() {
                        this.theme = this.theme === 'dark' ? 'light' : 'dark';
                        localStorage.setItem('theme', this.theme);
                        this.apply();
                    },

                    togglePinned() {
                        this.pinned = !this.pinned;
                        localStorage.setItem('sidebarPinned', this.pinned);
                    }
                }
            }
        </script>

        @include('partials.head')
    </head>

    <body
        x-data="themeHandler()"
        x-init="init()"
        class="min-h-screen bg-zinc-100 text-zinc-800 transition-colors duration-300 dark:bg-zinc-950 dark:text-zinc-100"
    >
        <flux:sidebar
            sticky
            stashable
            x-bind:class="pinned ? 'lg:w-64' : 'lg:w-20 lg:hover:w-64'"
            class="group/sb border-e border-zinc-200 bg-zinc-50 px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900 lg:w-64 overflow-x-hidden whitespace-nowrap transition-[width] duration-200"
        >
            <div class="flex items-center gap-1">
                <flux:sidebar.toggle
                    class="lg:hidden rounded-lg p-2 text-zinc-600 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-800"
                    icon="x-mark"
                />

                <a
                    href="{{ route('home') }}"
                    class="flex flex-1 items-center space-x-2 rounded-xl px-2 py-2 transition hover:bg-zinc-100 dark:hover:bg-zinc-800 rtl:space-x-reverse"
                    wire:navigate
                >
                    <x-app-logo />
                </a>

                <button
                    type="button"
                    @click="togglePinned()"
                    title="{{ __('Fijar / contraer menú') }}"
                    class="hidden lg:inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >
                    <i class="bi bi-list text-lg"></i>
                </button>
            </div>

            <!-- ✅ MENÚ PRINCIPAL -->
            <flux:navlist variant="outline" class="mt-4 text-sm [&_i]:text-sm">
                <flux:navlist.group :heading="__('Menú Principal')" class="grid gap-0.5 text-sm">
                    @can('Ver Inicio')
                    <flux:navlist.item
                        :href="route('home')"
                        :current="request()->routeIs('home')"
                        wire:navigate
                        class="rounded-xl px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-house-door-fill me-2"></i> {{ __('Inicio') }}
                    </flux:navlist.item>
                    @endcan

                    @can('Ver Persona')
                    <flux:navlist.item
                        :href="route('person')"
                        :current="request()->routeIs('person*')"
                        wire:navigate
                        class="rounded-xl px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-person-badge-fill me-2"></i> {{ __('Personas') }}
                    </flux:navlist.item>
                    @endcan

                    @can('Ver Caja')
                    <flux:navlist.item
                        :href="route('cash-registers')"
                        :current="request()->routeIs('cash-registers*')"
                        wire:navigate
                        class="rounded-xl px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-cash-coin me-2"></i> {{ __('Caja') }}
                    </flux:navlist.item>
                    @endcan

                    @can('Ver Clientes')
                    <flux:navlist.item
                        :href="route('clients')"
                        :current="request()->routeIs('clients*')"
                        wire:navigate
                        class="rounded-xl px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-people-fill me-2"></i> {{ __('Clientes') }}
                    </flux:navlist.item>
                    @endcan

                    @can('Ver Productos')
                    <flux:navlist.item
                        :href="route('products')"
                        :current="request()->routeIs('products*')"
                        wire:navigate
                        class="rounded-xl px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-capsule-pill me-2"></i> {{ __('Productos') }}
                    </flux:navlist.item>
                    @endcan

                    @can('Ver Compras')
                    <flux:navlist.item
                        :href="route('purchases')"
                        :current="request()->routeIs('purchases*')"
                        wire:navigate
                        class="rounded-xl px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-cart-plus-fill me-2"></i> {{ __('Compras') }}
                    </flux:navlist.item>
                    @endcan

                    @can('Ver Ventas')
                    <flux:navlist.item
                        :href="route('sales')"
                        :current="request()->routeIs('sales*')"
                        wire:navigate
                        class="rounded-xl px-3 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800"
                    >
                        <i class="bi bi-bag-check-fill me-2"></i> {{ __('Ventas') }}
                    </flux:navlist.item>
                    @endcan
                </flux:navlist.group>

                <!-- ✅ FARMACIA (un solo nivel, sin subgrupos) -->
                @canany([
                    'Ver Proveedores', 'Ver Laboratorios', 'Ver Categorias',
                    'Ver Presentaciones', 'Ver Marcas', 'Ver Lotes',
                    'Ver Ajustes Inventario', 'Ver Kardex',
                    'Ver Alertas Inventario', 'Ver Facturas', 'Ver Reportes'
                ])
                <flux:navlist.group :heading="__('Farmacia')" class="grid mt-3 text-sm">
                    <details class="group">
                        <summary class="flex items-center cursor-pointer rounded-xl px-3 py-2 text-sm text-zinc-700 transition hover:bg-zinc-200 dark:text-zinc-200 dark:hover:bg-zinc-800">
                            <i class="bi bi-capsule-pill me-2"></i>
                            <span>{{ __('Farmacia') }}</span>
                            <i class="bi bi-chevron-down ms-auto text-xs transition-transform duration-200 group-open:rotate-180"></i>
                        </summary>

                        <div class="ml-5 mt-1 space-y-0.5 border-l border-zinc-200 pl-3 dark:border-zinc-700">

                            @can('Ver Proveedores')
                                <flux:navlist.item :href="route('suppliers')" :current="request()->routeIs('suppliers*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('suppliers*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-truck me-2"></i> {{ __('Proveedores') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Laboratorios')
                                <flux:navlist.item :href="route('laboratories')" :current="request()->routeIs('laboratories*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('laboratories*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-building me-2"></i> {{ __('Laboratorios') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Categorias')
                                <flux:navlist.item :href="route('categories')" :current="request()->routeIs('categories*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('categories*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-tags-fill me-2"></i> {{ __('Categorías') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Presentaciones')
                                <flux:navlist.item :href="route('presentations')" :current="request()->routeIs('presentations*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('presentations*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-box-seam-fill me-2"></i> {{ __('Presentaciones') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Marcas')
                                <flux:navlist.item :href="route('brands')" :current="request()->routeIs('brands*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('brands*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-bookmark-star-fill me-2"></i> {{ __('Marcas') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Lotes')
                                <flux:navlist.item :href="route('lots')" :current="request()->routeIs('lots*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('lots*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-box-fill me-2"></i> {{ __('Lotes') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Ajustes Inventario')
                                <flux:navlist.item :href="route('inventory-adjustments')" :current="request()->routeIs('inventory-adjustments*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('inventory-adjustments*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-sliders2-vertical me-2"></i> {{ __('Ajustes de Inventario') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Facturas')
                                <flux:navlist.item :href="route('invoices')" :current="request()->routeIs('invoices*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('invoices*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-receipt me-2"></i> {{ __('Facturación') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Kardex')
                                <flux:navlist.item :href="route('kardex')" :current="request()->routeIs('kardex*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('kardex*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-journal-medical me-2"></i> {{ __('Kardex') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Alertas Inventario')
                                <flux:navlist.item :href="route('inventory-alerts')" :current="request()->routeIs('inventory-alerts*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('inventory-alerts*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ __('Alertas de Inventario') }}
                                </flux:navlist.item>
                            @endcan

                            @can('Ver Reportes')
                                <flux:navlist.item :href="route('reports')" :current="request()->routeIs('reports*')" wire:navigate class="rounded-lg px-3 py-1.5 text-sm {{ request()->routeIs('reports*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                    <i class="bi bi-file-earmark-bar-graph-fill me-2"></i> {{ __('Reportes') }}
                                </flux:navlist.item>
                            @endcan

                        </div>
                    </details>
                </flux:navlist.group>
                @endcanany

                <!-- ✅ GESTIÓN DE USUARIOS -->
                @can('Ver Gestion de Usuarios')
                <flux:navlist.group :heading="__('Gestión de Usuarios')" class="grid gap-0.5 mt-3 text-sm">
                    @can('Ver Usuarios')
                    <flux:navlist.item :href="route('users')" :current="request()->routeIs('users*')" wire:navigate class="rounded-xl px-3 py-2 text-sm {{ request()->routeIs('users*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                        <i class="bi bi-person-lines-fill me-2"></i> {{ __('Usuarios') }}
                    </flux:navlist.item>
                    @endcan

                    @can('Ver Permisos')
                    <flux:navlist.item :href="route('permissions')" :current="request()->routeIs('permissions*')" wire:navigate class="rounded-xl px-3 py-2 text-sm {{ request()->routeIs('permissions*') ? 'bg-zinc-200 dark:bg-zinc-800 font-semibold' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                        <i class="bi bi-shield-lock-fill me-2"></i> {{ __('Permisos') }}
                    </flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endcan
            </flux:navlist>

            <flux:spacer />

            <!-- ✅ PIE DE SIDEBAR (Desktop): usuario, tema y cerrar sesión -->
            <div class="hidden lg:flex items-center gap-2 border-t border-zinc-200 px-1 pt-3 dark:border-zinc-800">
                <flux:dropdown position="top" align="start" class="min-w-0 flex-1">
                    <button type="button" class="flex w-full min-w-0 items-center gap-2 rounded-xl px-2 py-2 text-start transition hover:bg-zinc-100 dark:hover:bg-zinc-800">
                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-zinc-700 dark:text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                        </span>

                        <span class="grid min-w-0 flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ __('Administrador') }}</span>
                        </span>
                    </button>

                    <flux:menu class="w-[220px] dark:bg-zinc-900 dark:text-zinc-100">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-zinc-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                                {{ __('Opciones') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>

                <!-- Botón tema -->
                <button
                    type="button"
                    @click="toggle()"
                    title="{{ __('Cambiar tema') }}"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >
                    <i class="bi bi-moon-stars-fill dark:hidden"></i>
                    <i class="bi bi-sun-fill hidden dark:inline text-yellow-400"></i>
                </button>

                <!-- Botón cerrar sesión -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        title="{{ __('Cerrar Sesión') }}"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-red-100 hover:text-red-600 dark:text-zinc-300 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                    >
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </flux:sidebar>

        <!-- ✅ MENÚ MÓVIL -->
        <flux:header class="border-b border-zinc-200 bg-white px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900 lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <div class="mx-2 flex-1">
                @livewire('livewire-global-search')
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="toggle()"
                    class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700"
                >
                    <i class="bi bi-moon-stars-fill dark:hidden"></i>
                    <i class="bi bi-sun-fill hidden dark:inline"></i>
                </button>

                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu class="dark:bg-zinc-900 dark:text-zinc-100">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-zinc-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>
                                {{ __('Opciones') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Cerrar Sesión') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </flux:header>

        <div class="hidden items-center border-b border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900 lg:flex">
            @livewire('livewire-global-search')
        </div>

        {{ $slot }}

        @fluxScripts

         <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->

<!-- <script>
document.addEventListener('livewire:init', () => {

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });

    Livewire.on('pdf-downloading', () => {
        Toast.fire({
            icon: 'info',
            title: 'Descargando PDF...'
        });
    });

    Livewire.on('pdf-downloaded', () => {
        Toast.fire({
            icon: 'success',
            title: 'PDF descargado correctamente'
        });
    });

});
</script> -->
    </body>
</html>