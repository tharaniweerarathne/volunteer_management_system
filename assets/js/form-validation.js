  // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('ri-eye-line');
            this.classList.toggle('ri-eye-off-line');
        });

        

        // email validations
 const emailInput = document.getElementById('email');
        const emailHints = document.getElementById('emailHints');

        emailInput.addEventListener('input', function() {
            const value = this.value.trim();
            emailHints.innerHTML = '';

            if (value.length > 0) {
                const messages = [];
                
                // checking for @ symbol
                if (!value.includes('@')) {
                    messages.push('Please add @');
                } else {
                    // checking for domain after @
                    const parts = value.split('@');
                    if (parts.length === 2 && parts[1].length > 0) {
                        // checking for dot
                        if (!parts[1].includes('.')) {
                            messages.push('Please add . (dot)');
                        } else {
                            // checking for domain extension
                            const afterDot = parts[1].split('.').pop();
                            if (afterDot.length < 2) {
                                messages.push('Please add domain extension (com, net, org, etc.)');
                            }
                        }
                    } else if (parts.length === 2 && parts[1].length === 0) {
                        messages.push('Please add domain name');
                    }
                }

                if (messages.length > 0) {
                    messages.forEach(msg => {
                        const span = document.createElement('span');
                        span.className = 'email-hint';
                        span.textContent = msg;
                        emailHints.appendChild(span);
                    });
                }
            }
        });
        


//telephone number validation
const phoneInput = document.getElementById('phone');
const mobileHints = document.getElementById('mobileHints');

phoneInput.addEventListener('input', function() {
    const value = this.value.trim();
    mobileHints.innerHTML = '';

    const messages = [];

    // checking if only numbers
    if (/[^0-9]/.test(value)) {
        messages.push('Only numbers allowed');
        // remove non-numeric characters immediately
        this.value = value.replace(/[^0-9]/g, '');
    }

    // checking length
    if (value.length > 10) {
        messages.push('Maximum 10 digits allowed');
        this.value = value.slice(0, 10); 
    }

    messages.forEach(msg => {
        const span = document.createElement('span');
        span.className = 'email-hint';
        span.textContent = msg;
        mobileHints.appendChild(span);
    });
});



//password
const passwordHints = document.getElementById('passwordHints');

passwordInput.addEventListener('input', function() {
    const value = this.value.trim();
    passwordHints.innerHTML = '';
    const messages = [];

    if (value.length < 5) {
        messages.push('Minimum 5 characters required');
    }
    if (!/[a-zA-Z]/.test(value)) {
        messages.push('Must include at least one letter');
    }
    if (!/[0-9]/.test(value)) {
        messages.push('Must include at least one number');
    }
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(value)) {
        messages.push('Must include at least one symbol');
    }

    messages.forEach(msg => {
        const span = document.createElement('span');
        span.className = 'email-hint';
        span.textContent = msg;
        passwordHints.appendChild(span);
    });
});

