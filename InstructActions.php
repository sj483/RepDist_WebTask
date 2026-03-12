<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';
require __DIR__ . '/FormatDateTimeStr.php';
require __DIR__ . '/GetTimeInterval.php';
require __DIR__ . '/GetTargetUrl.php';

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Database connection failed: " . $Conn->connect_error);
}

// Unpack the inputs ...
$Input = json_decode(file_get_contents('php://input'), true);

// SubjectId
$SubjectId = $Inputs['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// DateTime_Start
$DateTime_Start = FormatDateTimeStr($Inputs['DateTime_Start']);
$StartTime = new DateTimeImmutable(
    $DateTime_Start,
    new DateTimeZone('Europe/London')
);

// DateTime_Instruct (Now)
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Instruct = $Now->format('Y-m-d\TH:i:s');

// Interval between Now and Start
$Interval = GetTimeInterval($StartTime, $Now);

// ClientTimeZone
$ClientTimeZone = $Inputs['ClientTimeZone'];
$ClientTimeZone = mysqli_real_escape_string($Conn, $ClientTimeZone);

// TaskId
$TaskId = $Inputs['TaskId'];
$TaskId = mysqli_real_escape_string($Conn, $TaskId);

// Test to see if enough time has passed
$EnoughTime = false;
switch ($TaskId) {
    case 'TItrain':
        if ($Interval > 86) {
            $EnoughTime = true;
        }
        break;
    case 'TIprobe':
        if ($Interval > 45) {
            $EnoughTime = true;
        }
        break;
    default:
        $Conn->close();
        die('Bad TaskId!');
        break;
}

// Get the State
$Sql00 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
$QueryRes00 = mysqli_query($Conn, $Sql00);
if ($QueryRes00 === false) {
    $Conn->close();
    die("Query Sql00 failed to execute successfully!");
} else {
    while ($Row = mysqli_fetch_assoc($QueryRes00)) {
        $State = $Row["State"];
    }
}

$Result = array();
if ($EnoughTime) {
    // They are good to continue...
    $State++;
    if ($TaskId == 'TItrain') {
        $Sql01 = "UPDATE Register SET 
            State = $State, 
            DateTime_TIinstr = '$DateTime_Instruct' 
            WHERE SubjectId ='$SubjectId'";
    } else {
        $Sql01 = "UPDATE Register SET 
            State = $State 
            WHERE SubjectId ='$SubjectId'";
    }
    if ($Conn->query($Sql01) === true) {
        $Url = GetTargetUrl($Conn, $SubjectId);
        $Result['TargetUrl'] = $Url;
    } else {
        $Conn->close();
        die('Query Sql01 failed to execute successfully;');
    }
} else {
    // If they jumped the gun...
    $Sql02 = "INSERT INTO InstructNaughtiness 
        (SubjectId, State, TaskId, DateTime_Naughty) 
        VALUES ('$SubjectId', $State, '$TaskId', '$DateTime_Instruct')";
    if ($Conn->query($Sql02) === true) {
        $Result['TargetUrl'] = "./Instruct.html?" .
            "SubjectId=$SubjectId&TaskId=$TaskId&Warn=true#";
    } else {
        $Conn->close();
        die('Query Sql02 failed to execute successfully;');
    }
}

$Conn->close();
echo json_encode($Result);