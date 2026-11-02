<?php

namespace App\Filament\Support;

use App\Models\Language;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;

/** Builds one input per active language for a spatie/translatable attribute. */
class Translatable
{
    public static function text(string $attribute, string $label, bool $required = false): Fieldset
    {
        return Fieldset::make($label)->schema(
            collect(Language::activeCodes())->map(fn (string $code) => TextInput::make("$attribute.$code")
                ->label(strtoupper($code))
                ->required($required && $code === Language::defaultCode()))->all()
        )->columns(2);
    }

    public static function textarea(string $attribute, string $label, bool $required = false, int $rows = 4): Fieldset
    {
        return Fieldset::make($label)->schema(
            collect(Language::activeCodes())->map(fn (string $code) => Textarea::make("$attribute.$code")
                ->label(strtoupper($code))
                ->rows($rows)
                ->required($required && $code === Language::defaultCode()))->all()
        )->columns(1);
    }

    /** Expand translatable attributes into per-locale arrays so the form can fill them. */
    public static function fill(array $data, $record): array
    {
        foreach ($record->getTranslatableAttributes() as $attribute) {
            $data[$attribute] = $record->getTranslations($attribute);
        }

        return $data;
    }
}
