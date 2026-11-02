@props(['title' => null, 'appKey' => 'portal', 'back' => null])
@php
    $apps = [
        'portal' => ['name' => config('app.name'), 'color' => '#1f3a5f', 'home' => route('home')],
        'mdp' => ['name' => __('Devocional'), 'color' => '#7a1f1f', 'home' => route('mdp.today')],
        'cards' => ['name' => __('Fichas'), 'color' => '#2b5d8c', 'home' => route('cards.today')],
    ];
    $current = $apps[$appKey] ?? $apps['portal'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $current['color'] }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $current['name'] }}">
    <meta name="push-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <meta name="app-context" content="{{ $appKey }}">
    <title>{{ $title ? $title.' · ' : '' }}{{ $current['name'] }}</title>
    <link rel="manifest" href="{{ route('pwa.manifest', ['app' => $appKey]) }}">
    <link rel="apple-touch-icon" href="{{ asset("images/icons/$appKey-192.png") }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-stone-50 text-stone-900 antialiased" style="--app-color: {{ $current['color'] }}">
    <header class="sticky top-0 z-20 text-white shadow" style="background: var(--app-color)">
        <div class="mx-auto flex max-w-3xl items-center gap-3 px-4 py-3">
            @if ($back)
                <a href="{{ $back }}" class="rounded p-1 hover:bg-white/10" aria-label="{{ __('Volver') }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif
            <a href="{{ $current['home'] }}" class="font-semibold tracking-tight">{{ $current['name'] }}</a>
            <div class="ml-auto flex items-center gap-2 text-sm">
                @auth
                    <button type="button" id="push-bell" data-app="{{ $appKey }}" class="relative rounded p-1.5 hover:bg-white/10" aria-label="{{ __('Notificaciones') }}" title="{{ __('Activar notificaciones') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span id="push-bell-dot" class="absolute right-1 top-1 hidden h-2 w-2 rounded-full bg-emerald-400"></span>
                    </button>
                    @if ($appKey !== 'portal')
                        <a href="{{ route('home') }}" class="rounded px-2 py-1 hover:bg-white/10">{{ __('Portal') }}</a>
                    @endif
                    <a href="{{ route('account') }}" class="rounded px-2 py-1 hover:bg-white/10">{{ __('Cuenta') }}</a>
                @else
                    <a href="{{ route('login') }}" class="rounded px-2 py-1 hover:bg-white/10">{{ __('Entrar') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-3xl px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($title)
            <h1 class="mb-4 text-2xl font-semibold tracking-tight">{{ $title }}</h1>
        @endif
        {{ $slot }}
    </main>

    <div id="pwa-install" class="fixed inset-x-0 bottom-0 z-30 hidden px-4 pb-4" data-app="{{ $appKey }}">
        <div class="mx-auto flex max-w-3xl items-center gap-3 rounded-xl border border-stone-200 bg-white p-3 shadow-lg">
            <img src="{{ asset("images/icons/$appKey-192.png") }}" alt="" class="h-10 w-10 rounded-lg">
            <div class="flex-1 text-sm">
                <div class="font-medium">{{ __('Instala :app en tu móvil', ['app' => $current['name']]) }}</div>
                <div id="pwa-install-hint" class="text-xs text-stone-600">{{ __('Tendrás un icono en la pantalla de inicio y recibirás los recordatorios.') }}</div>
            </div>
            <x-ui.button type="button" id="pwa-install-btn" class="shrink-0">{{ __('Instalar') }}</x-ui.button>
            <button type="button" id="pwa-install-close" class="shrink-0 p-1 text-stone-400 hover:text-stone-700" aria-label="{{ __('Cerrar') }}">✕</button>
        </div>
    </div>

    @if ($appKey !== 'portal' && auth()->check())
        <nav class="sticky bottom-0 z-20 border-t border-stone-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-3xl justify-around text-xs">
                @foreach (($appKey === 'mdp' ? [
                    ['mdp.today', __('Hoy')], ['mdp.week', __('Semana')], ['mdp.history', __('Libreta')], ['mdp.order', __('Lectura')], ['mdp.settings', __('Ajustes')],
                ] : [
                    ['cards.today', __('Hoy')], ['cards.box', __('Caja')], ['cards.tracking', __('Planilla')], ['cards.progress', __('Progreso')], ['cards.settings', __('Ajustes')],
                ]) as [$routeName, $label])
                    <a href="{{ route($routeName) }}" @class(['flex flex-1 flex-col items-center py-2', 'font-semibold text-[color:var(--app-color)]' => request()->routeIs($routeName), 'text-stone-500' => ! request()->routeIs($routeName)])>{{ $label }}</a>
                @endforeach
            </div>
        </nav>
    @endif
</body>
</html>
