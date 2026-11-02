@props(['title' => null, 'appKey' => 'portal', 'back' => null, 'plain' => false])
@php
    $apps = [
        'portal' => ['name' => config('app.name'), 'color' => '#1f3a5f', 'home' => route('home')],
        'mdp' => ['name' => __('Devocional'), 'color' => '#7a1f1f', 'home' => route('mdp.today')],
        'cards' => ['name' => __('Fichas'), 'color' => '#2b5d8c', 'home' => route('cards.today')],
    ];
    $current = $apps[$appKey] ?? $apps['portal'];

    // Pestañas de cada app: [ruta, texto, icono, rutas que también la marcan como activa].
    $tabs = [
        'portal' => [
            ['home', __('Inicio'), 'home', ['home', 'paywall']],
            ['tests.index', __('Tests'), 'sparkles', ['tests.*']],
            ['account', __('Cuenta'), 'user', ['account']],
        ],
        'mdp' => [
            ['mdp.today', __('Hoy'), 'sun', ['mdp.today', 'mdp.devotional', 'mdp.rest']],
            ['mdp.week', __('Semana'), 'calendar', ['mdp.week', 'mdp.month', 'mdp.year']],
            ['mdp.history', __('Libreta'), 'book', ['mdp.history']],
            ['mdp.order', __('Lectura'), 'list', ['mdp.order']],
            ['mdp.settings', __('Ajustes'), 'sliders', ['mdp.settings']],
        ],
        'cards' => [
            ['cards.today', __('Hoy'), 'sun', ['cards.today', 'cards.day', 'cards.review']],
            ['cards.box', __('Caja'), 'box', ['cards.box', 'cards.show']],
            ['cards.tracking', __('Planilla'), 'calendar', ['cards.tracking']],
            ['cards.progress', __('Progreso'), 'chart', ['cards.progress']],
            ['cards.settings', __('Ajustes'), 'sliders', ['cards.settings']],
        ],
    ][$appKey] ?? [];
    $showTabs = ! $plain && auth()->check() && $tabs !== [];
    $initial = auth()->check() ? mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-canvas">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f5f6fa">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $current['name'] }}">
    <meta name="push-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <meta name="app-context" content="{{ $appKey }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ $current['name'] }}</title>
    <link rel="manifest" href="{{ route('pwa.manifest', ['app' => $appKey]) }}">
    <link rel="apple-touch-icon" href="{{ asset("images/icons/$appKey-192.png") }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-canvas font-sans text-ink antialiased" style="--app-color: {{ $current['color'] }}; --tabbar: {{ $showTabs ? '4.25rem' : '0px' }}">
    <header class="pt-safe sticky top-0 z-30 bg-canvas/85 backdrop-blur-md">
        <div class="mx-auto flex h-14 max-w-2xl items-center gap-2 px-4">
            @if ($back)
                <a href="{{ $back }}" class="-ml-1 grid h-10 w-10 place-items-center rounded-full bg-white text-ink shadow-soft active:scale-95" aria-label="{{ __('Volver') }}">
                    <x-ui.icon name="chevron-left" class="h-5 w-5" />
                </a>
            @else
                <a href="{{ $current['home'] }}" class="flex items-center gap-2.5">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-app text-sm font-bold text-white">{{ mb_substr($current['name'], 0, 1) }}</span>
                    <span class="text-[0.9375rem] font-semibold tracking-tight">{{ $current['name'] }}</span>
                </a>
            @endif

            @if ($back && $title)
                <span class="min-w-0 flex-1 truncate text-center text-[0.9375rem] font-semibold md:hidden">{{ $title }}</span>
            @endif

            @if ($showTabs)
                <nav class="ml-6 hidden items-center gap-1 md:flex" aria-label="{{ __('Secciones') }}">
                    @foreach ($tabs as [$routeName, $label, $icon, $active])
                        <a href="{{ route($routeName) }}" @class(['rounded-full px-3 py-1.5 text-sm font-medium transition', 'bg-app/10 text-app' => request()->routeIs(...$active), 'text-stone-500 hover:text-ink' => ! request()->routeIs(...$active)])>{{ $label }}</a>
                    @endforeach
                </nav>
            @endif

            <div class="ml-auto flex items-center gap-2">
                @auth
                    <button type="button" id="push-bell" data-app="{{ $appKey }}" class="relative grid h-10 w-10 place-items-center rounded-full bg-white text-stone-600 shadow-soft active:scale-95" aria-label="{{ __('Notificaciones') }}" title="{{ __('Activar notificaciones') }}">
                        <x-ui.icon name="bell" class="h-5 w-5" />
                        <span id="push-bell-dot" class="absolute right-2 top-2 hidden h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                    </button>
                    @if ($appKey !== 'portal')
                        <a href="{{ route('home') }}" class="grid h-10 w-10 place-items-center rounded-full bg-white text-stone-600 shadow-soft active:scale-95" aria-label="{{ __('Portal') }}" title="{{ __('Portal') }}">
                            <x-ui.icon name="grid" class="h-5 w-5" />
                        </a>
                    @endif
                    @unless ($appKey === 'portal' && $showTabs)
                        <a href="{{ route('account') }}" class="grid h-10 w-10 place-items-center rounded-full bg-app/10 text-sm font-semibold text-app active:scale-95" aria-label="{{ __('Cuenta') }}" title="{{ __('Cuenta') }}">{{ $initial }}</a>
                    @endunless
                @else
                    @unless ($plain)
                        <x-ui.button :href="route('login')" variant="soft" size="sm">{{ __('Entrar') }}</x-ui.button>
                    @endunless
                @endauth
            </div>
        </div>
    </header>

    <main @class(['mx-auto w-full max-w-2xl px-4 pt-2', 'pb-tabbar md:pb-12' => $showTabs, 'pb-12' => ! $showTabs])>
        @if (session('status'))
            <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
        @endif
        @if ($title && ! $plain)
            <h1 @class(['mb-5 text-[1.75rem] font-bold leading-tight tracking-tight', 'hidden md:block' => (bool) $back])>{{ $title }}</h1>
        @endif
        {{ $slot }}
    </main>

    {{-- Aviso de instalación PWA (resources/js/pwa.js). Queda encima de la barra de pestañas. --}}
    <div id="pwa-install" class="fixed inset-x-0 bottom-[calc(var(--tabbar)+env(safe-area-inset-bottom))] z-40 hidden px-3 pb-3 md:bottom-0" data-app="{{ $appKey }}">
        <div class="mx-auto flex max-w-2xl items-center gap-3 rounded-3xl bg-white p-3 shadow-float ring-1 ring-stone-200/60">
            <img src="{{ asset("images/icons/$appKey-192.png") }}" alt="" class="h-11 w-11 rounded-xl">
            <div class="min-w-0 flex-1 text-sm">
                <div class="font-semibold leading-snug">{{ __('Instala :app en tu móvil', ['app' => $current['name']]) }}</div>
                <div id="pwa-install-hint" class="text-xs leading-snug text-muted">{{ __('Tendrás un icono en la pantalla de inicio y recibirás los recordatorios.') }}</div>
            </div>
            <x-ui.button type="button" id="pwa-install-btn" size="sm" class="shrink-0">{{ __('Instalar') }}</x-ui.button>
            <button type="button" id="pwa-install-close" class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-stone-400 hover:bg-fill hover:text-stone-700" aria-label="{{ __('Cerrar') }}">✕</button>
        </div>
    </div>

    @if ($showTabs)
        <nav class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-stone-200/70 bg-white/95 backdrop-blur-md md:hidden" aria-label="{{ __('Secciones') }}">
            <div class="mx-auto flex max-w-2xl px-2">
                @foreach ($tabs as [$routeName, $label, $icon, $active])
                    @php $on = request()->routeIs(...$active); @endphp
                    <a href="{{ route($routeName) }}" @class(['group flex flex-1 flex-col items-center gap-0.5 pb-2 pt-2 text-[0.6875rem] font-medium transition', 'text-app' => $on, 'text-stone-400' => ! $on]) @if ($on) aria-current="page" @endif>
                        <span @class(['grid h-8 w-14 place-items-center rounded-full transition', 'bg-app/10' => $on])>
                            <x-ui.icon :name="$icon" class="h-[1.375rem] w-[1.375rem]" />
                        </span>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </nav>
    @endif
</body>
</html>
