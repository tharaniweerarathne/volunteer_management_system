// edit_profile.js --> assets/js folder

// Profile Form Submission
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        action: 'update_profile',
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        gender: formData.get('gender'),
        location: formData.get('location'),
        skills: formData.getAll('skills[]')
    };
    
    const btn = this.querySelector('.btn-primary');
    const originalText = btn.textContent;
    btn.textContent = 'Updating...';
    btn.disabled = true;
    
    try {
        const response = await fetch('handle_edit_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('profileMessage', result.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showMessage('profileMessage', result.message, 'error');
            btn.textContent = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('profileMessage', 'An error occurred. Please try again.', 'error');
        btn.textContent = originalText;
        btn.disabled = false;
    }
});

// Send OTP Button
document.getElementById('sendOtpBtn').addEventListener('click', async function() {
    const btn = this;
    const originalText = btn.textContent;
    btn.textContent = 'Sending...';
    btn.disabled = true;
    
    try {
        const response = await fetch('handle_edit_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_otp' })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('passwordMessage', result.message, 'success');
            setTimeout(() => {
                document.getElementById('otpModal').style.display = 'flex';
            }, 1000);
        } else {
            showMessage('passwordMessage', result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('passwordMessage', 'An error occurred. Please try again.', 'error');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});

// OTP Input Handling
const otpInputs = document.querySelectorAll('.otp-input');

otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        if (e.target.value.length === 1 && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
        if (e.target.value.length > 1) {
            e.target.value = e.target.value.slice(0, 1);
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
});

// Paste functionality for OTP
otpInputs[0].addEventListener('paste', (e) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData('text').slice(0, 6);
    pastedData.split('').forEach((char, index) => {
        if (otpInputs[index] && /^\d$/.test(char)) {
            otpInputs[index].value = char;
        }
    });
    if (pastedData.length === 6) otpInputs[5].focus();
});

// OTP Form Submission
document.getElementById('otpForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const otp = Array.from(otpInputs).map(input => input.value).join('');
    
    if (otp.length !== 6) {
        showMessage('otpMessage', 'Please enter all 6 digits', 'error');
        return;
    }
    
    const btn = this.querySelector('.btn-primary');
    const originalText = btn.textContent;
    btn.textContent = 'Verifying...';
    btn.disabled = true;
    
    try {
        const response = await fetch('handle_edit_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify_otp', otp: otp })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('otpMessage', result.message, 'success');
            setTimeout(() => {
                document.getElementById('otpModal').style.display = 'none';
                document.getElementById('passwordModal').style.display = 'flex';
                otpInputs.forEach(input => input.value = '');
            }, 1000);
        } else {
            showMessage('otpMessage', result.message, 'error');
            otpInputs.forEach(input => input.value = '');
            otpInputs[0].focus();
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('otpMessage', 'An error occurred. Please try again.', 'error');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});

// Password Toggle
document.getElementById('toggleNewPassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('newPassword');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    this.classList.toggle('ri-eye-line');
    this.classList.toggle('ri-eye-off-line');
});

document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('confirmPassword');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    this.classList.toggle('ri-eye-line');
    this.classList.toggle('ri-eye-off-line');
});

// Password Validation
document.getElementById('newPassword')?.addEventListener('input', function() {
    const password = this.value;
    
    document.getElementById('length')?.classList.toggle('valid', password.length >= 5);
    document.getElementById('number')?.classList.toggle('valid', /[0-9]/.test(password));
    document.getElementById('special')?.classList.toggle('valid', /[!@#$%^&*(),.?":{}|<>]/.test(password));
});

// Reset Password Form Submission
document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validate password
    if (password.length < 5) {
        showMessage('resetMessage', 'Password must be at least 5 characters long', 'error');
        return;
    }
    
    if (!/[0-9]/.test(password)) {
        showMessage('resetMessage', 'Password must contain at least one number', 'error');
        return;
    }
    
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        showMessage('resetMessage', 'Password must contain at least one special character', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        showMessage('resetMessage', 'Passwords do not match', 'error');
        return;
    }
    
    const btn = this.querySelector('.btn-primary');
    const originalText = btn.textContent;
    btn.textContent = 'Resetting...';
    btn.disabled = true;
    
    try {
        const response = await fetch('handle_edit_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reset_password', password: password })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('resetMessage', result.message, 'success');
            setTimeout(() => {
                document.getElementById('passwordModal').style.display = 'none';
                showMessage('passwordMessage', 'Password changed successfully!', 'success');
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
            }, 1500);
        } else {
            showMessage('resetMessage', result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('resetMessage', 'An error occurred. Please try again.', 'error');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});

// Close Modal Buttons
document.getElementById('closeOtpModal').addEventListener('click', function() {
    document.getElementById('otpModal').style.display = 'none';
    otpInputs.forEach(input => input.value = '');
});

document.getElementById('closePasswordModal').addEventListener('click', function() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
});

// Helper function to show messages
function showMessage(elementId, message, type) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.className = `message ${type}`;
        element.style.display = 'block';
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }
}