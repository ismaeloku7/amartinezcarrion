<div class="space-y-4" @if ($aiPending) wire:poll.4s @endif>
    <x-ui.card>
        <div class="mb-3 flex items-center justify-between text-xs text-stone-500">
            <span>{{ $take->completed_at?->translatedFormat('j M Y') }} · {{ __(':n enunciados respondidos', ['n' => $result['indicators']['answered']]) }}@if ($result['indicators']['unknown_count']) · {{ $result['indicators']['unknown_count'] }} «?»@endif</span>
        </div>
        <x-tests.bars :scores="$result['scores']" :max="$result['max']" :scales="$scales" :highlight="$viewType" :order="['2', '3', '4', '5', '6', '7', '8', '9', '1']" />
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-stone-600">
            <span><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background: #b5121b"></span> {{ __('Extrovertidos (2, 3, 7, 8)') }}</span>
            <span><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background: #2b5d8c"></span> {{ __('Introvertidos (1, 4, 5, 6)') }}</span>
            <span><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background: #3c8c5b"></span> {{ __('Pacificador (9)') }}</span>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="text-xs uppercase tracking-wide text-stone-500">{{ __('Tu tipo principal') }}</div>
        <div class="mt-1 flex items-baseline gap-3">
            <span class="text-5xl font-bold" style="color: {{ $scales[$top]->color }}">{{ $top }}</span>
            <div>
                <div class="text-xl font-semibold">{{ $profiles[$top]->title ?? $scales[$top]->name }}</div>
                @if ($second && $gap <= 2)
                    <div class="text-sm text-stone-600">{{ __('Casi empatado con el tipo :n (:name)', ['n' => $second, 'name' => $profiles[$second]->title ?? $scales[$second]->name]) }}</div>
                @elseif ($second)
                    <div class="text-sm text-stone-600">{{ __('Secundario: tipo :n (:name)', ['n' => $second, 'name' => $profiles[$second]->title ?? $scales[$second]->name]) }}</div>
                @endif
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-1">
            @foreach ($result['ranking'] as $k)
                <button type="button" wire:click="$set('viewType', '{{ $k }}')" class="rounded-full border px-2.5 py-1 text-xs font-medium {{ $viewType === $k ? 'text-white' : 'bg-white text-stone-700' }}" style="border-color: {{ $scales[$k]->color }}; {{ $viewType === $k ? 'background:'.$scales[$k]->color : '' }}">{{ $k }} · {{ $result['scores'][$k] }}</button>
            @endforeach
        </div>
    </x-ui.card>

    @if ($profile)
        <x-tests.profile :profile="$profile" :scale="$scales[$viewType]" :zone="$zone" />
    @endif

    @foreach ($rules as $rule)
        <x-ui.card :title="$rule->title"><p class="text-sm text-stone-700">{{ $rule->content }}</p></x-ui.card>
    @endforeach

    @if ($aiAvailable || $ai)
        <x-ui.card :title="__('Análisis personal con IA')">
            @if ($ai)
                <div class="prose prose-sm max-w-none prose-headings:mt-4 prose-headings:text-base">{!! \Illuminate\Support\Str::markdown($ai['text'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                <p class="mt-3 text-[11px] text-stone-400">{{ __('Generado el :d a partir de los perfiles del autor. No es un diagnóstico.', ['d' => \Carbon\Carbon::parse($ai['generated_at'])->translatedFormat('j M Y, H:i')]) }}</p>
            @elseif ($aiPending)
                <p class="animate-pulse text-sm text-stone-600">{{ __('Preparando tu análisis… suele tardar menos de un minuto.') }}</p>
            @else
                <p class="text-sm text-stone-600">{{ __('Un análisis escrito para ti a partir de tus puntuaciones y de los perfiles del autor: zona sana, sombras, dirección de crecimiento y tres pasos concretos.') }}</p>
                @if ($aiError)<p class="mt-2 text-xs text-red-600">{{ $aiError }}</p>@endif
                <x-ui.button wire:click="requestAi" class="mt-3">{{ __('Generar mi análisis') }}</x-ui.button>
            @endif
        </x-ui.card>
    @endif

    @if ($test->general_content)
        <x-ui.card><p class="text-sm text-stone-700">{{ $test->general_content }}</p></x-ui.card>
    @endif

    <div class="flex flex-wrap gap-2">
        @if ($previousTakes > 0)<x-ui.button :href="route('tests.scored.evolution', $test)" variant="secondary">{{ __('Ver evolución') }}</x-ui.button>@endif
        <x-ui.button wire:click="delete" variant="ghost" wire:confirm="{{ __('¿Borrar esta toma?') }}">{{ __('Borrar') }}</x-ui.button>
    </div>
</div>
