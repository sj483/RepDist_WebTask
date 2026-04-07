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
beta0 = 2/3; % Decision noise (sd) [0,Inf]
beta1 = 1/3; % Feedback noise (sd) [0,Inf]
lambda = 0.1; % Lapse [0,1]
gamma = 0.5; % Asymmetric updating for losers [0,Inf]
mu = 0.4; % Attentional gate mean [0,1]
kappa = 6; % Attentional gate concentration [0,Inf]
q = 0.05; % Diffusion/Forgetting [0,1]
psi = 100;
omega  = 1;
asymmetry = @(x) min(x,0)*gamma - min(-x,0);
for iTrial = 1:size(C,1)
    
    c = C(iTrial,:)';
    
    g = tanh(omega*(log(exp(iTrial-psi-1)+1)));
    mask = (1-g).*diag(abs(c)) + g.*eye(6);
    
    d = c'*x; % Decision margin
    u = c'*mask*Sigma*mask*c; % Decision uncertainty (var)
    a = betarnd(mu*kappa,(1-mu)*kappa,1);
    
    %% Decision
    v = u + (beta0^2); % Decision uncertainty + decision noise (var)
    pCorrect = (1-lambda)*normcdf(d/sqrt(v)) + lambda/2;
    
    %% Feedback
    s = u + (beta1^2); % Decision uncertainty + feedback noise (var)
    r = d/sqrt(s);
    w_x = normpdf(r)/normcdf(r);
    w_Sigma = w_x*(w_x+r);
    x = x + a * asymmetry(w_x * ((mask*Sigma*mask*c)/sqrt(s)));
    Sigma = Sigma - a * w_Sigma * (mask*Sigma*mask*(c*c')*mask*Sigma*mask)/s + q*eye(6);
    
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