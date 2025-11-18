<?php
session_start();

// Determine which step to show
$step = $_GET['step'] ?? 'email';

// Validate step transitions
if ($step === 'otp' && !isset($_SESSION['forgot_email'])) {
    header('Location: forgot_password.php?step=email');
    exit;
}

if ($step === 'reset' && !isset($_SESSION['otp_verified'])) {
    header('Location: forgot_password.php?step=email');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/forgot_password.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="forgot-container">
        <!-- Step 1: Email Input -->
        <div id="emailStep" style="display: <?php echo $step === 'email' ? 'block' : 'none'; ?>;">
            <div class="forgot-header">
                <a href="sign_in.php" class="back-link">
                    <i class="ri-arrow-left-line"></i> Back to Sign In
                </a>
                <h2>Forgot Password?</h2>
                <p>Enter your email address and we'll send you an OTP to reset your password.</p>
            </div>

            <div id="emailError" class="message error"></div>
            <div id="emailSuccess" class="message success"></div>

            <form id="emailForm">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="ri-mail-line input-icon"></i>
                        <input type="email" id="email" class="form-input" placeholder="Enter your registered email" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Send OTP</button>
            </form>
        </div>

        <!-- Step 2: OTP Verification -->
        <div id="otpStep" style="display: <?php echo $step === 'otp' ? 'block' : 'none'; ?>;">
            <div class="forgot-header">
                <a href="forgot_password.php?step=email" class="back-link">
                    <i class="ri-arrow-left-line"></i> Back
                </a>
                <h2>Verify OTP</h2>
                <p>Enter the 6-digit code sent to<br><strong id="displayEmail"><?php echo htmlspecialchars($_SESSION['forgot_email'] ?? ''); ?></strong></p>
            </div>

            <div id="otpError" class="message error"></div>
            <div id="otpSuccess" class="message success"></div>

            <form id="otpForm">
                <div class="otp-inputs">
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                    <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                </div>
                <button type="submit" class="submit-btn">Verify OTP</button>
            </form>

            <div class="resend-container">
                <a href="#" class="resend-link" id="resendLink">Resend OTP</a>
                <div class="timer" id="timer"></div>
            </div>
        </div>

        <!-- Step 3: Reset Password -->
        <div id="resetStep" style="display: <?php echo $step === 'reset' ? 'block' : 'none'; ?>;">
            <div class="forgot-header">
                <h2>Reset Password</h2>
                <p>Enter your new password below</p>
            </div>

            <div id="resetError" class="message error"></div>
            <div id="resetSuccess" class="message success"></div>

            <form id="resetForm">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="input-wrapper">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" id="password" class="form-input" placeholder="Enter new password" required>
                        <i class="ri-eye-line password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="ri-lock-line input-icon"></i>
                        <input type="password" id="confirmPassword" class="form-input" placeholder="Confirm new password" required>
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

                <button type="submit" class="submit-btn">Reset Password</button>
            </form>
        </div>
    </div>

    <script>
        // ==================== STEP 1: EMAIL SUBMISSION ====================
        document.getElementById('emailForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                showMessage('emailError', 'Please enter your email address');
                return;
            }
            
            const btn = this.querySelector('.submit-btn');
            const originalText = btn.textContent;
            btn.textContent = 'Sending...';
            btn.disabled = true;
            
            try {
                const response = await fetch('handle_forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'send_otp', email: email })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('emailSuccess', result.message);
                    setTimeout(() => {
                        window.location.href = 'forgot_password.php?step=otp';
                    }, 1500);
                } else {
                    showMessage('emailError', result.message);
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('emailError', 'An error occurred. Please try again.');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });

        // ==================== STEP 2: OTP VERIFICATION ====================
        const otpInputs = document.querySelectorAll('.otp-input');
        
        if (otpInputs.length > 0) {
            // Auto-focus and navigation
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

            // Paste functionality
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

            // OTP Form submission
            document.getElementById('otpForm')?.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const otp = Array.from(otpInputs).map(input => input.value).join('');
                
                if (otp.length !== 6) {
                    showMessage('otpError', 'Please enter all 6 digits');
                    return;
                }
                
                const btn = this.querySelector('.submit-btn');
                const originalText = btn.textContent;
                btn.textContent = 'Verifying...';
                btn.disabled = true;
                
                try {
                    const response = await fetch('handle_forgot_password.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'verify_otp', otp: otp })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showMessage('otpSuccess', result.message);
                        setTimeout(() => {
                            window.location.href = 'forgot_password.php?step=reset';
                        }, 1500);
                    } else {
                        showMessage('otpError', result.message);
                        otpInputs.forEach(input => input.value = '');
                        otpInputs[0].focus();
                        btn.textContent = originalText;
                        btn.disabled = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showMessage('otpError', 'An error occurred. Please try again.');
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            });

            // Resend OTP
            let timerInterval;
            let timeLeft = 60;

            function startTimer() {
                const resendLink = document.getElementById('resendLink');
                const timerDisplay = document.getElementById('timer');
                
                if (!resendLink || !timerDisplay) return;
                
                resendLink.style.pointerEvents = 'none';
                resendLink.style.opacity = '0.5';
                
                timerInterval = setInterval(() => {
                    timeLeft--;
                    timerDisplay.textContent = `Resend OTP in ${timeLeft}s`;
                    
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        resendLink.style.pointerEvents = 'auto';
                        resendLink.style.opacity = '1';
                        timerDisplay.textContent = '';
                        timeLeft = 60;
                    }
                }, 1000);
            }

            if (document.getElementById('otpStep').style.display === 'block') {
                startTimer();
            }

            document.getElementById('resendLink')?.addEventListener('click', async (e) => {
                e.preventDefault();
                
                try {
                    const response = await fetch('handle_forgot_password.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'resend_otp' })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showMessage('otpSuccess', 'OTP has been resent to your email!');
                        otpInputs.forEach(input => input.value = '');
                        otpInputs[0].focus();
                        clearInterval(timerInterval);
                        timeLeft = 60;
                        startTimer();
                    } else {
                        showMessage('otpError', result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showMessage('otpError', 'Failed to resend OTP. Please try again.');
                }
            });
        }

        // ==================== STEP 3: RESET PASSWORD ====================
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');

        // Password visibility toggle
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.classList.toggle('ri-eye-line');
            this.classList.toggle('ri-eye-off-line');
        });

        document.getElementById('toggleConfirmPassword')?.addEventListener('click', function() {
            const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
            confirmPasswordInput.type = type;
            this.classList.toggle('ri-eye-line');
            this.classList.toggle('ri-eye-off-line');
        });

        // Password validation
        passwordInput?.addEventListener('input', function() {
            const password = this.value;
            
            document.getElementById('length')?.classList.toggle('valid', password.length >= 5);
            document.getElementById('number')?.classList.toggle('valid', /[0-9]/.test(password));
            document.getElementById('special')?.classList.toggle('valid', /[!@#$%^&*(),.?":{}|<>]/.test(password));
        });

        // Reset form submission
        document.getElementById('resetForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Validate password
            if (password.length < 5) {
                showMessage('resetError', 'Password must be at least 5 characters long');
                return;
            }
            
            if (!/[0-9]/.test(password)) {
                showMessage('resetError', 'Password must contain at least one number');
                return;
            }
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                showMessage('resetError', 'Password must contain at least one special character');
                return;
            }
            if (password !== confirmPassword) {
                showMessage('resetError', 'Passwords do not match');
                return;
            }
            
            const btn = this.querySelector('.submit-btn');
            const originalText = btn.textContent;
            btn.textContent = 'Resetting...';
            btn.disabled = true;
            
            try {
                const response = await fetch('handle_forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reset_password', password: password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('resetSuccess', result.message);
                    setTimeout(() => {
                        window.location.href = 'sign_in.php';
                    }, 2000);
                } else {
                    showMessage('resetError', result.message);
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('resetError', 'An error occurred. Please try again.');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });

        // Helper function to show messages
        function showMessage(elementId, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = message;
                element.style.display = 'block';
                setTimeout(() => {
                    element.style.display = 'none';
                }, 5000);
            }
        }
    </script>
</body>
</html>