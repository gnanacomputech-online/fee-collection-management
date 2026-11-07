<?php

class FeeHead
{
    public static function all(): array
    {
        return Database::connect()->query('SELECT * FROM fee_heads ORDER BY name')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM fee_heads WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM fee_heads WHERE name = ?';
        $params = [$name];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(string $name, ?string $description): int
    {
        $stmt = Database::connect()->prepare('INSERT INTO fee_heads (name, description) VALUES (?, ?)');
        $stmt->execute([$name, $description]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function update(int $id, string $name, ?string $description): void
    {
        $stmt = Database::connect()->prepare('UPDATE fee_heads SET name = ?, description = ? WHERE id = ?');
        $stmt->execute([$name, $description, $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) FROM fee_structure_items WHERE fee_head_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false; // in use by a fee structure, refuse to delete
        }
        Database::connect()->prepare('DELETE FROM fee_heads WHERE id = ?')->execute([$id]);
        return true;
    }
}
