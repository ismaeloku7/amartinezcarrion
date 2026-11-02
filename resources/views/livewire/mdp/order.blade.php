<div class="space-y-4">
    <x-ui.alert>{{ __('Al principio conviene seguir un orden que facilite las proyecciones. Marca el libro por el que vas. Cuando llegues a la parte histórica del Antiguo Testamento, intercala un libro de ella con uno de esta lista.') }}</x-ui.alert>
    <x-ui.card class="p-2">
        <ol>
            @foreach ($books as $i => $book)
                @php $n = $i + 1; @endphp
                <li @class(['flex min-h-12 items-center gap-3 rounded-2xl px-3 py-2 text-[0.9375rem]', 'bg-app text-white' => $n === $position])>
                    <span @class(['grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-semibold', 'bg-white/20' => $n === $position, 'bg-emerald-100 text-emerald-700' => $n < $position, 'bg-fill text-stone-500' => $n > $position])>
                        @if ($n < $position)<x-ui.icon name="check" class="h-3.5 w-3.5" />@else{{ $n }}@endif
                    </span>
                    <span @class(['flex-1', 'font-semibold' => $n === $position, 'text-stone-400' => $n < $position])>{{ $book }}</span>
                    @if ($n !== $position)<button type="button" wire:click="setPosition({{ $n }})" class="rounded-full px-3 py-1.5 text-xs font-medium text-app active:bg-app/10">{{ __('Estoy aquí') }}</button>@endif
                </li>
            @endforeach
        </ol>
    </x-ui.card>
</div>
