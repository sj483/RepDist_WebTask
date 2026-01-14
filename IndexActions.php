<?php
error_log("IndexActions.php called");
error_log("Raw input: " . file_get_contents('php://input'));
header('Content-Type: application/json');
require __DIR__ . '/GetTargetUrl.php';
require __DIR__ . '/Credentials.php';


// Preallocate the result (which will contain the direction):
$Result = array();


//unpack the input
$Input = json_decode(file_get_contents('php://input'), true);
// Check input
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
        case 'LogLanding':
            
            // Set PoolId, SubjectId, and Now:
            $Input = $Input['Args'];
            $PoolId = $Input['PoolId'];
            $SubjectId = $Input['SubjectId'];
            //special handling of null in case of irl subjects 
            $PoolId = $PoolId === null ? "NULL": "'" . mysqli_real_escape_string($Conn, $PoolId) . "'";
            $SubjectId = mysqli_real_escape_string($Conn,$SubjectId);
            $Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
            $DateTime_Landing = $Now->format('Y-m-d\TH:i:s');
            
            // Query the Register to see if there is a match:
            $Sql00 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
            $QueryRes00 = mysqli_query($Conn, $Sql00);
            if ($QueryRes00 === false) {
                $Conn->close();
                die("Query Sql00 failed to execute successfully!");
            } else {
                $Virgin = true;
                while($Row = mysqli_fetch_assoc($QueryRes00)) {
                    $Virgin = false;
                    $PoolId = $Row["PoolId"]; // Redefine as it may be null;
                    $State = $Row["State"];
                    $TaskPerm = $Row["TaskPerm"];
                }
            }
            
            // Log and direct depedning on current state:
            if ($Virgin) {
                  
                // Cons
                
                $Sql01 = "INSERT INTO Register (PoolId, SubjectId, State, DateTime_Landing) 
				    VALUES ($PoolId, '$SubjectId', 0,'$DateTime_Landing')";
				if($Conn->query($Sql01) === true) {
                    // Get the TargetUrl and return it (will take them to consent page) 
                    $Url= GetTargetUrl($Conn, $SubjectId);
                    $Result['TargetUrl'] = $Url;
                        }
				else {
					$Conn->close();
					die('Query Sql01 failed to execute successfully!');
				    }

            } else {
                // They have been here before!
                // Log the Relanding
                $Sql02 = "INSERT INTO Relandings (PoolId, SubjectId, State, DateTime_Reland) VALUES ('$PoolId', '$SubjectId', $State, '$DateTime_Landing')";
                if($Conn->query($Sql02) === false) {
                    $Conn->close();
					die('Query Sql02 failed to execute successfully!');
                }
                
                // Get the TargetUrl and return it
                $Url = GetTargetUrl($Conn, $SubjectId);
                $Result['TargetUrl'] = $Url;
            }
            break;
            
}}
$Conn->close();
echo json_encode($Result);
?>