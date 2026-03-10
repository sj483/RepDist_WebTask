<?php
header('Content-Type: application/json');

// Unpack the input
$Input = json_decode(file_get_contents('php://input'), true);

// Check for required arguments
if (!isset($Input['PoolId'])) {
    die('GenSubjectId called without a PoolId.');
}

// Set the the output
$Result = array();
$PoolId = $Input['PoolId'];
$Hash = md5($PoolId);
$Result['SubjectId'] = substr($Hash, -8);
echo json_encode($Result);