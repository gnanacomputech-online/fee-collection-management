<?php

class FeeStructure
{
    private static function baseSelect(): string
    {
        return 'SELECT fs.*, c.name AS class_name, c.section AS class_section
                 FROM fee_structures fs
                 JOIN classes c ON c.id = fs.class_id';
    }

    public static function all(): array
    {
        $stmt = Database::connect()->query(
            self::baseSelect() . ' ORDER BY fs.academic_year DESC, c.name, c.section, fs.term'
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare(self::baseSelect() . ' WHERE fs.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function items(int $feeStructureId): array
    {
        $stmt = Database::connect()->prepare(
            'SELECT fsi.*, fh.name AS fee_head_name
             FROM fee_structure_items fsi
             JOIN fee_heads fh ON fh.id = fsi.fee_head_id
             WHERE fsi.fee_structure_id = ?
             ORDER BY fh.name'
        );
        $stmt->execute([$feeStructureId]);
        return $stmt->fetchAll();
    }

    public static function exists(int $classId, string $term, string $academicYear, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM fee_structures WHERE class_id = ? AND term = ? AND academic_year = ?';
        $params = [$classId, $term, $academicYear];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Creates the fee structure with its fee-head line items, then materializes
     * a student_dues row for every active student in that class -- this is the
     * "dues computed automatically from the active fee structure" behaviour.
     *
     * @param array<int,array{fee_head_id:int, amount:float}> $items
     */
    public static function create(
        int $classId,
        string $term,
        string $academicYear,
        string $dueDate,
        string $lateFeeType,
        float $lateFeeValue,
        int $graceDays,
        int $createdBy,
        array $items
    ): int {
        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO fee_structures (class_id, term, academic_year, due_date, late_fee_type, late_fee_value, grace_days, created_by, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active")'
            );
            $stmt->execute([$classId, $term, $academicYear, $dueDate, $lateFeeType, $lateFeeValue, $graceDays, $createdBy]);
            $structureId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO fee_structure_items (fee_structure_id, fee_head_id, amount) VALUES (?, ?, ?)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([$structureId, $item['fee_head_id'], $item['amount']]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        StudentDue::generateForStructure($structureId);

        return $structureId;
    }

    public static function toggleStatus(int $id): void
    {
        Database::connect()
            ->prepare("UPDATE fee_structures SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?")
            ->execute([$id]);
    }
}
