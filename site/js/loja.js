// Desenha os cartões de produto a partir de PRODUTOS (js/produtos.js).
(function () {
  const grelha = document.getElementById("grelha");
  if (!grelha || typeof PRODUTOS === "undefined") return;

  const euro = new Intl.NumberFormat("pt-PT", { minimumFractionDigits: 0, maximumFractionDigits: 2 });

  PRODUTOS.forEach(function (p) {
    const cartao = document.createElement("article");
    cartao.className = "produto";

    const foto = document.createElement("div");
    foto.className = "produto-foto cor-" + (p.cor || "limao");
    if (p.imagem) {
      const img = document.createElement("img");
      img.src = p.imagem;
      img.alt = p.nome;
      img.loading = "lazy";
      foto.appendChild(img);
    } else {
      const bicho = document.createElement("img");
      bicho.src = "img/bicharoco.svg";
      bicho.alt = "";
      bicho.className = "produto-placeholder";
      foto.appendChild(bicho);
    }
    if (p.etiqueta) {
      const tag = document.createElement("span");
      tag.className = "etiqueta";
      tag.textContent = p.etiqueta;
      foto.appendChild(tag);
    }

    const info = document.createElement("div");
    info.className = "produto-info";

    const nome = document.createElement("h3");
    nome.textContent = p.nome;

    const desc = document.createElement("p");
    desc.textContent = p.descricao;

    const rodape = document.createElement("div");
    rodape.className = "produto-rodape";

    const preco = document.createElement("span");
    preco.className = "preco";
    preco.innerHTML = euro.format(p.preco) + "<small> €</small>";

    let botao;
    if (p.stripe) {
      botao = document.createElement("a");
      botao.href = p.stripe;
      botao.className = "botao";
      botao.textContent = "Comprar";
    } else {
      botao = document.createElement("span");
      botao.className = "botao botao-off";
      botao.textContent = "Em breve";
    }

    rodape.append(preco, botao);
    info.append(nome, desc, rodape);
    cartao.append(foto, info);
    grelha.appendChild(cartao);
  });

  const ano = document.getElementById("ano");
  if (ano) ano.textContent = new Date().getFullYear();
})();
