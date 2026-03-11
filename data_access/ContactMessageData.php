<?php
require_once __DIR__ . '/../data_access/db.php';

class ContactMessageData {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // insert new contact message
    public function createMessage($senderName, $senderEmail, $message) {
        $stmt = $this->conn->prepare("INSERT INTO contact_messages (senderName, senderEmail, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $senderName, $senderEmail, $message);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // get all messages with reply details
    public function getAllMessages() {
        $query = "SELECT 
                    cm.messageId,
                    cm.senderName,
                    cm.senderEmail,
                    cm.message,
                    cm.status,
                    cm.createdAt,
                    cm.replyMessage,
                    cm.repliedAt,
                    u.name as repliedByName,
                    u.role as repliedByRole
                FROM contact_messages cm
                LEFT JOIN users u ON cm.repliedBy = u.userId
                ORDER BY cm.createdAt DESC";
        
        $result = $this->conn->query($query);
        $messages = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
        }
        
        return $messages;
    }

    // get single message by ID
    public function getMessageById($messageId) {
        $stmt = $this->conn->prepare("SELECT 
                    cm.*,
                    u.name as repliedByName,
                    u.role as repliedByRole
                FROM contact_messages cm
                LEFT JOIN users u ON cm.repliedBy = u.userId
                WHERE cm.messageId = ?");
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // update message with reply
    public function replyToMessage($messageId, $repliedBy, $replyMessage) {
        $stmt = $this->conn->prepare("UPDATE contact_messages 
                SET status = 'replied', 
                    repliedBy = ?, 
                    replyMessage = ?, 
                    repliedAt = NOW() 
                WHERE messageId = ?");
        $stmt->bind_param("isi", $repliedBy, $replyMessage, $messageId);
        
        return $stmt->execute();
    }

    // get message count by status
    public function getMessageCountByStatus($status) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
}
?>