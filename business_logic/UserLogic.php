<?php
require_once __DIR__ . "/../data_access/UserData.php";

class UserLogic {
    private $userData;

    public function __construct($conn) {
        $this->userData = new UserData($conn);
    }

    // Login function
    public function login($email, $password) {
        $user = $this->userData->getUserByEmail($email);

        if (!$user) {
            return ["success" => false, "message" => "Email not found"];
        }

        if (password_verify($password, $user['password'])) {
            return ["success" => true, "user" => $user];
        } else {
            return ["success" => false, "message" => "Incorrect password. Please check your credentials and try again later."];
        }
    }
}
?>