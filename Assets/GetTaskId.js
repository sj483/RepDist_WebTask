var TaskId = null;
function GetTaskId() {
    const Location = new URL(window.location.href);
    TaskId = Location.searchParams.get("TaskId");
}
GetTaskId();
