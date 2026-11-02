<div class="space-y-6">
    <div class="rounded-3xl bg-app p-5 text-white shadow-float">
        <div class="flex items-end justify-between gap-3">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-white/70">{{ __('Incorporadas') }}</div>
                <div class="mt-1 text-3xl font-bold">{{ $unlocked }}<span class="text-lg font-medium text-white/60"> / 42</span></div>
            </div>
            <x-ui.icon name="box" class="h-9 w-9 text-white/40" />
        </div>
        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/20"><div class="h-full rounded-full bg-white" style="width: {{ round($unlocked / 42 * 100) }}%"></div></div>
        <p class="mt-3 text-xs leading-relaxed text-white/75">{{ __('Al terminar cada apartado del libro, incorpora su ficha. La velocidad de lectura no importa; los repasos diarios sí.') }}</p>
        @if ($unlocked < 42)
            <button type="button" wire:click="unlockNext" class="mt-4 flex h-12 w-full items-center justify-center rounded-full bg-white font-semibold text-app active:scale-[0.98]">{{ __('He leído el apartado :n', ['n' => $unlocked + 1]) }}</button>
        @endif
    </div>

    @foreach ($groups as $g => $cards)
        <section>
            <h2 class="mb-2.5 flex items-center gap-2 text-sm font-semibold">
                <span class="h-2.5 w-2.5 rounded-full" style="background: {{ \App\Models\Cards\Card::GROUP_COLORS[$g] }}"></span>
                {{ __('Grupo :n', ['n' => $g]) }} <span class="font-normal text-muted">· {{ __('fichas :a-:b', ['a' => ($g - 1) * 7 + 1, 'b' => $g * 7]) }}</span>
            </h2>
            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                @foreach ($cards as $c)
                    @if ($c->number <= $unlocked)
                        <a href="{{ route('cards.show', $c->number) }}" class="flex aspect-[3/4] flex-col justify-between rounded-2xl bg-white p-2 shadow-soft active:scale-95" style="box-shadow: inset 0 3px 0 {{ $c->color() }}, var(--shadow-soft)">
                            <span class="text-lg font-bold leading-none" style="color: {{ $c->color() }}">{{ $c->number }}</span>
                            <span class="line-clamp-3 text-[0.625rem] leading-tight text-stone-600">{{ $c->title }}</span>
                        </a>
                    @else
                        <div class="flex aspect-[3/4] flex-col justify-between rounded-2xl border-2 border-dashed border-stone-200 p-2 text-stone-300">
                            <span class="text-lg font-bold leading-none">{{ $c->number }}</span>
                            <x-ui.icon name="lock" class="h-4 w-4" />
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach

    <x-ui.card :title="__('Cuando te haga falta')">
        <p class="text-xs text-muted">{{ __('Con el tiempo, varias fichas vendrán solas a la mente según la situación.') }}</p>
        <ul class="mt-3 divide-y divide-stone-100">
            @foreach ($situations as $label => $numbers)
                <li class="py-2.5 first:pt-0 last:pb-0">
                    <div class="text-sm text-stone-700">{{ $label }}</div>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        @foreach ($numbers as $n)
                            <a href="{{ $n <= $unlocked ? route('cards.show', $n) : '#' }}" class="grid h-8 min-w-8 place-items-center rounded-lg px-1.5 text-xs font-semibold text-white {{ $n <= $unlocked ? 'active:scale-95' : 'pointer-events-none opacity-30' }}" style="background: {{ \App\Models\Cards\Card::GROUP_COLORS[\App\Models\Cards\Card::groupOf($n)] }}">{{ $n }}</a>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    </x-ui.card>
</div>
