<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/DashboardShared.php';

DashboardEnsureSessionStarted();

if (!DashboardIsAuthConfigured()) {
    http_response_code(503);
    echo json_encode(array(
        'Notice' => 'Dashboard authentication is not configured in Credentials.php.'
    ));
    exit;
}

DashboardRequireAuthJson();

$Conn = null;

try {
    $Conn = DashboardOpenConnection();
    $Payload = DashboardBuildPayload($Conn);
    $Conn->close();
    echo json_encode($Payload);
} catch (Throwable $Throwable) {
    if ($Conn instanceof mysqli) {
        $Conn->close();
    }

    http_response_code(500);
    echo json_encode(array(
        'Notice' => 'Failed to load dashboard data.',
        'Detail' => $Throwable->getMessage()
    ));
}
