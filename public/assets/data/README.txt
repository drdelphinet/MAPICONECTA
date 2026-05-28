Coloque aqui a malha territorial real dos municipios do Piaui em GeoJSON.

Nome esperado pelo frontend:
- piaui-municipios.geojson

O arquivo deve conter propriedades que permitam identificar o municipio por slug ou nome.
Exemplos aceitos pelo frontend:
- id
- codigo_ibge
- slug
- municipio_slug
- slug_municipio
- nome
- name
- NM_MUN

Se o arquivo vier bruto de uma fonte externa, rode o script:
- `php scripts/enrich_piaui_geojson.php`

Ele injeta no GeoJSON os dados publicados do banco, incluindo `slug`, `codigo_ibge` e nomes normalizados.
