<div class="space-y-4" x-data="{ seconds: {{ (int) ($day->duration_seconds ?? 0) }}, running: false, timer: null,
    start() { if (this.running) return; this.running = true; this.timer = setInterval(() => { this.seconds++; if (this.seconds === 900 || this.seconds === 1200) { try { navigator.vibrate && navigator.vibrate(200) } catch(e) {} } }, 1000) },
    stop() { this.running = false; clearInterval(this.timer); $wire.saveDuration(this.seconds) },
    fmt() { const m = Math.floor(this.seconds / 60), s = this.seconds % 60; return `${m}:${s.toString().padStart(2, '0')}` } }">

    {{-- progress --}}
    <ol class="flex gap-1">
        @foreach ($steps as $s)
            <li class="h-1.5 flex-1 rounded-full {{ array_search($s, $steps) <= array_search($step, $steps) ? 'bg-red-700' : 'bg-stone-200' }}"></li>
        @endforeach
    </ol>
    <div class="text-xs text-stone-500">{{ $day->date->translatedFormat('l, j \d\e F') }} · {{ __('Fase :n', ['n' => $phase]) }}</div>

    @if ($step === 'prayer')
        <x-ui.card :title="__('Oración inicial')">
            <p class="text-stone-700">{{ __('Antes de abrir las Escrituras, pide al Señor que dirija tu lectura y te muestre el mensaje que necesitas hoy.') }}</p>
            @if ($phase === 2)<p class="mt-2 text-sm text-stone-600">{{ __('Una actitud de gratitud y alabanza prepara la mente y la hace más receptiva.') }}</p>@endif
        </x-ui.card>
    @endif

    @if ($step === 'reading')
        <x-ui.card :title="__('Tiempo de lectura')">
            <p class="text-sm text-stone-600">{{ __('De 15 a 20 minutos, con lectura lenta y pausada, fijándote en los verbos. No importa cuánto leas, sino que llegue a tu interior.') }}</p>
            <div class="my-6 text-center">
                <div class="font-mono text-5xl tabular-nums" x-text="fmt()"></div>
                <div class="mt-3 flex justify-center gap-2">
                    <x-ui.button type="button" x-show="!running" x-on:click="start()">{{ __('Iniciar') }}</x-ui.button>
                    <x-ui.button type="button" x-show="running" x-on:click="stop()" variant="secondary">{{ __('Pausar') }}</x-ui.button>
                </div>
            </div>
            @if ($phase === 2)
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Inicio de lectura') }}</span><input wire:model="readingFrom" placeholder="Jn 1:1" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                    <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Fin de lectura') }}</span><input wire:model="readingTo" placeholder="Jn 1:18" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                </div>
            @endif
            <p class="mt-3 text-xs text-stone-500">{{ __('Puedes ir anotando los textos mientras lees: pasa al siguiente paso cuando quieras, el tiempo queda guardado.') }}</p>
        </x-ui.card>
    @endif

    @if ($step === 'texts')
        <x-ui.card :title="__('Anotación de los textos')">
            <p class="text-sm text-stone-600">{{ __('Copia literalmente los textos que conectan con tu experiencia. Frases de 8 a 10 palabras, nunca más de :max.', ['max' => $maxWords]) }}</p>
            <form wire:submit="addText" class="mt-3 space-y-2" x-data="{ t: @entangle('newText') }">
                <textarea wire:model="newText" x-model="t" rows="2" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-base" placeholder="{{ __('Escribe el texto literal…') }}"></textarea>
                <div class="flex items-center justify-between text-xs">
                    <span x-data x-text="(() => { const n = t.trim() ? t.trim().split(/\s+/).length : 0; return n + ' {{ __('palabras') }}' + (n > {{ $warnWords }} ? ' · {{ __('intenta acortarlo') }}' : '') })()" :class="t.trim().split(/\s+/).filter(Boolean).length > {{ $maxWords }} ? 'text-red-600' : (t.trim().split(/\s+/).filter(Boolean).length > {{ $warnWords }} ? 'text-amber-600' : 'text-stone-500')"></span>
                    @if ($isFull && $phase === 2)
                        <select wire:model="noteType" class="rounded border border-stone-300 px-2 py-1 text-xs">
                            <option value="">{{ __('Sin anotación') }}</option>
                            <option value="rf">Rf · {{ __('Reflexión') }}</option>
                            <option value="pd">Pd · {{ __('Predicación') }}</option>
                            <option value="il">Il · {{ __('Ilustración') }}</option>
                        </select>
                    @endif
                </div>
                @error('newText')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <x-ui.button type="submit" variant="secondary" x-bind:disabled="t.trim().split(/\s+/).filter(Boolean).length > {{ $maxWords }} || !t.trim()">{{ __('Añadir texto') }}</x-ui.button>
            </form>
            <ul class="mt-4 divide-y divide-stone-100">
                @foreach ($texts as $t)
                    <li class="flex items-start justify-between gap-3 py-2 text-sm">
                        <span><x-mdp.text :text="$t" /> <span class="text-xs text-stone-400">({{ $t->word_count }})</span></span>
                        <button type="button" wire:click="removeText({{ $t->id }})" class="text-xs text-stone-400 hover:text-red-600">{{ __('Quitar') }}</button>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @if ($step === 'underline')
        <x-ui.card :title="__('Subrayado')">
            <p class="text-sm text-stone-600">{{ __('Toca la primera y la última palabra de la parte más significativa de cada texto.') }}</p>
            <ul class="mt-3 space-y-3">
                @foreach ($texts as $t)
                    <li class="rounded-lg border border-stone-200 p-2 {{ $underliningText === $t->id ? 'ring-2 ring-red-300' : '' }}">
                        <p class="leading-relaxed">
                            @foreach ($t->words() as $i => $w)
                                <button type="button" wire:click="tapWord({{ $t->id }}, {{ $i }})" @class(['rounded px-0.5', 'underline decoration-2 underline-offset-2' => $t->underline && $i >= $t->underline[0] && $i <= $t->underline[1], 'bg-red-100' => $underliningText === $t->id && $underlineStart === $i])>{{ $w }}</button>
                            @endforeach
                        </p>
                        @if ($t->underline)<button type="button" wire:click="clearUnderline({{ $t->id }})" class="mt-1 text-xs text-stone-400">{{ __('Borrar subrayado') }}</button>@endif
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @if ($step === 'select')
        <x-ui.card :title="__('Texto del día')">
            <p class="text-sm text-stone-600">{{ __('Elige el texto que sientas más necesario para ti en estos momentos. Es el mensaje del Señor para hoy.') }}</p>
            @error('select')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            <ul class="mt-3 space-y-2">
                @foreach ($texts as $t)
                    <li>
                        <button type="button" wire:click="selectText({{ $t->id }})" class="w-full rounded-lg border p-3 text-left {{ $t->is_day_text ? 'border-red-700 bg-red-50 text-red-900' : 'border-stone-200 hover:bg-stone-50' }}">
                            <x-mdp.text :text="$t" />
                        </button>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @if ($step === 'visualize')
        <x-ui.card :title="__('Visualización')">
            @if ($dayText)<p class="my-4 text-center text-2xl font-medium leading-snug text-red-800">{{ $dayText->body }}</p>@endif
            <p class="text-sm text-stone-600">{{ __('Piensa en la idea que te transmite este texto. Concéntrate en el mensaje y visualízate transformado por él: qué se produciría en ti si lo incorporaras plenamente.') }}</p>
        </x-ui.card>
    @endif

    @if ($step === 'written_prayer')
        <x-ui.card :title="__('Oración escrita')">
            @if ($dayText)<p class="mb-3 rounded bg-red-50 p-2 text-sm text-red-900">{{ $dayText->body }}</p>@endif
            <p class="text-sm text-stone-600">{{ __('Pide a Dios que te ayude con su Espíritu para que el mensaje se haga realidad en tu vida, comenzando por «este día».') }}</p>
            <textarea wire:model="prayer" rows="5" class="mt-3 w-full rounded-lg border border-stone-300 px-3 py-2 text-base" placeholder="{{ __('Señor, en este día…') }}"></textarea>
            @error('prayer')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            @if ($phase === 2)
                <label class="mt-3 block"><span class="mb-1 block text-sm font-medium">{{ __('Frase a subrayar de la oración') }}</span>
                    <input wire:model="prayerUnderline" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm" placeholder="{{ __('Copia aquí la frase más significativa') }}"></label>
            @endif
            <p class="mt-2 text-xs text-stone-500">{{ __('La oración es privada y se guarda cifrada.') }}</p>
        </x-ui.card>
    @endif

    @if ($step === 'done')
        <x-ui.card :title="__('Devocional completado')">
            @if ($dayText)
                <div class="text-xs uppercase tracking-wide text-stone-500">{{ __('Texto del día') }}</div>
                <p class="mt-1 text-xl font-medium text-red-800">{{ $dayText->body }}</p>
            @endif
            <p class="mt-3 text-sm text-stone-600">{{ __('Repásalo tres veces a lo largo del día. Antes de mirarlo, intenta recordarlo.') }}</p>
            <div class="mt-4 flex gap-2">
                <x-ui.button :href="route('mdp.today')">{{ __('Ir a hoy') }}</x-ui.button>
                <x-ui.button :href="route('mdp.week')" variant="secondary">{{ __('Ficha de la semana') }}</x-ui.button>
            </div>
        </x-ui.card>
    @endif

    @if ($step !== 'done')
        <div class="flex items-center justify-between">
            <x-ui.button type="button" wire:click="back" variant="ghost" :disabled="$step === $steps[0]">{{ __('Atrás') }}</x-ui.button>
            <div class="flex gap-2">
                @if (in_array($step, ['texts', 'select']) && $texts->isNotEmpty())
                    <x-ui.button type="button" wire:click="finishMinimum" variant="ghost" wire:confirm="{{ __('¿Terminar hoy con el mínimo? Cuenta como día hecho.') }}">{{ __('Hoy solo el mínimo') }}</x-ui.button>
                @endif
                <x-ui.button type="button" wire:click="next" x-on:click="running && stop()">{{ __('Siguiente') }}</x-ui.button>
            </div>
        </div>
    @endif
</div>
