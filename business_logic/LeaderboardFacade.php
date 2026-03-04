<?php
require_once 'LeaderboardLogic.php';

class LeaderboardFacade {

    private $leaderboardLogic;

    public function __construct() {
        $this->leaderboardLogic = new LeaderboardLogic();
    }

    public function getPodiumLeaderboard() {
        return $this->leaderboardLogic->getPodiumData();
    }
}
?>