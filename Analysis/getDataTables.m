function [DataTable00,DataTable01,DataTable02] = getDataTables()
analysisDir = fileparts(mfilename('fullpath'));
dataTablesFile = fullfile(analysisDir,'DataTables.mat');

if exist(dataTablesFile,'file')
    X = load(dataTablesFile);
    DataTable00 = X.DataTable00;
    DataTable01 = X.DataTable01;
    DataTable02 = X.DataTable02;
    return
end

%% Get the data
Data =  getData();

%% Call getMiniTabs()
rng(1729);
fh = waitbar(0,'Processing...');
for iSubject = 1:size(Data,1)
    TItrainIO = Data.TItrainIO{iSubject};
    if isempty(fieldnames(TItrainIO))
        continue
    end
    [Ta,Tb] = getMiniTabs(TItrainIO,Data.SubjectId(iSubject));
    if iSubject == 1
        DataTable00 = Tb;
        DataTable01 = Ta;
    else
        DataTable00 = [DataTable00;Tb]; %#ok<*AGROW>
        DataTable01 = [DataTable01;Ta];
    end
    waitbar(iSubject/size(Data,1),fh);
end
close(fh);

%% Get the probe data
ProbeData = getProbeData(Data);

%% Compute response entropy
[dH,zH,nValid] = getResponseEntropy(Data);

%% Extract columns from Data to join
Data2Add = table;
Data2Add.SubjectId = Data.SubjectId;
Data2Add.ClientTimeZone = Data.ClientTimeZone;
Data2Add.Duration_TItrain = Data.Duration_TItrain;
Data2Add.Duration_TIprobe = Data.Duration_TIprobe;
Data2Add.Age = years(Data.DateTime_Consent-Data.BMY);
Data2Add.zAge = nan(size(Data2Add.Age));
Data2Add = [Data2Add,Data(:,3:13)];
Data2Add.dH = dH;
Data2Add.zH = zH;
Data2Add.nTrainR = nValid;

%% Make the output tables
DataTable00 = outerjoin(DataTable00,ProbeData,'MergeKeys',true);
DataTable00 = outerjoin(Data2Add,DataTable00,'MergeKeys',true);

%DataTable01 = outerjoin(DataTable01,ProbeData,'MergeKeys',true);
DataTable01 = outerjoin(Data2Add,DataTable01,'MergeKeys',true);

DataTable02 = DataTable01;
DataTable01.RT = [];
S = DataTable02.Correct;
DataTable02 = DataTable02(S,:);
DataTable02 = DataTable02(DataTable02.RT>200,:);

%% Populate centred variables
DataTable00.zAge = zscore(DataTable00.Age);
DataTable01.zAge = zscore(DataTable01.Age);
DataTable02.zAge = zscore(DataTable02.Age);

%% Save
%save(dataTablesFile,'DataTable00','DataTable01','DataTable02');
return