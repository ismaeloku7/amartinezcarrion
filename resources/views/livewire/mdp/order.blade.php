<div class="space-y-4">
    <x-ui.alert>{{ __('Al principio conviene seguir un orden que facilite las proyecciones. Marca el libro por el que vas. Cuando llegues a la parte histórica del Antiguo Testamento, intercala un libro de ella con uno de esta lista.') }}</x-ui.alert>
    <x-ui.card>
        <ol class="divide-y divide-stone-100">
            @foreach ($books as $i => $book)
                <li class="flex items-center justify-between py-2 text-sm {{ $i + 1 === $position ? 'font-semibold text-red-800' : ($i + 1 < $position ? 'text-stone-400 line-through' : '') }}">
                    <span>{{ $i + 1 }}. {{ $book }}</span>
                    @if ($i + 1 !== $position)<button type="button" wire:click="setPosition({{ $i + 1 }})" class="text-xs text-stone-500 underline">{{ __('Estoy aquí') }}</button>@endif
                </li>
            @endforeach
        </ol>
    </x-ui.card>
</div>
