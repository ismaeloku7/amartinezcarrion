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
