<div class="space-y-6">
    @forelse ($years as $year => $months)
        <section>
            <h2 class="mb-3 text-[1.0625rem] font-semibold tracking-tight">{{ __('Año :n', ['n' => $year]) }}</h2>
            <ol class="grid gap-2.5 sm:grid-cols-2">
                @foreach ($months as $m)
                    <li class="rounded-3xl bg-green-50 p-4">
                        <div class="text-xs font-medium text-green-700">{{ __('Mes :n', ['n' => $m->index]) }} · {{ $m->starts_on->translatedFormat('M Y') }}</div>
                        <p class="mt-1 font-serif text-lg leading-snug text-green-900">{{ $m->monthText->body }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    @empty
        <x-ui.alert>{{ __('Aquí aparecerán los textos del mes, hasta doce por año. Un tesoro que irás formando con el tiempo.') }}</x-ui.alert>
    @endforelse
</div>
