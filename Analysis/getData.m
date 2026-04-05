function [WebData] =  getData()

WebData = webread('https://c01.learningandinference.org/GetData.php');
WebData = struct2table(WebData);

%% 1. SubjectId
WebData.SubjectId = categorical(WebData.SubjectId);

%% 2. Birth month+year
WebData.BMY = datetime(WebData.BMY,...
    'InputFormat','yyyy-MM',...
    'TimeZone','Europe/London') ...
    + duration(30.44*24/2,0,0);
% We add on 1/2 the average number of days in a month to minimise the
% expected error.

%% 3. Gender
female = cellfun(@(s)strcmpi(s(1),'f'),WebData.Gender);
male = cellfun(@(s)strcmpi(s(1),'m'),WebData.Gender);
nonbinary = ~(male|female);
WebData.Gender = categorical(...
    cellstr(char([female,male,nonbinary]*double('fmn')')));

%% 4. Handedness
WebData.Handedness = categorical(WebData.Handedness);

%% 5. L1
WebData.L1 = categorical(WebData.L1);

%% 6. State
WebData.State = cellfun(@(s)str2double(s),WebData.State);

%% 7. GroupId
WebData.GroupId = categorical(WebData.GroupId);

%% 8-13. ImgPerm
ImgPerm = struct2table(cellfun(@jsondecode,WebData.ImgPerm));
for c = cellfun(@(d){char(d)},num2cell((0:5)+double('A')))
    ImgPerm.(c{1}) = categorical(ImgPerm.(c{1}));
end
WebData = [WebData(:,1:7),ImgPerm,WebData(:,9:end)];

%% 14-19. DateTime_*
varNames = WebData.Properties.VariableNames;
for ii = 14:19
    s = varNames{ii};
    WebData.(s) = datetime(WebData.(s),'TimeZone','Europe/London');
end

%% 20. ClientTimeZone
WebData.ClientTimeZone = categorical(WebData.ClientTimeZone);

%% 21-23. TItrainIO
TItrainIO = cellfun(@decodeTaskIO,WebData.TItrainIO);
TItrainIO = struct2table(TItrainIO);
TItrainIO.Properties.VariableNames{1} = 'DateTime_StartTItrain';
TItrainIO.Properties.VariableNames{2} = 'TItrainIO';
TItrainIO.Duration_TItrain = WebData.DateTime_TItrain - ...
    TItrainIO.DateTime_StartTItrain;
TItrainIO = [TItrainIO(:,1),TItrainIO(:,3),TItrainIO(:,2)];
WebData = [WebData(:,1:20),TItrainIO,WebData(:,22:end)];

%% 24-25. TIprobeIO
TIprobeIO = cellfun(@decodeTaskIO,WebData.TIprobeIO);
TIprobeIO = struct2table(TIprobeIO);
TIprobeIO.Properties.VariableNames{1} = 'DateTime_StartTIprobe';
TIprobeIO.Properties.VariableNames{2} = 'TIprobeIO';
TIprobeIO.Duration_TIprobe = WebData.DateTime_TIprobe - ...
    TIprobeIO.DateTime_StartTIprobe;
TIprobeIO = [TIprobeIO(:,1),TIprobeIO(:,3),TIprobeIO(:,2)];
WebData = [WebData(:,1:23),TIprobeIO];
return

function [out] = decodeTaskIO(in)
if isempty(in)
    out.DateTime_Start = NaT;
    out.DateTime_Start.TimeZone = 'Europe/London';
    out.Trials = struct();
    return
end
out = jsondecode(in);
out.DateTime_Start = datetime(out.DateTime_Start,...
    'InputFormat','yyyyMMdd_HHmmss',...
    'TimeZone','Europe/London');
out = rmfield(out,'SubjectId');
out = rmfield(out,'ClientTimeZone');
out = rmfield(out,'GroupId');
out = rmfield(out,'Pairs');
return