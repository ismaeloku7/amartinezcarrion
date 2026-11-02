@props(['model', 'label'])
<label class="block">
    <span class="field-label">{{ $label }}</span>
    <select wire:model="{{ $model }}" class="field">
        @foreach ([1 => __('Lunes'), 2 => __('Martes'), 3 => __('Miércoles'), 4 => __('Jueves'), 5 => __('Viernes'), 6 => __('Sábado'), 0 => __('Domingo')] as $value => $name)
            <option value="{{ $value }}">{{ $name }}</option>
        @endforeach
    </select>
</label>
