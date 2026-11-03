<div class="space-y-4" x-data="{ seconds: {{ (int) ($day->duration_seconds ?? 0) }}, running: false, timer: null,
    storageKey: 'mdp-timer-{{ $day->id }}', base: 0, startedAt: null, lastSaved: 0,
    init() {
        /* A run interrupted by a lock, a tab switch or a reload resumes from the stored start time. */
        try {
            const saved = JSON.parse(localStorage.getItem(this.storageKey) || 'null');
            if (saved && saved.startedAt) { this.base = saved.base; this.startedAt = saved.startedAt; this.resume() }
        } catch (e) {}
        const catchUp = () => { if (this.running && ! document.hidden) this.sync(true) };
        document.addEventListener('visibilitychange', catchUp);
        window.addEventListener('pageshow', catchUp);
        window.addEventListener('focus', catchUp);
    },
    start() {
        if (this.running) return;
        this.base = this.seconds; this.startedAt = Date.now();
        try { localStorage.setItem(this.storageKey, JSON.stringify({ base: this.base, startedAt: this.startedAt })) } catch (e) {}
        this.resume();
    },
    resume() { this.running = true; this.sync(); this.timer = setInterval(() => this.sync(), 1000) },
    stop() {
        if (! this.running) return;
        this.sync(); this.running = false; clearInterval(this.timer); this.startedAt = null;
        try { localStorage.removeItem(this.storageKey) } catch (e) {}
        $wire.saveDuration(this.seconds);
    },
    /* Elapsed time comes from the clock, not from counting ticks, so it keeps running while the phone is locked. */
    sync(force = false) {
        if (! this.startedAt) return;
        const before = this.seconds;
        this.seconds = this.base + Math.floor((Date.now() - this.startedAt) / 1000);
        if ([900, 1200].some(mark => before < mark && this.seconds >= mark)) { try { navigator.vibrate && navigator.vibrate(200) } catch (e) {} }
        if (force || this.seconds - this.lastSaved >= 30) { this.lastSaved = this.seconds; $wire.saveDuration(this.seconds) }
    },
    fmt() { const m = Math.floor(this.seconds / 60), s = this.seconds % 60; return `${m}:${s.toString().padStart(2, '0')}` } }">

    @if ($step === 'page')
        <x-ui.card :eyebrow="$day->date->translatedFormat('l, j \d\e F Y')" :title="__('Página del día')">
            @if (session('status'))<x-ui.alert type="success" class="mb-3">{{ session('status') }}</x-ui.alert>@endif
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @if ($day->isCompleted())
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700">{{ $day->status === 'minimum' ? __('Hecho (mínimo)') : __('Hecho') }}</span>
                @else
                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700">{{ __('Sin terminar') }}</span>
                @endif
                @if ($day->duration_seconds)<span class="text-muted">{{ __(':m min de lectura', ['m' => intdiv($day->duration_seconds, 60)]) }}</span>@endif
            </div>

            <x-mdp.reading-range />

            <div class="mt-5">
                <div class="field-label">{{ __('Textos anotados') }}</div>
                <p class="mb-2 text-xs text-muted">{{ __('Toca un texto para marcarlo como texto del día.') }}</p>
                <ul class="space-y-1.5">
                    @forelse ($texts as $t)
                        <li class="flex items-start justify-between gap-3 rounded-2xl border px-3 py-2 text-[0.9375rem] {{ $t->is_day_text ? 'border-red-700 bg-red-50 text-red-900' : 'border-stone-200 bg-white' }}" wire:key="page-text-{{ $t->id }}">
                            <button type="button" wire:click="selectText({{ $t->id }})" class="flex-1 text-left"><x-mdp.text :text="$t" /></button>
                            <button type="button" wire:click="removeText({{ $t->id }})" wire:confirm="{{ __('¿Quitar este texto?') }}" class="text-xs text-stone-400 hover:text-red-600">{{ __('Quitar') }}</button>
                        </li>
                    @empty
                        <li class="text-sm text-muted">{{ __('Todavía no hay textos en esta página.') }}</li>
                    @endforelse
                </ul>
                <form wire:submit="addText" class="mt-2 flex gap-2">
                    <input wire:model="newText" class="field flex-1" placeholder="{{ __('Añadir un texto literal (máximo :max palabras)', ['max' => $maxWords]) }}" autocomplete="off">
                    <x-ui.button type="submit" variant="secondary" size="sm" class="shrink-0">{{ __('Añadir') }}</x-ui.button>
                </form>
                @error('newText')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            @if ($isFull)
                <div class="mt-5">
                    <label class="block"><span class="field-label">{{ __('Oración escrita') }}</span><textarea wire:model="prayer" rows="4" class="field" placeholder="{{ __('Señor, en este día…') }}"></textarea></label>
                    @error('prayer')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    <label class="mt-2 block"><span class="field-label">{{ __('Frase a subrayar de la oración') }}</span><input wire:model="prayerUnderline" class="field"></label>
                </div>
            @endif

            <div class="mt-5 flex flex-col gap-2">
                <x-ui.button wire:click="savePage" block>{{ __('Guardar') }}</x-ui.button>
                @unless ($day->isCompleted())
                    <x-ui.button wire:click="markDone" variant="soft" block>{{ __('Marcar como hecho') }}</x-ui.button>
                @endunless
                @unless ($isPast)
                    <x-ui.button wire:click="startSteps" variant="ghost" block>{{ __('Hacer el devocional paso a paso') }}</x-ui.button>
                @endunless
                <x-ui.button :href="route('mdp.history')" variant="ghost" block>{{ __('Volver a la libreta') }}</x-ui.button>
            </div>
        </x-ui.card>
    @else
    {{-- progress --}}
    <div class="sticky top-[calc(3.5rem+env(safe-area-inset-top))] z-10 -mx-4 bg-canvas/90 px-4 pb-3 pt-1 backdrop-blur">
        <ol class="flex gap-1">
            @foreach ($steps as $s)
                <li class="h-1.5 flex-1 rounded-full transition {{ array_search($s, $steps) <= array_search($step, $steps) ? 'bg-app' : 'bg-stone-200' }}"></li>
            @endforeach
        </ol>
        <div class="mt-2 flex items-center justify-between text-xs text-muted">
            <span class="first-letter:uppercase">{{ $day->date->translatedFormat('l, j \d\e F') }}</span>
            @if (! in_array($step, ['reading', 'done']))
                {{-- The reading timer keeps running while texts are written down. --}}
                <button type="button" x-show="running || seconds > 0" x-cloak x-on:click="running ? stop() : start()" class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 font-mono text-[13px] tabular-nums text-ink shadow-soft" :class="running ? 'ring-1 ring-app/40' : ''" :title="running ? '{{ __('Pausar') }}' : '{{ __('Reanudar') }}'">
                    <span class="h-2 w-2 rounded-full" :class="running ? 'animate-pulse bg-app' : 'bg-stone-300'"></span><span x-text="fmt()"></span>
                </button>
            @endif
            <span>{{ array_search($step, $steps) + 1 }}/{{ count($steps) }} · {{ __('Fase :n', ['n' => $phase]) }}</span>
        </div>
    </div>

    @if ($step === 'prayer')
        <x-ui.card :title="__('Oración inicial')">
            <p class="text-stone-700">{{ __('Antes de abrir las Escrituras, pide al Señor que dirija tu lectura y te muestre el mensaje que necesitas hoy.') }}</p>
            @if ($phase === 2)<p class="mt-2 text-sm text-stone-600">{{ __('Una actitud de gratitud y alabanza prepara la mente y la hace más receptiva.') }}</p>@endif
        </x-ui.card>
    @endif

    @if ($step === 'reading')
        <x-ui.card :title="__('Tiempo de lectura')">
            <p class="text-sm text-stone-600">{{ __('De 15 a 20 minutos, con lectura lenta y pausada, fijándote en los verbos. No importa cuánto leas, sino que llegue a tu interior.') }}</p>
            {{-- Reloj circular: el anillo se completa a los 20 minutos. --}}
            <div class="my-6 flex flex-col items-center">
                <div class="relative grid h-52 w-52 place-items-center">
                    <svg class="absolute inset-0 -rotate-90" viewBox="0 0 100 100" aria-hidden="true">
                        <circle cx="50" cy="50" r="45" fill="none" stroke-width="6" class="stroke-stone-100" />
                        <circle cx="50" cy="50" r="45" fill="none" stroke-width="6" stroke-linecap="round" class="stroke-app transition-all duration-1000" pathLength="100" stroke-dasharray="100" :stroke-dashoffset="100 - Math.min(100, seconds / 12)" />
                    </svg>
                    <div class="text-center">
                        <div class="text-5xl font-semibold tabular-nums tracking-tight" x-text="fmt()"></div>
                        <div class="mt-1 text-xs text-muted">{{ __('15–20 min') }}</div>
                    </div>
                </div>
                <div class="mt-5">
                    <x-ui.button type="button" x-show="!running" x-on:click="start()" class="w-40"><x-ui.icon name="play" class="h-4 w-4" />{{ __('Iniciar') }}</x-ui.button>
                    <x-ui.button type="button" x-show="running" x-on:click="stop()" variant="soft" class="w-40"><x-ui.icon name="pause" class="h-4 w-4" />{{ __('Pausar') }}</x-ui.button>
                </div>
            </div>
            <x-mdp.reading-range />
            <p class="mt-3 text-xs text-stone-500">{{ __('Puedes ir anotando los textos mientras lees: pasa al siguiente paso cuando quieras, el tiempo queda guardado.') }}</p>
        </x-ui.card>
    @endif

    @if ($step === 'texts')
        @unless ($isFull)
            <x-ui.card :title="__('Lectura de hoy')">
                <x-mdp.reading-range class="mt-0" />
            </x-ui.card>
        @endunless
        <x-ui.card :title="__('Anotación de los textos')">
            <p class="text-sm text-stone-600">{{ __('Copia literalmente los textos que conectan con tu experiencia. Frases de 8 a 10 palabras, nunca más de :max.', ['max' => $maxWords]) }}</p>
            <form wire:submit="addText" class="mt-3 space-y-2" x-data="{ t: @entangle('newText') }">
                <textarea wire:model="newText" x-model="t" rows="2" class="field" placeholder="{{ __('Escribe el texto literal…') }}"></textarea>
                <div class="flex items-center justify-between text-xs">
                    <span x-data x-text="(() => { const n = t.trim() ? t.trim().split(/\s+/).length : 0; return n + ' {{ __('palabras') }}' + (n > {{ $warnWords }} ? ' · {{ __('intenta acortarlo') }}' : '') })()" :class="t.trim().split(/\s+/).filter(Boolean).length > {{ $maxWords }} ? 'text-red-600' : (t.trim().split(/\s+/).filter(Boolean).length > {{ $warnWords }} ? 'text-amber-600' : 'text-stone-500')"></span>
                    @if ($isFull && $phase === 2)
                        <select wire:model="noteType" class="rounded-full border-0 bg-fill py-1.5 pl-3 pr-7 text-xs">
                            <option value="">{{ __('Sin anotación') }}</option>
                            <option value="rf">Rf · {{ __('Reflexión') }}</option>
                            <option value="pd">Pd · {{ __('Predicación') }}</option>
                            <option value="il">Il · {{ __('Ilustración') }}</option>
                        </select>
                    @endif
                </div>
                @error('newText')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <x-ui.button type="submit" variant="soft" block x-bind:disabled="t.trim().split(/\s+/).filter(Boolean).length > {{ $maxWords }} || !t.trim()">{{ __('Añadir texto') }}</x-ui.button>
            </form>
            <ul class="mt-4 divide-y divide-stone-100">
                @foreach ($texts as $t)
                    <li class="flex items-start justify-between gap-3 py-2 text-sm">
                        <span><x-mdp.text :text="$t" /> <span class="text-xs text-stone-400">({{ $t->word_count }})</span></span>
                        <button type="button" wire:click="removeText({{ $t->id }})" class="-m-2 shrink-0 p-2 text-xs text-stone-400 hover:text-red-600">{{ __('Quitar') }}</button>
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
                    <li class="rounded-2xl bg-fill p-3 {{ $underliningText === $t->id ? 'ring-2 ring-app/40' : '' }}">
                        <p class="text-lg leading-loose">
                            @foreach ($t->words() as $i => $w)
                                <button type="button" wire:click="tapWord({{ $t->id }}, {{ $i }})" @class(['rounded px-0.5', 'underline decoration-2 underline-offset-2' => $t->underline && $i >= $t->underline[0] && $i <= $t->underline[1], 'bg-app/15' => $underliningText === $t->id && $underlineStart === $i])>{{ $w }}</button>
                            @endforeach
                        </p>
                        @if ($t->underline)<button type="button" wire:click="clearUnderline({{ $t->id }})" class="mt-1 py-1 text-xs text-stone-400">{{ __('Borrar subrayado') }}</button>@endif
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
                        <button type="button" wire:click="selectText({{ $t->id }})" class="w-full rounded-2xl p-4 text-left text-[0.9375rem] leading-relaxed transition {{ $t->is_day_text ? 'bg-app text-white shadow-float' : 'bg-fill text-stone-800 active:bg-stone-200' }}">
                            <x-mdp.text :text="$t" />
                        </button>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @if ($step === 'visualize')
        <x-ui.card :title="__('Visualización')">
            @if ($dayText)<p class="my-6 text-center font-serif text-2xl font-medium leading-snug text-red-900">{{ $dayText->body }}</p>@endif
            <p class="text-sm text-stone-600">{{ __('Piensa en la idea que te transmite este texto. Concéntrate en el mensaje y visualízate transformado por él: qué se produciría en ti si lo incorporaras plenamente.') }}</p>
        </x-ui.card>
    @endif

    @if ($step === 'written_prayer')
        <x-ui.card :title="__('Oración escrita')">
            @if ($dayText)<p class="mb-4 rounded-2xl bg-app/5 p-4 font-serif text-[0.9375rem] leading-relaxed text-red-900">{{ $dayText->body }}</p>@endif
            <p class="text-sm text-stone-600">{{ __('Pide a Dios que te ayude con su Espíritu para que el mensaje se haga realidad en tu vida, comenzando por «este día».') }}</p>
            <textarea wire:model="prayer" rows="5" class="mt-3 field" placeholder="{{ __('Señor, en este día…') }}"></textarea>
            @error('prayer')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            @if ($phase === 2)
                <label class="mt-3 block"><span class="field-label">{{ __('Frase a subrayar de la oración') }}</span>
                    <input wire:model="prayerUnderline" class="field" placeholder="{{ __('Copia aquí la frase más significativa') }}"></label>
            @endif
            <p class="mt-2 text-xs text-stone-500">{{ __('La oración es privada y se guarda cifrada.') }}</p>
        </x-ui.card>
    @endif

    @if ($step === 'done')
        <x-ui.card :title="__('Devocional completado')">
            @if ($dayText)
                <div class="text-xs uppercase tracking-wide text-stone-500">{{ __('Texto del día') }}</div>
                <p class="mt-2 font-serif text-[1.375rem] font-medium leading-snug text-red-900">{{ $dayText->body }}</p>
            @endif
            <p class="mt-3 text-sm text-stone-600">{{ __('Repásalo tres veces a lo largo del día. Antes de mirarlo, intenta recordarlo.') }}</p>
            <div class="mt-5 space-y-2">
                <x-ui.button :href="route('mdp.today')" block>{{ __('Ir a hoy') }}</x-ui.button>
                <x-ui.button :href="route('mdp.week')" variant="soft" block>{{ __('Ficha de la semana') }}</x-ui.button>
                <x-ui.button wire:click="openPage" variant="ghost" block>{{ __('Revisar o corregir la página') }}</x-ui.button>
            </div>
        </x-ui.card>
    @endif

    @if ($step !== 'done')
        @if (in_array($step, ['texts', 'select']) && $texts->isNotEmpty())
            <x-ui.button type="button" wire:click="finishMinimum" x-on:click="running && stop()" variant="ghost" size="sm" block wire:confirm="{{ __('¿Terminar hoy con el mínimo? Cuenta como día hecho.') }}">{{ __('Hoy solo el mínimo') }}</x-ui.button>
        @endif
        <x-ui.action-bar class="mt-2!">
            <x-ui.button type="button" wire:click="back" variant="secondary" :disabled="$step === $steps[0]" class="w-12 shrink-0 px-0!" aria-label="{{ __('Atrás') }}"><x-ui.icon name="chevron-left" class="h-5 w-5" /></x-ui.button>
            <x-ui.button type="button" wire:click="next" x-on:click="{{ $step === 'written_prayer' || ($step === 'select' && ! $isFull) ? 'running && stop()' : '' }}" class="flex-1" size="lg">{{ __('Siguiente') }}<x-ui.icon name="arrow-right" class="h-4 w-4" /></x-ui.button>
        </x-ui.action-bar>
    @endif
    @endif
</div>
