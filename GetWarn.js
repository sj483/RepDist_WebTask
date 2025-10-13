function GetWarn() {
	var CurrentUrl = window.location.href;
	var QueryStart = CurrentUrl.indexOf("?") + 1;
	var QueryEnd = CurrentUrl.indexOf("#") + 1 || CurrentUrl.length + 1;
	var Query = CurrentUrl.slice(QueryStart, QueryEnd - 1);
	var Pairs = Query.replace(/\+/g, " ").split("&");
	var UrlParams = {};
	var i, n, v, nv;
	if (!(Query === CurrentUrl || Query === "")) {
	    for (i = 0; i < Pairs.length; i++) {
		    nv = Pairs[i].split("=", 2);
		    n = decodeURIComponent(nv[0]);
		    v = decodeURIComponent(nv[1]);
		    if (!UrlParams.hasOwnProperty(n)) {
		        UrlParams[n] = [];
		    }
		    UrlParams[n].push(nv.length === 2 ? v : null);
	    }
	}
	var IsEmpty = true;
	for (var Key in UrlParams) {
	    if (UrlParams.hasOwnProperty(Key)) { //COME BACK TO THIS WHAT DOES THIS BIT DO
	        IsEmpty = false;
	    }
	}
	if (!IsEmpty) {
	    if (UrlParams.hasOwnProperty('Warn')) {
	        Warn = UrlParams.Warn[0];
	    }
	}
}

var Warn = false;
GetWarn();