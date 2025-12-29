<?php


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
    
    // ==================== volunteer profiles ====================
    
    // get volunteer profile by ID
    public function getVolunteerProfile($userId) {
        $volunteer = $this->profileData->getVolunteerById($userId);
        if (!$volunteer) {
            return ["success" => false, "message" => "Volunteer not found"];
        }
        return ["success" => true, "data" => $volunteer];
    }
    
    // update volunteer profile (without password)
    public function updateVolunteerProfile($userId, $name, $email, $telephoneNo, $location, $gender, $skills = []) {
        
        if ($this->profileData->emailExistsForOtherUser($email, $userId)) {
            return ["success" => false, "message" => "This email is already used by another user."];
        }
        
        // update basic info
        if (!$this->profileData->updateVolunteerBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender)) {
            return ["success" => false, "message" => "Failed to update profile."];
        }
        
        
        // delete existing skills first
        $this->profileData->deleteVolunteerSkills($userId);
        
        
        if (!empty($skills)) {
            $skillIds = $this->profileData->getSkillIdsByNames($skills);
            if (!empty($skillIds)) {
                $this->profileData->addVolunteerSkills($userId, $skillIds);
            }
        }
        
        return ["success" => true, "message" => "Profile updated successfully!"];
    }
    
    // ==================== password reset via OTP (VOLUNTEER ONLY) ====================
    
    // generate 6-digit OTP
    private function generateOTP() {
        return sprintf("%06d", mt_rand(0, 999999));
    }
    
    // send OTP for password reset
    public function sendPasswordResetOTP($userId) {
        // get user email
        $volunteer = $this->profileData->getVolunteerById($userId);
        if (!$volunteer) {
            return ["success" => false, "message" => "User not found"];
        }
        
        $email = $volunteer['email'];
        $name = $volunteer['name'];
        $otp = $this->generateOTP();
        
        // store OTP in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['password_reset_otp'] = $otp;
        $_SESSION['password_reset_otp_time'] = time();
        $_SESSION['password_reset_user_id'] = $userId;
        
        // send OTP via email
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
    
    // verify OTP for password reset
    public function verifyPasswordResetOTP($enteredOTP) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['password_reset_otp']) || !isset($_SESSION['password_reset_otp_time'])) {
            return ["success" => false, "message" => "OTP session expired. Please request a new OTP."];
        }
        
        // check if OTP is expired (10 minutes)
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
    
    // reset password after OTP verification (VOLUNTEER)
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
            // clear session variables
            unset($_SESSION['password_reset_otp']);
            unset($_SESSION['password_reset_otp_time']);
            unset($_SESSION['password_reset_user_id']);
            unset($_SESSION['otp_verified']);
            
            return ["success" => true, "message" => "Password reset successfully!"];
        } else {
            return ["success" => false, "message" => "Failed to reset password. Please try again."];
        }
    }
    
    // ==================== coordinator profile ====================
    
    // get coordinator profile by ID
    public function getCoordinatorProfile($userId) {
        $coordinator = $this->profileData->getCoordinatorById($userId);
        if (!$coordinator) {
            return ["success" => false, "message" => "Coordinator not found"];
        }
        return ["success" => true, "data" => $coordinator];
    }
    
    // update coordinator profile (NO password change allowed)
    public function updateCoordinatorProfile($userId, $name, $email, $telephoneNo, $location, $gender) {
        
        if ($this->profileData->emailExistsForOtherUser($email, $userId)) {
            return ["success" => false, "message" => "This email is already used by another user."];
        }
        
        // updating info
        if (!$this->profileData->updateUserBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender)) {
            return ["success" => false, "message" => "Failed to update profile."];
        }
        
        return ["success" => true, "message" => "Profile updated successfully!"];
    }
    
    // ==================== admin profile ====================
    
    // get admin profile by ID
    public function getAdminProfile($userId) {
        $admin = $this->profileData->getAdminById($userId);
        if (!$admin) {
            return ["success" => false, "message" => "Admin not found"];
        }
        return ["success" => true, "data" => $admin];
    }
    
    // update admin profile (without password)
    public function updateAdminProfile($userId, $name, $email, $telephoneNo, $location, $gender) {
        
        if ($this->profileData->emailExistsForOtherUser($email, $userId)) {
            return ["success" => false, "message" => "This email is already used by another user."];
        }
        
        // updating info
        if (!$this->profileData->updateUserBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender)) {
            return ["success" => false, "message" => "Failed to update profile."];
        }
        
        return ["success" => true, "message" => "Profile updated successfully!"];
    }
    
    // update admin password (NO OTP required)
    public function updateAdminPassword($userId, $currentPassword, $newPassword) {
        // verify current password
        $admin = $this->profileData->getAdminById($userId);
        if (!$admin) {
            return ["success" => false, "message" => "Admin not found"];
        }
        
        if (!password_verify($currentPassword, $admin['password'])) {
            return ["success" => false, "message" => "Current password is incorrect"];
        }
        
        // update to new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        if ($this->profileData->updateUserPassword($userId, $hashedPassword)) {
            return ["success" => true, "message" => "Password changed successfully!"];
        } else {
            return ["success" => false, "message" => "Failed to change password. Please try again."];
        }
    }

    // ==================== organizer profiles ====================
    
    // get organizer profile by ID
    public function getOrganizerProfile($userId) {
        $organizer = $this->profileData->getOrganizerById($userId);
        if (!$organizer) {
            return ["success" => false, "message" => "Organizer not found"];
        }
        return ["success" => true, "data" => $organizer];
    }
    
    // update organizer profile (without password)
    public function updateOrganizerProfile($userId, $name, $email, $telephoneNo, $location, $gender) {
        // check if email exists for another user
        if ($this->profileData->emailExistsForOtherUser($email, $userId)) {
            return ["success" => false, "message" => "This email is already used by another user."];
        }
        
        // update basic info
        if (!$this->profileData->updateOrganizerBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender)) {
            return ["success" => false, "message" => "Failed to update profile."];
        }
        
        return ["success" => true, "message" => "Profile updated successfully!"];
    }
    
    // ==================== password reset via OTP (ORGANIZER) ====================
    
    // send OTP for password reset (Organizer)
    public function sendOrganizerPasswordResetOTP($userId) {
        // get organizer email
        $organizer = $this->profileData->getOrganizerById($userId);
        if (!$organizer) {
            return ["success" => false, "message" => "Organizer not found"];
        }
        
        $email = $organizer['email'];
        $name = $organizer['name'];
        $otp = $this->generateOTP();
        
        // store OTP in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['organizer_password_reset_otp'] = $otp;
        $_SESSION['organizer_password_reset_otp_time'] = time();
        $_SESSION['organizer_password_reset_user_id'] = $userId;
        
        // send OTP via email
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
            $mail->Subject = 'Password Reset OTP - Organizer';
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
    
    // verify OTP for password reset (Organizer)
    public function verifyOrganizerPasswordResetOTP($enteredOTP) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['organizer_password_reset_otp']) || !isset($_SESSION['organizer_password_reset_otp_time'])) {
            return ["success" => false, "message" => "OTP session expired. Please request a new OTP."];
        }
        
        // check if OTP is expired (10 minutes)
        if (time() - $_SESSION['organizer_password_reset_otp_time'] > 600) {
            return ["success" => false, "message" => "OTP has expired. Please request a new one."];
        }
        
        if ($_SESSION['organizer_password_reset_otp'] === $enteredOTP) {
            $_SESSION['organizer_otp_verified'] = true;
            return ["success" => true, "message" => "OTP verified successfully"];
        } else {
            return ["success" => false, "message" => "Invalid OTP. Please try again."];
        }
    }
    
    // reset password after OTP verification (Organizer)
    public function resetOrganizerPassword($newPassword) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['organizer_otp_verified']) || !$_SESSION['organizer_otp_verified']) {
            return ["success" => false, "message" => "Please verify OTP first"];
        }
        
        if (!isset($_SESSION['organizer_password_reset_user_id'])) {
            return ["success" => false, "message" => "Session expired. Please try again."];
        }
        
        $userId = $_SESSION['organizer_password_reset_user_id'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        if ($this->profileData->updateUserPassword($userId, $hashedPassword)) {
            // clear session variables
            unset($_SESSION['organizer_password_reset_otp']);
            unset($_SESSION['organizer_password_reset_otp_time']);
            unset($_SESSION['organizer_password_reset_user_id']);
            unset($_SESSION['organizer_otp_verified']);
            
            return ["success" => true, "message" => "Password reset successfully!"];
        } else {
            return ["success" => false, "message" => "Failed to reset password. Please try again."];
        }
    }
}
?>