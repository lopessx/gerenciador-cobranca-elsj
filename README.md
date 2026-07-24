# Cobri - Gerenciador de Pagamentos

Aplicação fullstack para gestão de cobranças, composta por uma API REST em Symfony e uma interface web em Quasar/Vue 3.

## Objetivo

O projeto tem como objetivo centralizar e simplificar o gerenciamento de cobranças, permitindo o cadastro de usuários, métodos de pagamento e pedidos, com controle de acesso baseado em papéis (admin/operador) e autenticação segura via JWT.

## Tecnologias

- **Backend**: Symfony 6.4, PHP 8.1+, Doctrine ORM, MySQL 8.0
- **Frontend**: Vue 3, Quasar Framework, TypeScript, Pinia, Axios
- **Infraestrutura**: Docker Compose (MySQL)

## Estrutura

```
├── backend/    # API REST (Symfony)
├── frontend/   # SPA (Quasar + Vue 3)
└── docker-compose.yml  # MySQL container
```

## Início Rápido

1. Suba o banco de dados:
   ```bash
   docker-compose up -d
   ```

2. Configure o backend:
   ```bash
   cd backend
   composer install
   php bin/console doctrine:migrations:migrate
   symfony server:start
   ```

3. Inicie o frontend:
   ```bash
   cd frontend
   npm install
   npm run dev
   ```
