<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Survey
{
    /** @return array<int, array<string,mixed>> */
    public static function all(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT id, title, created_by, created_at FROM surveys ORDER BY id DESC');
        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, title, created_by, created_at FROM surveys WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $title, int $createdBy): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO surveys (title, created_by, created_at) VALUES (:title, :created_by, NOW())');
        $stmt->execute(['title' => $title, 'created_by' => $createdBy]);
        return (int)$pdo->lastInsertId();
    }
}
