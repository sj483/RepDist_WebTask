<?php
header('Content-Type: application/json');

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

// Echo the output:
echo json_encode($Result);
?>