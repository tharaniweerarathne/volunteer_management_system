<?php

session_start();

// checking if user is logged in
if (!isset($_SESSION['userId']) || !in_array($_SESSION['role'], ['Volunteer', 'Admin', 'Coordinator'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit;
}

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/ProfileLogic.php";

header('Content-Type: application/json');

$profileLogic = new ProfileLogic($conn);
$userId = $_SESSION['userId'];
$userRole = $_SESSION['role'];


$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'update_profile':
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $gender = $input['gender'] ?? '';
        $location = trim($input['location'] ?? '');
        $skills = $input['skills'] ?? [];
        
        // validation
        if (empty($name) || empty($email) || empty($phone) || empty($gender) || empty($location)) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email format"]);
            exit;
        }
        
        // update method based on role
        switch ($userRole) {
            case 'Volunteer':
                $result = $profileLogic->updateVolunteerProfile($userId, $name, $email, $phone, $location, $gender, $skills);
                break;
            case 'Admin':
                $result = $profileLogic->updateAdminProfile($userId, $name, $email, $phone, $location, $gender);
                break;
            case 'Coordinator':
                $result = $profileLogic->updateCoordinatorProfile($userId, $name, $email, $phone, $location, $gender);
                break;
            default:
                $result = ["success" => false, "message" => "Invalid user role"];
        }
        
        echo json_encode($result);
        break;
        
    case 'send_otp':
        // only for Volunteers
        if ($userRole !== 'Volunteer') {
            echo json_encode(["success" => false, "message" => "Invalid action for your role"]);
            exit;
        }
        
        $result = $profileLogic->sendPasswordResetOTP($userId);
        echo json_encode($result);
        break;
        
    case 'verify_otp':
        // only for Volunteers
        if ($userRole !== 'Volunteer') {
            echo json_encode(["success" => false, "message" => "Invalid action for your role"]);
            exit;
        }
        
        $otp = $input['otp'] ?? '';
        
        if (empty($otp) || strlen($otp) !== 6) {
            echo json_encode(["success" => false, "message" => "Invalid OTP format"]);
            exit;
        }
        
        $result = $profileLogic->verifyPasswordResetOTP($otp);
        echo json_encode($result);
        break;
        
    case 'reset_password':
        // only for Volunteers (OTP-based reset)
        if ($userRole !== 'Volunteer') {
            echo json_encode(["success" => false, "message" => "Invalid action for your role"]);
            exit;
        }
        
        $password = $input['password'] ?? '';
        
        // password validation
        if (strlen($password) < 5) {
            echo json_encode(["success" => false, "message" => "Password must be at least 5 characters long"]);
            exit;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            echo json_encode(["success" => false, "message" => "Password must contain at least one number"]);
            exit;
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            echo json_encode(["success" => false, "message" => "Password must contain at least one special character"]);
            exit;
        }
        
        $result = $profileLogic->resetPassword($password);
        echo json_encode($result);
        break;
        
    case 'change_password':
        // only for Admins (current password verification)
        if ($userRole !== 'Admin') {
            echo json_encode(["success" => false, "message" => "Invalid action for your role"]);
            exit;
        }
        
        $currentPassword = $input['currentPassword'] ?? '';
        $newPassword = $input['newPassword'] ?? '';
        
        // validation
        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(["success" => false, "message" => "Both current and new passwords are required"]);
            exit;
        }
        
        // validation of new password
        if (strlen($newPassword) < 5) {
            echo json_encode(["success" => false, "message" => "Password must be at least 5 characters long"]);
            exit;
        }
        
        if (!preg_match('/[0-9]/', $newPassword)) {
            echo json_encode(["success" => false, "message" => "Password must contain at least one number"]);
            exit;
        }
        
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
            echo json_encode(["success" => false, "message" => "Password must contain at least one special character"]);
            exit;
        }
        
        $result = $profileLogic->updateAdminPassword($userId, $currentPassword, $newPassword);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        break;
}
?>