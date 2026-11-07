<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/StudentDue.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../../core/Pdf.php';

class PaymentController
{
    public function collectForm(): void
    {
        Auth::requireRole('accountant');
        $studentId = (int)($_GET['student_id'] ?? 0);
        $student = Student::find($studentId);
        if (!$student) {
            flash_set('error', 'Student not found.');
            redirect('dues');
        }

        $pageTitle = 'Collect Payment';
        $outstanding = StudentDue::outstandingForStudent($studentId);
        require __DIR__ . '/../views/fees/payment_form.php';
    }

    public function store(): void
    {
        Auth::requireRole('accountant');
        Csrf::requireValid();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $student = Student::find($studentId);
        if (!$student) {
            flash_set('error', 'Student not found.');
            redirect('dues');
        }

        $method = $_POST['method'] ?? '';
        $referenceNo = trim($_POST['reference_no'] ?? '') ?: null;
        $remarks = trim($_POST['remarks'] ?? '') ?: null;

        $v = new Validator();
        $v->inArray($_POST, 'method', ['cash', 'cheque', 'online'], 'Payment method');
        if ($v->fails()) {
            flash_set('error', 'Invalid payment method.');
            redirect('payments/collect?student_id=' . $studentId);
        }

        $dueIds = $_POST['due_id'] ?? [];
        $amountsIn = $_POST['pay_amount'] ?? [];
        $outstanding = StudentDue::outstandingForStudent($studentId);
        $balances = [];
        foreach ($outstanding as $due) {
            $balances[(int)$due['id']] = (float)$due['balance'];
        }

        $allocations = [];
        foreach ($dueIds as $idx => $dueId) {
            $dueId = (int)$dueId;
            $amount = isset($amountsIn[$idx]) ? (float)$amountsIn[$idx] : 0;
            if ($amount <= 0 || !isset($balances[$dueId])) {
                continue;
            }
            if ($amount > $balances[$dueId] + 0.001) {
                flash_set('error', 'Payment amount for one of the fee items exceeds its outstanding balance.');
                redirect('payments/collect?student_id=' . $studentId);
            }
            $allocations[$dueId] = $amount;
        }

        if (empty($allocations)) {
            flash_set('error', 'Enter an amount against at least one fee item.');
            redirect('payments/collect?student_id=' . $studentId);
        }

        $result = Payment::record($studentId, $allocations, $method, $referenceNo, $remarks, Auth::id());

        flash_set('success', 'Payment recorded. Receipt ' . $result['receipt_no'] . ' generated.');
        redirect('payments/receipt?id=' . $result['id']);
    }

    public function receipt(): void
    {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $payment = Payment::find($id);
        if (!$payment || !Payment::canView($payment, Auth::role(), Auth::studentId())) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $allocations = Payment::allocations($id);
        $pageTitle = 'Receipt ' . $payment['receipt_no'];
        require __DIR__ . '/../views/fees/receipt.php';
    }

    public function receiptDownload(): void
    {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $payment = Payment::find($id);
        if (!$payment || !Payment::canView($payment, Auth::role(), Auth::studentId())) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $allocations = Payment::allocations($id);
        ob_start();
        require __DIR__ . '/../views/fees/receipt_pdf.php';
        $html = ob_get_clean();

        $pdfBytes = Pdf::fromHtml($html);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $payment['receipt_no'] . '.pdf"');
        header('Content-Length: ' . strlen($pdfBytes));
        echo $pdfBytes;
        exit;
    }
}
