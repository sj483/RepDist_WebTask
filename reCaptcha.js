
grecaptcha.ready(function() {
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
            if(data.success) {
                console.log('User verified as human!');
                // Proceed with form submission or whatever you need
            } else {
                console.error('reCAPTCHA verification failed', data['error-codes']);
                alert('Please complete the captcha challenge.');
            }
        })
        .catch(err => console.error('Error verifying reCAPTCHA:', err));
    })
    .catch(function(err) {
        console.error('reCAPTCHA execution failed:', err);
    });
       
   
});

