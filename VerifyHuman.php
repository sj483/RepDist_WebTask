<?php
require __DIR__ . '/reCaptchaCredentials.php';

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['recaptchaToken'] ?? '';

$response = file_get_contents(
    "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$token"
);
$result = json_decode($response, true);

echo json_encode([
    'success' => $result['success'] ?? false,
    'score' => $result['score'] ?? 0,
    'error-codes' => $result['error-codes'] ?? []
]);
?>
