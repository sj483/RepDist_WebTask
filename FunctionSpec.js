async function GetAssignment() {

    // If no Subject ID is provided, we must be testing...
    if (!Boolean(SubjectId)) {
        var Assignment = {};
        Assignment.GroupId = 'Ani';
        Assignment.ImgPerm = {};
        for (iI = 0; iI < 6; iI++) {
            var LocId  = String.fromCharCode(65 + iI);
            Assignment.ImgPerm[LocId] = Assignment.GroupId + iI.toString();
        }
        return Assignment;
    }

    // Otherwise, request the Assignment from the server...
    var Data = {};
    Data.SubjectId = SubjectId;
    var P1 = await fetch('./GetAssignment.php', {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Data)
    });
    var Assignment = await P1.json();
    // Assignment contains the following fields: GroupId, ImgPerm
    return Assignment;
}

function GetImgsToPreload() {
    var Imgs = [];
    for (iI = 0; iI < 6; iI++) {
        Imgs.push('./Imgs/' + TaskIO.GroupId + iI.toString() + '.png');
    }
    return Imgs;
}

async function GetTimelineVars(Pairs) {
    var Timeline = [];
    for (iRep = 0; iRep < 1; iRep++) { /////////////////////////////////////////////////// 25 reps????
        var Order = [0, 1, 2, 3, 4];
        Shuffle(Order);
        for (iOrder = 0; iOrder < 5; iOrder++) {
            var cPairId = Pairs[Order[iOrder]].PairId;
            var cPos = './Imgs/' + Pairs[Order[iOrder]].Pos + '.png';
            var cNeg = './Imgs/' + Pairs[Order[iOrder]].Neg + '.png';
            var cP1 = await fetch('./Assets/RandBit.php');
            var cBit = await cP1.json();
            cBit = cBit == 1;
            Timeline.push({
                PairId: cPairId,
                Pos: cPos,
                Neg: cNeg,
                PosOnRight: cBit
            });
        }
    }
    return Timeline;
}

function Shuffle(array) {
    let currentIndex = array.length, randomIndex;
    while (currentIndex != 0) {
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex--;
        [array[currentIndex], array[randomIndex]] =
            [array[randomIndex], array[currentIndex]];
    }
    return array;
}

function ImgClicked(Id) {
    if (ResponseMade) {
        return;
    }
    // If no response was made previously (i.e., this is the first)...
    RT = jsPsych.getTotalTime() - StartTimeOfTrial;

    // Set SelectedRight
    if (Id == "ImgLeft") {
        SelectedRight = false;
    } else {
        SelectedRight = true;
    }

    // Combine PosOnRight and SelectedRight to set Correct
    Correct = !(PosOnRight ^ SelectedRight);

    // Change the appearance of the stimuli
    document.getElementById(Id).style =
        `vertical-align: middle; 
            margin: 0px 60px; 
            border: ${BorderWidth}px solid #0000ff; 
            width: ${ImgWidth1}px;`;
    if (Id == 'ImgLeft') {
        document.getElementById('ImgRight').style =
            `vertical-align: middle; 
                margin: 0px 60px; 
                border: ${BorderWidth}px solid #808080; 
                width: ${ImgWidth1}px;`;
    } else {
        document.getElementById('ImgLeft').style =
            `vertical-align:middle; 
                margin: 0px 60px; 
                border: ${BorderWidth}px solid #808080; 
                width: ${ImgWidth1}px;`;
    }

    // Set ResponseMade to be true
    ResponseMade = true;
}

async function WriteTaskIO() {
    var Data = {};
    Data.SubjectId = SubjectId;
    Data.ClientTimeZone = ClientTimeZone;
    Data.TItrainIO = JSON.stringify(TaskIO);
    var P1 = await fetch('./WriteXIO.php', {
        method: 'post',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Data)
    });
    var Result = await P1.json();
    return Result;
}

async function OnFinishTask() {
    try {
        var Result = await WriteTaskIO();
        window.location.replace(Result.TargetUrl);
    } catch (Err) {
        alert(`An error has occurred.\n
            Please report error code #XXX to the experimenter:\n` +
            Err);
        window.location.replace(
            "./Error.html?SubjectId="+SubjectId+'&ErrorCode=XXX#');
    }
}