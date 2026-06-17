<?php
header('Content-Type: application/json');

// Unpack the input
$Input = json_decode(file_get_contents('php://input'), true);
if (!is_array($Input) || !array_key_exists('PoolId', $Input)) {
    http_response_code(400);
    echo json_encode(array('Notice' => 'GenSubjectId called without a PoolId.'));
    exit;
}

// Check for required arguments
if (!isset($Input['PoolId'])) {
    http_response_code(400);
    echo json_encode(array('Notice' => 'GenSubjectId called without a PoolId.'));
    exit;
}

// Set the the output
$Result = array();
$PoolId = trim(strval($Input['PoolId']));
if ($PoolId === '') {
    http_response_code(400);
    echo json_encode(array('Notice' => 'GenSubjectId called with an empty PoolId.'));
    exit;
}
$Hash = md5($PoolId);
$Result['SubjectId'] = substr($Hash, -8);
echo json_encode($Result);
