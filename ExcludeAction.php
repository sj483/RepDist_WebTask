<?php
header('Content-Type: application/json');
require __DIR__ . '/GetTargetUrl.php';
require __DIR__ . '/Credentials.php';


// Preallocate the result (which will contain the direction):
$Result = array();


//unpack the input
$Input = json_decode(file_get_contents('php://input'), true);
// Check inputs
if (!isset($Input['FunctionCall']) ) {
    $Result['Error'] = 'No function name!';
}
if (!isset($Input['Args'])) {
    $Result['Error'] = 'No function arguments!';
}

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

// Test virginity, log via SQL, and set the result:
if (!isset($Result['Error'])) {
    switch ($Input['FunctionCall']) {            
        case 'LogExclusion':
            
            // Set PoolId, SubjectId, and Now:
            $PoolId = $Inputs['PoolId'];
            $SubjectId = $Inputs['SubjectId'];
            $OS = $Inputs['OS'];
            $Browser = $Inputs['Browser'];
            
            $PoolId = mysqli_real_escape_string($Conn,$PoolId);
            $SubjectId = mysqli_real_escape_string($Conn,$SubjectId);
            $OS = mysqli_real_escape_string($Conn,$OS);
            $Browser = mysqli_real_escape_string($Conn,$Browser);
            
            if (!boolval($PoolId)) {
                $PoolId = 'null';
            }
            if (!boolval($SubjectId)) {
                $SubjectId = 'null';
            }
            
            $Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
            $DateTime_Exclude = $Now->format('Y-m-d\TH:i:s');
            
            $Sql = "INSERT INTO Exclusions (PoolId, SubjectId, OS, Browser, DateTime_Exclude) VALUES ('$PoolId', '$SubjectId', '$OS', '$Browser', '$DateTime_Exclude')";
			if ($Conn->query($Sql) === true) {
			    $Result['Success'] = true;
			} else {
			    die("Query Sql failed to execute successfully!\n" . $Conn -> error);
			}
            break;

    }
}
$Conn->close();
echo json_encode($Result);
?>