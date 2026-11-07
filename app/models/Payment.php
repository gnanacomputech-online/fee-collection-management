<?php

class Payment
{
    /**
     * Records a payment against one or more of a student's outstanding dues in a single
     * transaction: inserts the payment, splits it across the given due->amount allocations
     * (this is what makes partial payments work / lets one payment clear several fee items),
     * and updates each due's amount_paid + status.
     *
     * @param array<int,float> $allocations dueId => amount allocated to that due
     */
    public static function record(
        int $studentId,
        array $allocations,
        string $method,
        ?string $referenceNo,
        ?string $remarks,
        int $recordedBy
    ): array {
        $pdo = Database::connect();
        $total = array_sum($allocations);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO payments (student_id, receipt_no, amount, method, reference_no, remarks, recorded_by, paid_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            // Placeholder receipt number, replaced below once we know the auto-increment id.
            $stmt->execute([$studentId, 'PENDING', $total, $method, $referenceNo, $remarks, $recordedBy]);
            $paymentId = (int)$pdo->lastInsertId();

            $receiptNo = sprintf('RCPT-%s-%04d', date('Y'), $paymentId);
            $pdo->prepare('UPDATE payments SET receipt_no = ? WHERE id = ?')->execute([$receiptNo, $paymentId]);

            $allocStmt = $pdo->prepare(
                'INSERT INTO payment_allocations (payment_id, student_due_id, allocated_amount) VALUES (?, ?, ?)'
            );
            foreach ($allocations as $dueId => $amount) {
                if ($amount <= 0) {
                    continue;
                }
                $allocStmt->execute([$paymentId, $dueId, $amount]);
                StudentDue::applyPayment($pdo, (int)$dueId, (float)$amount);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['id' => $paymentId, 'receipt_no' => $receiptNo];
    }

    public static function find(int $id): ?array
    {
        $sql = 'SELECT p.*, st.admission_no, u.name AS student_name, c.name AS class_name, c.section AS class_section,
                       ru.name AS recorded_by_name
                FROM payments p
                JOIN students st ON st.id = p.student_id
                JOIN users u ON u.id = st.user_id
                JOIN classes c ON c.id = st.class_id
                JOIN users ru ON ru.id = p.recorded_by
                WHERE p.id = ?';
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function allocations(int $paymentId): array
    {
        $sql = "SELECT pa.*, fh.name AS fee_head_name, fs.term
                FROM payment_allocations pa
                JOIN student_dues sd ON sd.id = pa.student_due_id
                JOIN fee_structure_items fsi ON fsi.id = sd.fee_structure_item_id
                JOIN fee_heads fh ON fh.id = fsi.fee_head_id
                JOIN fee_structures fs ON fs.id = fsi.fee_structure_id
                WHERE pa.payment_id = ?
                ORDER BY fh.name";
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute([$paymentId]);
        return $stmt->fetchAll();
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = Database::connect()->prepare(
            'SELECT * FROM payments WHERE student_id = ? ORDER BY paid_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    /** Whether $userId (accountant/admin) or the owning student may view this receipt. */
    public static function canView(array $payment, string $role, ?int $studentId): bool
    {
        if (in_array($role, ['super_admin', 'accountant'], true)) {
            return true;
        }
        return $role === 'student' && $studentId !== null && (int)$payment['student_id'] === $studentId;
    }
}
