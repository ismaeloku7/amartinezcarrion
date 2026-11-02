<?php

namespace App\Filament\Resources\WordLists\Pages;

use App\Filament\Resources\WordLists\WordListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWordLists extends ListRecords
{
    protected static string $resource = WordListResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
