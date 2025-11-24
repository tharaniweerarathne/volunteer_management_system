document.addEventListener("DOMContentLoaded", function() {

    // ===== EMAIL VALIDATION =====
    const emailInput = document.getElementById('email');
    const emailHints = document.getElementById('emailHints');

    if (emailInput && emailHints) {
        emailInput.addEventListener('input', function() {
            emailHints.innerHTML = '';
            const value = this.value.trim();
            const messages = [];

            if (!value.includes('@')) {
                messages.push('Please add @');
            } else {
                const parts = value.split('@');
                if (parts.length === 2) {
                    if (parts[1].length === 0) messages.push('Please add domain name');
                    else if (!parts[1].includes('.')) messages.push('Please add . (dot)');
                    else if (parts[1].split('.').pop().length < 2) messages.push('Add domain extension (com, net...)');
                }
            }

            messages.forEach(msg => {
                const span = document.createElement('span');
                span.className = 'email-hint';
                span.textContent = msg;
                emailHints.appendChild(span);
            });
        });
    }

    // ===== PHONE NUMBER VALIDATION =====
    const phoneInput = document.getElementById('phone');
    const mobileHints = document.getElementById('mobileHints');

    if (phoneInput && mobileHints) {
        phoneInput.addEventListener('input', function() {
            const value = this.value.trim();
            mobileHints.innerHTML = '';
            const messages = [];

            if (/[^0-9]/.test(value)) {
                messages.push('Only numbers allowed');
                this.value = value.replace(/[^0-9]/g, '');
            }

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
    }

    // ===== PASSWORD VALIDATION =====
    const passwordInput = document.getElementById('addPassword');
    const passwordHints = document.getElementById('passwordHints');

    if (passwordInput && passwordHints) {
        passwordInput.addEventListener('input', function() {
            const value = this.value.trim();
            passwordHints.innerHTML = '';
            const messages = [];

            if (value.length < 5) messages.push('Minimum 5 characters required');
            if (!/[a-zA-Z]/.test(value)) messages.push('Must include at least one letter');
            if (!/[0-9]/.test(value)) messages.push('Must include at least one number');
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(value)) messages.push('Must include at least one symbol');

            messages.forEach(msg => {
                const span = document.createElement('span');
                span.className = 'email-hint';
                span.textContent = msg;
                passwordHints.appendChild(span);
            });
        });
    }

});
