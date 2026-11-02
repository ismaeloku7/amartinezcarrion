@props(['test'])
<div class="cta my-6 flex flex-wrap items-center gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex-1">
        <div class="text-xs uppercase tracking-wide text-stone-500">{{ __('Test') }}</div>
        <div class="text-lg font-semibold">{{ $test->name }}</div>
        @if ($test->description)<p class="mt-1 text-sm text-stone-600">{{ $test->description }}</p>@endif
    </div>
    <x-ui.button :href="auth()->check() ? route('tests.show', $test) : route('login')" style="--app-color: var(--article-color)">{{ __('Hacer el test') }}</x-ui.button>
</div>
