<?php

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/CSVExportLogic.php";

// Check if export type is provided
if (!isset($_GET['type'])) {
    die("Export type not specified.");
}

$exportType = $_GET['type'];
$csvLogic = new CSVExportLogic($conn);

// Handle different export types
switch ($exportType) {
    case 'volunteers':
        $csvLogic->exportVolunteersToCSV();
        break;
        
    case 'coordinators':
        $csvLogic->exportCoordinatorsToCSV();
        break;
        
    case 'all_users':
        $csvLogic->exportAllUsersToCSV();
        break;
        
    case 'events':
        $csvLogic->exportEventsToCSV();
        break;
    
    // Add more export types here as needed
    // case 'registrations':
    //     $csvLogic->exportRegistrationsToCSV();
    //     break;
    
    // case 'donations':
    //     $csvLogic->exportDonationsToCSV();
    //     break;
        
    default:
        die("Invalid export type.");
}