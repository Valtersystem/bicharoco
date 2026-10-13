<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Services\StripeCatalog;
use App\Support\Euro;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->square()
                    ->defaultImageUrl(asset('img/bicharoco.svg')),
                ColorColumn::make('cor')->label('')->state(fn (Product $record) => $record->color->hex()),
                TextColumn::make('name')->label('Nome')->searchable()
                    ->description(fn (Product $record) => $record->badge),
                TextColumn::make('price_cents')->label('Preço')->money('EUR', divideBy: 100)->sortable(),
                TextColumn::make('lucro')->label('Lucro / peça')
                    ->state(fn (Product $record) => Euro::format($record->estimatedProfitCents()))
                    ->color(fn (Product $record) => $record->estimatedProfitCents() > 0 ? 'success' : 'danger'),
                TextColumn::make('orders_count')->label('Vendas')->counts('orders')->sortable(),
                IconColumn::make('is_personalizable')->label('Nome')->boolean(),
                ToggleColumn::make('is_active')->label('À venda'),
                IconColumn::make('stripe_payment_link_url')->label('Stripe')
                    ->state(fn (Product $record) => filled($record->stripe_payment_link_url))
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('stripe')
                    ->label('Sincronizar')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->visible(fn () => filled(config('services.stripe.secret')))
                    ->action(function (Product $record) {
                        StripeCatalog::make()->sync($record);
                        Notification::make()->title('Stripe atualizado')->success()->send();
                    }),
                EditAction::make(),
            ]);
    }
}
