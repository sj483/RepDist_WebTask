<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';
require __DIR__ . '/GetTargetUrl.php';

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Connection failed: " . $Conn->connect_error);
}

// Set $DateTime_Landing
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Landing = $Now->format('Y-m-d\TH:i:s');

// Preallocate the result
$Result = array();

// Unpack the inputs
$Input = json_decode(file_get_contents('php://input'), true);
$PoolId = $Input['PoolId'];
$SubjectId = $Input['SubjectId'];

// Sanitize the inputs
$PoolId = mysqli_real_escape_string($Conn, $PoolId);
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// Query the Register to see if there is a match
// Set $Virgin and $State
// Reset $PoolId if available
$Sql00 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
$QueryRes00 = mysqli_query($Conn, $Sql00);
if ($QueryRes00 === false) {
    $Conn->close();
    die("Query Sql00 failed to execute successfully!");
} else {
    $Virgin = true;
    while ($Row = mysqli_fetch_assoc($QueryRes00)) {
        $Virgin = false;
        $PoolId = $Row["PoolId"]; // Redefine as it may be null;
        $State = $Row["State"];
    }
}

// Branch dependent on whether they have been here before...
if ($Virgin) {
    // They HAVE been here before
    $Sql01 = "INSERT INTO Register 
        (PoolId, SubjectId, State, DateTime_Landing) 
        VALUES ($PoolId, '$SubjectId', 0,'$DateTime_Landing')";
    if ($Conn->query($Sql01) === false) {
        $Conn->close();
        die('Query Sql01 failed to execute successfully!');
    }
} else {
    // They have NOT been here before
    $Sql02 = "INSERT INTO Relandings 
        (PoolId, SubjectId, State, DateTime_Reland)
        VALUES ('$PoolId', '$SubjectId', $State, '$DateTime_Landing')";
    if ($Conn->query($Sql02) === false) {
        $Conn->close();
        die('Query Sql02 failed to execute successfully!');
    }
}

// Get TargetUrl and return
$Url = GetTargetUrl($Conn, $SubjectId);
$Result['TargetUrl'] = $Url;
$Conn->close();
echo json_encode($Result);