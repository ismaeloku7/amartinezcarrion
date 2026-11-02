<div class="space-y-4">
    @if ($topic->description)<p class="text-stone-600">{{ $topic->description }}</p>@endif
    @if ($children->isNotEmpty())
        <div class="flex flex-wrap gap-2">@foreach ($children as $c)<a href="{{ route('contents.topic', $c) }}" class="rounded-full border px-3 py-1 text-sm" style="border-color: {{ $c->color }}; color: {{ $c->color }}">{{ $c->name }}</a>@endforeach</div>
    @endif
    <div class="space-y-3">
        @forelse ($contents as $c)<x-contents.card :content="$c" />@empty<x-ui.alert>{{ __('Todavía no hay materiales en esta temática.') }}</x-ui.alert>@endforelse
    </div>
</div>
