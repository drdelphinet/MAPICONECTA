<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Medal;
use App\Models\Municipality;
use App\Models\QuizProgress;

final class MyMapiController extends Controller
{
    public function __construct(
        private readonly QuizProgress $progress = new QuizProgress(),
        private readonly Medal $medals = new Medal(),
        private readonly Municipality $municipalities = new Municipality()
    ) {
    }

    public function index(Request $request): void
    {
        $user = Auth::user();
        $stats = $this->progress->userStats((int) $user['id']);
        $history = $this->progress->userHistory((int) $user['id']);
        $ranking = $this->progress->ranking(10);

        $this->view('meu-mapi/index', [
            'title' => 'Meu MAPI',
            'user' => $user,
            'stats' => [
                'pontuacao_total' => (int) ($stats['pontuacao_total'] ?? 0),
                'medalhas' => $this->medals->countForUser((int) $user['id']),
                'municipios_visitados' => $this->municipalities->visitedCountForUser((int) $user['id']),
                'municipios_favoritos' => $this->municipalities->favoritesCountForUser((int) $user['id']),
                'quizzes_respondidos' => (int) ($stats['quizzes_respondidos'] ?? 0),
                'exploracao_percentual' => $this->municipalities->explorationPercentForUser((int) $user['id']),
            ],
            'medals' => $this->medals->listForUser((int) $user['id']),
            'history' => $history,
            'ranking' => $ranking,
            'favoriteMunicipalities' => $this->municipalities->favoriteListForUser((int) $user['id']),
            'visitedMunicipalities' => $this->municipalities->visitedListForUser((int) $user['id']),
            'recommendedMunicipalities' => $this->municipalities->recommendedPublicForUser((int) $user['id']),
        ]);
    }
}
