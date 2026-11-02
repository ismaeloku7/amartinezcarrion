@props(['label' => null, 'name'])
<label class="block">
    @if ($label)<span class="field-label">{{ $label }}</span>@endif
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'field']) }}>{{ $slot }}</select>
    @error($name)<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror
</label>
