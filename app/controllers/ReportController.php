<?php

require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/StudentDue.php';
require_once __DIR__ . '/../models/SchoolClass.php';

class ReportController
{
    public function index(): void
    {
        Auth::requireRole('super_admin', 'accountant');

        $type = $_GET['type'] ?? 'collection';
        $from = trim($_GET['from'] ?? '') ?: null;
        $to = trim($_GET['to'] ?? '') ?: null;
        $classId = (int)($_GET['class_id'] ?? 0);

        $pageTitle = 'Reports';
        $classes = SchoolClass::all();

        $byDay = [];
        $byClass = [];
        $byFeeHead = [];
        $defaulters = [];

        if ($type === 'defaulters') {
            $defaulters = StudentDue::defaulters(['class_id' => $classId > 0 ? $classId : null]);
        } else {
            $byDay = Report::collectionByDay($from, $to);
            $byClass = Report::collectionByClass($from, $to);
            $byFeeHead = Report::collectionByFeeHead($from, $to);
        }

        require __DIR__ . '/../views/fees/reports.php';
    }
}
