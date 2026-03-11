<?php
require_once 'StatisticsLogic.php';

class StatisticsFacade {

    private $statisticsLogic;

    public function __construct() {
        $this->statisticsLogic = new StatisticsLogic();
    }

    public function getDashboardStats() {
        return [
            'simple' => $this->statisticsLogic->getSimpleStats(),
            'detailed' => $this->statisticsLogic->getDetailedStats(),
            'all' => $this->statisticsLogic->getAllStatistics()
        ];
    }
}
?>