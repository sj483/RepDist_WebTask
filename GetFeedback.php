<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Database connection failed: " . $Conn->connect_error);
}

// Get the inputs
$Input = json_decode(file_get_contents('php://input'), true);
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// Query the database
$Sql = "SELECT * FROM Feedback WHERE SubjectId = '$SubjectId'";
$QueryRes = mysqli_query($Conn, $Sql);
if ($QueryRes === false) {
	$Conn->close();
	die("Query Sql failed to execute successfully");
}

// Set FeedbackFound
$FeedbackFound = false;
$Feedback = null;
while ($Row = mysqli_fetch_assoc($QueryRes)) {
	$FeedbackFound = true;
    $Feedback = $Row["Feedback"];
}

// Return the result
$Result = array();
$Result['FeedbackFound'] = $FeedbackFound;
$Result['Feedback'] = $Feedback;

$Conn->close();
echo json_encode($Result);