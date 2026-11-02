@props(['label' => null, 'name', 'type' => 'text', 'error' => null])
<label class="block">
    @if ($label)<span class="field-label">{{ $label }}</span>@endif
    <input type="{{ $type }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'field']) }}>
    @error($name)<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror
    @if ($error)<span class="mt-1.5 block text-xs text-red-600">{{ $error }}</span>@endif
</label>
