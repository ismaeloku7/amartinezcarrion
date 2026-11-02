<?php

namespace App\Filament\Resources\Cards;

use App\Filament\Support\Translatable;
use App\Models\Cards\Card;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?int $navigationSort = 20;

    public static function getNavigationLabel(): string
    {
        return __('Fichas (42)');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')->label(__('Número'))->numeric()->required()->minValue(1)->maxValue(42)->unique(ignoreRecord: true),
            TextInput::make('group_index')->label(__('Grupo'))->numeric()->required()->minValue(1)->maxValue(6),
            Translatable::text('title', __('Título'), true),
            Translatable::textarea('front_text', __('Frase del anverso'), false, 2),
            Translatable::textarea('back_text', __('Reverso: si se escoge esta ficha…'), true, 5),
            Translatable::textarea('section_summary', __('Resumen del apartado del libro (opcional)'), false, 8),
            FileUpload::make('image_path')->label(__('Imagen del anverso'))->image()->disk('public')->directory('cards')->visibility('public')
                ->helperText(__('Si está vacía se usa la imagen extraída del PDF.')),
            Toggle::make('selectable')->label(__('Se puede escoger como ficha del día'))->default(true)
                ->helperText(__('Desactivado en la 7 (El SARA) y la 31 (La visualización).')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('#')->sortable(),
                TextColumn::make('group_index')->label(__('Grupo'))->badge(),
                ImageColumn::make('image_path')->label('')->getStateUsing(fn (Card $r) => $r->imageUrl())->height(40),
                TextColumn::make('title')->label(__('Título'))->searchable(),
                IconColumn::make('selectable')->label(__('Elegible'))->boolean(),
                IconColumn::make('section_summary')->label(__('Resumen'))->boolean(fn ($state) => filled($state)),
            ])
            ->defaultSort('number')
            ->paginated([42])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
