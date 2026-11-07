<?php

class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = ?';
        $params = [$email];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        $stmt = Database::connect()->prepare(
            'INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, "active")'
        );
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function allByRole(string $role): array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE role = ? ORDER BY name');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public static function toggleStatus(int $id): void
    {
        Database::connect()->prepare(
            "UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?"
        )->execute([$id]);
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        Database::connect()
            ->prepare('UPDATE users SET password = ? WHERE id = ?')
            ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }
}
