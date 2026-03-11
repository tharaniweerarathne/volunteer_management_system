<?php
session_start();

if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'Organizer') {
    header('Location: sign_in.php');
    exit;
}

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/ProfileLogic.php";

$profileLogic = new ProfileLogic($conn);
$userId = $_SESSION['userId'];

// fetch current profile data
$profileResult = $profileLogic->getOrganizerProfile($userId);
$profile = $profileResult['success'] ? $profileResult['data'] : null;

if (!$profile) {
    die("Error loading profile");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Organizer</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/edit_pro1.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <a href="organizer_dashboard.php" class="back-link">
            <i class="ri-arrow-left-line"></i> Back to Dashboard
        </a>

        <!-- profile edit form -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Edit Profile - Organizer</h2>
                <p>Update your personal information</p>
            </div>
            <div class="card-body">
                <div id="profileMessage" class="message"></div>
                
                <form id="profileForm">
                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Full Name
                        </label>
                        <div class="input-group">
                            <i class="ri-user-line input-icon"></i>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Email Address
                        </label>
                        <div class="input-group">
                            <i class="ri-mail-line input-icon"></i>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Telephone Number
                        </label>
                        <div class="input-group">
                            <i class="ri-phone-line input-icon"></i>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['telephoneNo']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Gender
                        </label>
                        <select name="gender" class="form-select" required>
                            <option value="Male" <?php echo $profile['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $profile['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $profile['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            <option value="Prefer not to say" <?php echo $profile['gender'] === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="required">*</span> Location
                        </label>
                        <div class="input-group">
                            <i class="ri-map-pin-line input-icon"></i>
                            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($profile['location']); ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- password reset section -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Change Password</h2>
                <p>Reset your account password using OTP</p>
            </div>
            <div class="card-body">
                <div id="passwordMessage" class="message"></div>
                <p style="margin-bottom: 1.5rem; color: #6c757d;">
                    Click the button below to receive an OTP via email to reset your password.
                </p>
                <button type="button" id="sendOtpBtn" class="btn btn-secondary">Send OTP to Email</button>
            </div>
        </div>
    </div>

    <!-- OTP modal -->
    <div id="otpModal" class="otp-modal">
        <div class="otp-modal-content">
            <h3 style="text-align: center; margin-bottom: 1rem;">Enter OTP</h3>
            <p style="text-align: center; color: #6c757d; margin-bottom: 1.5rem;">
                Enter the 6-digit code sent to your email
            </p>
            <div id="otpMessage" class="message"></div>
            
            <form id="otpForm">
                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" pattern="\d" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="\d" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="\d" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="\d" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="\d" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="\d" inputmode="numeric" required>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-bottom: 1rem;">Verify OTP</button>
                <button type="button" id="closeOtpModal" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <!-- password reset modal -->
    <div id="passwordModal" class="otp-modal">
        <div class="otp-modal-content">
            <h3 style="text-align: center; margin-bottom: 1rem;">Reset Password</h3>
            <div id="resetMessage" class="message"></div>
            
            <form id="resetPasswordForm">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="input-group">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" id="newPassword" class="form-control" placeholder="Enter new password" required>
                        <i class="ri-eye-line password-toggle" id="toggleNewPassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm new password" required>
                        <i class="ri-eye-line password-toggle" id="toggleConfirmPassword"></i>
                    </div>
                </div>

                <div class="password-requirements">
                    <p>Password must contain:</p>
                    <ul>
                        <li id="length">At least 5 characters</li>
                        <li id="number">At least one number</li>
                        <li id="special">At least one special character</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-bottom: 1rem;">Reset Password</button>
                <button type="button" id="closePasswordModal" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <script>
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

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded - initializing event listeners');
        
        // ============== PROFILE FORM SUBMISSION ==============
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', async function(e) {
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
        }
        
        // ============== SEND OTP BUTTON ==============
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        if (sendOtpBtn) {
            sendOtpBtn.addEventListener('click', async function() {
                console.log('Send OTP button clicked');
                
                const btn = this;
                const originalText = btn.textContent;
                btn.textContent = 'Sending...';
                btn.disabled = true;
                
                try {
                    const response = await fetch('handle_edit_profile.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'send_organizer_otp' })
                    });
                    
                    const result = await response.json();
                    console.log('Send OTP result:', result);
                    
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
        }
        
        // ============== OTP INPUT HANDLING ==============
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
        
        // paste functionality for OTP
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

        // ============== OTP FORM SUBMISSION ==============
        const otpForm = document.getElementById('otpForm');
        if (otpForm) {
            otpForm.addEventListener('submit', async function(e) {
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
                        body: JSON.stringify({ action: 'verify_organizer_otp', otp: otp })
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
        }
        
        // ============== PASSWORD TOGGLE ==============
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

        // ============== PASSWORD VALIDATION ==============
        document.getElementById('newPassword')?.addEventListener('input', function() {
            const password = this.value;
            
            document.getElementById('length')?.classList.toggle('valid', password.length >= 5);
            document.getElementById('number')?.classList.toggle('valid', /[0-9]/.test(password));
            document.getElementById('special')?.classList.toggle('valid', /[!@#$%^&*(),.?":{}|<>]/.test(password));
        });

        // ============== RESET PASSWORD FORM SUBMISSION ==============
        const resetPasswordForm = document.getElementById('resetPasswordForm');
        if (resetPasswordForm) {
            resetPasswordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const password = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                // password validation
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
                        body: JSON.stringify({ action: 'reset_organizer_password', password: password })
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
        }
        
        // ============== CLOSE MODAL BUTTONS ==============
        document.getElementById('closeOtpModal')?.addEventListener('click', function() {
            document.getElementById('otpModal').style.display = 'none';
            otpInputs.forEach(input => input.value = '');
        });

        document.getElementById('closePasswordModal')?.addEventListener('click', function() {
            document.getElementById('passwordModal').style.display = 'none';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        });
    });
    </script>
</body>
</html>