@props(['text', 'wordUnderline' => null])
@php $u = $text->underline; $words = $text->words(); @endphp
<span>
@foreach ($words as $i => $w)<span @class([
    'underline decoration-2 underline-offset-2' => $u && $i >= $u[0] && $i <= $u[1],
    'bg-yellow-200 rounded px-0.5' => $wordUnderline !== null && $wordUnderline === $i,
])>{{ $w }}</span>{{ $loop->last ? '' : ' ' }}@endforeach
@if ($text->note_type)<span class="ml-1 rounded bg-violet-100 px-1 text-xs text-violet-800">{{ strtoupper($text->note_type) }}</span>@endif
</span>
