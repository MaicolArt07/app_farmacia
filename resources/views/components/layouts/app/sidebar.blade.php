<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            function themeHandler() {
                return {
                    theme: localStorage.getItem('theme') || 'light',
                    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',

                    init() {
                        this.theme = localStorage.getItem('theme') || 'light';
                        this.sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                        this.apply();

                        document.addEventListener('livewire:navigated', () => {
                            this.theme = localStorage.getItem('theme') || 'light';
                            this.sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
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

                    toggleSidebar() {
                        this.sidebarCollapsed = !this.sidebarCollapsed;
                        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
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
        <div class="flex min-h-screen">

            {{-- ═══════════════════════════════════════════════
                 SIDEBAR DESKTOP (oculto en móvil, visible en lg+)
            ═══════════════════════════════════════════════ --}}
            <aside
                class="hidden lg:flex flex-col flex-shrink-0 border-e border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 transition-all duration-300 ease-in-out overflow-hidden"
                :class="sidebarCollapsed ? 'w-[64px]' : 'w-[260px]'"
            >
                <div class="flex h-full flex-col overflow-y-auto overflow-x-hidden px-3 py-3">

                    <!-- Logo + botón toggle (expandido) -->
                    <div x-show="!sidebarCollapsed" class="mb-2 flex items-center justify-between">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2 rounded-xl px-2 py-2 transition hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-app-logo />
                        </a>
                        <button type="button" @click="toggleSidebar()" title="Colapsar sidebar"
                            class="flex shrink-0 items-center justify-center rounded-lg p-1.5 text-zinc-500 transition-colors hover:bg-zinc-200 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-100">
                            <i class="bi bi-layout-sidebar text-base"></i>
                        </button>
                    </div>

                    <!-- Logo + botón toggle (colapsado) -->
                    <div x-show="sidebarCollapsed" class="mb-2 flex flex-col items-center gap-1">
                        <a href="{{ route('home') }}" class="flex items-center justify-center rounded-xl px-2 py-2 transition hover:bg-zinc-100 dark:hover:bg-zinc-800" wire:navigate>
                            <x-app-logo-icon class="h-8 w-8" />
                        </a>
                        <button type="button" @click="toggleSidebar()" title="Expandir sidebar"
                            class="flex items-center justify-center rounded-lg p-1.5 text-zinc-500 transition-colors hover:bg-zinc-200 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-100">
                            <i class="bi bi-layout-sidebar-reverse text-base"></i>
                        </button>
                    </div>

                    <!-- ✅ MENÚ PRINCIPAL -->
                    <nav class="mt-2 flex flex-col gap-1">
                        <span class="overflow-hidden whitespace-nowrap px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-400 transition-all duration-200 dark:text-zinc-500"
                            :class="sidebarCollapsed ? 'opacity-0 h-0 py-0' : 'opacity-100'">
                            {{ __('Menú Principal') }}
                        </span>

                        @can('Ver Inicio')
                        <a href="{{ route('home') }}" wire:navigate title="{{ __('Inicio') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('home') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}"
                            :class="sidebarCollapsed ? 'justify-center' : 'gap-2'">
                            <i class="bi bi-house-door-fill shrink-0 text-base"></i>
                            <span class="overflow-hidden whitespace-nowrap transition-all duration-200" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'opacity-100'">{{ __('Inicio') }}</span>
                        </a>
                        @endcan

                        @can('Ver Persona')
                        <a href="{{ route('person') }}" wire:navigate title="{{ __('Personas') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('person*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}"
                            :class="sidebarCollapsed ? 'justify-center' : 'gap-2'">
                            <i class="bi bi-person-badge-fill shrink-0 text-base"></i>
                            <span class="overflow-hidden whitespace-nowrap transition-all duration-200" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'opacity-100'">{{ __('Personas') }}</span>
                        </a>
                        @endcan

                        @can('Ver Caja')
                        <a href="{{ route('cash-registers') }}" wire:navigate title="{{ __('Caja') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('cash-registers*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}"
                            :class="sidebarCollapsed ? 'justify-center' : 'gap-2'">
                            <i class="bi bi-cash-coin shrink-0 text-base"></i>
                            <span class="overflow-hidden whitespace-nowrap transition-all duration-200" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'opacity-100'">{{ __('Caja') }}</span>
                        </a>
                        @endcan

                        @can('Ver Clientes')
                        <a href="{{ route('clients') }}" wire:navigate title="{{ __('Clientes') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('clients*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}"
                            :class="sidebarCollapsed ? 'justify-center' : 'gap-2'">
                            <i class="bi bi-people-fill shrink-0 text-base"></i>
                            <span class="overflow-hidden whitespace-nowrap transition-all duration-200" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'opacity-100'">{{ __('Clientes') }}</span>
                        </a>
                        @endcan

                        @can('Ver Productos')
                        <a href="{{ route('products') }}" wire:navigate title="{{ __('Productos') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('products*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}"
                            :class="sidebarCollapsed ? 'justify-center' : 'gap-2'">
                            <i class="bi bi-capsule-pill shrink-0 text-base"></i>
                            <span class="overflow-hidden whitespace-nowrap transition-all duration-200" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'opacity-100'">{{ __('Productos') }}</span>
                        </a>
                        @endcan

                        @can('Ver Compras')
                        <a href="{{ route('purchases') }}" wire:navigate title="{{ __('Compras') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('purchases*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}"
                            :class="sidebarCollapsed ? 'justify-center' : 'gap-2'">
                            <i class="bi bi-cart-plus-fill shrink-0 text-base"></i>
                            <span class="overflow-hidden whitespace-nowrap transition-all duration-200" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'opacity-100'">{{ __('Compras') }}</span>
                        </a>
                        @endcan

                        @can('Ver Ventas')
                        <a href="{{ route('sales') }}" wire:navigate title="{{ __('Ventas') }}"
                            class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('sales*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white font-semibold' : 'text-zinc-700 dark:text-zinc-300' }}"
                            :class="sidebarCollapsed ? 'justify-center' : 'gap-2'">
                            <i class="bi bi-bag-check-fill shrink-0 text-base"></i>
                            <span class="overflow-hidden whitespace-nowrap transition-all duration-200" :class="sidebarCollapsed ? 'w-0 opacity-0' : 'opacity-100'">{{ __('Ventas') }}</span>
                        </a>
                        @endcan
                    </nav>

                    <!-- ✅ FARMACIA -->
                    @canany([
                        'Ver Proveedores', 'Ver Laboratorios', 'Ver Categorias',
                        'Ver Presentaciones', 'Ver Lotes',
                        'Ver Ajustes Inventario', 'Ver Kardex',
                        'Ver Facturas', 'Ver Reportes'
                    ])
                    @php
                        $isFarmaciaSection = request()->routeIs('suppliers*') || request()->routeIs('laboratories*')
                            || request()->routeIs('categories*') || request()->routeIs('presentations*')
                            || request()->routeIs('lots*')
                            || request()->routeIs('inventory-adjustments*') || request()->routeIs('invoices*')
                            || request()->routeIs('kardex*') || request()->routeIs('inventory-alerts*')
                            || request()->routeIs('reports*');
                    @endphp
                    <nav class="mt-4 flex flex-col gap-1">
                        <span class="overflow-hidden whitespace-nowrap px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-400 transition-all duration-200 dark:text-zinc-500"
                            :class="sidebarCollapsed ? 'opacity-0 h-0 py-0' : 'opacity-100'">
                            {{ __('Farmacia') }}
                        </span>

                        <!-- Colapsado: solo íconos -->
                        <div x-show="sidebarCollapsed" class="flex flex-col gap-1">
                            @can('Ver Proveedores')
                            <a href="{{ route('suppliers') }}" wire:navigate title="{{ __('Proveedores') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('suppliers*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-truck text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Laboratorios')
                            <a href="{{ route('laboratories') }}" wire:navigate title="{{ __('Laboratorios') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('laboratories*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-building text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Categorias')
                            <a href="{{ route('categories') }}" wire:navigate title="{{ __('Categorías') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('categories*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-tags-fill text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Presentaciones')
                            <a href="{{ route('presentations') }}" wire:navigate title="{{ __('Presentaciones') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('presentations*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-box-seam-fill text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Lotes')
                            <a href="{{ route('lots') }}" wire:navigate title="{{ __('Lotes') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('lots*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-box-fill text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Ajustes Inventario')
                            <a href="{{ route('inventory-adjustments') }}" wire:navigate title="{{ __('Ajustes de Inventario') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('inventory-adjustments*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-sliders2-vertical text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Facturas')
                            <a href="{{ route('invoices') }}" wire:navigate title="{{ __('Facturación') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('invoices*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-receipt text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Kardex')
                            <a href="{{ route('kardex') }}" wire:navigate title="{{ __('Kardex') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('kardex*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-journal-medical text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Reportes')
                            <a href="{{ route('reports') }}" wire:navigate title="{{ __('Reportes') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('reports*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-file-earmark-bar-graph-fill text-base"></i>
                            </a>
                            @endcan
                        </div>

                        <!-- Expandido: grupo colapsable -->
                        <div x-show="!sidebarCollapsed">
                            <details class="group" @if($isFarmaciaSection) open @endif>
                                <summary class="flex cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-200 dark:hover:bg-zinc-800 {{ $isFarmaciaSection ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 dark:text-zinc-200' }}">
                                    <i class="bi bi-capsule-pill shrink-0 text-base"></i>
                                    <span class="flex-1 whitespace-nowrap">{{ __('Farmacia') }}</span>
                                    <i class="bi bi-chevron-down text-xs transition-transform duration-200 group-open:rotate-180"></i>
                                </summary>
                                <div class="ml-5 mt-1 space-y-1 border-l border-zinc-200 pl-3 dark:border-zinc-700">
                                    @can('Ver Proveedores')
                                    <a href="{{ route('suppliers') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('suppliers*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-truck text-base"></i> {{ __('Proveedores') }}
                                    </a>
                                    @endcan
                                    @can('Ver Laboratorios')
                                    <a href="{{ route('laboratories') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('laboratories*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-building text-base"></i> {{ __('Laboratorios') }}
                                    </a>
                                    @endcan
                                    @can('Ver Categorias')
                                    <a href="{{ route('categories') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('categories*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-tags-fill text-base"></i> {{ __('Categorías') }}
                                    </a>
                                    @endcan
                                    @can('Ver Presentaciones')
                                    <a href="{{ route('presentations') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('presentations*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-box-seam-fill text-base"></i> {{ __('Presentaciones') }}
                                    </a>
                                    @endcan
                                    @can('Ver Lotes')
                                    <a href="{{ route('lots') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('lots*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-box-fill text-base"></i> {{ __('Lotes') }}
                                    </a>
                                    @endcan
                                    @can('Ver Ajustes Inventario')
                                    <a href="{{ route('inventory-adjustments') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('inventory-adjustments*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-sliders2-vertical text-base"></i> {{ __('Ajustes de Inventario') }}
                                    </a>
                                    @endcan
                                    @can('Ver Facturas')
                                    <a href="{{ route('invoices') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('invoices*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-receipt text-base"></i> {{ __('Facturación') }}
                                    </a>
                                    @endcan
                                    @can('Ver Kardex')
                                    <a href="{{ route('kardex') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('kardex*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-journal-medical text-base"></i> {{ __('Kardex') }}
                                    </a>
                                    @endcan
                                    @can('Ver Reportes')
                                    <a href="{{ route('reports') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('reports*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-file-earmark-bar-graph-fill text-base"></i> {{ __('Reportes') }}
                                    </a>
                                    @endcan
                                </div>
                            </details>
                        </div>
                    </nav>
                    @endcanany

                    <!-- ✅ GESTIÓN DE USUARIOS -->
                    @can('Ver Gestion de Usuarios')
                    @php
                        $isUserSection = request()->routeIs('users*') || request()->routeIs('permissions*');
                    @endphp
                    <nav class="mt-4 flex flex-col gap-1">
                        <span class="overflow-hidden whitespace-nowrap px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-400 transition-all duration-200 dark:text-zinc-500"
                            :class="sidebarCollapsed ? 'opacity-0 h-0 py-0' : 'opacity-100'">
                            {{ __('Gestión de Usuarios') }}
                        </span>

                        <div x-show="sidebarCollapsed" class="flex flex-col gap-1">
                            @can('Ver Usuarios')
                            <a href="{{ route('users') }}" wire:navigate title="{{ __('Usuarios') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('users*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-person-lines-fill text-base"></i>
                            </a>
                            @endcan
                            @can('Ver Permisos')
                            <a href="{{ route('permissions') }}" wire:navigate title="{{ __('Permisos') }}" class="flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ request()->routeIs('permissions*') ? 'bg-zinc-200 dark:bg-zinc-800 text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                <i class="bi bi-shield-lock-fill text-base"></i>
                            </a>
                            @endcan
                        </div>

                        <div x-show="!sidebarCollapsed">
                            <details class="group" @if($isUserSection) open @endif>
                                <summary class="flex cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-zinc-200 dark:hover:bg-zinc-800 {{ $isUserSection ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 dark:text-zinc-200' }}">
                                    <i class="bi bi-people-fill shrink-0 text-base"></i>
                                    <span class="flex-1 whitespace-nowrap">{{ __('Usuarios') }}</span>
                                    <i class="bi bi-chevron-down text-xs transition-transform duration-200 group-open:rotate-180"></i>
                                </summary>
                                <div class="ml-5 mt-1 space-y-1 border-l border-zinc-200 pl-3 dark:border-zinc-700">
                                    @can('Ver Usuarios')
                                    <a href="{{ route('users') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('users*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-person-lines-fill text-base"></i> {{ __('Usuarios') }}
                                    </a>
                                    @endcan
                                    @can('Ver Permisos')
                                    <a href="{{ route('permissions') }}" wire:navigate class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('permissions*') ? 'bg-zinc-200 font-semibold text-zinc-900 dark:bg-zinc-800 dark:text-white' : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800' }}">
                                        <i class="bi bi-shield-lock-fill text-base"></i> {{ __('Permisos') }}
                                    </a>
                                    @endcan
                                </div>
                            </details>
                        </div>
                    </nav>
                    @endcan

                    <div class="flex-1"></div>

                    <!-- ✅ PERFIL DE USUARIO (Desktop) -->
                    <div class="mt-4">
                        <div x-show="sidebarCollapsed" class="flex justify-center">
                            <flux:dropdown position="top" align="start">
                                <button type="button" title="{{ auth()->user()->name }}" class="flex h-9 w-9 items-center justify-center rounded-lg bg-neutral-200 text-sm font-semibold text-black transition hover:opacity-80 dark:bg-zinc-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </button>
                                <flux:menu class="w-[220px] dark:bg-zinc-900 dark:text-zinc-100">
                                    <flux:menu.radio.group>
                                        <div class="flex items-center gap-2 px-2 py-2 text-sm">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-neutral-200 font-semibold text-black dark:bg-zinc-700 dark:text-white">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                            <div class="grid flex-1 text-start text-sm leading-tight">
                                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                                <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
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

                        <div x-show="!sidebarCollapsed">
                            <flux:dropdown position="top" align="start">
                                <flux:profile
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                    icon:trailing="chevrons-up-down"
                                />
                                <flux:menu class="w-[220px] dark:bg-zinc-900 dark:text-zinc-100">
                                    <flux:menu.radio.group>
                                        <div class="flex items-center gap-2 px-2 py-2 text-sm">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-neutral-200 font-semibold text-black dark:bg-zinc-700 dark:text-white">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                            <div class="grid flex-1 text-start text-sm leading-tight">
                                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                                <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
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
                    </div>

                    <!-- ✅ BOTÓN TEMA (Desktop) -->
                    <div class="mt-3">
                        <div x-show="sidebarCollapsed" class="flex justify-center">
                            <button type="button" @click="toggle()"
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-300 bg-white text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700"
                                :title="theme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'">
                                <i class="bi bi-moon-stars-fill dark:hidden text-zinc-700"></i>
                                <i class="bi bi-sun-fill hidden dark:inline text-yellow-400"></i>
                            </button>
                        </div>

                        <div x-show="!sidebarCollapsed">
                            <button type="button" @click="toggle()"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm font-medium text-zinc-800 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                                <i class="bi bi-moon-stars-fill dark:hidden text-zinc-700"></i>
                                <i class="bi bi-sun-fill hidden dark:inline text-yellow-400"></i>
                                <span class="hidden dark:inline text-white">Modo oscuro</span>
                                <span class="dark:hidden">Modo claro</span>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- ═══════════════════════════════════════════════
                 CONTENIDO PRINCIPAL
            ═══════════════════════════════════════════════ --}}
            <div class="flex flex-1 flex-col min-w-0">

                <!-- ✅ HEADER MÓVIL -->
                <flux:header class="border-b border-zinc-200 bg-white px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900 lg:hidden">
                    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                    <flux:spacer />

                    <div class="flex items-center gap-2">
                        @livewire('livewire-notification-bell')

                        <button type="button" @click="toggle()"
                            class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                            <i class="bi bi-moon-stars-fill dark:hidden"></i>
                            <i class="bi bi-sun-fill hidden dark:inline"></i>
                        </button>

                        <flux:dropdown position="top" align="end">
                            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
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

                <!-- ✅ SIDEBAR MÓVIL (flyout de Flux, solo <lg) -->
                <flux:sidebar
                    sticky
                    stashable
                    class="border-e border-zinc-200 bg-zinc-50 px-3 py-3 dark:border-zinc-800 dark:bg-zinc-900 lg:hidden"
                >
                    <flux:sidebar.toggle
                        class="lg:hidden rounded-lg p-2 text-zinc-600 hover:bg-zinc-200 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        icon="x-mark"
                    />

                    <a href="{{ route('home') }}" class="me-5 flex items-center space-x-2 rounded-xl px-2 py-2 transition hover:bg-zinc-100 dark:hover:bg-zinc-800 rtl:space-x-reverse" wire:navigate>
                        <x-app-logo />
                    </a>

                    <flux:navlist variant="outline" class="mt-4">
                        <flux:navlist.group :heading="__('Menú Principal')" class="grid gap-1">
                            @can('Ver Inicio')
                            <flux:navlist.item :href="route('home')" :current="request()->routeIs('home')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-house-door-fill me-2"></i> {{ __('Inicio') }}
                            </flux:navlist.item>
                            @endcan
                            @can('Ver Persona')
                            <flux:navlist.item :href="route('person')" :current="request()->routeIs('person*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-person-badge-fill me-2"></i> {{ __('Personas') }}
                            </flux:navlist.item>
                            @endcan
                            @can('Ver Caja')
                            <flux:navlist.item :href="route('cash-registers')" :current="request()->routeIs('cash-registers*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-cash-coin me-2"></i> {{ __('Caja') }}
                            </flux:navlist.item>
                            @endcan
                            @can('Ver Clientes')
                            <flux:navlist.item :href="route('clients')" :current="request()->routeIs('clients*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-people-fill me-2"></i> {{ __('Clientes') }}
                            </flux:navlist.item>
                            @endcan
                            @can('Ver Productos')
                            <flux:navlist.item :href="route('products')" :current="request()->routeIs('products*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-capsule-pill me-2"></i> {{ __('Productos') }}
                            </flux:navlist.item>
                            @endcan
                            @can('Ver Compras')
                            <flux:navlist.item :href="route('purchases')" :current="request()->routeIs('purchases*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-cart-plus-fill me-2"></i> {{ __('Compras') }}
                            </flux:navlist.item>
                            @endcan
                            @can('Ver Ventas')
                            <flux:navlist.item :href="route('sales')" :current="request()->routeIs('sales*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-bag-check-fill me-2"></i> {{ __('Ventas') }}
                            </flux:navlist.item>
                            @endcan
                        </flux:navlist.group>

                        @canany([
                            'Ver Proveedores', 'Ver Laboratorios', 'Ver Categorias',
                            'Ver Presentaciones', 'Ver Lotes',
                            'Ver Ajustes Inventario', 'Ver Kardex',
                            'Ver Facturas', 'Ver Reportes'
                        ])
                        <flux:navlist.group :heading="__('Farmacia')" class="grid mt-3">
                            <details class="group" @if($isFarmaciaSection ?? false) open @endif>
                                <summary class="flex items-center cursor-pointer rounded-xl px-3 py-2.5 transition hover:bg-zinc-200 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
                                    <i class="bi bi-capsule-pill me-2"></i>
                                    <span>{{ __('Farmacia') }}</span>
                                    <i class="bi bi-chevron-down ms-auto transition-transform duration-200 group-open:rotate-180"></i>
                                </summary>
                                <div class="ml-5 mt-2 space-y-1 border-l border-zinc-200 pl-3 dark:border-zinc-700">
                                    @can('Ver Proveedores')
                                    <flux:navlist.item :href="route('suppliers')" :current="request()->routeIs('suppliers*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-truck me-2"></i> {{ __('Proveedores') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Laboratorios')
                                    <flux:navlist.item :href="route('laboratories')" :current="request()->routeIs('laboratories*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-building me-2"></i> {{ __('Laboratorios') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Categorias')
                                    <flux:navlist.item :href="route('categories')" :current="request()->routeIs('categories*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-tags-fill me-2"></i> {{ __('Categorías') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Presentaciones')
                                    <flux:navlist.item :href="route('presentations')" :current="request()->routeIs('presentations*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-box-seam-fill me-2"></i> {{ __('Presentaciones') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Lotes')
                                    <flux:navlist.item :href="route('lots')" :current="request()->routeIs('lots*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-box-fill me-2"></i> {{ __('Lotes') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Ajustes Inventario')
                                    <flux:navlist.item :href="route('inventory-adjustments')" :current="request()->routeIs('inventory-adjustments*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-sliders2-vertical me-2"></i> {{ __('Ajustes de Inventario') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Facturas')
                                    <flux:navlist.item :href="route('invoices')" :current="request()->routeIs('invoices*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-receipt me-2"></i> {{ __('Facturación') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Kardex')
                                    <flux:navlist.item :href="route('kardex')" :current="request()->routeIs('kardex*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-journal-medical me-2"></i> {{ __('Kardex') }}
                                    </flux:navlist.item>
                                    @endcan
                                    @can('Ver Reportes')
                                    <flux:navlist.item :href="route('reports')" :current="request()->routeIs('reports*')" wire:navigate class="rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                        <i class="bi bi-file-earmark-bar-graph-fill me-2"></i> {{ __('Reportes') }}
                                    </flux:navlist.item>
                                    @endcan
                                </div>
                            </details>
                        </flux:navlist.group>
                        @endcanany

                        @can('Ver Gestion de Usuarios')
                        <flux:navlist.group :heading="__('Gestión de Usuarios')" class="grid mt-3">
                            @can('Ver Usuarios')
                            <flux:navlist.item :href="route('users')" :current="request()->routeIs('users*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-person-lines-fill me-2"></i> {{ __('Usuarios') }}
                            </flux:navlist.item>
                            @endcan
                            @can('Ver Permisos')
                            <flux:navlist.item :href="route('permissions')" :current="request()->routeIs('permissions*')" wire:navigate class="rounded-xl px-3 py-2.5 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                <i class="bi bi-shield-lock-fill me-2"></i> {{ __('Permisos') }}
                            </flux:navlist.item>
                            @endcan
                        </flux:navlist.group>
                        @endcan
                    </flux:navlist>

                    <flux:spacer />

                    <div class="flex items-center gap-2 border-t border-zinc-200 px-1 pt-3 dark:border-zinc-800">
                        <button type="button" @click="toggle()" title="{{ __('Cambiar tema') }}"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            <i class="bi bi-moon-stars-fill dark:hidden"></i>
                            <i class="bi bi-sun-fill hidden dark:inline text-yellow-400"></i>
                        </button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="{{ __('Cerrar Sesión') }}"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-red-100 hover:text-red-600 dark:text-zinc-300 dark:hover:bg-red-900/30 dark:hover:text-red-400">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </flux:sidebar>

                <!-- ✅ TOP BAR ESCRITORIO -->
                <div class="hidden w-full items-center border-b border-zinc-200 bg-white px-4 py-2 dark:border-zinc-800 dark:bg-zinc-900 lg:flex">
                    <div class="ml-auto flex items-center">
                        @livewire('livewire-notification-bell')
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>

        @fluxScripts
    </body>
</html>
