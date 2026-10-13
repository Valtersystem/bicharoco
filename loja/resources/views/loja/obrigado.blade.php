@extends('layouts.loja')

@section('titulo', 'Obrigado! · Bicharoco')
@push('head')<meta name="robots" content="noindex">@endpush

@section('conteudo')
<main class="pagina">
  <div class="contentor">
    <img src="{{ asset('img/bicharoco.svg') }}" alt="" style="width:140px">
    <h1>Obrigado! A impressora já está a aquecer.</h1>
    <p>Recebemos a tua encomenda. Vais receber um email do Stripe com o recibo.</p>
    <p>Se pagaste por Multibanco, a encomenda avança assim que o pagamento for confirmado.</p>
    <p>Imprimimos a tua peça em 1 a 3 dias úteis e enviamos pelos CTT. Quando sair, mandamos-te o código de seguimento.</p>
    <p>Alguma dúvida? Escreve para {{ config('loja.email') }} ou fala connosco no Instagram {{ '@'.config('loja.instagram') }}.</p>
    <p><a class="botao" href="{{ route('loja') }}">Voltar à loja</a></p>
  </div>
</main>
@endsection
