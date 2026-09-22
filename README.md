# Mini Sistema de Gestão de Produtos

Este projeto é um pequeno sistema web para organizar fornecedores e produtos e montar uma cesta de compras. Cada pessoa cria sua conta, escolhe os produtos que deseja e vê a quantidade de itens e o valor total da cesta. Cada produto pode entrar **uma única vez**: não existe campo de quantidade.

Foi desenvolvido para um trabalho acadêmico com **PHP orientado a objetos**, **PDO**, **PostgreSQL no Supabase**, **HTML**, **CSS**, **JavaScript** e **Bootstrap**.

## O que o sistema oferece

- Cadastro de usuários e login.
- Cadastro de fornecedores e produtos vinculados a eles.
- Uma tela de **Cadastros** para incluir novos registros.
- Uma tela de **Atualização AJAX** para editar ou excluir fornecedores, produtos e itens da cesta sem recarregar a página.
- Catálogo com seleção de produtos por checkbox e validação de pelo menos um item.
- Cesta individual por usuário, com resumo da quantidade de produtos e do valor total.

## Telas e modelo de dados

Os [esboços no Figma](https://www.figma.com/design/mluNZpMTkSifzuvmTFSpxC) mostram login e criação de conta, cadastros, atualização AJAX, catálogo e cesta. Todas as telas do sistema têm menu de navegação.

O diagrama abaixo reúne as cinco tabelas, seus campos e relacionamentos:

![Diagrama Entidade Relacionamento do sistema](docs/der.svg)

As tabelas são `usuarios`, `fornecedores`, `produtos`, `cestas` e `itens_cesta`. Um fornecedor pode ter vários produtos; um usuário pode ter cestas; e cada cesta contém produtos. A chave composta de `itens_cesta` impede que o mesmo produto seja adicionado duas vezes à mesma cesta.

## Como executar

**Você precisa de:** PHP 8.1 ou superior com `pdo_pgsql` e `mbstring`, um projeto no Supabase e um navegador com JavaScript. O visual usa Bootstrap por CDN, então é necessário acesso à internet.

1. Crie um projeto no [Supabase](https://supabase.com/dashboard) e guarde a senha do banco definida na criação.
2. No painel do projeto, abra **Connect → Session pooler** e copie os dados de conexão. Use o host e o usuário mostrados pelo próprio Supabase; a porta do modo de sessão é `5432`.
3. Copie `.env.example` para `.env` e preencha `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`. Mantenha `DB_SSLMODE=require`. O `.env` não entra no Git. Se houver variáveis de ambiente definidas no sistema, elas têm prioridade.
4. No `php.ini`, habilite `extension=pdo_pgsql` e `extension=mbstring`.
5. Dentro da pasta do projeto, inicie o servidor:

   ```bash
   php -S localhost:8000 -t public
   ```

6. Abra `http://localhost:8000`, crie uma conta e faça login.

O Supabase cria o banco quando o projeto é criado. Na primeira conexão, a aplicação cria automaticamente as tabelas que faltarem. Não é preciso importar um arquivo SQL. O servidor deve apontar para `public`, mantendo `config.php` e `src/` fora da área servida pelo navegador.

Para incluir os dados de demonstração (3 fornecedores e 9 produtos), execute `php scripts/seed.php`. O comando pode ser repetido sem duplicar esses exemplos.

Se você tiver uma versão antiga deste projeto com tabelas em inglês, execute **uma vez**, antes de abrir o site, `php scripts/renomear_tabelas.php`. A migração renomeia as tabelas e preserva os registros e relacionamentos.

## Como usar

Comece cadastrando um fornecedor; depois, cadastre um produto e escolha esse fornecedor no formulário. Para alterar dados já existentes, vá até **Atualização AJAX**, escolha **Editar** ou **Excluir** e acompanhe a confirmação na própria página. No **Catálogo**, marque um ou mais produtos e adicione à **Cesta**. Lá você pode conferir o total e remover itens.

## Decisões de implementação

- O enunciado cita “SHA254”, que não é um algoritmo padronizado. Para atender à intenção do requisito, as senhas são armazenadas com **SHA-256 e um salt aleatório por usuário**. Para uso em produção, `password_hash()` com Argon2id ou bcrypt seria a escolha adequada.
- O PHP cuida do cadastro, do login e das sessões. O Supabase é usado como banco PostgreSQL. As tabelas têm **Row Level Security (RLS)** ativado e não oferecem acesso pela API pública do Supabase.
- As operações usam consultas preparadas com PDO, validação no servidor e token CSRF. O login renova a sessão e a comparação do hash usa `hash_equals()`.
- Um fornecedor com produtos não pode ser excluído até que os produtos sejam removidos ou vinculados a outro fornecedor.

## Arquivos principais

| Caminho | Função |
| --- | --- |
| `config.php` e `.env.example` | Configuração da conexão |
| `src/Database.php` e `src/Models.php` | Banco, criação das tabelas e classes do sistema |
| `public/` e `views/` | Páginas e interações AJAX |
| `scripts/seed.php` | Dados de demonstração |
| `scripts/renomear_tabelas.php` | Migração dos nomes antigos das tabelas |
| `docs/der.svg` | Diagrama Entidade Relacionamento |

## Equipe e entrega

| Nome | RA |
| --- | --- |
| João Paulo Porte de Meira | 60300541 |
