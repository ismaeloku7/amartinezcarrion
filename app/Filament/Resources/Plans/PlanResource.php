<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Support\Translatable;
use App\Models\Plan;
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

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 11;

    public static function getNavigationLabel(): string
    {
        return __('Planes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label(__('Clave'))->required()->unique(ignoreRecord: true),
            Translatable::text('name', __('Nombre'), true),
            Translatable::textarea('description', __('Descripción'), false, 2),
            Select::make('products')->label(__('Productos incluidos'))->relationship('products', 'key')->multiple()->preload()->required()
                ->helperText(__('Al añadir un producto, los suscriptores activos del plan reciben el acceso al renovarse o al pasar por el webhook.')),
            Select::make('interval')->label(__('Periodicidad'))->options([
                Plan::INTERVAL_MONTH => __('Mensual'), Plan::INTERVAL_YEAR => __('Anual'), Plan::INTERVAL_ONCE => __('Pago único'),
            ])->required(),
            TextInput::make('price_cents')->label(__('Precio (céntimos)'))->numeric()->required()->minValue(0),
            TextInput::make('currency')->label(__('Moneda'))->default('EUR')->maxLength(3),
            TextInput::make('stripe_price_id')->label(__('Stripe price ID'))->helperText(__('price_… creado en Stripe con el mismo importe y periodicidad.')),
            TextInput::make('trial_days')->label(__('Días de prueba'))->numeric()->default(0),
            TextInput::make('position')->label(__('Orden'))->numeric()->default(0),
            Toggle::make('active')->label(__('Activo'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label(__('Clave'))->searchable(),
                TextColumn::make('name')->label(__('Nombre')),
                TextColumn::make('interval')->label(__('Periodicidad'))->badge(),
                TextColumn::make('price_cents')->label(__('Precio'))->formatStateUsing(fn ($state, Plan $record) => $record->formattedPrice()),
                TextColumn::make('products.key')->label(__('Productos'))->badge(),
                IconColumn::make('stripe_price_id')->label('Stripe')->boolean(fn ($state) => filled($state)),
                IconColumn::make('active')->label(__('Activo'))->boolean(),
            ])
            ->defaultSort('position')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
