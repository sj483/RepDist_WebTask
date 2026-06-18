<?php
require_once __DIR__ . '/GetTargetUrl.php';
require_once __DIR__ . '/GetRegisterRow.php';
require_once __DIR__ . '/Credentials.php';

// Check that the Checkbox has been checked and return if not
if (!isset($_POST['Checkbox']) || ($_POST['Checkbox'] != 'Check')) {
    die('Consent not granted.');
}

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Database connection failed: " . $Conn->connect_error);
}

// Unpack the inputs
if (!isset($_POST['SubjectId']) || !isset($_POST['Initials'])) {
    $Conn->close();
    header('Location: ./Error.html#');
    exit();
}
$SubjectId = $_POST['SubjectId'];
$Initials = $_POST['Initials'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
$Initials = mysqli_real_escape_string($Conn, $Initials);
$SubjectRow = GetRegisterRow($Conn, $SubjectId);
if ($SubjectRow === null) {
    $Conn->close();
    header('Location: ./Error.html?SubjectId=' . rawurlencode($SubjectId) . '#');
    exit();
}
$State = intval($SubjectRow['State']);
if ($State !== 0) {
    $Url = GetTargetUrl($Conn, $SubjectId);
    $Conn->close();
    if ($Url === null) {
        header('Location: ./Error.html?SubjectId=' .
            rawurlencode($SubjectId) . '#');
    } else {
        header('Location: ' . $Url);
    }
    exit();
}
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Consent = $Now->format('Y-m-d\TH:i:s');

// Update the Register table
$Sql00 = "UPDATE Register SET 
    State = 1, DateTime_Consent = '$DateTime_Consent' 
    WHERE SubjectId = '$SubjectId' AND State = 0";
if ($Conn->query($Sql00) == false) {
    die("Query Sql0 failed to execute successfully!");
}

// Add to the ConsentLog table
$Sql01 = "CALL RecordConsentLog(
    '$SubjectId', '$Initials', '$DateTime_Consent')";
if ($Conn->query($Sql01) == false) {
    $Conn->close();
    die("Query Sql1 failed to execute successfully!");
}

// Close the database connection and redirect
$Url = GetTargetUrl($Conn, $SubjectId);
$Conn->close();
if ($Url === null) {
    header('Location: ./Error.html?SubjectId=' . rawurlencode($SubjectId) .
        '#');
    exit();
}
header('Location: ' . $Url);
exit();
