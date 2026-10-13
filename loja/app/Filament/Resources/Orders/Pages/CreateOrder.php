<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

// Vendas feitas fora do site (feiras, amigos, OLX).
class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Venda em mão';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $product = Product::find($data['product_id']);
        $data['material_cost_cents'] = ($product?->materialCostCents() ?? 0) * (int) $data['quantity'];
        $data['paid_at'] = now();

        return $data;
    }
}
