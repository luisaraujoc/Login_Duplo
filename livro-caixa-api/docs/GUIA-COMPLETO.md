# Guia Completo da API — Livro Caixa (Laravel)

Este documento explica **tudo** sobre o backend novo, do zero, assumindo que você nunca usou Laravel na vida. A ideia é que você consiga ler de cima pra baixo (ou pular direto pra seção que precisa) e entender exatamente onde cada coisa do sistema antigo foi parar, e onde mexer se quiser mudar alguma regra.

O sistema agora é dividido em **dois projetos separados**, que moram em pastas diferentes dentro do mesmo repositório:

- **`livro-caixa-api`** (este projeto) — o "cérebro". Não tem nenhuma tela. Só recebe pedidos (ex: "me dá a lista de lançamentos de julho") e devolve dados em formato JSON. É sobre ele que este documento fala.
- **`livro-caixa-web`** — as telas. Um projeto React separado que consome a API acima e mostra tudo bonitinho no navegador. Documentado à parte (ver `livro-caixa-web/README.md`).

No sistema antigo (o PHP puro, `Login_Duplo`), cada arquivo `.php` misturava três coisas ao mesmo tempo: a consulta no banco, a regra de negócio (cálculo de saldo, validação) e o HTML da tela. Isso significava que pra mudar uma regra de cálculo, às vezes você tinha que procurar em 3 ou 4 arquivos diferentes que faziam a mesma conta de jeitos ligeiramente diferentes (é literalmente o que acontecia — compare `index.php`, `filtrarpordata.php` e `filtrarporfolha.php` no projeto antigo: cada um tinha sua própria cópia, levemente diferente, da lógica de saldo).

Agora essas três coisas moram em lugares separados e cada regra existe **uma vez só**:

| Antes (PHP) | Agora |
|---|---|
| Consulta no banco espalhada em cada arquivo `.php` | `app/Models/*.php` (Eloquent) |
| Regra de negócio (cálculo de saldo, validação) misturada no meio do HTML | `app/Http/Controllers/Api/*.php` e `app/Services/*.php` |
| HTML da tela | Não existe mais aqui — está 100% no projeto `livro-caixa-web` |

---

## Sumário

1. [Conceitos básicos do Laravel](#1-conceitos-básicos-do-laravel)
2. [O que você precisa instalar](#2-o-que-você-precisa-instalar)
3. [Como rodar o projeto, passo a passo](#3-como-rodar-o-projeto-passo-a-passo)
4. [Estrutura de pastas — onde fica cada coisa](#4-estrutura-de-pastas--onde-fica-cada-coisa)
5. [O banco de dados, tabela por tabela](#5-o-banco-de-dados-tabela-por-tabela)
6. [Como funciona o "login duplo" agora](#6-como-funciona-o-login-duplo-agora)
7. [Todas as rotas da API, uma por uma](#7-todas-as-rotas-da-api-uma-por-uma)
8. [Onde mora cada lógica de negócio](#8-onde-mora-cada-lógica-de-negócio)
9. [Tabela de conversão: arquivo antigo → arquivo novo](#9-tabela-de-conversão-arquivo-antigo--arquivo-novo)
10. [Receitas: como alterar as coisas mais comuns](#10-receitas-como-alterar-as-coisas-mais-comuns)
11. [Glossário de termos do Laravel](#11-glossário-de-termos-do-laravel)
12. [Comandos do dia a dia (Artisan)](#12-comandos-do-dia-a-dia-artisan)
13. [Testando a API na mão, sem o site](#13-testando-a-api-na-mão-sem-o-site)
14. [Erros comuns e o que significam](#14-erros-comuns-e-o-que-significam)
15. [O que ainda falta fazer](#15-o-que-ainda-falta-fazer)

---

## 1. Conceitos básicos do Laravel

Antes de entrar no código, alguns conceitos que vão aparecer o tempo todo. Não precisa decorar — volte aqui sempre que esquecer o que uma palavra significa (tem também um [glossário](#11-glossário-de-termos-do-laravel) mais pra frente, mais curto e direto).

### O caminho de um pedido (request)

No PHP antigo, quando alguém abria `index.php` no navegador, o PHP rodava aquele arquivo de cima a baixo: conectava no banco, buscava os dados, e ia imprimindo HTML no meio do código. Um arquivo, um monte de responsabilidades.

No Laravel, um pedido percorre um caminho com paradas bem definidas. Por exemplo, quando o site pede "me dá os lançamentos de julho" (`GET /api/movements?month=7&year=2026`), acontece isto, nesta ordem:

1. **Rota** (`routes/api.php`) — o Laravel olha a URL pedida e decide: "isso aqui é uma chamada pro método `index` do `MovementController`".
2. **Middleware** — antes de chegar no Controller, o pedido passa por "seguranças" que podem barrar ele. Aqui tem duas: uma que checa se a pessoa está logada (`auth:sanctum`), e outra que checa se ela já escolheu uma conta pra trabalhar (`account.selected`, ver [seção 6](#6-como-funciona-o-login-duplo-agora)).
3. **Controller** (`app/Http/Controllers/Api/MovementController.php`) — a classe que efetivamente atende o pedido. Ela lê os parâmetros (`month=7&year=2026`), decide o que fazer.
4. **Model / Service** — o Controller normalmente não fala com o banco diretamente. Ele pede pro **Model** (`app/Models/Movement.php`, que representa a tabela `movements`) ou pro **Service** (`app/Services/BalanceService.php`, que tem a lógica de cálculo de saldo) fazer o trabalho pesado.
5. **Resource** (`app/Http/Resources/MovementResource.php`) — formata a resposta antes de mandar de volta, decidindo exatamente quais campos aparecem no JSON final e com que nome.
6. **Resposta em JSON** — o navegador (ou o React) recebe algo como:

```json
{
  "data": [
    { "id": 1, "description": "Salário", "amount": 1500, "type": "credit", "running_balance": 1500, ... }
  ]
}
```

Guarde esse fluxo. **Toda** rota da API segue basicamente esse caminho: Rota → Middleware → Controller → Model/Service → Resource → JSON.

### As peças, uma a uma

- **Rota (Route)** — um "endereço". Fica em `routes/api.php`. É a lista de todos os endereços que a API responde, tipo um índice.
- **Controller** — uma classe PHP com métodos. Cada método cuida de uma ação: listar (`index`), criar (`store`), ver um só (`show`), editar (`update`), apagar (`destroy`). Ficam em `app/Http/Controllers/Api/`.
- **Model** — uma classe PHP que representa uma tabela do banco. Por exemplo, `app/Models/Movement.php` representa a tabela `movements`. Em vez de escrever SQL na mão feito no PHP antigo (`"SELECT * FROM lc_movimento WHERE..."`), você escreve `Movement::where('account_id', 1)->get()` e o Laravel monta o SQL sozinho. Isso é o **Eloquent**, o "tradutor" de PHP pra SQL do Laravel.
- **Migration** — um arquivo PHP que descreve a estrutura de uma tabela (quais colunas, tipos, chaves estrangeiras). É a "receita" que cria o banco do zero. Ficam em `database/migrations/`. Isso substitui o `dblogin.sql` do sistema antigo — em vez de um arquivo `.sql` gigante, cada tabela tem seu próprio arquivo de migration, versionado.
- **Seeder** — um arquivo PHP que insere dados de exemplo/padrão no banco (tipo as 13 categorias padrão, ou os 5 tipos de combustível). Ficam em `database/seeders/`.
- **Middleware** — código que roda **antes** do Controller, podendo barrar o pedido. Usado aqui pra checar login e pra checar se uma conta foi selecionada.
- **Request / FormRequest** — valida os dados que chegaram (ex: "o campo `email` é obrigatório e tem que ser um e-mail válido"). Se a validação falhar, o Laravel já devolve o erro sozinho, sem o Controller precisar fazer nada.
- **Resource** — formata a resposta. Decide o "molde" do JSON que sai.
- **Service** — uma classe PHP comum (não é Model nem Controller) que guarda lógica complexa que seria repetida em vários lugares. Aqui tem duas: `BalanceService` (cálculo de saldo) e `FuelEfficiencyService` (cálculo de consumo de combustível).
- **Artisan** — o "canivete suíço" de linha de comando do Laravel. Todo comando começa com `php artisan ...`. Ver [seção 12](#12-comandos-do-dia-a-dia-artisan).
- **Sanctum** — o pacote que cuida do login. Ver [seção 6](#6-como-funciona-o-login-duplo-agora).

---

## 2. O que você precisa instalar

Pra rodar o projeto na sua máquina, você precisa de:

| Ferramenta | Versão mínima | Pra que serve |
|---|---|---|
| **PHP** | 8.3 | A linguagem que o Laravel usa |
| **Composer** | 2.x | O "gerenciador de pacotes" do PHP — baixa o Laravel e as bibliotecas que o projeto usa (parecido com o que o `npm` faz no JavaScript) |
| **Node.js + npm** | 20+ | Só necessário pro projeto `livro-caixa-web` (o front). A API em si não precisa. |
| **Banco de dados** | — | Ver abaixo |

### Extensões do PHP necessárias

O PHP precisa ter estas extensões ativadas (a maioria das instalações já vem com elas, mas se der erro na hora de instalar, é aqui que olhar primeiro):

`mbstring`, `xml`, `curl`, `pdo`, `pdo_sqlite` ou `pdo_mysql`, `zip`, `bcmath`, `gd`, `intl`, `tokenizer`, `openssl`.

### Banco de dados: SQLite vs MySQL

Por padrão, o projeto está configurado pra usar **SQLite** — um banco de dados que é literalmente **um arquivo só** (`database/database.sqlite`), sem precisar instalar servidor nenhum. Isso é ótimo pra desenvolver e testar rápido.

Se você preferir usar **MySQL** (o mesmo tipo de banco que o sistema antigo usava, com o `dblogin.sql`), é só trocar algumas linhas no arquivo `.env` (explico o que é esse arquivo na próxima seção):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=livro_caixa
DB_USERNAME=root
DB_PASSWORD=
```

**Importante:** o banco novo (seja SQLite ou MySQL) **não é o mesmo banco `dblogin`** do sistema antigo. As tabelas têm nomes e estrutura diferentes (ver [seção 5](#5-o-banco-de-dados-tabela-por-tabela) e a [tabela de conversão](#9-tabela-de-conversão-arquivo-antigo--arquivo-novo)). Não dá pra simplesmente apontar pro banco antigo — os dados precisariam ser migrados manualmente de um formato pro outro. Isso não foi feito nesta etapa.

---

## 3. Como rodar o projeto, passo a passo

Abra um terminal dentro da pasta `livro-caixa-api` e rode, **nesta ordem**:

```bash
# 1. Baixa todas as bibliotecas PHP que o projeto usa (Laravel, Sanctum, etc.)
composer install

# 2. Cria o arquivo de configuração .env a partir do modelo (só se .env ainda não existir)
cp .env.example .env

# 3. Gera uma "chave secreta" única pra essa instalação (usada pra criptografia interna)
php artisan key:generate

# 4. Cria todas as tabelas no banco de dados E já popula com dados de exemplo
php artisan migrate --seed

# 5. Sobe o servidor local
php artisan serve
```

Depois do passo 5, a API está rodando em **`http://localhost:8000`**. Deixe esse terminal aberto (o servidor roda enquanto o terminal estiver aberto).

### O que o `--seed` do passo 4 criou

O comando `migrate --seed` não só cria as tabelas vazias, como também roda os **seeders** (ver [seção 4](#4-estrutura-de-pastas--onde-fica-cada-coisa)), que inserem:

- **13 categorias padrão** (Salário, Vendas, Aluguel, Alimentação, etc.) — ver `database/seeders/CategorySeeder.php`
- **5 tipos de combustível padrão** (Gasolina Comum, Etanol, Diesel S10, etc.) — ver `database/seeders/FuelProductSeeder.php`
- **1 usuário de teste**: e-mail `demo@example.com`, senha `password`
- **1 conta de exemplo** ("Conta Principal") já vinculada a esse usuário, com **1 livro** e **2 lançamentos** de exemplo

Isso é só pra você já ter algo pra ver assim que sobe o projeto. Pode apagar esse usuário/conta de teste depois, ou simplesmente criar os seus por cima.

### Rodando o front junto

Em **outro terminal** (deixe o de cima rodando), vá até a pasta `livro-caixa-web` (do lado de fora desta pasta) e rode:

```bash
npm install
npm run dev
```

Isso sobe o site em `http://localhost:5173`, já configurado pra falar com a API em `http://localhost:8000`. Abra esse endereço no navegador pra usar o sistema.

### Recomeçando do zero

Se em algum momento você quiser **apagar tudo e recomeçar** o banco de dados (por exemplo, se bagunçou os dados de teste), rode:

```bash
php artisan migrate:fresh --seed
```

⚠️ Isso **apaga todos os dados** do banco e recria do zero com os dados de exemplo. Não tem volta. Só use em desenvolvimento, nunca com dados reais em produção.

---

## 4. Estrutura de pastas — onde fica cada coisa

```
livro-caixa-api/
├── app/
│   ├── Enums/
│   │   └── CashFlowType.php          ← Os dois "tipos" possíveis: crédito ou débito
│   ├── Http/
│   │   ├── Controllers/Api/          ← Um arquivo por "assunto" (Categoria, Lançamento, Veículo...)
│   │   ├── Middleware/
│   │   │   └── EnsureAccountSelected.php  ← "Segurança" que exige uma conta selecionada
│   │   ├── Requests/Auth/            ← Regras de validação de login/cadastro
│   │   └── Resources/                ← Formato do JSON de saída de cada coisa
│   ├── Models/                       ← Um arquivo por tabela do banco
│   ├── Providers/
│   │   └── AppServiceProvider.php    ← Configurações gerais que rodam na inicialização
│   └── Services/
│       ├── BalanceService.php        ← TODA a lógica de cálculo de saldo mora aqui
│       └── FuelEfficiencyService.php ← TODA a lógica de consumo de combustível mora aqui
├── database/
│   ├── migrations/                   ← A "receita" de cada tabela (uma por arquivo)
│   └── seeders/                      ← Dados de exemplo/padrão
├── resources/views/reports/
│   └── movements.blade.php           ← O "molde" HTML/CSS dos relatórios em PDF
├── routes/
│   └── api.php                       ← A LISTA COMPLETA de endereços que a API responde
├── config/                           ← Configurações do Laravel (raramente precisa mexer)
├── bootstrap/
│   └── app.php                       ← Liga as peças: rotas, middlewares, etc.
├── .env                              ← Configurações desta instalação (senha do banco, chaves, etc. — NUNCA vai pro Git)
├── .env.example                      ← Modelo do .env, sem segredos, esse sim vai pro Git
└── composer.json                     ← Lista de bibliotecas PHP que o projeto usa
```

### Por dentro de `app/Http/Controllers/Api/`

Cada arquivo cuida de um "assunto" só. Isso é bem diferente do PHP antigo, onde `movements.php`, `index.php`, `filtrarpordata.php` e `filtrarporfolha.php` tinham cada um sua própria cópia (levemente diferente) da lógica de lançamento.

| Arquivo | Assunto |
|---|---|
| `AuthController.php` | Login, cadastro, logout, dados do usuário logado |
| `AccountController.php` | Contas (livros-caixa): listar, criar, selecionar, convidar/remover usuários |
| `BookController.php` | Livros (dentro de uma conta) |
| `CategoryController.php` | Categorias (globais, todas as contas usam as mesmas) |
| `MovementController.php` | Lançamentos — o coração do sistema |
| `VehicleController.php` | Veículos da frota |
| `FuelSupplierController.php` | Fornecedores de combustível |
| `FuelProductController.php` | Tipos de combustível |
| `NfeLinkController.php` | Links de Nota Fiscal Eletrônica |
| `RefuelingController.php` | Abastecimentos |
| `UserController.php` | Lista global de usuários (pro seletor de convite) |
| `ReportController.php` | Relatórios em PDF |

### Por dentro de `app/Models/`

| Arquivo | Tabela no banco | O que representa |
|---|---|---|
| `User.php` | `users` | Um usuário (login pessoal) |
| `Account.php` | `accounts` | Uma conta / livro-caixa |
| `Book.php` | `books` | Um "livro" físico dentro de uma conta |
| `Category.php` | `categories` | Uma categoria de lançamento |
| `Movement.php` | `movements` | Um lançamento (entrada ou saída) |
| `Vehicle.php` | `vehicles` | Um veículo da frota |
| `FuelSupplier.php` | `fuel_suppliers` | Um fornecedor/posto de combustível |
| `FuelProduct.php` | `fuel_products` | Um tipo de combustível |
| `NfeLink.php` | `nfe_links` | Um link de Nota Fiscal Eletrônica |
| `Refueling.php` | `refuelings` | Um abastecimento |

---

## 5. O banco de dados, tabela por tabela

Cada tabela tem um arquivo de **migration** correspondente em `database/migrations/`, com um comentário no topo explicando de onde ela veio no sistema antigo. Aqui vai a versão completa, tabela por tabela.

### `users` — usuários (login pessoal)

Substitui a tabela `users` antiga. Continua com a mesma ideia: um cadastro pessoal.

| Coluna | Tipo | Significado |
|---|---|---|
| `id` | número | Identificador único |
| `name` | texto | Nome do usuário |
| `email` | texto (único) | E-mail, usado pra login |
| `password` | texto | Senha (sempre guardada criptografada, nunca em texto puro) |
| `photo_path` | texto, opcional | Caminho da foto de perfil (ainda sem upload implementado no front) |
| `email_verified_at`, `remember_token` | — | Campos padrão do Laravel, uso interno |

### `accounts` — contas / livros-caixa

Substitui a tabela `lc_contass`. **Diferença importante:** a conta antiga tinha uma senha própria (`password`), mas essa senha nunca era realmente checada em lugar nenhum do código antigo — era uma falha de segurança (qualquer usuário logado conseguia entrar em qualquer conta só digitando o nome dela). Por isso essa coluna de senha **não existe mais**. Em vez disso, o acesso é controlado pela tabela `account_user`, logo abaixo.

| Coluna | Tipo | Significado |
|---|---|---|
| `id` | número | Identificador único |
| `name` | texto | Nome da conta (era `conta` no antigo) |
| `owner_name` | texto | Nome do proprietário (era `proprietario` no antigo) |

### `account_user` — quem pode acessar qual conta

**Essa tabela não existia no sistema antigo.** Ela é a peça que resolve a falha de segurança citada acima: define, de verdade, quais usuários têm acesso a quais contas.

| Coluna | Tipo | Significado |
|---|---|---|
| `account_id` | número | A conta |
| `user_id` | número | O usuário |
| `role` | texto | Papel do usuário nessa conta (hoje só existe `"owner"`, mas o campo já está pronto pra ter outros papéis no futuro, tipo "visualizador") |

### `books` — livros (dentro de uma conta)

Substitui a tabela `lc_livros`. **Diferença importante:** no sistema antigo, `lc_livros` existia como uma tabelinha solta, mas o número do livro em cada lançamento (`lc_movimento.idlivro`) era só um número guardado ali, **sem nenhuma ligação de verdade** (chave estrangeira) com essa tabela — o PHP calculava o livro atual fazendo `MAX(idlivro)` na hora, sem checar se aquele número existia em `lc_livros`. Agora a ligação é real: cada lançamento aponta pra uma linha de verdade em `books`, e cada livro pertence a uma conta específica (livros não são compartilhados entre contas).

| Coluna | Tipo | Significado |
|---|---|---|
| `account_id` | número | A qual conta esse livro pertence |
| `number` | número | O número do livro (1, 2, 3...) — único dentro da mesma conta |
| `label` | texto, opcional | Um apelido pro livro, se quiser (ex: "Livro 2024") |

### `categories` — categorias de lançamento

Substitui a tabela `lc_cat`. **Ficam globais** (compartilhadas por todas as contas) porque é assim que o sistema antigo também funcionava — a tabela `lc_cat` nunca teve uma coluna ligando categoria a conta, e a tela de categorias sempre listava todas, pra qualquer conta.

| Coluna | Tipo | Significado |
|---|---|---|
| `name` | texto | Nome da categoria (era `nome`) |
| `type` | `credit` ou `debit` | Se é categoria de entrada ou de saída (era `operacao`, com os valores `"credito"`/`"debito"`) |

### `movements` — lançamentos

Substitui a tabela `lc_movimento` — o coração do sistema antigo e do novo. **Diferenças importantes:**

- As colunas `dia`, `mes`, `ano` do antigo **não existem mais** — eram redundantes, dá pra tirar dia/mês/ano direto da data completa (`movement_date`). Isso evita o tipo de bug que existia no antigo, onde às vezes `dia/mes/ano` e `datamov` podiam ficar dessincronizados.
- `data_lancamento` (a data/hora em que o lançamento foi *inserido no sistema*, diferente da data do lançamento em si) virou o campo padrão `created_at` do Laravel.

| Coluna | Tipo | Significado |
|---|---|---|
| `account_id` | número | De qual conta é esse lançamento |
| `book_id` | número | De qual livro (aponta pra `books`) |
| `category_id` | número, opcional | Categoria do lançamento (pode ficar em branco) |
| `page_number` | número | O número da folha dentro do livro (era `folha`) |
| `type` | `credit` ou `debit` | Entrada ou saída (era `tipo`, com `1`/`0`) |
| `description` | texto | Descrição do lançamento (era `descricao`) |
| `amount` | número decimal (2 casas) | Valor (era `valor`, guardado como `float` — trocado pra `decimal` aqui porque `float` pode causar erros de arredondamento em dinheiro) |
| `movement_date` | data | A data do lançamento (era `datamov`) |
| `created_at` | data/hora | Quando o registro foi criado no sistema (era `data_lancamento`) |

### `vehicles`, `fuel_suppliers`, `fuel_products`, `nfe_links`, `refuelings` — módulo Frota

Essas cinco tabelas **nunca tiveram tela nenhuma** no sistema antigo — existiam só no arquivo `dblogin.sql` (como `veiculos`, `fornecedores`, `produtos`, `linksnfe`, `abastecimentos`), sem nenhum `.php` que as usasse. Eram um módulo de controle de frota e consumo de combustível que ficou pela metade. Essa reconstrução **terminou o módulo**, criando as telas que nunca existiram.

São **globais** (não pertencem a uma conta específica) porque é assim que estavam desenhadas no `dblogin.sql` original — nenhuma dessas tabelas tinha coluna de conta.

**`vehicles`** (era `veiculos`):

| Coluna | Significado |
|---|---|
| `plate` | Placa (era `placa`) |
| `brand`, `model` | Marca e modelo |
| `manufacture_year`, `model_year` | Ano de fabricação e ano do modelo |
| `renavam`, `chassis` | Renavam e chassi (opcionais) |

**`fuel_suppliers`** (era `fornecedores`): razão social, endereço, bairro, cidade, estado, CEP, telefone, CNPJ.

**`fuel_products`** (era `produtos`): código, nome (ex: "Gasolina Comum"), unidade de medida (ex: "L").

**`nfe_links`** (era `linksnfe`): só um link (`url`) da nota fiscal eletrônica. Existe separado do abastecimento porque uma nota fiscal pode ter vários itens/abastecimentos ligados a ela.

**`refuelings`** (era `abastecimentos`) — o registro de cada abastecimento:

| Coluna | Significado |
|---|---|
| `vehicle_id`, `fuel_supplier_id`, `fuel_product_id` | Qual veículo, fornecedor e combustível |
| `nfe_link_id` | Link da nota fiscal, se tiver |
| `invoice_number`, `access_key` | Número da nota e chave de acesso |
| `refueled_at` | Data e hora do abastecimento (juntou `data_abast` + `hora_abast` do antigo numa coluna só) |
| `odometer_km` | Quilometragem no odômetro (era `km_abast`) |
| `quantity` | Quantidade abastecida (era `quantidade`) |
| `unit_price` | Preço por unidade (era `valor_unit`) |
| `notes` | Observação (era `observacao`) |
| `target_efficiency_km_per_liter` | Meta de consumo em km/l, padrão 14.10 (era `mediaideal`) |

**Sobre a coluna `valorcompra` do antigo:** no `dblogin.sql`, essa coluna era "gerada automaticamente" pelo próprio MySQL (`quantidade * valor_unit`). Aqui, esse cálculo virou um **accessor** no Model `Refueling` (o método `totalCost()`) — ou seja, é calculado em PHP toda vez que é pedido, e não fica guardado fisicamente no banco. Isso deixa o sistema independente de qual banco de dados está usando (nem todo banco suporta coluna gerada do jeito que o MySQL faz).

---

## 6. Como funciona o "login duplo" agora

O sistema antigo tinha dois logins: primeiro o login do **usuário** (`login.php`), depois o login da **conta** (`login_c.php`). O novo sistema mantém essa ideia de "dois passos", mas do jeito certo.

### Passo 1 — Login do usuário

`POST /api/login` com e-mail e senha. Se estiver certo, o Laravel guarda uma "sessão" (imagine um crachá temporário) pro navegador, usando cookies. Esse crachá é o que o Sanctum (o pacote de autenticação) usa pra saber, nos próximos pedidos, quem está logado — sem precisar mandar e-mail/senha de novo a cada clique.

### Passo 2 — Escolher a conta

Depois de logado, o usuário pode ter acesso a uma ou mais contas (livros-caixa). A lista de quais contas ele pode acessar vem da tabela `account_user` (explicada na [seção 5](#5-o-banco-de-dados-tabela-por-tabela)) — **não existe mais senha própria da conta**. Se o usuário está vinculado à conta, ele pode escolhê-la, ponto.

`POST /api/accounts/{id}/select` marca, dentro da mesma sessão, qual conta está "ativa" agora. Isso é guardado no lado do servidor (não é algo que o navegador possa fraudar).

### O "segurança" que cobra a conta selecionada

Tem um arquivo, `app/Http/Middleware/EnsureAccountSelected.php`, que fica na porta de tudo que só faz sentido dentro de uma conta (livros e lançamentos). Se o usuário tentar acessar `/api/movements` sem ter escolhido uma conta ainda, essa rota devolve erro **409** com a mensagem `"Nenhuma conta selecionada."` — é o jeito da API dizer "espera, falta um passo antes".

Uma vez que a conta foi escolhida, esse middleware disponibiliza ela pra todo o resto do código através de `$request->currentAccount()` — é como qualquer Controller descobre "ok, estamos falando da conta X agora" sem precisar ficar checando a sessão toda hora.

### Como criar novas contas / vincular seu pai a uma

Existe a rota `POST /api/accounts`, que cria uma conta nova e automaticamente vincula quem criou como dono (`role: "owner"`) — é o equivalente ao antigo `sign-up_c.php`. Isso já está disponível na tela "Selecionar Conta" do front (`livro-caixa-web`).

Se você quiser dar acesso a uma conta já existente pra outro usuário (por exemplo, seu pai ter acesso à mesma conta que você), tem uma tela pra isso: na página "Selecionar Conta" do front, clique no ícone de engrenagem ao lado da conta. Ali dá pra ver quem já tem acesso e convidar qualquer outro usuário cadastrado no sistema.

**Não existe fluxo de convite por e-mail/token.** Como o sistema roda localmente, para um grupo pequeno e conhecido de pessoas, o "convite" é simplesmente escolher alguém na lista de usuários já cadastrados (`GET /api/users` devolve todo mundo — ver [seção 7](#7-todas-as-rotas-da-api-uma-por-uma)) e vincular na hora, sem confirmação por e-mail. Isso é intencional: não faria sentido complicar esse fluxo pra um app que só roda na rede de casa.

A conta nunca pode ficar sem nenhum usuário vinculado — a API bloqueia (`422`) a tentativa de remover o último usuário restante de uma conta.

Se preferir fazer isso na mão, direto no banco, também dá (via `php artisan tinker`, ver [seção 12](#12-comandos-do-dia-a-dia-artisan)):

```php
$conta = \App\Models\Account::find(1);
$usuario = \App\Models\User::where('email', 'pai@exemplo.com')->first();
$conta->users()->attach($usuario, ['role' => 'owner']);
```

---

## 7. Todas as rotas da API, uma por uma

Toda rota começa com `http://localhost:8000/api`. As marcadas com 🔒 exigem estar logado (`auth:sanctum`). As marcadas com 🔒🏦 exigem, além de estar logado, **ter uma conta selecionada** (passam pelo middleware `account.selected`).

A lista completa e definitiva sempre está em `routes/api.php` — este documento a explica, mas se algum dia divergir, o arquivo é quem manda.

### Autenticação

| Rota | Controller | O que faz |
|---|---|---|
| `POST /register` | `AuthController@register` | Cria um usuário novo e já loga ele. Corpo: `name`, `email`, `password`, `password_confirmation` (senha mínima 8 caracteres). |
| `POST /login` | `AuthController@login` | Loga um usuário existente. Corpo: `email`, `password`. |
| 🔒 `POST /logout` | `AuthController@logout` | Desloga (encerra a sessão). |
| 🔒 `GET /me` | `AuthController@me` | Devolve os dados do usuário logado. |
| 🔒 `PUT /me` | `AuthController@updateMe` | Atualiza nome/e-mail do usuário logado. Corpo: `name`, `email`. |

### Usuários

| Rota | Controller | O que faz |
|---|---|---|
| 🔒 `GET /users` | `UserController@index` | Lista **todos** os usuários cadastrados no sistema (id, nome, e-mail). Usada só para popular o seletor de "convidar alguém pra esta conta" — não filtra nada, então não é apropriado deixar essa rota exposta assim se o sistema um dia deixar de ser só de uso local. |

### Contas

| Rota | Controller | O que faz |
|---|---|---|
| 🔒 `GET /accounts` | `AccountController@index` | Lista as contas que o usuário logado pode acessar. |
| 🔒 `GET /accounts/current` | `AccountController@current` | Devolve a conta ativa na sessão atual (ou `null` se nenhuma foi escolhida ainda — **essa rota não exige conta selecionada**, é justamente ela que serve pra descobrir isso). |
| 🔒 `POST /accounts` | `AccountController@store` | Cria uma conta nova e vincula o usuário logado como dono. Corpo: `name`, `owner_name`. |
| 🔒 `GET /accounts/{id}/users` | `AccountController@users` | Lista quem tem acesso a essa conta (nome, e-mail, papel). Só quem já é membro da conta pode ver essa lista (erro 403 senão). |
| 🔒 `POST /accounts/{id}/users` | `AccountController@inviteUser` | Dá acesso a essa conta pra outro usuário já cadastrado. Corpo: `user_id` (obrigatório), `role` (opcional, padrão `"member"`). Devolve `422` se esse usuário já tiver acesso. |
| 🔒 `DELETE /accounts/{id}/users/{user_id}` | `AccountController@removeUser` | Remove o acesso de um usuário a essa conta. Devolve `422` se for o **último** usuário restante — uma conta nunca pode ficar órfã. |
| 🔒 `POST /accounts/{account}/select` | `AccountController@select` | Marca essa conta como ativa na sessão. Dá erro 403 se o usuário não tiver acesso a ela. |

### Categorias (globais — não pedem conta selecionada)

Segue o padrão **REST** de 5 rotas que se repete várias vezes neste documento — no Laravel isso se chama `apiResource` e é criado com uma linha só (`Route::apiResource('categories', CategoryController::class)`):

| Método + Rota | Controller | O que faz |
|---|---|---|
| 🔒 `GET /categories` | `@index` | Lista todas as categorias |
| 🔒 `POST /categories` | `@store` | Cria uma categoria. Corpo: `name`, `type` (`credit` ou `debit`) |
| 🔒 `GET /categories/{id}` | `@show` | Mostra uma categoria específica |
| 🔒 `PUT /categories/{id}` | `@update` | Atualiza. Mesmo corpo do `store` |
| 🔒 `DELETE /categories/{id}` | `@destroy` | Apaga |

### Livros (🔒🏦 — pedem conta selecionada)

Mesmo padrão de 5 rotas, agora em `/books`:

| Método + Rota | O que faz |
|---|---|
| 🔒🏦 `GET /books` | Lista os livros **da conta ativa** |
| 🔒🏦 `POST /books` | Cria um livro na conta ativa. Corpo: `number`, `label` (opcional) |
| 🔒🏦 `GET /books/{id}` | Mostra um livro (dá 404 se o livro não for da conta ativa) |
| 🔒🏦 `PUT /books/{id}` | Atualiza |
| 🔒🏦 `DELETE /books/{id}` | Apaga (dá erro se ainda tiver lançamentos usando esse livro, porque a ligação é `restrictOnDelete` — ver [seção 5](#5-o-banco-de-dados-tabela-por-tabela)) |

### Lançamentos (🔒🏦 — pedem conta selecionada)

A parte mais importante do sistema. Mesmo padrão de 5 rotas em `/movements`, **mais** uma rota extra de resumo:

| Método + Rota | Controller | O que faz |
|---|---|---|
| 🔒🏦 `GET /movements` | `@index` | Lista lançamentos **com o saldo corrente calculado** (ver abaixo) |
| 🔒🏦 `GET /movements/summary` | `@summary` | Os totais pro dashboard (saldo anterior, entradas, saídas, saldo atual) |
| 🔒🏦 `POST /movements` | `@store` | Cria um lançamento |
| 🔒🏦 `GET /movements/{id}` | `@show` | Mostra um lançamento |
| 🔒🏦 `PUT /movements/{id}` | `@update` | Atualiza |
| 🔒🏦 `DELETE /movements/{id}` | `@destroy` | Apaga |

**Corpo esperado em `POST`/`PUT`:**

```json
{
  "book_id": 1,
  "category_id": 3,
  "page_number": 1,
  "type": "credit",
  "description": "Salário de julho",
  "amount": 1500.00,
  "movement_date": "2026-07-05"
}
```

(`category_id` pode ser omitido/nulo. `type` só aceita `"credit"` ou `"debit"`.)

**Filtros aceitos em `GET /movements`** (isso substitui as três telas separadas `index.php`, `filtrarpordata.php` e `filtrarporfolha.php` do sistema antigo por **uma única rota** com filtros diferentes):

| Combinação de parâmetros | Equivalente no sistema antigo |
|---|---|
| `?month=7&year=2026` (padrão: mês/ano atual se nada for passado) | `index.php` |
| `?date_from=2026-07-01&date_to=2026-07-31` (+ opcional `&q=texto` pra buscar na descrição) | `filtrarpordata.php` |
| `?book_id=1&page_from=1&page_to=5` (+ opcional `&q=`) | `filtrarporfolha.php` |

Toda listagem também aceita `&page=2` e `&per_page=20` pra paginação.

**`GET /movements/summary`** aceita os mesmos dois primeiros formatos de filtro (`month`/`year` ou `date_from`/`date_to`) — mas **atenção**, o formato da resposta muda dependendo de qual filtro você manda:

- Com `month`/`year`, devolve `{ "month": {...}, "year": {...} }` (dois blocos: o balanço do mês e o balanço acumulado do ano, exatamente como o antigo `index.php` mostrava dois painéis lado a lado — "BALANÇO MENSAL" e "BALANÇO ANUAL").
- Com `date_from`/`date_to`, devolve só um bloco direto: `{ "opening_balance": ..., "credits": ..., "debits": ..., "balance": ..., "closing_balance": ... }`.

Cada "bloco" de balanço tem sempre estes 5 campos:

| Campo | Significado | Nome no PHP antigo |
|---|---|---|
| `opening_balance` | Saldo antes do período (tudo que veio antes) | `saldo_anterior` / `saldo_ano_ant` |
| `credits` | Soma das entradas no período | `credito_mes` / `entradas_m` |
| `debits` | Soma das saídas no período | `debito_mes` / `saidas_m` |
| `balance` | `credits - debits` (o resultado só desse período) | `bal_mes` |
| `closing_balance` | `opening_balance + balance` (saldo final, incluindo tudo) | `saldo_atual_mes` |

### Relatórios em PDF (🔒🏦 — pedem conta selecionada)

Substituem os antigos `rel_pdf.php`, `rel_date_pdf.php` (que eram basicamente o mesmo arquivo duplicado) e `rel_cx_periodo.php`. Diferente de todas as outras rotas deste documento, essas **não devolvem JSON** — devolvem o arquivo PDF direto (`Content-Type: application/pdf`), pra abrir numa aba nova do navegador.

| Rota | Controller | O que faz |
|---|---|---|
| 🔒🏦 `GET /reports/monthly-pdf` | `ReportController@monthly` | Relatório do mês. Parâmetros: `?month=&year=` (padrão: mês/ano atual). Equivalente unificado de `rel_pdf.php` + `rel_date_pdf.php`. |
| 🔒🏦 `GET /reports/period-pdf` | `ReportController@period` | Relatório de um intervalo de datas. Parâmetros: `?date_from=&date_to=` (obrigatórios) `&q=` (opcional, busca na descrição). Equivalente a `rel_cx_periodo.php`. |

Os dois usam exatamente o mesmo `BalanceService` do resto do sistema pra calcular saldo/totais (ver [seção 8](#8-onde-mora-cada-lógica-de-negócio)) — diferente do sistema antigo, onde cada arquivo de relatório tinha sua própria cópia da lógica de cálculo, então não tem como o total do relatório em PDF um dia divergir do total mostrado na tela.

**Como o front chama isso:** como é uma navegação de página (`<a target="_blank">`/`window.open`) e não uma chamada via `fetch`/`axios`, o cookie de sessão já vai junto automaticamente — não precisa de token CSRF nem nada especial (rotas `GET` não passam pela checagem de CSRF do Laravel, só `POST`/`PUT`/`DELETE` passam). Ver `livro-caixa-web/src/api/reports.ts`.

A geração do PDF em si usa o pacote `barryvdh/laravel-dompdf`, que transforma uma página HTML/CSS comum (`resources/views/reports/movements.blade.php`) em PDF. Se quiser mudar o visual do relatório (cores, colunas, fontes), é **nesse arquivo Blade** que se mexe — é HTML e CSS normal, bem mais simples de editar do que o código antigo em FPDF, que posicionava cada célula manualmente com coordenadas X/Y (`$rel->Cell(10,7,$seq,1,0,"C")`, etc.).

### Frota — Veículos, Fornecedores, Produtos, Notas Fiscais, Abastecimentos

Todas globais (não pedem conta selecionada), todas seguindo o mesmo padrão de 5 rotas REST:

- `/vehicles` — campos: `plate`, `brand`, `model`, `manufacture_year`, `model_year`, `renavam` (opcional), `chassis` (opcional)
- `/fuel-suppliers` — campos: `company_name`, `address`, `neighborhood`, `city`, `state`, `zip_code`, `phone`, `cnpj`
- `/fuel-products` — campos: `code`, `name`, `unit`
- `/nfe-links` — campo: `url`
- `/refuelings` — campos: `vehicle_id`, `fuel_supplier_id`, `fuel_product_id`, `nfe_link_id` (opcional), `invoice_number`, `access_key` (opcional), `refueled_at`, `odometer_km`, `quantity`, `unit_price`, `notes` (opcional), `target_efficiency_km_per_liter` (opcional, padrão 14.10 se não mandar nada)

`GET /refuelings` aceita `?vehicle_id=1` pra filtrar por veículo, e é paginado (`per_page`, padrão 15).

**Rota especial:** `GET /vehicles/{id}/efficiency` — devolve todos os abastecimentos daquele veículo, **mais** um bloco `meta.efficiency` com, pra cada abastecimento, o km rodado desde o anterior, km/l, R$/km, l/km, e se bateu a meta (`meets_target`). Essa conta é feita pelo `FuelEfficiencyService` (ver [próxima seção](#8-onde-mora-cada-lógica-de-negócio)) e substitui as views `mediakm`/`mediakm2` do banco antigo.

---

## 8. Onde mora cada lógica de negócio

Se você quiser **mudar uma regra**, é aqui que precisa olhar primeiro — antes de qualquer Controller.

### `app/Services/BalanceService.php` — todo o cálculo de saldo

No sistema antigo, o cálculo de saldo corrente vivia dentro de consultas SQL gigantes, cheias de sub-consultas aninhadas (dá pra ver isso abrindo `parameters.php` ou `class/class.lancamentos.php` no projeto antigo — tem consultas com mais de 1500 caracteres numa linha só). Cada tela (`index.php`, `filtrarpordata.php`, `filtrarporfolha.php`) tinha sua própria versão, ligeiramente diferente, dessa consulta.

Agora existe **um lugar só**: `BalanceService`. Ele tem estes métodos:

- `ledgerByDate($accountId)` — devolve todos os lançamentos de uma conta, em ordem de data, **cada um já com o saldo acumulado até aquele ponto** (`running_balance`). Isso é calculado com um recurso do banco de dados chamado *window function* (`SUM(...) OVER (ORDER BY ...)`), que soma "andando" linha por linha sem precisar de sub-consulta nenhuma.
- `ledgerByBookPage($accountId)` — a mesma ideia, mas ordenado por livro/folha em vez de por data (usado no filtro "por livro/folha").
- `balanceBefore($accountId, $data)` — o saldo acumulado até (mas sem incluir) uma data específica. Usado pra calcular o "saldo anterior" de qualquer período.
- `summaryForRange($accountId, $de, $ate)` — o balanço (entradas, saídas, saldo) de um intervalo de datas.
- `summaryForMonth($accountId, $mes, $ano)` — chama `summaryForRange` duas vezes: uma pro mês, uma pro ano inteiro até aquele mês. É o que alimenta os dois painéis do dashboard.

**Se um dia você achar que o saldo está calculado errado, ou quiser mudar a fórmula, é neste arquivo que a mudança precisa acontecer — só aqui.** Como só existe uma versão da lógica, a correção vale pra toda tela que usa saldo (dashboard, filtro por data, filtro por livro/folha) de uma vez.

### `app/Services/FuelEfficiencyService.php` — todo o cálculo de consumo

Substitui as views `mediakm`/`mediakm2` do `dblogin.sql`, que calculavam km/l usando sub-consultas correlacionadas (pra cada abastecimento, buscar o abastecimento anterior do mesmo veículo). Aqui a mesma ideia é feita com PHP puro, em `forVehicle()`: ordena os abastecimentos por data, e pra cada um calcula a diferença de km rodado, km/l, custo por km e litros por km em relação ao abastecimento anterior daquele veículo.

### `app/Http/Controllers/Api/*.php` — orquestração, não cálculo

Os Controllers **não deveriam** ter lógica de cálculo complexa dentro deles — o trabalho deles é: validar o que chegou, chamar o Model ou Service certo, e devolver a resposta formatada. Se você abrir, por exemplo, `MovementController.php`, vai ver que ele não sabe *como* o saldo é calculado — ele só chama `$this->balances->summaryForMonth(...)` e devolve o resultado.

### `app/Http/Middleware/EnsureAccountSelected.php` — o "segurança" da conta

Já explicado na [seção 6](#6-como-funciona-o-login-duplo-agora). Fica de porteiro em todas as rotas de `/books` e `/movements`.

### `app/Providers/AppServiceProvider.php` — a "cola" de `currentAccount()`

Esse arquivo registra um atalho (`Request::macro('currentAccount', ...)`) que deixa qualquer Controller perguntar `$request->currentAccount()` pra saber qual é a conta ativa da sessão, sem precisar reescrever essa busca em todo canto. É um detalhe técnico pequeno, mas é bom saber que existe se um dia esse atalho parar de funcionar — o problema vai estar aqui.

### `app/Enums/CashFlowType.php` — os dois tipos possíveis

Um "Enum" é uma lista fechada de valores possíveis. Aqui só tem dois: `Credit` (`"credit"`) e `Debit` (`"debit"`). É usado tanto em `categories.type` quanto em `movements.type`, garantindo que ninguém consiga salvar um tipo inválido tipo `"entrada"` ou `"1"` por engano.

---

## 9. Tabela de conversão: arquivo antigo → arquivo novo

Esta é provavelmente a tabela mais útil deste documento se você já conhece o sistema antigo e quer achar rápido "onde foi parar" cada coisa.

| Arquivo/Classe no PHP antigo | O que fazia lá | Onde está agora |
|---|---|---|
| `config/dbconfig.php` | Conexão manual com o banco via PDO | Não existe mais como arquivo — o Laravel cuida disso sozinho a partir do `.env` (`config/database.php`) |
| `config/session.php`, `config/config_session.php` | Configuração manual de sessão | Substituído pela sessão nativa do Laravel + Sanctum |
| `class/class.user.php` (`USER`) | Login, cadastro, recuperação de senha do usuário | `app/Http/Controllers/Api/AuthController.php` + `app/Models/User.php`. A recuperação de senha (que estava quebrada no antigo — ver função `setNovaSenha`) ainda **não foi reimplementada** ([seção 15](#15-o-que-ainda-falta-fazer)) |
| `class/class.account.php` (`ACCOUNT`) | Login da conta, dados da conta | `app/Http/Controllers/Api/AccountController.php` + `app/Models/Account.php` |
| `class/class.lancamentos.php` (`MOVS`) | Inserir/editar/apagar lançamento; `bal_pormes`, `bal_pordata`, `bal_porfolha`, `dados_pormes`, `dados_pordata`, `dados_porfolha` (todas as variações de cálculo de saldo) | `app/Http/Controllers/Api/MovementController.php` (CRUD) + `app/Services/BalanceService.php` (TODOS os cálculos, unificados) |
| `class/class.cat.php` (`EVENTS_CAT`) | CRUD de categorias (e também tinha, morto, um pedaço de CRUD de lançamento duplicado) | `app/Http/Controllers/Api/CategoryController.php` |
| `functions.php` (`formata_dinheiro`, `mostraMes`, `InvertData`) | Formatação de dinheiro, nome do mês, inversão de data | `livro-caixa-web/src/lib/format.ts` — a formatação pra exibição agora é responsabilidade do front, não do backend (a API sempre devolve números "crus" e datas em formato `AAAA-MM-DD`) |
| `index.php` | Tela inicial: balanço mensal/anual + tabela de lançamentos do mês | Backend: `MovementController@summary` + `MovementController@index`. Tela: `livro-caixa-web/src/pages/DashboardPage.tsx` |
| `filtrarpordata.php` | Filtro por intervalo de datas | Backend: `MovementController@index` com `?date_from&date_to`. Tela: `livro-caixa-web/src/pages/MovementsPage.tsx`, aba "Filtrar por Data" |
| `filtrarporfolha.php` | Filtro por livro/folha | Backend: `MovementController@index` com `?book_id&page_from&page_to`. Tela: `MovementsPage.tsx`, aba "Filtrar por Livro/Folha" |
| `operations/add_mov.php`, `edit_mov.php`, `edit_del_mov.php` | Modais de novo/editar/apagar lançamento | Backend: `MovementController@store/update/destroy`. Tela: `livro-caixa-web/src/components/movements/MovementFormDialog.tsx` |
| `list_cat.php`, `operations/add_cat.php`, `edit_del_cat.php` | Tela e CRUD de categorias | Backend: `CategoryController`. Tela: `livro-caixa-web/src/pages/CategoriesPage.tsx` |
| `login.php` | Formulário de login do usuário (+ pedido de recuperação de senha) | Backend: `AuthController@login`. Tela: `livro-caixa-web/src/pages/LoginPage.tsx` |
| `login_c.php` | Formulário de login da conta | Backend: `AccountController@select`. Tela: `livro-caixa-web/src/pages/SelectAccountPage.tsx` |
| `sign-up.php` | Cadastro de usuário | Backend: `AuthController@register`. Tela: `livro-caixa-web/src/pages/RegisterPage.tsx` |
| `sign-up_c.php` | Cadastro de conta | Backend: `AccountController@store`. Tela: `SelectAccountPage.tsx` (botão "Criar nova conta") |
| `logout.php`, `logout_c.php` | Encerrar sessão de usuário/conta | Backend: `AuthController@logout` (um logout só encerra tudo — não faz mais sentido ter dois separados, já que a conta ativa é parte da mesma sessão do usuário) |
| `profile_user.php` | Ver/editar dados do usuário e da conta | Backend: `AuthController@updateMe`. Tela: `livro-caixa-web/src/pages/ProfilePage.tsx` |
| `rel_pdf.php`, `rel_date_pdf.php`, `rel_cx_periodo.php` (usavam a biblioteca FPDF) | Relatório de caixa em PDF | Backend: `app/Http/Controllers/Api/ReportController.php` + `resources/views/reports/movements.blade.php` (agora usando `barryvdh/laravel-dompdf` em vez de FPDF). Tela: botão "Exportar PDF" em `DashboardPage.tsx` e na aba "Filtrar por Data" de `MovementsPage.tsx` |
| `botoes_paginacao.php` | Paginação manual, calculada na mão | Cada `apiResource` de listagem já pagina sozinho (`?page=` e `?per_page=`), e o front usa componentes prontos do shadcn/ui pra mostrar isso |
| `dblogin.sql` — tabelas `veiculos`, `fornecedores`, `produtos`, `linksnfe`, `abastecimentos` | Existiam só no banco, **nenhuma tela as usava** | Módulo Frota inteiro (`VehicleController`, `FuelSupplierController`, `FuelProductController`, `NfeLinkController`, `RefuelingController` + telas em `livro-caixa-web/src/pages/fleet/`) — essa reconstrução **terminou** um módulo que tinha ficado pela metade no sistema antigo |
| `dblogin.sql` — views `mediakm`, `mediakm2` | Cálculo de consumo (km/l) por sub-consulta correlacionada, hardcoded pra `idveiculo=1` | `app/Services/FuelEfficiencyService.php` — generalizado pra qualquer veículo |
| `dblogin.sql` — tabelas `lc_notas`, `lc_conferencia` + views `ent`, `sai`, `soma`, `saldo_c`, `saldo_d` | Conferência de caixa por cédula (contagem física) | **Removido por decisão do dono do sistema** — não faz parte da reconstrução |
| `lc_movimentos` (no plural, tabela irmã de `lc_movimento`) | Uma versão alternativa/abandonada do schema de lançamentos, nunca usada pelo código | Não recriada — a tabela `movements` nova já nasceu no formato "limpo" que essa tabela abandonada tentava alcançar |

---

## 10. Receitas: como alterar as coisas mais comuns

### "Quero mudar as categorias que já vêm prontas quando alguém instala o sistema"

Edite `database/seeders/CategorySeeder.php`. Depois, pra aplicar num banco que já existe, rode:

```bash
php artisan db:seed --class=CategorySeeder
```

(Isso não apaga nada — o seeder usa `firstOrCreate`, ou seja, só cria o que ainda não existe.)

### "Quero mudar a meta padrão de consumo (hoje é 14.10 km/l)"

Tem em dois lugares (de propósito, um é o "padrão de banco" e outro é o "padrão de aplicação" — ver comentário na migration sobre por quê):

1. `database/migrations/2026_07_16_210851_create_refuelings_table.php`, linha do `target_efficiency_km_per_liter` (o valor `14.10` do `->default(...)`)
2. `app/Http/Controllers/Api/RefuelingController.php`, método `store()`, linha `$data['target_efficiency_km_per_liter'] ??= 14.10;`

### "Quero adicionar um campo novo num lançamento" (exemplo: "forma de pagamento")

Isso toca em vários lugares — é um bom exemplo de como uma mudança de verdade se propaga pelo sistema:

1. **Criar a coluna no banco.** Rode `php artisan make:migration add_payment_method_to_movements_table` e dentro do método `up()` do arquivo criado, adicione `$table->string('payment_method')->nullable();`. Depois rode `php artisan migrate`.
2. **Liberar o campo pra ser salvo.** Em `app/Models/Movement.php`, adicione `'payment_method'` na lista dentro de `#[Fillable([...])]`.
3. **Validar o campo.** Em `app/Http/Controllers/Api/MovementController.php`, método `rules()`, adicione a regra, ex: `'payment_method' => ['nullable', 'string', 'max:50'],`.
4. **Incluir na resposta.** Em `app/Http/Resources/MovementResource.php`, adicione `'payment_method' => $this->payment_method,` dentro do `toArray()`.
5. **(Se quiser mostrar/editar no site)** Em `livro-caixa-web`: adicione o campo em `src/types/index.ts` (na interface `Movement`), no formulário `src/components/movements/MovementFormDialog.tsx`, e onde mais fizer sentido exibir.

### "Quero mudar como o saldo é calculado"

`app/Services/BalanceService.php` — e só ali. Veja a explicação detalhada na [seção 8](#8-onde-mora-cada-lógica-de-negócio).

### "Quero adicionar uma rota nova"

1. Crie o método no Controller certo (ou crie um Controller novo com `php artisan make:controller Api/NomeController`).
2. Registre a rota em `routes/api.php`, apontando pro método.
3. Rode `php artisan route:list` pra conferir que ela apareceu certinha.

### "Quero ver o que tem no banco sem instalar um programa de banco de dados"

```bash
php artisan tinker
```

Isso abre um "console" interativo de PHP já carregado com o Laravel. Exemplos:

```php
\App\Models\Movement::count();                 // quantos lançamentos existem
\App\Models\Movement::latest()->first();        // o lançamento mais recente
\App\Models\Account::with('users')->get();      // todas as contas com seus usuários
```

### "Bagunçei tudo, quero recomeçar o banco do zero"

```bash
php artisan migrate:fresh --seed
```

⚠️ Apaga tudo e recria com os dados de exemplo. Sem volta.

---

## 11. Glossário de termos do Laravel

| Termo | Explicação simples |
|---|---|
| **Model** | Classe PHP que representa uma tabela do banco. `Movement::create([...])` em vez de escrever `INSERT INTO` na mão. |
| **Migration** | Arquivo que descreve/cria a estrutura de uma tabela. Fica em `database/migrations/`. |
| **Seeder** | Arquivo que insere dados de exemplo/padrão. Fica em `database/seeders/`. |
| **Controller** | Classe que recebe um pedido HTTP e decide o que fazer. |
| **Route (Rota)** | Um "endereço" que aponta pra um método de Controller. Todas ficam listadas em `routes/api.php`. |
| **Middleware** | Código que roda antes do Controller, podendo bloquear o pedido (ex: checar login). |
| **Request / FormRequest** | Valida os dados recebidos antes de chegar no Controller. |
| **Resource** | Formata os dados antes de virar JSON de resposta. |
| **Service** | Classe comum de PHP (nem Model, nem Controller) que guarda lógica reutilizável. |
| **Eloquent** | O nome do "tradutor" do Laravel entre PHP e SQL — é o que faz `Movement::where(...)` virar `SELECT ... WHERE ...` sozinho. |
| **ORM** | Sigla pra "Object-Relational Mapping" — o nome geral da técnica que o Eloquent usa (representar tabelas como classes/objetos). |
| **Artisan** | O comando de linha (`php artisan ...`) usado pra tudo: rodar o servidor, criar arquivos, mexer no banco, etc. |
| **Sanctum** | O pacote do Laravel que cuida do login (sessão via cookie, nesse projeto). |
| **Endpoint** | Sinônimo de "rota" — um endereço específico da API. |
| **Migração (`migrate`)** | O ato de rodar as migrations pra criar/atualizar as tabelas no banco de verdade. |
| **Enum** | Uma lista fechada de valores possíveis (aqui: só `credit` ou `debit`). |
| **FK / Chave estrangeira** | Uma coluna que aponta pro `id` de outra tabela (ex: `movements.book_id` aponta pra `books.id`). Garante que não dá pra criar um lançamento apontando pra um livro que não existe. |
| **Pivot / tabela pivô** | Uma tabela que só existe pra ligar duas outras (aqui: `account_user`, ligando `users` e `accounts`). |
| **JSON** | O formato de texto em que a API responde (`{"chave": "valor"}`). É o que o front-end lê pra montar as telas. |

---

## 12. Comandos do dia a dia (Artisan)

```bash
# Sobe o servidor local (roda em http://localhost:8000)
php artisan serve

# Cria as tabelas no banco (sem apagar dados existentes, só cria o que falta)
php artisan migrate

# Apaga tudo e recria o banco do zero, já com os dados de exemplo
php artisan migrate:fresh --seed

# Roda só um seeder específico (não apaga nada, só insere o que falta)
php artisan db:seed --class=CategorySeeder

# Abre um console interativo pra mexer no banco/testar código na mão
php artisan tinker

# Mostra a lista de TODAS as rotas registradas (ótimo pra conferir se algo existe)
php artisan route:list

# Lista as rotas de um jeito filtrado, ex: só as de movimentos
php artisan route:list --path=movements

# Cria um Controller novo (vazio, pra você preencher)
php artisan make:controller Api/NomeController

# Cria um Model novo
php artisan make:model NomeDoModelo

# Cria uma migration nova
php artisan make:migration nome_descrevendo_o_que_ela_faz

# Cria um Seeder novo
php artisan make:seeder NomeSeeder

# Instala/atualiza as bibliotecas PHP do projeto (equivalente ao "npm install")
composer install
composer update
```

---

## 13. Testando a API na mão, sem o site

Se você quiser testar uma rota sem passar pelo `livro-caixa-web`, o jeito mais simples é usar um programa como **Postman** ou **Insomnia** (gratuitos, com interface visual).

**Atenção a um detalhe técnico:** como o login usa cookies (sessão), pra testar rotas que exigem login você precisa:

1. Ativar a opção de "guardar cookies entre pedidos" no Postman/Insomnia (geralmente já vem ligado por padrão).
2. Antes de fazer login, fazer um `GET` em `http://localhost:8000/sanctum/csrf-cookie` (isso guarda um cookie de segurança necessário).
3. Depois disso, fazer o `POST /api/login` normalmente, com `email` e `password` no corpo, em formato JSON.
4. A partir daí, todos os pedidos seguintes na mesma "aba"/coleção já vão automaticamente autenticados, porque o cookie de sessão fica guardado.

Se preferir usar o terminal com `curl`, o mesmo fluxo funciona, mas dá mais trabalho (precisa guardar os cookies manualmente com `-c cookies.txt -b cookies.txt` e mandar o cabeçalho `Origin: http://localhost:5173` pra simular que o pedido veio do front). Pra um teste rápido do dia a dia, vale mais a pena usar o Postman/Insomnia mesmo.

---

## 14. Erros comuns e o que significam

| Erro / código | O que significa | Como resolver |
|---|---|---|
| `401 Unauthenticated` | Não está logado (ou a sessão expirou) | Faça login de novo (`POST /api/login`) |
| `409 Nenhuma conta selecionada.` | Está logado, mas tentou acessar `/books` ou `/movements` sem antes escolher uma conta | Chame `POST /api/accounts/{id}/select` primeiro |
| `403` | Está logado, mas tentando algo que não tem permissão (ex: selecionar uma conta que não é sua) | Confira se o usuário realmente está vinculado àquela conta na tabela `account_user` |
| `404` | O registro não existe (ou existe, mas é de outra conta — por segurança, `BookController` e `MovementController` tratam "não é seu" e "não existe" da mesma forma) | Confira o ID |
| `422 Validation error` | Algum campo obrigatório está faltando ou num formato errado | Olhe o campo `"errors"` na resposta — ele lista exatamente qual campo e por quê |
| `419 CSRF token mismatch` (comum ao testar na mão) | Esqueceu de buscar `/sanctum/csrf-cookie` antes de logar | Ver [seção 13](#13-testando-a-api-na-mão-sem-o-site) |
| Erro de CORS no navegador (aparece no console do navegador, não numa resposta da API) | O endereço do front (`FRONTEND_URL` no `.env` do backend) não bate com a porta em que o `npm run dev` realmente subiu | Confira `livro-caixa-api/.env`, variável `FRONTEND_URL`, e `livro-caixa-web/.env`, variável `VITE_API_URL` |
| `Connection error` ao rodar `php artisan migrate` | O banco configurado no `.env` não está acessível (se for MySQL, o servidor MySQL pode não estar rodando) | Confira `DB_CONNECTION`, `DB_HOST`, `DB_PORT` etc. no `.env`, ou volte pra `DB_CONNECTION=sqlite` que não depende de servidor nenhum |

---

## 15. O que ainda falta fazer

Pra ser transparente sobre o estado atual do projeto — isto **não foi feito ainda** nesta reconstrução:

- **Recuperação de senha** (o antigo `login.php` tinha um fluxo de recuperação por e-mail, mas ele estava quebrado no sistema antigo — ver os bugs em `class.user.php`, métodos `setNovaSenha` e `deletacodigo`). O novo sistema ainda não tem esse fluxo implementado (o Laravel tem um mecanismo pronto pra isso, `Password::sendResetLink`, mas ainda não foi ligado).
- **Upload de foto de perfil** — a coluna `photo_path` existe no banco, mas não tem rota nem tela pra upload ainda.
- **Testes automatizados** (Pest/PHPUnit no backend, Vitest no front) — o projeto foi validado manualmente (via `curl` e build de produção), mas não tem uma suíte de testes automatizados ainda.
