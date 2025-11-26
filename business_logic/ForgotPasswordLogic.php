<?php
require_once __DIR__ . "/../data_access/ForgotPasswordData.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ForgotPasswordLogic {
    private $forgotPasswordData;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->forgotPasswordData = new ForgotPasswordData($conn);
    }
    
    // generating 6-digit OTP
    private function generateOTP() {
        return sprintf("%06d", mt_rand(0, 999999));
    }
    
    // sending OTP via email
    public function sendPasswordResetOTP($email) {
        // check if email exists
        $user = $this->forgotPasswordData->getUserByEmail($email);
        
        if (!$user) {
            return ["success" => false, "message" => "Email address not found in our system."];
        }
        
        $otp = $this->generateOTP();
        
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['forgot_otp'] = $otp;
        $_SESSION['forgot_otp_time'] = time();
        $_SESSION['forgot_email'] = $email;
        
        
        $mail = new PHPMailer(true);
        
        try {
            
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infocontact256@gmail.com'; 
            $mail->Password   = 'ffvr keeu ztxj bwpa'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            
            $mail->setFrom('your-email@gmail.com', 'Unity Volunteers Trust');
            $mail->addAddress($email, $user['name']);
            
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Hello {$user['name']},</h2>
                    <p>You requested to reset your password.</p>
                    <p>Your OTP verification code is:</p>
                    <h1 style='color: #4F46E5; letter-spacing: 5px;'>$otp</h1>
                    <p>This code will expire in 10 minutes.</p>
                    <p>If you didn't request this, please ignore this email and your password will remain unchanged.</p>
                </body>
                </html>
            ";
            
            $mail->send();
            return ["success" => true, "message" => "OTP sent to your email successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to send OTP. Error: {$mail->ErrorInfo}"];
        }
    }
    
    // verify OTP
    public function verifyPasswordResetOTP($enteredOTP) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['forgot_otp']) || !isset($_SESSION['forgot_otp_time'])) {
            return ["success" => false, "message" => "OTP session expired. Please request a new OTP."];
        }
        
        
        if (time() - $_SESSION['forgot_otp_time'] > 600) {
            return ["success" => false, "message" => "OTP has expired. Please request a new one."];
        }
        
        if ($_SESSION['forgot_otp'] === $enteredOTP) {
            return ["success" => true, "message" => "OTP verified successfully"];
        } else {
            return ["success" => false, "message" => "Invalid OTP. Please try again."];
        }
    }
    
    // reset password
    public function resetPassword($newPassword) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['forgot_email'])) {
            return ["success" => false, "message" => "Session expired. Please start the process again."];
        }
        
        $email = $_SESSION['forgot_email'];
        
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // update password in database
        $result = $this->forgotPasswordData->updatePassword($email, $hashedPassword);
        
        if ($result) {
            
            unset($_SESSION['forgot_otp']);
            unset($_SESSION['forgot_otp_time']);
            unset($_SESSION['forgot_email']);
            
            return ["success" => true, "message" => "Password reset successfully!"];
        } else {
            return ["success" => false, "message" => "Failed to reset password. Please try again."];
        }
    }
}
?>