// Import the main jsPsych object
const jsPsych = initJsPsych({
    on_finish: function() {
        WriteTaskIO().then(function(Result) {
            window.location.replace(Result.TargetUrl);
        }).catch(function(Err) {
            alert('An error has occurred.\nPlease report error code #200 to the experimenter:\n'+Err);
            window.location.replace("./Error.html");
        });
    }
});

// Set some global variables that are available in all functions
// High-level global variables
var Assignment;
var Pairs = [];
var TimelineVars;

// Trial specific global variables
var PairId = null;
var PosOnRight = null;
var StartTimeOfTrial = null;
var SelectedRight = null;
var Correct = null;
var RT = null;
var ResponseMade = false;

// Specify the preload event

var ImgsToPreload = [
"Imgs/Ani0.png",
"Imgs/Ani1.png",
"Imgs/Ani2.png",
"Imgs/Ani3.png",
"Imgs/Ani4.png",
"Imgs/Ani5.png",
"Imgs/Art0.png",
"Imgs/Art1.png",
"Imgs/Art2.png",
"Imgs/Art3.png",
"Imgs/Art4.png",
"Imgs/Art5.png",
"Imgs/Fac0.png",
"Imgs/Fac1.png",
"Imgs/Fac2.png",
"Imgs/Fac3.png",
"Imgs/Fac4.png",
"Imgs/Fac5.png",
"Imgs/Foo0.png",
"Imgs/Foo1.png",
"Imgs/Foo2.png",
"Imgs/Foo3.png",
"Imgs/Foo4.png",
"Imgs/Foo5.png",
"Imgs/Lin0.png",
"Imgs/Lin1.png",
"Imgs/Lin2.png",
"Imgs/Lin3.png",
"Imgs/Lin4.png",
"Imgs/Lin5.png",
"Imgs/Obj0.png",
"Imgs/Obj1.png",
"Imgs/Obj2.png",
"Imgs/Obj3.png",
"Imgs/Obj4.png",
"Imgs/Obj5.png",
"Imgs/Pla0.png",    
"Imgs/Pla1.png",
"Imgs/Pla2.png",
"Imgs/Pla3.png",
"Imgs/Pla4.png",
"Imgs/Pla5.png",
"Imgs/Spa0.png",
"Imgs/Spa1.png",
"Imgs/Spa2.png",
"Imgs/Spa3.png",
"Imgs/Spa4.png",
"Imgs/Spa5.png",
"Imgs/Tex0.png",
"Imgs/Tex1.png",
"Imgs/Tex2.png",
"Imgs/Tex3.png",
"Imgs/Tex4.png",
"Imgs/Tex5.png"
    ];
var PreloadImgs = {
    type: jsPsychPreload,
    images: ImgsToPreload
};

// Specify the ExitFullscreen event
var ExitFullscreen = {
    type: jsPsychFullscreen,
    fullscreen_mode: false,
    on_finish: function() {EnforceUnfocus = false;}
};

// Specify the Fixation event
var Fixation = {
    type: jsPsychHtmlButtonResponse,
    stimulus: '<p><font color="#000000" size="60px">+</font></p>',
    choices: [],
    prompt: "",
    trial_duration: 1000,
    
    // Reset all the global variables (on_start is called at the beginning of the trial)
    on_start: function() {
        PairId = jsPsych.timelineVariable('PairId');
        PosOnRight = jsPsych.timelineVariable('PosOnRight');
        ResponseMade = false;
        SelectedRight = null;
        Correct = null;
        RT = null;
    }
};

// Specify the Trial event
var Trial = {
    
    // Set the start time of this trial
    on_start: function() {
        StartTimeOfTrial = jsPsych.getTotalTime();
    },
    
    // Set the type of this trial
    type: jsPsychHtmlButtonResponse,
    
    // Set the stimuli to display
    stimulus: function(){
        // Specify the individual part of the stimulus HTML string
        var Part1 = '<img src="';
        var Part3 = `" width="${ImgWidth0}px" style="vertical-align:middle;margin:0px 60px" id="ImgLeft" onclick="javascript:ImgClicked(this.id)"> <img src="`;
        var Part5 = `" width="${ImgWidth0}px" style="vertical-align:middle;margin:0px 60px" id="ImgRight" onclick="javascript:ImgClicked(this.id)">`;
        var Pos = jsPsych.timelineVariable('Pos');
        var Neg = jsPsych.timelineVariable('Neg');
        
        // Construct the StimString based on the value of PosOnRight
        if (jsPsych.timelineVariable('PosOnRight')){
            var StimString = Part1+Neg+Part3+Pos+Part5;
        } else {
            var StimString = Part1+Pos+Part3+Neg+Part5;
        }
        return StimString;
        },
    
    // Set the choices and prompt fields to be empty (we don't use them)
    choices: [],
    prompt: "",
    
    // Set the trial duration
    trial_duration: 4000,
    
    // At the end of the trial, push the results into TaskIO.Trials
    on_finish: function() {
        var TrialObj = {
            PairId: PairId,
            PosOnRight: PosOnRight,
            ResponseMade: ResponseMade,
            Correct: Correct,
            RT: RT
        };
        TaskIO.Trials.push(TrialObj);
    }
};

// Specify the Feedback event
var Feedback = {
    type: jsPsychHtmlButtonResponse,
    
    // Set the feedback to display
    stimulus: function() {
        if (ResponseMade) {
            if (Correct) {
                return '<p style="vertical-align:middle; margin: 0px 5px 30px; color:#00ff00; font-size:100px">&#10003;</p>';
            } else {
                return '<p style="vertical-align:middle; margin: 0px 5px 30px; color:#ff0000; font-size:100px">&#10060;</p>';
            }
        } else {
            return '<p style="vertical-align:middle;color:#ff0000;font-size:40px">Please try to respond on time!</p>';
        }
    },
    
    // Set the choices and prompt fields to be empty (we don't use them)
    choices: [],
    prompt: "",
    
    // Set the feedback duration (depends on whether a response was made)
    trial_duration: function() {
        if (ResponseMade) {
            return 3000;
        } else {
            return 5000;
        }
    }
};