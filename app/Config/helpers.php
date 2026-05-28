<?php

declare(strict_types=1);

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        $basePath = dirname(__DIR__, 2);

        return $path ? $basePath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $basePath;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $items = [];

        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

        if ($file === null || $item === null) {
            return $default;
        }

        if (!array_key_exists($file, $items)) {
            $path = app_path('config/' . $file . '.php');
            $items[$file] = file_exists($path) ? require $path : [];
        }

        return $items[$file][$item] ?? $default;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $basePath = base_url_path();
        $relativePath = ltrim($path, '/');
        $url = $basePath . '/public/' . $relativePath;
        $localPath = app_path('public/' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));

        if (is_file($localPath)) {
            return $url . '?v=' . filemtime($localPath);
        }

        return $url;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $basePath = base_url_path();

        return $basePath . '/' . ltrim($path, '/');
    }
}

if (!function_exists('absolute_url')) {
    function absolute_url(string $path = ''): string
    {
        $configured = trim((string) config('app.url', ''));
        if ($configured !== '') {
            return rtrim($configured, '/') . '/' . ltrim($path, '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

        return rtrim($scheme . '://' . $host . base_url_path(), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('base_url_path')) {
    function base_url_path(): string
    {
        $configured = trim((string) config('app.base_path', ''));

        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $directory = str_replace('\\', '/', dirname($scriptName));

        if ($directory === '/' || $directory === '\\' || $directory === '.') {
            return '';
        }

        if (str_ends_with($directory, '/public')) {
            $directory = substr($directory, 0, -7);
        }

        return rtrim($directory, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('session_get')) {
    function session_get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('session_forget')) {
    function session_forget(string $key): void
    {
        unset($_SESSION[$key]);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return session_get('_old_input', [])[$key] ?? $default;
    }
}

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('slugify')) {
    function slugify(string $value): string
    {
        $value = strtr($value, [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c',
            'Ñ' => 'N', 'ñ' => 'n',
        ]);
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $normalized = $normalized !== false ? $normalized : $value;
        $normalized = strtolower($normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? '';

        return trim($normalized, '-') ?: 'item';
    }
}

if (!function_exists('normalize_visible_portuguese')) {
    function normalize_visible_portuguese(string $html): string
    {
        if ($html === '' || stripos($html, '<html') === false) {
            return $html;
        }

        $protectedBlocks = [];
        $html = preg_replace_callback(
            '/<(script|style|code|pre|textarea)\b[^>]*>.*?<\/\1>/is',
            static function (array $matches) use (&$protectedBlocks): string {
                $token = '__MAPI_PROTECTED_' . count($protectedBlocks) . '__';
                $protectedBlocks[$token] = $matches[0];

                return $token;
            },
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/>([^<]+)</u',
            static function (array $matches): string {
                return '>' . normalize_portuguese_text_chunk($matches[1]) . '<';
            },
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '/\b(placeholder|title|alt|aria-label)=("|\')(.*?)\2/iu',
            static function (array $matches): string {
                return $matches[1] . '=' . $matches[2] . normalize_portuguese_text_chunk($matches[3]) . $matches[2];
            },
            $html
        ) ?? $html;

        return strtr($html, $protectedBlocks);
    }
}

if (!function_exists('normalize_portuguese_text_chunk')) {
    function normalize_portuguese_text_chunk(string $text): string
    {
        static $patterns = null;

        if ($patterns === null) {
            $replacements = [
                'Inicio' => 'Início',
                'Pagina' => 'Página',
                'Paginas' => 'Páginas',
                'Municipio' => 'Município',
                'Municipios' => 'Municípios',
                'municipio' => 'município',
                'municipios' => 'municípios',
                'Descricao' => 'Descrição',
                'descricoes' => 'descrições',
                'descricao' => 'descrição',
                'Descricoes' => 'Descrições',
                'Endereco' => 'Endereço',
                'enderecos' => 'endereços',
                'endereco' => 'endereço',
                'Educacao' => 'Educação',
                'educacao' => 'educação',
                'Informacao' => 'Informação',
                'Informacoes' => 'Informações',
                'informacao' => 'informação',
                'informacoes' => 'informações',
                'Publico' => 'Público',
                'publico' => 'público',
                'publica' => 'pública',
                'publicas' => 'públicas',
                'publicacao' => 'publicação',
                'publicacoes' => 'publicações',
                'acao' => 'ação',
                'Acao' => 'Ação',
                'acoes' => 'ações',
                'Acoes' => 'Ações',
                'gestao' => 'gestão',
                'Gestao' => 'Gestão',
                'operacao' => 'operação',
                'Operacao' => 'Operação',
                'operacoes' => 'operações',
                'Operacoes' => 'Operações',
                'administracao' => 'administração',
                'Administracao' => 'Administração',
                'integracao' => 'integração',
                'Integracao' => 'Integração',
                'integracoes' => 'integrações',
                'Integracoes' => 'Integrações',
                'revisao' => 'revisão',
                'Revisao' => 'Revisão',
                'aprovacao' => 'aprovação',
                'Aprovacao' => 'Aprovação',
                'correcao' => 'correção',
                'Correcao' => 'Correção',
                'analise' => 'análise',
                'Analise' => 'Análise',
                'avaliacao' => 'avaliação',
                'Avaliacao' => 'Avaliação',
                'conteudo' => 'conteúdo',
                'Conteudo' => 'Conteúdo',
                'conteudos' => 'conteúdos',
                'Conteudos' => 'Conteúdos',
                'autenticacao' => 'autenticação',
                'Autenticacao' => 'Autenticação',
                'navegacao' => 'navegação',
                'Navegacao' => 'Navegação',
                'confortavel' => 'confortável',
                'seguranca' => 'segurança',
                'Seguranca' => 'Segurança',
                'solicitacao' => 'solicitação',
                'Solicitacao' => 'Solicitação',
                'historico' => 'histórico',
                'Historico' => 'Histórico',
                'historia' => 'história',
                'Historia' => 'História',
                'trajetoria' => 'trajetória',
                'Trajetoria' => 'Trajetória',
                'geografico' => 'geográfico',
                'Geografico' => 'Geográfico',
                'geografica' => 'geográfica',
                'Geografica' => 'Geográfica',
                'turistico' => 'turístico',
                'turisticos' => 'turísticos',
                'Turistico' => 'Turístico',
                'Turisticos' => 'Turísticos',
                'experiencia' => 'experiência',
                'experiencias' => 'experiências',
                'Experiencia' => 'Experiência',
                'Experiencias' => 'Experiências',
                'interacao' => 'interação',
                'Interacao' => 'Interação',
                'relacao' => 'relação',
                'Relacao' => 'Relação',
                'relacoes' => 'relações',
                'Relacoes' => 'Relações',
                'exploracao' => 'exploração',
                'Exploracao' => 'Exploração',
                'populacao' => 'população',
                'Populacao' => 'População',
                'fundacao' => 'fundação',
                'Fundacao' => 'Fundação',
                'gentilico' => 'gentílico',
                'Gentilico' => 'Gentílico',
                'credito' => 'crédito',
                'Credito' => 'Crédito',
                'creditos' => 'créditos',
                'Creditos' => 'Créditos',
                'licenca' => 'licença',
                'Licenca' => 'Licença',
                'colecao' => 'coleção',
                'Colecao' => 'Coleção',
                'conexao' => 'conexão',
                'Conexao' => 'Conexão',
                'simbolo' => 'símbolo',
                'simbolos' => 'símbolos',
                'Simbolo' => 'Símbolo',
                'Simbolos' => 'Símbolos',
                'patrimonio' => 'patrimônio',
                'Patrimonio' => 'Patrimônio',
                'regiao' => 'região',
                'Regiao' => 'Região',
                'varias' => 'várias',
                'Varias' => 'Várias',
                'area' => 'área',
                'Area' => 'Área',
                'codigo' => 'código',
                'Codigo' => 'Código',
                'joia' => 'jóia',
                'Joia' => 'Jóia',
                'usuario' => 'usuário',
                'usuarios' => 'usuários',
                'Usuario' => 'Usuário',
                'Usuarios' => 'Usuários',
                'sessao' => 'sessão',
                'Sessao' => 'Sessão',
                'permissao' => 'permissão',
                'Permissao' => 'Permissão',
                'missao' => 'missão',
                'Missao' => 'Missão',
                'visao' => 'visão',
                'Visao' => 'Visão',
                'orgao' => 'órgão',
                'Orgao' => 'Órgão',
                'padrao' => 'padrão',
                'Padrao' => 'Padrão',
                'proximo' => 'próximo',
                'Proximo' => 'Próximo',
                'proximos' => 'próximos',
                'Proximos' => 'Próximos',
                'fisico' => 'físico',
                'Fisico' => 'Físico',
                'fisicos' => 'físicos',
                'Fisicos' => 'Físicos',
                'basica' => 'básica',
                'Basica' => 'Básica',
                'basico' => 'básico',
                'Basico' => 'Básico',
                'rapida' => 'rápida',
                'Rapida' => 'Rápida',
                'rapido' => 'rápido',
                'Rapido' => 'Rápido',
                'memoria' => 'memória',
                'Memoria' => 'Memória',
                'autonoma' => 'autônoma',
                'Autonoma' => 'Autônoma',
                'conteudos' => 'conteúdos',
                'localizacao' => 'localização',
                'Localizacao' => 'Localização',
                'apresentacao' => 'apresentação',
                'Apresentacao' => 'Apresentação',
                'reuniao' => 'reunião',
                'Reuniao' => 'Reunião',
                'distribuicao' => 'distribuição',
                'Distribuicao' => 'Distribuição',
                'pontuacao' => 'pontuação',
                'Pontuacao' => 'Pontuação',
                'estao' => 'estão',
                'Estao' => 'Estão',
                'tambem' => 'também',
                'Tambem' => 'Também',
                'ja' => 'já',
                'Ja' => 'Já',
                'voce' => 'você',
                'Voce' => 'Você',
                'nao' => 'não',
                'Nao' => 'Não',
                'sao' => 'são',
                'Sao' => 'São',
                'Piaui' => 'Piauí',
            ];

            uksort(
                $replacements,
                static fn (string $left, string $right): int => strlen($right) <=> strlen($left)
            );

            $patterns = [];
            foreach ($replacements as $search => $replace) {
                $patterns[] = [
                    'pattern' => '/(?<![\pL\pN])' . preg_quote($search, '/') . '(?![\pL\pN])/u',
                    'replace' => $replace,
                ];
            }
        }

        foreach ($patterns as $rule) {
            $text = preg_replace($rule['pattern'], $rule['replace'], $text) ?? $text;
        }

        return $text;
    }
}
