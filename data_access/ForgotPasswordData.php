<?php
class ForgotPasswordData {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // checking if email exists
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT userId, name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // updating user password
    public function updatePassword($email, $hashedPassword) {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        return $stmt->execute();
    }
}
?>