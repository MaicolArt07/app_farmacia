<div class="flex flex-col gap-6">
     <x-auth-header :title="__('Ingresar')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

<form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="correo@ejemplo.com"
        />

        <!-- Password -->
         <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Contraseña')"
                viewable
            />

        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Recordarme')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Ingresar') }}</flux:button>
        </div>
    </form>

</div>
