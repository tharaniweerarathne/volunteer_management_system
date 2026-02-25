<?php
// business_logic/LeaderboardLogic.php
require_once '../data_access/LeaderboardData.php';

class LeaderboardLogic {
    private $leaderboardData;
    
    public function __construct() {
        $this->leaderboardData = new LeaderboardData();
    }
    
    // Get formatted leaderboard data for podium (top 3)
    public function getPodiumData() {
        $topVolunteers = $this->leaderboardData->getTopVolunteersByAttendance(3);
        $podium = [];
        
        $rankings = [
            1 => ['class' => 'first', 'icon' => 'ri-trophy-fill', 'color' => '#ffffff'],
            2 => ['class' => 'second', 'icon' => 'ri-medal-2-fill', 'color' => '#ffffff'],
            3 => ['class' => 'third', 'icon' => 'ri-medal-fill', 'color' => '#ffffff']
        ];
        
        foreach ($topVolunteers as $index => $volunteer) {
            $rank = $index + 1;
            $initials = $this->getInitials($volunteer['name']);
            $level = $this->getVolunteerLevel($volunteer['total_hours'] ?? 0);
            $trend = $this->getTrendData($volunteer['userId']);
            
            $podium[] = [
                'rank' => $rank,
                'name' => $volunteer['name'],
                'initials' => $initials,
                'total_attendance' => $volunteer['total_attendance'],
                'unique_events' => $volunteer['unique_events'],
                'total_hours' => round($volunteer['total_hours'] ?? 0, 1), // Round to 1 decimal
                'level' => $level['name'],
                'level_color' => $level['color'],
                'trend' => $trend,
                'class' => $rankings[$rank]['class'],
                'icon' => $rankings[$rank]['icon'],
                'icon_color' => $rankings[$rank]['color']
            ];
        }
        
        return $podium;
    }
    
    // Get volunteer level based on total hours
    private function getVolunteerLevel($totalHours) {
        if ($totalHours >= 200) {
            return ['name' => 'Elite Volunteer', 'color' => '#ffd900'];
        } elseif ($totalHours >= 100) {
            return ['name' => 'Senior Volunteer', 'color' => '#c0c0c0'];
        } elseif ($totalHours >= 50) {
            return ['name' => 'Regular Volunteer', 'color' => '#cd7f32'];
        } else {
            return ['name' => 'Rising Star', 'color' => '#92400c'];
        }
    }
    
    // Get initials from name
    private function getInitials($name) {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return strlen($initials) >= 2 ? substr($initials, 0, 2) : $initials;
    }
    
    // Calculate trend based on monthly hours
    private function getTrendData($userId) {
        $months = $this->leaderboardData->getVolunteerMonthlyHours($userId);
        
        if (count($months) < 2) {
            return ['direction' => 'stable', 'percentage' => 0];
        }
        
        $current = (float)($months[0]['total_hours'] ?? 0);
        $previous = (float)($months[1]['total_hours'] ?? 0);
        
        if ($previous == 0) {
            return ['direction' => 'up', 'percentage' => 100];
        }
        
        $change = (($current - $previous) / $previous) * 100;
        
        if ($change > 0) {
            return ['direction' => 'up', 'percentage' => round($change)];
        } elseif ($change < 0) {
            return ['direction' => 'down', 'percentage' => round(abs($change))];
        } else {
            return ['direction' => 'stable', 'percentage' => 0];
        }
    }
}
?>