<?php
require_once __DIR__ . '/GetRegister.php';

// A function that returns the TargetUrl given only a SubjectId ...
// ... and an active SQL connection;
function GetTargetUrl($Conn, $SubjectId)
{
	$Row = GetRegisterRow($Conn, $SubjectId);
	if ($Row === null || !isset($Row["State"])) {
		return null;
	}

	$State = $Row["State"];
	if ($State === null || $State === '') {
		return null;
	}
	$State = intval($State);

	switch ($State) {
		case -2:
			// Clicked away for too long
			return "./Coventry.html?SubjectId=$SubjectId&State=-2#";

		case -1:
			// Clicked away for too often
			return "./Coventry.html?SubjectId=$SubjectId&State=-1#";
			
		case 0:
			return "./Consent.html?SubjectId=$SubjectId#";

		case 1:
			return "./Register.html?SubjectId=$SubjectId#";

		case 2:
			return "./Instruct.html?SubjectId=$SubjectId&TaskId=TItrain#";

		case 3:
			return "./TItrain.html?SubjectId=$SubjectId#";

		case 4:
			return "./Instruct.html?SubjectId=$SubjectId&TaskId=TIprobe#";

		case 5:
			return "./TIprobe.html?SubjectId=$SubjectId#";

		case 6:
			return "./Complete.html?SubjectId=$SubjectId#";

		default:
			return null;
	}
}
