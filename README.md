# MAPI CONECTA

MVP web para valorizacao territorial e educacional dos municipios do Piaui. O sistema combina mapa interativo, paginas publicas por municipio, quizzes, area administrativa, curadoria editorial, pontos turisticos e galeria de midias com suporte a importacoes externas como IBGE e Wikimedia Commons.

## Visualizacao online

O projeto esta hospedado temporariamente para visualizacao em:

**https://mapiconecta.i9webdesigners.com.br/**

## Em destaque

- mapa publico para exploracao territorial
- paginas municipais com conteudo cultural, educativo e turistico
- quizzes e jornada de descoberta
- painel administrativo com curadoria e importacoes
- acervo visual com integracao Wikimedia Commons
- script do banco incluido no repositorio em `database/schema.sql`

## Objetivo do MVP

Demonstrar a viabilidade tecnica de uma plataforma capaz de:

- organizar dados territoriais e editoriais de municipios
- publicar conteudo educativo, turistico e cultural
- operar com fluxo de curadoria
- enriquecer a base com integracoes publicas

## Tecnologias utilizadas

- PHP 8
- MySQL
- HTML5
- CSS3
- JavaScript
- Leaflet para mapa publico
- Arquitetura MVC propria

## Uso de IA no desenvolvimento

Este projeto contou com apoio de tecnologia de inteligencia artificial durante etapas de desenvolvimento, refinamento de interface, organizacao tecnica e aceleracao de implementacao. A IA foi utilizada como apoio de produtividade, enquanto as decisoes de produto, validacao, curadoria e entrega final permaneceram sob responsabilidade da equipe.

## Funcionalidades principais

- login administrativo com perfis e fluxo de sessao
- CRUD de estados e municipios
- publicacao e curadoria de conteudo municipal
- pagina publica de municipios com busca
- mapa publico com marcadores e suporte a GeoJSON
- quizzes e ranking
- pontos turisticos e galeria de midias
- importacao de municipios e dados factuais via IBGE
- importacao de midias via Wikimedia Commons

## Estrutura do projeto

```text
app/
  Config/
  Controllers/
  Core/
  Middleware/
  Models/
  Services/
  Views/
bootstrap/
config/
database/
docs/
public/
routes/
scripts/
storage/
```

## Como executar localmente

### Requisitos

- PHP 8 com PDO MySQL
- MySQL ou MariaDB
- XAMPP, Laragon ou ambiente equivalente

### Passo a passo

1. Clone ou copie este projeto para a pasta web local.
2. Copie `.env.example` para `.env`.
3. Ajuste as variaveis de banco e URL no `.env`.
4. Crie um banco MySQL chamado `mapiconecta`.
5. Importe o arquivo `database/schema.sql`.
6. Acesse `http://localhost/mapiconecta`.

O script principal do banco faz parte do repositorio:

- `database/schema.sql`

### Exemplo de ambiente local

```env
APP_NAME="MAPI CONECTA"
APP_ENV=local
APP_DEBUG=true
APP_URL="http://localhost/mapiconecta"
APP_BASE_PATH="/mapiconecta"
APP_TIMEZONE="America/Fortaleza"

DB_HOST=127.0.0.1
DB_PORT=13306
DB_NAME=mapiconecta
DB_CHARSET=utf8mb4
DB_USER=root
DB_PASS=
DB_TIMEOUT=3
```

## Credenciais iniciais

- Email: `admin@mapiconecta.local`
- Senha: `admin123`

## Scripts uteis

- `php scripts/import_municipality_facts.php PI`
  Importa dados factuais do IBGE para os municipios do estado.
- `php scripts/import_wikimedia_state.php PI 0 --min-score=0`
  Faz importacao ampla de midias Wikimedia para o estado.
- `php scripts/fill_municipality_coordinates.php`
  Apoia o preenchimento de coordenadas.

## Documentacao complementar

- [Setup detalhado](docs/SETUP.md)
- [Deploy em hospedagem compartilhada](docs/HOSTGATOR_DEPLOY.md)
- [Arquitetura da solucao](docs/ARQUITETURA.md)
- [Importacao via Wikimedia Commons](docs/WIKIMEDIA_IMPORT.md)
- [Checklist para envio ao GitHub](docs/SUBMISSAO_GITHUB.md)
- [Direcao funcional do projeto](docs/PROJECT_PROMPT.md)

## Arquitetura

O diagrama textual e o fluxo da solucao estao em [docs/ARQUITETURA.md](docs/ARQUITETURA.md), cobrindo:

- camadas MVC
- fluxo entre usuario, rotas, controllers, services e banco
- integracoes com IBGE e Wikimedia
- operacao publica e administrativa

## Estado atual do MVP

O MVP ja e executavel e demonstra:

- navegacao publica
- painel administrativo
- base de municipios do Piaui
- modulo de quizzes
- curadoria e historico editorial
- turismo, midias e importacoes externas
