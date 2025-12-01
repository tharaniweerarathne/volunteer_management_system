// profile form submission
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        action: 'update_profile', 
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        gender: formData.get('gender'),
        location: formData.get('location')
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

// password toggle functions
document.getElementById('toggleCurrentPassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('currentPassword');
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    this.classList.toggle('ri-eye-line');
    this.classList.toggle('ri-eye-off-line');
});

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

// password validation
document.getElementById('newPassword')?.addEventListener('input', function() {
    const password = this.value;
    
    document.getElementById('length')?.classList.toggle('valid', password.length >= 5);
    document.getElementById('number')?.classList.toggle('valid', /[0-9]/.test(password));
    document.getElementById('special')?.classList.toggle('valid', /[!@#$%^&*(),.?":{}|<>]/.test(password));
});

// password change form submission
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // password validation
    if (newPassword.length < 5) {
        showMessage('passwordMessage', 'Password must be at least 5 characters long', 'error');
        return;
    }
    
    if (!/[0-9]/.test(newPassword)) {
        showMessage('passwordMessage', 'Password must contain at least one number', 'error');
        return;
    }
    
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(newPassword)) {
        showMessage('passwordMessage', 'Password must contain at least one special character', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showMessage('passwordMessage', 'New passwords do not match', 'error');
        return;
    }
    
    if (currentPassword === newPassword) {
        showMessage('passwordMessage', 'New password must be different from current password', 'error');
        return;
    }
    
    const btn = this.querySelector('.btn-primary');
    const originalText = btn.textContent;
    btn.textContent = 'Changing...';
    btn.disabled = true;
    
    try {
        const response = await fetch('handle_edit_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: 'change_password', 
                currentPassword: currentPassword,
                newPassword: newPassword
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('passwordMessage', result.message, 'success');
            // clear form
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
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

// helper function to show messages
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