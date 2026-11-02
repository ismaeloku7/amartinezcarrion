<div class="space-y-7">
    <div class="flex items-center gap-3">
        @auth
            <span class="grid h-12 w-12 place-items-center rounded-full bg-app/10 text-lg font-semibold text-app">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        @endauth
        <div class="min-w-0">
            <h1 class="truncate text-[1.375rem] font-bold leading-tight tracking-tight">{{ __('Hola') }}@auth, {{ auth()->user()->name }}@endauth</h1>
            <p class="text-sm text-muted">{{ __('Libros, prácticas y tests de Antonio Martínez.') }}</p>
        </div>
    </div>

    <div class="space-y-3">
        @foreach ($modules as $m)
            @php
                $href = auth()->check() ? ($m['access'] ? $m['route'] : route('paywall', ['product' => $m['key']])) : route('login');
                $cta = auth()->check() ? ($m['access'] ? __('Abrir') : __('Ver planes')) : __('Entrar para acceder');
            @endphp
            <a href="{{ $href }}" class="relative flex flex-col overflow-hidden rounded-3xl p-5 text-white shadow-float transition active:scale-[0.99]" style="background: {{ $m['color'] }}">
                {{-- Onda decorativa --}}
                <svg class="pointer-events-none absolute -right-6 bottom-0 h-28 w-64 text-white/15" viewBox="0 0 260 110" fill="none" aria-hidden="true"><path d="M0 90C40 90 50 30 95 30s50 55 95 55 45-60 70-75" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M0 110C45 110 60 60 105 60s45 40 90 40 50-35 65-50" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity=".6"/></svg>
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-white/15">
                    <x-ui.icon :name="$m['key'] === 'mdp' ? 'book' : 'box'" class="h-5 w-5" />
                </span>
                <h2 class="mt-4 text-lg font-semibold leading-snug">{{ $m['name'] }}</h2>
                <p class="mt-1 text-sm leading-relaxed text-white/75">{{ $m['description'] }}</p>
                <span class="mt-4 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/95 px-4 py-2 text-sm font-semibold" style="color: {{ $m['color'] }}">
                    @if (auth()->check() && ! $m['access'])<x-ui.icon name="lock" class="h-4 w-4" />@endif
                    {{ $cta }}
                </span>
            </a>
        @endforeach
    </div>

    <section>
        <div class="mb-3 flex items-baseline justify-between">
            <h2 class="text-[1.0625rem] font-semibold tracking-tight">{{ __('Tests') }}</h2>
            @auth<a href="{{ route('tests.index') }}" class="text-sm font-medium text-app">{{ __('Ver todos') }}</a>@endauth
        </div>
        <div class="space-y-2.5">
            @forelse ($tests as $test)
                <a href="{{ auth()->check() ? route('tests.show', $test) : route('login') }}" class="flex items-center gap-3.5 rounded-2xl bg-white p-3.5 shadow-soft transition active:scale-[0.99]">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-app/10 text-app"><x-ui.icon name="sparkles" class="h-5 w-5" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium">{{ $test->name }}</span>
                        <span class="line-clamp-2 text-sm text-muted">{{ $test->description }}</span>
                    </span>
                    <x-ui.icon name="chevron-right" class="h-5 w-5 text-stone-300" />
                </a>
            @empty
                <p class="rounded-2xl bg-white p-4 text-sm text-muted shadow-soft">{{ __('Todavía no hay tests publicados.') }}</p>
            @endforelse
        </div>
    </section>
</div>
