<div class="space-y-4">
    @forelse ($years as $year => $months)
        <x-ui.card :title="__('Año :n', ['n' => $year])">
            <ol class="grid gap-2 sm:grid-cols-2">
                @foreach ($months as $m)
                    <li class="rounded-lg border border-green-200 bg-green-50 p-3">
                        <div class="text-xs text-green-700">{{ __('Mes :n', ['n' => $m->index]) }} · {{ $m->starts_on->translatedFormat('M Y') }}</div>
                        <p class="text-lg leading-snug text-green-900">{{ $m->monthText->body }}</p>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Aquí aparecerán los textos del mes, hasta doce por año. Un tesoro que irás formando con el tiempo.') }}</x-ui.alert>
    @endforelse
</div>
