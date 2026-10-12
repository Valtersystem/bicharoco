# Bicharoco — site da loja

Site estático em HTML, CSS e JS, sem frameworks. Os pagamentos são feitos com Stripe Payment Links.

```
site/
├── index.html      página principal (loja, como funciona, quem somos, perguntas)
├── obrigado.html   página que o cliente vê depois de pagar
├── termos.html     termos, envios e devoluções (RASCUNHO, preencher os [ ])
├── css/style.css   cores e estilos da marca
├── js/produtos.js  ← O CATÁLOGO. Para mudar produtos, preços e links, edita só este ficheiro.
├── js/loja.js      desenha os cartões dos produtos
└── img/            mascote e fotos dos produtos
```

## 1. Ver o site no computador

```bash
python3 -m http.server 8080 --directory site
```
Depois abre http://localhost:8080

## 2. Criar os links de pagamento no Stripe

1. Criem a conta em https://dashboard.stripe.com/register (com os dados de quem abrir atividade nas Finanças).
2. Ativem os métodos de pagamento: **Settings → Payment methods**. Liguem Cartão e, se aparecerem, MB WAY e Multibanco.
3. Para cada produto: **Payment Links → New**
   - Criem o produto com nome, preço e foto.
   - **Recolher morada de envio:** ativem "Collect customers' addresses" → Shipping, só Portugal.
   - **Custo de envio:** "Shipping rates", por exemplo "CTT correio normal".
   - **Produto personalizável:** ativem "Add custom fields" → Text → "Nome a imprimir".
   - **Quantidade:** "Let customers adjust quantity" (opcional).
   - **Depois do pagamento:** "Don't show confirmation page" → redirecionar para `https://O-VOSSO-SITE/obrigado.html`.
4. Copiem o link (`https://buy.stripe.com/...`) e colem-no no campo `stripe` do produto em `js/produtos.js`.

Enquanto o campo `stripe` estiver vazio, o botão mostra "Em breve".

**Testem primeiro em Test mode**, com o cartão de teste `4242 4242 4242 4242`. Só depois passem para o modo real.

## 3. Pôr o site online (grátis)

A forma mais simples é o **Netlify Drop**: https://app.netlify.com/drop. Arrastem a pasta `site` para a página e o site fica online logo, com um endereço `.netlify.app`.

Mais tarde podem ligar um domínio `.pt` (por volta de 10 €/ano). Alternativas: Cloudflare Pages ou GitHub Pages.

## 4. Antes da primeira venda real

- [ ] Abrir atividade nas Finanças (Portal das Finanças → Início de atividade). Confirmem o CAE e o regime de IVA com um contabilista.
- [ ] Preencher os `[ ]` em `termos.html` e o NIF no rodapé de `index.html`.
- [ ] Aderir ao Livro de Reclamações Eletrónico (obrigatório para quem vende).
- [ ] Trocar o email e o Instagram (`ola@bicharoco.pt`, `@bicharoco`) pelos vossos.
- [ ] Tirar fotos dos produtos (fundo liso, luz natural, quadradas) e pô-las em `img/`.

## Contas rápidas (porta-chaves a 6 €)

| | |
|---|---|
| Filamento (~8 g de PLA a ~20 €/kg) | 0,16 € |
| Eletricidade | ~0,05 € |
| Argola | ~0,10 € |
| Comissão Stripe (cartão europeu, ~1,5 % + 0,25 €) | ~0,34 € |
| **Lucro por peça** | **~5,35 €** |

O envio é pago pelo cliente no checkout. As comissões do Stripe mudam, por isso confirmem os valores atuais em stripe.com/pt/pricing.
