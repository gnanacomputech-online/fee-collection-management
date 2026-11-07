<?php

class Report
{
    public static function todaysCollection(): float
    {
        $stmt = Database::connect()->query(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(paid_at) = CURDATE()"
        );
        return (float)$stmt->fetchColumn();
    }

    public static function totalOutstanding(): float
    {
        $sql = 'SELECT COALESCE(SUM(base_amount - concession_amount + late_fee_amount - amount_paid), 0)
                FROM student_dues WHERE status != "paid"';
        return (float)Database::connect()->query($sql)->fetchColumn();
    }

    public static function defaultersCount(): int
    {
        return (int)Database::connect()->query(
            "SELECT COUNT(*) FROM student_dues WHERE status != 'paid' AND due_date < CURDATE()"
        )->fetchColumn();
    }

    public static function totalCollectedBy(int $recordedBy): float
    {
        $stmt = Database::connect()->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE recorded_by = ? AND DATE(paid_at) = CURDATE()"
        );
        $stmt->execute([$recordedBy]);
        return (float)$stmt->fetchColumn();
    }

    /** Daily collection totals within an optional date range. */
    public static function collectionByDay(?string $from, ?string $to): array
    {
        $sql = 'SELECT DATE(paid_at) AS day, COUNT(*) AS payment_count, SUM(amount) AS total
                FROM payments WHERE 1=1';
        $params = [];
        if ($from) {
            $sql .= ' AND DATE(paid_at) >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND DATE(paid_at) <= ?';
            $params[] = $to;
        }
        $sql .= ' GROUP BY DATE(paid_at) ORDER BY day DESC';
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function collectionByClass(?string $from, ?string $to): array
    {
        $sql = "SELECT c.name AS class_name, c.section AS class_section, COUNT(*) AS payment_count, SUM(p.amount) AS total
                FROM payments p
                JOIN students st ON st.id = p.student_id
                JOIN classes c ON c.id = st.class_id
                WHERE 1=1";
        $params = [];
        if ($from) {
            $sql .= ' AND DATE(p.paid_at) >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND DATE(p.paid_at) <= ?';
            $params[] = $to;
        }
        $sql .= ' GROUP BY c.id ORDER BY c.name, c.section';
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function collectionByFeeHead(?string $from, ?string $to): array
    {
        $sql = "SELECT fh.name AS fee_head_name, SUM(pa.allocated_amount) AS total
                FROM payment_allocations pa
                JOIN payments p ON p.id = pa.payment_id
                JOIN student_dues sd ON sd.id = pa.student_due_id
                JOIN fee_structure_items fsi ON fsi.id = sd.fee_structure_item_id
                JOIN fee_heads fh ON fh.id = fsi.fee_head_id
                WHERE 1=1";
        $params = [];
        if ($from) {
            $sql .= ' AND DATE(p.paid_at) >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND DATE(p.paid_at) <= ?';
            $params[] = $to;
        }
        $sql .= ' GROUP BY fh.id ORDER BY fh.name';
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
