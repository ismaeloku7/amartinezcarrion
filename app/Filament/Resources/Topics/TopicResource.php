<?php

namespace App\Filament\Resources\Topics;

use App\Filament\Support\Translatable;
use App\Models\Contents\Topic;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?int $navigationSort = 40;

    protected static ?string $recordRouteKeyName = 'id';

    public static function getNavigationLabel(): string
    {
        return __('Temáticas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->helperText(__('Parte de la URL: autoconocimiento, eneagrama…')),
            Select::make('parent_id')->label(__('Temática superior (vacío = principal)'))->relationship('parent', 'slug')->preload(),
            Translatable::text('name', __('Nombre'), true),
            Translatable::textarea('description', __('Descripción'), false, 2),
            ColorPicker::make('color')->label(__('Color'))->default('#1f3a5f'),
            TextInput::make('position')->label(__('Orden'))->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')->searchable(),
                TextColumn::make('name')->label(__('Nombre')),
                TextColumn::make('parent.slug')->label(__('Superior'))->placeholder('—'),
                ColorColumn::make('color')->label(__('Color')),
                TextColumn::make('contents_count')->counts('contents')->label(__('Contenidos')),
            ])
            ->defaultSort('position')
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopics::route('/'),
            'create' => Pages\CreateTopic::route('/create'),
            'edit' => Pages\EditTopic::route('/{record}/edit'),
        ];
    }
}
