<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\StripeCatalog;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

// Sempre que um produto é guardado (painel, botão ativo/inativo, seeder),
// o produto e o link de pagamento no Stripe são atualizados.
class ProductObserver
{
    public function saved(Product $product): void
    {
        $catalog = StripeCatalog::make();

        if (! $catalog || ! $product->needsStripeSync()) {
            return;
        }

        try {
            $catalog->sync($product);

            Notification::make()->title('Stripe atualizado')->success()->send();
        } catch (\Throwable $e) {
            Log::error('Falhou a sincronização com o Stripe', ['product' => $product->id, 'erro' => $e->getMessage()]);

            Notification::make()
                ->title('O produto foi guardado, mas o Stripe deu erro')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
