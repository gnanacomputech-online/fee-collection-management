<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/StudentDue.php';
require_once __DIR__ . '/../models/Payment.php';

class StudentController
{
    private function guard(): array
    {
        Auth::requireRole('student');
        $studentId = Auth::studentId();
        $student = $studentId ? Student::find($studentId) : null;
        if (!$student) {
            // Defensive: a student user without a linked students row shouldn't happen via normal flows.
            Auth::logout();
            redirect('login');
        }
        return $student;
    }

    public function dashboard(): void
    {
        $student = $this->guard();
        $pageTitle = 'Dashboard';

        $dues = StudentDue::forStudent((int)$student['id']);
        $totalOutstanding = array_sum(array_map(static fn($d) => max(0, (float)$d['balance']), $dues));
        $overdueCount = 0;
        $nextDue = null;
        foreach ($dues as $d) {
            if ((float)$d['balance'] > 0) {
                if (strtotime($d['due_date']) < strtotime(date('Y-m-d'))) {
                    $overdueCount++;
                }
                if ($nextDue === null || $d['due_date'] < $nextDue['due_date']) {
                    $nextDue = $d;
                }
            }
        }

        require __DIR__ . '/../views/student/dashboard.php';
    }

    public function dues(): void
    {
        $student = $this->guard();
        $pageTitle = 'My Dues';
        $dues = StudentDue::forStudent((int)$student['id']);
        require __DIR__ . '/../views/student/dues.php';
    }

    public function history(): void
    {
        $student = $this->guard();
        $pageTitle = 'Payment History';
        $payments = Payment::forStudent((int)$student['id']);
        require __DIR__ . '/../views/student/history.php';
    }
}
