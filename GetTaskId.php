<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

function RedirectToUrl($Url) {
    header('Location: '.$Url);
    exit();
}
//unpack the input
$Input = json_decode(file_get_contents('php://input'), true);
//intialise the output
$Result = array();

// Connect to the database:
$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if($Conn->connect_error) {
	die("Connection failed: " . $Conn->connect_error);
}

$SubjectId = mysqli_real_escape_string($Conn,$Input['SubjectId']);
$Sql = "SELECT `State` FROM `Register` WHERE `SubjectId`='$SubjectId'";


$Result = mysqli_query($Conn,$Sql);
if (!$Result) {
    die("Query failed: " . mysqli_error($Conn));
}

$Row = mysqli_fetch_assoc($Result);
$State = $Row['State'];
if (!($State == 2 or $State == 4)){
    RedirectToUrl('Error.html'); ///RETURN HERE TO DECIDE IF WE WANT THIS BEHAVIOUR
}

echo json_encode($State);
?>