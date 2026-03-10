<?php

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/CSVExportLogic.php";


if (!isset($_GET['type'])) {
    die("Export type not specified.");
}

$exportType = $_GET['type'];
$csvLogic = new CSVExportLogic($conn);


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


    case 'events_filtered':
        
        $filters = [
            'startDate' => $_GET['startDate'] ?? '',
            'endDate'   => $_GET['endDate'] ?? '',
            'category'  => $_GET['category'] ?? ''
        ];
        
        $csvLogic->exportFilteredEventsToCSV($filters);
        break;

    case 'organizers':
        $csvLogic->exportOrganizersToCSV();
        break;
        
    default:
        die("Invalid export type.");
}