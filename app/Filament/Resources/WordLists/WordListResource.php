<?php

namespace App\Filament\Resources\WordLists;

use App\Filament\Support\Translatable;
use App\Models\Language;
use App\Models\Tests\WordList;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WordListResource extends Resource
{
    protected static ?string $model = WordList::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = 30;

    public static function getNavigationLabel(): string
    {
        return __('Listas de palabras');
    }

    public static function form(Schema $schema): Schema
    {
        $wordFields = [TextInput::make('key')->label(__('Clave'))->required()->distinct()];
        foreach (Language::activeCodes() as $code) {
            $wordFields[] = TextInput::make("label.$code")->label(__('Palabra').' '.strtoupper($code))->required($code === Language::defaultCode());
        }
        $wordFields[] = Select::make('tone')->label(__('Tono'))->options(['positive' => __('Positivo'), 'neutral' => __('Neutro'), 'uncomfortable' => __('Incómodo')]);

        return $schema->components([
            TextInput::make('key')->label(__('Clave'))->required()->unique(ignoreRecord: true),
            Translatable::text('name', __('Nombre'), true),
            Translatable::textarea('description', __('Descripción'), false, 2),
            Toggle::make('active')->label(__('Activa'))->default(true),
            Toggle::make('is_default')->label(__('Lista por defecto')),
            Repeater::make('words')->label(__('Palabras'))->relationship()
                ->schema($wordFields)->columns(count(Language::activeCodes()) + 2)
                ->orderColumn('position')->reorderable()->collapsible()->defaultItems(0)
                ->mutateRelationshipDataBeforeFillUsing(fn (array $data, $record) => $record ? Translatable::fill($data, $record) : $data)
                ->itemLabel(fn (array $state) => $state['label'][Language::defaultCode()] ?? $state['key'] ?? null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label(__('Clave')),
                TextColumn::make('name')->label(__('Nombre')),
                TextColumn::make('words_count')->counts('words')->label(__('Palabras')),
                IconColumn::make('active')->label(__('Activa'))->boolean(),
                IconColumn::make('is_default')->label(__('Por defecto'))->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')->label(__('Duplicar'))->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->form([TextInput::make('key')->label(__('Clave nueva'))->required()->unique('word_lists', 'key'), TextInput::make('name')->label(__('Nombre'))->required()])
                    ->action(fn (WordList $record, array $data) => $record->duplicate($data['key'], $data['name'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWordLists::route('/'),
            'create' => Pages\CreateWordList::route('/create'),
            'edit' => Pages\EditWordList::route('/{record}/edit'),
        ];
    }
}
