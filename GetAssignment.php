<?php
header('Content-Type: application/json');
require_once __DIR__ . '/Credentials.php';
require_once __DIR__ . '/GetRegisterRow.php';
require_once __DIR__ . '/GetTargetUrl.php';
require_once __DIR__ . '/MakeAssignment.php';

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
   die('Failed to connect to database;');
}

// Unpack input
$Input = json_decode(file_get_contents('php://input'), true);
if (!$Input) {
    // If using MATLAB's webwrite function
	$Input = $_POST;
}
$Input = is_array($Input) ? $Input : array();
$SubjectId = isset($Input['SubjectId']) ? $Input['SubjectId'] : null;
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
if (!boolval($SubjectId)) {
	$Conn->close();
    RespondWithJsonNotice(400, 'SubjectId not set in call to GetAssignment.php;');
}

// Check whether an assignment has been made yet
$SubjectRow = GetRegisterRow($Conn, $SubjectId);
if ($SubjectRow === null) {
	$Conn->close();
	RespondWithJsonNotice(404, 'Unknown SubjectId.');
}
$State = intval($SubjectRow["State"]);
if ($State !== 3 && $State !== 5) {
	$Url = GetTargetUrl($Conn, $SubjectId);
	$Conn->close();
	echo json_encode(array('TargetUrl' => $Url));
	exit;
}

$GroupId = $SubjectRow["GroupId"];
if (boolval($SubjectRow["ImgPerm"])) {
    $ImgPerm = json_decode($SubjectRow["ImgPerm"], true);
} else {
    $ImgPerm = null;
}
$Assignment = array();
$Assignment["GroupId"] = $GroupId;
$Assignment["ImgPerm"] = $ImgPerm;

// If either the GroupId or ImgPerm are unset
$MadeAss = false;
if ((!boolval($GroupId)) || (!boolval($ImgPerm))) {
    $Assignment = MakeAssignment($SubjectId);
    $GroupId = $Assignment["GroupId"];
    $ImgPerm = $Assignment["ImgPerm"];
    $MadeAss = true;
}

// If we have just made an assignment ... 
// ... and the SubjectId is already recorded in the Register table ...
if ($MadeAss) {
    $ImgPerm = json_encode($ImgPerm);
    $Sql01 = "UPDATE Register SET 
        GroupId = '$GroupId', 
        ImgPerm = '$ImgPerm'
        WHERE SubjectId = '$SubjectId'";
    if ($Conn->query($Sql01) === false) {
        die('Sql01 failed to execute successfully!');
    }
}

// Close the database connection and return the result
$Conn->close();
echo(json_encode($Assignment));
