# Livro Caixa — Web (React)

Este é o **front-end** do sistema Livro Caixa: as telas que aparecem no navegador. Ele não guarda dados nem faz contas sozinho — para tudo isso, ele conversa com a API que fica na pasta `../livro-caixa-api` (documentação completa dela em [`../livro-caixa-api/docs/GUIA-COMPLETO.md`](../livro-caixa-api/docs/GUIA-COMPLETO.md), incluindo todas as rotas que este projeto consome).

## Como rodar

Precisa da API rodando em `http://localhost:8000` primeiro (ver o guia linkado acima). Com ela no ar, em outro terminal:

```bash
npm install
npm run dev
```

Abre em `http://localhost:5173`. Login de teste (já vem pronto pela API): `demo@example.com` / `password`.

## Tecnologias

- **React 19 + TypeScript + Vite** — a base do projeto
- **React Router** — navegação entre páginas (`src/App.tsx` tem a lista de todas as rotas do site)
- **TanStack Query** — busca e guarda em cache os dados vindos da API (é o que faz as telas atualizarem sozinhas depois de criar/editar/apagar algo)
- **React Hook Form + Zod** — formulários e validação
- **shadcn/ui + Tailwind CSS** — os componentes visuais (botões, tabelas, modais, etc.)
- **Recharts** — o gráfico de consumo (km/l) na tela de eficiência de veículo

## Estrutura de pastas

```
src/
├── api/            ← Funções que chamam a API (uma por assunto: auth, accounts, movements, resources...)
├── hooks/          ← "Ganchos" do React que usam o TanStack Query em cima do api/ (useMovements, useVehicles...)
├── types/          ← Os formatos (TypeScript) dos dados que vêm da API
├── components/     ← Pedaços de tela reutilizáveis (formulários, tabelas, o menu lateral...)
│   └── ui/         ← Componentes do shadcn/ui (não mexer direto, exceto para customizar estilo)
├── pages/          ← Uma tela completa por arquivo (Dashboard, Lançamentos, Categorias, Veículos...)
│   └── fleet/      ← As telas do módulo Frota
├── lib/
│   ├── utils.ts    ← Utilitário `cn()` do shadcn (mistura classes CSS)
│   └── format.ts   ← Formatação de moeda (R$), data e nomes de mês
└── App.tsx          ← A lista de rotas do site e quem pode acessar cada uma
```

### Como uma tela busca dados

Exemplo com a tela de Categorias:

1. `src/api/resources.ts` define `categoriesApi`, com as chamadas HTTP pra `/api/categories`.
2. `src/hooks/useCategories.ts` usa o TanStack Query em cima disso (`useCategoriesQuery`, `useCategoryMutations`), cuidando de cache, loading e atualização automática da tela depois de criar/editar/apagar.
3. `src/pages/CategoriesPage.tsx` usa esses hooks e monta a tela (tabela + modal de formulário).

Esse mesmo padrão se repete em todas as áreas do sistema (Livros, Lançamentos, Veículos, etc.) — uma vez que você entender uma, as outras seguem a mesma receita.

### Autenticação e conta ativa

`src/hooks/useAuth.ts` e `src/hooks/useAccount.ts` cuidam de login/logout e da seleção da conta ativa (equivalente ao antigo "login duplo" — ver a explicação completa no guia da API). `src/components/layout/RequireAuth.tsx` e `RequireAccount.tsx` são os "seguranças" do lado do front: eles impedem de entrar numa tela sem estar logado / sem ter escolhido uma conta, redirecionando para `/login` ou `/select-account` conforme necessário.
