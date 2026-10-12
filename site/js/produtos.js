// Catálogo da loja. Para adicionar ou mudar um produto, edita só esta lista.
//
// nome       – nome que aparece no cartão
// preco      – em euros, com ponto decimal (ex.: 6 ou 6.5)
// descricao  – uma frase curta
// cor        – cor de fundo do cartão: "limao", "chiclete", "ceu", "menta" ou "laranja"
// imagem     – caminho da foto (ex.: "img/porta-chaves.jpg"). Deixa "" até teres foto.
// stripe     – link do Stripe Payment Link (https://buy.stripe.com/...). Deixa "" e o botão mostra "Em breve".
// etiqueta   – opcional, um selo pequeno (ex.: "Personalizável", "Novo")

const PRODUTOS = [
  {
    nome: "Porta-chaves com nome",
    preco: 6,
    descricao: "Escreves o nome no checkout, nós imprimimos.",
    cor: "limao",
    imagem: "",
    stripe: "",
    etiqueta: "Personalizável",
  },
  {
    nome: "Bicharoco de secretária",
    preco: 9,
    descricao: "Segura o telemóvel e sorri o dia todo.",
    cor: "chiclete",
    imagem: "",
    stripe: "",
    etiqueta: "Mascote",
  },
  {
    nome: "Bases de azulejo (4)",
    preco: 12,
    descricao: "Padrão de azulejo português para pousar o café.",
    cor: "ceu",
    imagem: "",
    stripe: "",
    etiqueta: "",
  },
  {
    nome: "Organizador de cabos (6)",
    preco: 5,
    descricao: "Acaba com o ninho de fios atrás da secretária.",
    cor: "menta",
    imagem: "",
    stripe: "",
    etiqueta: "",
  },
  {
    nome: "Vaso bicho mini",
    preco: 8,
    descricao: "Para um cato pequeno com cara de quem manda.",
    cor: "laranja",
    imagem: "",
    stripe: "",
    etiqueta: "Novo",
  },
  {
    nome: "Placa de nome para porta",
    preco: 10,
    descricao: "Para o quarto, o escritório ou a casota do cão.",
    cor: "limao",
    imagem: "",
    stripe: "",
    etiqueta: "Personalizável",
  },
];
