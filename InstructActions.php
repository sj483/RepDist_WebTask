<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';
require __DIR__ . '/FormatDateTimeStr.php';
require __DIR__ . '/GetRegister.php';
require __DIR__ . '/GetTimeInterval.php';
require __DIR__ . '/GetTargetUrl.php';

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
    die("Database connection failed: " . $Conn->connect_error);
}

// Unpack the inputs ...
$Input = json_decode(file_get_contents('php://input'), true);
if (
    !is_array($Input) ||
    !isset($Input['SubjectId']) ||
    !isset($Input['DateTime_Start']) ||
    !isset($Input['ClientTimeZone']) ||
    !isset($Input['TaskId'])
) {
    $Conn->close();
    RespondWithJsonNotice(400, 'InstructActions.php called with bad inputs.');
}

// SubjectId
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// DateTime_Start
$DateTime_Start = FormatDateTimeStr($Input['DateTime_Start']);
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
$ClientTimeZone = $Input['ClientTimeZone'];
$ClientTimeZone = mysqli_real_escape_string($Conn, $ClientTimeZone);

// TaskId
$TaskId = $Input['TaskId'];
$TaskId = mysqli_real_escape_string($Conn, $TaskId);

// Test to see if enough time has passed
$EnoughTime = false;
$ExpectedState = null;
$NextState = null;
$WriteInstructionTime = false;
switch ($TaskId) {
    case 'TItrain':
        $ExpectedState = 2;
        $NextState = 3;
        $WriteInstructionTime = true;
        if ($Interval > 81) {
            $EnoughTime = true;
        }
        break;
    case 'TIprobe':
        $ExpectedState = 4;
        $NextState = 5;
        if ($Interval > 44) {
            $EnoughTime = true;
        }
        break;
    default:
        $Conn->close();
        die('Bad TaskId!');
        break;
}

// Get the State
$SubjectRow = GetRegisterRow($Conn, $SubjectId);
if ($SubjectRow === null) {
    $Conn->close();
    RespondWithJsonNotice(404, 'Unknown SubjectId.');
}
$State = intval($SubjectRow["State"]);
if ($State !== $ExpectedState) {
    $Url = GetTargetUrl($Conn, $SubjectId);
    $Conn->close();
    if ($Url === null) {
        RespondWithJsonNotice(
            409,
            'Subject is not ready for this instruction page.'
        );
    }
    echo json_encode(array('TargetUrl' => $Url));
    exit;
}

$Result = array();
if ($EnoughTime) {
    // They are good to continue...
    if ($WriteInstructionTime) {
        $Sql01 = "UPDATE Register SET 
            State = $NextState, 
            DateTime_TIinstr = '$DateTime_Instruct' 
            WHERE SubjectId ='$SubjectId' AND State = $ExpectedState";
    } else {
        $Sql01 = "UPDATE Register SET 
            State = $NextState 
            WHERE SubjectId ='$SubjectId' AND State = $ExpectedState";
    }
    if ($Conn->query($Sql01) === true) {
        $Url = GetTargetUrl($Conn, $SubjectId);
        if ($Url === null) {
            $Conn->close();
            RespondWithJsonNotice(500, 'Failed to determine the next page.');
        }
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
