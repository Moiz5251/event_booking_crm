<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

function clean_input(string $key): string
{
    $value = $_POST[$key] ?? '';
    $value = is_string($value) ? $value : '';
    return trim(strip_tags($value));
}

$name = clean_input('name');
$email = clean_input('email');
$whatsapp = clean_input('whatsapp');
$source = clean_input('source');
$offer = clean_input('offer');

if ($name === '' || $email === '' || $whatsapp === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /?lead=invalid#top');
    exit;
}

$submittedAt = gmdate('Y-m-d H:i:s') . ' UTC';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

$lead = [
    $submittedAt,
    $name,
    $email,
    $whatsapp,
    $source,
    $offer,
    $ipAddress,
    $userAgent,
];

$csvPath = __DIR__ . '/leads.csv';
$isNewFile = !file_exists($csvPath);
$csv = fopen($csvPath, 'ab');

if ($csv !== false) {
    if ($isNewFile) {
        fputcsv($csv, ['Submitted At', 'Name', 'Email', 'WhatsApp', 'Source', 'Offer', 'IP Address', 'User Agent']);
    }

    fputcsv($csv, $lead);
    fclose($csv);
}

$to = 'moizhussain.mh53@gmail.com';
$subject = 'New Velvet CRM $300 Offer Lead';
$message = implode("\n", [
    'New Velvet CRM lead',
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    'WhatsApp: ' . $whatsapp,
    'Source: ' . $source,
    'Offer: ' . $offer,
    'Submitted At: ' . $submittedAt,
    'IP Address: ' . $ipAddress,
]);

$host = $_SERVER['HTTP_HOST'] ?? 'velvet.moizweb.dev';
$from = 'no-reply@' . preg_replace('/[^A-Za-z0-9.-]/', '', $host);
$headers = [
    'From: Velvet CRM <' . $from . '>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'Content-Type: text/plain; charset=UTF-8',
];

@mail($to, $subject, $message, implode("\r\n", $headers));

header('Location: /?lead=success#top');
exit;
