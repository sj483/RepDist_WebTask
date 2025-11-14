<?php
header('Content-Type: application/json');
require __DIR__ . '/GetTargetUrl.php';
require __DIR__ . '/Credentials.php';

// Decode JSON input (fetch was used to call this script, not like the consent form)
$Input = json_decode(file_get_contents('php://input'), true);

$Result = array();

if (!isset($Input['FunctionCall'])) {
	die("No function name!");
}
if (!isset($Input['Args'])) {
	die("No function arguments!"); 
}
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

// Set Now
$Now = new DateTimeImmutable("now", new DateTimeZone(timezone: 'Europe/London'));

switch($Input['FunctionCall']) {
	case 'Register':
		
		//unpack the arguments
		$Input = $Input['Args'];
		// Define SubjectId
		$SubjectId = $Input["SubjectId"];
		$SubjectId = mysqli_real_escape_string($Conn,$SubjectId);
		
		// Define Gender
		$Gender = $Input["Gender"];
		$Gender = mysqli_real_escape_string($Conn,$Gender);
		
		// Define L1
		$L1 = $Input["L1"];
		$L1 = mysqli_real_escape_string($Conn,$L1);
		
		// Define Handedness:
		$Handedness = $Input["Handedness"];
		$Handedness = mysqli_real_escape_string($Conn,$Handedness);

		$BMY = $Input["BMY"];
		$BMY = mysqli_real_escape_string($Conn,string: $BMY);
		
		// Define DateTime_Register
		$DateTime_Register = $Now->format('Y-m-d\TH:i:s');
		
		// Set Sql to enter the data into Register
		$Sql = "UPDATE Register SET 
		    State = 2,
	    	Gender = '$Gender', 
	    	L1 = '$L1',
	    	Handedness = '$Handedness', 
			BMY = '$BMY', 		
	    	DateTime_Register = '$DateTime_Register'
			WHERE SubjectId = '$SubjectId'";
		
		// Run Sql:
		if ($Conn->query($Sql) === true) {
		    $Url = GetTargetUrl($Conn, $SubjectId);
			$Result['TargetUrl'] = $Url;
		} else {
			$Conn->close();
			die('Query $Sql failed to execute successfully;');
		}
		break;
		
	default:
		$Conn->close();
		die('Bad function call.');
		break;
}
$Conn->close();
echo json_encode($Result);
?>