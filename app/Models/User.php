<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    /** @return array<string,mixed>|null */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
