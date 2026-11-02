<div class="space-y-2.5">
    @foreach ($tests as $test)
        <a href="{{ route('tests.show', $test) }}" class="flex items-center gap-3.5 rounded-2xl bg-white p-3.5 shadow-soft transition active:scale-[0.99]">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-app/10 text-app"><x-ui.icon name="sparkles" class="h-5 w-5" /></span>
            <span class="min-w-0 flex-1">
                <span class="block font-medium">{{ $test->name }}</span>
                <span class="line-clamp-2 text-sm text-muted">{{ $test->description }}</span>
                @if (($takes[$test->id] ?? collect())->isNotEmpty())<span class="mt-1 inline-block rounded-full bg-fill px-2 py-0.5 text-xs text-stone-600">{{ __(':n tomas', ['n' => $takes[$test->id]->count()]) }}</span>@endif
            </span>
            <x-ui.icon name="chevron-right" class="h-5 w-5 text-stone-300" />
        </a>
    @endforeach
</div>
