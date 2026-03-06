var EnforceUnfocus = false;

async function LogDeltaVisibility(ComingOrGoing) {
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
    var P1 = await fetch(Daddy, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Data)
    });

    // Unpack the Result
    var Result = await P1.json();
    var Bool = Result.Bool;
    var TargetUrl = Result.TargetUrl;
    var Notice = Result.Notice;
    var Reason = Result.Reason;

    // Branch depending on the Result
    if (!Bool) {
        alert(Notice);
    } else {
        TaskIO.Sent2Coventry = Reason;
        await WriteTaskIO();
        window.location.replace(TargetUrl);
    }
    return;
}

document.addEventListener("visibilitychange", (event) => {
    if (!(Boolean(SubjectId) && EnforceUnfocus)) {
        return;
    }
    if (document.visibilityState === "hidden") {
        LogDeltaVisibility('Going');
    } else {
        LogDeltaVisibility('Coming');
    }
});