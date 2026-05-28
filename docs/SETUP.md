# Setup inicial

## 1. Arquivos de ambiente

1. Copie `.env.example` para `.env`.
2. Ajuste `APP_URL`, `APP_BASE_PATH` e as credenciais do MySQL.

Para deploy em hospedagem compartilhada HostGator, consulte tambem [docs/HOSTGATOR_DEPLOY.md](C:\xampp\htdocs\mapiconecta\docs\HOSTGATOR_DEPLOY.md:1).

Exemplo local com XAMPP:

```env
APP_URL="http://localhost/mapiconecta"
APP_BASE_PATH="/mapiconecta"
DB_HOST=127.0.0.1
DB_PORT=13306
DB_NAME=mapiconecta
DB_USER=root
DB_PASS=
DB_TIMEOUT=3
```

Se preferir, sem `.env` o projeto tambem sobe com defaults locais para XAMPP:

- `APP_BASE_PATH` detectado automaticamente como `/mapiconecta`
- `DB_HOST=127.0.0.1`
- `DB_PORT=13306`
- `DB_NAME=mapiconecta`
- `DB_USER=root`
- `DB_PASS=`
- `DB_TIMEOUT=3`

Para producao em HostGator, o recomendado e usar `.env` com `DB_HOST=localhost` e `DB_PORT=3306`.

## 2. Banco de dados

1. Crie o banco e as tabelas executando [database/schema.sql](C:\xampp\htdocs\mapiconecta\database\schema.sql:1) no MySQL.
2. O script inclui um usuario inicial:

- Email: `admin@mapiconecta.local`
- Senha: `admin123`

## 3. Estrutura entregue nesta etapa

- Front controller em [public/index.php](C:\xampp\htdocs\mapiconecta\public\index.php:1)
- Autoload simples em [bootstrap/app.php](C:\xampp\htdocs\mapiconecta\bootstrap\app.php:1)
- Rotas em [routes/web.php](C:\xampp\htdocs\mapiconecta\routes\web.php:1)
- Configuracoes em [config/app.php](C:\xampp\htdocs\mapiconecta\config\app.php:1) e [config/database.php](C:\xampp\htdocs\mapiconecta\config\database.php:1)
- Nucleo MVC em [app/Core](C:\xampp\htdocs\mapiconecta\app\Core)
- Layouts em [app/Views/layouts](C:\xampp\htdocs\mapiconecta\app\Views\layouts)

## 4. Modulos ja entregues na Etapa 2

- CRUD administrativo de estados
- CRUD administrativo de municipios
- Listagem publica de municipios com busca
- Pagina publica de municipio por slug
- Base de importacao via IBGE Localidades

## 5. Modulos ja entregues na Etapa 3 e 4

- Mapa publico com Leaflet
- Filtros por estado e municipio no mapa
- Fila de curadoria
- Historico de curadoria por municipio
- Regras basicas de permissao para editor, curador e administrador geral

## 6. Modulos ja entregues na Etapa 5

- CRUD administrativo de quizzes
- CRUD administrativo de perguntas
- Quiz publico com correcao e explicacao
- Salvamento de progresso para usuario logado
- Ranking por pontos
- Medalhas por faixas de pontuacao

## 7. Rotas principais

- `/municipios`
- `/mapa`
- `/mapi-educacao`
- `/municipio/{slug}`
- `/quiz/{id}`
- `/ranking`
- `/admin/estados`
- `/admin/municipios`
- `/admin/curadoria`
- `/admin/quizzes`

## 8. Importante ao atualizar banco existente

Se voce ja criou o banco antes desta etapa, precisa atualizar o schema para incluir:

- `historico_curadoria`
- `respostas_quiz_usuario`
- a coluna `total_perguntas` em `progresso_quiz_usuario`
- seeds de `medalhas`, `quizzes` e `perguntas_quiz`
- a tabela `midias_municipio`
- seeds iniciais de `pontos_turisticos` e `midias_municipio`

Para esta etapa, voce tambem pode aplicar diretamente o arquivo incremental:

- [database/updates/2026-05-20_turismo_midias.sql](C:\xampp\htdocs\mapiconecta\database\updates\2026-05-20_turismo_midias.sql:1)

## 9. Proximo ciclo recomendado

- Pagina publica mais rica para municipio
- Complemento de dados estatisticos e geograficos
- Turismo, midias e QR Codes

## 10. Mapa territorial real

O mapa atual funciona com marcadores por latitude/longitude e ja suporta uma camada territorial real quando o arquivo abaixo existir:

- `public/assets/data/piaui-municipios.geojson`

Quando esse arquivo for adicionado, os municipios passam a aparecer como areas clicaveis no Leaflet.
