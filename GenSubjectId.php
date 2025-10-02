<?php
header('Content-Type: application/json');

//unpack the input
$Input = json_decode(file_get_contents('php://input'), true);
//intialise the output
$Result = array();
//check for required arguments
if (!isset($Input['PID'])) {
    $Result['Error'] = 'No function arguments!';
}
if(!isset($Result['Error'])) {
    // Decode raw JSON from request body
    $PID = $Input['PID'];
    $RawHash = md5($PID);
    $Result['SubjectId'] = substr($RawHash, -8);
    }
echo json_encode($Result);
?>