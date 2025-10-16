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
		// this should be called in RegActions/ WriteTaskIO
		//state 2 means they have registered but not done instructs yet
		//state 4 means they have done TItrain and not done instructs for probe yet
		return "./Instruct.html?SubjectId=$SId#";
		//Once instructions duration has passed we update state with instruct actions to 3...
		//initially sends to TI train
		//second time people lands, instruct ations updates state to 5 and sends them to TIprobe	
	
	} else if ($State == 3) {
		//means they have finished listening to instructs
		//and are ready to start task
		//(called in instruct actions)
		//they will stay in state 3 until they finish TItrain
		return "./TItrain.html?SubjectId=$SId#";	
		//After TItrain, WriteXIO.php is called to update state to 4 which sends them back 
		//to Instruct page once again 

	} else if ($State == 5) {
		return "./TIprobe.html?SubjectId=$SId#";
		//means that they have finished listening to the probe instructions and are 
		//ready to do the task	
		
	} else if ($State == 6) {
		//this means they have finished TIprobe and are ready to go to the end
		//and sent back to SONA for credit/ prolific
		return "./End.html?SubjectId=$SId#"	;
		
	} else if ($State < 0) {
		// Kicked off
		return "./Coventry.html?SubjectId=$SId#";
		
		
	} else {
		//debugging
		die("'$State'");
		// DECIDE THIS !
		// Finished all tasks (State=5) * add a call to landingactions.php in TIProbe 
		//return "./Complete.html?SubjectId=$SId#";
	}
}

?>