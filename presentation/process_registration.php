<?php
session_start();
require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/RegistrationLogic.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $phone = trim($input['phone'] ?? '');
    $gender = $input['gender'] ?? '';
    $location = trim($input['location'] ?? '');
    $skills = $input['skills'] ?? [];
    
    // validation of input
    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($gender) || empty($location)) {
        echo json_encode(["success" => false, "message" => "All required fields must be filled"]);
        exit;
    }
    
    // Validation of email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format"]);
        exit;
    }
    
    // checking email
    $registrationLogic = new RegistrationLogic($conn);
    
    
    if ($registrationLogic->checkEmailExists($email)) {
        echo json_encode(["success" => false, "message" => "This email is already registered. Please use a different email or sign in."]);
        exit;
    }
    
    
    $_SESSION['temp_registration'] = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'phone' => $phone,
        'gender' => $gender,
        'location' => $location,
        'skills' => $skills
    ];
    
    // send OTP
    $result = $registrationLogic->sendOTP($email, $name);
    
    echo json_encode($result);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>