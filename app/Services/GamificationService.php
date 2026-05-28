<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Medal;
use App\Models\QuizProgress;

final class GamificationService
{
    public function __construct(
        private readonly QuizProgress $progress = new QuizProgress(),
        private readonly Medal $medals = new Medal()
    ) {
    }

    public function syncUserMedals(int $userId): array
    {
        $stats = $this->progress->userStats($userId);
        $points = (int) ($stats['pontuacao_total'] ?? 0);
        $awarded = [];

        foreach ($this->medals->allByThreshold() as $medal) {
            if ($points >= (int) $medal['pontos_necessarios']) {
                $this->medals->awardToUser($userId, (int) $medal['id_medalha']);
                $awarded[] = $medal;
            }
        }

        return $awarded;
    }
}
