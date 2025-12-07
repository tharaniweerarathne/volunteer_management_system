<?php
// business_logic/MessageLogic.php

require_once __DIR__ . "/../data_access/MessageData.php";

class MessageLogic {
    private $messageData;
    
    public function __construct($conn) {
        $this->messageData = new MessageData($conn);
    }
    
    // Send message with validation
    public function sendMessage($senderId, $senderRole, $receiverIds, $subject, $message) {
        // Validate inputs
        if (empty($subject) || empty($message)) {
            return ["success" => false, "message" => "Subject and message are required"];
        }
        
        if (strlen($subject) > 255) {
            return ["success" => false, "message" => "Subject is too long (max 255 characters)"];
        }
        
        // Validate receiver IDs
        if (empty($receiverIds)) {
            return ["success" => false, "message" => "No recipients selected"];
        }
        
        // Check permissions based on role
        foreach ($receiverIds as $receiverId) {
            if (!$this->canSendTo($senderRole, $receiverId)) {
                return ["success" => false, "message" => "You don't have permission to send to this recipient"];
            }
        }
        
        // Send to multiple recipients
        if ($this->messageData->sendToMultiple($senderId, $receiverIds, $subject, $message)) {
            return ["success" => true, "message" => "Message sent successfully"];
        } else {
            return ["success" => false, "message" => "Failed to send message"];
        }
    }
    
    // Check if sender can send to receiver
    private function canSendTo($senderRole, $receiverId) {
        // In a real implementation, you would check the receiver's role
        // For simplicity, allowing all communications as per requirements
        // Admin can send to everyone
        // Volunteer can send to Admin/Coordinator
        // Coordinator can send to Volunteer/Admin
        
        return true; // Allowing all for now, can add specific rules later
    }
    
    // Get inbox messages for user
    public function getInbox($userId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $messages = $this->messageData->getMessagesForUser($userId, $perPage, $offset);
        $unreadCount = $this->messageData->getUnreadCount($userId);
        
        return [
            "success" => true,
            "messages" => $messages,
            "unreadCount" => $unreadCount,
            "page" => $page,
            "hasMore" => count($messages) === $perPage
        ];
    }
    
    // Get sent messages
    public function getSentMessages($userId, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $messages = $this->messageData->getSentMessages($userId, $perPage, $offset);
        
        return [
            "success" => true,
            "messages" => $messages,
            "page" => $page,
            "hasMore" => count($messages) === $perPage
        ];
    }
    
    // Get single message
    public function getMessage($messageId, $userId) {
        $message = $this->messageData->getMessageById($messageId, $userId);
        
        if (!$message) {
            return ["success" => false, "message" => "Message not found or access denied"];
        }
        
        // Mark as read if receiver is viewing
        if ($message['receiverId'] == $userId && !$message['isRead']) {
            $this->messageData->markAsRead($messageId, $userId);
        }
        
        return ["success" => true, "data" => $message];
    }
    
    // Get available recipients based on user role
    public function getAvailableRecipients($userId, $userRole) {
        $users = $this->messageData->getAllUsersExcept($userId);
        
        // Filter based on role permissions
        $filteredUsers = [];
        foreach ($users as $user) {
            if ($this->canSendBasedOnRole($userRole, $user['role'])) {
                $filteredUsers[] = $user;
            }
        }
        
        return ["success" => true, "users" => $filteredUsers];
    }
    
    // Role-based permission check
    private function canSendBasedOnRole($senderRole, $receiverRole) {
        $rules = [
            'Admin' => ['Volunteer', 'Coordinator', 'Admin'], // Admin can send to all
            'Volunteer' => ['Admin', 'Coordinator'], // Volunteer can send to Admin/Coordinator only
            'Coordinator' => ['Volunteer', 'Admin'] // Coordinator can send to Volunteer/Admin
        ];
        
        return isset($rules[$senderRole]) && in_array($receiverRole, $rules[$senderRole]);
    }
    
    // Get recipients for admin broadcast
    public function getBroadcastRecipients($userId, $userRole) {
        if ($userRole !== 'Admin') {
            return ["success" => false, "message" => "Only admins can broadcast"];
        }
        
        // Admin can broadcast to all volunteers
        $volunteerIds = $this->messageData->getAllVolunteers();
        $allUsers = $this->messageData->getAllUsersExcept($userId);
        
        // Filter volunteers
        $volunteers = array_filter($allUsers, function($user) {
            return $user['role'] === 'Volunteer';
        });
        
        return ["success" => true, "recipients" => array_values($volunteers)];
    }
    
    // Delete message
    public function deleteMessage($messageId, $userId) {
        if ($this->messageData->deleteMessage($messageId, $userId)) {
            return ["success" => true, "message" => "Message deleted"];
        } else {
            return ["success" => false, "message" => "Failed to delete message or access denied"];
        }
    }
}
?>