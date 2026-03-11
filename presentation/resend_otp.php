<?php
session_start();
require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/RegistrationLogic.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['temp_registration'])) {
        echo json_encode(["success" => false, "message" => "Session expired. Please start registration again."]);
        exit;
    }
    
    $data = $_SESSION['temp_registration'];
    $registrationLogic = new RegistrationLogic($conn);
    
    $result = $registrationLogic->sendOTP($data['email'], $data['name']);
    echo json_encode($result);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>