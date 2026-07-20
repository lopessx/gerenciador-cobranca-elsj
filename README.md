# Gerenciador de Cobranças (ELSJ)

Portal para gerenciamento de boletos, cobranças e parcelamentos de grandes valores.

Sistema multi-tenant desenvolvido em **Symfony 6.4 LTS** com **PHP 8.1+**, utilizando Doctrine ORM para persistência e Twig para renderização.

---

## Índice

- [Requisitos Mínimos](#requisitos-mínimos)
- [Instalação](#instalação)
- [Executar Localmente (Desenvolvimento)](#executar-localmente-desenvolvimento)
  - [Com SQLite (recomendado para dev)](#com-sqlite-recomendado-para-dev)
  - [Com MySQL](#com-mysql)
- [Preparar para Produção (MySQL)](#preparar-para-produção-mysql)
- [Configuração](#configuração)
- [Comandos Úteis](#comandos-úteis)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Arquitetura](#arquitetura)
- [Testes](#testes)
- [Licença](#licença)

---

## Requisitos Mínimos

| Requisito | Versão |
|-----------|--------|
| **PHP** | 8.1 ou superior |
| **Extensões PHP** | `ctype`, `iconv`, `pdo_mysql` ou `pdo_sqlite`, `mbstring`, `json`, `xml` |
| **Composer** | 2.x |
| **Banco de Dados** | MySQL 8.0+ (produção) ou SQLite 3.x (desenvolvimento) |
| **Servidor Web** | Nginx ou Apache com mod_rewrite |
| **Node.js** | Não necessário (AssetMapper nativo) |

### Dependências PHP (composer.json)

- `php: >=8.1`
- `symfony/framework-bundle: 6.4.*`
- `doctrine/orm: ^3.6`
- `doctrine/doctrine-bundle: ^2.18`
- `doctrine/doctrine-migrations-bundle: ^3.7`
- `symfony/security-bundle: 6.4.*`
- `symfony/twig-bundle: 6.4.*`
- `symfony/asset-mapper: 6.4.*`
- `symfony/validator: 6.4.*`

---

## Instalação

```bash
# 1. Clone o repositório
git clone git@github.com:lopessx/gerenciador-cobranca-elsj.git
cd gerenciador-cobranca-elsj

# 2. Instale as dependências
composer install

# 3. Configure o ambiente
cp .env .env.local
# Edite .env.local com suas configurações
```

---

## Executar Localmente (Desenvolvimento)

### Com SQLite (recomendado para dev)

O SQLite é a opção mais simples para desenvolvimento, pois não requer instalação de servidor de banco de dados.

#### 1. Configure o `.env.local`

```env
# .env.local
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data/database.sqlite"
APP_SECRET=seu-app-secret-aqui
MASTER_EMAIL=admin@sistema.com.br
MASTER_PASSWORD=M4ster_Secure_2026!
MAILER_DSN=null://null
MESSENGER_TRANSPORT_DSN=doctrine://default
```

#### 2. Crie o banco e aplique as migrations

> **Importante:** O comando `doctrine:database:create` **não funciona com SQLite**. Isso ocorre porque o SQLite não possui o conceito de "criar um banco de dados" como MySQL ou PostgreSQL — no SQLite, o banco é simplesmente um arquivo que é criado automaticamente na primeira conexão. O Doctrine tenta executar `CREATE DATABASE`, um comando SQL que o SQLite não implementa.

```bash
# Apenas garanta que o diretório existe
mkdir -p var/data

# As migrations criam o banco automaticamente
php bin/console doctrine:migrations:migrate

# Cria o admin master
php bin/console app:seed-master-user
```

#### 3. Inicie o servidor

```bash
php bin/console server:start
# Acesse: http://localhost:8000
# Login: admin@sistema.com.br
# Senha: M4ster_Secure_2026!
```

### Com MySQL

Para desenvolvimento com MySQL (útil se o ambiente de produção for MySQL e você quiser testar localmente com o mesmo banco).

#### 1. Configure o `.env.local`

```env
# .env.local
DATABASE_URL="mysql://app:senha@127.0.0.1:3306/gerenciador_cobranca?serverVersion=8.0&charset=utf8mb4"
APP_SECRET=seu-app-secret-aqui
MASTER_EMAIL=admin@sistema.com.br
MASTER_PASSWORD=M4ster_Secure_2026!
MAILER_DSN=null://null
MESSENGER_TRANSPORT_DSN=doctrine://default
```

#### 2. Crie o banco e aplique as migrations

```bash
# Cria o banco de dados (funciona com MySQL)
php bin/console doctrine:database:create

# Aplica as migrations
php bin/console doctrine:migrations:migrate

# Cria o admin master
php bin/console app:seed-master-user
```

#### 3. Inicie o servidor

```bash
php bin/console server:start
# Acesse: http://localhost:8000
# Login: admin@sistema.com.br
# Senha: M4ster_Secure_2026!
```

---

## Preparar para Produção (MySQL)

### 1. Configuração de Produção

```env
# .env.local (produção)
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=uma-chave-segura-aleatoria-aqui

DATABASE_URL="mysql://usuario:senha@localhost:3306/gerenciador_cobranca?serverVersion=8.0&charset=utf8mb4"

MASTER_EMAIL=admin@sistema.com.br
MASTER_PASSWORD=senha-forte-aqui

MAILER_DSN=smtp://user:pass@smtp.example.com:587
MESSENGER_TRANSPORT_DSN=doctrine://default
```

### 2. Otimização para Produção

```bash
# 1. Instalar dependências sem dev
composer install --no-dev --optimize-autoloader

# 2. Limpar e aquecer cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# 3. Compilar assets
php bin/console asset-map:compile --env=prod

# 4. Criar banco e aplicar migrations
php bin/console doctrine:database:create --env=prod
php bin/console doctrine:migrations:migrate --env=prod

# 5. Criar admin master
php bin/console app:seed-master-user --env=prod
```

### 3. Servidor Web (Nginx)

```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /caminho/para/projeto/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/project_error.log;
    access_log /var/log/nginx/project_access.log;
}
```

### 4. Verificação Pós-Deploy

```bash
# Verificar se tudo está funcionando
php bin/console about
php bin/console debug:router
php bin/console doctrine:migrations:status
```

---

## Configuração

### Variáveis de Ambiente (`.env`)

| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `DATABASE_URL` | String de conexão com o banco | `mysql://app:senha@127.0.0.1:3306/gerenciador_cobranca?serverVersion=8.0&charset=utf8mb4` |
| `APP_SECRET` | Chave secreta da aplicação | `e2d8f9a1b3c4d5e6f7a8b9c0d1e2f3a4` |
| `MASTER_EMAIL` | Email do admin inicial | `admin@sistema.com.br` |
| `MASTER_PASSWORD` | Senha do admin inicial | `M4ster_Secure_2026!` |
| `MAILER_DSN` | Configuração de email | `null://null` (desabilitado) |
| `MESSENGER_TRANSPORT_DSN` | Transporte de mensageria | `doctrine://default` |

### Configuração de Multi-Tenancy

O sistema utiliza um filtro global do Doctrine (`TenantFilter`) que isola os dados por empresa. O filtro é ativado automaticamente quando um usuário não-admin faz uma requisição com uma empresa ativa na sessão (`active_company_id`).

Para configurar a empresa ativa de um usuário:

```php
// Exemplo: definir empresa ativa na sessão
$session->set('active_company_id', 'id-da-empresa');
```

---

## Comandos Úteis

```bash
# Cache
php bin/console cache:clear                    # Limpa cache
php bin/console cache:warmup                   # Aquece cache

# Banco de Dados
php bin/console doctrine:database:create       # Cria o banco (MySQL/PostgreSQL apenas)
php bin/console doctrine:database:drop         # Remove o banco
php bin/console doctrine:migrations:diff       # Gera migration das mudanças nas entidades
php bin/console doctrine:migrations:migrate    # Aplica migrations pendentes
php bin/console doctrine:migrations:status     # Status das migrations

# Usuários
php bin/console app:seed-master-user           # Cria o admin master

# Assets
php bin/console asset-map:compile              # Compila assets para produção
php bin/console debug:asset-map                # Lista assets disponíveis

# Rotas
php bin/console debug:router                   # Lista todas as rotas

# Testes
php bin/phpunit                                # Executa testes

# Servidor
php bin/console server:start                   # Inicia servidor dev
php bin/console server:stop                    # Para servidor dev
```

---

## Estrutura do Projeto

```
├── assets/
│   ├── app.js                    # JavaScript principal (validação client-side, theme toggle)
│   ├── bootstrap.js              # Bootstrap Stimulus (não utilizado - JS puro)
│   └── styles/
│       └── app.css               # Estilos globais com suporte a dark mode
├── config/
│   ├── packages/
│   │   ├── doctrine.yaml         # Configuração do Doctrine ORM + TenantFilter
│   │   ├── security.yaml         # Segurança (Entity User Provider)
│   │   └── ...
│   ├── routes.yaml               # Mapeamento de rotas
│   └── services.yaml             # Serviços e injeção de dependência
├── migrations/
│   └── Version20260719000100.php # Migration inicial (6 tabelas)
├── public/
│   └── index.php                 # Entry point da aplicação
├── src/
│   ├── Command/
│   │   └── SeedMasterUserCommand.php  # Cria admin master via CLI
│   ├── Controller/
│   │   ├── Admin/
│   │   │   ├── BillingController.php  # CRUD de cobranças
│   │   │   ├── CompanyController.php  # CRUD de empresas
│   │   │   └── PatientController.php  # CRUD de pacientes
│   │   ├── DashboardController.php    # Dashboard principal
│   │   └── SecurityController.php     # Login/Logout
│   ├── Entity/
│   │   ├── Billing.php           # Cobrança (totalAmount, entryAmount em centavos)
│   │   ├── Company.php           # Empresa/Tenant
│   │   ├── Installment.php       # Parcela (amount em centavos)
│   │   ├── Patient.php           # Paciente
│   │   ├── TenantInterface.php   # Interface de marcação para multi-tenancy
│   │   └── User.php              # Usuário (admin/operador)
│   ├── EventListener/
│   │   ├── CompanyLifecycleListener.php  # Desassocia usuários ao inativar empresa
│   │   └── TenantContextListener.php     # Ativa filtro de tenant por sessão
│   ├── Filter/
│   │   └── TenantFilter.php      # Filtro global Doctrine (WHERE company_id = ?)
│   ├── Payment/
│   │   ├── GatewayRegistry.php           # Registry de gateways (Strategy Pattern)
│   │   ├── PaymentGatewayInterface.php   # Contrato para gateways
│   │   └── PaymentResult.php             # DTO de resultado
│   ├── Repository/
│   │   └── BillingRepository.php
│   └── Service/
│       └── BillingDistributor.php # Engine de parcelamento (intdiv, centavos)
├── templates/
│   ├── admin/
│   │   ├── billings/             # CRUD Cobranças (index, new, show)
│   │   ├── companies/            # CRUD Empresas (index, new, edit)
│   │   └── patients/             # CRUD Pacientes (index, new, edit)
│   ├── dashboard/
│   │   └── index.html.twig       # Dashboard com últimas cobranças
│   ├── security/
│   │   └── login.html.twig       # Tela de login
│   └── base.html.twig            # Layout base com dark mode
└── tests/
    └── Service/
        └── BillingDistributorTest.php  # 7 testes unitários
```

---

## Arquitetura

### Multi-Tenancy

O sistema adota isolamento lógico de dados via **Doctrine SQL Filter**. Todas as entidades sensíveis (`Patient`, `Billing`) implementam `TenantInterface`. O `TenantFilter` adiciona automaticamente `WHERE company_id = :current_company_id` em todas as queries.

### Precisão de Centavos

Valores monetários são armazenados como **inteiros** (centavos) para evitar bugs de ponto flutuante:

- R$ 150,00 → `15000`
- A divisão de parcelas usa `intdiv()` do PHP
- O resto matemático é acumulado na última parcela

### Padrões de Design

- **Strategy Pattern**: `PaymentGatewayInterface` + `GatewayRegistry` para múltiplos gateways de pagamento
- **Repository Pattern**: Consultas encapsuladas em repositórios Doctrine
- **Thin Controllers**: Lógica de negócio em serviços, controllers apenas orquestram
- **MVC**: Separação clara entre Model (Entity), View (Twig) e Controller

### Segurança

- Autenticação via `security.yaml` com Entity User Provider
- Senhas hasheadas com algoritmo `auto` (bcrypt/argon2)
- Controle de primeiro acesso (`mustChangePwd`)
- Isolamento multi-tenant por empresa

---

## Testes

```bash
# Executar todos os testes
php bin/phpunit

# Executar com cobertura
php bin/phpunit --coverage-html var/coverage

# Executar teste específico
php bin/phpunit tests/Service/BillingDistributorTest.php
```

### Testes Atuais

- **BillingDistributorTest** (7 testes):
  - Distribuição com resto na última parcela
  - Divisão exata (sem resto)
  - Com valor de entrada
  - Parcela única
  - Resto em parcela única
  - Zero parcelas (retorno vazio)
  - Soma total igual ao valor parcelado

---

## Licença

Proprietário. Todos os direitos reservados.