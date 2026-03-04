<?php
require_once '../business_logic/RecommendationService.php';
require_once '../business_logic/PredictionService.php';
require_once '../data_access/EventData.php';
require_once '../data_access/UserData.php';

session_start();

class Chatbot {
    private $recommendationService;
    private $predictionService;
    private $eventData;
    private $userData;
    private $userId;
    private $conn;
    
    public function __construct($userId) {
        global $conn;
        $this->conn = $conn;
        $this->userId = $userId;
        $this->recommendationService = new RecommendationService();
        $this->predictionService = new PredictionService();
        $this->eventData = new EventData($conn);
        $this->userData = new UserData($conn);
    }
    
    public function processMessage($message) {
        $message = strtolower(trim($message));
        
        // Get user data for personalization
        $user = $this->userData->getUserById($this->userId);
        $userSkills = !empty($user['skillNames']) ? explode(', ', $user['skillNames']) : [];
        
        // Detect intent
        if ($this->contains($message, ['hi', 'hello', 'hey', 'greetings'])) {
            return $this->greetUser($user);
        }
        elseif ($this->contains($message, ['recommend', 'suggest', 'show me events', 'what events'])) {
            return $this->getRecommendations();
        }
        elseif ($this->contains($message, ['predict', 'attendance', 'how many', 'will people come'])) {
            return $this->handlePrediction($message);
        }
        elseif ($this->contains($message, ['skill', 'need', 'require', 'matching'])) {
            return $this->findEventsBySkill($message, $userSkills);
        }
        elseif ($this->contains($message, ['location', 'near', 'around', 'close'])) {
            return $this->findEventsByLocation($message, $user);
        }
        elseif ($this->contains($message, ['this weekend', 'tomorrow', 'today', 'next week'])) {
            return $this->findEventsByDate($message);
        }
        elseif ($this->contains($message, ['help', 'what can you do', 'support'])) {
            return $this->getHelp();
        }
        elseif ($this->contains($message, ['bye', 'goodbye', 'see you'])) {
            return $this->sayGoodbye();
        }
        else {
            return $this->handleUnknown();
        }
    }
    
    private function contains($message, $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function greetUser($user) {
        $greetings = [
            "👋 Hello " . htmlspecialchars($user['name']) . "! How can I help you today?",
            "Hi there! I'm your AI assistant. Ask me about events, recommendations, or predictions!",
            "Hey " . htmlspecialchars($user['name']) . "! Ready to find some volunteering opportunities?"
        ];
        
        $quickActions = [
            ["text" => "🔍 Find events", "action" => "find events"],
            ["text" => "🎯 Get recommendations", "action" => "recommend me"],
            ["text" => "❓ Help", "action" => "help"]
        ];
        
        return [
            "type" => "greeting",
            "message" => $greetings[array_rand($greetings)],
            "quick_actions" => $quickActions
        ];
    }
    
    private function getRecommendations() {
        $result = $this->recommendationService->getRecommendedEventsForVolunteer($this->userId);
        
        if (!$result['success'] || empty($result['events'])) {
            return [
                "type" => "text",
                "message" => "😔 I couldn't find any recommendations right now. Try checking back later or browse all events!"
            ];
        }
        
        $response = "🎯 **Top Recommendations For You:**\n\n";
        $events = array_slice($result['events'], 0, 5); // Show top 5
        
        foreach ($events as $event) {
            $skillBadge = $event['user_has_skill'] ? "✅ You have this skill!" : "⚠️ Skill needed";
            $response .= "• **{$event['eventName']}** - {$event['match_percentage']}% match\n";
            $response .= "  📍 {$event['location']} | 📅 " . date('M d', strtotime($event['startDate'])) . "\n";
            $response .= "  🏷️ Required: {$event['skillName']} {$skillBadge}\n\n";
        }
        
        $response .= "Want to join any of these? Just say 'join [event name]'!";
        
        return [
            "type" => "recommendations",
            "message" => $response,
            "events" => $events
        ];
    }
    
    private function handlePrediction($message) {
        // Extract event name from message
        preg_match('/for (.*?)(\?|$)/i', $message, $matches);
        $eventName = $matches[1] ?? '';
        
        if (empty($eventName)) {
            return [
                "type" => "text",
                "message" => "Which event would you like me to predict attendance for? Please specify the event name."
            ];
        }
        
        // Find event by name
        $event = $this->findEventByName($eventName);
        
        if (!$event) {
            return [
                "type" => "text",
                "message" => "😕 I couldn't find an event called '{$eventName}'. Try being more specific!"
            ];
        }
        
        // Get prediction from your ML model
        $prediction = $this->predictionService->predictParticipation($event['eventId']);
        
        if (!$prediction['success']) {
            return [
                "type" => "text",
                "message" => "Sorry, I couldn't get a prediction right now. Please try again later."
            ];
        }
        
        $attendanceLevel = $prediction['prediction'] == 1 ? "HIGH 📈" : "MODERATE 📊";
        $confidence = isset($prediction['probability']) ? round($prediction['probability'] * 100) . "%" : "N/A";
        
        $spotsLeft = $event['maxVolunteers'] - ($event['joinedCount'] ?? 0);
        
        $response = "📊 **Attendance Prediction for {$event['eventName']}**\n\n";
        $response .= "• Expected attendance: **{$attendanceLevel}**\n";
        $response .= "• Model confidence: {$confidence}\n";
        $response .= "• Current spots: {$spotsLeft} of {$event['maxVolunteers']} left\n\n";
        
        if ($prediction['prediction'] == 1) {
            $response .= "✨ This event is predicted to have good attendance! Join soon before spots fill up!";
        } else {
            $response .= "💡 This event might need more promotion. Share it with friends!";
        }
        
        return [
            "type" => "prediction",
            "message" => $response,
            "event" => $event,
            "prediction" => $prediction
        ];
    }
    
    private function findEventsBySkill($message, $userSkills) {
        // Extract skill from message
        preg_match('/skill (.*?)(\?|$)/i', $message, $matches);
        $requestedSkill = $matches[1] ?? '';
        
        $allEvents = $this->eventData->getAllUpcomingEventsRaw();
        $matchingEvents = [];
        
        foreach ($allEvents as $event) {
            if (!empty($event['skillName'])) {
                if (!empty($requestedSkill) && stripos($event['skillName'], $requestedSkill) !== false) {
                    $matchingEvents[] = $event;
                } elseif (empty($requestedSkill) && in_array($event['skillName'], $userSkills)) {
                    $matchingEvents[] = $event;
                }
            }
        }
        
        if (empty($matchingEvents)) {
            return [
                "type" => "text",
                "message" => "😕 I couldn't find any events matching that skill. Try a different skill or browse all events!"
            ];
        }
        
        $response = "🔍 **Events ";
        $response .= !empty($requestedSkill) ? "requiring '{$requestedSkill}' skill" : "matching your skills";
        $response .= ":**\n\n";
        
        foreach (array_slice($matchingEvents, 0, 5) as $event) {
            $hasSkill = in_array($event['skillName'], $userSkills);
            $skillBadge = $hasSkill ? "✅ You have this!" : "";
            
            $response .= "• **{$event['eventName']}**\n";
            $response .= "  📍 {$event['location']} | 📅 " . date('M d', strtotime($event['startDate'])) . "\n";
            $response .= "  🏷️ Required: {$event['skillName']} {$skillBadge}\n\n";
        }
        
        return [
            "type" => "events",
            "message" => $response,
            "events" => $matchingEvents
        ];
    }
    
    private function findEventsByLocation($message, $user) {
        // Extract location from message
        preg_match('/near (.*?)(\?|$)/i', $message, $matches);
        $location = $matches[1] ?? $user['location'] ?? '';
        
        if (empty($location)) {
            return [
                "type" => "text",
                "message" => "Which location should I search near? (e.g., 'near Colombo')"
            ];
        }
        
        $allEvents = $this->eventData->getAllUpcomingEventsRaw();
        $nearbyEvents = [];
        
        foreach ($allEvents as $event) {
            if (!empty($event['location']) && stripos($event['location'], $location) !== false) {
                $nearbyEvents[] = $event;
            }
        }
        
        if (empty($nearbyEvents)) {
            return [
                "type" => "text",
                "message" => "😕 I couldn't find any events near '{$location}'. Try a different location!"
            ];
        }
        
        $response = "📍 **Events near {$location}:**\n\n";
        
        foreach (array_slice($nearbyEvents, 0, 5) as $event) {
            $response .= "• **{$event['eventName']}**\n";
            $response .= "  📍 {$event['location']} | 📅 " . date('M d', strtotime($event['startDate'])) . "\n";
            $response .= "  🏷️ {$event['category']} | 👥 " . ($event['maxVolunteers'] - ($event['joinedCount'] ?? 0)) . " spots\n\n";
        }
        
        return [
            "type" => "events",
            "message" => $response,
            "events" => $nearbyEvents
        ];
    }
    
    private function findEventsByDate($message) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $weekend = date('Y-m-d', strtotime('next Saturday'));
        
        $allEvents = $this->eventData->getAllUpcomingEventsRaw();
        $filteredEvents = [];
        
        foreach ($allEvents as $event) {
            $eventDate = $event['startDate'];
            
            if (strpos($message, 'today') !== false && $eventDate == $today) {
                $filteredEvents[] = $event;
            } elseif (strpos($message, 'tomorrow') !== false && $eventDate == $tomorrow) {
                $filteredEvents[] = $event;
            } elseif (strpos($message, 'weekend') !== false && date('N', strtotime($eventDate)) >= 6) {
                $filteredEvents[] = $event;
            }
        }
        
        if (empty($filteredEvents)) {
            return [
                "type" => "text",
                "message" => "😕 I couldn't find any events for that time period. Try checking the full calendar!"
            ];
        }
        
        $period = strpos($message, 'today') ? 'today' : (strpos($message, 'tomorrow') ? 'tomorrow' : 'this weekend');
        
        $response = "📅 **Events {$period}:**\n\n";
        
        foreach (array_slice($filteredEvents, 0, 5) as $event) {
            $response .= "• **{$event['eventName']}**\n";
            $response .= "  📍 {$event['location']} | 🕒 " . date('h:i A', strtotime($event['startTime'])) . "\n";
            $response .= "  🏷️ {$event['category']} | 👥 " . ($event['maxVolunteers'] - ($event['joinedCount'] ?? 0)) . " spots\n\n";
        }
        
        return [
            "type" => "events",
            "message" => $response,
            "events" => $filteredEvents
        ];
    }
    
    private function getHelp() {
        $help = "🤖 **I can help you with:**\n\n";
        $help .= "• 🔍 **Find events** - 'find events this weekend'\n";
        $help .= "• 🎯 **Get recommendations** - 'recommend events for me'\n";
        $help .= "• 📊 **Predict attendance** - 'predict attendance for Beach Cleanup'\n";
        $help .= "• 🏷️ **Search by skill** - 'events needing First Aid'\n";
        $help .= "• 📍 **Search by location** - 'events near Colombo'\n";
        $help .= "• 📅 **Search by date** - 'events tomorrow'\n\n";
        $help .= "Just type your question naturally!";
        
        return [
            "type" => "help",
            "message" => $help
        ];
    }
    
    private function sayGoodbye() {
        $goodbyes = [
            "👋 Goodbye! Come back anytime to find more events!",
            "See you later! Hope you found some great volunteering opportunities!",
            "Bye! Remember, I'm always here to help you find events!"
        ];
        
        return [
            "type" => "goodbye",
            "message" => $goodbyes[array_rand($goodbyes)]
        ];
    }
    
    private function handleUnknown() {
        return [
            "type" => "text",
            "message" => "😕 I'm not sure I understand. Try asking about events, recommendations, or predictions. Type 'help' to see what I can do!"
        ];
    }
    
    private function findEventByName($name) {
        $allEvents = $this->eventData->getAllUpcomingEventsRaw();
        
        foreach ($allEvents as $event) {
            if (stripos($event['eventName'], $name) !== false) {
                return $event;
            }
        }
        
        return null;
    }
}

// API endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['userId'] ?? $_SESSION['userId'] ?? null;
    $message = $data['message'] ?? '';
    
    if (!$userId || !$message) {
        echo json_encode(['error' => 'Missing userId or message']);
        exit;
    }
    
    $chatbot = new Chatbot($userId);
    $response = $chatbot->processMessage($message);
    
    echo json_encode($response);
}
?>