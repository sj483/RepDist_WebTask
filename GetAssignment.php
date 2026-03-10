<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';
require __DIR__ . '/MakeAssignment.php';

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
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
if (!boolval($SubjectId)) {
    die('SubjectId not set in call to GetAssignment.php;');
}

// Check whether an assignment has been made yet
$Sql00 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
$QueryRes00 = mysqli_query($Conn, $Sql00);
$SubjectFound = false;
$GroupId = null;
$ImgPerm = null;
if ($QueryRes00 === false) {
    die("Sql00 failed to execute successfully!");
} else {
    while ($Row = mysqli_fetch_assoc($QueryRes00)) {
        $SubjectFound = true;
        $GroupId = $Row["GroupId"];
        $ImgPerm = $Row["ImgPerm"];
    }
}

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
if ($SubjectFound && $MadeAss) {
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