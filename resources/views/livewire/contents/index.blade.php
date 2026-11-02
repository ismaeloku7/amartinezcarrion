<div class="space-y-6">
    <p class="text-stone-600">{{ __('Artículos y materiales de Antonio Martínez, organizados por temáticas.') }}</p>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach ($topics as $t)
            <a href="{{ route('contents.topic', $t) }}" class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-sm transition hover:shadow-md" style="background: linear-gradient(135deg, {{ $t->color }}, {{ $t->color }}cc)">
                <div class="text-lg font-semibold">{{ $t->name }}</div>
                @if ($t->description)<p class="mt-1 text-sm opacity-90">{{ $t->description }}</p>@endif
                @if ($t->children->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1">@foreach ($t->children as $c)<span class="rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ $c->name }}</span>@endforeach</div>
                @endif
            </a>
        @endforeach
    </div>

    @if ($latest->isNotEmpty())
        <section>
            <h2 class="mb-3 text-lg font-semibold">{{ __('Últimos materiales') }}</h2>
            <div class="space-y-3">
                @foreach ($latest as $c)<x-contents.card :content="$c" />@endforeach
            </div>
        </section>
    @endif
</div>
