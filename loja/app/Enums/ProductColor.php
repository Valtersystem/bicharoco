<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

// As cores da marca. O valor é a classe CSS usada na loja (cor-limao, cor-ceu...).
enum ProductColor: string implements HasLabel
{
    case Limao = 'limao';
    case Chiclete = 'chiclete';
    case Ceu = 'ceu';
    case Menta = 'menta';
    case Laranja = 'laranja';

    public function getLabel(): string
    {
        return match ($this) {
            self::Limao => 'Limão',
            self::Chiclete => 'Chiclete',
            self::Ceu => 'Céu',
            self::Menta => 'Menta',
            self::Laranja => 'Laranja',
        };
    }

    public function hex(): string
    {
        return match ($this) {
            self::Limao => '#FFD23F',
            self::Chiclete => '#FF6FAE',
            self::Ceu => '#3EC1F3',
            self::Menta => '#3DD598',
            self::Laranja => '#FF8A3D',
        };
    }
}
