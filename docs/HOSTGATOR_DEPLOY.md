# Deploy na HostGator

Este projeto ja foi preparado para subir em hospedagem compartilhada Apache com PHP e MySQL, usando arquivo `.env` para configuracao.

## 1. Arquivo de ambiente

O projeto agora possui um arquivo `.env` pronto para producao com estas configuracoes:

```
env
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_PORT=3306
DB_NAME=seu_banco
DB_USER=seu_usuario
DB_PASS="sua_senha"
``` 

Antes de publicar, ajuste apenas:

- `APP_URL` para o dominio real
- `APP_BASE_PATH` se o projeto nao ficar na raiz do dominio

Exemplos:

Se o site ficar na raiz:

```env
APP_URL="https://seudominio.com.br"
APP_BASE_PATH=
```

Se o site ficar em subpasta:

```env
APP_URL="https://seudominio.com.br/mapiconecta"
APP_BASE_PATH="/mapiconecta"
```

## 2. Banco de dados

No painel da HostGator:

1. Abra o phpMyAdmin.
2. Selecione o banco configurado para o projeto.
3. Importe o arquivo [database/schema.sql](C:\xampp\htdocs\mapiconecta\database\schema.sql:1).

Observacao:

- se o banco ja existir com tabelas antigas, revise antes de reimportar para evitar conflito com seeds e estruturas ja criadas
- se a base atual for mantida sem reimportacao completa, aplique tambem a nova tabela `midias_municipio`
- para atualizacao incremental desta etapa, use [database/updates/2026-05-20_turismo_midias.sql](C:\xampp\htdocs\mapiconecta\database\updates\2026-05-20_turismo_midias.sql:1)

## 3. Estrutura para upload

Pode subir o projeto inteiro para a pasta do dominio, por exemplo `public_html`, porque:

- existe um `index.php` na raiz do projeto
- esse `index.php` encaminha a execucao para `public/index.php`
- o `.htaccess` da raiz direciona as rotas para o front controller

Estrutura esperada na hospedagem:

```text
public_html/
  app/
  bootstrap/
  config/
  database/
  docs/
  public/
  resources/
  routes/
  storage/
  .env
  .htaccess
  index.php
```

## 4. Permissoes

Garanta permissao de escrita quando necessario em:

- `storage/cache`
- `storage/logs`
- `storage/uploads`

Em hospedagem compartilhada, normalmente `755` para pastas e `644` para arquivos resolve. Se houver falha de escrita, ajuste conforme a politica da conta.

## 5. Checklist rapido de publicacao

1. Enviar os arquivos para `public_html` ou para a subpasta desejada.
2. Confirmar que o `.env` subiu junto.
3. Ajustar `APP_URL`.
4. Ajustar `APP_BASE_PATH` se estiver em subpasta.
5. Importar o banco.
6. Abrir o dominio e testar:
   - home
   - `/municipios`
   - `/mapa`
   - `/admin`

## 6. Login inicial

Se o `schema.sql` for importado integralmente, o usuario inicial continua:

- Email: `admin@mapiconecta.local`
- Senha: `admin123`

Recomendacao:

- alterar a senha administrativa logo apos a primeira entrada em producao
