<?php

namespace App\Filament\Resources\WordLists\Pages;

use App\Filament\Resources\WordLists\WordListResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWordList extends CreateRecord
{
    protected static string $resource = WordListResource::class;
}
