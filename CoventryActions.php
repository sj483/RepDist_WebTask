<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Preallocate the Result
$Result = array();

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Connection failed: " . $Conn->connect_error);
}

// Get the inputs
$Input = json_decode(file_get_contents('php://input'), true);
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// Get the State
$Sql = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
$QueryRes = mysqli_query($Conn, $Sql);
if ($QueryRes === false) {
    die("Sql failed to execute successfully!");
} else {
    while ($Row = mysqli_fetch_assoc($QueryRes)) {
        $State = $Row["State"];
    }
}

// Set the Result
if ($State >= 0) {
    $State = null;
}
$Result['State'] = $State;

// Close the database connection and return the Result
$Conn->close();
echo json_encode($Result);