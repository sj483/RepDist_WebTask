<?php

// A function that returns the TargetUrl given only a SubjectId ...
// ... and an active SQL connection;
function GetTargetUrl($Dbc, $SId) {

    // Get the State and the TaskPerm
    $Sql = "SELECT * FROM Register WHERE SubjectId = '$SId'";
    $QueryRes = mysqli_query($Dbc, $Sql);
    if($QueryRes === false) {
        die("Sql failed to execute successfully!");
    } else {
        while($Row = mysqli_fetch_assoc($QueryRes)) {
            $State = $Row["State"];
        }
    }

    // Main if statement...
    if ($State == 0) {
		///this should be called in landing actions 
		return "./Consent.html?SubjectId=$SId#";
		
	} else if ($State == 1) {
		// this should be called in consent actions 
		return "./Register.html?SubjectId=$SId#";
	
	} else if ($State == 2 or $State == 4) {
		// this should be called in RegActions
		return "./Instruct.html?SubjectId=$SId#";
		//Once instructions duration has passed we update state with instruct actions to 3...
		//initially sends to TI train
		//second time people lands, instruct ations updates state to 5 and sends them to TIprobe	
	
	} else if ($State == 3) {
		// this should be called in instruct actions
		return "./TItrain.html?SubjectId=$SId#";	
		//After TItrain, WriteXIO.php is called to update state to 4 which sends them back 
		//to Instruct page once again 

	} else if ($State == 5) {
		// Begin probe phase! * add a call to landingactions.php in TIPtrain
		//return "./TIProbe.html?SubjectId=$SId#";		
		
	} else if ($State < 0) {
		// Kicked off
		return "./Coventry.html?SubjectId=$SId#";
		
		
	} else {
		// DECIDE THIS !
		// Finished all tasks (State=5) * add a call to landingactions.php in TIProbe 
		//return "./Complete.html?SubjectId=$SId#";
	}
}

?>