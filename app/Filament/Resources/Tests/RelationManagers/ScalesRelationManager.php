<?php

namespace App\Filament\Resources\Tests\RelationManagers;

use App\Filament\Support\Translatable;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScalesRelationManager extends RelationManager
{
    protected static string $relationship = 'scales';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Escalas / tipos');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label(__('Clave'))->required()->maxLength(20),
            Translatable::text('name', __('Nombre'), true),
            ColorPicker::make('color')->label(__('Color')),
            Select::make('group')->label(__('Grupo'))->options(['extrovert' => __('Extrovertido'), 'introvert' => __('Introvertido'), 'balanced' => __('Ambos')]),
            TextInput::make('position')->label(__('Orden'))->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label(__('Clave')),
                TextColumn::make('name')->label(__('Nombre')),
                ColorColumn::make('color')->label(__('Color')),
                TextColumn::make('group')->label(__('Grupo'))->badge(),
            ])
            ->defaultSort('position')
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make()->mutateRecordDataUsing(fn (array $data, $record) => Translatable::fill($data, $record)),
                DeleteAction::make(),
            ]);
    }
}
