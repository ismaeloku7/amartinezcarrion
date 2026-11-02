@props(['label' => null, 'name', 'type' => 'text', 'error' => null])
<label class="block">
    @if ($label)<span class="mb-1 block text-sm font-medium text-stone-700">{{ $label }}</span>@endif
    <input type="{{ $type }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-[color:var(--app-color)] focus:outline-none focus:ring-1 focus:ring-[color:var(--app-color)]']) }}>
    @error($name)<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
    @if ($error)<span class="mt-1 block text-xs text-red-600">{{ $error }}</span>@endif
</label>
