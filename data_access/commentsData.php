<?php
// data_access/commentsData.php
require_once 'db.php';

class CommentsData {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getCommentsByResultId($resultId, $includeDeleted = false) {
        global $conn;
        
        // Removed u.profileImage from SELECT
        $sql = "SELECT c.*, u.name as userName, u.role as userRole,
                u.userId as commenterId,
                (SELECT COUNT(*) FROM result_comments rc WHERE rc.parentCommentId = c.commentId AND rc.isDeleted = 0) as replyCount
                FROM result_comments c
                LEFT JOIN users u ON c.userId = u.userId
                WHERE c.resultId = ? 
                AND c.parentCommentId IS NULL";
        
        if (!$includeDeleted) {
            $sql .= " AND c.isDeleted = 0";
        }
        
        $sql .= " ORDER BY c.createdAt DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);
        $stmt->execute();
        $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($comments as &$comment) {
            $comment['replies'] = $this->getCommentReplies($comment['commentId'], $includeDeleted);
        }
        
        return $comments;
    }
    
    private function getCommentReplies($commentId, $includeDeleted = false) {
        global $conn;
        
        // Removed u.profileImage from SELECT
        $sql = "SELECT c.*, u.name as userName, u.role as userRole,
                u.userId as commenterId
                FROM result_comments c
                LEFT JOIN users u ON c.userId = u.userId
                WHERE c.parentCommentId = ?";
        
        if (!$includeDeleted) {
            $sql .= " AND c.isDeleted = 0";
        }
        
        $sql .= " ORDER BY c.createdAt ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function addComment($resultId, $userId, $comment, $parentCommentId = null) {
        global $conn;
        
        $sql = "INSERT INTO result_comments (resultId, userId, comment, parentCommentId) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisi", $resultId, $userId, $comment, $parentCommentId);
        
        if ($stmt->execute()) {
            $commentId = $conn->insert_id;
            
            // Removed u.profileImage from SELECT
            $sql = "SELECT c.*, u.name as userName, u.role as userRole, 
                    u.userId as commenterId
                    FROM result_comments c
                    LEFT JOIN users u ON c.userId = u.userId
                    WHERE c.commentId = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $commentId);
            $stmt->execute();
            
            return $stmt->get_result()->fetch_assoc();
        }
        
        return false;
    }
    
    public function deleteComment($commentId, $userId) {
        global $conn;
        
        // Check if user owns the comment or is admin/organizer
        $comment = $this->getCommentById($commentId);
        
        if (!$comment) {
            return ['success' => false, 'message' => 'Comment not found'];
        }
        
        $userRole = $this->getUserRole($userId);
        
        // Allow deletion if: user owns comment OR user is admin/organizer
        if ($comment['userId'] != $userId && !in_array($userRole, ['Admin', 'Organizer'])) {
            return ['success' => false, 'message' => 'Permission denied'];
        }
        
        $sql = "UPDATE result_comments SET isDeleted = 1 WHERE commentId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $commentId);
        
        return [
            'success' => $stmt->execute(),
            'message' => $stmt->execute() ? 'Comment deleted' : 'Failed to delete comment'
        ];
    }
    
    private function getCommentById($commentId) {
        global $conn;
        
        $sql = "SELECT * FROM result_comments WHERE commentId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    private function getUserRole($userId) {
        global $conn;
        
        $sql = "SELECT role FROM users WHERE userId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['role'] : null;
    }
    
    public function getReactionCounts($resultId) {
        global $conn;
        
        $sql = "SELECT 
                SUM(CASE WHEN reactionType = 'like' THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN reactionType = 'dislike' THEN 1 ELSE 0 END) as dislikes
                FROM result_reactions 
                WHERE resultId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getUserReaction($resultId, $userId) {
        global $conn;
        
        $sql = "SELECT reactionType FROM result_reactions 
                WHERE resultId = ? AND userId = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $resultId, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['reactionType'] : null;
    }
    
    public function addReaction($resultId, $userId, $reactionType) {
        global $conn;
        
        $existing = $this->getUserReaction($resultId, $userId);
        
        if ($existing) {
            if ($existing === $reactionType) {
                $sql = "DELETE FROM result_reactions WHERE resultId = ? AND userId = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $resultId, $userId);
                return $stmt->execute() ? 'removed' : false;
            } else {
                $sql = "UPDATE result_reactions SET reactionType = ? 
                        WHERE resultId = ? AND userId = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $reactionType, $resultId, $userId);
                return $stmt->execute() ? 'updated' : false;
            }
        } else {
            $sql = "INSERT INTO result_reactions (resultId, userId, reactionType) 
                    VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $resultId, $userId, $reactionType);
            return $stmt->execute() ? 'added' : false;
        }
    }
    
    public function getCommentCount($resultId) {
        global $conn;
        
        $sql = "SELECT COUNT(*) as count FROM result_comments 
                WHERE resultId = ? AND isDeleted = 0";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $resultId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }
}
?>