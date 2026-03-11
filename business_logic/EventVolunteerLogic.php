<?php
require_once '../data_access/EventVolunteerData.php';

class EventVolunteerLogic {
    private $eventVolunteerData;
    
    public function __construct() {
        $this->eventVolunteerData = new EventVolunteerData();
    }
    

// Check if user can view volunteers
public function canViewVolunteers($eventId, $userId, $userRole) {
    if ($userRole === 'Admin') {
        return true;
    }
    
    // Allow ALL coordinators and organizers 
    if ($userRole === 'Organizer' || $userRole === 'Coordinator') {
        return true;
    }
    
    return false;
}
   
    public function getVolunteers($eventId) {
        return $this->eventVolunteerData->getVolunteersByEvent($eventId);
    }
    
    
    public function getEvent($eventId) {
        return $this->eventVolunteerData->getEventDetails($eventId);
    }
    
  
    public function getStatistics($eventId) {
        return $this->eventVolunteerData->getEventStatistics($eventId);
    }
    
    // Export volunteers to CSV
    public function exportCSV($eventId) {
        return $this->eventVolunteerData->exportVolunteersToCSV($eventId);
    }
    


   
public function getVolunteersForExport($eventId) {
    $volunteersData = $this->eventVolunteerData->exportVolunteersToCSV($eventId);
    $formatted = [];
    
    foreach ($volunteersData as $volunteer) {
        
        $registrationDate = date('Y-m-d', strtotime($volunteer['registrationDate']));
        
        $formatted[] = [
            'name' => $volunteer['name'],
            'email' => $volunteer['email'],
            'phone' => $volunteer['telephoneNo'] ?? 'N/A',
            'gender' => $volunteer['gender'] ?? 'Not specified',
            'location' => $volunteer['location'] ?? 'Not specified',
            'registrationDate' => $registrationDate, 
            'skills' => $volunteer['skills'] ?? 'No skills'
        ];
    }
    
    return $formatted;
}



public function searchVolunteers($eventId, $searchParams = []) {
    $volunteers = $this->eventVolunteerData->searchVolunteersByEvent($eventId, $searchParams);
    
    $formatted = [];
    foreach ($volunteers as $volunteer) {
        $formatted[] = [
            'id' => $volunteer['userId'],
            'name' => htmlspecialchars($volunteer['name']),
            'email' => htmlspecialchars($volunteer['email']),
            'phone' => $volunteer['phone'] ? htmlspecialchars($volunteer['phone']) : 'N/A',
            'gender' => $volunteer['gender'] ?? 'Not specified',
            'location' => $volunteer['location'] ? htmlspecialchars($volunteer['location']) : 'Not specified',
            'registrationDate' => date('M d, Y', strtotime($volunteer['registrationDate'])),
            'registrationTime' => date('h:i A', strtotime($volunteer['registrationDate'])),
            'skills' => $volunteer['skills'] ? explode(', ', $volunteer['skills']) : []
        ];
    }
    
    return $formatted;
}


public function getFormattedVolunteers($eventId, $searchParams = []) {
    if (!empty($searchParams)) {
        return $this->searchVolunteers($eventId, $searchParams);
    }
    
    
    $volunteers = $this->getVolunteers($eventId);
    $formatted = [];
    
    foreach ($volunteers as $volunteer) {
        $formatted[] = [
            'id' => $volunteer['userId'],
            'name' => htmlspecialchars($volunteer['name']),
            'email' => htmlspecialchars($volunteer['email']),
            'phone' => $volunteer['phone'] ? htmlspecialchars($volunteer['phone']) : 'N/A',
            'gender' => $volunteer['gender'] ?? 'Not specified',
            'location' => $volunteer['location'] ? htmlspecialchars($volunteer['location']) : 'Not specified',
            'registrationDate' => date('M d, Y', strtotime($volunteer['registrationDate'])),
            'registrationTime' => date('h:i A', strtotime($volunteer['registrationDate'])),
            'skills' => $volunteer['skills'] ? explode(', ', $volunteer['skills']) : []
        ];
    }
    
    return $formatted;
}
}
?>