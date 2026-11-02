<?php

namespace App\Filament\Resources\Users;

use App\Models\Language;
use App\Models\User;
use BackedEnum;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('Usuarios');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('Nombre'))->required(),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('password')->label(__('Contraseña'))->password()->dehydrated(fn ($state) => filled($state))->required(fn (string $operation) => $operation === 'create'),
            Select::make('locale')->label(__('Idioma'))->options(fn () => Language::query()->pluck('name', 'code'))->default('es'),
            TextInput::make('timezone')->label(__('Zona horaria'))->default('Europe/Madrid'),
            Toggle::make('is_admin')->label(__('Administrador')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(__('Nombre'))->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('locale')->label(__('Idioma'))->badge(),
                IconColumn::make('is_admin')->label(__('Admin'))->boolean(),
                TextColumn::make('entitlements_count')->counts('entitlements')->label(__('Accesos')),
                TextColumn::make('created_at')->label(__('Alta'))->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
