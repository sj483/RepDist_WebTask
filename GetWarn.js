function GetWarn() {
    const url = new URL(window.location.href);
	var warn = false;
    const warnVal = url.searchParams.get("Warn");
	if (warnVal == "true")
		warn = true;
	
    // Return true if Warn=true (case-insensitive)
    return warn;
};
var Warn = GetWarn();
