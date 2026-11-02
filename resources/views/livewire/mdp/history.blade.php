<div class="space-y-4">
    @forelse ($days as $d)
        <x-ui.card>
            <div class="flex items-center justify-between text-xs text-stone-500">
                <span>{{ $d->date->translatedFormat('l, j \d\e F Y') }}</span>
                <span>@if ($d->reading_from){{ $d->reading_from }} – {{ $d->reading_to }}@endif @if ($d->status === 'minimum')· {{ __('mínimo') }}@endif</span>
            </div>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($d->texts as $t)<li class="{{ $t->is_day_text ? 'font-medium text-red-800' : '' }}"><x-mdp.text :text="$t" /></li>@endforeach
            </ul>
            @if ($d->prayer)
                <details class="mt-2 text-sm text-stone-600"><summary class="cursor-pointer text-xs">{{ __('Oración') }}</summary><p class="mt-1 whitespace-pre-line">{{ $d->prayer }}</p></details>
            @endif
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Todavía no hay páginas en tu libreta.') }}</x-ui.alert>
    @endforelse
    {{ $days->links() }}
</div>
