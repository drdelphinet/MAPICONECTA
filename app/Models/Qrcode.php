<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Qrcode
{
    public function findByEntity(string $entityType, int $entityId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT * FROM qrcodes WHERE entidade_tipo = :tipo AND entidade_id = :entidade LIMIT 1'
        );
        $statement->execute([
            'tipo' => $entityType,
            'entidade' => $entityId,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function ensureForMunicipality(array $municipality): array
    {
        $publicUrl = absolute_url('municipio/' . (string) $municipality['slug']);
        $existing = $this->findByEntity('municipio', (int) $municipality['id_municipio']);

        if ($existing !== null) {
            $statement = Database::connection()->prepare(
                'UPDATE qrcodes
                 SET url_destino = :url_destino,
                     status = :status,
                     atualizado_em = NOW()
                 WHERE id_qrcode = :id'
            );
            $statement->execute([
                'url_destino' => $publicUrl,
                'status' => 'ativo',
                'id' => (int) $existing['id_qrcode'],
            ]);

            return $this->findByEntity('municipio', (int) $municipality['id_municipio']) ?? $existing;
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO qrcodes (entidade_tipo, entidade_id, url_destino, arquivo_qrcode, total_acessos, status)
             VALUES (:tipo, :entidade, :url_destino, NULL, 0, :status)'
        );
        $statement->execute([
            'tipo' => 'municipio',
            'entidade' => (int) $municipality['id_municipio'],
            'url_destino' => $publicUrl,
            'status' => 'ativo',
        ]);

        return $this->findByEntity('municipio', (int) $municipality['id_municipio']) ?? [];
    }

    public function trackingUrlForMunicipality(array $municipality): string
    {
        return absolute_url('qrcode/municipio/' . (string) $municipality['slug']);
    }

    public function imageUrl(string $trackingUrl, int $size = 320): string
    {
        $size = max(120, min(1000, $size));

        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . rawurlencode($trackingUrl);
    }

    public function registerAccess(int $qrcodeId, ?string $origin = null): void
    {
        $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        $userAgent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

        $statement = Database::connection()->prepare(
            'INSERT INTO qrcode_acessos (id_qrcode, ip_hash, user_agent, origem)
             VALUES (:qrcode, :ip_hash, :user_agent, :origem)'
        );
        $statement->execute([
            'qrcode' => $qrcodeId,
            'ip_hash' => $ip !== '' ? hash('sha256', $ip) : null,
            'user_agent' => $userAgent !== '' ? mb_substr($userAgent, 0, 255) : null,
            'origem' => $origin !== null && $origin !== '' ? mb_substr($origin, 0, 100) : null,
        ]);

        $update = Database::connection()->prepare(
            'UPDATE qrcodes SET total_acessos = total_acessos + 1, atualizado_em = NOW() WHERE id_qrcode = :id'
        );
        $update->execute(['id' => $qrcodeId]);
    }
}
