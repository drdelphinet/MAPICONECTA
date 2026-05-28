# Importacao de Imagens com Wikimedia Commons

## Objetivo

Usar imagens com rastreabilidade clara para enriquecer municipios, pontos turisticos, simbolos e patrimonio visual do acervo.

## Fontes priorizadas

- Wikimedia Commons para fotos, bandeiras, brasoes e patrimonio com licenca explicita.
- Midias proprias ou autorizadas quando houver acervo local validado pela equipe.
- Outras fontes abertas somente quando a licenca e a atribuicao estiverem claras.

## Fluxo recomendado

1. Escolher um municipio prioritario.
2. Buscar arquivos no Wikimedia Commons usando o nome do municipio, do atrativo, da bandeira ou do brasao.
3. Priorizar:
   - paisagem urbana ou rural representativa;
   - patrimonio historico;
   - pontos turisticos;
   - eventos e cultura;
   - bandeira e brasao.
4. Abrir a pagina do arquivo e conferir:
   - se o arquivo realmente representa o municipio;
   - se a licenca esta explicita;
   - se o credito esta disponivel;
   - se a qualidade visual e boa.
5. No admin, usar `Midias > Importar do Wikimedia Commons` para entradas individuais ou `Midias > Importar lote` para varias imagens do mesmo municipio.
6. Na importacao simples, colar a URL da pagina do arquivo ou o titulo `File:...`.
7. Na importacao em lote, usar uma linha por item nos formatos:
   - `https://commons.wikimedia.org/wiki/File:...`
   - `File:Nome do arquivo.jpg | Titulo opcional`
   - `categoria | File:Nome do arquivo.jpg | Titulo opcional`
8. Escolher o municipio, revisar os campos preenchidos e salvar.
9. Se a imagem for o principal destaque da pagina, copiar a URL do arquivo para `imagem_principal` do municipio.
10. Se a imagem representar um atrativo especifico, tambem registrar no cadastro do ponto turistico.

## Campos que precisam ficar salvos

- `url_arquivo`: arquivo final usado pela galeria.
- `url_origem`: pagina original no Wikimedia Commons.
- `credito`: autoria e/ou credito informado.
- `licenca`: licenca exibida no arquivo.
- `texto_alternativo`: descricao curta para acessibilidade.
- `categoria_visual`: classificacao editorial como `paisagem`, `patrimonio`, `atrativo`, `bandeira` ou `brasao`.

## Boas praticas editoriais

- Evitar imagem generica que nao comprove relacao com o municipio.
- Preferir 1 imagem forte por municipio antes de adicionar muitas fracas.
- Em simbolos oficiais, conferir se a bandeira ou o brasao realmente pertencem ao municipio correto.
- Em fotos antigas, indicar isso na descricao.
- Quando a licenca nao estiver clara, nao publicar.

## Sugestao de ordem de abastecimento

1. Imagem principal do municipio.
2. Foto dos principais pontos turisticos.
3. Bandeira.
4. Brasao.
5. Patrimonio historico e cultural.
6. Paisagens complementares.
