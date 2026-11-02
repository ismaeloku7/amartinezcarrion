@props(['model', 'label'])
<label class="block">
    <span class="mb-1 block text-sm font-medium text-stone-700">{{ $label }}</span>
    <select wire:model="{{ $model }}" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm">
        @foreach ([1 => __('Lunes'), 2 => __('Martes'), 3 => __('Miércoles'), 4 => __('Jueves'), 5 => __('Viernes'), 6 => __('Sábado'), 0 => __('Domingo')] as $value => $name)
            <option value="{{ $value }}">{{ $name }}</option>
        @endforeach
    </select>
</label>
