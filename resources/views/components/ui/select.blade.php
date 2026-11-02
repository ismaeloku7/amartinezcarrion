@props(['label' => null, 'name'])
<label class="block">
    @if ($label)<span class="mb-1 block text-sm font-medium text-stone-700">{{ $label }}</span>@endif
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-[color:var(--app-color)] focus:outline-none']) }}>{{ $slot }}</select>
    @error($name)<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
</label>
