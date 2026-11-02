@props(['content'])
<a href="{{ route('contents.show', $content) }}" class="flex gap-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm transition hover:shadow-md">
    <div class="h-16 w-16 shrink-0 rounded-lg" style="background: linear-gradient(135deg, {{ $content->color() }}, {{ $content->color() }}99)"></div>
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-wide text-stone-500">
            @if ($content->topic)<span style="color: {{ $content->topic->color }}">{{ $content->topic->name }}</span>@endif
            <span>{{ $content->type === 'course' ? __('Curso') : __('Artículo') }}</span>
            <span>{{ __(':n min', ['n' => $content->readingMinutes()]) }}</span>
            @if ($content->product_id)<span class="rounded bg-amber-100 px-1 text-amber-800">{{ __('Premium') }}</span>@endif
        </div>
        <div class="mt-1 font-semibold leading-snug">{{ $content->title }}</div>
        @if ($content->excerpt)<p class="mt-1 line-clamp-2 text-sm text-stone-600">{{ $content->excerpt }}</p>@endif
    </div>
</a>
