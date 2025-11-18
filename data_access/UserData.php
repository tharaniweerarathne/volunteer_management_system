<?php
class UserData {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); 
    }
}
?>
