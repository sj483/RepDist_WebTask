async function PostJson(Url, Data) {
    var Response = await fetch(Url, {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Data),
        keepalive: true
    });

    var Raw = await Response.text();
    var Result = {};
    try {
        Result = Raw ? JSON.parse(Raw) : {};
    } catch (Err) {
        throw new Error(
            'Invalid JSON from ' + Url + ': ' + Raw.slice(0, 160)
        );
    }

    if (!Response.ok) {
        throw new Error(Result.Notice ||
            ('HTTP ' + Response.status + ' from ' + Url));
    }
    return Result;
}