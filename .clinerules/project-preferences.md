## Brief overview
Este projeto é uma aplicação Symfony 6.4 em PHP 8.1 voltada para gerenciamento de boletos, cobranças e parcelamentos. As regras abaixo refletem as convenções e boas práticas estabelecidas para o desenvolvimento no repositório.

## Arquitetura e organização
- A lógica de negócio deve ficar em serviços ou classes de domínio, nunca diretamente nos controllers.
- Controllers devem ser "finos": recebem dados, chamam uma camada de serviço/repositório, e retornam a resposta.
- Repositórios encapsulam consultas e acesso ao banco de dados.
- Entidades Doctrine representam os modelos persistidos.
- Templates Twig renderizam a resposta, sem conter regras de negócio.

## Regras de desenvolvimento
- Siga as convenções do Symfony e Doctrine; prefira soluções idiomáticas da framework.
- Use injeção de dependência e serviços configurados em `config/services.yaml`.
- Preserve a legibilidade do código: tipos explícitos, nomes claros, métodos pequenos e com responsabilidade única.
- Ao adicionar uma feature, siga o padrão já existente no projeto e prefira reutilizar componentes antes de criar novos.
- Não altere arquivos vendor nem dependências sem necessidade explícita.
- Use mensagens e nomes consistentes com o domínio do projeto (boletos, cobranças, parcelamentos, pacientes, empresas).

## Banco de dados e migrations
- Mudanças de schema devem ser feitas via entidades Doctrine, com migrations geradas a partir delas.
- Não faça alterações manuais no banco sem ajustar a migration correspondente.
- Antes de submeter mudanças, verifique se as migrations estão corretas e aplicáveis.

## Testes
- Escreva ou atualize testes para novas funcionalidades ou correções.
- Prefira testes funcionais para fluxos de controller/endpoint e testes unitários para lógica isolada.
- Se uma correção alterar comportamento visível, atualize ou adicione testes correspondentes.

## Comandos úteis
- Instalar dependências: `composer install`
- Limpar cache: `php bin/console cache:clear`
- Rodar testes: `php bin/phpunit`
- Criar migration: `php bin/console doctrine:migrations:diff`
- Aplicar migrations: `php bin/console doctrine:migrations:migrate`