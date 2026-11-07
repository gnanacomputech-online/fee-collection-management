<?php

class StudentDue
{
    private static function baseSelect(): string
    {
        return "SELECT sd.*,
                        fh.name AS fee_head_name,
                        fs.term, fs.academic_year, fs.late_fee_type, fs.late_fee_value, fs.grace_days,
                        fsi.fee_structure_id,
                        (sd.base_amount - sd.concession_amount + sd.late_fee_amount) AS net_payable,
                        (sd.base_amount - sd.concession_amount + sd.late_fee_amount - sd.amount_paid) AS balance
                 FROM student_dues sd
                 JOIN fee_structure_items fsi ON fsi.id = sd.fee_structure_item_id
                 JOIN fee_heads fh ON fh.id = fsi.fee_head_id
                 JOIN fee_structures fs ON fs.id = fsi.fee_structure_id";
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare(self::baseSelect() . ' WHERE sd.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function forStudent(int $studentId, ?string $status = null): array
    {
        $sql = self::baseSelect() . ' WHERE sd.student_id = ?';
        $params = [$studentId];
        if ($status !== null) {
            $sql .= ' AND sd.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY fs.due_date, fh.name';

        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Dues with an outstanding balance, for the payment-collection screen. */
    public static function outstandingForStudent(int $studentId): array
    {
        $sql = self::baseSelect() . " WHERE sd.student_id = ? AND sd.status != 'paid'
                ORDER BY fs.due_date, fh.name";
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Materialize dues for every active student in a fee structure's class, one row
     * per fee-head line item, skipping any that already exist (idempotent).
     */
    public static function generateForStructure(int $feeStructureId): void
    {
        $sql = 'INSERT INTO student_dues (student_id, fee_structure_item_id, base_amount, due_date, status)
                SELECT s.id, fsi.id, fsi.amount, fs.due_date, "pending"
                FROM students s
                JOIN fee_structures fs ON fs.id = ?
                JOIN fee_structure_items fsi ON fsi.fee_structure_id = fs.id
                WHERE s.class_id = fs.class_id AND s.status = "active"
                AND NOT EXISTS (
                    SELECT 1 FROM student_dues sd
                    WHERE sd.student_id = s.id AND sd.fee_structure_item_id = fsi.id
                )';
        Database::connect()->prepare($sql)->execute([$feeStructureId]);
    }

    /**
     * Materialize dues for one student against every active fee structure already
     * defined for their class -- called after enrolling a student (single or bulk import).
     */
    public static function generateForStudent(int $studentId): void
    {
        $sql = 'INSERT INTO student_dues (student_id, fee_structure_item_id, base_amount, due_date, status)
                SELECT ?, fsi.id, fsi.amount, fs.due_date, "pending"
                FROM students s
                JOIN fee_structures fs ON fs.class_id = s.class_id AND fs.status = "active"
                JOIN fee_structure_items fsi ON fsi.fee_structure_id = fs.id
                WHERE s.id = ?
                AND NOT EXISTS (
                    SELECT 1 FROM student_dues sd
                    WHERE sd.student_id = s.id AND sd.fee_structure_item_id = fsi.id
                )';
        Database::connect()->prepare($sql)->execute([$studentId, $studentId]);
    }

    /**
     * Applies the late fee to every due whose (due_date + grace_days) has passed,
     * that isn't already fully paid and hasn't had a late fee applied yet.
     *
     * In production this would run as a daily cron job; for this local/XAMPP demo
     * it is invoked opportunistically at the top of every request (see public/index.php)
     * so an overdue due picks up its late fee the moment its due date+grace period lapses,
     * without needing a real OS-level scheduler.
     *
     * @return int number of due rows the late fee was newly applied to
     */
    public static function applyLateFees(): int
    {
        $sql = "UPDATE student_dues sd
                JOIN fee_structure_items fsi ON fsi.id = sd.fee_structure_item_id
                JOIN fee_structures fs ON fs.id = fsi.fee_structure_id
                SET sd.late_fee_amount = CASE
                        WHEN fs.late_fee_type = 'flat' THEN fs.late_fee_value
                        ELSE ROUND(sd.base_amount * fs.late_fee_value / 100, 2)
                    END,
                    sd.late_fee_applied_at = NOW(),
                    sd.status = CASE
                        WHEN sd.amount_paid <= 0 THEN 'pending'
                        WHEN sd.amount_paid < (sd.base_amount - sd.concession_amount + CASE
                                WHEN fs.late_fee_type = 'flat' THEN fs.late_fee_value
                                ELSE ROUND(sd.base_amount * fs.late_fee_value / 100, 2)
                            END) THEN 'partial'
                        ELSE 'paid'
                    END
                WHERE sd.late_fee_applied_at IS NULL
                  AND sd.status != 'paid'
                  AND DATE_ADD(sd.due_date, INTERVAL fs.grace_days DAY) < CURDATE()";
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function applyConcession(int $dueId, float $amount, ?string $reason, int $byUserId): void
    {
        $stmt = Database::connect()->prepare(
            'UPDATE student_dues
             SET concession_amount = ?, concession_reason = ?, concession_by = ?, concession_at = NOW(),
                 status = CASE
                     WHEN amount_paid <= 0 THEN "pending"
                     WHEN amount_paid < (base_amount - ? + late_fee_amount) THEN "partial"
                     ELSE "paid"
                 END
             WHERE id = ?'
        );
        $stmt->execute([$amount, $reason, $byUserId, $amount, $dueId]);
    }

    /** Applies a payment allocation to one due row: bumps amount_paid, recomputes status. */
    public static function applyPayment(PDO $pdo, int $dueId, float $amount): void
    {
        $stmt = $pdo->prepare(
            'UPDATE student_dues
             SET amount_paid = amount_paid + ?,
                 status = CASE
                     WHEN (amount_paid + ?) <= 0 THEN "pending"
                     WHEN (amount_paid + ?) < (base_amount - concession_amount + late_fee_amount) THEN "partial"
                     ELSE "paid"
                 END
             WHERE id = ?'
        );
        $stmt->execute([$amount, $amount, $amount, $dueId]);
    }

    public static function defaulters(array $filters = []): array
    {
        $sql = "SELECT sd.*,
                       fh.name AS fee_head_name,
                       fs.term, fs.academic_year, fs.late_fee_type, fs.late_fee_value, fs.grace_days,
                       (sd.base_amount - sd.concession_amount + sd.late_fee_amount) AS net_payable,
                       (sd.base_amount - sd.concession_amount + sd.late_fee_amount - sd.amount_paid) AS balance,
                       st.admission_no, u.name AS student_name, c.id AS class_id, c.name AS class_name, c.section AS class_section
                FROM student_dues sd
                JOIN fee_structure_items fsi ON fsi.id = sd.fee_structure_item_id
                JOIN fee_heads fh ON fh.id = fsi.fee_head_id
                JOIN fee_structures fs ON fs.id = fsi.fee_structure_id
                JOIN students st ON st.id = sd.student_id
                JOIN users u ON u.id = st.user_id
                JOIN classes c ON c.id = st.class_id
                WHERE sd.status != 'paid' AND sd.due_date < CURDATE()";
        $params = [];

        if (!empty($filters['class_id'])) {
            $sql .= ' AND c.id = ?';
            $params[] = $filters['class_id'];
        }

        $sql .= ' ORDER BY sd.due_date, u.name';

        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
