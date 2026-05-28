# Relatorio de Dados e Fluxo do Projeto MAPI CONECTA

## 1. Visao geral

O MAPI CONECTA foi estruturado para transformar cada municipio em uma unidade digital completa de informacao, curadoria e experiencia do usuario. O banco de dados nao guarda apenas cadastros basicos: ele organiza o fluxo entre coleta, revisao, publicacao, distribuicao e retorno de uso.

Hoje o projeto esta apoiado em 21 tabelas principais, separadas por dominios funcionais. O municipio e o eixo central do sistema. A partir dele, o projeto conecta base territorial, conteudo editorial, educacao, turismo, quizzes, gamificacao, QR Codes e trilhas operacionais.

## 2. Tecnologia usada no desenvolvimento

O projeto foi desenvolvido com stack web classica e modular, pensada para rodar em ambiente PHP com MySQL e evoluir por etapas sem depender de framework pesado.

Tecnologias principais:

- PHP 8 com arquitetura MVC propria
- MySQL com acesso via PDO
- HTML5 para estrutura das telas
- CSS3 para interface responsiva e acessivel
- JavaScript para interacoes no front-end
- Leaflet para renderizacao do mapa interativo
- GeoJSON para camada territorial dos municipios

Caracteristicas tecnicas da implementacao:

- front controller central em `public/index.php`
- roteamento proprio em `routes/web.php`
- controllers, models, services e views separados por responsabilidade
- integracoes externas encapsuladas em servicos dedicados
- area publica e area administrativa compartilhando a mesma base de dados

## 3. APIs e servicos usados

O fluxo de dados do MAPI CONECTA nao depende apenas de digitacao manual. O sistema foi preparado para consumir servicos publicos e registrar essas integracoes de forma rastreavel.

APIs e servicos mapeados no projeto:

- API de Localidades do IBGE
- portal IBGE Cidades e Estados
- tiles cartograficos do OpenStreetMap
- arquivo GeoJSON territorial local usado pelo mapa

Como cada integracao entra no produto:

- `IBGE Localidades`: importa estados e municipios, incluindo codigo IBGE, nome, sigla e estrutura territorial inicial.
- `IBGE Cidades e Estados`: complementa fatos municipais como populacao, area territorial, densidade demografica e gentilico.
- `OpenStreetMap`: fornece a base visual do mapa para navegacao publica.
- `GeoJSON`: permite representar municipios como areas clicaveis no mapa, enriquecendo a leitura territorial.

Observacao importante:

As APIs externas funcionam como apoio de coleta e enriquecimento. A publicacao final continua condicionada ao fluxo de curadoria do sistema. Isso evita dependencia cega de origem externa e protege a qualidade editorial do conteudo.

## 4. O que e coletado em banco

### Identidade e acesso

Tabelas:

- `perfis`
- `usuarios`

Dados armazenados:

- nome do usuario
- email
- senha
- status da conta
- perfil de permissao
- data do ultimo login

Finalidade:

- controlar acesso publico e administrativo
- separar responsabilidades entre administrador, curador, editor e usuario comum

### Base territorial e editorial

Tabelas:

- `estados`
- `regioes_turisticas`
- `municipios`
- `municipio_regiao_turistica`
- `indicadores_municipais`
- `pontos_turisticos`

Dados armazenados:

- codigo IBGE, nome, sigla, slug e regiao
- textos de descricao, historia, geografia, economia, cultura, turismo, educacao e curiosidades
- populacao, area, densidade demografica, gentilico e data de fundacao
- latitude, longitude e distancia da capital
- fonte de dados
- vinculo com regiao turistica
- pontos turisticos associados ao municipio

Finalidade:

- criar a pagina publica de cada municipio
- sustentar mapa, busca, exploracao e leitura territorial
- permitir enriquecimento progressivo do acervo

### Curadoria e governanca

Tabelas:

- `historico_curadoria`

Dados armazenados:

- tipo da entidade
- id da entidade
- acao executada
- status anterior
- status novo
- observacao interna
- usuario responsavel
- data da acao

Finalidade:

- registrar tudo o que foi revisado, aprovado, corrigido, reprovado ou publicado
- garantir rastreabilidade editorial

### Educacao

Tabelas:

- `atividades_pedagogicas`

Dados armazenados:

- titulo e descricao da atividade
- disciplina
- ano escolar
- objetivo
- metodologia
- recursos necessarios
- desenvolvimento
- avaliacao
- arquivo PDF
- status de curadoria

Finalidade:

- transformar municipios em material de uso escolar
- apoiar professores, estudantes e roteiros de estudo

### Quizzes, gamificacao e jornada do usuario

Tabelas:

- `quizzes`
- `perguntas_quiz`
- `progresso_quiz_usuario`
- `respostas_quiz_usuario`
- `medalhas`
- `usuarios_medalhas`
- `municipios_favoritos`
- `municipios_visitados`

Dados armazenados:

- quizzes por municipio
- perguntas, alternativas e resposta correta
- respostas marcadas e acertos
- pontuacao total e progresso salvo
- medalhas conquistadas
- municipios favoritos
- municipios visitados e origem da visita

Finalidade:

- aumentar engajamento
- registrar aprendizado e exploracao
- criar trilha de relacionamento do usuario com o territorio

### QR Codes, integracoes e leitura operacional

Tabelas:

- `qrcodes`
- `qrcode_acessos`
- `integracao_logs`

Dados armazenados:

- QR Code associado a entidade
- URL de destino
- total de acessos
- hash de IP, user agent, origem e data de acesso
- fonte da integracao
- endpoint consultado
- parametros
- status, mensagem e resumo de resposta

Finalidade:

- conectar o digital com materiais impressos, escolas, eventos e turismo
- acompanhar operacao tecnica e importacoes externas

## 5. Sobre o banco de dados

O banco foi modelado em MySQL com foco em normalizacao, rastreabilidade e crescimento progressivo do produto. A estrutura atual usa 21 tabelas principais com relacionamentos por chaves estrangeiras, permitindo que o municipio seja o nucleo de conexao entre conteudo, operacao e experiencia do usuario.

Pontos tecnicos do banco:

- charset `utf8mb4` e collation `utf8mb4_unicode_ci`
- relacionamentos por foreign keys entre usuarios, municipios, quizzes, atividades e historicos
- uso de campos `ENUM` para status operacionais e editoriais
- campo `JSON` em `integracao_logs` para registrar parametros de chamadas externas
- timestamps de criacao e atualizacao para trilha temporal
- seeds iniciais para perfis, usuario administrador, estado, municipios, quizzes, perguntas e medalhas

Papeis do banco no projeto:

- armazenar a base territorial e editorial dos municipios
- sustentar o fluxo de curadoria e publicacao
- registrar interacoes do usuario, como favoritos, visitas e respostas de quiz
- auditar integracoes externas e mudancas editoriais
- apoiar leitura gerencial no painel administrativo

Em termos de arquitetura, o banco nao foi desenhado apenas para cadastro. Ele funciona como camada de memoria do produto, preservando origem, status, autoria, revisao, publicacao e sinais de uso.

## 6. Fontes de entrada dos dados

O projeto foi preparado para receber dados por quatro caminhos principais:

1. Cadastro manual interno.
2. Curadoria e refinamento editorial.
3. Integracoes com APIs publicas, principalmente IBGE.
4. Interacao direta do usuario final no produto.

As APIs externas entram como apoio e nao como publicacao automatica. A arquitetura foi pensada para preservar conteudo editorial manual e exigir curadoria antes de publicar.

## 7. Fluxo do projeto

### Etapa 1. Coleta e entrada

Os dados chegam por seed inicial, cadastro manual, integracoes publicas e importacoes futuras. O foco inicial e formar uma base municipal consistente, tecnicamente vinculada ao codigo IBGE e pronta para enriquecimento editorial.

### Etapa 2. Estruturacao no banco

Cada dominio tem tabela propria. Isso permite que o mesmo municipio concentre:

- base geografica
- textos editoriais
- indicadores
- atividades
- quizzes
- pontos turisticos
- rastros de interacao

Nessa etapa, o MySQL atua como espinha dorsal da plataforma, e a camada PDO em PHP garante persistencia centralizada e segura dos registros.

### Etapa 3. Curadoria obrigatoria

Todo conteudo passa por workflow editorial:

- rascunho
- enviado para revisao
- em correcao
- aprovado
- publicado
- reprovado
- arquivado

As integracoes externas nao pulam essa etapa. Mesmo quando os dados sao importados por API, eles entram no ecossistema do produto como base de apoio e podem ser revisados antes de ganhar visibilidade publica.

### Etapa 4. Distribuicao no produto

Depois da publicacao, os dados passam a abastecer:

- pagina publica do municipio
- mapa interativo
- area MAPI Educacao
- quizzes
- QR Codes

No front-end, essa distribuicao acontece em paginas web responsivas, com mapa interativo em Leaflet, base cartografica OpenStreetMap e camada territorial em GeoJSON quando disponivel.

### Etapa 5. Retorno de uso

O usuario realimenta o banco com novos sinais:

- favoritos
- municipios visitados
- progresso em quizzes
- respostas registradas
- medalhas
- acessos por QR Code

### Etapa 6. Leitura gerencial

O painel administrativo transforma esse conjunto em leitura operacional:

- cobertura do mapa
- municipios publicados
- fila editorial
- atividades e quizzes ativos
- logs de integracao
- gargalos de curadoria

## 8. Resumo para apresentacao

O MAPI CONECTA e uma plataforma territorial e educacional centrada no municipio. Foi desenvolvido em PHP, MySQL, HTML, CSS e JavaScript, com mapa interativo em Leaflet e apoio de servicos do IBGE e OpenStreetMap. O banco de dados foi desenhado para ir alem do cadastro: ele sustenta coleta, organizacao, curadoria, publicacao, engajamento e monitoramento. Isso permite que o projeto funcione ao mesmo tempo como acervo digital, ferramenta pedagogica, ambiente de exploracao territorial e base de gestao do crescimento do produto.

## 9. Como transformar em PDF

Opcoes praticas:

1. Abrir o dashboard administrativo no navegador e usar a funcao de impressao para salvar em PDF.
2. Abrir este arquivo Markdown em um editor compatível e exportar para PDF.
3. Usar este texto como base para uma proposta institucional ou apresentacao comercial.
