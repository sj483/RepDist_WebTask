<?php

// A function that returns the TargetUrl given only a SubjectId ...
// ... and an active SQL connection;
function GetTargetUrl($Dbc, $SId)
{

	// Get the State
	$Sql = "SELECT * FROM Register WHERE SubjectId = '$SId'";
	$QueryRes = mysqli_query($Dbc, $Sql);
	if ($QueryRes === false) {
		die("Sql failed to execute successfully!");
	} else {
		while ($Row = mysqli_fetch_assoc($QueryRes)) {
			$State = $Row["State"];
		}
	}

	if ($State < 0) {
		return "./Coventry.html?SubjectId=$SId#";
	}

	switch ($State) {
		case 0:
			return "./Consent.html?SubjectId=$SId#";

		case 1:
			return "./Register.html?SubjectId=$SId#";

		case 2:
			return "./Instruct.html?SubjectId=$SId&Task=TItrain#";

		case 3:
			return "./TItrain.html?SubjectId=$SId#";

		case 4:
			return "./Instruct.html?SubjectId=$SId&Task=TIprobe#";

		case 5:
			return "./TIprobe.html?SubjectId=$SId#";

		case 6:
			return "./Complete.html?SubjectId=$SId#";

		default:
			die("Invalid state: '$State'");
	}
}