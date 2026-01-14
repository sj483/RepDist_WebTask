<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if($Conn->connect_error) {
   die('Failed to connect to database');
}

function MakeAssignment($SubjectId) {
	$Groups  = ['Ani','Art','Fac','Foo','Lin','Obj','Pla','Spa','Tex'];
	$RndN = rand(0, 8); 
	$GroupId = $Groups[$RndN];
    $ImgNums = range(0,5);
	$ImgIds = array();

	for ($ii=0; $ii<6; $ii++) {
		$ImgIds[$ii] = sprintf("./Imgs/%s%01d.png", $GroupId, $ImgNums[$ii]);
	}
	
    shuffle($ImgIds);
    $Assignment['tA'] = $ImgIds[ 0];
    $Assignment['tB'] = $ImgIds[ 1];
    $Assignment['tC'] = $ImgIds[ 2];
    $Assignment['tD'] = $ImgIds[ 3];
    $Assignment['tE'] = $ImgIds[ 4];
    $Assignment['tF'] = $ImgIds[ 5];
    return $Assignment;
}

// Unpack inputs:
$Input = json_decode(file_get_contents('php://input'), true);
if (!$Input) {
	$Input = $_POST; // Only used when testing via MATLAB's webwrite function.
}
$SubjectId = $Input['SubjectId'];

// Create the output array:
$Result = array();

// Get the SubjectInt
$SubjectId = strtolower($SubjectId);
$SubjectInt = intval($SubjectId,36);
$SubjectIntW = $SubjectInt % 4294967295;
			
// Set the Seed:
srand($SubjectIntW);

// Set the output:
$Assignment = MakeAssignment($SubjectId);
$Result['Assignment'] = $Assignment;

//Check if the asingment has previously been recorded, and if not, save in register
$sqlPrepedAssi = json_encode($Assignment);
$sql1 = "SELECT Assignment FROM Register WHERE SubjectId = '$SubjectId'";
$sql2 = "UPDATE Register SET Assignment = '$sqlPrepedAssi' WHERE SubjectId = '$SubjectId'";
if($result = $Conn->query($sql1)){
    if($result->num_rows > 0){
        $Row = mysqli_fetch_assoc($result);
        if($Row['Assignment'] == null){
            // Assignment does not exist, insert new record first
            if ($Conn->query($sql2) === true) {
                // Successfully inserted
                //could make result sent back to the task page only after successful insert
            } else {
                $Conn->close();
                die( 'Failed to record assignment');
            }
        }} // else assignment already exists, do nothing
} else {
    $Conn->close();
    die('Failed to check if assignment existed');
}
echo json_encode($Result);
// Echo the output:
?>