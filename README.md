# Cobri - Gerenciador de Pagamentos

Aplicação fullstack para gestão de cobranças, composta por uma API REST em Symfony e uma interface web em Quasar/Vue 3.

## Objetivo

O projeto tem como objetivo centralizar e simplificar o gerenciamento de cobranças, permitindo o cadastro de usuários, métodos de pagamento e pedidos, com controle de acesso baseado em papéis (admin/operador) e autenticação segura via JWT.

## Tecnologias

- **Backend**: Symfony 6.4, PHP 8.1+, Doctrine ORM, MySQL 8.0
- **Frontend**: Vue 3, Quasar Framework, TypeScript, Pinia, Axios
- **Infraestrutura**: Docker Compose (MySQL)

### Bibliotecas PHP

| Biblioteca                                         | Descrição                          |
| -------------------------------------------------- | ---------------------------------- |
| `doctrine/doctrine-bundle`                         | Integração do Doctrine com Symfony |
| `doctrine/doctrine-migrations-bundle`              | Gerenciamento de migrations        |
| `doctrine/orm`                                     | ORM para mapeamento objeto-relacional |
| `lexik/jwt-authentication-bundle`                  | Autenticação stateless via JWT     |
| `symfony/console`                                  | CLI e comandos personalizados      |
| `symfony/dotenv`                                   | Carregamento de variáveis de ambiente |
| `symfony/flex`                                     | Plugin Composer para automação Symfony |
| `symfony/framework-bundle`                         | Bundle principal do framework      |
| `symfony/http-client`                              | Cliente HTTP                       |
| `symfony/runtime`                                  | Runtime component                  |
| `symfony/serializer`                               | Serialização de dados              |
| `symfony/validator`                                | Validação de dados                 |
| `symfony/yaml`                                     | Suporte a parsing de YAML          |

### Extensões PHP

As seguintes extensões precisam estar instaladas e ativadas no `php.ini`:

| Extensão         | Obrigatória | Finalidade                                          |
| ---------------- | :---------: | --------------------------------------------------- |
| `ctype`          |     ✅      | Verificação de tipos de caractere                   |
| `iconv`          |     ✅      | Conversão entre codificações de caracteres          |
| `json`           |     ✅      | Codificação/decodificação JSON                      |
| `openssl`        |     ✅      | Criptografia e assinatura JWT (Lexik)               |
| `pcre`           |     ✅      | Expressões regulares (PCRE)                         |
| `tokenizer`      |     ✅      | Tokenização de código PHP (Doctrine)                |
| `xml`            |     ✅      | Parsing de XML (Symfony Framework Bundle)           |
| `mbstring`       |     ✅      | Manipulação de strings multibyte                    |
| `pdo`            |     ✅      | Camada de abstração de banco de dados               |
| `pdo_mysql`      |     ✅      | Driver PDO para MySQL                               |

**Instalação no Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.1-cli php8.1-common php8.1-mbstring php8.1-mysql php8.1-xml
```

> **Nota:** Em sistemas com PHP 8.2+, substitua `8.1` por `8.2` ou `8.3`. As extensões `json` e `pcre` já vêm compiladas no core do PHP 8+ e não precisam ser instaladas separadamente, mas devem estar habilitadas no `php.ini`. Verifique com `php -m` se todas estão ativas.

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

3. Rode o seed para criar o usuário admin padrão:
   ```bash
   cd backend
   php bin/console app:seed-user
   ```
   > O comando lê as variáveis `DEFAULT_ADMIN_EMAIL` e `DEFAULT_ADMIN_PASSWORD` do `backend/.env`.  
   > Se quiser alterar o e-mail/senha do admin, edite essas variáveis antes de executar o seed.

4. Inicie o frontend:
   ```bash
   cd frontend
   npm install
   npm run dev
   ```
