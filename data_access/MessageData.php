<?php
// data_access/MessageData.php

class MessageData {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Send a new message
    public function sendMessage($senderId, $receiverId, $subject, $message) {
        $stmt = $this->conn->prepare("INSERT INTO messages (senderId, receiverId, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $senderId, $receiverId, $subject, $message);
        return $stmt->execute();
    }
    
    // Send to multiple recipients
    public function sendToMultiple($senderId, $receiverIds, $subject, $message) {
        $success = true;
        foreach ($receiverIds as $receiverId) {
            if (!$this->sendMessage($senderId, $receiverId, $subject, $message)) {
                $success = false;
            }
        }
        return $success;
    }
    
    // Get messages for a user
    public function getMessagesForUser($userId, $limit = 50, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT m.*, 
                   u_sender.name as senderName,
                   u_receiver.name as receiverName
            FROM messages m
            JOIN users u_sender ON m.senderId = u_sender.userId
            JOIN users u_receiver ON m.receiverId = u_receiver.userId
            WHERE m.receiverId = ?
            ORDER BY m.sentAt DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }
    
    // Get sent messages by a user
    public function getSentMessages($userId, $limit = 50, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT m.*, 
                   u_receiver.name as receiverName
            FROM messages m
            JOIN users u_receiver ON m.receiverId = u_receiver.userId
            WHERE m.senderId = ?
            ORDER BY m.sentAt DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }
    
    // Get unread message count
    public function getUnreadCount($userId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM messages WHERE receiverId = ? AND isRead = FALSE");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    // Mark message as read
    public function markAsRead($messageId, $userId) {
        $stmt = $this->conn->prepare("UPDATE messages SET isRead = TRUE WHERE messageId = ? AND receiverId = ?");
        $stmt->bind_param("ii", $messageId, $userId);
        return $stmt->execute();
    }
    
    // Get message by ID
    public function getMessageById($messageId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT m.*, 
                   u_sender.name as senderName,
                   u_sender.role as senderRole,
                   u_receiver.name as receiverName,
                   u_receiver.role as receiverRole
            FROM messages m
            JOIN users u_sender ON m.senderId = u_sender.userId
            JOIN users u_receiver ON m.receiverId = u_receiver.userId
            WHERE m.messageId = ? AND (m.senderId = ? OR m.receiverId = ?)
        ");
        $stmt->bind_param("iii", $messageId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Get all users except the current user
    public function getAllUsersExcept($currentUserId) {
        $stmt = $this->conn->prepare("
            SELECT userId, name, email, role 
            FROM users 
            WHERE userId != ? 
            ORDER BY role, name
        ");
        $stmt->bind_param("i", $currentUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }
    
    // Get all volunteers (for admin broadcast)
    public function getAllVolunteers() {
        $stmt = $this->conn->prepare("SELECT userId FROM users WHERE role = 'Volunteer'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $volunteerIds = [];
        while ($row = $result->fetch_assoc()) {
            $volunteerIds[] = $row['userId'];
        }
        return $volunteerIds;
    }
    
    // Get all admins
    public function getAllAdmins() {
        $stmt = $this->conn->prepare("SELECT userId FROM users WHERE role = 'Admin'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $adminIds = [];
        while ($row = $result->fetch_assoc()) {
            $adminIds[] = $row['userId'];
        }
        return $adminIds;
    }
    
    // Get all coordinators
    public function getAllCoordinators() {
        $stmt = $this->conn->prepare("SELECT userId FROM users WHERE role = 'Coordinator'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $coordinatorIds = [];
        while ($row = $result->fetch_assoc()) {
            $coordinatorIds[] = $row['userId'];
        }
        return $coordinatorIds;
    }
    
    // Delete message (only by sender or receiver)
    public function deleteMessage($messageId, $userId) {
        $stmt = $this->conn->prepare("DELETE FROM messages WHERE messageId = ? AND (senderId = ? OR receiverId = ?)");
        $stmt->bind_param("iii", $messageId, $userId, $userId);
        return $stmt->execute();
    }
}
?>