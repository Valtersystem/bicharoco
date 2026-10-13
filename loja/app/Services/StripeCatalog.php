<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Stripe\StripeClient;

/**
 * Mantém cada produto da loja igual no Stripe: produto, preço e Payment Link.
 *
 * Os preços no Stripe não se podem alterar. Quando o preço muda, criamos um
 * preço novo e um link novo, e desativamos o link antigo.
 */
class StripeCatalog
{
    public function __construct(private StripeClient $stripe) {}

    public static function make(): ?self
    {
        $secret = config('services.stripe.secret');

        return $secret ? new self(new StripeClient($secret)) : null;
    }

    public function sync(Product $product): void
    {
        $this->syncProduct($product);

        $priceChanged = $product->stripe_price_cents !== $product->price_cents;
        $needsNewLink = ! $product->stripe_payment_link_id
            || $priceChanged
            || $product->wasChanged('is_personalizable');

        if (! $product->stripe_price_id || $priceChanged) {
            $price = $this->stripe->prices->create([
                'product' => $product->stripe_product_id,
                'unit_amount' => $product->price_cents,
                'currency' => 'eur',
            ]);
            $product->stripe_price_id = $price->id;
            $product->stripe_price_cents = $product->price_cents;
        }

        if ($needsNewLink) {
            $this->deactivateLink($product);
            $link = $this->stripe->paymentLinks->create($this->linkParams($product));
            $product->stripe_payment_link_id = $link->id;
            $product->stripe_payment_link_url = $link->url;
        } else {
            $this->stripe->paymentLinks->update($product->stripe_payment_link_id, [
                'active' => $product->is_active,
            ]);
        }

        $product->stripe_synced_at = now();
        $product->saveQuietly();
    }

    private function syncProduct(Product $product): void
    {
        $data = [
            'name' => $product->name,
            'description' => $product->description ?: null,
            'active' => $product->is_active,
            'metadata' => ['product_id' => $product->id],
        ];

        // O Stripe só aceita imagens com endereço público (não funciona em localhost).
        $image = $product->imageUrl();
        if ($image && str_starts_with($image, 'https://')) {
            $data['images'] = [$image];
        }

        if ($product->stripe_product_id) {
            $this->stripe->products->update($product->stripe_product_id, $data);
        } else {
            $product->stripe_product_id = $this->stripe->products->create($data)->id;
        }
    }

    private function deactivateLink(Product $product): void
    {
        if ($product->stripe_payment_link_id) {
            $this->stripe->paymentLinks->update($product->stripe_payment_link_id, ['active' => false]);
        }
    }

    private function linkParams(Product $product): array
    {
        $lineItem = ['price' => $product->stripe_price_id, 'quantity' => 1];

        // Um produto personalizado tem um só nome, por isso vende-se um de cada vez.
        if (! $product->is_personalizable) {
            $lineItem['adjustable_quantity'] = ['enabled' => true, 'minimum' => 1, 'maximum' => 10];
        }

        $params = [
            'line_items' => [$lineItem],
            'shipping_address_collection' => ['allowed_countries' => ['PT']],
            'shipping_options' => [['shipping_rate' => $this->shippingRate()]],
            'phone_number_collection' => ['enabled' => true],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => route('obrigado')],
            ],
            'metadata' => ['product_id' => $product->id],
        ];

        if ($product->is_personalizable) {
            $params['custom_fields'] = [[
                'key' => 'nome',
                'label' => ['type' => 'custom', 'custom' => 'Nome a imprimir'],
                'type' => 'text',
                'text' => ['maximum_length' => 20],
            ]];
        }

        if (! $product->is_active) {
            $params['active'] = false;
        }

        return $params;
    }

    private function shippingRate(): string
    {
        if ($id = config('services.stripe.shipping_rate')) {
            return $id;
        }

        $cents = config('loja.envio_cents');

        return Cache::rememberForever("stripe.shipping_rate.$cents", fn () => $this->stripe->shippingRates->create([
            'display_name' => config('loja.envio_nome'),
            'type' => 'fixed_amount',
            'fixed_amount' => ['amount' => $cents, 'currency' => 'eur'],
            'delivery_estimate' => [
                'minimum' => ['unit' => 'business_day', 'value' => 3],
                'maximum' => ['unit' => 'business_day', 'value' => 6],
            ],
        ])->id);
    }
}
