@extends('layouts.loja')

@section('titulo', 'Termos, envios e devoluções · Bicharoco')

@section('conteudo')
<main class="pagina">
  <div class="contentor">
    <h1>Termos, envios e devoluções</h1>

    <p class="nota">Rascunho. Preencham os campos entre [ ] e confirmem o texto com as Finanças ou um contabilista antes de publicar.</p>

    <h2>Quem vende</h2>
    <p>[Nome completo do titular da atividade], NIF {{ config('loja.nif') }}, com morada em [morada]. Contacto: {{ config('loja.email') }}.</p>

    <h2>Encomendas e pagamento</h2>
    <p>Os preços incluem todos os impostos aplicáveis. O pagamento é feito no checkout do Stripe. Não guardamos os dados do teu cartão.</p>

    <h2>Produção e envio</h2>
    <p>Cada peça é impressa por encomenda em 1 a 3 dias úteis. Enviamos pelos CTT para Portugal continental e ilhas. O custo do envio aparece no checkout antes do pagamento.</p>

    <h2>Devoluções</h2>
    <p>Tens 14 dias a contar da receção para devolver produtos não personalizados, sem precisares de dar motivo. Escreve-nos para {{ config('loja.email') }}. Devolvemos o valor pago, incluindo o envio original, até 14 dias depois de recebermos a peça de volta. Os portes da devolução ficam a teu cargo.</p>
    <p>Produtos personalizados (por exemplo, com o teu nome) são feitos à tua medida e não têm direito de livre resolução. Se chegarem partidos ou com defeito, trocamos ou devolvemos o dinheiro.</p>

    <h2>Reclamações</h2>
    <p>Podes usar o <a href="https://www.livroreclamacoes.pt" rel="noopener">Livro de Reclamações Eletrónico</a>. Em caso de litígio, podes recorrer a uma entidade de resolução alternativa de litígios de consumo: [nome da entidade, por exemplo o centro de arbitragem da vossa zona], mais informação em www.consumidor.gov.pt.</p>

    <h2>Dados pessoais</h2>
    <p>Usamos o teu nome, email e morada apenas para produzir e enviar a encomenda. Os pagamentos são tratados pelo Stripe. Não vendemos nem partilhamos os teus dados com mais ninguém.</p>
  </div>
</main>
@endsection
