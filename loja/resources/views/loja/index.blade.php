@extends('layouts.loja')

@section('conteudo')
<main>
  <section class="hero">
    <div class="contentor">
      <div>
        <h1>Coisinhas pequenas com <em>imaginação</em> a mais.</h1>
        <p>Porta-chaves com o teu nome, bichos de secretária e outras coisas úteis e esquisitas. Impressas em 3D por nós, em Portugal.</p>
        <div class="acoes">
          <a class="botao" href="#loja">Ver a loja</a>
          <a class="botao botao-claro" href="#como">Como funciona</a>
        </div>
      </div>
      <img class="hero-bicho" src="{{ asset('img/bicharoco.svg') }}" alt="O Bicharoco, a nossa mascote amarela com antenas">
    </div>
  </section>

  @php
    $faixa = '<span>Feito por encomenda <i>✦</i></span><span>Envio pelos CTT <i>✦</i></span><span>Pagamento seguro com Stripe <i>✦</i></span><span>Personalizamos com o teu nome <i>✦</i></span><span>Feito em Portugal <i>✦</i></span>';
  @endphp
  <div class="faixa" aria-hidden="true">
    <div class="faixa-pista">{!! str_repeat($faixa, 4) !!}</div>
  </div>

  <section class="seccao" id="loja">
    <div class="contentor">
      <div class="seccao-titulo">
        <h2>A loja</h2>
        <p>Cada peça sai da nossa impressora quando a encomendas. Nos produtos personalizáveis escreves o nome no pagamento.</p>
      </div>
      <div class="grelha">
        @forelse ($produtos as $p)
          <article class="produto">
            <div class="produto-foto cor-{{ $p->color->value }}">
              @if ($p->imageUrl())
                <img src="{{ $p->imageUrl() }}" alt="{{ $p->name }}" loading="lazy">
              @else
                <img class="produto-placeholder" src="{{ asset('img/bicharoco.svg') }}" alt="">
              @endif
              @if ($p->badge)
                <span class="etiqueta">{{ $p->badge }}</span>
              @endif
            </div>
            <div class="produto-info">
              <h3>{{ $p->name }}</h3>
              <p>{{ $p->description }}</p>
              <div class="produto-rodape">
                <span class="preco">{{ rtrim(rtrim(number_format($p->price_cents / 100, 2, ',', ''), '0'), ',') }}<small> €</small></span>
                @if ($p->stripe_payment_link_url)
                  <a class="botao" href="{{ $p->stripe_payment_link_url }}">Comprar</a>
                @else
                  <span class="botao botao-off">Em breve</span>
                @endif
              </div>
            </div>
          </article>
        @empty
          <p>Estamos a aquecer a impressora. Volta daqui a pouco!</p>
        @endforelse
      </div>
    </div>
  </section>

  <section class="seccao seccao-suave" id="como">
    <div class="contentor">
      <div class="seccao-titulo"><h2>Como funciona</h2></div>
      <ol class="passos">
        <li><h3>Escolhes</h3><p>Carregas em "Comprar" e pagas no checkout seguro do Stripe.</p></li>
        <li><h3>Imprimimos</h3><p>A tua peça é impressa camada a camada. Demora 1 a 3 dias úteis.</p></li>
        <li><h3>Enviamos</h3><p>Vai pelos CTT para qualquer morada em Portugal. Recebes o código de seguimento por email.</p></li>
      </ol>
    </div>
  </section>

  <section class="seccao" id="sobre">
    <div class="contentor sobre">
      <div class="sobre-cartao"><img src="{{ asset('img/bicharoco.svg') }}" alt=""></div>
      <div class="sobre-texto">
        <div class="seccao-titulo" style="margin-bottom:0"><h2>Quem somos</h2></div>
        <p>Somos dois amigos com uma impressora 3D e demasiadas ideias. Começámos a imprimir coisas para nós e para a família, e agora imprimimos para ti.</p>
        <p>O Bicharoco é a nossa mascote: um bicho amarelo, meio torto e sempre bem-disposto. É assim que queremos que sejam as nossas peças. Pequenas, úteis e com um bocadinho de imaginação a mais.</p>
      </div>
    </div>
  </section>

  <section class="seccao seccao-suave" id="perguntas">
    <div class="contentor">
      <div class="seccao-titulo"><h2>Perguntas</h2></div>
      <div class="perguntas">
        <details>
          <summary>Quanto custa o envio?</summary>
          <p>O envio pelos CTT custa {{ number_format(config('loja.envio_cents') / 100, 2, ',', '') }} € e aparece no checkout antes de pagares.</p>
        </details>
        <details>
          <summary>Como digo o nome que quero na peça?</summary>
          <p>Nos produtos personalizáveis, o checkout tem um campo "Nome a imprimir". Confirma bem as letras antes de pagar!</p>
        </details>
        <details>
          <summary>Como posso pagar?</summary>
          <p>Com cartão e, quando disponível no checkout, MB WAY ou Multibanco. O pagamento é feito no Stripe; nós nunca vemos os dados do teu cartão.</p>
        </details>
        <details>
          <summary>De que material são as peças?</summary>
          <p>Usamos PLA, um plástico feito a partir de amido de milho. É leve e resistente, mas não gosta de calor forte: não deixes as peças dentro do carro ao sol.</p>
        </details>
        <details>
          <summary>Posso devolver?</summary>
          <p>Produtos não personalizados podem ser devolvidos até 14 dias depois de os receberes. Peças com nome são feitas só para ti e não têm devolução, a não ser que cheguem com defeito. Vê os <a href="{{ route('termos') }}">termos e envios</a>.</p>
        </details>
        <details>
          <summary>Fazem peças por medida?</summary>
          <p>Sim. Manda-nos uma mensagem no Instagram com a ideia e dizemos-te se é possível e quanto custa.</p>
        </details>
      </div>
    </div>
  </section>
</main>
@endsection
