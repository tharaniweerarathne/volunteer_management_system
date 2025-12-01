<?php
// ProfileLogic.php --> business_logic folder

require_once __DIR__ . "/../data_access/ProfileData.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ProfileLogic {
    private $profileData;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->profileData = new ProfileData($conn);
    }
    
    // ==================== VOLUNTEER PROFILE ====================
    
    // Get volunteer profile by ID
    public function getVolunteerProfile($userId) {
        $volunteer = $this->profileData->getVolunteerById($userId);
        if (!$volunteer) {
            return ["success" => false, "message" => "Volunteer not found"];
        }
        return ["success" => true, "data" => $volunteer];
    }
    
    // Update volunteer profile (without password)
    public function updateVolunteerProfile($userId, $name, $email, $telephoneNo, $location, $gender, $skills = []) {
        // Check if email exists for another user
        if ($this->profileData->emailExistsForOtherUser($email, $userId)) {
            return ["success" => false, "message" => "This email is already used by another user."];
        }
        
        // Update basic info
        if (!$this->profileData->updateVolunteerBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender)) {
            return ["success" => false, "message" => "Failed to update profile."];
        }
        
        // Update skills if provided
        // Delete existing skills first
        $this->profileData->deleteVolunteerSkills($userId);
        
        // Add new skills if any
        if (!empty($skills)) {
            $skillIds = $this->profileData->getSkillIdsByNames($skills);
            if (!empty($skillIds)) {
                $this->profileData->addVolunteerSkills($userId, $skillIds);
            }
        }
        
        return ["success" => true, "message" => "Profile updated successfully!"];
    }
    
    // ==================== Password Reset via OTP (VOLUNTEER ONLY) ====================
    
    // Generate 6-digit OTP
    private function generateOTP() {
        return sprintf("%06d", mt_rand(0, 999999));
    }
    
    // Send OTP for password reset
    public function sendPasswordResetOTP($userId) {
        // Get user email
        $volunteer = $this->profileData->getVolunteerById($userId);
        if (!$volunteer) {
            return ["success" => false, "message" => "User not found"];
        }
        
        $email = $volunteer['email'];
        $name = $volunteer['name'];
        $otp = $this->generateOTP();
        
        // Store OTP in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['password_reset_otp'] = $otp;
        $_SESSION['password_reset_otp_time'] = time();
        $_SESSION['password_reset_user_id'] = $userId;
        
        // Send OTP via email
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'infocontact256@gmail.com'; 
            $mail->Password   = 'ffvr keeu ztxj bwpa'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            $mail->setFrom('infocontact256@gmail.com', 'Unity Volunteers Trust');
            $mail->addAddress($email, $name);
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Hello $name,</h2>
                    <p>You requested to reset your password.</p>
                    <p>Your OTP verification code is:</p>
                    <h1 style='color: #4F46E5; letter-spacing: 5px;'>$otp</h1>
                    <p>This code will expire in 10 minutes.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </body>
                </html>
            ";
            
            $mail->send();
            return ["success" => true, "message" => "OTP sent to your email successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to send OTP. Error: {$mail->ErrorInfo}"];
        }
    }
    
    // Verify OTP for password reset
    public function verifyPasswordResetOTP($enteredOTP) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['password_reset_otp']) || !isset($_SESSION['password_reset_otp_time'])) {
            return ["success" => false, "message" => "OTP session expired. Please request a new OTP."];
        }
        
        // Check if OTP is expired (10 minutes)
        if (time() - $_SESSION['password_reset_otp_time'] > 600) {
            return ["success" => false, "message" => "OTP has expired. Please request a new one."];
        }
        
        if ($_SESSION['password_reset_otp'] === $enteredOTP) {
            $_SESSION['otp_verified'] = true;
            return ["success" => true, "message" => "OTP verified successfully"];
        } else {
            return ["success" => false, "message" => "Invalid OTP. Please try again."];
        }
    }
    
    // Reset password after OTP verification (VOLUNTEER)
    public function resetPassword($newPassword) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
            return ["success" => false, "message" => "Please verify OTP first"];
        }
        
        if (!isset($_SESSION['password_reset_user_id'])) {
            return ["success" => false, "message" => "Session expired. Please try again."];
        }
        
        $userId = $_SESSION['password_reset_user_id'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        if ($this->profileData->updateUserPassword($userId, $hashedPassword)) {
            // Clear session variables
            unset($_SESSION['password_reset_otp']);
            unset($_SESSION['password_reset_otp_time']);
            unset($_SESSION['password_reset_user_id']);
            unset($_SESSION['otp_verified']);
            
            return ["success" => true, "message" => "Password reset successfully!"];
        } else {
            return ["success" => false, "message" => "Failed to reset password. Please try again."];
        }
    }
    
    // ==================== COORDINATOR PROFILE ====================
    
    // Get coordinator profile by ID
    public function getCoordinatorProfile($userId) {
        $coordinator = $this->profileData->getCoordinatorById($userId);
        if (!$coordinator) {
            return ["success" => false, "message" => "Coordinator not found"];
        }
        return ["success" => true, "data" => $coordinator];
    }
    
    // Update coordinator profile (NO password change allowed)
    public function updateCoordinatorProfile($userId, $name, $email, $telephoneNo, $location, $gender) {
        // Check if email exists for another user
        if ($this->profileData->emailExistsForOtherUser($email, $userId)) {
            return ["success" => false, "message" => "This email is already used by another user."];
        }
        
        // Update basic info
        if (!$this->profileData->updateUserBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender)) {
            return ["success" => false, "message" => "Failed to update profile."];
        }
        
        return ["success" => true, "message" => "Profile updated successfully!"];
    }
    
    // ==================== ADMIN PROFILE ====================
    
    // Get admin profile by ID
    public function getAdminProfile($userId) {
        $admin = $this->profileData->getAdminById($userId);
        if (!$admin) {
            return ["success" => false, "message" => "Admin not found"];
        }
        return ["success" => true, "data" => $admin];
    }
    
    // Update admin profile (without password)
    public function updateAdminProfile($userId, $name, $email, $telephoneNo, $location, $gender) {
        // Check if email exists for another user
        if ($this->profileData->emailExistsForOtherUser($email, $userId)) {
            return ["success" => false, "message" => "This email is already used by another user."];
        }
        
        // Update basic info
        if (!$this->profileData->updateUserBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender)) {
            return ["success" => false, "message" => "Failed to update profile."];
        }
        
        return ["success" => true, "message" => "Profile updated successfully!"];
    }
    
    // Update admin password (NO OTP required)
    public function updateAdminPassword($userId, $currentPassword, $newPassword) {
        // Verify current password
        $admin = $this->profileData->getAdminById($userId);
        if (!$admin) {
            return ["success" => false, "message" => "Admin not found"];
        }
        
        if (!password_verify($currentPassword, $admin['password'])) {
            return ["success" => false, "message" => "Current password is incorrect"];
        }
        
        // Update to new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        if ($this->profileData->updateUserPassword($userId, $hashedPassword)) {
            return ["success" => true, "message" => "Password changed successfully!"];
        } else {
            return ["success" => false, "message" => "Failed to change password. Please try again."];
        }
    }
}
?>