<?php
// get_result.php ---> presentation folder

// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['userId'];
$role = $_SESSION['role'];

// Check if user has permission (Admin, Organizer, or Coordinator)
if (!in_array($role, ['Admin', 'Organizer', 'Coordinator'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

require_once __DIR__ . "/../data_access/resultsData.php";

// Get result ID from query parameter
$resultId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($resultId <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid result ID']);
    exit();
}

$resultsData = new ResultsData();
$result = $resultsData->getResultById($resultId);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Result not found']);
    exit();
}

// Check if user has permission to view this result
// Admin can view all, Organizers/Coordinators can only view their own
if ($role !== 'Admin' && $result['addedById'] != $userId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

// Return result data as JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'result' => $result
]);
?>