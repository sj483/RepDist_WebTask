function [Data] =  getData()

Data = webread('https://c01.learningandinference.org/GetData.php');
Data = struct2table(Data);

%% 1. SubjectId
Data.SubjectId = categorical(Data.SubjectId);

%% 6. State (We do this first so we can filter)
Data.State = cellfun(@(s)str2double(s),Data.State);
Data = Data(Data.State==6,:);

%% 2. Birth month+year
Data.BMY = datetime(Data.BMY,...
    'InputFormat','yyyy-MM',...
    'TimeZone','Europe/London') ...
    + duration(30.44*24/2,0,0);
% We add on 1/2 the average number of days in a month to minimise the
% expected error.

%% 3. Gender
female = cellfun(@(s)strcmpi(s(1),'f'),Data.Gender);
male = cellfun(@(s)strcmpi(s(1),'m'),Data.Gender);
nonbinary = ~(male|female);
Data.Gender = categorical(...
    cellstr(char([female,male,nonbinary]*double('fmn')')));

%% 4. Handedness
Data.Handedness = categorical(Data.Handedness);

%% 5. L1
Data.L1 = categorical(Data.L1);

%% 7. GroupId
Data.GroupId = categorical(Data.GroupId);

%% 8-13. ImgPerm
ImgPerm = struct2table(cellfun(@jsondecode,Data.ImgPerm));
for c = cellfun(@(d){char(d)},num2cell((0:5)+double('A')))
    ImgPerm.(c{1}) = categorical(ImgPerm.(c{1}));
end
Data = [Data(:,1:7),ImgPerm,Data(:,9:end)];

%% 14-19. DateTime_*
varNames = Data.Properties.VariableNames;
for ii = 14:19
    s = varNames{ii};
    Data.(s) = datetime(Data.(s),'TimeZone','Europe/London');
end

%% 20. ClientTimeZone
Data.ClientTimeZone = categorical(Data.ClientTimeZone);

%% 21-23. TItrainIO
TItrainIO = cellfun(@decodeTaskIO,Data.TItrainIO);
TItrainIO = struct2table(TItrainIO);
TItrainIO.Properties.VariableNames{1} = 'DateTime_StartTItrain';
TItrainIO.Properties.VariableNames{2} = 'TItrainIO';
TItrainIO.Duration_TItrain = Data.DateTime_TItrain - ...
    TItrainIO.DateTime_StartTItrain;
TItrainIO = [TItrainIO(:,1),TItrainIO(:,3),TItrainIO(:,2)];
Data = [Data(:,1:20),TItrainIO,Data(:,22:end)];

%% 24-25. TIprobeIO
TIprobeIO = cellfun(@decodeTaskIO,Data.TIprobeIO);
TIprobeIO = struct2table(TIprobeIO);
TIprobeIO.Properties.VariableNames{1} = 'DateTime_StartTIprobe';
TIprobeIO.Properties.VariableNames{2} = 'TIprobeIO';
TIprobeIO.Duration_TIprobe = Data.DateTime_TIprobe - ...
    TIprobeIO.DateTime_StartTIprobe;
TIprobeIO = [TIprobeIO(:,1),TIprobeIO(:,3),TIprobeIO(:,2)];
Data = [Data(:,1:23),TIprobeIO];
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