<div class="flex flex-col gap-6">
    <div class="flex w-full flex-col text-center">
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Ingresar') }}</h1>
    </div>

    @if (session('status'))
        <div class="rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-center text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="flex flex-col gap-5">
        <div class="space-y-1">
            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Correo electrónico') }}</label>
            <input
                wire:model="email"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="correo@ejemplo.com"
                class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
            @error('email')
                <span class="text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="space-y-1">
            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Contraseña') }}</label>
            <input
                wire:model="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="{{ __('Contraseña') }}"
                class="w-full rounded border border-zinc-400 bg-white px-3 py-2 text-sm text-zinc-800 focus:border-primary focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
            @error('password')
                <span class="text-sm text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
            <input type="checkbox" wire:model="remember" class="rounded border-zinc-400 accent-primary dark:border-zinc-600">
            {{ __('Recordarme') }}
        </label>

        <button type="submit" class="w-full cursor-pointer rounded border border-primary bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-110">
            {{ __('Ingresar') }}
        </button>
    </form>
</div>
