<?php
header('Content-Type: application/json');
require_once __DIR__ . '/Credentials.php';
require_once __DIR__ . '/GetRegisterRow.php';

// Preallocate the Result
$Result = array();

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Database connection failed: " . $Conn->connect_error);
}

// Get the inputs
$Input = json_decode(file_get_contents('php://input'), true);
$Input = is_array($Input) ? $Input : array();
$SubjectId = isset($Input['SubjectId']) ? $Input['SubjectId'] : null;
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// Get the State
$State = null;
$SubjectRow = GetRegisterRow($Conn, $SubjectId);
if ($SubjectRow !== null) {
    $State = intval($SubjectRow["State"]);
}

// Set the Result
if ($State !== null && $State >= 0) {
    $State = null;
}
$Result['State'] = $State;

// Close the database connection and return the Result
$Conn->close();
echo json_encode($Result);
