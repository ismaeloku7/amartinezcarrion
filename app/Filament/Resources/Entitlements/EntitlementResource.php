<?php

namespace App\Filament\Resources\Entitlements;

use App\Models\Entitlement;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EntitlementResource extends Resource
{
    protected static ?string $model = Entitlement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 12;

    public static function getNavigationLabel(): string
    {
        return __('Accesos');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->label(__('Usuario'))->relationship('user', 'email')->searchable()->preload()->required(),
            Select::make('product_id')->label(__('Producto'))->relationship('product', 'key')->preload()->required(),
            Select::make('source')->label(__('Origen'))->options([
                Entitlement::SOURCE_MANUAL => __('Manual'), Entitlement::SOURCE_SUBSCRIPTION => __('Suscripción'),
                Entitlement::SOURCE_PURCHASE => __('Compra'), Entitlement::SOURCE_FREE => __('Gratuito'),
            ])->default(Entitlement::SOURCE_MANUAL)->required(),
            DateTimePicker::make('starts_at')->label(__('Desde'))->default(now())->required(),
            DateTimePicker::make('ends_at')->label(__('Hasta (vacío = sin caducidad)')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label(__('Usuario'))->searchable(),
                TextColumn::make('product.key')->label(__('Producto'))->badge(),
                TextColumn::make('source')->label(__('Origen'))->badge(),
                TextColumn::make('starts_at')->label(__('Desde'))->dateTime('d/m/Y'),
                TextColumn::make('ends_at')->label(__('Hasta'))->dateTime('d/m/Y')->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntitlements::route('/'),
            'create' => Pages\CreateEntitlement::route('/create'),
            'edit' => Pages\EditEntitlement::route('/{record}/edit'),
        ];
    }
}
