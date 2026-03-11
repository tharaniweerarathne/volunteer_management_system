<?php


class ProfileData {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // ==================== volunteer method ====================
    
    // get volunteer by ID with skills
    public function getVolunteerById($userId) {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender FROM users WHERE userId = ? AND role = 'Volunteer'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $volunteer = $result->fetch_assoc();
        
        if ($volunteer) {
            
            $skillMapping = [
                1 => 'teaching',
                2 => 'event-organizing',
                3 => 'first-aid',
                4 => 'photography',
                5 => 'cooking',
                6 => 'fundraising',
                7 => 'social-media',
                8 => 'graphic-design',
                9 => 'writing',
                10 => 'translation',
                11 => 'it-support',
                12 => 'mentoring'
            ];
            
            // get skill IDs for this volunteer
            $skillStmt = $this->conn->prepare("SELECT skillId FROM volunteer_skills WHERE userId = ?");
            $skillStmt->bind_param("i", $userId);
            $skillStmt->execute();
            $skillResult = $skillStmt->get_result();
            
            $skills = [];
            while ($skillRow = $skillResult->fetch_assoc()) {
                $skillId = $skillRow['skillId'];
                if (isset($skillMapping[$skillId])) {
                    $skills[] = $skillMapping[$skillId];
                }
            }
            $volunteer['skills'] = $skills;
        }
        
        return $volunteer;
    }
    
    // updating volunteer info (without password)
    public function updateVolunteerBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, telephoneNo = ?, location = ?, gender = ? WHERE userId = ? AND role = 'Volunteer'");
        $stmt->bind_param("sssssi", $name, $email, $telephoneNo, $location, $gender, $userId);
        return $stmt->execute();
    }
    
    // get skill IDs by skill names
    public function getSkillIdsByNames($skillNames) {
        if (empty($skillNames)) {
            return [];
        }
        
        
        $skillMapping = [
            'teaching' => 1,
            'event-organizing' => 2,
            'first-aid' => 3,
            'photography' => 4,
            'cooking' => 5,
            'fundraising' => 6,
            'social-media' => 7,
            'graphic-design' => 8,
            'writing' => 9,
            'translation' => 10,
            'it-support' => 11,
            'mentoring' => 12
        ];
        
        $skillIds = [];
        foreach ($skillNames as $skillName) {
            if (isset($skillMapping[$skillName])) {
                $skillIds[] = $skillMapping[$skillName];
            }
        }
        
        return $skillIds;
    }
    
    // delete volunteer skills
    public function deleteVolunteerSkills($userId) {
        $stmt = $this->conn->prepare("DELETE FROM volunteer_skills WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    // add volunteer skills
    public function addVolunteerSkills($userId, $skillIds) {
        if (empty($skillIds)) {
            return true;
        }
        
        $stmt = $this->conn->prepare("INSERT INTO volunteer_skills (userId, skillId) VALUES (?, ?)");
        
        foreach ($skillIds as $skillId) {
            $stmt->bind_param("ii", $userId, $skillId);
            if (!$stmt->execute()) {
                return false;
            }
        }
        return true;
    }
    
    // ==================== coordinator methods ====================
    
    // get coordinator by ID (no skills)
    public function getCoordinatorById($userId) {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender FROM users WHERE userId = ? AND role = 'Coordinator'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // ==================== admin methods ====================
    
    // get admin by ID with password (for verification)
    public function getAdminById($userId) {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender, password FROM users WHERE userId = ? AND role = 'Admin'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
   
    
    // updating user basic info 
    public function updateUserBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, telephoneNo = ?, location = ?, gender = ? WHERE userId = ?");
        $stmt->bind_param("sssssi", $name, $email, $telephoneNo, $location, $gender, $userId);
        return $stmt->execute();
    }
    
    // updating user password 
    public function updateUserPassword($userId, $hashedPassword) {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE userId = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        return $stmt->execute();
    }
    
    // check if email exists for another user
    public function emailExistsForOtherUser($email, $userId) {
        $stmt = $this->conn->prepare("SELECT userId FROM users WHERE email = ? AND userId != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

        // get organizer by ID (no skills)
    public function getOrganizerById($userId) {
        $stmt = $this->conn->prepare("SELECT userId, name, email, telephoneNo, location, gender FROM users WHERE userId = ? AND role = 'Organizer'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

        // update organizer basic info
    public function updateOrganizerBasicInfo($userId, $name, $email, $telephoneNo, $location, $gender) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, telephoneNo = ?, location = ?, gender = ? WHERE userId = ? AND role = 'Organizer'");
        $stmt->bind_param("sssssi", $name, $email, $telephoneNo, $location, $gender, $userId);
        return $stmt->execute();
    }
}
?>