var TaskId = null;
function GetTaskId() {
    var query = window.location.search.slice(1);
    if (!query) {
        return;
    }

    var match = query.match(/(?:^|&)TaskId(?:=([^&]*))?(?:&|$)/);
    if (!match) {
        return;
    }

    TaskId = match[1] === undefined
        ? null
        : decodeURIComponent(match[1].replace(/\+/g, " "));
}
GetTaskId();
