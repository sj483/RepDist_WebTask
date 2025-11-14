<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

// Preallocate the result:
$Result = array();

// Get the input variables
$Input = json_decode(file_get_contents('php://input'), true);
// Check and unpack inputs:
if (!isset($Input['FunctionCall']) ) {
    $Result['Error'] = 'No function name!';
}
if (!isset($Input['SubjectId'])) {
    $Result['Error'] = 'No function arguments!';
} else {
    $SubjectId = $Input['SubjectId'];
}

// Connect to the database:

$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

// Sanitize SubjectId
$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);

// Set DateTime_Write
$Now = new DateTimeImmutable("now", new DateTimeZone('Europe/London'));
$DateTime_Write = $Now->format('Y-m-d\TH:i:s');

// Get assignment
if(!isset($Result['Error'])) {
    switch($Input['FunctionCall']) {
        
        case 'GetCompletionLink':
    		$Sql1 = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
    		$QueryRes = mysqli_query($Conn, $Sql1);
    		$FoundSubject = false;
    		if($QueryRes === FALSE) {
    			// If there is an SQL error:
    			$Conn->close();
    			die("Query Sql1 failed to execute successfully");
    		} else {
    			// If the query ran successfully...
    			while($Row = mysqli_fetch_assoc($QueryRes)) {
    				$FoundSubject = true;
    				$State = $Row["State"];
					$PoolId = $Row["PoolId"];
					// Other fields can be extracted here as needed
    			}
    		}
    		$Result['FoundSubject'] = $FoundSubject;
    		$Result['Completed'] = false;
    		$Result['CompletionLink'] = '<a id="CompletionLink" href="./Error.html?SubjectId='.$SubjectId.'#" target="_blank">###</a>';
    		if ($FoundSubject) {
    		    if ($State == 6) {
					$Result['Completed'] = true;
					


					if (strlen($PoolId)>5){
    		        //RETURN HERE TO SWAP OUT PROLIFIC CODE FOR NEW ONE(FORM TO EXPECT IS E.G CCK0I5MM)
    		        $Result['CompletionLink'] = '<a id="CompletionLink" href="'.$PrlfcCmplLnk.'" target="_blank">'.$PrlfcCmplLnk.'</a>';
					} elseif(strlen($PoolId)==5 ) {
						//return double check that sona surveycodes are 5 characters long
						$Result['CompletionLink'] = '<a id="CompletionLink" href="https://sussexpsychology.sona-systems.com/webstudy_credit.aspx?".
    					"experiment_id=2001&credit_token=1aad237a918a43ee89c82d814bf28823&survey_code='.$PoolId.'" target="_blank">https://sussexpsychology.sona-systems.com/webstudy_credit.aspx?".
    					"experiment_id=2001&credit_token=1aad237a918a43ee89c82d814bf28823&survey_code='.$PoolId.'</a>';
    		    } else {
					$Result['CompletionLink'] = '<a id="CompletionLink" href="./PoolError.html?SubjectId='.$SubjectId.'#" target="_blank">./PoolError.html?SubjectId='.$SubjectId.'#</a>';
				}
    		}
    		
    		// Log that they have landed on the Complete.html page:
    		$Sql2 = "UPDATE Register SET DateTime_Complete = '$DateTime_Write' WHERE SubjectId ='$SubjectId'";
    		if ($Conn->query($Sql2)===false) {
    		    $Conn->close();
			    die('Query Sql2 failed to execute successfully;');
    		}
            break;
			}		
	default:
            $Result['Error'] = 'Bad function call!';
            break;
}
    
}

$Conn->close();
echo json_encode($Result);
?>