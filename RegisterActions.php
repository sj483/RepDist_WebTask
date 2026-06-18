<?php
header('Content-Type: application/json');
require_once __DIR__ . '/Credentials.php';
require_once __DIR__ . '/GetRegisterRow.php';
require_once __DIR__ . '/GetTargetUrl.php';

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Database connection failed: " . $Conn->connect_error);
}

// Unpack the inputs...
$Input = json_decode(file_get_contents('php://input'), true);
if (!is_array($Input) || !isset($Input['SubjectId'])) {
	$Conn->close();
	RespondWithJsonNotice(400, 'RegisterActions.php called with bad inputs.');
}

// SubjectId
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
$SubjectRow = GetRegisterRow($Conn, $SubjectId);
if ($SubjectRow === null) {
	$Conn->close();
	RespondWithJsonNotice(404, 'Unknown SubjectId.');
}
$State = intval($SubjectRow['State']);
if ($State !== 1) {
	$Url = GetTargetUrl($Conn, $SubjectId);
	$Conn->close();
	echo json_encode(array('TargetUrl' => $Url));
	exit;
}

// Gender
$Gender = $Input["Gender"];
$Gender = mysqli_real_escape_string($Conn, $Gender);

// First language
$L1 = $Input["L1"];
$L1 = mysqli_real_escape_string($Conn, $L1);

// Handedness
$Handedness = $Input["Handedness"];
$Handedness = mysqli_real_escape_string($Conn, $Handedness);

// Birth Month Year
$BMY = $Input["BMY"];
$BMY = mysqli_real_escape_string($Conn, string: $BMY);

// DateTime_Register
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Register = $Now->format('Y-m-d\TH:i:s');

// Write to the Register table
$Sql00 = "UPDATE Register SET 
	State = 2,
	Gender = '$Gender', 
	L1 = '$L1',
	Handedness = '$Handedness', 
	BMY = '$BMY', 		
	DateTime_Register = '$DateTime_Register'
	WHERE SubjectId = '$SubjectId' AND State = 1";
if ($Conn->query($Sql00) === false) {
	$Conn->close();
	die('Query $Sql00 failed to execute successfully;');
}

// Get the TargetUrl
$Url = GetTargetUrl($Conn, $SubjectId);
if ($Url === null) {
	$Conn->close();
	RespondWithJsonNotice(500, 'Failed to determine the next page.');
}
$Result = array();
$Result['TargetUrl'] = $Url;

// Close the database connection and return the result
$Conn->close();
echo json_encode($Result);
