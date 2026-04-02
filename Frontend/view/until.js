const nomeProduto = "Dipirona";  // const = imutável (não pode mudar)
let quantidadeEstoque = 50;      // let = mutável (pode mudar)
const produtoAtivo = true;       // const = imutável, true = booleano


 function saudarCliente(nomeUsuario){
    return "Olá, " + nomeUsuario + ", Seja Bem Vindo na FarmaVida.";

 }

 let nomeUsuario = prompt("Digite seu nome: ");

alert(saudarCliente(nomeUsuario));

 function formatarMoeda(valor){
    return "R$ " + valor.tofixed(2)
 }

 let valorUsuario = parseFloat(prompt("Digite o valor (ex: 15.5): "));
alert (formatarMoeda(valorUsuario));

 function calcularDesconto(precoOriginal, isFuncionario) {
    let precoFinal;
    
    if (isFuncionario) {
        precoFinal = precoOriginal * 0.7;
    } else {
        precoFinal = precoOriginal;
    }
    
    return "R$ " + precoFinal.toFixed(2);
}

const produtos = [
    { id: 1, nome: "Tadala", preco: 2500, categorias: ["Remedio"] },
    { id: 2, nome: "Prudence TAM:GG", preco: 150, categorias: ["Acessórios"] },
    { id: 3, nome: "Paracetamol", preco: 200, categorias: ["Remedio"] }
];

let opcao = prompt("Digite o ID do produto (1, 2 ou 3):");
let produtoEscolhido = produtos[opcao - 1];

alert("Produto: " + produtoEscolhido.nome + "\nPreço: R$ " + produtoEscolhido.preco);

function validarSenha(senha) {
    return senha.length >= 8 && senha !== "12345678" && senha !== "senha";
}

let senhaUsuario;

do {
    senhaUsuario = prompt("Digite sua senha:");
    
    if (!validarSenha(senhaUsuario)) {
        alert("Senha inválida! Tente novamente.");
    }
    
} while (!validarSenha(senhaUsuario));

alert("✅ Senha cadastrada com sucesso!");

function fecharCarrinho(valorProduto, quantidade, valorFrete) {
    let totalProdutos = valorProduto * quantidade;
    
    if (totalProdutos > 200) {
        return totalProdutos;
    } else {
        return totalProdutos + valorFrete;
    }
}


let valorProduto = parseFloat(prompt("Digite o valor do produto:"));
let quantidade = parseInt(prompt("Digite a quantidade:"));
let valorFrete = parseFloat(prompt("Digite o valor do frete:"));


let total = fecharCarrinho(valorProduto, quantidade, valorFrete);

alert("Total da compra: R$ " + total.toFixed(2));

function validarTamanhoCPF(cpf) {
    let cpfLimpo = cpf.trim();
    
    if (cpfLimpo.length === 11 && !isNaN(cpfLimpo)) {
        return true;
    } else {
        return false;
    }
}

let cpfUsuario = prompt("Digite seu cpf(Apenas numeros):"); 

function validarCampoVazio(valor) {
    return !(valor === null || valor === undefined || valor.trim() === "");
}

let nome = prompt("Digite seu nome:");

if (validarCampoVazio(nome)) {
    alert("Nome válido: " + nome);
} else {
    alert("Campo obrigatório!");
}