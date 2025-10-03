<?php
header('Content-Type: application/json');
require __DIR__ . '/GetTargetUrl.php';
require __DIR__ . '/Credentials.php';
///RETURN TO THIS - THIS IS THE LOGIC OG FROM LANDING ACTIONS.PHP WHICH 
//NEEDS TO BE ADAPTED TO JUST HAVE BEEN CALLED FROM THE INSTRUCT PAGE 

//YOU ALSO NEED TO ADD THE LOGIC WHICH CHECKES WHETHER THE STATE IS 2 OR 4
//AND RETURNS EITHER THE TI TRAIN OR TI PROBE TEXT ACCORDINGLY


// A function that formats datetime strings for SQL insertion
function FormatDateTimeStr($Str){
	$OutStr = substr($Str,0,4)
		.'-'.substr($Str,4,2)
		.'-'.substr($Str,6,2)
		.'T'.substr($Str,9,2)
		.':'.substr($Str,11,2)
		.':'.substr($Str,13,2);
	return $OutStr;
}

//MAYBE THIS IS USED FOR COVENTRY STUFF - IGNORE FOR NOW!

// A function that computes the time interval in seconds between PHP DateTime objects
// ... this function returns the signed difference in seconds between inputs (A and B).
// ... The result is greater than zero when B > A.
function GetTimeInterval($A, $B) {
    $Yr = date_diff($A, $B)->y;
    $Mo = date_diff($A, $B)->m;
	$Dy = date_diff($A, $B)->d;
    $Hr = date_diff($A, $B)->h;
    $Mi = date_diff($A, $B)->i;
    $Sc = date_diff($A, $B)->s;
    $Interval = ($Yr*365.25*24*60*60) + ($Mo*30.4375*24*60*60) + ($Dy*24*60*60) + ($Hr*60*60) + ($Mi*60) + $Sc;
    if ($A > $B) {
        $Interval = -1 * $Interval;
    } else {
        $Interval =  1 * $Interval ;
    }
    return $Interval;
}


// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}


            // Set SubjectId, and Now:
            $SubjectId = $Inputs['SubjectId'];
            $SubjectId = mysqli_real_escape_string($Conn,$SubjectId);
            $Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
            $DateTime_Instruct = $Now->format('Y-m-d\TH:i:s');
            
            // DateTime_Start
            $DateTime_Start = FormatDateTimeStr($Inputs['DateTime_Start']);
            $Start = new DateTimeImmutable($DateTime_Start, new DateTimeZone('Europe/London'));
            
            // Interval between Now and Start
            $Interval = GetTimeInterval($Start,$Now);
            
            // ClientTimeZone
            $ClientTimeZone = $Inputs['ClientTimeZone'];
            $ClientTimeZone = mysqli_real_escape_string($Conn,$ClientTimeZone);
            
            // TaskId
            $TaskId = $Inputs['TaskId'];
            $TaskId = mysqli_real_escape_string($Conn,$TaskId);
            
            // Test to see if enough time has passed (depending on the TaskId)
            $TestBool = false;
            switch ($TaskId) {
                case 'TItrain':
                    if ($Interval > 86) {
                        $TestBool = true;
                    }
                    break;
                case 'TIprobe':
                    if ($Interval > 45) {
                        $TestBool = true;
                    }
                    break;
                default:
                    die('Bad TaskId!');
                    break;
            }
            
            if ($TestBool) {
                // They are good to continue...
                
                // Increment State and send them on there way
                $Sql00 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
	            $QueryRes00 = mysqli_query($Conn, $Sql00);
	            if($QueryRes00 === false) {
                    $Conn->close();
                    die("Query Sql00 failed to execute successfully!");
                } else {
                    while($Row = mysqli_fetch_assoc($QueryRes00)) {
                        $State = $Row["State"];
                    }
                }
                /// NOT %100 ON THIS LOGIC COME BACK TO THIS
                if ($State == 2){
                    $State = 3;
                }else if ($State == 4){
                    $State = 5;
                }else {
                    die('Bad State!');
                }
                // SQL to update the Register table
                if ($TaskId=='TItrain') {
                    $Sql01 = "UPDATE Register SET State = $State, DateTime_TIinstr = '$DateTime_Instruct' WHERE SubjectId ='$SubjectId'";
                    
                } else {
                    $Sql01 = "UPDATE Register SET State = $State WHERE SubjectId ='$SubjectId'";
                    
                }
		    
			    // Run and set the result:
			    if ($Conn->query($Sql01)===true) {
			        $Url = GetTargetUrl($Conn, $SubjectId);
                    $Result['TargetUrl'] = $Url;
			    } else {
			        $Conn->close();
			        die('Query Sql01 failed to execute successfully;');
			    }
            } else {
                // If they are not good to continue (i.e., they jumpped the gun)...
                
                // Get their State...
                $Sql02 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
	            $QueryRes02 = mysqli_query($Conn, $Sql02);
	            if($QueryRes02 === false) {
                    $Conn->close();
                    die("Query Sql02 failed to execute successfully!");
                } else {
                    while($Row = mysqli_fetch_assoc($QueryRes02)) {
                        $State = $Row["State"];
                    }
                }
                
                // Record this naughtiness
                $Sql03 = "INSERT INTO InstructNaughtiness (SubjectId, State, TaskId, DateTime_Naughty) VALUES ('$SubjectId', $State, '$TaskId', '$DateTime_Instruct')";
                if ($Conn->query($Sql03)===true) {
			        $Result['TargetUrl'] = "https://c01.learningandinference.org/Instruct.html?SubjectId=$SubjectId&TaskId=$TaskId&Warn=true#";
			    } else {
			        $Conn->close();
			        die('Query Sql03 failed to execute successfully;');
			    }
                
            }
            $Conn->close();

            ?>