<?php
session_start();

// Check if user has registration data
if (!isset($_SESSION['temp_registration'])) {
    header('Location: sign_up.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/otp_verification.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="otp-container">
        <div class="otp-header">
            <h2>OTP Verification</h2>
            <p>Enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['otp_email'] ?? 'your email'); ?></p>
        </div>

        <div id="errorMessage" style="display: none; color: red; text-align: center; margin-bottom: 15px;"></div>
        <div id="successMessage" style="display: none; color: green; text-align: center; margin-bottom: 15px;"></div>

        <form id="otpForm">
            <div class="otp-inputs">
                <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
                <input type="number" class="otp-input" maxlength="1" pattern="\d" required>
            </div>

            <button type="submit" class="verify-btn">Verify OTP</button>
        </form>

        <div class="resend-container">
            <a href="#" class="resend-link" id="resendLink">Resend OTP</a>
            <div class="timer" id="timer"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const inputs = document.querySelectorAll('.otp-input');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        
        // Auto-focus and navigation
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                if (e.target.value.length > 1) {
                    e.target.value = e.target.value.slice(0, 1);
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        // Paste functionality
        inputs[0].addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').slice(0, 6);
            
            pastedData.split('').forEach((char, index) => {
                if (inputs[index] && /^\d$/.test(char)) {
                    inputs[index].value = char;
                }
            });
            
            if (pastedData.length === 6) {
                inputs[5].focus();
            }
        });

        // Form submission
        document.getElementById('otpForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const otp = Array.from(inputs).map(input => input.value).join('');
            
            if (otp.length !== 6) {
                showError('Please enter all 6 digits');
                return;
            }
            
            try {
                const response = await fetch('verify_otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ otp: otp })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess(result.message);
                    setTimeout(() => {
                        window.location.href = result.redirect || 'sign_in.php';
                    }, 1500);
                } else {
                    showError(result.message);
                    inputs.forEach(input => input.value = '');
                    inputs[0].focus();
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
            }
        });

        // Resend OTP functionality
        let timerInterval;
        let timeLeft = 60;

        function startTimer() {
            const resendLink = document.getElementById('resendLink');
            const timerDisplay = document.getElementById('timer');
            
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

        startTimer();

        document.getElementById('resendLink').addEventListener('click', async (e) => {
            e.preventDefault();
            
            try {
                const response = await fetch('resend_otp.php', {
                    method: 'POST'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('OTP has been resent to your email!');
                    inputs.forEach(input => input.value = '');
                    inputs[0].focus();
                    
                    clearInterval(timerInterval);
                    timeLeft = 60;
                    startTimer();
                } else {
                    showError(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Failed to resend OTP. Please try again.');
            }
        });

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
            setTimeout(() => {
                errorMessage.style.display = 'none';
            }, 5000);
        }

        function showSuccess(message) {
            successMessage.textContent = message;
            successMessage.style.display = 'block';
            errorMessage.style.display = 'none';
        }
    </script>
</body>
</html>