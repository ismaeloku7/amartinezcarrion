<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('Bienvenido') }}@auth, {{ auth()->user()->name }}@endauth</h1>
        <p class="text-sm text-stone-600">{{ __('Libros, prácticas y tests de Antonio Martínez.') }}</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($modules as $m)
            <x-ui.card class="flex flex-col" style="border-top: 4px solid {{ $m['color'] }}">
                <h2 class="text-lg font-semibold">{{ $m['name'] }}</h2>
                <p class="mt-1 flex-1 text-sm text-stone-600">{{ $m['description'] }}</p>
                <div class="mt-4 flex items-center justify-between">
                    @auth
                        @if ($m['access'])
                            <x-ui.button :href="$m['route']" style="--app-color: {{ $m['color'] }}">{{ __('Abrir') }}</x-ui.button>
                        @else
                            <x-ui.button :href="route('paywall', ['product' => $m['key']])" variant="secondary">{{ __('Ver planes') }}</x-ui.button>
                        @endif
                    @else
                        <x-ui.button :href="route('login')" variant="secondary">{{ __('Entrar para acceder') }}</x-ui.button>
                    @endauth
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <x-ui.card :title="__('Tests')">
        @forelse ($tests as $test)
            <a href="{{ auth()->check() ? route('tests.show', $test) : route('login') }}" class="flex items-center justify-between rounded-lg px-2 py-3 hover:bg-stone-50">
                <div>
                    <div class="font-medium">{{ $test->name }}</div>
                    <div class="text-sm text-stone-600">{{ $test->description }}</div>
                </div>
                <span class="text-stone-400">›</span>
            </a>
        @empty
            <p class="text-sm text-stone-500">{{ __('Todavía no hay tests publicados.') }}</p>
        @endforelse
    </x-ui.card>
</div>
