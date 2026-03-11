<?php
require_once __DIR__ . '/../data_access/db.php';

class SkillData {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getSkillById($skillId) {
        if (!$skillId) return null;
        
        $sql = "SELECT * FROM skills WHERE skillId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $skillId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getAllSkills() {
        $sql = "SELECT * FROM skills ORDER BY skillName";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>