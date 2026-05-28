<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class WikimediaCommonsService
{
    private const API_URL = 'https://commons.wikimedia.org/w/api.php';
    private const SEARCH_URL = 'https://commons.wikimedia.org/w/index.php';
    private const CATEGORY_URL = 'https://commons.wikimedia.org/wiki/Category:';

    public function fetchFileMetadata(string $source): array
    {
        $title = $this->normalizeFileTitle($source);
        if ($title === '') {
            throw new RuntimeException('Informe um titulo de arquivo do Wikimedia Commons ou a URL da pagina do arquivo.');
        }

        $url = self::API_URL . '?' . http_build_query([
            'action' => 'query',
            'format' => 'json',
            'formatversion' => '2',
            'prop' => 'imageinfo',
            'iiprop' => 'url|mime|mediatype|extmetadata',
            'titles' => $title,
        ]);

        $response = $this->fetchJson($url);
        $pages = $response['query']['pages'] ?? [];
        $page = is_array($pages) && $pages !== [] ? $pages[0] : null;

        if (!is_array($page) || !empty($page['missing'])) {
            throw new RuntimeException('Arquivo nao encontrado no Wikimedia Commons.');
        }

        $imageInfo = $page['imageinfo'][0] ?? null;
        if (!is_array($imageInfo)) {
            throw new RuntimeException('O Wikimedia Commons nao retornou metadados suficientes para este arquivo.');
        }

        $metadata = is_array($imageInfo['extmetadata'] ?? null) ? $imageInfo['extmetadata'] : [];
        $canonicalTitle = (string) ($page['title'] ?? $title);
        $description = $this->cleanMetadataValue($metadata['ImageDescription']['value'] ?? '');
        $artist = $this->cleanMetadataValue($metadata['Artist']['value'] ?? '');
        $credit = $this->cleanMetadataValue($metadata['Credit']['value'] ?? '');
        $license = $this->cleanMetadataValue($metadata['LicenseShortName']['value'] ?? $metadata['UsageTerms']['value'] ?? '');
        $licenseUrl = $this->cleanMetadataValue($metadata['LicenseUrl']['value'] ?? '');
        $fileUrl = (string) ($imageInfo['url'] ?? '');

        if ($fileUrl === '') {
            throw new RuntimeException('O Wikimedia Commons nao retornou a URL do arquivo.');
        }

        $pageSlug = str_replace('%3A', ':', rawurlencode(str_replace(' ', '_', $canonicalTitle)));
        $pageUrl = 'https://commons.wikimedia.org/wiki/' . $pageSlug;

        return [
            'title' => preg_replace('/^File:/i', '', $canonicalTitle) ?: $canonicalTitle,
            'canonical_title' => $canonicalTitle,
            'description' => $description,
            'file_url' => $fileUrl,
            'mime' => trim((string) ($imageInfo['mime'] ?? '')),
            'media_type' => trim((string) ($imageInfo['mediatype'] ?? '')),
            'source_url' => $pageUrl,
            'license' => $license !== '' ? $license : 'Licenca nao informada',
            'license_url' => $licenseUrl !== '' ? $licenseUrl : null,
            'credit' => $this->mergeCredits($artist, $credit),
            'artist' => $artist !== '' ? $artist : null,
            'alt_text' => $description !== '' ? $description : preg_replace('/^File:/i', '', $canonicalTitle),
        ];
    }

    public function searchUrl(string $query): string
    {
        return self::SEARCH_URL . '?' . http_build_query([
            'search' => trim($query),
            'title' => 'Special:MediaSearch',
            'type' => 'image',
        ]);
    }

    public function categoryUrl(string $categoryName): string
    {
        $categoryName = trim(str_replace(' ', '_', $categoryName));

        return self::CATEGORY_URL . rawurlencode($categoryName);
    }

    public function searchCandidates(string $query, int $limit = 8): array
    {
        $query = trim($query);
        $limit = max(1, min(20, $limit));

        if ($query === '') {
            return [];
        }

        $url = self::API_URL . '?' . http_build_query([
            'action' => 'query',
            'format' => 'json',
            'formatversion' => '2',
            'generator' => 'search',
            'gsrsearch' => $query,
            'gsrnamespace' => 6,
            'gsrlimit' => $limit,
            'prop' => 'imageinfo',
            'iiprop' => 'url|mime|mediatype|extmetadata',
        ]);

        $response = $this->fetchJson($url);
        $pages = $response['query']['pages'] ?? [];
        if (!is_array($pages)) {
            return [];
        }

        $results = [];
        foreach ($pages as $page) {
            if (!is_array($page) || !empty($page['missing'])) {
                continue;
            }

            $imageInfo = $page['imageinfo'][0] ?? null;
            if (!is_array($imageInfo)) {
                continue;
            }

            $metadata = is_array($imageInfo['extmetadata'] ?? null) ? $imageInfo['extmetadata'] : [];
            $canonicalTitle = (string) ($page['title'] ?? '');
            $pageSlug = str_replace('%3A', ':', rawurlencode(str_replace(' ', '_', $canonicalTitle)));

            $results[] = [
                'title' => preg_replace('/^File:/i', '', $canonicalTitle) ?: $canonicalTitle,
                'canonical_title' => $canonicalTitle,
                'description' => $this->cleanMetadataValue($metadata['ImageDescription']['value'] ?? ''),
                'file_url' => (string) ($imageInfo['url'] ?? ''),
                'mime' => trim((string) ($imageInfo['mime'] ?? '')),
                'media_type' => trim((string) ($imageInfo['mediatype'] ?? '')),
                'source_url' => 'https://commons.wikimedia.org/wiki/' . $pageSlug,
                'license' => $this->cleanMetadataValue($metadata['LicenseShortName']['value'] ?? $metadata['UsageTerms']['value'] ?? ''),
                'credit' => $this->mergeCredits(
                    $this->cleanMetadataValue($metadata['Artist']['value'] ?? ''),
                    $this->cleanMetadataValue($metadata['Credit']['value'] ?? '')
                ),
                'thumbnail_url' => (string) ($imageInfo['thumburl'] ?? $imageInfo['url'] ?? ''),
            ];
        }

        return $results;
    }

    private function normalizeFileTitle(string $source): string
    {
        $source = trim($source);
        if ($source === '') {
            return '';
        }

        $decoded = urldecode($source);
        if (preg_match('~commons\.wikimedia\.org/wiki/([^?#]+)~i', $decoded, $matches) === 1) {
            $decoded = str_replace('_', ' ', $matches[1]);
        }

        $decoded = preg_replace('~^https?://upload\.wikimedia\.org/.*/([^/]+)$~i', '$1', $decoded) ?: $decoded;
        $decoded = trim(str_replace('_', ' ', $decoded));

        if (!str_starts_with($decoded, 'File:') && !str_starts_with($decoded, 'file:')) {
            $decoded = 'File:' . ltrim($decoded, ':');
        }

        return $decoded;
    }

    private function fetchJson(string $url): array
    {
        if (function_exists('curl_init')) {
            $response = $this->fetchWithCurl($url);
        } else {
            $response = $this->fetchWithFileGetContents($url);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Resposta invalida do Wikimedia Commons.');
        }

        return $decoded;
    }

    private function fetchWithFileGetContents(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'header' => "Accept: application/json\r\nUser-Agent: MapiConecta/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            throw new RuntimeException('Falha ao consultar o Wikimedia Commons.');
        }

        return $response;
    }

    private function fetchWithCurl(string $url): string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Nao foi possivel inicializar a consulta ao Wikimedia Commons.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: MapiConecta/1.0',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!is_string($response) || $response === '' || $httpCode >= 400 || $error !== '') {
            throw new RuntimeException('Falha ao consultar o Wikimedia Commons.');
        }

        return $response;
    }

    private function cleanMetadataValue(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return trim($value);
    }

    private function mergeCredits(string $artist, string $credit): ?string
    {
        $parts = array_values(array_filter([$artist, $credit], static fn (string $value): bool => $value !== ''));
        if ($parts === []) {
            return null;
        }

        return implode(' | ', array_unique($parts));
    }
}
