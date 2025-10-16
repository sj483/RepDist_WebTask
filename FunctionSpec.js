// Function to get the stimulus assignments
async function GetAssignment(){
    var Data = {};
	Data.SubjectId = SubjectId;

	//Send data to php script
	var P1 = await fetch('./GetAssignment.php', {
		method: 'post',
		headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
		body: JSON.stringify(Data)
	});

    // Return the result
    var Result = await P1.json();
    return Result.Assignment;
}
  
// Function to shuffle an array (used in GetTimelineVars)
function Shuffle(array) {
    let currentIndex = array.length,  randomIndex;
    
    // While there remain elements to shuffle.
    while (currentIndex != 0) {
        // Pick a remaining element.
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex--;
        
        // And swap it with the current element.
        [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
    }
    return array;
}

// Function to set what happens when an image is clicked
function ImgClicked(Id) {
    
    if (!ResponseMade) {
        // If no response was made previously (i.e., this is the first click)...
        
        // Set the response time
        RT = jsPsych.getTotalTime() - StartTimeOfTrial;
        
        // Set SelectedRight
        if (Id=="ImgLeft") {
            SelectedRight = false;
        } else {
            SelectedRight = true;
        }
        
        // Combine PosOnRight and SelectedRight to set Correct
        Correct = !(PosOnRight^SelectedRight);
        
        // Change the appearance of the stimuli
        document.getElementById(Id).style = `vertical-align:middle; margin: 0px 60px; border: ${BorderWidth}px solid #0000ff; width: ${ImgWidth1}px;`;
        if (Id=='ImgLeft') {
            document.getElementById('ImgRight').style = `vertical-align:middle; margin: 0px 60px; border: ${BorderWidth}px solid #808080; width: ${ImgWidth1}px;`;
        } else {
            document.getElementById('ImgLeft').style = `vertical-align:middle; margin: 0px 60px; border: ${BorderWidth}px solid #808080; width: ${ImgWidth1}px;`;
        }
        
        // Set ResponseMade to be true so that the above code cannot be run again during the current trial
        ResponseMade = true;
    }
}