<?php

require_once __DIR__ . '/../models/SchoolClass.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/RosterImporter.php';
require_once __DIR__ . '/../models/StudentDue.php';
require_once __DIR__ . '/../models/Payment.php';

class AdminController
{
    private function guard(): void
    {
        Auth::requireRole('super_admin');
    }

    public function dashboard(): void
    {
        $this->guard();
        $pageTitle = 'Dashboard';

        $pdo = Database::connect();
        $stats = [
            'classes' => (int)$pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn(),
            'students' => Student::countActive(),
            'accountants' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'accountant'")->fetchColumn(),
            'structures' => (int)$pdo->query("SELECT COUNT(*) FROM fee_structures WHERE status = 'active'")->fetchColumn(),
            'todays_collection' => Report::todaysCollection(),
            'total_outstanding' => Report::totalOutstanding(),
            'defaulters' => Report::defaultersCount(),
        ];

        $recentPayments = $pdo->query(
            "SELECT p.*, u.name AS student_name, st.admission_no
             FROM payments p
             JOIN students st ON st.id = p.student_id
             JOIN users u ON u.id = st.user_id
             ORDER BY p.paid_at DESC LIMIT 8"
        )->fetchAll();

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    // ---------------- Classes ----------------

    public function classes(): void
    {
        $this->guard();
        $pageTitle = 'Classes & Sections';
        $classes = SchoolClass::all();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/classes.php';
    }

    public function classStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $v = new Validator();
        $v->required($_POST, 'name', 'Class name')->maxLength($_POST, 'name', 50, 'Class name')
          ->required($_POST, 'section', 'Section')->maxLength($_POST, 'section', 10, 'Section')
          ->required($_POST, 'academic_year', 'Academic year')->maxLength($_POST, 'academic_year', 9, 'Academic year');

        $errors = $v->errors();
        if (empty($errors) && SchoolClass::exists(trim($_POST['name']), trim($_POST['section']), trim($_POST['academic_year']))) {
            $errors['name'] = 'This class/section/year combination already exists.';
        }

        if (!empty($errors)) {
            flash_set('error', implode(' ', $errors));
            redirect('admin/classes');
        }

        SchoolClass::create(trim($_POST['name']), trim($_POST['section']), trim($_POST['academic_year']));
        flash_set('success', 'Class created.');
        redirect('admin/classes');
    }

    public function classUpdate(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $section = trim($_POST['section'] ?? '');
        $year = trim($_POST['academic_year'] ?? '');

        if ($id <= 0 || $name === '' || $section === '' || $year === '') {
            flash_set('error', 'Invalid class update.');
            redirect('admin/classes');
        }
        if (SchoolClass::exists($name, $section, $year, $id)) {
            flash_set('error', 'Another class already uses that name/section/year combination.');
            redirect('admin/classes');
        }

        SchoolClass::update($id, $name, $section, $year);
        flash_set('success', 'Class updated.');
        redirect('admin/classes');
    }

    public function classDelete(): void
    {
        $this->guard();
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        if (!SchoolClass::delete($id)) {
            flash_set('error', 'This class has students or fee structures attached and cannot be deleted.');
        } else {
            flash_set('success', 'Class deleted.');
        }
        redirect('admin/classes');
    }

    // ---------------- Students ----------------

    public function students(): void
    {
        $this->guard();
        $pageTitle = 'Students';
        $classId = (int)($_GET['class_id'] ?? 0);
        $search = trim($_GET['q'] ?? '');
        $students = Student::all([
            'class_id' => $classId > 0 ? $classId : null,
            'search' => $search !== '' ? $search : null,
        ]);
        $classes = SchoolClass::all();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/students.php';
    }

    public function studentStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $v = new Validator();
        $v->required($_POST, 'name', 'Name')->maxLength($_POST, 'name', 100, 'Name')
          ->required($_POST, 'email', 'Email')->email($_POST, 'email', 'Email')
          ->required($_POST, 'admission_no', 'Admission number')->maxLength($_POST, 'admission_no', 30, 'Admission number')
          ->required($_POST, 'class_id', 'Class')
          ->required($_POST, 'roll_no', 'Roll number')->numeric($_POST, 'roll_no', 'Roll number');

        $errors = $v->errors();
        if (empty($errors) && Student::admissionExists(trim($_POST['admission_no']))) {
            $errors['admission_no'] = 'This admission number is already in use.';
        }
        if (empty($errors) && User::emailExists(trim($_POST['email']))) {
            $errors['email'] = 'This email is already registered.';
        }

        if (!empty($errors)) {
            flash_set('error', 'Please fix: ' . implode(' ', $errors));
            redirect('admin/students');
        }

        $password = generate_password();

        Student::create(
            trim($_POST['name']),
            trim($_POST['email']),
            $password,
            trim($_POST['admission_no']),
            (int)$_POST['class_id'],
            (int)$_POST['roll_no'],
            trim($_POST['guardian_name'] ?? '') ?: null,
            trim($_POST['guardian_phone'] ?? '') ?: null,
            trim($_POST['dob'] ?? '') ?: null,
            trim($_POST['address'] ?? '') ?: null
        );

        flash_set('success', 'Student enrolled. Temporary password (shown once, share with the student now): "' . $password . '". They can change it after logging in from "Change Password". Dues generated for their class.');
        redirect('admin/students');
    }

    public function studentImportForm(): void
    {
        $this->guard();
        $pageTitle = 'Bulk Import Students';
        require __DIR__ . '/../views/admin/students_import.php';
    }

    public function studentImport(): void
    {
        $this->guard();
        Csrf::requireValid();

        if (empty($_FILES['roster']) || $_FILES['roster']['error'] !== UPLOAD_ERR_OK) {
            flash_set('error', 'Please choose a valid .xlsx file to upload.');
            redirect('admin/students/import');
        }

        $file = $_FILES['roster'];
        $allowedExt = ['xlsx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', // some environments report xlsx as zip
            'application/octet-stream',
        ];

        if (!in_array($ext, $allowedExt, true)) {
            flash_set('error', 'Only .xlsx files are supported.');
            redirect('admin/students/import');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            flash_set('error', 'File is too large (max 5 MB).');
            redirect('admin/students/import');
        }
        $mime = mime_content_type($file['tmp_name']);
        if ($mime !== false && !in_array($mime, $allowedMimes, true)) {
            flash_set('error', 'Unrecognized file type. Please upload a valid .xlsx roster.');
            redirect('admin/students/import');
        }

        // Store under a randomized filename to avoid overwrite/path-traversal risk from the client name.
        $storedName = bin2hex(random_bytes(16)) . '.xlsx';
        $storedPath = __DIR__ . '/../../public/uploads/roster_imports/' . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
            flash_set('error', 'Could not save the uploaded file.');
            redirect('admin/students/import');
        }

        try {
            $result = RosterImporter::import($storedPath);
        } catch (Throwable $e) {
            flash_set('error', 'Import failed: could not read the spreadsheet. ' . $e->getMessage());
            @unlink($storedPath);
            redirect('admin/students/import');
        }
        @unlink($storedPath);

        $_SESSION['import_result'] = $result;
        redirect('admin/students/import/result');
    }

    public function studentImportResult(): void
    {
        $this->guard();
        $pageTitle = 'Import Result';
        $result = $_SESSION['import_result'] ?? null;
        unset($_SESSION['import_result']);
        if ($result === null) {
            redirect('admin/students/import');
        }
        require __DIR__ . '/../views/admin/students_import_result.php';
    }

    public function studentView(): void
    {
        $this->guard();
        $id = (int)($_GET['id'] ?? 0);
        $student = Student::find($id);
        if (!$student) {
            flash_set('error', 'Student not found.');
            redirect('admin/students');
        }
        $pageTitle = 'Student Profile';
        $dues = StudentDue::forStudent($id);
        $payments = Payment::forStudent($id);
        require __DIR__ . '/../views/admin/student_view.php';
    }

    public function studentStatus(): void
    {
        $this->guard();
        Csrf::requireValid();
        $id = (int)($_POST['id'] ?? 0);
        Student::toggleStatus($id);
        flash_set('success', 'Student status updated.');
        redirect('admin/students');
    }

    // ---------------- Accountants ----------------

    public function accountants(): void
    {
        $this->guard();
        $pageTitle = 'Accountant Accounts';
        $accountants = User::allByRole('accountant');
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/accountants.php';
    }

    public function accountantStore(): void
    {
        $this->guard();
        Csrf::requireValid();

        $v = new Validator();
        $v->required($_POST, 'name', 'Name')->maxLength($_POST, 'name', 100, 'Name')
          ->required($_POST, 'email', 'Email')->email($_POST, 'email', 'Email')
          ->required($_POST, 'password', 'Password')->minLength($_POST, 'password', 8, 'Password');

        $errors = $v->errors();
        if (empty($errors) && User::emailExists(trim($_POST['email']))) {
            $errors['email'] = 'This email is already registered.';
        }

        if (!empty($errors)) {
            flash_set('error', 'Please fix: ' . implode(' ', $errors));
            redirect('admin/accountants');
        }

        User::create(trim($_POST['name']), trim($_POST['email']), $_POST['password'], 'accountant');
        flash_set('success', 'Accountant account created.');
        redirect('admin/accountants');
    }

    public function accountantStatus(): void
    {
        $this->guard();
        Csrf::requireValid();
        $id = (int)($_POST['id'] ?? 0);
        User::toggleStatus($id);
        flash_set('success', 'Accountant status updated.');
        redirect('admin/accountants');
    }
}
