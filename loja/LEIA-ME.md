# Bicharoco — loja + painel (Laravel 13 + Filament 5)

- **Loja pública** em `/`, com os produtos da base de dados e os botões "Comprar" ligados ao Stripe.
- **Painel** em `/admin`, com:
  - **Encomendas:** fila de produção (a imprimir, impressa, enviada), nome a imprimir, morada, código CTT, lucro real e vendas em mão (feiras, OLX).
  - **Produtos:** criar, editar e esconder produtos, fotos, custos por peça e lucro estimado.
  - **Painel de controlo:** peças para imprimir, vendas e lucro do mês, lucro total.
- **Stripe automático:** ao guardar um produto, o Laravel cria ou atualiza o produto, o preço e o Payment Link no Stripe. Se o preço mudar, cria um link novo e desativa o antigo.
- **Webhook:** cada pagamento vira uma encomenda no painel, com a comissão real do Stripe.

## Correr no computador

```bash
cd loja
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

- Loja: http://localhost:8000
- Painel: http://localhost:8000/admin
- O seeder cria um utilizador local `admin@bicharoco.test` com a senha `bicharoco-local` e os 6 produtos de exemplo. É só para o computador. Na VPS criem o vosso utilizador.

Testes: `php artisan test`

## Ligar o Stripe (modo de teste primeiro)

1. Em https://dashboard.stripe.com/test/apikeys copiem a **Secret key** (`sk_test_...`) para `STRIPE_SECRET` no `.env`.
2. Abram cada produto no painel e carreguem em **Guardar**, ou usem **Sincronizar** na lista. O link de pagamento aparece sozinho.
3. Para receber as vendas no computador, instalem o [Stripe CLI](https://docs.stripe.com/stripe-cli) e corram:
   ```bash
   stripe listen --forward-to localhost:8000/stripe/webhook --events checkout.session.completed,checkout.session.async_payment_succeeded,checkout.session.async_payment_failed,charge.updated
   ```
   Copiem o `whsec_...` que aparece para `STRIPE_WEBHOOK_SECRET`.
4. Façam uma compra na loja com o cartão `4242 4242 4242 4242`. A encomenda aparece em **Encomendas → A imprimir**.

## Instalar na VPS

O que é preciso: PHP 8.4 (com as extensões `mbstring`, `xml`, `curl`, `intl`, `zip`, `gd`, `pdo_mysql`), Composer, MySQL ou MariaDB, Nginx e Certbot para o HTTPS.

1. **Domínio:** um registo DNS `A` de `bicharoco.pt` (e `www`) para o IP da VPS.
2. **Código:**
   ```bash
   git clone https://github.com/Valtersystem/bicharoco.git /var/www/bicharoco
   cd /var/www/bicharoco/loja
   composer install --no-dev --optimize-autoloader
   cp .env.example .env && php artisan key:generate
   ```
3. **`.env` de produção:**
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://bicharoco.pt
   DB_CONNECTION=mysql
   DB_DATABASE=bicharoco
   DB_USERNAME=bicharoco
   DB_PASSWORD=...
   STRIPE_SECRET=sk_live_...        # só depois de testarem com sk_test_
   STRIPE_WEBHOOK_SECRET=whsec_...   # do passo 6
   LOJA_NIF=...
   ```
4. **Base de dados e ficheiros:**
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan make:filament-user
   php artisan optimize
   sudo chown -R www-data:www-data storage bootstrap/cache
   ```
   Não corram `--seed` em produção: o seeder cria o utilizador de teste. Criem os produtos no painel.
5. **Nginx:** o `root` aponta para `/var/www/bicharoco/loja/public`, com a configuração padrão de Laravel (https://laravel.com/docs/deployment#nginx). Depois: `sudo certbot --nginx -d bicharoco.pt -d www.bicharoco.pt`.
6. **Webhook no Stripe:** em Developers → Webhooks → Add endpoint:
   - URL: `https://bicharoco.pt/stripe/webhook`
   - Eventos: `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`, `charge.updated`
   - Copiem o *Signing secret* para `STRIPE_WEBHOOK_SECRET` e corram `php artisan optimize` outra vez.
7. **Links do Stripe:** na lista de produtos, carreguem em **Sincronizar** em cada um. Assim os links passam a voltar para `https://bicharoco.pt/obrigado` e as fotos aparecem no checkout.

**Atualizar a loja mais tarde:**
```bash
cd /var/www/bicharoco && git pull && cd loja
composer install --no-dev --optimize-autoloader
php artisan migrate --force && php artisan optimize
```

## Onde mexer

| Quero mudar... | Ficheiro |
|---|---|
| Textos da loja | `resources/views/loja/index.blade.php` |
| Cores e estilos da loja | `public/css/loja.css` |
| Preço do filamento, custo do envio, email, NIF | `.env` (variáveis `LOJA_*`) |
| O que é enviado ao Stripe | `app/Services/StripeCatalog.php` |
| Como uma venda vira encomenda | `app/Http/Controllers/StripeWebhookController.php` |
| Formulários e tabelas do painel | `app/Filament/Resources/` |
