// --- Set and update DateTime_Start and ClientTimeZone ---
var DateTime_Start = null;
var ClientTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
async function SetDateTime() {
    var P1 = await fetch('./Assets/GetDateTime.php');
    DateTime_Start = await P1.json();
    DateTime_Start = DateTime_Start.DateTime;

    // If TaskIO has been set above, add vars in here!
	if (typeof(TaskIO)=="object") {
	    TaskIO.DateTime_Start = DateTime_Start;
	    TaskIO.ClientTimeZone = ClientTimeZone;
	}
}
SetDateTime();