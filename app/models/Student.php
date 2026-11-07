<?php

class Student
{
    private static function baseSelect(): string
    {
        return 'SELECT st.*, u.name, u.email, u.status AS user_status,
                        c.name AS class_name, c.section AS class_section, c.academic_year
                 FROM students st
                 JOIN users u ON u.id = st.user_id
                 JOIN classes c ON c.id = st.class_id';
    }

    public static function all(array $filters = []): array
    {
        $sql = self::baseSelect() . ' WHERE 1=1';
        $params = [];

        if (!empty($filters['class_id'])) {
            $sql .= ' AND st.class_id = ?';
            $params[] = $filters['class_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (u.name LIKE ? OR st.admission_no LIKE ? OR u.email LIKE ?)';
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY c.name, c.section, st.roll_no';

        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare(self::baseSelect() . ' WHERE st.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = Database::connect()->prepare(self::baseSelect() . ' WHERE st.user_id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function admissionExists(string $admissionNo, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM students WHERE admission_no = ?';
        $params = [$admissionNo];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Creates the login (users) row and the students row together, then materializes
     * this student's dues against every active fee structure already defined for their class.
     */
    public static function create(
        string $name,
        string $email,
        string $password,
        string $admissionNo,
        int $classId,
        int $rollNo,
        ?string $guardianName,
        ?string $guardianPhone,
        ?string $dob,
        ?string $address
    ): int {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $userId = User::create($name, $email, $password, 'student');

            $stmt = $pdo->prepare(
                'INSERT INTO students (user_id, admission_no, class_id, roll_no, guardian_name, guardian_phone, dob, address, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active")'
            );
            $stmt->execute([$userId, $admissionNo, $classId, $rollNo, $guardianName, $guardianPhone, $dob, $address]);
            $studentId = (int)$pdo->lastInsertId();

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        StudentDue::generateForStudent($studentId);

        return $studentId;
    }

    public static function toggleStatus(int $studentId): void
    {
        $student = self::find($studentId);
        if (!$student) {
            return;
        }
        User::toggleStatus((int)$student['user_id']);
        Database::connect()
            ->prepare("UPDATE students SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?")
            ->execute([$studentId]);
    }

    public static function countActive(): int
    {
        return (int)Database::connect()->query("SELECT COUNT(*) FROM students WHERE status = 'active'")->fetchColumn();
    }
}
