<div class="mx-auto max-w-3xl p-6">
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100">
            Datos de la Empresa
        </h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            Información del contribuyente usada para la facturación (NIT, razón social, y datos del sistema autorizado ante el SIN).
        </p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 px-6 py-5 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">NIT <span class="text-red-600">*</span></label>
                <input type="text" wire:model="form.nit"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.nit') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Razón Social <span class="text-red-600">*</span></label>
                <input type="text" wire:model="form.razon_social"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.razon_social') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Nombre Comercial <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.nombre_comercial"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.nombre_comercial') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Actividad Económica <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.actividad_economica"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.actividad_economica') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Correo Electrónico <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="email" wire:model="form.email"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Teléfono <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.phone"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.phone') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Dirección <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.address"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.address') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2 border-t border-gray-200 pt-4 dark:border-zinc-700">
                <h2 class="mb-3 text-sm font-semibold text-zinc-700 dark:text-zinc-200">Datos del Sistema de Facturación (SIAT)</h2>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Código de Sistema <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.codigo_sistema"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.codigo_sistema') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">N° Solicitud <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="number" wire:model="form.numero_solicitud"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.numero_solicitud') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">CUIS <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.cuis"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.cuis') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Ambiente <span class="text-red-600">*</span></label>
                <select wire:model="form.ambiente"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="PRUEBA">Pruebas (Piloto)</option>
                    <option value="PRODUCCION">Producción</option>
                </select>
                @error('form.ambiente') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Versión de Servicios SOAP <span class="text-red-600">*</span></label>
                <select wire:model="form.soap_version"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                    <option value="V2">V2</option>
                    <option value="V3">V3 (sfd)</option>
                </select>
                @error('form.soap_version') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">WSDL - Sincronización de Datos <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.wsdl_sincronizacion"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.wsdl_sincronizacion') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">WSDL - Operaciones <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.wsdl_operaciones"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.wsdl_operaciones') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">WSDL - Obtención de Códigos (CUIS/CUFD) <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.wsdl_codigos"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.wsdl_codigos') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">WSDL - Factura Compra-Venta <span class="text-xs font-normal text-gray-400 dark:text-zinc-500">(Opcional)</span></label>
                <input type="text" wire:model="form.wsdl_factura_compra_venta"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-xs dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                @error('form.wsdl_factura_compra_venta') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-gray-200 px-6 py-4 dark:border-zinc-800">
            <button wire:click="save"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                Guardar
            </button>
        </div>
    </div>
</div>
