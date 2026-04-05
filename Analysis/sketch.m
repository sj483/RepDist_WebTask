Data = getData();
TaskIO = struct2table(Data.TItrainIO{3});
pairId = TaskIO.PairId;

%%
C = [pairId==0,...              A
    (pairId==1)-(pairId==0),... B
    (pairId==2)-(pairId==1),... C
    (pairId==3)-(pairId==2),... D
    (pairId==4)-(pairId==3),... E
               -(pairId==4)]; % F
Trials = struct;
x = zeros(6,1);
Sigma = eye(6);
beta0 = 2/3; % Decision noise (sd)
beta1 = 1/3; % Feedback noise (sd)
lambda = 0.1; % Lapse [0,1]
alpha = 0; % Reduced updating for losers [0,1]
gamma = 6; % Attentional gate [1,Inf]
q = 0.05; % Forgetting
asymmetry = @(x) min(x,0)*0.5 - min(-x,0);
for iTrial = 1:size(C,1)
    
    c = C(iTrial,:)';
    d = c'*x; % Decision variable
    u = c'*Sigma*c; % Decision uncertainty (var)
    a = betarnd(1,gamma,1);
    
    %% Decision
    v = u + (beta0^2); % Decision uncertainty + decision noise (var)
    pCorrect = (1-lambda)*normcdf(d/sqrt(v)) + lambda/2;
    
    %% Feedback
    s = u + (beta1^2); % Decision uncertainty + feedback noise (var)
    r = d/sqrt(s);
    w_x = normpdf(r)/normcdf(r);
    w_Sigma = w_x*(w_x+r);
    x = x + a * w_x * ((Sigma*c)/sqrt(s));
    Sigma = Sigma - a * w_Sigma * (Sigma*(c*c')*Sigma)/s + q*eye(6);
    
    %% Save
    Trials(iTrial,1).pCorrect = pCorrect;
    Trials(iTrial,1).x = x';
    Trials(iTrial,1).Sigma = Sigma;
    
end
Trials = struct2table(Trials);