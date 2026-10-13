<?php

// Valores usados para calcular custos e lucro. Mudem-nos no .env.
return [
    // Preço do filamento, em cêntimos por kg (20 € = 2000)
    'filamento_cents_kg' => (int) env('LOJA_FILAMENTO_CENTS_KG', 2000),

    // Eletricidade da impressora, em cêntimos por hora de impressão
    'energia_cents_hora' => (int) env('LOJA_ENERGIA_CENTS_HORA', 3),

    // Envio cobrado ao cliente no checkout do Stripe, em cêntimos
    'envio_cents' => (int) env('LOJA_ENVIO_CENTS', 200),
    'envio_nome' => env('LOJA_ENVIO_NOME', 'CTT correio normal'),

    // Estimativa da comissão Stripe para cartões europeus (só para previsões;
    // nas encomendas usamos o valor real que o Stripe devolve)
    'stripe_pct' => (float) env('LOJA_STRIPE_PCT', 1.5),
    'stripe_fixo_cents' => (int) env('LOJA_STRIPE_FIXO_CENTS', 25),

    'email' => env('LOJA_EMAIL', 'ola@bicharoco.pt'),
    'instagram' => env('LOJA_INSTAGRAM', 'bicharoco'),
    'nif' => env('LOJA_NIF', '[o vosso NIF]'),
];
