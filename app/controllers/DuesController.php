<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/StudentDue.php';
require_once __DIR__ . '/../models/SchoolClass.php';
require_once __DIR__ . '/../models/Payment.php';

// Student dues lookup, shared between Super Admin (view-only) and Accountant
// (view + apply concession). Payment collection itself lives in PaymentController.
class DuesController
{
    public function search(): void
    {
        Auth::requireRole('super_admin', 'accountant');
        $pageTitle = 'Dues Lookup';
        $query = trim($_GET['q'] ?? '');
        $classId = (int)($_GET['class_id'] ?? 0);
        $classes = SchoolClass::all();

        $students = [];
        if ($query !== '' || $classId > 0) {
            $students = Student::all([
                'search' => $query !== '' ? $query : null,
                'class_id' => $classId > 0 ? $classId : null,
            ]);
        }

        require __DIR__ . '/../views/fees/dues_search.php';
    }

    public function view(): void
    {
        Auth::requireRole('super_admin', 'accountant');
        $studentId = (int)($_GET['student_id'] ?? 0);
        $student = Student::find($studentId);
        if (!$student) {
            flash_set('error', 'Student not found.');
            redirect('dues');
        }

        $pageTitle = 'Student Dues';
        $dues = StudentDue::forStudent($studentId);
        $payments = Payment::forStudent($studentId);
        require __DIR__ . '/../views/fees/dues_view.php';
    }

    public function concessionStore(): void
    {
        Auth::requireRole('accountant', 'super_admin');
        Csrf::requireValid();

        $dueId = (int)($_POST['due_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $amount = (float)($_POST['concession_amount'] ?? 0);
        $reason = trim($_POST['concession_reason'] ?? '');

        $due = StudentDue::find($dueId);
        if (!$due || (int)$due['student_id'] !== $studentId) {
            flash_set('error', 'Due item not found.');
            redirect('dues/view?student_id=' . $studentId);
        }

        $maxConcession = (float)$due['base_amount'] - (float)$due['amount_paid'];
        if ($amount < 0 || $amount > $maxConcession) {
            flash_set('error', 'Concession amount must be between 0 and ' . inr($maxConcession) . '.');
            redirect('dues/view?student_id=' . $studentId);
        }
        if ($amount > 0 && $reason === '') {
            flash_set('error', 'A reason is required when applying a concession.');
            redirect('dues/view?student_id=' . $studentId);
        }

        StudentDue::applyConcession($dueId, $amount, $reason ?: null, Auth::id());
        flash_set('success', 'Concession applied.');
        redirect('dues/view?student_id=' . $studentId);
    }
}
