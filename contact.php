<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

// Set HEADWAY_CONTACT_EMAIL in the server environment (or in the vhost config).
$recipient = trim((string) (getenv('HEADWAY_CONTACT_EMAIL') ?: ''));
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    respond(503, ['ok' => false, 'error' => 'mail_not_configured']);
}

function field(string $key, int $maxLength): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        return '';
    }

    $value = trim($value);
    $value = str_replace(["\r", "\n", '%0a', '%0d'], ' ', $value);

    return function_exists('mb_substr')
        ? mb_substr($value, 0, $maxLength, 'UTF-8')
        : substr($value, 0, $maxLength);
}

// Honeypot: accept silently so bots do not learn that they were blocked.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    respond(200, ['ok' => true]);
}

$name = field('name', 120);
$company = field('company', 160);
$email = field('email', 200);
$phone = field('phone', 40);
$message = field('message', 5000);
$consent = isset($_POST['consent']) && $_POST['consent'] !== '';

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$consent) {
    respond(422, ['ok' => false, 'error' => 'invalid_input']);
}

$host = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['SERVER_NAME'] ?? 'localhost');
$host = $host !== '' ? $host : 'localhost';

$subject = 'Website enquiry from ' . $name . ($company !== '' ? ' (' . $company . ')' : '');
$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$lines = [
    'Name: ' . $name,
    'Company: ' . ($company !== '' ? $company : '—'),
    'Email: ' . $email,
    'Phone: ' . ($phone !== '' ? $phone : '—'),
    '',
    'Message:',
    $message,
];

$headers = [
    'From: Headway website <no-reply@' . $host . '>',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: Headway website',
];

$sent = @mail($recipient, $subject, implode("\n", $lines), implode("\r\n", $headers));

respond($sent ? 200 : 500, ['ok' => $sent, 'error' => $sent ? null : 'send_failed']);
