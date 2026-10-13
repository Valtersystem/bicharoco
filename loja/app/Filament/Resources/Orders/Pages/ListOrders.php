<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Venda em mão'),
        ];
    }

    public function getTabs(): array
    {
        $tab = fn (OrderStatus $status) => Tab::make($status->getLabel())
            ->badge(Order::where('status', $status)->count() ?: null)
            ->badgeColor($status->getColor())
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));

        return [
            'a_imprimir' => $tab(OrderStatus::AImprimir),
            'impressa' => $tab(OrderStatus::Impressa),
            'enviada' => $tab(OrderStatus::Enviada)->badge(null),
            'aguarda' => $tab(OrderStatus::AguardaPagamento),
            'todas' => Tab::make('Todas'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'a_imprimir';
    }
}
