<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_session_id')->nullable()->unique(); // vazio nas vendas em mão (feiras, OLX)
            $table->string('stripe_payment_intent_id')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('a_imprimir')->index();

            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->json('shipping_address')->nullable();
            $table->string('personalization')->nullable();
            $table->unsignedInteger('quantity')->default(1);

            // Valores em cêntimos
            $table->unsignedInteger('amount_total_cents')->default(0); // pago pelo cliente, com envio
            $table->unsignedInteger('shipping_charged_cents')->default(0); // envio pago pelo cliente
            $table->unsignedInteger('stripe_fee_cents')->nullable();
            $table->unsignedInteger('material_cost_cents')->default(0); // fotografado no momento da venda
            $table->unsignedInteger('shipping_cost_cents')->nullable(); // o que pagámos aos CTT

            $table->string('tracking_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
