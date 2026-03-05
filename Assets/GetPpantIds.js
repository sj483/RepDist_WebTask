var PoolId = null;
var SubjectId = null;

function getParamOrNull(params, key) {
    return params.has(key) ? params.get(key) : null;
}

function GetPpantIds() {
    if (window.location.search === "") {
        return;
    }

    var params = new URLSearchParams(window.location.search);

    var fromPoolId = getParamOrNull(params, "PoolId");
    var fromSona = getParamOrNull(params, "SONA_PID");
    var fromProlific = getParamOrNull(params, "PROLIFIC_PID");

    // Keep the old precedence: PoolId > SONA_PID > PROLIFIC_PID
    PoolId = fromPoolId;
    if (PoolId === null) {
        PoolId = fromSona;
    }
    if (PoolId === null) {
        PoolId = fromProlific;
    }

    SubjectId = getParamOrNull(params, "SubjectId");

    if (typeof TaskIO === "object" && TaskIO !== null) {
        TaskIO.SubjectId = SubjectId;
    }
}

GetPpantIds();