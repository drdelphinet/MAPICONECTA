<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId === null) {
            return null;
        }

        return (new User())->findById((int) $userId);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['user_id'] = (int) $user['id'];
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
        Session::regenerate();
    }

    public static function hasRole(array|string $roles): bool
    {
        $user = self::user();

        if ($user === null) {
            return false;
        }

        $roles = (array) $roles;

        return in_array($user['role_slug'], $roles, true);
    }
}
