<?php
// business_logic/StatisticsLogic.php
require_once '../data_access/StatisticsData.php';

class StatisticsLogic {
    private $statisticsData;
    
    public function __construct() {
        $this->statisticsData = new StatisticsData();
    }
    
    // Get all statistics for the dashboard
    public function getAllStatistics() {
        $totalVolunteers = $this->statisticsData->getTotalVolunteers();
        $activeVolunteers = $this->statisticsData->getActiveVolunteers();
        $completedEvents = $this->statisticsData->getCompletedEvents();
        $upcomingEvents = $this->statisticsData->getUpcomingEvents();
        $totalEvents = $this->statisticsData->getTotalEvents();
        $uniqueLocations = $this->statisticsData->getUniqueLocations();
        $totalHours = $this->statisticsData->getTotalVolunteerHours();
        $categoryStats = $this->statisticsData->getEventsByCategory();
        
        // Format numbers (add + sign and round if needed)
        return [
            'volunteers' => [
                'total' => $totalVolunteers,
                'display' => '+' . number_format($totalVolunteers),
                'active' => $activeVolunteers,
                'active_percentage' => $totalVolunteers > 0 ? round(($activeVolunteers / $totalVolunteers) * 100) : 0
            ],
            'events' => [
                'completed' => $completedEvents,
                'display_completed' => '+' . $completedEvents . ' Events',
                'upcoming' => $upcomingEvents,
                'total' => $totalEvents
            ],
            'locations' => [
                'total' => $uniqueLocations,
                'display' => '+' . $uniqueLocations . ' Areas'
            ],
            'hours' => [
                'total' => $totalHours,
                'display' => number_format($totalHours) . '+'
            ],
            'categories' => $categoryStats
        ];
    }
    
    // Get simplified stats for the statistics section
    public function getSimpleStats() {
        $totalVolunteers = $this->statisticsData->getTotalVolunteers();
        $completedEvents = $this->statisticsData->getCompletedEvents();
        $uniqueLocations = $this->statisticsData->getUniqueLocations();
        
        return [
            [
                'number' => '+' . number_format($totalVolunteers),
                'label' => 'Volunteers'
            ],
            [
                'number' => '+' . $completedEvents . ' Events',
                'label' => 'Completed'
            ],
            [
                'number' => '+' . $uniqueLocations . ' Areas',
                'label' => 'Served'
            ]
        ];
    }
    
    // Get detailed stats with additional information
    public function getDetailedStats() {
        $totalVolunteers = $this->statisticsData->getTotalVolunteers();
        $activeVolunteers = $this->statisticsData->getActiveVolunteers();
        $completedEvents = $this->statisticsData->getCompletedEvents();
        $totalEvents = $this->statisticsData->getTotalEvents();
        $uniqueLocations = $this->statisticsData->getUniqueLocations();
        $totalHours = $this->statisticsData->getTotalVolunteerHours();
        
        return [
            [
                'icon' => 'ri-group-line',
                'number' => number_format($totalVolunteers),
                'label' => 'Total Volunteers',
                'subtext' => $activeVolunteers . ' active volunteers',
                'color' => '#3498db'
            ],
            [
                'icon' => 'ri-calendar-check-line',
                'number' => $completedEvents,
                'label' => 'Events Completed',
                'subtext' => $totalEvents . ' total events',
                'color' => '#2ecc71'
            ],
            [
                'icon' => 'ri-map-pin-line',
                'number' => $uniqueLocations,
                'label' => 'Areas Served',
                'subtext' => 'Across different locations',
                'color' => '#e74c3c'
            ],
            [
                'icon' => 'ri-time-line',
                'number' => number_format($totalHours),
                'label' => 'Volunteer Hours',
                'subtext' => 'Total contribution',
                'color' => '#f39c12'
            ]
        ];
    }
}
?>