<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductColor;
use App\Models\Product;
use App\Support\Euro;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Produto')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nome')->required()->maxLength(80),
                        self::euroInput('price_cents', 'Preço')->required(),
                        TextInput::make('description')->label('Descrição curta')->maxLength(140)->columnSpanFull()
                            ->helperText('Uma frase. Aparece no cartão da loja e no checkout.'),
                        Select::make('color')->label('Cor do cartão')->options(ProductColor::class)->default(ProductColor::Limao)->required(),
                        TextInput::make('badge')->label('Etiqueta')->placeholder('Novo, Personalizável...')->maxLength(20),
                        FileUpload::make('image')->label('Foto')->image()->disk('public')->directory('produtos')
                            ->imageEditor()->imageCropAspectRatio('4:3')->maxSize(4096)->columnSpanFull()
                            ->helperText('Fundo liso e luz natural. Sem foto, aparece o Bicharoco.'),
                    ]),

                Grid::make(1)->columnSpan(1)->schema([
                    Section::make('Na loja')->schema([
                        Toggle::make('is_active')->label('À venda')->default(true),
                        Toggle::make('is_personalizable')->label('Personalizável')
                            ->helperText('O checkout pede o "Nome a imprimir".'),
                        TextInput::make('sort')->label('Ordem')->numeric()->default(0),
                    ]),

                    Section::make('Custos por peça')
                        ->description('Para calcular o lucro.')
                        ->schema([
                            TextInput::make('filament_grams')->label('Filamento')->numeric()->suffix('g')->default(0)->live(debounce: 500),
                            TextInput::make('print_minutes')->label('Tempo de impressão')->numeric()->suffix('min')->default(0)->live(debounce: 500),
                            self::euroInput('extra_cost_cents', 'Extras (argola, íman, embalagem)')->default('0.00')->live(debounce: 500),
                            TextEntry::make('lucro')
                                ->label('Lucro estimado por peça')
                                ->state(fn (Get $get) => self::profitPreview($get))
                                ->weight('bold'),
                        ]),
                ]),

                Section::make('Stripe')
                    ->columnSpanFull()
                    ->columns(2)
                    ->hiddenOn('create')
                    ->schema([
                        TextEntry::make('stripe_payment_link_url')->label('Link de pagamento')
                            ->url(fn (?Product $record) => $record?->stripe_payment_link_url, shouldOpenInNewTab: true)
                            ->placeholder('Ainda não criado. Configurem STRIPE_SECRET no .env.')
                            ->copyable(),
                        TextEntry::make('stripe_synced_at')->label('Última sincronização')->since()->placeholder('Nunca'),
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
            ->dehydrateStateUsing(fn ($state) => Euro::toCents($state));
    }

    private static function profitPreview(Get $get): string
    {
        $product = new Product([
            'price_cents' => Euro::toCents($get('price_cents')),
            'filament_grams' => (float) $get('filament_grams'),
            'print_minutes' => (int) $get('print_minutes'),
            'extra_cost_cents' => Euro::toCents($get('extra_cost_cents')),
        ]);

        return Euro::format($product->estimatedProfitCents())
            .' (material '.Euro::format($product->materialCostCents())
            .', Stripe ≈ '.Euro::format($product->estimatedStripeFeeCents()).')';
    }
}
