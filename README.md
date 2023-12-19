# ![Configurações do Ambiente](./resources/images/screwdriver-wrench-solid.svg) Configurações do Ambiente

1 Configuração do .env
- Duplicar o arquivo .env.example na raiz do sistema e renomear para .env.
- Editar as variáveis para a conexão com o banco de dados desejado e configurar as opções necessárias para o serviço de e-mail, incluindo: `DB_*` `MAIL_*`

2 Instalações
- Instalar dependências listadas no package.json (dependências JavaScript)
`npm install`
- Instalar dependências listadas no composer.json (dependências PHP)
`composer install`

3 Iniciar a Aplicação
- Rodar o seguinte comando para iniciar a aplicação (e gerar a chave, caso seja a primeira execução neste projeto):
`php artisan serve`

4 Executar as Migrations
- Para criar corretamente as tabelas necessárias, executar o comando:
`php artisan migrate`

## ![Sobre a Aplicação](./resources/images/book-open-solid.svg) Sobre a Aplicação

1 - Funcionamento da Aplicação:

A aplicação oferece rotas para realizar ações relacionadas a usuários, contas e transações. Baseia-se em três entidades principais: `User`, `Account` e `Transactions`. O funcionamento é descrito da seguinte maneira:

- Cada usuário possui apenas uma conta.
- Existem 3 possíveis status para a transação, 'pending' => pendente, 'success' => sucesso, 'refund' => reembolsado.
- Todas as transações requerem um usuário pagante, um usuário recebedor e um valor.
- Para obter uma lista de todas as rotas disponíveis, utilize o endpoint `http://aplicacao/api/all-routes`.
- Para melhor confiabilidade dos dados, nenhum usuário ou conta são excluída de fato e sim desabilitada. Exceto as transações que não podem ser excluídas nem desabilitadas.

2 - Testes Implementados:

Foram implementados testes para garantir que novas funcionalidades não interfiram no funcionamento correto da aplicação. Sempre que uma nova funcionalidade é adicionada, os testes correspondentes são executados para garantir a estabilidade do sistema.

3 - Desenvolvimento da Aplicação

Para o desenvolvimento desta aplicação, foram necessários arquivos nas seguintes pastas:

- app/Http/Controllers

- - Contém todos os arquivos necessários para a lógica de cada rota, seja para cadastro, atualização, seleção ou exclusão de dados.

- app/Http/Models
- - Aqui estão os arquivos que recebem os dados enviados pelo controlador e interagem com o banco de dados.

- app/Http/Mail
- - Arquivo utilizado como modelo para os envios de e-mail.

- database/migrations
- - Contém todas as migrations necessárias para versionamento do banco de dados.

- routes/api
- - Inclui todas as rotas necessárias para usufruir de todas as funcionalidades do sistema.

Essas estruturas foram desenvolvidas para organizar e manter a clareza no código durante o processo de criação e manutenção da aplicação.

## License

[MIT](https://choosealicense.com/licenses/mit/)