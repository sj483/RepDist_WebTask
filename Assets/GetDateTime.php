<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$Result = array();
if( !isset($input['FunctionCall']) ) { $Result['Error'] = 'No function name!'; }
if( !isset($input['Args']) ) { $Result['Error'] = 'No function arguments!'; }

if( !isset($Result['Error']) ) {
    switch($input['FunctionCall']) {
        case 'GetDateTime':
            $Now = new DateTime('now', new DateTimeZone('Europe/London'));
            $Result['DateTime'] = $Now -> format("Ymd_His");
           break;
		   
        default:
           $Result['Error'] = 'Not found function '.$input['FunctionCall'].'!';
           break;
    }
}
echo json_encode($Result);
?>