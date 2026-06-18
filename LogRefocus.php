<?php
header('Content-Type: application/json');
require_once __DIR__ . '/Credentials.php';
require_once __DIR__ . '/GetRegisterRow.php';
require_once __DIR__ . '/GetTimeInterval.php';

// Preallocate the result
$Result = array();
$Result['Bool'] = false; // Set to true on exclusion
$Result['TargetUrl'] = '';
$Result['Notice'] = '';
$Result['Reason'] = '';

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Database connection failed: " . $Conn->connect_error);
}

// Get the input variables
$Input = json_decode(file_get_contents('php://input'), true);
$Input = is_array($Input) ? $Input : array();
$SubjectId = isset($Input['SubjectId']) ? $Input['SubjectId'] : null;
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
$SubjectRow = GetRegisterRow($Conn, $SubjectId);
if ($SubjectRow === null) {
    $Conn->close();
    echo json_encode($Result);
    exit;
}

// Set the current time
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));

// Query the Unfocuses table to get the latest time they unfocused
$Sql00 = "SELECT DateTime_Unfocus FROM Unfocuses
    WHERE SubjectId = '$SubjectId'
    ORDER BY DateTime_Unfocus DESC
    LIMIT 1";
$QueryRes00 = mysqli_query($Conn, $Sql00);
if ($QueryRes00 === false) {
    $Conn->close();
    die("Query Sql00 failed to execute successfully!");
}
$Row = mysqli_fetch_assoc($QueryRes00);
if ($Row === null) {
    $Conn->close();
    echo json_encode($Result);
    exit;
}

$DateTime_Unfocus = new DateTimeImmutable(
    str_replace(' ', 'T', $Row["DateTime_Unfocus"]),
    new DateTimeZone('Europe/London')
);

// Now we have the latest DateTime_Unfocus set, ...
// ... compute whether the participant has been off away for too long
$AwayTooLong = abs(GetTimeInterval($DateTime_Unfocus, $Now)) > (7 * 60);

// If AwayTooLong ...
if ($AwayTooLong) {
    // Set State = -2
    $Sql01 = "UPDATE Register SET State = -2 WHERE SubjectId ='$SubjectId'";
    if ($Conn->query($Sql01) === false) {
        $Conn->close();
        die('Query Sql01 failed to execute successfully;');
    }

    // Set the Response
    $Result['Bool'] = true;
    $Result['TargetUrl'] = "./Coventry.html?SubjectId=$SubjectId&State=-2#";
    $Result['Notice'] = 'You blew it!';
    $Result['Reason'] = 'Away too long.';
}

// Close the database connection and return the result
$Conn->close();
echo json_encode($Result);
