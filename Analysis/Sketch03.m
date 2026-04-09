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
%%
Trials = struct;

M = zeros(6); % Learned item co-occurences
x = zeros(6,1); % Item values
Sigma = eye(6); % Value covariences

alpha0 = 0.9; % Learning rate for item co-occurences
gamma = 0.9; % Discount factor

beta0 = 0; % Decision noise (sd) [0,Inf]
beta1 = 0; % Feedback noise (sd) [0,Inf]
lambda = 0; % Lapse [0,1]
psi = 0.5; % Asymmetric updating for losers [0,Inf]
q = 0; % Diffusion/Forgetting [0,1]
alpha2 = 0.8; % Learning rate for item values

%%
asymmetry = @(dx) min(x,0)*psi - min(-dx,0);
for iTrial = 1:size(C,1)
    
    c = C(iTrial,:)';
    
    %% Update M and generate the successor representation
    cStar = abs(c);
    cStar = cStar ./ sum(cStar);
    M = M + alpha0.*(1-sum(M,1)).*(cStar*cStar');
    S = inv(eye(6)-gamma.*M); % Successor representation
    
    %% Generate the item mask and SigmaStar
    mask = S*cStar; %#ok<MINV>
    mask = mask ./ max(mask);
    mask = diag(mask);
    SigmaStar = mask*Sigma*mask; % Masked value covariences
    
    %% Generate the Decision margin and uncertainty
    d = c'*x; % Decision margin
    u = c'*SigmaStar*c; % Decision uncertainty (var)
    
    %% Compute pCorrect
    v = u + (beta0^2); % Decision uncertainty + decision noise (var)
    pCorrect = (1-lambda)*normcdf(d/sqrt(v)) + lambda/2;
    
    %% Feedback
    s = u + (beta1^2); % Decision uncertainty + feedback noise (var)
    r = d/sqrt(s); % Like a t-stat
    w_x = normpdf(r)/normcdf(r); % Inverse Mills ratio
    w_Sigma = w_x*(w_x+r);
    x = x + alpha2 * asymmetry(w_x * ((SigmaStar*c)/sqrt(s)));
    Sigma = Sigma - alpha2 * w_Sigma * (SigmaStar*(c*c')*SigmaStar)/s + q*eye(6);
    
    %% Save
    Trials(iTrial,1).pCorrect = pCorrect;
    Trials(iTrial,1).x = x';
    Trials(iTrial,1).Sigma = Sigma;
    
end
Trials = struct2table(Trials);

figure;
subplot(1,2,1);
plot(Trials.pCorrect);
subplot(1,2,2);
imagesc(Trials.x);