function WarningNeeded() {
    const URL = new URL(window.location.href);
	var ToWarn = false;
    const WarnVal = URL.searchParams.get("Warn");
	if (WarnVal == "true") {
		ToWarn = true;
    }
    return ToWarn;
};
var ShowWarning = WarningNeeded();

if (ShowWarning) {
    var Warning = document.createElement('div');
    Warning.id = 'Warning';
    Warning.className = 'alert';
    Warning.innerHTML = 
        "<span class=\"closebtn\" onclick=\"CloseWarning();\">" + 
        "&times;</span>" + 
        "<strong>Please listen to all of the audio instructions!</strong>";
    document.body.prepend(Warning);
}

function CloseWarning() {
    document.body.removeChild(document.getElementById('Warning'));
}