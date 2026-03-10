<?php

require_once '../data_access/commentsData.php';

class CommentsLogic {
    private $commentsData;
    
    public function __construct() {
        $this->commentsData = new CommentsData();
    }
    
    public function handleAddComment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        
        if (!isset($_SESSION['userId'])) {
            return ['success' => false, 'message' => 'Please login to add feedback'];
        }
        
        $required = ['resultId', 'comment'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }
        
        $comment = trim($_POST['comment']);
        if (strlen($comment) < 3) {
            return ['success' => false, 'message' => 'Feedback must be at least 3 characters'];
        }
        
        if (strlen($comment) > 1000) {
            return ['success' => false, 'message' => 'Feedback is too long (max 1000 characters)'];
        }
        
        $parentCommentId = !empty($_POST['parentCommentId']) ? $_POST['parentCommentId'] : null;
        
        $newComment = $this->commentsData->addComment(
            $_POST['resultId'],
            $_SESSION['userId'],
            $comment,
            $parentCommentId
        );
        
        if ($newComment) {
            return [
                'success' => true,
                'message' => 'Feedback added successfully',
                'comment' => $newComment
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to add feedback'];
    }
    
    public function handleDeleteComment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        
        if (!isset($_SESSION['userId'])) {
            return ['success' => false, 'message' => 'Please login'];
        }
        
        if (empty($_POST['commentId'])) {
            return ['success' => false, 'message' => 'Comment ID is required'];
        }
        
        return $this->commentsData->deleteComment(
            $_POST['commentId'],
            $_SESSION['userId']
        );
    }
    
    public function handleReaction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        
        if (!isset($_SESSION['userId'])) {
            return ['success' => false, 'message' => 'Please login to react'];
        }
        
        $required = ['resultId', 'reactionType'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }
        
        $validReactions = ['like', 'dislike'];
        if (!in_array($_POST['reactionType'], $validReactions)) {
            return ['success' => false, 'message' => 'Invalid reaction type'];
        }
        
        $result = $this->commentsData->addReaction(
            $_POST['resultId'],
            $_SESSION['userId'],
            $_POST['reactionType']
        );
        
        if ($result) {
            $counts = $this->commentsData->getReactionCounts($_POST['resultId']);
            $userReaction = $this->commentsData->getUserReaction($_POST['resultId'], $_SESSION['userId']);
            
            return [
                'success' => true,
                'action' => $result,
                'counts' => $counts,
                'userReaction' => $userReaction
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to add reaction'];
    }
    
    public function getComments($resultId, $includeDeleted = false) {
        return $this->commentsData->getCommentsByResultId($resultId, $includeDeleted);
    }
    
    public function getReactionCounts($resultId) {
        return $this->commentsData->getReactionCounts($resultId);
    }
    
    public function getUserReaction($resultId, $userId) {
        return $this->commentsData->getUserReaction($resultId, $userId);
    }
    
    public function getCommentCount($resultId) {
        return $this->commentsData->getCommentCount($resultId);
    }
}
?>