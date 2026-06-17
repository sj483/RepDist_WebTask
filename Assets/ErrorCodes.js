var ErrorCodes = Object.freeze({
    LandingMissingIds: "000",
    LandingSubmitPoolId: "001",
    LandingSubmitSubjectId: "002",
    LandingResumeSubjectId: "003",
    LandingResumePoolId: "004",
    VisibilityLoggingFailed: "005",
    CoventryStateLookupFailed: "006",
    RegisterMissingTargetUrl: "010",
    InstructUnknownTaskId: "020",
    InstructMissingTargetUrl: "021",
    InstructContinueFailed: "022",
    TaskCompletionWriteFailed: "030",
    CompleteMissingSubject: "040",
    CompleteIncomplete: "041"
});

function GetErrorUrl(ErrorCode, Options) {
    var SafeOptions = Options || {};
    var CurrentPoolId = typeof PoolId === 'undefined' ? null : PoolId;
    var CurrentSubjectId =
        typeof SubjectId === 'undefined' ? null : SubjectId;
    var PoolIdValue = Object.prototype.hasOwnProperty.call(
        SafeOptions,
        "PoolId"
    ) ? SafeOptions.PoolId : CurrentPoolId;
    var SubjectIdValue = Object.prototype.hasOwnProperty.call(
        SafeOptions,
        "SubjectId"
    ) ? SafeOptions.SubjectId : CurrentSubjectId;
    var Params = new URLSearchParams();

    if (PoolIdValue !== null && PoolIdValue !== undefined) {
        Params.set("PoolId", String(PoolIdValue));
    }
    if (SubjectIdValue !== null && SubjectIdValue !== undefined) {
        Params.set("SubjectId", String(SubjectIdValue));
    }
    Params.set("ErrorCode", String(ErrorCode));

    return "./Error.html?" + Params.toString() + "#";
}

function RedirectToError(ErrorCode, Options) {
    window.location.replace(GetErrorUrl(ErrorCode, Options));
}

function ShowReportableErrorAlert(ErrorCode, Message) {
    var Prefix = Message ? Message + "\n" : "";
    alert(
        Prefix +
        "Please report error code #" +
        ErrorCode +
        " to Sophie (sj483@sussex.ac.uk)."
    );
}
