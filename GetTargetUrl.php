<?php

// A function that returns the TargetUrl given only a SubjectId ...
// ... and an active SQL connection;
function GetTargetUrl($Conn, $SubjectId)
{
	// Get the State
	$Sql = "SELECT * FROM Register WHERE SubjectId = '$SubjectId'";
	$QueryRes = mysqli_query($Conn, $Sql);
	if ($QueryRes === false) {
		die("Sql failed to execute successfully!");
	} else {
		while ($Row = mysqli_fetch_assoc($QueryRes)) {
			$State = $Row["State"];
		}
	}

	if ($State < 0) {
		return "./Coventry.html?SubjectId=$SubjectId#";
		// State == -1: Clicked away too many times;
		// State == -2: Clicked away for too long;
	}

	switch ($State) {
		case 0:
			return "./Consent.html?SubjectId=$SubjectId#";

		case 1:
			return "./Register.html?SubjectId=$SubjectId#";

		case 2:
			return "./Instruct.html?SubjectId=$SubjectId&Task=TItrain#";

		case 3:
			return "./TItrain.html?SubjectId=$SubjectId#";

		case 4:
			return "./Instruct.html?SubjectId=$SubjectId&Task=TIprobe#";

		case 5:
			return "./TIprobe.html?SubjectId=$SubjectId#";

		case 6:
			return "./Complete.html?SubjectId=$SubjectId#";

		default:
			die("Invalid state: '$State'");
	}
}