<?php

namespace App\Filament\Resources\Tests\RelationManagers;

use App\Filament\Support\Translatable;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** The author's text for each type: zones, danger, desire, fear, growth and his own conclusion. */
class ProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'ownProfiles';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('Perfiles por tipo');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('scale_key')->label(__('Tipo (clave de la escala)'))->required()->maxLength(20),
            Translatable::text('title', __('Título'), true),
            Translatable::text('subtitle', __('Rasgo central')),
            Section::make(__('Ficha'))->columns(2)->schema([
                Translatable::text('character', __('Carácter')),
                Translatable::text('temperament', __('Temperamento')),
                Translatable::text('danger', __('Peligro')),
                Translatable::text('basic_desire', __('Deseo básico')),
                Translatable::text('basic_fear', __('Temor básico')),
                Translatable::text('hidden_complaint', __('Queja oculta')),
                Translatable::text('childhood_origin', __('Origen infantil')),
                Translatable::text('integration', __('Integración')),
                Translatable::text('disintegration', __('Desintegración')),
            ]),
            Section::make(__('Zonas (un rasgo por línea)'))->schema([
                Translatable::textarea('healthy', __('Zona sana'), false, 8),
                Translatable::textarea('shadow', __('Zona de sombra'), false, 8),
                Translatable::textarea('unhealthy', __('Zona malsana'), false, 6),
            ]),
            Translatable::textarea('growth_action', __('Botón de acción para el crecimiento'), false, 2),
            Translatable::textarea('notes', __('Notas'), false, 3),
            Translatable::textarea('conclusion', __('Conclusión del autor (se muestra en resultados y se da a la IA)'), false, 5),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scale_key')->label(__('Tipo'))->sortable(),
                TextColumn::make('title')->label(__('Título')),
                TextColumn::make('danger')->label(__('Peligro')),
                TextColumn::make('conclusion')->label(__('Conclusión'))->limit(40),
            ])
            ->defaultSort('scale_key')
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make()->mutateRecordDataUsing(fn (array $data, $record) => Translatable::fill($data, $record)),
                DeleteAction::make(),
            ]);
    }
}
