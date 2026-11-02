<?php

namespace App\Filament\Support;

trait EditsTranslatable
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return Translatable::fill($data, $this->getRecord());
    }
}
