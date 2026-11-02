<div class="space-y-4">
    <x-ui.card :title="__('Invita a personas de confianza')">
        <p class="text-sm text-stone-600">{{ __('Comparte un enlace con cada persona. Responden sin registrarse y de forma anónima. Verás los resultados cuando haya al menos :n respuestas.', ['n' => $min]) }}</p>
        <form wire:submit="create" class="mt-3 flex flex-wrap items-end gap-2">
            <label class="block flex-1"><span class="mb-1 block text-xs font-medium">{{ __('Etiqueta (solo la ves tú)') }}</span><input wire:model="label" placeholder="{{ __('Familia, trabajo, amigos…') }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
            <label class="block w-24"><span class="mb-1 block text-xs font-medium">{{ __('Usos') }}</span><input type="number" min="1" max="20" wire:model="maxUses" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
            <x-ui.button type="submit">{{ __('Crear enlace') }}</x-ui.button>
        </form>
        @error('label')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-center justify-between">
            <span class="text-sm">{{ __('Respuestas recibidas') }}: <strong>{{ $responses }}</strong> / {{ $min }} {{ __('mínimas') }}</span>
            @if ($hasResults)<x-ui.button :href="route('tests.johari.results', $take)">{{ __('Ver resultados') }}</x-ui.button>@endif
        </div>
        <ul class="mt-3 divide-y divide-stone-100 text-sm">
            @forelse ($invitations as $inv)
                <li class="py-2" x-data="{ copied: false }">
                    <div class="flex items-center justify-between gap-2">
                        <span>{{ $inv->label ?: __('Enlace') }} · {{ $inv->uses }}/{{ $inv->max_uses }} @if ($inv->revoked_at)· <span class="text-red-600">{{ __('revocado') }}</span>@elseif ($inv->expires_at->isPast())· {{ __('caducado') }}@endif</span>
                        <div class="flex gap-1">
                            @if ($inv->isUsable())
                                <x-ui.button type="button" variant="secondary" x-on:click="navigator.clipboard.writeText('{{ $inv->url() }}'); copied = true; setTimeout(() => copied = false, 1500)"><span x-show="!copied">{{ __('Copiar') }}</span><span x-show="copied">{{ __('Copiado') }}</span></x-ui.button>
                                <x-ui.button type="button" variant="ghost" wire:click="revoke({{ $inv->id }})">{{ __('Revocar') }}</x-ui.button>
                            @endif
                        </div>
                    </div>
                    @if ($inv->isUsable())<div class="mt-1 break-all text-xs text-stone-400">{{ $inv->url() }}</div>@endif
                </li>
            @empty
                <li class="py-2 text-stone-500">{{ __('Todavía no has creado ningún enlace.') }}</li>
            @endforelse
        </ul>
    </x-ui.card>
</div>
