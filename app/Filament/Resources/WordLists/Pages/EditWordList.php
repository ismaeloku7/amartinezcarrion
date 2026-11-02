<?php

namespace App\Filament\Resources\WordLists\Pages;

use App\Filament\Resources\WordLists\WordListResource;
use App\Filament\Support\EditsTranslatable;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWordList extends EditRecord
{
    use EditsTranslatable;

    protected static string $resource = WordListResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
