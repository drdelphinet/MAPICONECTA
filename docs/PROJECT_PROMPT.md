# Prompt mestre do projeto MAPI CONECTA

Este documento registra as decisoes tecnicas e funcionais que devem orientar o desenvolvimento do sistema.

## 1. Visao geral

Continuar o desenvolvimento do projeto MAPI CONECTA considerando as seguintes decisoes tecnicas e funcionais.

O sistema sera inicialmente uma aplicacao web responsiva, desenvolvida em PHP, MySQL, HTML5, CSS3 e JavaScript, com possibilidade futura de transformacao em PWA.

O projeto deve nascer preparado para expansao. Embora a primeira versao seja focada no estado do Piaui, a estrutura do banco de dados, rotas, cadastros e regras devem permitir futuramente incluir outros estados e municipios do Brasil.

O desenvolvimento sera feito no VS Code usando Codex, portanto o codigo precisa ser organizado, modular, documentado e facil de evoluir em etapas.

## 2. Modo visitante e usuario logado

### Visitante

- Pode acessar a pagina inicial.
- Pode explorar o mapa.
- Pode visualizar municipios.
- Pode acessar historia, geografia, economia, cultura, turismo, educacao, curiosidades e midias.
- Pode responder quizzes, mas sem salvar progresso.
- Pode compartilhar paginas de municipios.
- Pode acessar QR Codes publicos.

### Usuario logado

- Pode fazer tudo que o visitante faz.
- Pode salvar municipios favoritos.
- Pode marcar municipios como visitados.
- Pode salvar progresso nos quizzes.
- Pode acumular pontos.
- Pode ganhar medalhas.
- Pode visualizar ranking.
- Pode acessar seu historico.
- Pode visualizar seu painel pessoal.

### Meu MAPI

Criar uma area chamada "Meu MAPI", onde o usuario veja:

- Dados do perfil.
- Pontuacao total.
- Medalhas conquistadas.
- Municipios visitados.
- Municipios favoritos.
- Quizzes respondidos.
- Percentual de exploracao do estado.
- Sugestoes de proximos municipios para conhecer.

## 3. Painel administrativo forte

Criar um painel administrativo completo, responsivo e seguro.

### Perfis administrativos

- Administrador Geral
- Curador
- Editor

### Administrador Geral

- Gerencia todo o sistema.
- Cadastra estados.
- Cadastra municipios.
- Gerencia usuarios.
- Gerencia permissoes.
- Aprova ou reprova conteudos.
- Configura integracoes com APIs.
- Acessa relatorios completos.
- Publica ou despublica qualquer conteudo.

### Curador

- Revisa conteudos cadastrados.
- Aprova conteudos de municipios, pontos turisticos, textos, midias e quizzes.
- Solicita correcoes.
- Adiciona observacoes internas.
- Valida se o conteudo esta adequado para publicacao.

### Editor

- Cadastra e edita conteudos.
- Cadastra textos, fotos, pontos turisticos, quizzes e midias.
- Nao pode publicar diretamente se o sistema estiver com curadoria obrigatoria.
- Envia conteudos para aprovacao.

### Fluxo de curadoria

- Rascunho
- Enviado para revisao
- Em correcao
- Aprovado
- Publicado
- Reprovado
- Arquivado

Todo conteudo publico deve ter status.

Campos obrigatorios em conteudos:

- criado_por
- atualizado_por
- revisado_por
- publicado_por
- status_curadoria
- data_criacao
- data_atualizacao
- data_revisao
- data_publicacao

### Modulos do painel administrativo

- Dashboard geral.
- Municipios.
- Estados.
- Regioes.
- Pontos turisticos.
- Categorias.
- Conteudos culturais.
- Galeria de midias.
- Quizzes.
- Perguntas.
- Usuarios.
- Perfis e permissoes.
- Curadoria.
- Importacoes.
- Integracoes externas.
- QR Codes.
- Relatorios.
- Configuracoes.

### Dashboard administrativo

- Total de estados cadastrados.
- Total de municipios cadastrados.
- Total de municipios publicados.
- Total de municipios pendentes de revisao.
- Total de pontos turisticos.
- Total de quizzes.
- Total de usuarios.
- Total de acessos.
- Municipios mais acessados.
- Conteudos pendentes de curadoria.
- Grafico de evolucao de cadastros.
- Grafico de quizzes respondidos.
- Grafico de municipios explorados.

## 4. APIs publicas e coleta automatica de dados

Implementar modulo de integracao com APIs publicas.

### A) IBGE Localidades

Usar para:

- Estados.
- Municipios.
- Codigo oficial IBGE.
- Regiao.
- Mesorregiao, microrregiao ou regioes intermediarias/imediatas, quando disponiveis.

Exemplo de uso:

- Buscar todos os estados do Brasil.
- Buscar municipios de um estado.
- Salvar codigo IBGE do municipio como identificador oficial.

### B) IBGE Agregados / SIDRA

Usar para:

- Populacao.
- Dados censitarios.
- Indicadores economicos.
- Dados educacionais.
- Dados sociais.
- PIB ou PIB per capita, quando disponivel via tabela adequada.
- Series historicas.

O sistema deve ter uma tabela para guardar dados importados por ano, fonte e indicador.

### C) IBGE Malhas Geograficas

Usar para:

- Obter malha territorial do estado.
- Obter malhas dos municipios.
- Gerar mapa interativo com GeoJSON ou SVG.
- Permitir futuramente exibir contorno real dos municipios no mapa.

### D) OpenStreetMap / Overpass API

Usar como fonte auxiliar para:

- Restaurantes.
- Pracas.
- Igrejas.
- Museus.
- Parques.
- Atrativos.
- Hospedagens.
- Pontos de interesse.

Importante: os dados vindos do OpenStreetMap devem entrar como pendentes de curadoria, nunca como publicados automaticamente.

### E) Wikidata, opcional

Usar como fonte complementar para:

- Coordenadas.
- Resumos.
- Links externos.
- Identificadores.
- Informacoes historicas complementares.

Importante: dados de Wikidata tambem devem passar por curadoria.

### Tela de Integracoes Publicas

Criar no painel administrativo uma tela chamada "Integracoes Publicas".

Essa tela deve permitir:

- Importar estados.
- Importar municipios por estado.
- Importar dados estatisticos por municipio.
- Atualizar dados de um municipio especifico.
- Consultar possiveis pontos turisticos externos.
- Ver logs de importacao.
- Ver erros de importacao.
- Reprocessar importacoes.
- Enviar dados importados para curadoria.

### Logs de integracao

- id_log
- fonte
- endpoint
- parametros
- status
- mensagem
- resposta_resumida
- data_execucao
- usuario_id

Nenhum dado externo deve sobrescrever conteudo editorial manual sem aprovacao.
Se um campo ja tiver sido editado manualmente, o sistema deve preservar o conteudo e apenas sugerir atualizacao.

## 5. QR Code por municipio

Criar modulo de QR Code.

Cada municipio deve ter um QR Code proprio apontando para sua pagina publica.

Exemplos:

- `https://dominio.com/municipio/teresina`
- `https://dominio.com/municipio/parnaiba`

### Funcionalidades

- Gerar QR Code automaticamente ao publicar municipio.
- Botao para baixar QR Code em PNG.
- Botao para imprimir QR Code.
- QR Code deve poder ser usado em escolas, feiras, eventos, pontos turisticos e materiais impressos.
- Cada QR Code deve registrar acessos.

### Tabela sugerida `qrcodes`

- id_qrcode
- entidade_tipo
- entidade_id
- url_destino
- arquivo_qrcode
- total_acessos
- status
- criado_em
- atualizado_em

### Tabela sugerida `qrcode_acessos`

- id_acesso
- id_qrcode
- ip_hash
- user_agent
- origem
- data_acesso

Ao acessar por QR Code, o sistema deve registrar a visita sem armazenar dados sensiveis desnecessarios.

## 6. Area pedagogica

Criar area educacional para professores, estudantes e visitantes.

Nome sugerido: MAPI Educacao

### Funcionalidades

- Listar conteudos por municipio.
- Listar conteudos por disciplina.
- Criar roteiros de estudo.
- Disponibilizar atividades pedagogicas.
- Sugerir planos de aula.
- Relacionar municipio com Historia, Geografia, Artes, Cultura, Economia e Tecnologia.
- Permitir baixar atividade em PDF.
- Permitir professor favoritar atividades.
- Permitir quiz por municipio.

### Tipos de conteudo pedagogico

- Roteiro de estudo.
- Plano de aula.
- Atividade de pesquisa.
- Quiz.
- Texto de apoio.
- Desafio cultural.
- Material complementar.

### Tabela sugerida `atividades_pedagogicas`

- id_atividade
- id_municipio
- titulo
- descricao
- disciplina
- ano_escolar
- objetivo
- metodologia
- recursos_necessarios
- desenvolvimento
- avaliacao
- arquivo_pdf
- status_curadoria
- criado_por
- revisado_por
- publicado_por
- criado_em
- atualizado_em

### Pagina publica da area pedagogica

- Filtro por municipio.
- Filtro por disciplina.
- Filtro por ano escolar.
- Cards com atividades.
- Botao "baixar atividade".
- Botao "ver municipio relacionado".

Exemplo de atividade:

"Explore o municipio de Oeiras, leia a aba Historia, identifique tres elementos importantes da formacao do Piaui e responda ao quiz da cidade."

## 7. Acessibilidade e experiencia do usuario

O sistema deve ter foco em usuario comum, estudantes e turistas.

Implementar:

- Layout responsivo.
- Mobile first.
- Botoes grandes e claros.
- Contraste adequado.
- Textos legiveis.
- Icones explicativos.
- Feedback visual em acoes.
- Mensagens amigaveis.
- Navegacao simples.
- Breadcrumbs.
- Campo de busca visivel.
- Imagens com texto alternativo.
- Botao para aumentar e diminuir fonte.
- Modo alto contraste.
- Modo escuro, se possivel.
- Player de audio para conteudos narrados.
- Legendas ou descricao para videos.
- Evitar telas poluidas.
- Evitar linguagem tecnica para o usuario final.

### Barra de acessibilidade

- Aumentar fonte.
- Diminuir fonte.
- Alto contraste.
- Modo leitura.
- Ir para conteudo.
- Ir para menu.

A interface deve ter aparencia moderna, educativa e turistica.

## 8. PWA e uso offline

Preparar o sistema para futuramente virar PWA.

Na primeira versao, estruturar:

- `manifest.json`
- `service-worker.js`
- icones do app
- cor do tema
- tela inicial responsiva
- possibilidade de instalacao no celular

### Funcionalidades offline desejadas

- Acessar pagina inicial em cache.
- Acessar ultimos municipios visualizados.
- Acessar imagens principais ja carregadas.
- Acessar quizzes baixados previamente.
- Exibir aviso quando estiver sem internet.
- Sincronizar respostas de quiz quando a conexao voltar.

Criar controle de cache com cuidado para nao deixar dados administrativos sensiveis offline.

PWA deve ser aplicado apenas na area publica e area do usuario comum.
Area administrativa nao deve funcionar offline por seguranca.

## 9. Expansao para outros estados

Mesmo que o MVP seja apenas do Piaui, o banco deve nascer preparado para o Brasil inteiro.

### Tabela `estados`

- id_estado
- codigo_ibge
- nome
- sigla
- regiao
- slug
- status
- criado_em
- atualizado_em

### Tabela `municipios`

- id_municipio
- id_estado
- codigo_ibge
- nome
- slug
- descricao_curta
- historia
- geografia
- economia
- cultura
- turismo
- educacao
- curiosidades
- populacao
- area
- densidade_demografica
- gentilico
- data_fundacao
- latitude
- longitude
- distancia_capital
- imagem_principal
- fonte_dados
- status_curadoria
- status_publicacao
- criado_por
- atualizado_por
- revisado_por
- publicado_por
- criado_em
- atualizado_em
- publicado_em

### Tabela `indicadores_municipais`

- id_indicador
- id_municipio
- fonte
- codigo_tabela
- nome_indicador
- valor
- unidade
- ano
- data_importacao
- observacao

### Tabela `regioes_turisticas`

- id_regiao_turistica
- id_estado
- nome
- descricao
- status

### Tabela `municipio_regiao_turistica`

- id
- id_municipio
- id_regiao_turistica

Dessa forma, quando outro estado for incorporado, o sistema nao precisara ser refeito.

## 10. Importacao manual caso API nao resolva tudo

Como nem todo dado cultural e turistico estara disponivel por API, criar importador CSV/XLSX.

O sistema deve permitir importar:

- Municipios.
- Dados demograficos.
- Pontos turisticos.
- Conteudos culturais.
- Curiosidades.
- Atividades pedagogicas.
- Quizzes.

Criar tela "Importacoes" no painel.

### Fluxo

1. Usuario admin baixa modelo de planilha.
2. Preenche os dados.
3. Faz upload.
4. Sistema valida os campos.
5. Sistema mostra previa.
6. Sistema informa erros.
7. Admin confirma importacao.
8. Dados entram como rascunho ou pendentes de curadoria.
9. Curador revisa e publica.

### Campos minimos para importacao de municipios

- estado_sigla
- codigo_ibge
- nome
- slug
- descricao_curta
- historia
- geografia
- economia
- cultura
- turismo
- educacao
- curiosidades
- populacao
- area
- latitude
- longitude
- imagem_principal

### Campos minimos para importacao de pontos turisticos

- estado_sigla
- municipio_codigo_ibge
- municipio_nome
- nome_ponto
- categoria
- descricao
- endereco
- latitude
- longitude
- horario_funcionamento
- valor_entrada
- contato
- imagem
- fonte

### Campos minimos para importacao de quizzes

- estado_sigla
- municipio_codigo_ibge
- titulo_quiz
- pergunta
- alternativa_a
- alternativa_b
- alternativa_c
- alternativa_d
- resposta_correta
- explicacao
- pontos

## 11. Quiz por municipio

O quiz sera inicialmente por municipio.

### Regras

- Cada municipio pode ter um ou mais quizzes.
- Cada quiz pode ter varias perguntas.
- Perguntas de multipla escolha.
- Cada pergunta deve ter 4 alternativas.
- Apenas uma resposta correta.
- Exibir explicacao apos resposta ou ao final.
- Visitante pode responder, mas nao salva progresso.
- Usuario logado salva progresso, pontuacao e historico.
- Perguntas tambem passam por curadoria.

## 12. Ordem de desenvolvimento recomendada para o Codex

Desenvolver em etapas para economizar tokens e reduzir erros.

### Etapa 1 - Base do projeto

- Estrutura de pastas.
- Conexao PDO.
- Banco `schema.sql`.
- Sistema de login.
- Perfis de usuario.
- Layout base publico.
- Layout base admin.

### Etapa 2 - Estados e municipios

- Tabelas de estados e municipios.
- CRUD admin.
- Importacao inicial via IBGE Localidades.
- Pagina publica do municipio.
- Lista de municipios.
- Busca.

### Etapa 3 - Mapa

- Integracao com Leaflet.
- Marcadores por latitude/longitude.
- Futuro suporte a GeoJSON via IBGE Malhas.
- Filtro por estado e municipio.

### Etapa 4 - Curadoria

- Fluxo de rascunho, revisao e publicacao.
- Perfis admin, curador e editor.
- Historico de alteracoes.

### Etapa 5 - Quizzes e gamificacao

- CRUD de quizzes.
- CRUD de perguntas.
- Respostas do usuario.
- Pontuacao.
- Medalhas.
- Ranking.

### Etapa 6 - Turismo e midias

- Pontos turisticos.
- Galeria.
- Audios.
- Videos.
- QR Codes.

### Etapa 7 - Area pedagogica

- Atividades.
- Planos de aula.
- Materiais para download.
- Filtros por disciplina e ano escolar.

### Etapa 8 - PWA e acessibilidade

- Manifest.
- Service worker.
- Cache publico.
- Barra de acessibilidade.
- Modo alto contraste.
- Melhorias mobile.

## 13. Regras finais

O sistema deve ser criado sempre pensando em:

- Simplicidade de hospedagem.
- PHP e MySQL como base principal.
- Expansao futura para outros estados.
- Curadoria antes da publicacao.
- Dados publicos importados, mas revisados.
- Interface moderna para usuario comum.
- Seguranca no painel administrativo.
- Separacao clara entre area publica, area do usuario e area administrativa.
