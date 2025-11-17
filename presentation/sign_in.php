<?php
session_start();
$emailValue = $_SESSION['old_email'] ?? '';
$passwordValue = $_SESSION['old_password'] ?? '';
$loginError = $_SESSION['login_error'] ?? '';

unset($_SESSION['old_email'], $_SESSION['old_password'], $_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Login</title>
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link rel="stylesheet" href="../assets/css/sign_in.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- left Section - login form -->
            <div class="col-lg-7 left-section">
                <div class="form-container">
                    <h2>Sign In</h2>
                    <p class="form-subtitle">Welcome back! Please sign in to access your volunteer account and continue making a difference in the community.</p>

                     <form id="volunteerForm" method="POST" action="login_process.php">
                        
                        <div class="mb-3">
                            <label class="form-label">
                               <span class="required">*</span> Email Address
                            </label>
                            <div class="input-group">
                                <i class="ri-mail-line input-icon"></i>
                                <input type="email" id="email" name="email" class="form-control with-icon" placeholder="Enter your email" required>
                            </div>
                            <div class="email-hints" id="emailHints"></div>
                            </div>

                        
                        <div class="mb-3">
                            <label class="form-label">
                                <span class="required">*</span> Password
                            </label>
                            <div class="input-group">
                                <i class="ri-lock-line input-icon"></i>
                                <input type="password" name="password" class="form-control with-icon" id="passwordInput" placeholder="Enter Your Password" required>
                                <i class="ri-eye-line password-toggle" id="togglePassword"></i>
                            </div>
                        </div>

                        <!-- Show login error -->
<?php if($loginError): ?>
    <div style="
        color: #721c24; 
        background-color: #f8d7da; 
        border: 1px solid #f5c6cb; 
        padding: 10px 15px; 
        border-radius: 5px; 
        margin: 10px 0;
        font-family: Arial, sans-serif;
        font-size: 14px;
    ">
        <?php echo $loginError; ?>
    </div>
<?php endif; ?>


                        <div class="forgot-password">
                            <a href="#">Forgot Password?</a>
                        </div>

                        
                        <button type="submit" class="submit-btn">Sign In</button>
                    </form>
                </div>
            </div>

            <!-- right section -->
            <div class="col-lg-5 right-section">
                <div class="right-content">
                    <h1>Welcome Back!</h1>
                    <p>Don't have an account?</p>
                    <button class="sign-up-btn" onclick="window.location.href='sign_up.html'">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/form-validation.js"></script>
    <script>
        
    </script>
</body>
</html>