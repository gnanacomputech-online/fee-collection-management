<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $route): void
{
    header('Location: /index.php?route=' . $route);
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function flash_get(string $type): ?string
{
    if (empty($_SESSION['flash'][$type])) {
        return null;
    }
    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);
    return $message;
}

function old(array $data, string $key, string $default = ''): string
{
    return e($data[$key] ?? $default);
}

/**
 * Format a number as Indian Rupees, e.g. 1234567.5 -> "₹12,34,567.50" (Indian digit grouping).
 */
function inr(float|int|string $amount): string
{
    $amount = (float)$amount;
    $negative = $amount < 0;
    $amount = abs($amount);

    $parts = explode('.', number_format($amount, 2, '.', ''));
    $intPart = $parts[0];
    $decPart = $parts[1];

    // Indian grouping: last 3 digits, then groups of 2.
    if (strlen($intPart) > 3) {
        $lastThree = substr($intPart, -3);
        $rest = substr($intPart, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $grouped = $rest . ',' . $lastThree;
    } else {
        $grouped = $intPart;
    }

    return ($negative ? '-' : '') . '₹' . $grouped . '.' . $decPart;
}

function format_date(?string $date): string
{
    if (empty($date)) {
        return '-';
    }
    $ts = strtotime($date);
    return $ts === false ? '-' : date('d M Y', $ts);
}

function format_datetime(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }
    $ts = strtotime($datetime);
    return $ts === false ? '-' : date('d M Y, h:i A', $ts);
}

/**
 * Generates a random per-account temporary password (one upper, one lower, one digit,
 * one symbol guaranteed, rest filled from the full pool, then shuffled) instead of a
 * shared hardcoded default. Meant to be shown once to the enrolling admin/accountant
 * and handed to the account owner out of band; there is no way to recover it later.
 */
function generate_password(int $length = 10): string
{
    $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // no I/O to avoid look-alike confusion
    $lower = 'abcdefghjkmnpqrstuvwxyz';
    $digits = '23456789';
    $symbols = '!@#$%&*';
    $all = $upper . $lower . $digits . $symbols;

    $pick = static fn(string $set): string => $set[random_int(0, strlen($set) - 1)];

    $password = [$pick($upper), $pick($lower), $pick($digits), $pick($symbols)];
    for ($i = count($password); $i < $length; $i++) {
        $password[] = $pick($all);
    }

    for ($i = count($password) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$password[$i], $password[$j]] = [$password[$j], $password[$i]];
    }

    return implode('', $password);
}
