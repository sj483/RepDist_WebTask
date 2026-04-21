function [Ta,Tb] = getMiniTabs(TItrainIO,SubjectId)
%% Ta for DataTable01
Ta = struct2table(TItrainIO);
Ta.t = floor(((1:numel(TItrainIO))'-1)./5);
Ta.PairId = categorical(Ta.PairId);
if iscell(Ta.Correct)
    missing = cellfun(@isempty,Ta.Correct);
    Ta.Correct(missing) = num2cell(rand(sum(missing),1)>0.5);
    Ta.RT(missing) = num2cell(nan(sum(missing),1));
    Ta.Correct = cell2mat(Ta.Correct);
    Ta.RT = cell2mat(Ta.RT);
end
Ta.pCorrect = nan(size(Ta.Correct));
Ta.apCorrect = nan(size(Ta.Correct));
Ta = [Ta(:,6),Ta(:,1:3),Ta(:,7:8),Ta(:,4:5)];

uPairId = unique(Ta.PairId);
Pcorr  = nan(25,5);
for iPairId = 1:numel(uPairId)
    y = Ta.Correct(uPairId(iPairId)==Ta.PairId);
    [~, ~, cpmid, ~, ~] = dyadicStateSpaceMdl(y, 1, 0.5, 0.005, 2);
    Ta.pCorrect(Ta.PairId==uPairId(iPairId)) = cpmid(1:end-1);
    Pcorr(:,iPairId) = cpmid(2:end)';
end

%% Tb for DataTable00
Tb = table;
Tb.nTrainT = nan(1,1);
Tb.kTrain = nan(1,1);
Tb.fpCorrect = nan(1,1);
Tb.afpCorrect = nan(1,1);
Tb.b0 = nan(1,1);
Tb.b1 = nan(1,1);
Tb.mRtcTrain = nan(1,1);

x = (0:24)';
mu = geomean(Pcorr,2);
Tb.fpCorrect = mu(end);
mdl = fitglm(table(x,mu),'mu ~ 1 + x','Link','logit');
k = sum(Ta.Correct);
n = numel(Ta.Correct);
try
    mRtc = mean(Ta.RT(Ta.Correct),'omitmissing');
catch
    mRtc = nanmean(Ta.Correct); %#ok<NANMEAN>
end
Tb.b0 = mdl.Coefficients.Estimate(1);
Tb.b1 = mdl.Coefficients.Estimate(2);
Tb.nTrainT = n;
Tb.kTrain = k;
Tb.mRtcTrain = mRtc;

%% Finish off
Ta.apCorrect = (Ta.pCorrect-0.5).*2;
Tb.afpCorrect = (Tb.fpCorrect-0.5).*2;
Tc = table(repmat(SubjectId,size(Ta,1),1),'VariableNames',{'SubjectId'});
Ta = [Tc,Ta];
Tc = table(repmat(SubjectId,size(Tb,1),1),'VariableNames',{'SubjectId'});
Tb = [Tc,Tb];
return