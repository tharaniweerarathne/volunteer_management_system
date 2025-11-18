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
    
    
    public function checkEmailExists($email) {
        return $this->registrationData->emailExists($email);
    }
    
    // generating 6-digit OTP
    private function generateOTP() {
        return sprintf("%06d", mt_rand(0, 999999));
    }
    
    // sending OTP via email
    public function sendOTP($email, $name) {
        $otp = $this->generateOTP();
        
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_time'] = time();
        $_SESSION['otp_email'] = $email;
        
        
        $mail = new PHPMailer(true);
        
        try {
            
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infocontact256@gmail.com'; 
            $mail->Password   = 'ffvr keeu ztxj bwpa'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            
            $mail->setFrom('your-email@gmail.com', 'Volunteer Platform');
            $mail->addAddress($email, $name);
            
            
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
    
    // verify OTP
    public function verifyOTP($enteredOTP) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_time'])) {
            return ["success" => false, "message" => "OTP session expired"];
        }
        
        
        if (time() - $_SESSION['otp_time'] > 600) {
            return ["success" => false, "message" => "OTP has expired. Please request a new one."];
        }
        
        if ($_SESSION['otp'] === $enteredOTP) {
            return ["success" => true, "message" => "OTP verified successfully"];
        } else {
            return ["success" => false, "message" => "Invalid OTP. Please try again."];
        }
    }
    
    // user registration
    public function registerUser($name, $email, $password, $telephoneNo, $location, $gender, $skills = []) {
        
        if ($this->registrationData->emailExists($email)) {
            return ["success" => false, "message" => "This email is already registered. Please use a different email or sign in."];
        }
        
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // user creation
        $userId = $this->registrationData->createUser($name, $email, $hashedPassword, $telephoneNo, $location, $gender);
        
        if (!$userId) {
            return ["success" => false, "message" => "Registration failed. Please try again."];
        }
        
        // adding skills 
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