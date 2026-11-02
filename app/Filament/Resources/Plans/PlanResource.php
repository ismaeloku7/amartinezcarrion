<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Support\Translatable;
use App\Models\Plan;
use App\Services\PayPal\PayPalClient;
use App\Services\PayPal\PayPalPlans;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
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
            TextInput::make('paypal_plan_id')->label(__('PayPal plan ID'))
                ->helperText(__('Se rellena solo al guardar un plan mensual o anual: el portal crea el producto y el plan (P-…) en PayPal con este importe, periodicidad y prueba. Si cambias el precio se crea un plan nuevo y el anterior se desactiva. El pago único no lo necesita.')),
            TextInput::make('trial_days')->label(__('Días de prueba'))->numeric()->default(0)
                ->helperText(__('Se traslada al plan de PayPal como ciclo de prueba gratuito.')),
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
                IconColumn::make('paypal_plan_id')->label('PayPal')->boolean()->getStateUsing(fn (Plan $record) => $record->isPurchasable()),
                IconColumn::make('active')->label(__('Activo'))->boolean(),
            ])
            ->defaultSort('position')
            ->recordActions([EditAction::make(), static::paypalAction()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    /** Button to (re)create the plan on PayPal by hand, e.g. after configuring the credentials. */
    public static function paypalAction(): Action
    {
        return Action::make('paypal')
            ->label(__('Sincronizar con PayPal'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (Plan $record) => PayPalClient::enabled() && $record->isRecurring())
            ->requiresConfirmation()
            ->modalDescription(__('Crea o actualiza en PayPal el producto y el plan de suscripción con el importe y la periodicidad actuales.'))
            ->action(fn (Plan $record) => static::syncWithPayPal($record));
    }

    /** Push the plan to PayPal and tell the admin what happened; never blocks saving. */
    public static function syncWithPayPal(Plan $plan): void
    {
        if (! PayPalClient::enabled() || ! $plan->isRecurring()) {
            return;
        }

        $result = app(PayPalPlans::class)->syncQuietly($plan);
        $planId = $plan->fresh()->paypal_plan_id;

        match ($result['status']) {
            'created' => Notification::make()->success()->title(__('Plan creado en PayPal'))->body($planId)->send(),
            'replaced' => Notification::make()->success()->title(__('Nuevo plan creado en PayPal'))->body(__('El plan anterior se ha desactivado; los suscriptores actuales mantienen su precio. Nuevo ID: :id', ['id' => $planId]))->send(),
            'updated' => Notification::make()->success()->title(__('Plan actualizado en PayPal'))->send(),
            'failed' => Notification::make()->danger()->title(__('No se pudo sincronizar con PayPal'))->body($result['error'])->persistent()->send(),
            default => null,
        };
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
