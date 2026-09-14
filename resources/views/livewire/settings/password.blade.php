<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Actualizar contrasena')" :subheading="__('Asegurate de que tu cuenta utilice una contrasena larga y aleatoria para mantenerse segura')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">

            <flux:input
                wire:model="current_password"
                :label="__('Contrasena actual')"
                type="password"
                required
                autocomplete="current-password"
            />

            <flux:input
                wire:model="password"
                :label="__('Nueva contrasena')"
                type="password"
                required
                autocomplete="new-password"
            />

            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirmar contrasena')"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">
                        {{ __('Guardar') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>

        </form>
    </x-settings.layout>
</section>