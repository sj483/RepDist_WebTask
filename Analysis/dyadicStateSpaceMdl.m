%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% Script to run the analysis of the Individual subject learning curve using
% binomial Expectation Maximization
% Version 1.3
% 
% Anne Smith, April 28th, 2003
% 
% updated Anne Smith, August 10th, 2004  - adjusted initial variance for UpdaterFlag=2 case
% 
% updated Leo Walton, July 27th, 2006 - added comments, changed variable
% names
% %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
% 
% Given a set of behavioral experiment trial data, this program estimates an
% unobservable learning state process, defined as a random walk, by tracking
% the evolution of the observable trial data.  It uses a state-space model
% and Expectation-Maximization algorithm to estimate a learning curve that
% characterizes the dynamics of the learning process as a function of trial
% number.  For a thorough description of this method of analysis, see: 
%       Smith et al. (2004)  Dynamic Analysis of Learning in Behavioral
%           Experiments. The Journal of Neuroscience. 24(2):447-461.
% Throughout this code, references to equations from this journal article 
% will be indicated with a "*":  
% 
% Input: 
%        Responses      (1 x N vector) number of correct responses at
%                                                 each trial -- required
%
%        MaxResponse    (single value)  total number that could be correct 
%                                       at each trial.  1 for binary data.
%                                       -- required
%
%        BackgroundProb     (single value) probabilty of correct by chance
%                                       -- required
%
%        SigE       (single value) SIG_EPSILON, sqrt(variance) of learning
%                               state process -- optional. Default 0.005
% 
%        UpdaterFlag    (single value)
%                        0-to fix initial condition (more likely to give a result)
%                        1-to estimate initial condition 
%                        2-to remove xo from likelihood - this means that the latent
% 					     learning process is not started at 0 and allows for alot
% 					     of bias 
%                        -- optional. Default 2
                   
% functions variables
%        x, s   (vectors)         (hidden) learning state process and its variance (forward estimate)
%        xnew, signewsq (vectors) (hidden) learning state process and its variance (backward estimate)
%        newsigsq                 estimate of learning state process variance from EM 
%        A (vector)               A(k), (equation A.11)*                       
%        p (vectors)              mode of prob correct estimate 
%        p05,p95,pmid, pmode,pmatrix (vectors)     conf limits of prob correct estimate
%
% helper functions
%       forwardfilter   solves the forward recursive filtering algorithm to
%                       estimate p, x, s, xold, and sold
%       backwardfilter  solves the backward filter smoothing algorithm to 
%                       estimate xnew, signewsq, and A
%       embino         solves the maximization step of the EM algorithm to
%                       estimate newsigsq
%       pdistn          calculates the confidence limits (p05, p95, pmid, 
%                       pmode, pmatrix) of the EM estimate of the learning
%                       state process 
% 

function [p05, p95, pmid, pmode, pmatrix] = dyadicStateSpaceMdl(...
     Responses,MaxResponse, BackgroundProb, SigE, UpdaterFlag)

if nargin<4
    warning('SigE = 0.005.  Default variance of learning state process is sqrt(0.005)');
    SigE = 0.005; %default variance of learning state process is sqrt(0.005)
end
if nargin<5
    warning('UpdaterFlag = 2.  Default setting allows bias');    
    UpdaterFlag = 2;  %default allows bias 
end
        
% check data format.  Reshape dataset if needed
[a,b] = size(Responses);
if a>b
    Responses = Responses';
end

I = [Responses; MaxResponse*ones(1,length(Responses))];

SigsqGuess  = SigE^2;

%set the value of mu from the chance of correct
 mu = log(BackgroundProb/(1-BackgroundProb)); 

%convergence criterion for SIG_EPSILON^2
 CvgceCrit = 1e-8;

%----------------------------------------------------------------------------------

xguess         = 0;  
NumberSteps  = 2000;

%loop through EM algorithm: forward filter, backward filter, then
%M-step
for i=1:NumberSteps
   
   %Compute the forward (filter algorithm) estimates of the learning state
   %and its variance: x{k|k} and sigsq{k|k}
   [p, x, s, xold, sold] = forwardfilter_sam(I, SigE, xguess, SigsqGuess, mu);   
   
   %Compute the backward (smoothing algorithm) estimates of the learning 
   %state and its variance: x{k|K} and sigsq{k|K}
   [xnew, signewsq, A]   = backwardfilter(x, xold, s, sold); 

   if (UpdaterFlag == 1)
        xnew(1) = 0.5*xnew(2);   %updates the initial value of the latent process
        signewsq(1) = SigE^2;
   elseif(UpdaterFlag == 0)
        xnew(1) = 0;             %fixes initial value (no bias at all)
        signewsq(1) = SigE^2;
   elseif(UpdaterFlag == 2)
        xnew(1) = xnew(2);       %x(0) = x(1) means no prior chance probability
        signewsq(1) = signewsq(2);
   end
   
   %Compute the EM estimate of the learning state process variance
   [newsigsq(i)]         = em_bino(I, xnew, signewsq, A, UpdaterFlag);
   
   xnew1save(i) = xnew(1);
   
   %check for convergence
   if(i>1)
      a1 = abs(newsigsq(i) - newsigsq(i-1));
	  a2 = abs(xnew1save(i) -xnew1save(i-1));
      if( a1 < CvgceCrit & a2 < CvgceCrit & UpdaterFlag >= 1)
          %fprintf(2, 'EM estimates of learning state process variance and start point converged after %d steps   \n',  i)
          break
      elseif ( a1 < CvgceCrit & UpdaterFlag == 0)
          %fprintf(2, 'EM estimate of learning state process variance converged after %d steps   \n',  i)
          break
      end
   end
 
   SigE   = sqrt(newsigsq(i));
   xguess = xnew(1);
   SigsqGuess = signewsq(1);

end
   
if(i == NumberSteps)
     %fprintf(2,'failed to converge after %d steps; convergence criterion was %f \n', i, CvgceCrit)
end

%-----------------------------------------------------------------------------------
%integrate and do change of variables to get confidence limits

[p05, p95, pmid, pmode, pmatrix] = pdistn(xnew, signewsq, mu, BackgroundProb);

%-------------------------------------------------------------------------------------
%find the last point where the 90 interval crosses chance
%for the backward filter (cback)

 cback = find(p05 < BackgroundProb);

 if(~isempty(cback))
      if(cback(end) < size(I,2) )
           cback = cback(end);
      else
           cback = NaN;
      end
 else
      cback = NaN;
 end
return

function  [p, xhat, sigsq, xhatold, sigsqold] ... 
		= forwardfilter_sam(I, sigE, xguess, sigsqguess, mu);
%forwardfilter is a helper function that implements the forward recursive 
%filtering algorithm to estimate the learning state (hidden process) at 
%trial k as the Gaussian random variable with mean x{k|k} (xhat) and 
%SIG^2{k|k} (sigsq).  
% 
%variables:
%   xhatold      x{k|k-1}, one-step prediction (equation A.6)*
%   sigsqold     SIG^2{k|k-1}, one-step prediction variance (equation A.7)*
%   xhat         x{k|k}, posterior mode (equation A.8)* 
%   sigsq        SIG^2{k|k}, posterior variance (equation A.9)*
%   p            p{k|k}, observation model probability (equation 2.2)*
%   N            vector of number correct at each trial
%   Nmax         total number that could be correct at each trial
%   K            total number of trials
%   number_fail  saves the time steps if Newton's Method fails

K = size(I,2);
N = I(1,:);  
Nmax = I(2,:);

%Initial conditions: use values from previous iteration
xhat(1)   = xguess;    
sigsq(1)  = sigsqguess;
number_fail = [];

for k=2:K+1 
   %for each trial, compute estimates of the one-step prediction, the
   %posterior mode (using Newton's Method), and the posterior variance
   %(estimates from subject's POV)
   
   %Compute the one-step prediction estimate of mean and variance     
   xhatold(k)  = xhat(k-1);  
   sigsqold(k) = sigsq(k-1) + sigE^2;  
   
   %Use Newton's Method to compute the nonlinear posterior mode estimate                                    
   [xhat(k),flagfail] = newtonsolve_sam(mu,  xhatold(k), sigsqold(k), N(k-1), Nmax(k-1));
            
   if flagfail>0 %if Newton's Method fails, number_fail saves the time step
      number_fail = [number_fail k];
   end
   
   %Compute the posterior variance estimate
   denom       = -1/sigsqold(k) - Nmax(k-1)*exp(mu)*exp(xhat(k))/(1+exp(mu)*exp(xhat(k)))^2;                                
   sigsq(k)    = -1/denom;

end

if isempty(number_fail)<1
   %fprintf(2,'Newton convergence failed at times %d \n', number_fail)
end

%Compute the observation model probability estimate
p = exp(mu)*exp(xhat)./(1+exp(mu)*exp(xhat));

return



function [x, timefail] = newtonsolve_sam(mu,  xold, sigoldsq, N, Nmax);
%newtonsolve is a helper function that implements Newton's Method in order 
%to recursively estimate the posterior mode (x).  Once the subsequent estimates
%sufficiently converge, the function returns the last estimate.  If, having
%never met this convergence condition, the function goes through all of the
%recursions, then a special flag (timefail) - indicating the convergence 
%failure - is returned along with the last posterior mode estimate.
%
%variables: 
%   g(i)         derivative of the learning state process
%   gprime(i)    derivative of g
%   it(i)        estimate of posterior mode (A.8)*
%   x            x{k|k}, the posterior mode

it(1) = xold + sigoldsq*(N - Nmax*exp(mu)*exp(xold)/(1 ... 
                                  + exp(mu)*exp(xold)));
                              
for i = 1:40    
   g(i)     = xold + sigoldsq*(N - Nmax*exp(mu)*exp(it(i))/...
                              (1+exp(mu)*exp(it(i)))) - it(i);
   gprime(i)= -Nmax*sigoldsq*exp(mu)*exp(it(i))/(1+exp(mu)*exp(it(i)))^2 - 1;
   it(i+1)  = it(i) - g(i)/gprime(i);
 
   x        = it(i+1);
   if abs(x-it(i))<1e-14    
      timefail = 0; 
      return
   end
end

if(i==40) 
   %fprintf(2, 'failed to converge \n');
   timefail = 1;
   return 
end

return

function [xnew, signewsq, A] = backwardfilter(x, xold, sigsq, sigsqold);
%backwardfilter is a helper function that implements the backward filter
%smoothing algorithm to estimate the learning state at trial k, given all
%the data, as the Gaussian random variable with mean x{k|K} (xnew) and
%SIG^2{k|K} (signewsq).  
% 
%variables:
%   x            x{k|k}, posterior mode 
%   xold         x{k|k-1}, one-step prediction
%   sigsq        SIG^2{k|k}, posterior variance
%   sigsqold     SIG^2{k|k-1}, one-step prediction variance
%   A(i)         A{k}, (equation A.11)*
%   xnew         x{k|K}, backward estimate of learning state given all the data (equation A.10)*
%   signewsq     SIG^2{k|K}, backward estimate of learning state variance (equation A.12)*
%   T            total number of posterior mode estimates (K + 1)

T = size(x,2);

%Initial conditions: use values of posterior mode and posterior variance
xnew(T)     = x(T);
signewsq(T) = sigsq(T);


for i = T-1 :-1: 2
 %for each posterior mode prediction, compute new estimates given all of
 %the data from the experiment (estimates from ideal observer)
   A(i)        = sigsq(i)/sigsqold(i+1);
   xnew(i)     = x(i) + A(i)*(xnew(i+1) - xold(i+1));
   signewsq(i) = sigsq(i) + A(i)*A(i)*(signewsq(i+1)-sigsqold(i+1));
end

return

function [newsigsq] = em_bino(I, xnew, signewsq, A, startflag);
%em_bino is a helper function that computes sigma_eps squared (estimated 
%learning process variance).  
%
%variables:
%   xnew         x{k|K}, backward estimate of learning state
%   signewsq     SIG^2{k|K}, backward estimate of learning state variance  
%   A            A{k}
%   M            total number of backward estimates (K + 1)
%   covcalc      covariance estimate (equation A.13)*
%   term1        W{k|K}       (equation A.15)*
%   term2        W{k,k-1|K}   (equation A.14)*
%   term3        derived from W{1|K}     (applies equation A.15)* 
%   term4        W{K|K}     (applies equation A.15)*
%   newsigsq     SIG_EPSILON^2, estimate of learning state variance from EM (equation A.16)*

M           = size(xnew,2);  

xnewt      = xnew(3:M);
xnewtm1    = xnew(2:M-1);
signewsqt  = signewsq(3:M);
A          = A(2:end);

covcalc    = signewsqt.*A;

term1      = sum(xnewt.^2) + sum(signewsqt);
term2      = sum(covcalc) + sum(xnewt.*xnewtm1);

if startflag == 1
 term3      = 1.5*xnew(2)*xnew(2) + 2.0*signewsq(2); 
 term4      = xnew(end)^2 + signewsq(end);
elseif( startflag == 0)
 term3      = 2*xnew(2)*xnew(2) + 2*signewsq(2);
 term4      = xnew(end)^2 + signewsq(end);
elseif( startflag == 2)
 term3      = 1*xnew(2)*xnew(2) + 2*signewsq(2);
 term4      = xnew(end)^2 + signewsq(end);
 M = M-1;
end

newsigsq   = (2*(term1-term2)+term3-term4)/M;
return

function [p05, p95, pmid, pmode, pmatrix] = pdistn(x, s, mu, background_prob);
%pdist is a helper function that calculates the confidence limits of the EM
%estimate of the learning state process.  For each trial, the function
%constructs the probability density for a correct response.  It then builds
%the cumulative density function from this and computes the p values of
%the confidence limits
%
%variables:
%   xx(ov)   EM estimate of learning state process
%   ss(ov)   EM estimate of learning state process variance
%   pmatrix  vector of the level of certainty the ideal observer has that performance is better than chance at each trial  
%   dels     bin size of the probability density p values
%   pr       bins of the probability density distribution
%   fp       p{k|j}, probability density of the probability of a correct response at trial k     (equation B.3)*
%   pdf      probability density function      
%   sumpdf   cumulative density function of the pdf
%   lowlimit index of the p value that gives the lower 95% confidence
%            bound
%   highlimit   index of the p value that gives the upper 95% confidence
%               bound
%   middlimit   index of the p value that gives the 
%   p05      the p value that gives the lower 95% confidence bound
%   p95      the p value that gives the upper 95% confidence bound
%   pmid     the p value that gives the 50% confidence bound
%   pmode    the p value that gives the highest probability density

pmatrix = [];
for ov = 1:size(x,2)

 xx = x(ov);
 ss = s(ov);
 
 dels=1e-4;

 pr  = dels:dels:1-dels;
 term1 = 1./(sqrt(2*pi*ss) * (pr.*(1-pr)));
 term2 = exp(-1/(2*ss) * (log (pr./((1-pr)*exp(mu))) - xx).^2);
 pdf = term1 .* term2;
 pdf = dels * pdf;
 
 
% Integrate the pdf
 sumpdf = cumtrapz(pdf);
% sumpdf = cumsum(pdf);

lowlimit  = find(sumpdf>0.05);
if(~isempty(lowlimit) )
lowlimit  = lowlimit(1);
else
lowlimit  = 1;
end

highlimit = find(sumpdf>0.95);
% highlimit = find(sumpdf>0.995);
if(~isempty(highlimit) )
if(length(highlimit)>1)
highlimit = highlimit(1)-1;
else
highlimit =  highlimit(1);
end
else
highlimit = length(pr);
end

middlimit = find(sumpdf>0.5);
if(~isempty(middlimit))
middlimit = middlimit(1);
else
middlimit = length(pr);
end


 p05(ov)   = pr(lowlimit(1));
 p95(ov)   = pr(highlimit(1));
 pmid(ov)  = pr(middlimit(1));
 [y,i]     = max(pdf);
 pmode(ov) = pr(i);
 

 pmatrix =[pmatrix; sumpdf];
 
end

inte = fix(background_prob/dels);

pmatrix = pmatrix(:, inte);

return