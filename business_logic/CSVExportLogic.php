<?php

require_once __DIR__ . "/../data_access/RegistrationData.php";

class CSVExportLogic {
    private $registrationData;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->registrationData = new RegistrationData($conn);
    }
    
    // ==================== csv file export methods ====================
    
    // generate CSV for volunteers
    public function exportVolunteersToCSV() {
        $volunteers = $this->registrationData->getAllVolunteersForExport();
        
        if (empty($volunteers)) {
            return ["success" => false, "message" => "No volunteers found to export."];
        }
        
        return $this->generateCSV($volunteers, 'volunteers_' . date('Y-m-d_H-i-s') . '.csv');
    }
    
    // generate CSV for coordinators
    public function exportCoordinatorsToCSV() {
        $coordinators = $this->registrationData->getAllCoordinatorsForExport();
        
        if (empty($coordinators)) {
            return ["success" => false, "message" => "No coordinators found to export."];
        }
        
        return $this->generateCSV($coordinators, 'coordinators_' . date('Y-m-d_H-i-s') . '.csv');
    }
    
    // generate CSV for all users
    public function exportAllUsersToCSV() {
        $users = $this->registrationData->getAllUsersForExport();
        
        if (empty($users)) {
            return ["success" => false, "message" => "No users found to export."];
        }
        
        return $this->generateCSV($users, 'all_users_' . date('Y-m-d_H-i-s') . '.csv');
    }
    
    
    // generic export method - can be used for any data
    public function exportCustomDataToCSV($data, $filename) {
        if (empty($data)) {
            return ["success" => false, "message" => "No data found to export."];
        }
        
        return $this->generateCSV($data, $filename);
    }

    // export events with filters applied
public function exportFilteredEventsToCSV($filters = []) {
    // get filtered events
    $events = $this->registrationData->getAllEventsForExport($filters);

    if (empty($events)) {
        return ["success" => false, "message" => "No events found for the selected filters."];
    }

    // filename with filter tag
    $filename = 'filtered_events_' . date('Y-m-d_H-i-s') . '.csv';

    return $this->generateCSV($events, $filename);
}

    
    // ==================== private helper methods ====================
    
    // private method to generate CSV file
private function generateCSV($data, $filename) { 
    try { 
        // set headers for download 
        header('Content-Type: text/csv; charset=utf-8'); 
        header('Content-Disposition: attachment; filename="' . $filename . '"'); 
        header('Pragma: no-cache'); 
        header('Expires: 0'); 
         
        // open output stream 
        $output = fopen('php://output', 'w'); 
         
        // add BOM for UTF-8 (helps with Excel) 
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 
         
        // add column headers 
        if (!empty($data)) { 
            fputcsv($output, array_keys($data[0])); 
        } 
         
        // add data rows with date formatting
        foreach ($data as $row) {
            // Format dates and times to prevent #### in Excel
            foreach ($row as $key => $value) {
                // Handle date fields
if (in_array($key, ['startDate', 'endDate', 'createdAt']) && !empty($value)) {
    if ($value !== '0000-00-00' && $value !== '0000-00-00 00:00:00') {
        // Add leading space to force text format in Excel
        $row[$key] = ' ' . date('Y-m-d', strtotime($value));
    } else {
        $row[$key] = '';
    }
}

                
                // Handle time fields
                if (in_array($key, ['startTime', 'endTime']) && !empty($value)) {
                    // Format as HH:MM:SS
                    $row[$key] = date('H:i:s', strtotime($value));
                }
                
                // Handle datetime fields
                if (stripos($key, 'datetime') !== false && !empty($value)) {
                    $row[$key] = date('Y-m-d H:i:s', strtotime($value));
                }
            }
            
            fputcsv($output, $row); 
        } 
         
        fclose($output); 
        exit(); 
         
    } catch (Exception $e) { 
        return ["success" => false, "message" => "Failed to generate CSV: " . $e->getMessage()]; 
    } 
} 
 
// remove sensitive fields from data before export 
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