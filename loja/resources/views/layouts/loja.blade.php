<!doctype html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('titulo', 'Bicharoco · Coisinhas impressas em 3D')</title>
  <meta name="description" content="Porta-chaves com nome, bichinhos de secretária e outras coisinhas impressas em 3D em Portugal. Feitas por encomenda, enviadas pelos CTT.">
  <link rel="icon" href="{{ asset('img/bicharoco.svg') }}" type="image/svg+xml">
  <meta property="og:title" content="Bicharoco · Coisinhas impressas em 3D">
  <meta property="og:description" content="Coisas pequenas, feitas camada a camada, com imaginação a mais.">
  <meta property="og:image" content="{{ asset('img/bicharoco.svg') }}">
  @stack('head')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bagel+Fat+One&family=Figtree:wght@400;500;700;800&display=swap">
  <link rel="stylesheet" href="{{ asset('css/loja.css') }}">
</head>
<body>

<header class="topo">
  <div class="contentor">
    <a class="marca" href="{{ route('loja') }}">
      <img src="{{ asset('img/bicharoco.svg') }}" alt="">
      <span>bicharoco<b>.</b></span>
    </a>
    <nav class="menu" aria-label="Principal">
      <a class="menu-loja" href="{{ route('loja') }}#loja">Loja</a>
      <a href="{{ route('loja') }}#como">Como funciona</a>
      <a href="{{ route('loja') }}#sobre">Quem somos</a>
      <a href="{{ route('loja') }}#perguntas">Perguntas</a>
    </nav>
  </div>
</header>

@yield('conteudo')

<footer class="rodape">
  <div class="contentor">
    <div>
      <a class="marca" href="{{ route('loja') }}"><img src="{{ asset('img/bicharoco.svg') }}" alt=""><span>bicharoco<b>.</b></span></a>
      <p style="margin-top:12px">Coisinhas impressas em 3D, em Portugal.</p>
    </div>
    <div>
      <h3>Contacto</h3>
      <ul>
        <li>Email: {{ config('loja.email') }}</li>
        <li>Instagram: <a href="https://instagram.com/{{ config('loja.instagram') }}" rel="noopener">{{ '@'.config('loja.instagram') }}</a></li>
      </ul>
    </div>
    <div>
      <h3>Informação</h3>
      <ul>
        <li><a href="{{ route('termos') }}">Termos, envios e devoluções</a></li>
        <li><a href="https://www.livroreclamacoes.pt" rel="noopener">Livro de Reclamações</a></li>
      </ul>
    </div>
    <p class="legal">© {{ date('Y') }} Bicharoco · NIF {{ config('loja.nif') }} · Pagamentos processados pelo Stripe.</p>
  </div>
</footer>

</body>
</html>
