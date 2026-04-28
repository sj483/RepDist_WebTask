<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Connection failed: " . $Conn->connect_error);
}

$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Exclude = $Now->format('Y-m-d\TH:i:s');

// Get the input
$Input = json_decode(file_get_contents('php://input'), true);
$PoolId = $Input['PoolId'];
$SubjectId = $Input['SubjectId'];
$OS = $Input['OS'];
$Browser = $Input['Browser'];
$ScreenWidth = $Input['ScreenWidth'];
$ScreenHeight =  $Input['ScreenHeight'];
$PoolId = mysqli_real_escape_string($Conn, $PoolId);
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
$OS = mysqli_real_escape_string($Conn, $OS);
$Browser = mysqli_real_escape_string($Conn, $Browser);
$ScreenWidth = mysqli_real_escape_string($Conn, $ScreenWidth);
$ScreenHeight = mysqli_real_escape_string($Conn, $ScreenHeight);

if (!boolval($PoolId)) {
    $PoolId = 'null';
}
if (!boolval($SubjectId)) {
    $SubjectId = 'null';
}

// Write to the database
$Sql = "INSERT INTO Exclusions 
    (PoolId, SubjectId, OS, Browser, 
        ScreenWidth, ScreenHeight, DateTime_Exclude) 
    VALUES 
    ('$PoolId', '$SubjectId', '$OS', '$Browser', 
        $ScreenWidth, $ScreenHeight, '$DateTime_Exclude')";
if ($Conn->query($Sql) === true) {
    $Result['Success'] = true;
} else {
    die("Query Sql failed to execute successfully!\n" . $Conn->error);
}
$Conn->close();