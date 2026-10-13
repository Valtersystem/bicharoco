<?php

namespace App\Support;

// Conversões entre euros (o que se escreve no painel) e cêntimos (o que se guarda).
class Euro
{
    public static function toCents(mixed $euros): int
    {
        return (int) round((float) str_replace(',', '.', (string) $euros) * 100);
    }

    public static function fromCents(?int $cents): ?string
    {
        return $cents === null ? null : number_format($cents / 100, 2, '.', '');
    }

    public static function format(?int $cents): string
    {
        return $cents === null ? '—' : number_format($cents / 100, 2, ',', ' ').' €';
    }
}
