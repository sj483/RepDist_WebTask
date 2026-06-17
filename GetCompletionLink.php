<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';
require __DIR__ . '/CompletionLinks.php';

// Connect to the database
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Database connection failed: " . $Conn->connect_error);
}

// Get the inputs
$Input = json_decode(file_get_contents('php://input'), true);
$SubjectId = $Input['SubjectId'];
$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);

// Query the database
$Sql = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
$QueryRes = mysqli_query($Conn, $Sql);
if ($QueryRes === false) {
	$Conn->close();
	die("Query Sql failed to execute successfully");
}

// Set FoundSubject, State, PoolId and IsWorthy
$FoundSubject = false;
while ($Row = mysqli_fetch_assoc($QueryRes)) {
	$FoundSubject = true;
	$PoolId = $Row["PoolId"];
	$State = $Row["State"];
}
$IsWorthy = $FoundSubject && ($State == 6);

// Set the Link
if ($IsWorthy) {
	if (strlen($PoolId) == 5) {
		$SonaCompletionLink = $SonaCompletionLink . $PoolId;
		$Link = '<a id="CompletionLink" href="' . $SonaCompletionLink .
			'" target="_blank">' . $SonaCompletionLink . '</a>';
	} else if (strlen($PoolId) > 8) {
		$Link = '<a id="CompletionLink" href="' . $ProlificCompletionLink .
			'" target="_blank">' . $ProlificCompletionLink . '</a>';
	} else {
		$Link = '<a id="CompletionLink" href="https://www.sussex.ac.uk/"' .
			' target="_blank">Continue...</a>';
	}
} else {
	$ErrorCode = $FoundSubject ? '041' : '040';
	$Link = '<a id="CompletionLink" href="./Error.html?SubjectId=' .
		$SubjectId . '&ErrorCode=' . $ErrorCode .
		'#" target="_blank">ERROR</a>';
}

// Return the result
$Result = array();
$Result['FoundSubject'] = $FoundSubject;
$Result['Completed'] = $IsWorthy;
$Result['CompletionLink'] = $Link;

$Conn->close();
echo json_encode($Result);
