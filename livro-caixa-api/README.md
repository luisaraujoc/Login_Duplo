# Livro Caixa — API (Laravel)

Esta é a API do sistema **Livro Caixa**, reconstrução do antigo sistema PHP (`Login_Duplo`) usando Laravel. Ela **não tem telas** — só responde dados em JSON. Quem mostra telas é o outro projeto, `livro-caixa-web` (React), que fica numa pasta separada, do lado de fora desta.

📖 **Antes de mexer em qualquer coisa, leia [`docs/GUIA-COMPLETO.md`](docs/GUIA-COMPLETO.md).** Esse guia foi escrito do zero, assumindo que você nunca usou Laravel, e explica tudo: como rodar, onde fica cada coisa, o que cada rota faz, e uma tabela mostrando exatamente pra onde foi cada arquivo `.php` do sistema antigo.

## Resumo ultra-rápido pra rodar (interessante ter o compose do PhP instalado)

```bash
composer install
cp .env.example .env      # só se o arquivo .env ainda não existir
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

A API sobe em `http://localhost:8000`. Um usuário de teste já vem pronto: `demo@example.com` / `password`.

Para rodar o front (em outro terminal, na pasta `livro-caixa-web`):

```bash
npm install
npm run dev
```

Ele sobe em `http://localhost:5173` e já vem configurado pra conversar com a API acima.

Tudo isso — e muito mais — está detalhado em [`docs/GUIA-COMPLETO.md`](docs/GUIA-COMPLETO.md).
