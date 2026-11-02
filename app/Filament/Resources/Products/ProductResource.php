<?php

namespace App\Filament\Resources\Products;

use App\Filament\Support\Translatable;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('Productos');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label(__('Clave'))->required()->unique(ignoreRecord: true)->helperText(__('Identificador usado por el código: mdp, cards, test-johari…')),
            Select::make('type')->label(__('Tipo'))->options([
                Product::TYPE_PRACTICE => __('Práctica de libro'),
                Product::TYPE_TEST => __('Test'),
                Product::TYPE_CONTENT => __('Contenido'),
                Product::TYPE_COURSE => __('Curso'),
            ])->required(),
            Translatable::text('name', __('Nombre'), true),
            Translatable::textarea('description', __('Descripción')),
            Toggle::make('is_free')->label(__('Gratuito para usuarios registrados')),
            Toggle::make('active')->label(__('Activo'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label(__('Clave'))->searchable(),
                TextColumn::make('name')->label(__('Nombre')),
                TextColumn::make('type')->label(__('Tipo'))->badge(),
                IconColumn::make('is_free')->label(__('Gratis'))->boolean(),
                IconColumn::make('active')->label(__('Activo'))->boolean(),
                TextColumn::make('plans_count')->counts('plans')->label(__('Planes')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
