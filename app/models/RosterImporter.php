<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Bulk student roster import from an .xlsx upload (PhpSpreadsheet).
 * Expected header row (case-insensitive, any column order):
 *   admission_no | name | email | class_name | section | academic_year | roll_no | guardian_name | guardian_phone | dob | address
 * class_name/section/academic_year must match an existing class exactly -- classes are
 * managed separately by the Super Admin, the importer does not create them on the fly.
 */
class RosterImporter
{
    private const REQUIRED_COLUMNS = ['admission_no', 'name', 'email', 'class_name', 'section', 'academic_year', 'roll_no'];

    /**
     * @return array{imported:int, skipped:int, errors:array<int,string>, credentials:array<int,array{name:string,email:string,admission_no:string,password:string}>}
     */
    public static function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['File has no data rows.']];
        }

        $header = array_map(static fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $colIndex = array_flip($header);

        foreach (self::REQUIRED_COLUMNS as $col) {
            if (!isset($colIndex[$col])) {
                return ['imported' => 0, 'skipped' => 0, 'errors' => ["Missing required column: $col"]];
            }
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $credentials = [];

        // Cache class lookups (name|section|academic_year -> id) to avoid a query per row.
        $classCache = [];
        foreach (SchoolClass::all() as $c) {
            $key = strtolower($c['name']) . '|' . strtolower($c['section']) . '|' . $c['academic_year'];
            $classCache[$key] = (int)$c['id'];
        }

        for ($r = 1; $r < count($rows); $r++) {
            $rowNum = $r + 1; // 1-based, matching what a spreadsheet user sees
            $row = $rows[$r];
            $get = static fn(string $col) => isset($colIndex[$col], $row[$colIndex[$col]])
                ? trim((string)$row[$colIndex[$col]])
                : '';

            $admissionNo = $get('admission_no');
            $name = $get('name');
            $email = $get('email');
            $className = $get('class_name');
            $section = $get('section');
            $academicYear = $get('academic_year');
            $rollNo = $get('roll_no');
            $guardianName = $get('guardian_name') ?: null;
            $guardianPhone = $get('guardian_phone') ?: null;
            $dob = $get('dob') ?: null;
            $address = $get('address') ?: null;

            // Skip fully blank rows silently (common at the end of a sheet).
            if ($admissionNo === '' && $name === '' && $email === '') {
                continue;
            }

            if ($admissionNo === '' || $name === '' || $email === '' || $className === '' || $section === '' || $academicYear === '' || $rollNo === '') {
                $errors[] = "Row $rowNum: missing a required field.";
                $skipped++;
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row $rowNum: '$email' is not a valid email address.";
                $skipped++;
                continue;
            }

            if (!is_numeric($rollNo)) {
                $errors[] = "Row $rowNum: roll_no '$rollNo' must be numeric.";
                $skipped++;
                continue;
            }

            $classKey = strtolower($className) . '|' . strtolower($section) . '|' . $academicYear;
            if (!isset($classCache[$classKey])) {
                $errors[] = "Row $rowNum: no class '$className $section ($academicYear)' found -- create it first.";
                $skipped++;
                continue;
            }

            if (Student::admissionExists($admissionNo)) {
                $errors[] = "Row $rowNum: admission number '$admissionNo' already exists.";
                $skipped++;
                continue;
            }

            if (User::emailExists($email)) {
                $errors[] = "Row $rowNum: email '$email' already exists.";
                $skipped++;
                continue;
            }

            $password = generate_password();

            try {
                Student::create(
                    $name,
                    $email,
                    $password,
                    $admissionNo,
                    $classCache[$classKey],
                    (int)$rollNo,
                    $guardianName,
                    $guardianPhone,
                    $dob,
                    $address
                );
                $imported++;
                $credentials[] = [
                    'name' => $name,
                    'email' => $email,
                    'admission_no' => $admissionNo,
                    'password' => $password,
                ];
            } catch (Throwable $e) {
                $errors[] = "Row $rowNum: could not import -- " . $e->getMessage();
                $skipped++;
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors, 'credentials' => $credentials];
    }
}
