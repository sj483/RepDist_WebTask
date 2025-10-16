<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Preallocate the result
$Result = array();

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

// Get the input variables
$Input = json_decode(file_get_contents('php://input'), true);
if (!$Input) {
	$Input = $_POST; // Only used when testing via MATLAB's webwrite function.
}
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);
$Href = $Input['Href'];
$Href = mysqli_real_escape_string($Conn,$Href);

// Set the current time
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Now = $Now->format('Y-m-d\TH:i:s');

// Get the reason for this exclusion
$HrefObj = parse_url($Href);
$UrlParams = array();
parse_str($HrefObj['query'],$UrlParams);
$Reason = $UrlParams['Reason'];

// Set the Result
if ($Reason == 0) {
    $Result['HTML'] = "<h1><b>Oops...</b></h1>
        <p>Sorry, it appears that you clicked away from the experiment for too long.</p>
        <p>This means that we will not be able to use your data &#128546;.</p>
        <p>Your participation has been discontinued.</p>";
} else {
    $Result['HTML'] = "<h1><b>Oops...</b></h1>
        <p>Sorry, it appears that you clicked away from the experiment too many times.</p>
        <p>This means that we will not be able to use your data &#128546;.</p>
        <p>Your participation has been discontinued.</p>";
}

// Close the database connection and return the Result
$Conn->close();
echo json_encode($Result);
?>