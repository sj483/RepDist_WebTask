<?php
header('Content-Type: application/json');
require __DIR__ . '/Credentials.php';

$Conn = new mysqli($Servername, $Username, $Password, $Dbname);
if ($Conn->connect_error) {
	die("Database connection failed: " . $Conn->connect_error);
}

function StringOrEmpty($Value)
{
	if ($Value === null) {
		return '';
	}

	return strval($Value);
}

function GetSubjectData($SubjectId, $Table)
{
	global $Conn;

	$AllowedTables = array('TItrainIO', 'TIprobeIO');
	if (!in_array($Table, $AllowedTables, true)) {
		return array(
			'DateTime_Write' => '',
			'ClientTimeZone' => '',
			'Json' => ''
		);
	}

	$SubjectId = mysqli_real_escape_string($Conn, $SubjectId);
	$Sql = "SELECT DateTime_Write, ClientTimeZone, $Table FROM $Table WHERE SubjectId = '$SubjectId';";
	$QueryRes = mysqli_query($Conn, $Sql);
	if (!$QueryRes) {
		$Conn->close();
		die("Query Sql failed to execute successfully");
	}

	$Data = array(
		'DateTime_Write' => '',
		'ClientTimeZone' => '',
		'Json' => ''
	);

	if ($Row = mysqli_fetch_assoc($QueryRes)) {
		$Data['DateTime_Write'] = StringOrEmpty($Row['DateTime_Write']);
		$Data['ClientTimeZone'] = StringOrEmpty($Row['ClientTimeZone']);
		$Data['Json'] = StringOrEmpty($Row[$Table]);
	}

	return $Data;
}

$Sql = "SELECT * FROM Register;";
$QueryRes = mysqli_query($Conn, $Sql);
$Data = array(
	'SubjectId' => array(),
	'BMY' => array(),
	'Gender' => array(),
	'Handedness' => array(),
	'L1' => array(),
	'State' => array(),
	'GroupId' => array(),
	'ImgPerm' => array(),
	'DateTime_Landing' => array(),
	'DateTime_Consent' => array(),
	'DateTime_Register' => array(),
	'DateTime_TIinstr' => array(),
	'DateTime_TItrain' => array(),
	'DateTime_TIprobe' => array(),
	'ClientTimeZone' => array(),
	'TItrainIO' => array(),
	'TIprobeIO' => array()
);
if (!$QueryRes) {
	$Conn->close();
	die("Query Sql failed to execute successfully");
} else {
	while ($Row = mysqli_fetch_assoc($QueryRes)) {
		$SubjectId = StringOrEmpty($Row["SubjectId"]);
		$BMY = StringOrEmpty($Row["BMY"]);
		$Gender = StringOrEmpty($Row["Gender"]);
		$Handedness = StringOrEmpty($Row["Handedness"]);
		$L1 = StringOrEmpty($Row["L1"]);
		$State = StringOrEmpty($Row["State"]);
		$GroupId = StringOrEmpty($Row["GroupId"]);
		$ImgPerm = StringOrEmpty($Row["ImgPerm"]);
		$DateTime_Landing = StringOrEmpty($Row["DateTime_Landing"]);
		$DateTime_Consent = StringOrEmpty($Row["DateTime_Consent"]);
		$DateTime_Register = StringOrEmpty($Row["DateTime_Register"]);
		$DateTime_TIinstr = StringOrEmpty($Row["DateTime_TIinstr"]);
		$DateTime_TItrain = StringOrEmpty($Row["DateTime_TItrain"]);
		$DateTime_TIprobe = StringOrEmpty($Row["DateTime_TIprobe"]);

		$TrainData = GetSubjectData($SubjectId, 'TItrainIO');
		$ClientTimeZone = $TrainData["ClientTimeZone"];
		if ($TrainData["DateTime_Write"] !== '') {
			$DateTime_TItrain = $TrainData["DateTime_Write"];
		}
		$TItrainIO = $TrainData["Json"];

		$ProbeData = GetSubjectData($SubjectId, 'TIprobeIO');
		if ($ClientTimeZone === '') {
			$ClientTimeZone = $ProbeData["ClientTimeZone"];
		}
		if ($ProbeData["DateTime_Write"] !== '') {
			$DateTime_TIprobe = $ProbeData["DateTime_Write"];
		}
		$TIprobeIO = $ProbeData["Json"];

		$Data['SubjectId'][] = $SubjectId;
		$Data['BMY'][] = $BMY;
		$Data['Gender'][] = $Gender;
		$Data['Handedness'][] = $Handedness;
		$Data['L1'][] = $L1;
		$Data['State'][] = $State;
		$Data['GroupId'][] = $GroupId;
		$Data['ImgPerm'][] = $ImgPerm;
		$Data['DateTime_Landing'][] = $DateTime_Landing;
		$Data['DateTime_Consent'][] = $DateTime_Consent;
		$Data['DateTime_Register'][] = $DateTime_Register;
		$Data['DateTime_TIinstr'][] = $DateTime_TIinstr;
		$Data['ClientTimeZone'][] = $ClientTimeZone;
		$Data['DateTime_TItrain'][] = $DateTime_TItrain;
		$Data['DateTime_TIprobe'][] = $DateTime_TIprobe;
		$Data['TItrainIO'][] = $TItrainIO;
		$Data['TIprobeIO'][] = $TIprobeIO;
	}
}

$Conn->close();
echo (json_encode($Data));
