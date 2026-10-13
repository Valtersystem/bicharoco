<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LojaTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_loja_mostra_so_os_produtos_a_venda(): void
    {
        Product::create(['name' => 'Porta-chaves', 'price_cents' => 600, 'stripe_payment_link_url' => 'https://buy.stripe.com/test_abc']);
        Product::create(['name' => 'Produto escondido', 'price_cents' => 900, 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Porta-chaves')
            ->assertSee('https://buy.stripe.com/test_abc')
            ->assertDontSee('Produto escondido');
    }

    public function test_produto_sem_link_mostra_em_breve(): void
    {
        Product::create(['name' => 'Vaso', 'price_cents' => 850]);

        $this->get('/')->assertOk()->assertSee('Em breve')->assertSee('8,5');
    }

    public function test_paginas_de_obrigado_e_termos(): void
    {
        $this->get('/obrigado')->assertOk()->assertSee('Obrigado');
        $this->get('/termos')->assertOk()->assertSee('Devoluções');
    }

    public function test_lucro_estimado_por_peca(): void
    {
        config(['loja.filamento_cents_kg' => 2000, 'loja.energia_cents_hora' => 3, 'loja.stripe_pct' => 1.5, 'loja.stripe_fixo_cents' => 25]);

        $p = new Product(['price_cents' => 600, 'filament_grams' => 8, 'print_minutes' => 60, 'extra_cost_cents' => 10]);

        $this->assertSame(16 + 3 + 10, $p->materialCostCents());   // filamento + energia + argola
        $this->assertSame(34, $p->estimatedStripeFeeCents());     // 9 + 25
        $this->assertSame(600 - 29 - 34, $p->estimatedProfitCents());
    }
}
