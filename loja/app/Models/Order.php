<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'shipping_address' => 'array',
            'paid_at' => 'datetime',
            'printed_at' => 'datetime',
            'shipped_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Lucro real: o que o cliente pagou, menos a comissão do Stripe, o material
     * e o envio. Enquanto não registarmos o custo real dos CTT, assumimos que
     * custou o mesmo que o cliente pagou de envio.
     */
    public function profitCents(): int
    {
        return $this->amount_total_cents
            - ($this->stripe_fee_cents ?? 0)
            - $this->material_cost_cents
            - ($this->shipping_cost_cents ?? $this->shipping_charged_cents);
    }

    public function shippingAddressLines(): array
    {
        $a = $this->shipping_address ?? [];

        return array_values(array_filter([
            $a['name'] ?? $this->customer_name,
            $a['line1'] ?? null,
            $a['line2'] ?? null,
            trim(($a['postal_code'] ?? '').' '.($a['city'] ?? '')),
            $a['state'] ?? null,
            $a['country'] ?? null,
        ]));
    }
}
