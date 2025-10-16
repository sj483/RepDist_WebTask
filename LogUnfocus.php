<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';
require __DIR__ . '/GetTimeInterval.php';

// Preallocate the result
$Result = array();
// If FunctionCall='LogUnfocus', Result will have two properties:
// - Count
// - Notice
// If FunctionCall='LogUnfocus', Result will have one property:
// - Bool

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

// Get the input variables
$Input = json_decode(file_get_contents('php://input'), true);
if (!$Input) {
	$Input = $_POST; // Only used when testing via MATLAB's webwrite function.
}
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);
$FunctionCall = $Input['FunctionCall'];
$FunctionCall = mysqli_real_escape_string($Conn,$FunctionCall);

// Set the current time
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

switch($FunctionCall) {
	case 'LogUnfocus':
        // Set the location input
        $Location = $Input['Location'];
        $Location = mysqli_real_escape_string($Conn,$Location);
        
        // Insert a new record into the Unfocuses table
        $Sql01 = "INSERT INTO Unfocuses (SubjectId, Location, DateTime_Unfocus) VALUES ('$SubjectId', '$Location', '$DateTime_Write')";
	    if (($Conn->query($Sql01))===false) {
	        $Conn->close();
	        die('Query Sql01 failed to execute successfully;');
	    }
	    
        // Count the number of Unfocuses so far
	    $Sql02 = "SELECT * FROM Unfocuses WHERE SubjectId = '$SubjectId'";
	    $QueryRes02 = mysqli_query($Conn, $Sql02);
        $Result['Count'] = $QueryRes02->num_rows;
        $Result['Notice'] = "###";
        if ($Result['Count'] == 1) {
            $Result['Notice'] = "Please stay focused on this tab for the duration of the experiment.\nThis is your 1st warning. Repeatedly clicking away will result in your participation being discontinued.";
        } elseif ($Result['Count'] == 2) {
            $Result['Notice'] = "Please stay focused on this tab for the duration of the experiment.\nThis is your 2nd warning. Repeatedly clicking away will result in your participation being discontinued.";
        } elseif ($Result['Count'] == 3) {
            $Result['Notice'] = "Please stay focused on this tab for the duration of the experiment.\nThis is your 3rd and FINAL warning. Clicking away once more will result in your participation being discontinued.";
        } else {
            $Result['Notice'] = 'You blew it!';
        }

        // Break
		break;
		
	case 'LogRefocus':
        // Get the DateTime of the last Unfocus
	    $Sql03 = "SELECT * FROM Unfocuses WHERE SubjectId = '$SubjectId'";
	    $QueryRes03 = mysqli_query($Conn, $Sql03);
	    if($QueryRes03 === false) {
            $Conn->close();
            die("Query Sql04 failed to execute successfully!");
        } else {
            $ii = 0;
            while($Row = mysqli_fetch_assoc($QueryRes03)) {
                $ii = $ii + 1;
                $DTU = new DateTimeImmutable(str_replace(' ','T',$Row["DateTime_Unfocus"]), new DateTimeZone('Europe/London'));
                if ($ii==1) {
                    $DateTime_Unfocus = $DTU;
                } else {
                    if ($DTU > $DateTime_Unfocus) {
                        $DateTime_Unfocus = $DTU;
                    }
                }
            }
        }

        // Now we have the latest DateTime_Unfocus set, ...
        // ... compute whether the participant has been off away for too long
        $Bool = abs(GetTimeInterval($DateTime_Unfocus,$Now)) > (7*60);
        $Result['Bool'] = $Bool;
		break;

    case 'SetFinalState':
        $State = -1; //After writing the TaskIO the state will be 4, but we can't leave it like this
        //otherwise if they typed in the TItrain url again and that would take them to the probe instructions
        $Sql04 = "UPDATE Register SET State = $State WHERE SubjectId = '$SubjectId'";
        if(($Conn->query($Sql04)) === false) {
            $Conn->close();
            die("Query Sql04 failed to execute successfully!");
        }
        $Result['StateSet'] = true;
        break;      
		
	default:
		// Kill it if the function call is bad
		$Conn->close();
		die('Bad function call.');
		break;
}

// Close the database connection and return the result
$Conn->close();
echo json_encode($Result);
?>