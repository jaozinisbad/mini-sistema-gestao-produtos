# Mini Sistema de Gestão de Produtos

Aplicação web acadêmica em PHP orientado a objetos, MySQL/PDO, HTML, CSS, JavaScript e Bootstrap. Permite cadastro e autenticação de usuários, gestão de fornecedores e produtos e uma cesta com uma unidade por produto.

## Equipe

| Nome | RA |
| --- | --- |
| Preencher | Preencher |

> Substitua os campos acima pelos nomes e RAs de todos os integrantes antes da entrega. Cada integrante deve registrar seus próprios commits.

## Esboços das telas

[Abrir os esboços no Figma](https://www.figma.com/design/mluNZpMTkSifzuvmTFSpxC) — login/cadastro, cadastros e atualização, seleção de produtos e cesta. A navegação está representada em todas as telas.

## Diagrama Entidade Relacionamento

![DER com todas as tabelas, campos e relacionamentos](docs/der.svg)

Relações: um usuário possui várias cestas; uma cesta possui vários itens; cada item aponta para um produto; um fornecedor possui vários produtos. A chave composta `(basket_id, product_id)` impede repetição do mesmo produto na cesta.

## Requisitos

- PHP 8.1 ou superior, com extensões `pdo_mysql` e `mbstring`.
- MySQL 8 ou superior.
- Navegador com JavaScript e acesso à CDN do Bootstrap.

## Instalação e execução

1. Inicie o MySQL e configure o usuário com permissão para criar banco e tabelas.
2. Ajuste `config.php` ou defina as variáveis `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`.
3. No terminal, dentro desta pasta, execute `php -S localhost:8000 -t public`.
4. Acesse `http://localhost:8000` e crie uma conta.

Na primeira conexão, `Database::connect()` cria automaticamente o banco e as cinco tabelas, se não existirem. Não é necessário importar SQL manualmente. O servidor deve apontar para `public`, para que `config.php` e `src` não sejam servidos diretamente.

## Como usar

1. Cadastre um usuário e entre na conta.
2. Em **Cadastros**, cadastre fornecedores e produtos vinculados a eles.
3. Em **Atualização AJAX**, edite ou exclua fornecedores, produtos e itens da cesta sem enviar a página inteira. A lista é recarregada após a confirmação do servidor.
4. Em **Produtos**, marque ao menos um checkbox e adicione os itens à cesta.
5. Em **Cesta**, confira itens, quantidade e valor total, ou remova itens.

## Regras e segurança

- O enunciado menciona “SHA254”, que não é um algoritmo padronizado. Foi adotado **SHA-256 com salt aleatório por usuário** para atender à intenção do requisito. Para um sistema de produção, recomenda-se `password_hash()` com Argon2id ou bcrypt.
- Senhas nunca são armazenadas em texto puro. A autenticação usa `hash_equals()`; sessões são renovadas no login.
- Todas as alterações passam por token CSRF, validação no servidor e consultas preparadas com PDO.
- Um produto só pode pertencer a um fornecedor existente. Um fornecedor com produtos não pode ser excluído até que seus produtos sejam removidos ou transferidos.
- Não há campo de quantidade. Cada produto aparece no máximo uma vez por cesta.

## Estrutura

```text
config.php           Configuração do MySQL
src/Database.php     Conexão e criação automática do esquema
src/Models.php       Classes User, Supplier, Product e Basket
public/index.php     Rotas, formulários, AJAX e telas
docs/der.svg         DER para consulta no README
```

## Git

O histórico registra etapas de implementação. Antes da entrega, cada integrante deve realizar alterações identificáveis e commits em sua própria conta Git. Publique este repositório no GitHub ou serviço exigido pela disciplina.
