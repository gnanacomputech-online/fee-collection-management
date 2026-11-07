<?php

// Named SchoolClass (not Class) since `class` is a reserved word in PHP.
class SchoolClass
{
    public static function all(): array
    {
        $sql = 'SELECT c.*,
                       (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id AND s.status = "active") AS student_count
                FROM classes c
                ORDER BY c.academic_year DESC, c.name, c.section';
        return Database::connect()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM classes WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function exists(string $name, string $section, string $academicYear, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM classes WHERE name = ? AND section = ? AND academic_year = ?';
        $params = [$name, $section, $academicYear];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(string $name, string $section, string $academicYear): int
    {
        $stmt = Database::connect()->prepare(
            'INSERT INTO classes (name, section, academic_year) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $section, $academicYear]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function update(int $id, string $name, string $section, string $academicYear): void
    {
        $stmt = Database::connect()->prepare(
            'UPDATE classes SET name = ?, section = ?, academic_year = ? WHERE id = ?'
        );
        $stmt->execute([$name, $section, $academicYear, $id]);
    }

    public static function delete(int $id): bool
    {
        // Refuse to delete a class that already has students or fee structures attached.
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE class_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false;
        }
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM fee_structures WHERE class_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false;
        }
        $pdo->prepare('DELETE FROM classes WHERE id = ?')->execute([$id]);
        return true;
    }

    public static function label(array $class): string
    {
        return $class['name'] . ' - ' . $class['section'] . ' (' . $class['academic_year'] . ')';
    }
}
