# Backend - Gerenciador de Cobrança

API REST desenvolvida com **Symfony 6.4** (PHP 8.1+) para o sistema de gerenciamento de cobranças. Utiliza autenticação JWT, Doctrine ORM com MySQL e migrations para controle de versão do banco de dados.

---

## 📋 Pré-requisitos

- PHP >= 8.1
- Composer
- MySQL 8.0+
- Extensões PHP: `ctype`, `iconv`, `openssl`, `sodium`

---

## 🚀 Ambiente de Desenvolvimento

### 1. Instalar dependências

```bash
composer install
```

### 2. Configurar variáveis de ambiente

Copie o arquivo `.env` para `.env.local` e ajuste conforme necessário:

```bash
cp .env .env.local
```

Edite o `.env.local` com suas configurações, por exemplo:

```env
APP_ENV=dev
APP_SECRET=seu-secret-aqui
DATABASE_URL="mysql://usuario:senha@127.0.0.1:3306/nome_do_banco?serverVersion=8.0"
```

> **Nota:** O arquivo `.env.local` não é versionado (já está no `.gitignore`) e serve para sobrescrever configurações locais.

### 3. Gerar chaves JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

> Este comando gera os arquivos `config/jwt/private.pem` e `config/jwt/public.pem`. Estes arquivos também estão no `.gitignore` por questões de segurança.

### 4. Criar o banco de dados

```bash
php bin/console doctrine:database:create
```

### 5. Executar migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Iniciar o servidor de desenvolvimento

```bash
php -S localhost:8000 -t public/
```

Ou, se estiver usando o Symfony CLI:

```bash
symfony server:start
```

A API estará disponível em: `http://localhost:8000`

---

## 🏭 Ambiente de Produção

### 1. Instalar dependências (sem dev)

```bash
composer install --no-dev --optimize-autoloader
```

### 2. Configurar variáveis de ambiente

Defina as variáveis de ambiente diretamente no servidor ou compile o `.env`:

```bash
composer dump-env prod
```

> Isso gera o arquivo `.env.local.php` com todas as variáveis compiladas para produção.

### 3. Configurar o banco de dados

Ajuste a `DATABASE_URL` para apontar para o banco de produção e execute:

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Limpar e warmup de cache

```bash
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

### 5. Configurar permissões

```bash
chmod -R 775 var/
chmod -R 775 config/jwt/
```

### 6. Configurar o servidor web

Aponte o document root para a pasta `public/`. Exemplo de configuração Nginx:

```nginx
server {
    listen 80;
    server_name api.exemplo.com;
    root /var/www/backend/public;

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
}
```

---

## 🔨 Build

Este projeto não requer build de assets (é uma API REST), mas os seguintes comandos são úteis para otimização:

```bash
# Otimizar autoloader
composer dump-autoload --optimize --classmap-authoritative

# Limpar e warmup de cache (produção)
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Compilar .env para produção
composer dump-env prod
```

---

## 🗄️ Migrations

### Ver status das migrations

```bash
php bin/console doctrine:migrations:status
```

### Executar migrations pendentes

```bash
php bin/console doctrine:migrations:migrate
```

### Criar uma nova migration

```bash
php bin/console doctrine:migrations:diff
```

> Gera uma migration automaticamente com base nas diferenças entre as entidades e o banco atual.

### Criar uma migration vazia

```bash
php bin/console doctrine:migrations:generate
```

### Executar migrations até uma versão específica

```bash
php bin/console doctrine:migrations:execute 'DoctrineMigrations\Version20260723022624' --up
```

### Reverter a última migration

```bash
php bin/console doctrine:migrations:rollback
```

---

## 📁 Estrutura do Projeto

```
backend/
├── bin/                    # Scripts executáveis (console)
├── config/                 # Configurações do Symfony
│   ├── packages/           # Configurações de bundles
│   ├── routes/             # Definições de rotas
│   └── jwt/                # Chaves JWT (não versionadas)
├── migrations/             # Migrations do Doctrine
├── public/                 # Document root (index.php)
├── src/                    # Código-fonte da aplicação
│   ├── Controller/         # Controllers da API
│   ├── Entity/             # Entidades Doctrine
│   ├── Enum/               # Enums PHP
│   ├── Repository/         # Repositories
│   └── Kernel.php          # Kernel do Symfony
├── var/                    # Cache, logs (não versionado)
├── vendor/                 # Dependências Composer (não versionado)
├── .env                    # Variáveis de ambiente padrão
├── .env.dev                # Variáveis de ambiente de desenvolvimento
├── composer.json           # Dependências do projeto
└── symfony.lock            # Lock de receitas Symfony
```

---

## 🔐 Segurança

- Nunca commite o arquivo `.env.local` ou arquivos `.env.*.local`
- Nunca commite as chaves JWT em `config/jwt/*.pem`
- Em produção, defina `APP_ENV=prod` e use um `APP_SECRET` forte e único
- Utilize HTTPS em produção

---

## 🧪 Comandos Úteis

| Comando | Descrição |
|---------|-----------|
| `php bin/console list` | Lista todos os comandos disponíveis |
| `php bin/console debug:router` | Lista todas as rotas registradas |
| `php bin/console doctrine:schema:validate` | Valida o mapeamento do Doctrine |
| `php bin/console doctrine:schema:update --dump-sql` | Mostra SQL necessário para sincronizar o banco |
| `php bin/console security:check` | Verifica vulnerabilidades de segurança |

---

## 📚 Documentação

- [Symfony](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Doctrine Migrations](https://www.doctrine-project.org/projects/migrations.html)
- [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.md#getting-started)
