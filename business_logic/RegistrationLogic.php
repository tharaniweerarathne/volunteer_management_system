<?php
require_once __DIR__ . "/../data_access/RegistrationData.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class RegistrationLogic {
    private $registrationData;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->registrationData = new RegistrationData($conn);
    }
    
    // ADD THIS NEW METHOD
    public function checkEmailExists($email) {
        return $this->registrationData->emailExists($email);
    }
    
    // Generate 6-digit OTP
    private function generateOTP() {
        return sprintf("%06d", mt_rand(0, 999999));
    }
    
    // Send OTP via email
    public function sendOTP($email, $name) {
        $otp = $this->generateOTP();
        
        // Store OTP in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_time'] = time();
        $_SESSION['otp_email'] = $email;
        
        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Change to your SMTP host
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infocontact256@gmail.com'; // Change to your email
            $mail->Password   = 'ffvr keeu ztxj bwpa'; // Change to your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Recipients
            $mail->setFrom('your-email@gmail.com', 'Volunteer Platform');
            $mail->addAddress($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Verification Code';
            $mail->Body    = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Hello $name,</h2>
                    <p>Thank you for registering with our Volunteer Platform!</p>
                    <p>Your OTP verification code is:</p>
                    <h1 style='color: #4F46E5; letter-spacing: 5px;'>$otp</h1>
                    <p>This code will expire in 10 minutes.</p>
                    <p>If you didn't request this code, please ignore this email.</p>
                </body>
                </html>
            ";
            
            $mail->send();
            return ["success" => true, "message" => "OTP sent successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to send OTP. Error: {$mail->ErrorInfo}"];
        }
    }
    
    // Verify OTP
    public function verifyOTP($enteredOTP) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_time'])) {
            return ["success" => false, "message" => "OTP session expired"];
        }
        
        // Check if OTP is expired (10 minutes)
        if (time() - $_SESSION['otp_time'] > 600) {
            return ["success" => false, "message" => "OTP has expired. Please request a new one."];
        }
        
        if ($_SESSION['otp'] === $enteredOTP) {
            return ["success" => true, "message" => "OTP verified successfully"];
        } else {
            return ["success" => false, "message" => "Invalid OTP. Please try again."];
        }
    }
    
    // Register user
    public function registerUser($name, $email, $password, $telephoneNo, $location, $gender, $skills = []) {
        // Double-check if email already exists (safety measure)
        if ($this->registrationData->emailExists($email)) {
            return ["success" => false, "message" => "This email is already registered. Please use a different email or sign in."];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user
        $userId = $this->registrationData->createUser($name, $email, $hashedPassword, $telephoneNo, $location, $gender);
        
        if (!$userId) {
            return ["success" => false, "message" => "Registration failed. Please try again."];
        }
        
        // Add skills if provided
        if (!empty($skills)) {
            $skillIds = $this->registrationData->getSkillIdsByNames($skills);
            if (!empty($skillIds)) {
                $this->registrationData->addVolunteerSkills($userId, $skillIds);
            }
        }
        
        return ["success" => true, "message" => "Registration successful!", "userId" => $userId];
    }
}
?>