# FarmaVida — organização MVC

A aplicação atual foi reorganizada mantendo as URLs existentes. Não é necessário importar outro banco, instalar um framework ou configurar reescrita de URLs.

```text
FarmaVida/
├── app/
│   ├── Controllers/        # Requisições, autenticação, validações e respostas
│   ├── Models/             # Consultas SQL e operações no banco
│   ├── Views/              # HTML, apresentação e JavaScript das telas
│   │   ├── auth/
│   │   ├── clientes/
│   │   ├── dashboard/
│   │   ├── errors/
│   │   ├── layouts/        # Cabeçalho e rodapé compartilhados
│   │   ├── loja/
│   │   ├── pedidos/
│   │   ├── produtos/
│   │   ├── relatorios/
│   │   └── vendas/
│   ├── Core/              # Classes base Controller e Model
│   └── Support/           # JSON, uploads, sessão da loja e geração de PIX
├── assets/css/style.css   # Estilos compartilhados
├── config/config.php      # Configuração central, sessão e conexão PDO
├── scripts/               # Utilitários de manutenção do banco
├── uploads/               # Imagens existentes e novos uploads
├── loja/                  # Entradas públicas da loja
├── bootstrap.php          # Inicialização e carregamento automático das classes
├── config.php             # Compatibilidade com o caminho anterior
└── *.php                  # Entradas públicas que chamam os Controllers
```

## Fluxo das requisições

Por exemplo, `produtos.php` carrega o `bootstrap.php` e chama `ProdutoController::index()`. O Controller verifica a sessão e recebe os filtros, usa `ProdutoModel` para consultar o banco e passa os resultados para `app/Views/produtos/index.php`. A View inclui os layouts usando caminhos absolutos do sistema de arquivos.

As APIs seguem o mesmo fluxo: `api.php` chama `ApiController` e `ApiModel`; `loja/loja_api.php` chama `LojaApiController` e `LojaModel`. Os nomes dos endpoints, os parâmetros e as respostas JSON foram preservados. Os Models retornam resultados ou statements PDO; os Controllers continuam responsáveis pela execução com parâmetros, montagem das respostas e coordenação das transações.

## Executar

Com o Apache e o MySQL do XAMPP iniciados, acesse o caminho da pasta `FarmaVida` configurado no seu servidor, normalmente:

`http://localhost:9999/grupo-SA-farmacia-/FarmaVida/`

O Apache deste XAMPP está configurado na porta `9999`. Se você alterar essa configuração, adapte a porta na URL.

Configure o banco e os pagamentos em `config/config.php`. A loja permanece em `loja/index.php`. O projeto usa PHP 8.2 no ambiente XAMPP atual, com PDO MySQL; a loja também usa mbstring.

Para conferir as telas públicas com o servidor embutido do PHP, execute dentro de `FarmaVida`:

```sh
php -S 127.0.0.1:8098
```

O servidor embutido não interpreta `.htaccess`. Para o Apache, as pastas `app`, `config` e `scripts` possuem regras que impedem acesso direto pela web. Os utilitários antigos `fix_banco.php` e `limpar_duplicados.php` continuam disponíveis por compatibilidade e executam seus arquivos em `scripts`; acessá-los altera o banco.

## Onde alterar

- Layout ou conteúdo de uma tela: `app/Views/`.
- Tratamento de formulário, permissões, redirecionamento ou endpoint: `app/Controllers/`.
- Consulta ou operação SQL: `app/Models/`.
- Estilos compartilhados: `assets/css/style.css`.
- Configuração do banco e dos pagamentos: `config/config.php`.

Os arquivos de entrada na raiz e em `loja` devem permanecer pequenos. As pastas `Entrega` e `DOCS`, fora da aplicação, preservam os arquivos históricos e a documentação original.

## Validação da reorganização

A sintaxe dos 69 arquivos PHP foi conferida. Foram verificadas 15 respostas HTTP para telas públicas, redirecionamentos, API sem sessão e CSS, além de 6 verificações pelo Apache, incluindo as regras de acesso às pastas internas. As consultas das telas autenticadas não puderam ser validadas porque o MySQL local retornou `Unknown database 'farmavida'`. O esquema original está em `../DOCS/BD/farmavida.sql`; a reorganização não importa nem altera esse banco por conta própria.
