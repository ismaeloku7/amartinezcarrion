<?php

namespace App\Filament\Resources\Contents;

use App\Filament\Support\Translatable;
use App\Models\Contents\Content;
use App\Models\Language;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 41;

    protected static ?string $recordRouteKeyName = 'id';

    public static function getNavigationLabel(): string
    {
        return __('Materiales');
    }

    public static function form(Schema $schema): Schema
    {
        $bodyTabs = [];
        foreach (Language::activeCodes() as $code) {
            $bodyTabs[] = Tab::make(strtoupper($code))->schema([
                RichEditor::make("body.$code")->label(__('Cuerpo'))->columnSpanFull()
                    ->helperText(__('Bloques especiales: [[eneatipos]] fichas de los nueve tipos · [[eneagrama-diagrama]] · [[eneagrama-tabla]] · [[test:eneagrama]], [[test:rheti]], [[test:johari]] llamada a un test. Clases HTML disponibles: lead, cards/card, card warn, steps.')),
            ]);
        }

        return $schema->components([
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
            Select::make('topic_id')->label(__('Temática'))->relationship('topic', 'slug')->preload(),
            Select::make('type')->label(__('Tipo'))->options([Content::TYPE_ARTICLE => __('Artículo'), Content::TYPE_COURSE => __('Curso')])->default(Content::TYPE_ARTICLE)->required(),
            Translatable::text('title', __('Título'), true),
            Translatable::textarea('excerpt', __('Entradilla'), false, 2),
            Tabs::make('body')->tabs($bodyTabs)->columnSpanFull(),
            Section::make(__('Publicación'))->columns(2)->schema([
                TextInput::make('author')->label(__('Autor'))->default('Antonio Martínez'),
                TextInput::make('reading_minutes')->label(__('Minutos de lectura (vacío = automático)'))->numeric(),
                ColorPicker::make('cover_color')->label(__('Color (vacío = el de la temática)')),
                TextInput::make('position')->label(__('Orden'))->numeric()->default(0),
                DateTimePicker::make('published_at')->label(__('Publicado desde (vacío = borrador)')),
                Select::make('product_id')->label(__('Producto de pago (vacío = gratuito)'))->relationship('product', 'key')->preload(),
                Toggle::make('requires_login')->label(__('Solo usuarios registrados')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label(__('Título'))->searchable()->wrap(),
                TextColumn::make('topic.slug')->label(__('Temática'))->placeholder('—'),
                TextColumn::make('type')->label(__('Tipo'))->badge(),
                IconColumn::make('published_at')->label(__('Publicado'))->boolean(fn ($state) => filled($state)),
                IconColumn::make('product_id')->label(__('De pago'))->boolean(fn ($state) => filled($state)),
                TextColumn::make('updated_at')->label(__('Actualizado'))->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
