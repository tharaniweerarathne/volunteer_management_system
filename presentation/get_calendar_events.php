<?php

session_start();
require_once __DIR__ . '/../business_logic/calendarLogic.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userId']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$calendarLogic = new CalendarLogic();


$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$data = $calendarLogic->getCalendarViewData($_SESSION['userId'], $_SESSION['role']);

echo json_encode($data);
?>