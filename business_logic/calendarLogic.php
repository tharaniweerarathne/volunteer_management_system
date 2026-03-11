<?php

require_once __DIR__ . '/../data_access/calendarData.php';

class CalendarLogic {
    private $calendarData;
    
    public function __construct() {
        $this->calendarData = new CalendarData();
    }
    
    // Get events based on user role
    public function getEventsForUser($userId, $userRole) {
        switch ($userRole) {
            case 'Admin':
                return $this->calendarData->getAllEvents(); 
            case 'Coordinator':
                return $this->calendarData->getCoordinatorEvents($userId);
            case 'Organizer':
                return $this->calendarData->getOrganizerEvents($userId);
            case 'Volunteer':
                return $this->calendarData->getVolunteerJoinedEvents($userId);
            default:
                return [];
        }
    }
    
    // Format events for FullCalendar
    public function formatEventsForCalendar($events, $userRole = 'Volunteer') {
        $formattedEvents = [];
        
        foreach ($events as $event) {
            $status = $this->calendarData->getEventStatus($event);
            
            
            $color = $this->getEventColor($event, $status, $userRole);
            
            
            $startTime = !empty($event['startTime']) ? $event['startTime'] : '00:00:00';
            $endTime = !empty($event['endTime']) ? $event['endTime'] : '23:59:59';
            
            $startDateTime = $event['startDate'] . 'T' . $startTime;
            $endDateTime = $event['endDate'] . 'T' . $endTime;
            
            // Create event description
            $description = $this->createEventDescription($event, $status, $userRole);
            
            $formattedEvents[] = [
                'id' => $event['eventId'],
                'title' => $event['eventName'],
                'start' => $startDateTime,
                'end' => $endDateTime,
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'description' => $description,
                    'location' => $event['location'] ?? 'Not specified',
                    'organizer' => $event['organizerName'] ?? 'Unknown',
                    'organizerId' => $event['organizerId'] ?? null,
                    'coordinators' => $event['coordinators'] ?? 'No coordinators assigned',
                    'coordinatorIds' => $event['coordinatorIds'] ?? '',
                    'status' => $status,
                    'eventStatus' => $event['status'] ?? 'Active',
                    'availableSlots' => $event['availableSlots'] ?? 0,
                    'maxVolunteers' => $event['maxVolunteers'] ?? 0,
                    'joinedCount' => $event['joinedCount'] ?? 0,
                    'totalRegistrations' => $event['total_registrations'] ?? 0,
                    'category' => $event['category'] ?? 'General',
                    'requiredSkill' => $event['skillName'] ?? 'No specific skill required',
                    'registrationStatus' => $event['registrationStatus'] ?? null
                ]
            ];
        }
        
        return $formattedEvents;
    }
    
    private function getEventColor($event, $status, $userRole) {
        
        if ($status === 'cancelled') {
            return '#dc3545'; 
        } elseif ($status === 'over') {
            return '#6c757d'; 
        }
        
        
        if ($userRole === 'Admin') {
            
            $eventStatus = $event['status'] ?? 'Active';
            switch ($eventStatus) {
                case 'Active': return '#198754'; 
                case 'Pending': return '#ffc107'; 
                case 'Draft': return '#6c757d'; 
                default: return '#0dcaf0'; 
            }
        } else {
            
            switch ($userRole) {
                case 'Coordinator': return '#0d6efd'; 
                case 'Organizer': return '#ffc107'; 
                case 'Volunteer': return '#6f42c1'; 
                default: return '#20c997'; 
            }
        }
    }
    
    private function createEventDescription($event, $status, $userRole) {
        $description = "<strong>" . htmlspecialchars($event['eventName']) . "</strong><br>";
        
        // Status badges
        if ($status === 'cancelled') {
            $description .= "<span class='badge bg-danger'>CANCELLED</span><br>";
        } elseif ($status === 'over') {
            $description .= "<span class='badge bg-secondary'>EVENT OVER</span><br>";
        } elseif (isset($event['status']) && $event['status'] !== 'Active') {
            $description .= "<span class='badge bg-warning text-dark'>" . strtoupper($event['status']) . "</span><br>";
        }
        
        $description .= "<strong>Date:</strong> " . date('M j, Y', strtotime($event['startDate']));
        if ($event['startDate'] !== $event['endDate']) {
            $description .= " to " . date('M j, Y', strtotime($event['endDate']));
        }
        
        $description .= "<br><strong>Time:</strong> " . 
            (!empty($event['startTime']) ? date('h:i A', strtotime($event['startTime'])) : 'All day') . 
            " - " . 
            (!empty($event['endTime']) ? date('h:i A', strtotime($event['endTime'])) : 'All day');
        
        $description .= "<br><strong>Location:</strong> " . htmlspecialchars($event['location'] ?? 'Not specified');
        $description .= "<br><strong>Organizer:</strong> " . htmlspecialchars($event['organizerName'] ?? 'Unknown');
        
        if (!empty($event['coordinators'])) {
            $description .= "<br><strong>Coordinators:</strong> " . htmlspecialchars($event['coordinators']);
        }
        
        if ($userRole === 'Admin') {
            
            $description .= "<br><strong>Event Status:</strong> " . ($event['status'] ?? 'Active');
            $description .= "<br><strong>Created:</strong> " . date('M j, Y', strtotime($event['createdAt'] ?? ''));
            
            if (isset($event['total_registrations'])) {
                $description .= "<br><strong>Total Registrations:</strong> " . $event['total_registrations'];
            }
        }
        
        if (isset($event['maxVolunteers']) && $event['maxVolunteers'] > 0) {
            $description .= "<br><strong>Capacity:</strong> " . 
                ($event['joinedCount'] ?? 0) . "/" . $event['maxVolunteers'] . 
                " (" . ($event['availableSlots'] ?? 0) . " available)";
        }
        
        if (!empty($event['category'])) {
            $description .= "<br><strong>Category:</strong> " . htmlspecialchars($event['category']);
        }
        
        if (!empty($event['skillName'])) {
            $description .= "<br><strong>Required Skill:</strong> " . htmlspecialchars($event['skillName']);
        }
        
        return $description;
    }
    
    // Get calendar view based on user role
    public function getCalendarViewData($userId, $userRole) {
        $events = $this->getEventsForUser($userId, $userRole);
        $formattedEvents = $this->formatEventsForCalendar($events, $userRole);
        
        return [
            'success' => true,
            'events' => $formattedEvents,
            'totalEvents' => count($events),
            'userRole' => $userRole
        ];
    }
    
    // Get dashboard statistics based on user role
    public function getDashboardStatistics($userId, $userRole) {
        $stats = [];
        
        if ($userRole === 'Admin') {
            $eventStats = $this->calendarData->getAdminStatistics();
            $userStats = $this->calendarData->getUserStatistics();
            
            $stats = array_merge($eventStats, $userStats);
        } else {
            
            $events = $this->getEventsForUser($userId, $userRole);
            $totalEvents = count($events);
            
            // Count upcoming events
            $upcoming = 0;
            $active = 0;
            $over = 0;
            
            foreach ($events as $event) {
                $status = $this->calendarData->getEventStatus($event);
                if ($status === 'over') {
                    $over++;
                } elseif ($status === 'active') {
                    $active++;
                }
                
                
                if (isset($event['startDate']) && strtotime($event['startDate']) > time()) {
                    $upcoming++;
                }
            }
            
            $stats = [
                'total_events' => $totalEvents,
                'upcoming_events' => $upcoming,
                'active_events' => $active,
                'past_events' => $over
            ];
        }
        
        return $stats;
    }
}
?>