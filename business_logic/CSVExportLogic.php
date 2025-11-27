<?php

require_once __DIR__ . "/../data_access/RegistrationData.php";

class CSVExportLogic {
    private $registrationData;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->registrationData = new RegistrationData($conn);
    }
    
    // ==================== Export Methods ====================
    
    // Generate CSV for volunteers
    public function exportVolunteersToCSV() {
        $volunteers = $this->registrationData->getAllVolunteersForExport();
        
        if (empty($volunteers)) {
            return ["success" => false, "message" => "No volunteers found to export."];
        }
        
        return $this->generateCSV($volunteers, 'volunteers_' . date('Y-m-d_H-i-s') . '.csv');
    }
    
    // Generate CSV for coordinators
    public function exportCoordinatorsToCSV() {
        $coordinators = $this->registrationData->getAllCoordinatorsForExport();
        
        if (empty($coordinators)) {
            return ["success" => false, "message" => "No coordinators found to export."];
        }
        
        return $this->generateCSV($coordinators, 'coordinators_' . date('Y-m-d_H-i-s') . '.csv');
    }
    
    // Generate CSV for all users
    public function exportAllUsersToCSV() {
        $users = $this->registrationData->getAllUsersForExport();
        
        if (empty($users)) {
            return ["success" => false, "message" => "No users found to export."];
        }
        
        return $this->generateCSV($users, 'all_users_' . date('Y-m-d_H-i-s') . '.csv');
    }
    
    // Generate CSV for events
    public function exportEventsToCSV() {
        $events = $this->registrationData->getAllEventsForExport();
        
        if (empty($events)) {
            return ["success" => false, "message" => "No events found to export."];
        }
        
        return $this->generateCSV($events, 'events_' . date('Y-m-d_H-i-s') . '.csv');
    }
    
    // Generic export method - can be used for any data
    public function exportCustomDataToCSV($data, $filename) {
        if (empty($data)) {
            return ["success" => false, "message" => "No data found to export."];
        }
        
        return $this->generateCSV($data, $filename);
    }
    
    // ==================== Private Helper Methods ====================
    
    // Private method to generate CSV file
    private function generateCSV($data, $filename) {
        try {
            // Set headers for download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 (helps with Excel)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add column headers
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0]));
            }
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            
            fclose($output);
            exit();
            
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to generate CSV: " . $e->getMessage()];
        }
    }
    
    // Remove sensitive fields from data before export
    private function removeSensitiveFields($data, $fieldsToRemove = ['password', 'token', 'secret']) {
        $cleanedData = [];
        
        foreach ($data as $row) {
            $cleanedRow = [];
            foreach ($row as $key => $value) {
                if (!in_array(strtolower($key), $fieldsToRemove)) {
                    $cleanedRow[$key] = $value;
                }
            }
            $cleanedData[] = $cleanedRow;
        }
        
        return $cleanedData;
    }
}