function [dH,zH,nValid] = getResponseEntropy(Data)
h = cellfun(@getH,Data.TItrainIO);
nValid = cellfun(@getN,Data.TItrainIO);

if exist('h0.mat','file')
    h0 = load('h0.mat');
    h0 = h0.h0;
else
    h0 = computeH0();
    save('h0.mat','h0');
end
zH = (mean(h0)-h)./std(h0);
dH = 3-h;
return

function [h] = getH(TItrainIO)
if isempty(fieldnames(TItrainIO))
    h = NaN;
    return
end
PosOnRight = [TItrainIO([TItrainIO.ResponseMade]').PosOnRight]';
Correct = [TItrainIO.Correct]';
RespondedRight = ~xor(PosOnRight,Correct);
r = categorical(RespondedRight);
B = crosstab(r(1:end-2),r(2:end-1),r(3:end));
b = B(:);
p = b./sum(b);
h = -sum(p.*log2(p));
return

function [n] = getN(TItrainIO)
if isempty(fieldnames(TItrainIO))
    n = NaN;
    return
end
r = [TItrainIO.ResponseMade];
n = sum(r);
return

function [h0] = computeH0()
rng(196883);
nI = 1e5;
h0 = nan(nI,1);
for ii = 1:nI
    r = randi([0,1],125,1);
    B = crosstab(r(1:end-2),r(2:end-1),r(3:end));
    b = B(:);
    p = b./sum(b);
    try
        h0(ii) = -sum(p.*log2(p),'omitmissing');
    catch
        h0(ii) = -nansum(p.*log2(p)); %#ok<NANSUM>
    end
end
return