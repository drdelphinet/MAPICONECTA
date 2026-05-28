<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;
use RuntimeException;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->view('auth/login', [
            'title' => 'Entrar',
            'message' => Session::getFlash('message'),
        ], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('entrar', 'error', 'Sua sessao expirou. Tente novamente.');
        }

        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        $_SESSION['_old_input'] = ['email' => $email];

        if ($email === '' || $password === '') {
            $this->redirectWithMessage('entrar', 'warning', 'Informe email e senha.');
        }

        $user = (new User())->findByEmail($email);

        if ($user === null || !password_verify($password, $user['senha'])) {
            $this->redirectWithMessage('entrar', 'error', 'Credenciais invalidas.');
        }

        if (($user['status'] ?? 'ativo') !== 'ativo') {
            $this->redirectWithMessage('entrar', 'warning', 'Sua conta nao esta ativa.');
        }

        unset($_SESSION['_old_input']);

        Auth::login($user);
        $destination = Auth::hasRole(['administrador-geral', 'curador', 'editor']) ? 'admin' : 'meu-mapi';

        $this->redirectWithMessage($destination, 'success', 'Login realizado com sucesso.');
    }

    public function logout(Request $request): void
    {
        if ($request->isPost()) {
            try {
                Csrf::validate((string) $request->input('_token'));
            } catch (RuntimeException $exception) {
                $this->redirectWithMessage('', 'error', 'Nao foi possivel encerrar a sessao.');
            }
        }

        Auth::logout();
        $this->redirectWithMessage('', 'success', 'Sessao encerrada.');
    }
}
