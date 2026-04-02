// 1. Variáveis iniciais
const nomeProduto = "Dipirona";  
let quantidadeEstoque = 50;      
const produtoAtivo = true;       

// 2. Saudação ao cliente
function saudarCliente(nomeUsuario) {
    return "Olá, " + nomeUsuario + ", Seja Bem Vindo na FarmaVida.";
}

let nomeUsuario = prompt("Digite seu nome: ");
alert(saudarCliente(nomeUsuario));

// 3. Formatação em moeda (CORRIGIDO)
function formatarMoeda(valor) {
    return "R$ " + valor.toFixed(2);  
}

let valorUsuario = parseFloat(prompt("Digite o valor (ex: 15.5): "));
alert(formatarMoeda(valorUsuario));

// 4. Desconto para funcionário
function calcularDesconto(precoOriginal, isFuncionario) {
    let precoFinal;
    
    if (isFuncionario) {
        precoFinal = precoOriginal * 0.7;
    } else {
        precoFinal = precoOriginal;
    }
    
    return "R$ " + precoFinal.toFixed(2);
}

// 5. Lista de produtos
const produtos = [
    { id: 1, nome: "Tadala", preco: 2500, categorias: ["Remedio"] },
    { id: 2, nome: "Prudence TAM:GG", preco: 150, categorias: ["Acessórios"] },
    { id: 3, nome: "Paracetamol", preco: 200, categorias: ["Remedio"] }
];

let opcao = prompt("Digite o ID do produto (1, 2 ou 3):");
let produtoEscolhido = produtos[opcao - 1];

alert("Produto: " + produtoEscolhido.nome + "\nPreço: R$ " + produtoEscolhido.preco);

// 6. Validador de senha
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

alert(" Senha cadastrada com sucesso!");

// 7. Fechar carrinho
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

// 8. Validador de CPF
function validarTamanhoCPF(cpf) {
    let cpfLimpo = cpf.trim();
    
    if (cpfLimpo.length === 11 && !isNaN(cpfLimpo)) {
        return true;
    } else {
        return false;
    }
}

let cpfUsuario = prompt("Digite seu CPF (Apenas números):");
let cpfValido = validarTamanhoCPF(cpfUsuario);

if (cpfValido) {
    alert(" CPF válido!");
} else {
    alert(" CPF inválido! Deve conter exatamente 11 números.");
}

// 9. Validador de campo vazio
function validarCampoVazio(valor) {
    return !(valor === null || valor === undefined || valor.trim() === "");
}

let nomeCliente = prompt("Digite seu nome:"); 
if (validarCampoVazio(nomeCliente)) {
    alert("Nome válido: " + nomeCliente);
} else {
    alert("Campo obrigatório!");
}

// 10. Formatação em moeda (versão BRL)
function formatarMoedaBRL(valor) {
    return "R$ " + valor.toFixed(2);
}

// 11. Gerar resumo
function gerarResumo(nomeCliente, totalCompra) {
    let valorFormatado = formatarMoedaBRL(totalCompra);
    return "Cliente: " + nomeCliente + ", Total a Pagar: " + valorFormatado;
}


let nomeClienteResumo = prompt("Digite o nome do cliente:");  
let totalCompra = parseFloat(prompt("Digite o total da compra:"));

alert(gerarResumo(nomeClienteResumo, totalCompra));