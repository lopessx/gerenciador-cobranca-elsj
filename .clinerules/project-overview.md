## Brief overview

Projeto **Cobri - Gerenciador de Pagamentos**: aplicação fullstack para gestão de cobranças com arquitetura separada em backend API REST (Symfony 6.4 + PHP 8.1) e frontend SPA (Vue 3 + Quasar Framework + TypeScript). Banco de dados MySQL 8.0 via Docker. Autenticação stateless com JWT (LexikJWTAuthenticationBundle).

---

## Communication style

- Sempre responder em **português (pt-BR)**.
- Ser direto e objetivo; evitar verbosidade desnecessária.
- Ao sugerir código, preferir snippets concisos e bem comentados apenas quando a lógica não for trivial.

---

## Backend guidelines

- Framework: **Symfony 6.4** (PHP 8.1+).
- ORM: **Doctrine 3.6** com mapeamento por atributos PHP 8 (`#[ORM\Entity]`, `#[ORM\Column]`, etc.).
- Estrutura de controllers: todos herdam `AbstractController` e usam atributos de rota (`#[Route('/api/...')]`).
- Padrão de resposta: sempre `JsonResponse`; entidades expõem método `toArray()` para serialização.
- Não há camada de Service ou Repository customizado neste projeto; controllers usam `EntityManagerInterface` diretamente.
- Validação via `Symfony\Component\Validator\Validator\ValidatorInterface`.
- Enum PHP 8.1 para roles (`App\Enum\Role`: `admin`, `operator`).

---

## Frontend guidelines

- Framework: **Quasar v2** com **Vue 3** (Composition API + `<script setup>`) e **TypeScript**.
- Gerenciamento de estado: **Pinia** (store modular em `src/stores/`).
- Requisições HTTP: **Axios** centralizado em `src/services/api.ts` com interceptors para JWT.
- Services por domínio: `userService.ts`, `orderService.ts`, `paymentMethodService.ts` — padrão objeto com métodos `list`, `get`, `create`, `update`, `remove`.
- Tipos centralizados em `src/types/index.ts` (interfaces `User`, `Order`, `PaymentMethod`).
- Router: modo `hash`, guards no `beforeEach` para proteção de rotas (`meta.requiresAuth`).
- Layout principal: `MainLayout.vue` com `QLayout` (drawer + toolbar + logout).
- Componentes Quasar preferidos: `QTable`, `QInput`, `QBtn`, `QCard`, `QDialog`, `QNotify`.

---

## Database & migrations

- Banco: **MySQL 8.0** via Docker (`docker-compose.yml`).
- Doctrine configurado para `pdo_mysql`, `utf8mb4`, naming strategy `underscore_number_aware`.
- Migrations gerenciadas pelo `DoctrineMigrationsBundle`.
- Convenção de nomenclatura de tabelas/campos: `snake_case` no banco (ex: `payment_methods`, `paymentmethod_id`).

---

## Security & auth

- Autenticação via **JWT** (LexikJWTAuthenticationBundle).
- Login em `/api/login` (json_login) retorna token JWT.
- Registro em `/api/register` (sem autenticação).
- Todas as rotas `/api/*` exigem `IS_AUTHENTICATED_FULLY`, exceto login e register.
- Token TTL: **3600 segundos**.
- Frontend armazena token em `localStorage` (`jwt_token`) e anexa no header `Authorization: Bearer <token>` via Axios interceptor.
- Em resposta 401, o frontend remove o token e redireciona para `/login`.

---

## Naming conventions

- **PHP**: `PascalCase` classes, `camelCase` métodos/propriedades, `snake_case` campos de banco.
- **TypeScript/Vue**: `PascalCase` componentes, `camelCase` variáveis/funções, `kebab-case` rotas e nomes de arquivos `.vue`.
- **API endpoints**: `kebab-case` (ex: `/api/payment-methods`).
- **Entidades**: IDs compostos com nome da entidade (ex: `userId`, `orderId`, `paymentmethodId`).

---

## Development workflow

- Subir banco: `docker-compose up -d` (na raiz do projeto).
- Backend: rodar migrations com `php bin/console doctrine:migrations:migrate`.
- Frontend: `npm run dev` (dentro de `frontend/`) — Quasar CLI com Vite.
- Lint: `npm run lint` (Prettier + ESLint com flat config).
- Type-check: `npm run typecheck` (`vue-tsc --noEmit`).
