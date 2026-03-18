<?php
require __DIR__ . '/ReCaptchaCreds.php';

$Input = json_decode(file_get_contents('php://input'), true);
$Token = $Input['recaptchaToken'] ?? '';

$Response = file_get_contents(
    "https://www.google.com/recaptcha/api/siteverify?" .
        "secret=$SecretKey&response=$Token");
$Result = json_decode($Response, true);

echo json_encode([
    'success' => $Result['success'] ?? false,
    'score' => $Result['score'] ?? 0,
    'error-codes' => $Result['error-codes'] ?? []
]);