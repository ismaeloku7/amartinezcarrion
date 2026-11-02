<div class="space-y-5">
    <div class="flex flex-col items-center pb-2 text-center">
        <span class="grid h-20 w-20 place-items-center rounded-full bg-app/10 text-3xl font-semibold text-app">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
        <div class="mt-3 text-lg font-semibold">{{ $name }}</div>
        <div class="text-sm text-muted">{{ auth()->user()->email }}</div>
    </div>

    <x-ui.card :title="__('Datos')">
        <form wire:submit="save" class="space-y-4">
            <label class="block">
                <span class="field-label">{{ __('Nombre') }}</span>
                <input wire:model="name" class="field" autocomplete="name">
                @error('name')<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="field-label">{{ __('Idioma') }}</span>
                <select wire:model="locale" class="field">
                    @foreach ($languages as $lang)<option value="{{ $lang->code }}">{{ $lang->name }}</option>@endforeach
                </select>
            </label>
            <label class="block">
                <span class="field-label">{{ __('Zona horaria') }}</span>
                <select wire:model="timezone" class="field">
                    @foreach ($timezones as $tz)<option value="{{ $tz }}">{{ $tz }}</option>@endforeach
                </select>
                <span class="mt-1.5 block text-xs text-muted">{{ __('Los recordatorios se envían según esta zona horaria.') }}</span>
            </label>
            <x-ui.button type="submit" block>{{ __('Guardar') }}</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-start gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-app/10 text-app"><x-ui.icon name="bell" class="h-5 w-5" /></span>
            <div>
                <h2 class="font-semibold">{{ __('Notificaciones') }}</h2>
                <p class="mt-0.5 text-sm text-muted">{{ __('Activa los avisos en este dispositivo para recibir los recordatorios de lectura y repaso.') }}</p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <x-ui.button type="button" onclick="window.appPush.subscribe('portal')">{{ __('Activar') }}</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.appPush.unsubscribe()">{{ __('Desactivar') }}</x-ui.button>
        </div>
        <p id="push-status" class="mt-2 text-center text-xs text-muted"></p>
    </x-ui.card>

    <x-ui.card :title="__('Accesos')">
        <div class="divide-y divide-stone-100">
            @forelse ($entitlements as $e)
                <div class="flex items-center justify-between gap-3 py-3 text-sm first:pt-0 last:pb-0">
                    <span class="flex items-center gap-2 font-medium"><x-ui.icon name="check" class="h-4 w-4 text-emerald-600" />{{ $e->product->name }}</span>
                    <span class="shrink-0 text-xs text-muted">{{ $e->ends_at ? __('hasta :d', ['d' => $e->ends_at->translatedFormat('d M Y')]) : __('sin caducidad') }}</span>
                </div>
            @empty
                <p class="text-sm text-muted">{{ __('No tienes accesos activos.') }}</p>
            @endforelse
        </div>
        @if ($subscriptions->isNotEmpty())
            <x-ui.button :href="route('billing.portal')" variant="soft" block class="mt-4">{{ __('Gestionar suscripción y facturas') }}</x-ui.button>
        @endif
    </x-ui.card>

    <x-ui.card :title="__('Contraseña')">
        <form method="POST" action="{{ route('user-password.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <x-ui.input name="current_password" type="password" :label="__('Contraseña actual')" required autocomplete="current-password" />
            <x-ui.input name="password" type="password" :label="__('Nueva contraseña')" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" type="password" :label="__('Repite la nueva contraseña')" required autocomplete="new-password" />
            <x-ui.button type="submit" variant="secondary" block>{{ __('Cambiar contraseña') }}</x-ui.button>
        </form>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-ui.button type="submit" variant="ghost" block class="text-red-600"><x-ui.icon name="logout" class="h-5 w-5" />{{ __('Cerrar sesión') }}</x-ui.button>
    </form>
</div>
