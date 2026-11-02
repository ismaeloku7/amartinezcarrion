<div class="space-y-3">
    @forelse ($days as $d)
        <x-ui.card class="p-4">
            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-xs">
                <span class="font-semibold text-stone-700 first-letter:uppercase">{{ $d->date->translatedFormat('l, j \d\e F Y') }}</span>
                <span class="flex items-center gap-1.5 text-muted">
                    @if ($d->reading_from){{ $d->reading_from }} – {{ $d->reading_to }}@endif
                    @if ($d->status === 'minimum')<span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700">{{ __('mínimo') }}</span>@endif
                </span>
            </div>
            <ul class="mt-2.5 space-y-1.5 text-[0.9375rem] leading-relaxed">
                @foreach ($d->texts as $t)<li class="{{ $t->is_day_text ? 'font-medium text-red-900' : 'text-stone-700' }}"><x-mdp.text :text="$t" /></li>@endforeach
            </ul>
            @if ($d->prayer)
                <details class="mt-3 rounded-2xl bg-fill px-3.5 py-2.5 text-sm text-stone-600">
                    <summary class="cursor-pointer text-xs font-medium text-stone-500">{{ __('Oración') }}</summary>
                    <p class="mt-2 whitespace-pre-line">{{ $d->prayer }}</p>
                </details>
            @endif
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Todavía no hay páginas en tu libreta.') }}</x-ui.alert>
    @endforelse
    {{ $days->links() }}
</div>
