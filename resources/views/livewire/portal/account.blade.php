<div class="space-y-6">
    <x-ui.card :title="__('Datos')">
        <form wire:submit="save" class="space-y-4">
            <label class="block">
                <span class="mb-1 block text-sm font-medium">{{ __('Nombre') }}</span>
                <input wire:model="name" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm">
                @error('name')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium">{{ __('Idioma') }}</span>
                <select wire:model="locale" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
                    @foreach ($languages as $lang)<option value="{{ $lang->code }}">{{ $lang->name }}</option>@endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium">{{ __('Zona horaria') }}</span>
                <select wire:model="timezone" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
                    @foreach ($timezones as $tz)<option value="{{ $tz }}">{{ $tz }}</option>@endforeach
                </select>
                <span class="text-xs text-stone-500">{{ __('Los recordatorios se envían según esta zona horaria.') }}</span>
            </label>
            <x-ui.button type="submit">{{ __('Guardar') }}</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card :title="__('Notificaciones')">
        <p class="text-sm text-stone-600">{{ __('Activa los avisos en este dispositivo para recibir los recordatorios de lectura y repaso.') }}</p>
        <div class="mt-3 flex gap-2">
            <x-ui.button type="button" onclick="window.appPush.subscribe('portal')">{{ __('Activar en este dispositivo') }}</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.appPush.unsubscribe()">{{ __('Desactivar') }}</x-ui.button>
        </div>
        <p id="push-status" class="mt-2 text-xs text-stone-500"></p>
    </x-ui.card>

    <x-ui.card :title="__('Accesos')">
        @forelse ($entitlements as $e)
            <div class="flex items-center justify-between border-b border-stone-100 py-2 text-sm last:border-0">
                <span>{{ $e->product->name }}</span>
                <span class="text-stone-500">{{ $e->ends_at ? __('hasta :d', ['d' => $e->ends_at->translatedFormat('d M Y')]) : __('sin caducidad') }}</span>
            </div>
        @empty
            <p class="text-sm text-stone-500">{{ __('No tienes accesos activos.') }}</p>
        @endforelse
        @if ($subscriptions->isNotEmpty())
            <x-ui.button :href="route('billing.portal')" variant="secondary" class="mt-3">{{ __('Gestionar suscripción y facturas') }}</x-ui.button>
        @endif
    </x-ui.card>

    <x-ui.card :title="__('Contraseña')">
        <form method="POST" action="{{ route('user-password.update') }}" class="space-y-3">
            @csrf @method('PUT')
            <x-ui.input name="current_password" type="password" :label="__('Contraseña actual')" required />
            <x-ui.input name="password" type="password" :label="__('Nueva contraseña')" required />
            <x-ui.input name="password_confirmation" type="password" :label="__('Repite la nueva contraseña')" required />
            <x-ui.button type="submit" variant="secondary">{{ __('Cambiar contraseña') }}</x-ui.button>
        </form>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}">@csrf <x-ui.button type="submit" variant="ghost">{{ __('Cerrar sesión') }}</x-ui.button></form>
</div>
