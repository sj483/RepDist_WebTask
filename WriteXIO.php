<?php
header('Content-Type: application/json');
require __DIR__ . '/DotProduct.php';
require __DIR__ . '/Credentials.php';
require __DIR__ . '/GetRegisterRow.php';
require __DIR__ . '/GetTargetUrl.php';

// Grab the input
$Input = json_decode(file_get_contents('php://input'), true);
if (
	!is_array($Input) ||
	!isset($Input['SubjectId']) ||
	!isset($Input['ClientTimeZone'])
) {
	RespondWithJsonNotice(400, 'WriteXIO.php invoked with bad inputs.');
}

// Determine what kind of data we are processing
$HasTItrainIO = array_key_exists('TItrainIO', $Input);
$HasTIprobeIO = array_key_exists('TIprobeIO', $Input);
$Makeup = array((int)$HasTItrainIO, (int)$HasTIprobeIO);
$PowersOf2 = array(1, 2);
$Signature = DotProduct($Makeup, $PowersOf2);
switch ($Signature) {
	case 1:
		$DataType = 'TItrainIO';
		break;
	case 2:
		$DataType = 'TIprobeIO';
		break;
	default:
		RespondWithJsonNotice(400, 'WriteXIO.php invoked with bad inputs.');
	}

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Database connection failed: " . $Conn->connect_error);
}

// Extract the SubjectId and the ClientTimeZone
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
$ClientTimeZone = $Input['ClientTimeZone'];
$ClientTimeZone = mysqli_real_escape_string($Conn, $ClientTimeZone);
$Data = $Input[$DataType];
$Data = mysqli_real_escape_string($Conn, $Data);

// Check that the participant exists and is on the right task page
$SubjectRow = GetRegisterRow($Conn, $SubjectId);
if ($SubjectRow === null) {
	$Conn->close();
	RespondWithJsonNotice(404, 'Unknown SubjectId.');
}

$ExpectedState = $DataType === 'TItrainIO' ? 3 : 5;
$NextState = $ExpectedState + 1;
$State = intval($SubjectRow["State"]);
$AllowWriteDuringExclusion = ($State === -1 || $State === -2);
if ($State !== $ExpectedState && !$AllowWriteDuringExclusion) {
	$Url = GetTargetUrl($Conn, $SubjectId);
	$Conn->close();
	if ($Url === null) {
		RespondWithJsonNotice(
			409,
			'Subject is not ready to submit this task.'
		);
	}
	echo json_encode(array('TargetUrl' => $Url));
	exit;
}

// Set DateTime_Write
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

// Save the data
$Sql00 = "CALL Record$DataType(
	'$SubjectId','$DateTime_Write','$ClientTimeZone','$Data')";
if (($Conn->query($Sql00)) === false) {
	$Conn->close();
	die('Query Sql00 failed to execute successfully.');
}

$StateToWrite = $AllowWriteDuringExclusion ? $State : $NextState;
$TimeField = 'DateTime_'.substr($DataType,0,-2);
$Sql02 = "UPDATE Register 
	SET State = $StateToWrite, 
	$TimeField = '$DateTime_Write' 
	WHERE SubjectId ='$SubjectId' AND State = $State";
if (($Conn->query($Sql02)) === false) {
	$Conn->close();
	die('Query Sql02 failed to execute successfully.');
}

// Set the response
$Result = array();
$Url = GetTargetUrl($Conn, $SubjectId);
if ($Url === null) {
	$Conn->close();
	RespondWithJsonNotice(500, 'Failed to determine the next page.');
}
$Result['TargetUrl'] = $Url;

$Conn->close();
echo json_encode($Result);
