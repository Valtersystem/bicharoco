<?php

namespace App\Models;

use App\Enums\ProductColor;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'color' => ProductColor::class,
            'is_personalizable' => 'boolean',
            'is_active' => 'boolean',
            'filament_grams' => 'float',
            'stripe_synced_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /** Filamento + eletricidade + extras, por unidade. */
    public function materialCostCents(): int
    {
        $filamento = $this->filament_grams * config('loja.filamento_cents_kg') / 1000;
        $energia = $this->print_minutes / 60 * config('loja.energia_cents_hora');

        return (int) round($filamento + $energia + $this->extra_cost_cents);
    }

    public function estimatedStripeFeeCents(): int
    {
        return (int) round($this->price_cents * config('loja.stripe_pct') / 100 + config('loja.stripe_fixo_cents'));
    }

    /** Lucro estimado por unidade vendida (o envio é pago pelo cliente). */
    public function estimatedProfitCents(): int
    {
        return $this->price_cents - $this->materialCostCents() - $this->estimatedStripeFeeCents();
    }

    public function needsStripeSync(): bool
    {
        return ! $this->stripe_payment_link_url
            || $this->stripe_price_cents !== $this->price_cents
            || $this->wasChanged(['name', 'description', 'is_personalizable', 'is_active', 'image']);
    }
}
