<div class="space-y-5">
    <x-ui.card :title="__('Invita a personas de confianza')">
        <p class="text-sm leading-relaxed text-muted">{{ __('Comparte un enlace con cada persona. Responden sin registrarse y de forma anónima. Verás los resultados cuando haya al menos :n respuestas.', ['n' => $min]) }}</p>
        <form wire:submit="create" class="mt-4 space-y-3">
            <div class="grid grid-cols-[1fr_5.5rem] gap-2">
                <label class="block"><span class="field-label">{{ __('Etiqueta (solo la ves tú)') }}</span><input wire:model="label" placeholder="{{ __('Familia, trabajo, amigos…') }}" class="field"></label>
                <label class="block"><span class="field-label">{{ __('Usos') }}</span><input type="number" inputmode="numeric" min="1" max="20" wire:model="maxUses" class="field text-center"></label>
            </div>
            @error('label')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <x-ui.button type="submit" block><x-ui.icon name="link" class="h-5 w-5" />{{ __('Crear enlace') }}</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card>
        @php $pct = $min > 0 ? min(100, round($responses / $min * 100)) : 100; @endphp
        <div class="flex items-baseline justify-between text-sm">
            <span class="text-stone-600">{{ __('Respuestas recibidas') }}</span>
            <span><strong class="text-lg">{{ $responses }}</strong><span class="text-muted"> / {{ $min }} {{ __('mínimas') }}</span></span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-fill"><div class="h-full rounded-full bg-app transition-all" style="width: {{ $pct }}%"></div></div>
        @if ($hasResults)<x-ui.button :href="route('tests.johari.results', $take)" block class="mt-4">{{ __('Ver resultados') }}</x-ui.button>@endif
    </x-ui.card>

    <section class="space-y-2.5">
        @forelse ($invitations as $inv)
            <div class="rounded-2xl bg-white p-4 shadow-soft" x-data="{ copied: false }">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-medium">{{ $inv->label ?: __('Enlace') }}</span>
                    <span class="shrink-0 text-xs text-muted">
                        {{ $inv->uses }}/{{ $inv->max_uses }}
                        @if ($inv->revoked_at)· <span class="text-red-600">{{ __('revocado') }}</span>@elseif ($inv->expires_at->isPast())· {{ __('caducado') }}@endif
                    </span>
                </div>
                @if ($inv->isUsable())
                    <div class="mt-1 truncate text-xs text-muted">{{ $inv->url() }}</div>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <x-ui.button type="button" variant="soft" size="sm" x-on:click="navigator.clipboard.writeText('{{ $inv->url() }}'); copied = true; setTimeout(() => copied = false, 1500)"><span x-show="!copied">{{ __('Copiar') }}</span><span x-show="copied">{{ __('Copiado') }}</span></x-ui.button>
                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="revoke({{ $inv->id }})">{{ __('Revocar') }}</x-ui.button>
                    </div>
                @endif
            </div>
        @empty
            <p class="rounded-2xl bg-white p-4 text-sm text-muted shadow-soft">{{ __('Todavía no has creado ningún enlace.') }}</p>
        @endforelse
    </section>
</div>
