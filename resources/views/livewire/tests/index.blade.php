<div class="space-y-3">
    @foreach ($tests as $test)
        <x-ui.card class="flex items-center justify-between gap-3">
            <div>
                <div class="font-medium">{{ $test->name }}</div>
                <div class="text-sm text-stone-600">{{ $test->description }}</div>
                @if (($takes[$test->id] ?? collect())->isNotEmpty())<div class="mt-1 text-xs text-stone-500">{{ __(':n tomas', ['n' => $takes[$test->id]->count()]) }}</div>@endif
            </div>
            <x-ui.button :href="route('tests.show', $test)" variant="secondary">{{ __('Abrir') }}</x-ui.button>
        </x-ui.card>
    @endforeach
</div>
