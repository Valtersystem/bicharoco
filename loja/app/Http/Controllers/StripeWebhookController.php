<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * Recebe os eventos do Stripe e transforma cada pagamento numa encomenda.
 *
 * Cartão: checkout.session.completed já vem pago.
 * MB WAY / Multibanco: chega primeiro sem pagamento e depois vem
 * checkout.session.async_payment_succeeded (ou _failed).
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.webhook_secret'),
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            return response('Assinatura inválida', 400);
        }

        $session = $event->data->object->toArray();

        match ($event->type) {
            'checkout.session.completed' => $this->record($session),
            'checkout.session.async_payment_succeeded' => $this->record($session),
            'checkout.session.async_payment_failed' => $this->cancel($session),
            default => null,
        };

        return response('ok');
    }

    private function record(array $session): void
    {
        $paid = ($session['payment_status'] ?? null) === 'paid';

        $order = Order::firstOrNew(['stripe_session_id' => $session['id']]);
        $isNew = ! $order->exists;

        if ($isNew) {
            $product = Product::where('stripe_payment_link_id', $session['payment_link'] ?? '')->first();
            // Cada link vende um só produto, por isso a quantidade sai do subtotal.
            $unit = $product?->stripe_price_cents ?: $product?->price_cents;
            $quantity = $unit ? max(1, (int) round(($session['amount_subtotal'] ?? $unit) / $unit)) : 1;

            $shipping = data_get($session, 'collected_information.shipping_details')
                ?? data_get($session, 'shipping_details')
                ?? [];

            $personalization = collect($session['custom_fields'] ?? [])
                ->firstWhere('key', 'nome')['text']['value'] ?? null;

            $order->fill([
                'product_id' => $product?->id,
                'customer_name' => data_get($shipping, 'name') ?? data_get($session, 'customer_details.name'),
                'customer_email' => data_get($session, 'customer_details.email'),
                'customer_phone' => data_get($session, 'customer_details.phone'),
                'shipping_address' => array_merge(
                    ['name' => data_get($shipping, 'name')],
                    data_get($shipping, 'address') ?? data_get($session, 'customer_details.address') ?? [],
                ),
                'personalization' => $personalization,
                'quantity' => $quantity,
                'amount_total_cents' => $session['amount_total'] ?? 0,
                'shipping_charged_cents' => data_get($session, 'total_details.amount_shipping', 0),
                'material_cost_cents' => ($product?->materialCostCents() ?? 0) * $quantity,
                'stripe_payment_intent_id' => $session['payment_intent'] ?? null,
            ]);
        }

        if ($paid && $order->paid_at === null) {
            $order->status = OrderStatus::AImprimir;
            $order->paid_at = now();
            $order->stripe_fee_cents = $this->fee($order->stripe_payment_intent_id);
        } elseif ($isNew && ! $paid) {
            $order->status = OrderStatus::AguardaPagamento;
        }

        $order->save();
    }

    private function cancel(array $session): void
    {
        Order::where('stripe_session_id', $session['id'])
            ->update(['status' => OrderStatus::Cancelada]);
    }

    private function fee(?string $paymentIntentId): ?int
    {
        $secret = config('services.stripe.secret');

        if (! $paymentIntentId || ! $secret) {
            return null;
        }

        try {
            $intent = (new StripeClient($secret))->paymentIntents->retrieve($paymentIntentId, [
                'expand' => ['latest_charge.balance_transaction'],
            ]);

            return $intent->latest_charge?->balance_transaction?->fee;
        } catch (\Throwable $e) {
            Log::warning('Não foi possível obter a comissão do Stripe', ['intent' => $paymentIntentId, 'erro' => $e->getMessage()]);

            return null;
        }
    }
}
