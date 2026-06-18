<?php
header('Content-Type: application/json');
require_once __DIR__ . '/Credentials.php';

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Database connection failed: " . $Conn->connect_error);
}

// Get the inputs
$Input = json_decode(file_get_contents('php://input'), true);
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
$Feedback = $Input['Feedback'];
$Feedback = mysqli_real_escape_string($Conn, $Feedback);

// Set DateTime_Write
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

// Query the database
$Sql = "CALL RecordFeedback('$SubjectId','$Feedback','$DateTime_Write')";
$QueryRes = mysqli_query($Conn, $Sql);
if ($QueryRes === false) {
	$Conn->close();
	die("Query Sql failed to execute successfully");
}

// Return the result
$Result = "Done";

$Conn->close();
echo json_encode($Result);
