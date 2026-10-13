<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Utilizador do painel para desenvolvimento local. Em produção criem o
        // vosso com: php artisan make:filament-user
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@bicharoco.test')],
            ['name' => 'Admin', 'password' => env('ADMIN_PASSWORD', 'bicharoco-local')],
        );

        $produtos = [
            ['Porta-chaves com nome', 600, 'Escreves o nome no checkout, nós imprimimos.', 'limao', 'Personalizável', true, 8, 40, 10],
            ['Bicharoco de secretária', 900, 'Segura o telemóvel e sorri o dia todo.', 'chiclete', 'Mascote', false, 45, 180, 0],
            ['Bases de azulejo (4)', 1200, 'Padrão de azulejo português para pousar o café.', 'ceu', null, false, 60, 240, 0],
            ['Organizador de cabos (6)', 500, 'Acaba com o ninho de fios atrás da secretária.', 'menta', null, false, 12, 50, 0],
            ['Vaso bicho mini', 800, 'Para um cato pequeno com cara de quem manda.', 'laranja', 'Novo', false, 35, 150, 0],
            ['Placa de nome para porta', 1000, 'Para o quarto, o escritório ou a casota do cão.', 'limao', 'Personalizável', true, 30, 120, 30],
        ];

        foreach ($produtos as $i => [$nome, $preco, $desc, $cor, $badge, $pers, $g, $min, $extra]) {
            Product::firstOrCreate(['name' => $nome], [
                'price_cents' => $preco,
                'description' => $desc,
                'color' => $cor,
                'badge' => $badge,
                'is_personalizable' => $pers,
                'filament_grams' => $g,
                'print_minutes' => $min,
                'extra_cost_cents' => $extra,
                'sort' => $i,
            ]);
        }
    }
}
