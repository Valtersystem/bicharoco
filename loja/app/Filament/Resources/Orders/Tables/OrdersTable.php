<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Support\Euro;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'asc')
            ->columns([
                TextColumn::make('created_at')->label('Data')->date('d/m H:i')->sortable(),
                TextColumn::make('product.name')->label('Produto')
                    ->description(fn (Order $record) => $record->quantity > 1 ? "× {$record->quantity}" : null),
                TextColumn::make('personalization')->label('Nome a imprimir')
                    ->weight('bold')->size('lg')->copyable()->placeholder('—'),
                TextColumn::make('customer_name')->label('Cliente')->searchable()
                    ->description(fn (Order $record) => $record->shipping_address['city'] ?? null),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('amount_total_cents')->label('Total')->money('EUR', divideBy: 100),
                TextColumn::make('lucro')->label('Lucro')
                    ->state(fn (Order $record) => Euro::format($record->profitCents()))
                    ->color(fn (Order $record) => $record->profitCents() > 0 ? 'success' : 'danger'),
                TextColumn::make('tracking_code')->label('CTT')->copyable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product')->label('Produto')->relationship('product', 'name'),
            ])
            ->recordActions([
                Action::make('impressa')
                    ->label('Impressa')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('info')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::AImprimir)
                    ->action(fn (Order $record) => $record->update([
                        'status' => OrderStatus::Impressa,
                        'printed_at' => now(),
                    ])),
                Action::make('enviar')
                    ->label('Enviada')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('success')
                    ->visible(fn (Order $record) => in_array($record->status, [OrderStatus::AImprimir, OrderStatus::Impressa]))
                    ->modalHeading('Marcar como enviada')
                    ->schema([
                        TextInput::make('tracking_code')->label('Código CTT'),
                        TextInput::make('shipping_cost')->label('Quanto pagámos aos CTT')
                            ->numeric()->step('0.01')->suffix('€')
                            ->default(fn (Order $record) => Euro::fromCents($record->shipping_charged_cents)),
                    ])
                    ->action(fn (Order $record, array $data) => $record->update([
                        'status' => OrderStatus::Enviada,
                        'tracking_code' => $data['tracking_code'],
                        'shipping_cost_cents' => blank($data['shipping_cost']) ? null : Euro::toCents($data['shipping_cost']),
                        'printed_at' => $record->printed_at ?? now(),
                        'shipped_at' => now(),
                    ])),
                EditAction::make(),
            ]);
    }
}
