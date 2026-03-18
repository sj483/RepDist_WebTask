function ValidateReCaptcha() {
    return new Promise((resolve) => {
        grecaptcha.execute('6Lce3-0rAAAAAEXTQHBc-CcfNZaZFsVAnm-YKls5', {action: 'homepage'})
        .then(function(token) {
            // Send token to your server
            fetch('./VerifyHuman.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ recaptchaToken: token })
            })
            .then(response => response.json())
            .then(data => {
                if(data.score >= 0.5) {
                    // Proceed with actions on register submission 
                    resolve(true);

                } else {
                    console.error('reCAPTCHA verification failed', data['error-codes']);
                    alert('reCAPTCHA verification failed, please contact the researcher if you are indeed human (sj483@sussex.ac.uk)'); 
                   resolve(false);
                }
            })
            .catch(err => {
                console.error('Error receiving reCAPTCHA status', err);
                resolve(false);
              })
        })
        .catch(function(err) {
            console.error('Error executing reCAPTCHA', err);
            resolve(false);
        });
    });
}