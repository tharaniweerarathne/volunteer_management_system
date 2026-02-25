<?php
// data_access/LeaderboardData.php
require_once 'db.php';

class LeaderboardData {
    
    // Get top volunteers based on attendance with ACTUAL hours calculated from time
    public function getTopVolunteersByAttendance($limit = 10) {
        global $conn;
        
        $sql = "SELECT 
                u.userId,
                u.name,
                u.email,
                COUNT(a.attendanceId) as total_attendance,
                COUNT(DISTINCT a.eventId) as unique_events,
                COUNT(DISTINCT DATE(a.attendanceDate)) as unique_days,
                SUM(TIMESTAMPDIFF(HOUR, e.startTime, e.endTime)) as total_hours,
                GROUP_CONCAT(DISTINCT s.skillName SEPARATOR ', ') as skills
                FROM users u
                LEFT JOIN attendance a ON u.userId = a.userId AND a.status = 'Present'
                LEFT JOIN events e ON a.eventId = e.eventId
                LEFT JOIN volunteer_skills vs ON u.userId = vs.userId
                LEFT JOIN skills s ON vs.skillId = s.skillId
                WHERE u.role = 'Volunteer'
                GROUP BY u.userId
                HAVING total_attendance > 0
                ORDER BY total_hours DESC, total_attendance DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $volunteers = [];
        while ($row = $result->fetch_assoc()) {
            $volunteers[] = $row;
        }
        
        return $volunteers;
    }

    // Get volunteer's monthly hours for trend
    public function getVolunteerMonthlyHours($userId) {
        global $conn;
        
        $sql = "SELECT 
                DATE_FORMAT(a.attendanceDate, '%Y-%m') as month,
                COUNT(a.attendanceId) as attendance_count,
                SUM(TIMESTAMPDIFF(HOUR, e.startTime, e.endTime)) as total_hours
                FROM attendance a
                LEFT JOIN events e ON a.eventId = e.eventId
                WHERE a.userId = ? 
                AND a.status = 'Present'
                AND a.attendanceDate >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                GROUP BY DATE_FORMAT(a.attendanceDate, '%Y-%m')
                ORDER BY month DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $months = [];
        while ($row = $result->fetch_assoc()) {
            $months[] = $row;
        }
        
        return $months;
    }
}
?>