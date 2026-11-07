<?php

require_once __DIR__ . '/../models/Report.php';

class AccountantController
{
    public function dashboard(): void
    {
        Auth::requireRole('accountant');
        $pageTitle = 'Dashboard';

        $pdo = Database::connect();
        $stats = [
            'todays_collection' => Report::todaysCollection(),
            'my_collection_today' => Report::totalCollectedBy(Auth::id()),
            'total_outstanding' => Report::totalOutstanding(),
            'defaulters' => Report::defaultersCount(),
        ];

        $recentPayments = $pdo->prepare(
            "SELECT p.*, u.name AS student_name, st.admission_no
             FROM payments p
             JOIN students st ON st.id = p.student_id
             JOIN users u ON u.id = st.user_id
             WHERE p.recorded_by = ?
             ORDER BY p.paid_at DESC LIMIT 8"
        );
        $recentPayments->execute([Auth::id()]);
        $recentPayments = $recentPayments->fetchAll();

        require __DIR__ . '/../views/accountant/dashboard.php';
    }
}
