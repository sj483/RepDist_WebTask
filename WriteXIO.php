<?php
header('Content-Type: application/json');
require __DIR__ . '/DotProduct.php';
require __DIR__ . '/Credentials.php';
require __DIR__ . '/GetTargetUrl.php';

// Grab the input
$Input = json_decode(file_get_contents('php://input'), true);

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
		$DataType = 'TItrainIO';
		break;
	default:
		die('WriteXIO.php invoked with bad inputs.');
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

// Set DateTime_Write
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

// Save the data
$Sql00 = "CALL Record$DataType('$SubjectId','$DateTime_Write','$ClientTimeZone','$Data')";
if (($Conn->query($Sql00)) === false) {
	$Conn->close();
	die('Query Sql00 failed to execute successfully.');
}

// Get the State
$Sql01 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
$QueryRes01 = mysqli_query($Conn, $Sql01);
if ($QueryRes01 === false) {
	$Conn->close();
	die("Sql01 failed to execute successfully!");
} else {
	while ($Row = mysqli_fetch_assoc($QueryRes01)) {
		$State = $Row["State"];
	}
}

// Increment the state if positive ...
// (i.e., as long as there has not been an exclusion)
if ($State > 0) {
	$State = $State + 1;
}
$TimeField = 'DateTime_'.substr($DataType,0,-2);
$Sql02 = "UPDATE Register 
	SET State = $State, 
	$TimeField = '$DateTime_Write' 
	WHERE SubjectId ='$SubjectId'";
if (($Conn->query($Sql02)) === false) {
	$Conn->close();
	die('Query Sql02 failed to execute successfully.');
}

// Set the response
$Result = array();
$Result['TargetUrl'] = GetTargetUrl($Conn, $SubjectId);

$Conn->close();
echo json_encode($Result);