<?php

namespace App\Filament\Resources\Tests;

use App\Filament\Support\Translatable;
use App\Models\Language;
use App\Models\Tests\Test;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestResource extends Resource
{
    protected static ?string $model = Test::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 31;

    public static function getNavigationLabel(): string
    {
        return __('Tests');
    }

    public static function form(Schema $schema): Schema
    {
        $ruleFields = [
            Select::make('indicator')->label(__('Indicador'))->required()->options([
                'open_count' => __('Palabras en área abierta'), 'blind_count' => __('Palabras en área ciega'),
                'hidden_count' => __('Palabras en área oculta'), 'unknown_count' => __('Palabras en área desconocida'),
                'open_pct' => __('% área abierta'), 'blind_pct' => __('% área ciega'), 'hidden_pct' => __('% área oculta'),
                'agreement_pct' => __('% coincidencia'), 'dominant_area' => __('Área dominante (open|blind|hidden)'), 'responses' => __('Número de respuestas'),
            ]),
            Select::make('operator')->label(__('Operador'))->required()->options(['=' => '=', '!=' => '≠', '>' => '>', '>=' => '≥', '<' => '<', '<=' => '≤']),
            TextInput::make('value')->label(__('Valor'))->required(),
        ];
        foreach (Language::activeCodes() as $code) {
            $ruleFields[] = TextInput::make("title.$code")->label(__('Título').' '.strtoupper($code));
            $ruleFields[] = Textarea::make("content.$code")->label(__('Contenido').' '.strtoupper($code))->rows(3)->columnSpanFull();
        }

        return $schema->components([
            TextInput::make('key')->label(__('Clave'))->required()->unique(ignoreRecord: true),
            Select::make('type')->label(__('Tipo'))->options([Test::TYPE_JOHARI => __('Ventana de Johari')])->required(),
            Translatable::text('name', __('Nombre'), true),
            Translatable::textarea('description', __('Descripción corta'), false, 2),
            Translatable::textarea('intro', __('Introducción'), false, 4),
            Translatable::textarea('general_content', __('Contenido general al terminar'), false, 5),
            Select::make('word_list_id')->label(__('Lista de palabras'))->relationship('wordList', 'key')->preload(),
            Select::make('product_id')->label(__('Producto de pago (vacío = gratuito)'))->relationship('product', 'key')->preload(),
            Section::make(__('Parámetros'))->schema([
                KeyValue::make('config')->label(__('Configuración'))->keyLabel(__('Parámetro'))->valueLabel(__('Valor'))
                    ->helperText(__('self_words, other_words, min_responses, max_invitations, invitation_days, repeat_allowed (1/0), repeat_after_days')),
            ]),
            Section::make(__('Contenidos según resultado'))->schema([
                Repeater::make('rules')->label('')->relationship()->schema($ruleFields)->columns(3)->orderColumn('position')->reorderable()->collapsible()->defaultItems(0)
                    ->mutateRelationshipDataBeforeFillUsing(fn (array $data, $record) => $record ? Translatable::fill($data, $record) : $data),
            ]),
            Toggle::make('active')->label(__('Activo'))->default(true),
            TextInput::make('position')->label(__('Orden'))->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label(__('Clave')),
                TextColumn::make('name')->label(__('Nombre')),
                TextColumn::make('type')->label(__('Tipo'))->badge(),
                TextColumn::make('wordList.key')->label(__('Lista')),
                TextColumn::make('takes_count')->counts('takes')->label(__('Tomas')),
                IconColumn::make('active')->label(__('Activo'))->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTests::route('/'),
            'create' => Pages\CreateTest::route('/create'),
            'edit' => Pages\EditTest::route('/{record}/edit'),
        ];
    }
}
