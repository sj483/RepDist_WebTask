function [DataTable] = getProbeData(Data)
nProbeT = [];
%nProbeR = [];
kProbe = [];
mRtcProbe = [];
SubjectId = [];
for iSubject = 1:size(Data,1)

    TIprobeIO = Data.TIprobeIO{iSubject};
    if isempty(fieldnames(TIprobeIO))
        continue
    end
    TIprobeIO = struct2table(TIprobeIO);
    TIprobeIO.Inffered = TIprobeIO.PairId > 4;

    % Deal with missing responses
    nMissing = sum(~TIprobeIO.ResponseMade);
    if nMissing > 0
        n01 = floor(nMissing/2);
        v = [
            zeros(n01,1);
            ones(n01,1);
            randi([0,1],mod(nMissing,2),1)];
        v = v(randperm(numel(v)));
        v = logical(v);
        TIprobeIO.Correct(~TIprobeIO.ResponseMade) = num2cell(v);
        TIprobeIO.RT(~TIprobeIO.ResponseMade) = {NaN};
        TIprobeIO.Correct = cell2mat(TIprobeIO.Correct);
        TIprobeIO.RT = cell2mat(TIprobeIO.RT);
    end
    n = groupsummary(TIprobeIO,'Inffered','sum','ResponseMade');
    k = groupsummary(TIprobeIO,'Inffered','sum','Correct');
    s0 = ~TIprobeIO.Inffered & TIprobeIO.Correct;
    s1 = TIprobeIO.Inffered & TIprobeIO.Correct;
    try
        rt = [...
            mean(TIprobeIO.RT(s0),'omitmissing'), ...
            mean(TIprobeIO.RT(s1),'omitmissing')];
    catch
        rt = [...
            nanmean(TIprobeIO.RT(s0)), ...
            nanmean(TIprobeIO.RT(s1))]; %#ok<NANMEAN>
    end
    
    SubjectId = [SubjectId;Data.SubjectId(iSubject)]; %#ok<*AGROW>
    nProbeT = [nProbeT;n.GroupCount'];
    %nProbeR = [nProbeR;n.sum_ResponseMade'];
    kProbe = [kProbe;k.sum_Correct'];
    mRtcProbe = [mRtcProbe;rt];
end
SubjectId = table(SubjectId);
DataTable = array2table([nProbeT,kProbe,mRtcProbe],'VariableNames',...
    {'nPremiT','nInferT','kPremi','kInfer','mRtcPremi','mRtcInfer'});
DataTable = [...
    DataTable(:,1),DataTable(:,3),...
    DataTable(:,2),DataTable(:,4),...
    DataTable(:,5:6)];
DataTable = [SubjectId,DataTable];
return