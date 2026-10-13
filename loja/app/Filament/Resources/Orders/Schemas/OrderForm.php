<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Support\Euro;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Encomenda')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')->label('Produto')
                            ->relationship('product', 'name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::fillTotal($get, $set)),
                        TextInput::make('quantity')->label('Quantidade')->numeric()->minValue(1)->default(1)->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::fillTotal($get, $set)),
                        TextInput::make('personalization')->label('Nome a imprimir')->maxLength(40),
                        Select::make('status')->label('Estado')->options(OrderStatus::class)
                            ->default(OrderStatus::AImprimir)->required(),
                        self::euroInput('amount_total_cents', 'Total pago pelo cliente')->required()
                            ->helperText('Inclui o envio, se o cliente o pagou.'),
                        self::euroInput('shipping_charged_cents', 'Envio pago pelo cliente')->default('0.00'),
                        Textarea::make('notes')->label('Notas')->rows(2)->columnSpanFull(),
                    ]),

                Section::make('Cliente')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('customer_name')->label('Nome'),
                        TextInput::make('customer_email')->label('Email')->email(),
                        TextInput::make('customer_phone')->label('Telefone'),
                        TextEntry::make('morada')->label('Morada de envio')
                            ->state(fn (?Order $record) => $record ? implode("\n", $record->shippingAddressLines()) : null)
                            ->placeholder('Sem morada')
                            ->hiddenOn('create'),
                    ]),

                Section::make('Envio')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('tracking_code')->label('Código CTT'),
                        self::euroInput('shipping_cost_cents', 'Quanto pagámos aos CTT')->nullable(),
                    ]),

                Section::make('Contas')
                    ->columnSpan(1)
                    ->hiddenOn('create')
                    ->schema([
                        TextEntry::make('stripe_fee_cents')->label('Comissão Stripe')
                            ->state(fn (Order $record) => Euro::format($record->stripe_fee_cents)),
                        TextEntry::make('material_cost_cents')->label('Material')
                            ->state(fn (Order $record) => Euro::format($record->material_cost_cents)),
                        TextEntry::make('lucro')->label('Lucro')->weight('bold')
                            ->state(fn (Order $record) => Euro::format($record->profitCents())),
                    ]),
            ]);
    }

    private static function euroInput(string $field, string $label): TextInput
    {
        return TextInput::make($field)
            ->label($label)
            ->numeric()
            ->step('0.01')
            ->minValue(0)
            ->suffix('€')
            ->formatStateUsing(fn ($state) => Euro::fromCents($state === null ? null : (int) $state))
            ->dehydrateStateUsing(fn ($state) => blank($state) ? null : Euro::toCents($state));
    }

    // Nas vendas em mão, sugere o total a partir do preço do produto.
    private static function fillTotal(Get $get, Set $set): void
    {
        $product = Product::find($get('product_id'));

        if ($product) {
            $set('amount_total_cents', Euro::fromCents($product->price_cents * max(1, (int) $get('quantity'))));
        }
    }
}
