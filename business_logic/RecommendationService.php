<?php
require_once __DIR__ . '/../data_access/EventData.php';
require_once __DIR__ . '/../data_access/UserData.php';

class RecommendationService
{
    private $eventData;
    private $userData;
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
        $this->eventData = new EventData($conn);
        $this->userData = new UserData($conn);
    }

    public function getRecommendedEventsForVolunteer($userId)
    {
        // Get volunteer details
        $user = $this->userData->getUserById($userId);

        if (!$user) {
            return [
                "success" => false,
                "message" => "User not found"
            ];
        }

        // Get user's skill IDs
        $userSkillIds = [];
        if (!empty($user['skillIds'])) {
            $userSkillIds = explode(',', $user['skillIds']);
        }
        // Use first skill as primary (or you can loop through all)
        $primarySkill = !empty($userSkillIds) ? intval($userSkillIds[0]) : 0;

        // Get all upcoming events
        $events = $this->eventData->getAllUpcomingEventsRaw();

        if (empty($events)) {
            return [
                "success" => true,
                "events" => []
            ];
        }

        // Prepare features for ALL events (one API call with all events)
        $eventFeatures = [];
        foreach ($events as $event) {
            $eventFeatures[] = [
                "userId" => intval($userId),
                "eventId" => intval($event['eventId']),
                "user_location" => $user['location'] ?? '',
                "event_location" => $event['location'] ?? '',
                "requiredSkillId" => intval($event['requiredSkillId'] ?? 0),
                "volunteer_skill" => $primarySkill, // User's primary skill
                "category" => $event['category'] ?? '',
                "attended" => $this->hasUserAttendedEvent($userId, $event['eventId']) ? 1 : 0
            ];
        }

        // Send ALL events to Flask at once
        $payload = [
            "events" => $eventFeatures  // Send array of events
        ];

        // Flask API call
        $ch = curl_init("http://127.0.0.1:5000/recommend_events");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                "success" => false,
                "message" => "Flask connection error: " . $error,
                "error_type" => "curl"
            ];
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                "success" => false,
                "message" => "Flask returned HTTP $httpCode",
                "error_type" => "http"
            ];
        }

        $result = json_decode($response, true);

        // Check if Flask returned scores
        if (!isset($result['scores'])) {
            return [
                "success" => false,
                "message" => "Flask response missing 'scores' field",
                "error_type" => "format",
                "response" => $result
            ];
        }

        // Check if number of scores matches number of events
        if (count($result['scores']) !== count($events)) {
            return [
                "success" => false,
                "message" => "Score count mismatch: Flask returned " . count($result['scores']) . 
                             " scores but there are " . count($events) . " events",
                "error_type" => "mismatch"
            ];
        }

        // Attach scores to events and build new array
        $scoredEvents = [];
        foreach ($events as $index => $event) {
            $score = $result['scores'][$index] ?? 0;
            $matchPercentage = round($score * 100);
            $userHasSkill = !empty($event['requiredSkillId']) && in_array($event['requiredSkillId'], $userSkillIds);
            
            $scoredEvents[] = [
                'eventId' => $event['eventId'],
                'eventName' => $event['eventName'],
                'eventDescription' => $event['eventDescription'] ?? '',
                'category' => $event['category'] ?? '',
                'location' => $event['location'] ?? '',
                'googleMapLink' => $event['googleMapLink'] ?? '',
                'startDate' => $event['startDate'],
                'endDate' => $event['endDate'],
                'startTime' => $event['startTime'],
                'endTime' => $event['endTime'],
                'maxVolunteers' => $event['maxVolunteers'] ?? 0,
                'requiredSkillId' => $event['requiredSkillId'] ?? null,
                'skillName' => $event['skillName'] ?? null,
                'eventImage' => $event['eventImage'] ?? null,
                'joinedCount' => $event['joinedCount'] ?? 0,
                'score' => $score,
                'match_percentage' => $matchPercentage,
                'user_has_skill' => $userHasSkill
            ];
        }

        // Sort by score (highest first)
        usort($scoredEvents, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // 🔥 FILTER: Only show events with match percentage >= 50% (or adjust as needed)
        $minMatchThreshold = 50; // You can change this value
        $suitableEvents = array_filter($scoredEvents, function($event) use ($minMatchThreshold) {
            return $event['match_percentage'] >= $minMatchThreshold;
        });

        // Sort again after filtering (just to be safe)
        $suitableEvents = array_values($suitableEvents); // Re-index array

        return [
            "success" => true,
            "events" => $suitableEvents,
            "total_events" => count($scoredEvents),
            "suitable_count" => count($suitableEvents),
            "threshold" => $minMatchThreshold,
            "user_skills" => $user['skillNames'] ?? 'No skills',
            "skill_count" => count($userSkillIds)
        ];
    }

    private function hasUserAttendedEvent($userId, $eventId)
    {
        $sql = "SELECT COUNT(*) as count FROM attendance 
                WHERE userId = ? AND eventId = ? AND status = 'Present'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return ($result['count'] ?? 0) > 0;
    }
}
?>