<?php
session_start();
require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/ForgotPasswordLogic.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$forgotPasswordLogic = new ForgotPasswordLogic($conn);

switch ($action) {
    case 'send_otp':
        $email = trim($input['email'] ?? '');
        
        if (empty($email)) {
            echo json_encode(["success" => false, "message" => "Email is required"]);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email format"]);
            exit;
        }
        
        
        unset($_SESSION['otp_verified']);
        
        $result = $forgotPasswordLogic->sendPasswordResetOTP($email);
        echo json_encode($result);
        break;
        
    case 'verify_otp':
        $otp = $input['otp'] ?? '';
        
        if (empty($otp)) {
            echo json_encode(["success" => false, "message" => "Please enter OTP"]);
            exit;
        }
        
        $result = $forgotPasswordLogic->verifyPasswordResetOTP($otp);
        
        if ($result['success']) {
            $_SESSION['otp_verified'] = true;
        }
        
        echo json_encode($result);
        break;
        
    case 'resend_otp':
        if (!isset($_SESSION['forgot_email'])) {
            echo json_encode(["success" => false, "message" => "Session expired. Please start again."]);
            exit;
        }
        
        $email = $_SESSION['forgot_email'];
        $result = $forgotPasswordLogic->sendPasswordResetOTP($email);
        echo json_encode($result);
        break;
        
    case 'reset_password':
        $password = $input['password'] ?? '';
        
        if (empty($password)) {
            echo json_encode(["success" => false, "message" => "Password is required"]);
            exit;
        }
        
        // validating password 
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
        
        if (!isset($_SESSION['otp_verified'])) {
            echo json_encode(["success" => false, "message" => "Please verify OTP first"]);
            exit;
        }
        
        $result = $forgotPasswordLogic->resetPassword($password);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        break;
}
?>