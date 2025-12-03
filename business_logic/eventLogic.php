<?php
require_once '../data_access/eventData.php';

session_start();

// Handle image upload
function uploadImage($eventName) {
    if (!isset($_FILES['eventImage']) || $_FILES['eventImage']['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['eventImage'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    
    if (!in_array($mime, $allowedTypes)) {
        return false;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . preg_replace('/[^a-z0-9]/i', '_', $eventName) . '.' . $extension;
    
    $uploadDir = '../assets/event_img/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'assets/event_img/' . $filename;
    }
    
    return false;
}

// Create event
function handleCreateEvent() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request'];
    }

    $required = ['eventName', 'startDate', 'endDate', 'location'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            return ['success' => false, 'message' => "$field is required"];
        }
    }

    if (strtotime($_POST['endDate']) < strtotime($_POST['startDate'])) {
        return ['success' => false, 'message' => 'End date cannot be before start date'];
    }

    $imagePath = uploadImage($_POST['eventName']);
    if ($imagePath === false) {
        return ['success' => false, 'message' => 'Invalid image file'];
    }

    $eventData = [
        'eventName' => $_POST['eventName'],
        'eventDescription' => $_POST['eventDescription'] ?? '',
        'category' => $_POST['category'] ?? '',
        'location' => $_POST['location'],
        'googleMapLink' => $_POST['googleMapLink'] ?? '',
        'startDate' => $_POST['startDate'],
        'endDate' => $_POST['endDate'],
        'startTime' => $_POST['startTime'] ?? '00:00',
        'endTime' => $_POST['endTime'] ?? '23:59',
        'maxVolunteers' => $_POST['maxVolunteers'] ?? 0,
        'requiredSkillId' => $_POST['requiredSkillId'] ?? null,
        'eventImage' => $imagePath,
        'createdBy' => $_SESSION['userId']
    ];

    $eventId = createEvent($eventData);
    
    if ($eventId) {
        // Assign coordinators if admin
        if ($_SESSION['role'] === 'Admin' && isset($_POST['coordinators'])) {
            $coordinatorIds = array_map('intval', $_POST['coordinators']);
            $assignResult = assignCoordinators($eventId, $coordinatorIds);
            
            if (!$assignResult['success']) {
                $conflictMsg = '';
                foreach ($assignResult['conflicts'] as $conflict) {
                    $conflictMsg .= $conflict['coordinatorName'] . " has conflict with '{$conflict['eventName']}' ";
                }
                return ['success' => true, 'eventId' => $eventId, 'warning' => $conflictMsg];
            }
        }
        
        return ['success' => true, 'eventId' => $eventId];
    }
    
    return ['success' => false, 'message' => 'Failed to create event'];
}

// Update event
function handleUpdateEvent($eventId) {
    $event = getEventById($eventId);
    if (!$event) {
        return ['success' => false, 'message' => 'Event not found'];
    }

    if (!canUserEditEvent($eventId, $_SESSION['userId'])) {
        return ['success' => false, 'message' => 'Permission denied'];
    }

    $imagePath = uploadImage($_POST['eventName']);
    if ($imagePath === false) {
        return ['success' => false, 'message' => 'Invalid image file'];
    }

    $eventData = [
        'eventName' => $_POST['eventName'],
        'eventDescription' => $_POST['eventDescription'] ?? '',
        'category' => $_POST['category'] ?? '',
        'location' => $_POST['location'],
        'googleMapLink' => $_POST['googleMapLink'] ?? '',
        'startDate' => $_POST['startDate'],
        'endDate' => $_POST['endDate'],
        'startTime' => $_POST['startTime'] ?? '00:00',
        'endTime' => $_POST['endTime'] ?? '23:59',
        'maxVolunteers' => $_POST['maxVolunteers'] ?? 0,
        'requiredSkillId' => $_POST['requiredSkillId'] ?? null,
        'eventImage' => $imagePath !== null ? $imagePath : $event['eventImage']
    ];

    $success = updateEvent($eventId, $eventData);
    
    if ($success && $_SESSION['role'] === 'Admin' && isset($_POST['coordinators'])) {
        $coordinatorIds = array_map('intval', $_POST['coordinators']);
        $assignResult = assignCoordinators($eventId, $coordinatorIds);
        
        if (!$assignResult['success']) {
            $conflictMsg = '';
            foreach ($assignResult['conflicts'] as $conflict) {
                $conflictMsg .= $conflict['coordinatorName'] . " has conflict with '{$conflict['eventName']}' ";
            }
            return ['success' => true, 'warning' => $conflictMsg];
        }
    }
    
    return ['success' => $success, 'message' => $success ? 'Event updated' : 'Update failed'];
}

// Delete event
function handleDeleteEvent($eventId) {
    $event = getEventById($eventId);
    if (!$event) {
        return ['success' => false, 'message' => 'Event not found'];
    }

    if (!canUserEditEvent($eventId, $_SESSION['userId'])) {
        return ['success' => false, 'message' => 'Permission denied'];
    }

    // Delete image file if exists
    if (!empty($event['eventImage']) && file_exists('../' . $event['eventImage'])) {
        unlink('../' . $event['eventImage']);
    }

    $success = deleteEvent($eventId);
    
    return ['success' => $success, 'message' => $success ? 'Event deleted successfully' : 'Failed to delete event'];
}

// Check if event is over
function isEventOver($event) {
    $eventDateTime = $event['endDate'] . ' ' . $event['endTime'];
    return strtotime($eventDateTime) < time();
}


function getUserRoleSafe() {
    if (isset($_SESSION['userRole'])) {
        return $_SESSION['userRole'];
    }
    
    if (isset($_SESSION['userId'])) {
        return getUserRole($_SESSION['userId']);
    }
    
    return null;
}
?>