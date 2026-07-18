# Instruções para o repositório

## Visão geral
Este projeto é uma aplicação Symfony 6.4 em PHP 8.1 voltada para o gerenciamento de boletos, cobranças e parcelamentos de grandes valores. A estrutura segue convenções Symfony/Doctrine e prioriza organização por domínio, baixo acoplamento e testes automatizados.

## Arquitetura
- A entrada principal da aplicação fica em [public/index.php](public/index.php) e o kernel em [src/Kernel.php](src/Kernel.php).
- O fluxo típico é:
  1. Controller recebe a requisição.
  2. A lógica de negócio deve ficar em serviços ou classes de domínio, não diretamente no controller.
  3. Repositórios encapsulam consultas e acesso ao banco.
  4. Entidades Doctrine representam os modelos persistidos.
  5. Templates Twig renderizam a resposta para o usuário.

## Estrutura principal
- [src/Controller](src/Controller): controladores HTTP e ações de endpoint.
- [src/Entity](src/Entity): entidades Doctrine.
- [src/Repository](src/Repository): repositórios com consultas específicas.
- [templates](templates): templates Twig.
- [config](config): configurações do Symfony, rotas, serviços e bundles.
- [migrations](migrations): alterações de esquema do banco.
- [tests](tests): testes automatizados com PHPUnit.

## Regras de desenvolvimento
- Siga as convenções do Symfony e do Doctrine; prefira soluções idiomáticas.
- Mantenha os controllers finos: receba dados, chame uma camada de serviço/repositório e retorne a resposta.
- Evite colocar regra de negócio em templates ou controllers.
- Prefira repositórios para consultas e entidades para representação de dados.
- Sempre que houver mudança de schema, crie ou ajuste uma migration em vez de alterar o banco manualmente.
- Use injeção de dependência e serviços configurados em [config/services.yaml](config/services.yaml).
- Preserve a legibilidade do código: tipagem, nomes claros e pequenas funções.
- Ao adicionar uma feature, siga o padrão já existente no projeto e prefira reutilizar componentes antes de criar novos.
- Não altere arquivos de vendor nem dependências sem necessidade explícita.

## Padrões de implementação
- Use tipos explícitos em propriedades, parâmetros e retornos.
- Prefira métodos pequenos e com responsabilidade única.
- Para regras de negócio mais complexas, considere criar serviços próprios em vez de espalhar lógica em controllers.
- Quando possível, escreva ou atualize testes para novas funcionalidades ou correções.
- Use mensagens e nomes consistentes com o domínio do projeto (boletos, cobranças, parcelamentos).

## Banco de dados e migrations
- As alterações no modelo devem ser feitas via entidades Doctrine e, em seguida, geradas como migrations.
- Não faça alterações manuais no banco em ambientes de desenvolvimento sem ajustar a migration correspondente.
- Antes de submeter mudanças relevantes, verifique se as migrations estão corretas e aplicáveis.

## Testes
- Os testes devem cobrir comportamento real sempre que possível.
- Prefira testes funcionais para fluxos de controller/endpoint e testes unitários para lógica isolada.
- Se uma correção alterar comportamento visível, atualize ou adicione testes correspondentes.

## Comandos úteis
- Instalar dependências: `composer install`
- Limpar cache: `php bin/console cache:clear`
- Rodar testes: `php bin/phpunit`
- Criar migration a partir de mudanças nas entidades: `php bin/console doctrine:migrations:diff`
- Aplicar migrations: `php bin/console doctrine:migrations:migrate`

## Considerações de ambiente
- Use arquivos de ambiente locais para configurações sensíveis, sem incluir segredos no repositório.
- Mantenha as alterações compatíveis com a configuração padrão do projeto e com o ambiente Docker/Compose, quando aplicável.
