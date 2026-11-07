<?php

require_once __DIR__ . '/../models/FeeHead.php';
require_once __DIR__ . '/../models/FeeStructure.php';
require_once __DIR__ . '/../models/SchoolClass.php';

// Fee head & fee structure management -- shared between Super Admin and Accountant
// (both may define fee heads and fee structures per the FEATURES.md persona table).
class FeeController
{
    private function guard(): void
    {
        Auth::requireRole('super_admin', 'accountant');
    }

    public function heads(): void
    {
        $this->guard();
        $pageTitle = 'Fee Heads';
        $feeHeads = FeeHead::all();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/fees/heads.php';
    }

    public function headStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $v = new Validator();
        $v->required($_POST, 'name', 'Name')->maxLength($_POST, 'name', 100, 'Name');
        $errors = $v->errors();
        if (empty($errors) && FeeHead::nameExists(trim($_POST['name']))) {
            $errors['name'] = 'A fee head with this name already exists.';
        }

        if (!empty($errors)) {
            flash_set('error', 'Please fix the errors below: ' . implode(' ', $errors));
            redirect('fee-heads');
        }

        FeeHead::create(trim($_POST['name']), trim($_POST['description'] ?? '') ?: null);
        flash_set('success', 'Fee head created.');
        redirect('fee-heads');
    }

    public function headUpdate(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || $name === '') {
            flash_set('error', 'Invalid fee head update.');
            redirect('fee-heads');
        }
        if (FeeHead::nameExists($name, $id)) {
            flash_set('error', 'Another fee head already uses that name.');
            redirect('fee-heads');
        }

        FeeHead::update($id, $name, trim($_POST['description'] ?? '') ?: null);
        flash_set('success', 'Fee head updated.');
        redirect('fee-heads');
    }

    public function headDelete(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        if (!FeeHead::delete($id)) {
            flash_set('error', 'This fee head is used by a fee structure and cannot be deleted.');
        } else {
            flash_set('success', 'Fee head deleted.');
        }
        redirect('fee-heads');
    }

    public function structures(): void
    {
        $this->guard();
        $pageTitle = 'Fee Structures';
        $structures = FeeStructure::all();
        require __DIR__ . '/../views/fees/structures.php';
    }

    public function structureCreateForm(): void
    {
        $this->guard();
        $pageTitle = 'New Fee Structure';
        $classes = SchoolClass::all();
        $feeHeads = FeeHead::all();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/fees/structure_form.php';
    }

    public function structureStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $v = new Validator();
        $v->required($_POST, 'class_id', 'Class')
          ->required($_POST, 'term', 'Term')->maxLength($_POST, 'term', 30, 'Term')
          ->required($_POST, 'academic_year', 'Academic year')
          ->required($_POST, 'due_date', 'Due date')->date($_POST, 'due_date', 'Due date')
          ->inArray($_POST, 'late_fee_type', ['flat', 'percent'], 'Late fee type')
          ->numeric($_POST, 'late_fee_value', 'Late fee value')->min($_POST, 'late_fee_value', 0, 'Late fee value')
          ->numeric($_POST, 'grace_days', 'Grace days')->min($_POST, 'grace_days', 0, 'Grace days');

        $headIds = $_POST['fee_head_id'] ?? [];
        $amounts = $_POST['amount'] ?? [];
        $items = [];
        foreach ($headIds as $idx => $headId) {
            $headId = (int)$headId;
            $amount = $amounts[$idx] ?? '';
            if ($headId > 0 && $amount !== '' && is_numeric($amount) && (float)$amount > 0) {
                $items[$headId] = ['fee_head_id' => $headId, 'amount' => (float)$amount];
            }
        }
        if ($v->fails() || empty($items)) {
            flash_set('error', 'Please fix the errors and include at least one fee head with an amount.');
            redirect('fee-structures/create');
        }

        $classId = (int)$_POST['class_id'];
        $term = trim($_POST['term']);
        $academicYear = trim($_POST['academic_year']);

        if (FeeStructure::exists($classId, $term, $academicYear)) {
            flash_set('error', 'A fee structure for this class, term and academic year already exists.');
            redirect('fee-structures/create');
        }

        FeeStructure::create(
            $classId,
            $term,
            $academicYear,
            $_POST['due_date'],
            $_POST['late_fee_type'],
            (float)$_POST['late_fee_value'],
            (int)$_POST['grace_days'],
            Auth::id(),
            array_values($items)
        );

        flash_set('success', 'Fee structure created and dues generated for all active students in that class.');
        redirect('fee-structures');
    }

    public function structureView(): void
    {
        $this->guard();
        $id = (int)($_GET['id'] ?? 0);
        $structure = FeeStructure::find($id);
        if (!$structure) {
            flash_set('error', 'Fee structure not found.');
            redirect('fee-structures');
        }
        $pageTitle = 'Fee Structure Detail';
        $items = FeeStructure::items($id);
        require __DIR__ . '/../views/fees/structure_view.php';
    }

    public function structureStatus(): void
    {
        $this->guard();
        Csrf::requireValid();
        $id = (int)($_POST['id'] ?? 0);
        FeeStructure::toggleStatus($id);
        flash_set('success', 'Fee structure status updated.');
        redirect('fee-structures');
    }
}
