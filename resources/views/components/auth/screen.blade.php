@props(['title', 'subtitle' => null])
{{-- Pantalla de acceso: marca centrada, título grande y formulario a todo el ancho. --}}
<x-layouts.app :title="$title" plain>
    <div class="mx-auto flex min-h-[calc(100dvh-7rem)] max-w-sm flex-col justify-center py-6">
        <div class="mb-8 text-center">
            <span class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-[1.375rem] bg-app text-2xl font-bold text-white shadow-float">{{ mb_substr(config('app.name'), 0, 1) }}</span>
            <h1 class="text-[1.625rem] font-bold leading-tight tracking-tight">{{ $title }}</h1>
            @if ($subtitle)<p class="mx-auto mt-2 max-w-xs text-sm leading-relaxed text-muted">{{ $subtitle }}</p>@endif
        </div>
        {{ $slot }}
        @isset($footer)
            <p class="mt-8 text-center text-sm text-muted">{{ $footer }}</p>
        @endisset
    </div>
</x-layouts.app>
