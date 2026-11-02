<?php

namespace App\Filament\Resources\Cards\Pages;

use App\Filament\Resources\Cards\CardResource;
use App\Filament\Support\EditsTranslatable;
use Filament\Resources\Pages\EditRecord;

class EditCard extends EditRecord
{
    use EditsTranslatable;

    protected static string $resource = CardResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['image_path'] ?? null) && ! str_starts_with($data['image_path'], 'images/')) {
            $data['image_path'] = 'storage/'.$data['image_path'];
        }

        return $data;
    }
}
