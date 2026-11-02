<?php

namespace App\Filament\Resources\Topics\Pages;

use App\Filament\Resources\Topics\TopicResource;
use App\Filament\Support\EditsTranslatable;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord
{
    use EditsTranslatable;

    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
