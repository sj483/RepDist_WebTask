<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

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
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
$Href = $Input['Href'];
$Href = mysqli_real_escape_string($Conn, $Href);

// Set the current time
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

// Insert a new record into the Unfocuses table
$Sql01 =
    "INSERT INTO Unfocuses (SubjectId, Href, DateTime_Unfocus) 
     VALUES ('$SubjectId', '$Href', '$DateTime_Write')";
if (($Conn->query($Sql01)) === false) {
    $Conn->close();
    die('Query Sql01 failed to execute successfully;');
}

// Count the number of Unfocuses so far
$Sql02 = "SELECT * FROM Unfocuses WHERE SubjectId = '$SubjectId'";
$QueryRes02 = mysqli_query($Conn, $Sql02);
$Count = $QueryRes02->num_rows;

// Set $Result['Notice']
if ($Count == 1) {
    $Result['Notice'] = "Please stay focused on this tab for the duration 
        of the experiment.\nThis is your 1st warning. Repeatedly clicking 
        away will result in your participation being discontinued.";
} elseif ($Count == 2) {
    $Result['Notice'] = "Please stay focused on this tab for the duration 
        of the experiment.\nThis is your 2nd warning. Repeatedly clicking 
        away will result in your participation being discontinued.";
} elseif ($Count == 3) {
    $Result['Notice'] = "Please stay focused on this tab for the duration 
        of the experiment.\nThis is your 3rd and FINAL warning. Clicking 
        away once more will result in your participation being 
        discontinued.";
} else {
    // If Count >= 4, update the State to -1.
    $Result['Bool'] = true;
    $Result['TargetUrl'] = "./Coventry.html?SubjectId=$SubjectId&State=-1#";
    $Result['Notice'] = 'You blew it!';
    $Result['Reason'] = 'Away too often.';
    $Sql03 = "UPDATE Register SET State = -1 WHERE SubjectId ='$SubjectId'";
    if ($Conn->query($Sql03)===false) {
	    $Conn->close();
		die('Query Sql03 failed to execute successfully;');
    }
}

// Close the database connection and return the result
$Conn->close();
echo json_encode($Result);