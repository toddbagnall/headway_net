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

// Default recipient for the live site. Override with HEADWAY_CONTACT_EMAIL
// in the PHP-FPM pool or the Apache vhost if the inbox changes.
$recipient = trim((string) (getenv('HEADWAY_CONTACT_EMAIL') ?: 'headway@headwaynet.com'));
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

$lang = (string) ($_POST['lang'] ?? 'es') === 'en' ? 'en' : 'es';
$name = field('name', 120);
$company = field('company', 160);
$email = field('email', 200);
$phone = field('phone', 40);
$interest = field('interest', 80);
$message = field('message', 5000);
$consent = isset($_POST['consent']) && $_POST['consent'] !== '';

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$consent) {
    respond(422, ['ok' => false, 'error' => 'invalid_input']);
}

$labels = $lang === 'en'
    ? [
        'subject' => 'Website enquiry',
        'name' => 'Name',
        'company' => 'Company or school',
        'email' => 'Email',
        'phone' => 'Phone',
        'interest' => 'Interested in',
        'message' => 'Message',
        'none' => '—',
    ]
    : [
        'subject' => 'Consulta web',
        'name' => 'Nombre',
        'company' => 'Empresa o centro',
        'email' => 'Email',
        'phone' => 'Teléfono',
        'interest' => 'Me interesa',
        'message' => 'Mensaje',
        'none' => '—',
    ];

$host = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['SERVER_NAME'] ?? 'localhost');
$host = $host !== '' ? $host : 'localhost';

$subject = $labels['subject'] . ' — ' . $name . ($company !== '' ? ' (' . $company . ')' : '');
$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$lines = [
    $labels['name'] . ': ' . $name,
    $labels['company'] . ': ' . ($company !== '' ? $company : $labels['none']),
    $labels['email'] . ': ' . $email,
    $labels['phone'] . ': ' . ($phone !== '' ? $phone : $labels['none']),
    $labels['interest'] . ': ' . ($interest !== '' ? $interest : $labels['none']),
    '',
    $labels['message'] . ':',
    $message,
];

$headers = [
    'From: Headway website <no-reply@' . $host . '>',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: Headway website',
];

$sent = @mail($recipient, $subject, implode("\n", $lines), implode("\r\n", $headers));

respond($sent ? 200 : 500, ['ok' => $sent, 'error' => $sent ? null : 'send_failed']);
