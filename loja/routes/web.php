<?php

use App\Http\Controllers\StripeWebhookController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('loja.index', ['produtos' => Product::visible()->get()]))->name('loja');
Route::view('/obrigado', 'loja.obrigado')->name('obrigado');
Route::view('/termos', 'loja.termos')->name('termos');

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
