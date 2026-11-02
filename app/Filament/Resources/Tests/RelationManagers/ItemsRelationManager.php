<?php

namespace App\Filament\Resources\Tests\RelationManagers;

use App\Filament\Support\Translatable;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Enunciados');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('position')->label(__('Orden'))->numeric()->default(fn () => ($this->getOwnerRecord()->items()->max('position') ?? 0) + 1),
            TextInput::make('group_key')->label(__('Grupo / página'))->maxLength(20),
            Translatable::textarea('statement', __('Enunciado'), true, 2),
            KeyValue::make('weights')->label(__('Puntos por escala'))->keyLabel(__('Escala'))->valueLabel(__('Puntos'))->default(['1' => 1]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->label('#')->sortable(),
                TextColumn::make('statement')->label(__('Enunciado'))->searchable()->wrap(),
                TextColumn::make('weights')->label(__('Escalas'))->formatStateUsing(fn ($state) => collect($state)->map(fn ($v, $k) => "$k×$v")->join(', ')),
            ])
            ->defaultSort('position')
            ->paginated([25, 50, 100])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make()->mutateRecordDataUsing(fn (array $data, $record) => Translatable::fill($data, $record)),
                DeleteAction::make(),
            ]);
    }
}
