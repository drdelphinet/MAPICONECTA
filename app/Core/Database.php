<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;
    private static array $tableExistsCache = [];
    private static array $tableColumnsCache = [];
    private const REQUIRED_TABLES = [
        'perfis',
        'usuarios',
        'estados',
        'municipios',
        'quizzes',
        'perguntas_quiz',
        'progresso_quiz_usuario',
        'medalhas',
    ];

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = self::databaseConfig();

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $timeout = max(1, (int) ($config['timeout'] ?? 3));
            self::$connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => $timeout,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Nao foi possivel conectar ao banco de dados: ' . $exception->getMessage(), 0, $exception);
        }

        return self::$connection;
    }

    private static function databaseConfig(): array
    {
        $path = app_path('config/database.php');
        $config = file_exists($path) ? require $path : null;

        if (!is_array($config)) {
            throw new RuntimeException('Configuracao de banco de dados nao encontrada em config/database.php.');
        }

        $requiredKeys = ['host', 'port', 'database', 'charset', 'username', 'password', 'timeout'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $config)) {
                throw new RuntimeException("Configuracao de banco incompleta: chave obrigatoria ausente '{$key}'.");
            }
        }

        return $config;
    }

    public static function status(): array
    {
        $config = self::databaseConfig();
        $status = [
            'ready' => false,
            'can_connect' => false,
            'has_schema' => false,
            'database' => (string) $config['database'],
            'missing_tables' => self::REQUIRED_TABLES,
            'error' => null,
        ];

        try {
            $pdo = self::connection();
            $status['can_connect'] = true;

            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $status['missing_tables'] = array_values(array_diff(self::REQUIRED_TABLES, $tables));
            $status['has_schema'] = $status['missing_tables'] === [];
            $status['ready'] = $status['can_connect'] && $status['has_schema'];
        } catch (RuntimeException $exception) {
            $status['error'] = $exception->getMessage();
        } catch (PDOException $exception) {
            $status['error'] = $exception->getMessage();
        }

        return $status;
    }

    public static function isReady(): bool
    {
        return self::status()['ready'] === true;
    }

    public static function tableExists(string $table): bool
    {
        if (array_key_exists($table, self::$tableExistsCache)) {
            return self::$tableExistsCache[$table];
        }

        $statement = self::connection()->prepare(
            'SELECT 1
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table
             LIMIT 1'
        );
        $statement->execute(['table' => $table]);

        self::$tableExistsCache[$table] = $statement->fetchColumn() !== false;

        return self::$tableExistsCache[$table];
    }

    public static function columnExists(string $table, string $column): bool
    {
        $columns = self::tableColumns($table);

        return in_array($column, $columns, true);
    }

    public static function tableColumns(string $table): array
    {
        if (array_key_exists($table, self::$tableColumnsCache)) {
            return self::$tableColumnsCache[$table];
        }

        if (!self::tableExists($table)) {
            self::$tableColumnsCache[$table] = [];
            return [];
        }

        $statement = self::connection()->prepare(
            'SELECT column_name
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = :table
             ORDER BY ordinal_position ASC'
        );
        $statement->execute(['table' => $table]);

        self::$tableColumnsCache[$table] = array_map(
            static fn (mixed $value): string => (string) $value,
            $statement->fetchAll(PDO::FETCH_COLUMN) ?: []
        );

        return self::$tableColumnsCache[$table];
    }
}
