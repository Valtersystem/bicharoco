<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('price_cents');
            $table->string('color')->default('limao');
            $table->string('badge')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_personalizable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);

            // Custos de produção, por unidade
            $table->decimal('filament_grams', 8, 1)->default(0);
            $table->unsignedInteger('print_minutes')->default(0);
            $table->unsignedInteger('extra_cost_cents')->default(0); // argola, íman, embalagem...

            // Stripe
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->unsignedInteger('stripe_price_cents')->nullable();
            $table->string('stripe_payment_link_id')->nullable()->index();
            $table->string('stripe_payment_link_url')->nullable();
            $table->timestamp('stripe_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
