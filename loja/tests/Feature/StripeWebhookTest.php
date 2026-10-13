<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test_bicharoco';

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.webhook_secret' => self::SECRET, 'services.stripe.secret' => null]);

        $this->product = Product::create([
            'name' => 'Porta-chaves com nome',
            'price_cents' => 600,
            'is_personalizable' => true,
            'filament_grams' => 8,
            'stripe_price_cents' => 600,
            'stripe_payment_link_id' => 'plink_123',
        ]);
    }

    public function test_rejeita_eventos_sem_assinatura_valida(): void
    {
        $this->postJson('/stripe/webhook', ['type' => 'checkout.session.completed'], ['Stripe-Signature' => 't=1,v1=errado'])
            ->assertStatus(400);

        $this->assertSame(0, Order::count());
    }

    public function test_pagamento_com_cartao_cria_encomenda_para_imprimir(): void
    {
        $this->send('checkout.session.completed', $this->checkoutSession(['amount_subtotal' => 1200, 'amount_total' => 1400]))
            ->assertOk();

        $order = Order::sole();
        $this->assertSame(OrderStatus::AImprimir, $order->status);
        $this->assertSame($this->product->id, $order->product_id);
        $this->assertSame('MARIA', $order->personalization);
        $this->assertSame(2, $order->quantity);
        $this->assertSame(1400, $order->amount_total_cents);
        $this->assertSame(200, $order->shipping_charged_cents);
        $this->assertSame($this->product->materialCostCents() * 2, $order->material_cost_cents);
        $this->assertSame('Lisboa', $order->shipping_address['city']);
        $this->assertSame('Maria Silva', $order->customer_name);
        $this->assertNotNull($order->paid_at);
    }

    public function test_o_mesmo_evento_duas_vezes_nao_duplica(): void
    {
        $this->send('checkout.session.completed', $this->checkoutSession());
        $this->send('checkout.session.completed', $this->checkoutSession());

        $this->assertSame(1, Order::count());
    }

    public function test_multibanco_espera_pelo_pagamento(): void
    {
        $this->send('checkout.session.completed', $this->checkoutSession(['payment_status' => 'unpaid']));
        $this->assertSame(OrderStatus::AguardaPagamento, Order::sole()->status);
        $this->assertNull(Order::sole()->paid_at);

        $this->send('checkout.session.async_payment_succeeded', $this->checkoutSession());
        $this->assertSame(OrderStatus::AImprimir, Order::sole()->status);
        $this->assertNotNull(Order::sole()->paid_at);
    }

    public function test_multibanco_falhado_cancela(): void
    {
        $this->send('checkout.session.completed', $this->checkoutSession(['payment_status' => 'unpaid']));
        $this->send('checkout.session.async_payment_failed', $this->checkoutSession(['payment_status' => 'unpaid']));

        $this->assertSame(OrderStatus::Cancelada, Order::sole()->status);
    }

    public function test_lucro_da_encomenda(): void
    {
        $order = new Order([
            'amount_total_cents' => 800,
            'stripe_fee_cents' => 37,
            'material_cost_cents' => 26,
            'shipping_charged_cents' => 200,
        ]);
        $this->assertSame(800 - 37 - 26 - 200, $order->profitCents());

        $order->shipping_cost_cents = 150; // os CTT custaram menos do que cobrámos
        $this->assertSame(800 - 37 - 26 - 150, $order->profitCents());
    }

    private function checkoutSession(array $overrides = []): array
    {
        return array_merge([
            'id' => 'cs_test_1',
            'object' => 'checkout.session',
            'payment_link' => 'plink_123',
            'payment_status' => 'paid',
            'payment_intent' => 'pi_123',
            'amount_subtotal' => 600,
            'amount_total' => 800,
            'total_details' => ['amount_shipping' => 200],
            'customer_details' => ['email' => 'maria@example.com', 'name' => 'Maria Silva', 'phone' => '+351910000000'],
            'collected_information' => ['shipping_details' => [
                'name' => 'Maria Silva',
                'address' => ['line1' => 'Rua do Bicho 1', 'postal_code' => '1000-001', 'city' => 'Lisboa', 'country' => 'PT'],
            ]],
            'custom_fields' => [['key' => 'nome', 'type' => 'text', 'text' => ['value' => 'MARIA']]],
        ], $overrides);
    }

    private function send(string $type, array $session)
    {
        $payload = json_encode([
            'id' => 'evt_'.uniqid(),
            'object' => 'event',
            'type' => $type,
            'data' => ['object' => $session],
        ]);
        $t = time();
        $signature = "t=$t,v1=".hash_hmac('sha256', "$t.$payload", self::SECRET);

        return $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);
    }
}
