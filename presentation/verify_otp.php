<?php
session_start();
require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/RegistrationLogic.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $enteredOTP = $input['otp'] ?? '';
    
    if (empty($enteredOTP)) {
        echo json_encode(["success" => false, "message" => "Please enter OTP"]);
        exit;
    }
    
    $registrationLogic = new RegistrationLogic($conn);
    $verifyResult = $registrationLogic->verifyOTP($enteredOTP);
    
    if ($verifyResult['success']) {
        // OTP is correct, now complete registration
        if (isset($_SESSION['temp_registration'])) {
            $data = $_SESSION['temp_registration'];
            
            // CHECK AGAIN BEFORE FINAL REGISTRATION (in case someone registered between OTP send and verify)
            if ($registrationLogic->checkEmailExists($data['email'])) {
                // Clear session data
                unset($_SESSION['temp_registration']);
                unset($_SESSION['otp']);
                unset($_SESSION['otp_time']);
                unset($_SESSION['otp_email']);
                
                echo json_encode([
                    "success" => false, 
                    "message" => "This email has already been registered. Please sign in instead.",
                    "redirect" => "sign_in.php"
                ]);
                exit;
            }
            
            $registerResult = $registrationLogic->registerUser(
                $data['name'],
                $data['email'],
                $data['password'],
                $data['phone'],
                $data['location'],
                $data['gender'],
                $data['skills']
            );
            
            if ($registerResult['success']) {
                // Clear temporary data
                unset($_SESSION['temp_registration']);
                unset($_SESSION['otp']);
                unset($_SESSION['otp_time']);
                unset($_SESSION['otp_email']);
                
                echo json_encode([
                    "success" => true,
                    "message" => "Registration completed successfully!",
                    "redirect" => "sign_in.php"
                ]);
            } else {
                echo json_encode($registerResult);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Registration data not found. Please start again."]);
        }
    } else {
        echo json_encode($verifyResult);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>