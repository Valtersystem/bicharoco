<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Support\Euro;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LojaStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $vendidas = Order::whereNotIn('status', [OrderStatus::Cancelada, OrderStatus::AguardaPagamento]);

        $doMes = (clone $vendidas)->where('created_at', '>=', now()->startOfMonth())->get();
        $todas = $vendidas->get();
        $aImprimir = Order::where('status', OrderStatus::AImprimir)->count();

        return [
            Stat::make('Para imprimir', $aImprimir)
                ->description($aImprimir ? 'Ver a fila de produção' : 'Fila vazia')
                ->color($aImprimir ? 'warning' : 'gray')
                ->url(OrderResource::getUrl('index')),
            Stat::make('Vendas este mês', Euro::format($doMes->sum('amount_total_cents')))
                ->description($doMes->count().' encomendas'),
            Stat::make('Lucro este mês', Euro::format($doMes->sum(fn (Order $o) => $o->profitCents())))
                ->color('success'),
            Stat::make('Lucro total', Euro::format($todas->sum(fn (Order $o) => $o->profitCents())))
                ->description('desde a primeira venda'),
        ];
    }
}
