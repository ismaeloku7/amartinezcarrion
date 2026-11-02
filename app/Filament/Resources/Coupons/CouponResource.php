<?php

namespace App\Filament\Resources\Coupons;

use App\Models\Coupon;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 12;

    public static function getNavigationLabel(): string
    {
        return __('Cupones');
    }

    public static function getModelLabel(): string
    {
        return __('Cupón');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Cupones');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label(__('Código'))->required()->maxLength(40)->unique(ignoreRecord: true)
                ->dehydrateStateUsing(fn (?string $state) => Coupon::normalize((string) $state))
                ->helperText(__('Sin espacios; se guarda en mayúsculas. Los usuarios lo escriben en la página de planes, o llega en el enlace /planes/{producto}?cupon=CÓDIGO.')),
            TextInput::make('description')->label(__('Nota interna'))->maxLength(255),
            Select::make('type')->label(__('Tipo de descuento'))->required()->default(Coupon::TYPE_PERCENT)->live()->options([
                Coupon::TYPE_PERCENT => __('Porcentaje'), Coupon::TYPE_FIXED => __('Importe fijo'),
            ]),
            TextInput::make('value')->label(fn (Get $get) => $get('type') === Coupon::TYPE_FIXED ? __('Descuento (céntimos)') : __('Descuento (%)'))
                ->numeric()->required()->minValue(1)->maxValue(fn (Get $get) => $get('type') === Coupon::TYPE_FIXED ? null : 100),
            Select::make('duration')->label(__('En suscripciones'))->required()->default(Coupon::DURATION_ONCE)->options([
                Coupon::DURATION_ONCE => __('Solo el primer periodo'), Coupon::DURATION_FOREVER => __('Todos los periodos mientras dure la suscripción'),
            ])->helperText(__('En los planes de pago único el descuento se aplica una vez en cualquier caso.')),
            Select::make('plans')->label(__('Planes en los que vale'))->relationship('plans', 'key')->multiple()->preload()
                ->helperText(__('Vacío = todos los planes.')),
            Section::make(__('Límites'))->schema([
                TextInput::make('max_redemptions')->label(__('Usos máximos'))->numeric()->minValue(1)->helperText(__('Vacío = sin límite. Solo cuentan los pagos confirmados.')),
                Toggle::make('once_per_user')->label(__('Un solo uso por usuario'))->default(true),
                DateTimePicker::make('starts_at')->label(__('Válido desde'))->seconds(false),
                DateTimePicker::make('expires_at')->label(__('Caduca'))->seconds(false),
            ])->columns(2),
            Toggle::make('active')->label(__('Activo'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label(__('Código'))->searchable()->weight('bold'),
                TextColumn::make('value')->label(__('Descuento'))->formatStateUsing(fn ($state, Coupon $record) => $record->formattedValue()),
                TextColumn::make('duration')->label(__('Suscripciones'))->formatStateUsing(fn ($state) => $state === Coupon::DURATION_FOREVER ? __('Siempre') : __('Primer periodo'))->badge(),
                TextColumn::make('plans.key')->label(__('Planes'))->badge()->placeholder(__('Todos')),
                TextColumn::make('confirmed_redemptions_count')->label(__('Usos'))->counts('confirmedRedemptions')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->max_redemptions ? "{$state} / {$record->max_redemptions}" : $state),
                TextColumn::make('expires_at')->label(__('Caduca'))->dateTime('d/m/Y H:i')->placeholder('—'),
                IconColumn::make('active')->label(__('Activo'))->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
