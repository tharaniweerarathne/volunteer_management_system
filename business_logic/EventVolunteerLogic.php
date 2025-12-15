<?php
require_once '../data_access/EventVolunteerData.php';

class EventVolunteerLogic {
    private $eventVolunteerData;
    
    public function __construct() {
        $this->eventVolunteerData = new EventVolunteerData();
    }
    
    // Check if user can view volunteers (Admin or assigned Coordinator)
    public function canViewVolunteers($eventId, $userId, $userRole) {
        if ($userRole === 'Admin') {
            return true;
        }
        
        if ($userRole === 'Coordinator') {
            return $this->eventVolunteerData->isUserCoordinatorForEvent($eventId, $userId);
        }
        
        return false;
    }
    
    // Get volunteers for event
    public function getVolunteers($eventId) {
        return $this->eventVolunteerData->getVolunteersByEvent($eventId);
    }
    
    // Get event details
    public function getEvent($eventId) {
        return $this->eventVolunteerData->getEventDetails($eventId);
    }
    
    // Get event statistics
    public function getStatistics($eventId) {
        return $this->eventVolunteerData->getEventStatistics($eventId);
    }
    
    // Export volunteers to CSV
    public function exportCSV($eventId) {
        return $this->eventVolunteerData->exportVolunteersToCSV($eventId);
    }
    
    // Get formatted volunteers data for display
    public function getFormattedVolunteers($eventId) {
        $volunteers = $this->getVolunteers($eventId);
        $formatted = [];
        
        foreach ($volunteers as $volunteer) {
            $formatted[] = [
                'id' => $volunteer['userId'],
                'name' => htmlspecialchars($volunteer['name']),
                'email' => htmlspecialchars($volunteer['email']),
                'phone' => $volunteer['telephoneNo'] ? htmlspecialchars($volunteer['telephoneNo']) : 'N/A',
                'gender' => $volunteer['gender'] ?? 'Not specified',
                'registrationDate' => date('M d, Y', strtotime($volunteer['registrationDate'])),
                'skills' => $volunteer['skills'] ? explode(', ', $volunteer['skills']) : [],
                'profileImage' => $volunteer['profile_image'] ?? null
            ];
        }
        
        return $formatted;
    }

    // Add this method to your EventVolunteerLogic class:
public function getVolunteersForExport($eventId) {
    $volunteersData = $this->eventVolunteerData->exportVolunteersToCSV($eventId);
    $formatted = [];
    
    foreach ($volunteersData as $volunteer) {
        // Format date properly for CSV (YYYY-MM-DD format is Excel-friendly)
        $registrationDate = date('Y-m-d', strtotime($volunteer['registrationDate']));
        
        $formatted[] = [
            'name' => $volunteer['name'],
            'email' => $volunteer['email'],
            'phone' => $volunteer['telephoneNo'] ?? 'N/A',
            'gender' => $volunteer['gender'] ?? 'Not specified',
            'location' => $volunteer['location'] ?? 'Not specified',
            'registrationDate' => $registrationDate, // Formatted date
            'skills' => $volunteer['skills'] ?? 'No skills'
        ];
    }
    
    return $formatted;
}
}
?>