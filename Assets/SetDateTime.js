// --- Set and update DateTime_Start and ClientTimeZone ---
var DateTime_Start = null;
var ClientTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
async function UpdateDateTime() {
    var P1 = await fetch('./Assets/GetDateTime.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ FunctionCall: 'GetDateTime', Args: { DoYouFeelMe: true } })
    })
    var data = await P1.json();

    if (data.DateTime) {
        return data;
    } else {
        throw new Error('DateTime field missing from API response');
    }
}
UpdateDateTime().then(P2 => {
    DateTime_Start = P2.DateTime;

    // If TaskIO has been set above, add vars in here!
    if (typeof (TaskIO) == "object") {
        TaskIO.DateTime_Start = DateTime_Start;
        TaskIO.ClientTimeZone = ClientTimeZone;
    }
}).catch(error => {
    console.error('Fetch failed:', error);
})