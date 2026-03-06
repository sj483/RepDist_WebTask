var EnforceUnfocus = false;

async function LogDeltaVisibility(ComingOrGoing) {
    try {
        // Set the data to send
        var Data = {
            SubjectId: SubjectId,
            Href: window.location.href
        };

        // Send the data
        var Daddy;
        if (ComingOrGoing === 'Going') {
            Daddy = './LogUnfocus.php';
        } else {
            Daddy = './LogRefocus.php';
        }

        // Unpack the Result
        var Result = await PostJson(Daddy, Data);
        var {
            Bool: Bool = false,
            TargetUrl: TargetUrl = '',
            Notice: Notice = '',
            Reason: Reason = ''
        } = Result || {};

        // Branch depending on the Result
        if (!Bool) {
            if (Notice) {
                alert(Notice);
            }
        } else {
            TaskIO.Sent2Coventry = Reason;
            await WriteTaskIO();
            if (TargetUrl) {
                window.location.replace(TargetUrl);
            }
        }
    } catch (Err) {
        console.error('LogDeltaVisibility failed:', Err);
        alert(
                "An error has occurred.\n" +
                "Please report error code #005 " +
                "to Sophie (sj483@sussex.ac.uk)."
            );
    }
    return;
}

document.addEventListener("visibilitychange", () => {
    if (!(Boolean(SubjectId) && EnforceUnfocus)) {
        return;
    }
    if (document.visibilityState === "hidden") {
        void LogDeltaVisibility('Going');
    } else {
        void LogDeltaVisibility('Coming');
    }
    return;
});