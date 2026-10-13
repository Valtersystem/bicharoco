<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case AguardaPagamento = 'aguarda_pagamento';
    case AImprimir = 'a_imprimir';
    case Impressa = 'impressa';
    case Enviada = 'enviada';
    case Cancelada = 'cancelada';

    public function getLabel(): string
    {
        return match ($this) {
            self::AguardaPagamento => 'Aguarda pagamento',
            self::AImprimir => 'A imprimir',
            self::Impressa => 'Impressa',
            self::Enviada => 'Enviada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AguardaPagamento => 'gray',
            self::AImprimir => 'warning',
            self::Impressa => 'info',
            self::Enviada => 'success',
            self::Cancelada => 'danger',
        };
    }
}
