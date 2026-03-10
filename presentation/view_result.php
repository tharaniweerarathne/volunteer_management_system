<?php

session_start();

require_once __DIR__ . "/../data_access/db.php";
require_once __DIR__ . "/../business_logic/resultsLogic.php";
require_once __DIR__ . "/../business_logic/commentsLogic.php";


$resultId = isset($_GET['resultId']) ? intval($_GET['resultId']) : 0;

if ($resultId <= 0) {
    header("Location: past_events_main.php");
    exit();
}

$resultsLogic = new ResultsLogic();
$commentsLogic = new CommentsLogic();

// Get result data
$resultData = $resultsLogic->getResultById($resultId);
if (!$resultData['success']) {
    header("Location: past_events_main.php");
    exit();
}

$result = $resultData['result'];
if (!$result) {
    header("Location: past_events_main.php");
    exit();
}

// Check if result is approved or user is admin
$isApproved = ($result['approvalStatus'] === 'Approved');
$isAdmin = isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin');
$canView = $isApproved || $isAdmin;

if (!$canView) {
    header("Location: past_events_main.php");
    exit();
}

$isLoggedIn = isset($_SESSION['userId']);
$userRole = $_SESSION['role'] ?? null;
$currentUserId = $isLoggedIn ? $_SESSION['userId'] : null;

$comments = $commentsLogic->getComments($resultId);
$commentCount = $commentsLogic->getCommentCount($resultId);
$reactionCounts = $commentsLogic->getReactionCounts($resultId);
$userReaction = $isLoggedIn ? $commentsLogic->getUserReaction($resultId, $currentUserId) : null;

// Get all images for the result 
$resultImages = [];
for ($i = 1; $i <= 5; $i++) {
    $fieldName = $i == 1 ? 'resultImage' : 'resultImage' . $i;
    if (!empty($result[$fieldName])) {
        $resultImages[] = $result[$fieldName];
    }
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_comment':
            if (!$isLoggedIn) {
                $message = "Please login to add feedback";
            } else {
                $response = $commentsLogic->handleAddComment();
                if ($response['success']) {
                    $_SESSION['message'] = $response['message'];
                    header("Location: view_result.php?resultId=$resultId");
                    exit();
                } else {
                    $message = $response['message'];
                }
            }
            break;
            
        case 'delete_comment':
            if (!$isLoggedIn) {
                $message = "Please login first";
            } else {
                $response = $commentsLogic->handleDeleteComment();
                if ($response['success']) {
                    $_SESSION['message'] = $response['message'];
                    header("Location: view_result.php?resultId=$resultId");
                    exit();
                } else {
                    $message = $response['message'];
                }
            }
            break;
            
        case 'react':
            if (!$isLoggedIn) {
                echo json_encode(['success' => false, 'message' => 'Please login to react']);
                exit();
            }
            $response = $commentsLogic->handleReaction();
            echo json_encode($response);
            exit();
            break;
    }
}

$message = $_SESSION['message'] ?? $message;
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($result['resultTitle']) ?> - Unity Volunteers Trust</title>
    <link rel="stylesheet" href="../assets/css/a7.css">
    <link rel="icon" type="image/png" href="../assets/images/title.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery-bundle.min.css">
    <style>
        :root {
            --primary-color: #ff6b35;
            --secondary-color: #2d3436;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .result-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #1a1a1a 100%);
            color: white;
            padding: 100px 0 60px;
            position: relative;
            overflow: hidden;
        }
        
        
        .result-image-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            margin-top: -60px;
            z-index: 10;
            border: 5px solid white;
        }
        
        .result-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .result-image:hover {
            transform: scale(1.05);
        }
        
        /* Image Gallery Styles */
        .gallery-container {
            margin-top: 30px;
        }
        
        .gallery-title {
            margin-bottom: 25px;
            color: var(--secondary-color);
            font-weight: 700;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 12px;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            height: 180px;
        }
        
        .gallery-item:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
        }
        
        .gallery-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .gallery-item:hover .gallery-img {
            transform: scale(1.1);
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);
            display: flex;
            align-items: flex-end;
            padding: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-number {
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .gallery-main {
            position: relative;
        }
        
        .gallery-main-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
            z-index: 2;
        }
        
        .image-count-badge {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 2;
        }
        
        .gallery-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .gallery-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .gallery-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .gallery-btn-view {
            background: var(--primary-color);
            color: white;
        }
        
        .gallery-btn-download {
            background: #3b82f6;
            color: white;
        }
        
        /* Lightbox Styles */
        .lg-backdrop {
            background: rgba(0, 0, 0, 0.95) !important;
        }
        
        .lg-image {
            border-radius: 12px;
        }
        
        .lg-sub-html {
            color: white !important;
            font-size: 1.1rem !important;
        }
        
        .lg-toolbar {
            background: rgba(0, 0, 0, 0.7) !important;
        }
        
        /* Continue with the rest of your CSS styles... */
        .info-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
        }
        
        .info-card h3 {
            color: var(--secondary-color);
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 12px;
            margin-bottom: 25px;
            position: relative;
            font-weight: 700;
        }
        
        .badge-custom {
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 10px 10px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .category-badge {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .skill-badge {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
        }
        
        .date-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .organizer-card {
            background: linear-gradient(135deg, #fef3c7, #fbbf24);
            border-radius: 16px;
            padding: 28px;
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 30px;
            border: none;
            box-shadow: 0 10px 25px -5px rgba(251, 191, 36, 0.3);
        }
        
        .organizer-icon {
            background: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .reaction-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .reaction-btn {
            padding: 12px 28px;
            border-radius: 30px;
            border: 2px solid var(--border-color);
            background: white;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .reaction-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .reaction-btn.active-like {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(255, 107, 53, 0.05));
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.2);
        }
        
        .reaction-btn.active-dislike {
            border-color: #dc2626;
            color: #dc2626;
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(220, 38, 38, 0.05));
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        
        .comment-section {
            background: var(--light-bg);
            border-radius: 20px;
            padding: 50px;
            margin-top: 60px;
            border: 1px solid var(--border-color);
        }
        
        .comment-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .comment-card:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }
        
        .comment-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            background: linear-gradient(135deg, var(--primary-color), #ff8e53);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .comment-user {
            flex: 1;
        }
        
        .comment-user h6 {
            margin: 0 0 6px 0;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .comment-user small {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .comment-actions {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }
        
        .comment-action-btn {
            background: none;
            border: none;
            color: #6b7280;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .comment-action-btn:hover {
            color: var(--primary-color);
            background: rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }
        
        .reply-form {
            margin-top: 20px;
            padding-left: 72px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .reply-list {
            margin-top: 20px;
            padding-left: 72px;
        }
        
        .reply-card {
            background: #f8fafc;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .reply-card:hover {
            transform: translateX(5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }
        
        .floating-actions {
            position: fixed;
            bottom: 40px;
            right: 40px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 1000;
        }
        
        .action-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .action-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .action-btn:hover::before {
            width: 180px;
            height: 180px;
        }
        
        .action-btn:hover {
            transform: scale(1.1) translateY(-5px);
        }
        
        .back-btn {
            background: white;
            color: var(--secondary-color);
        }
        
        .comment-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .share-btn {
            background: linear-gradient(135deg, var(--primary-color), #ff8e53);
            color: white;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color), #ff8e53);
            color: white;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 8px;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 600;
        }
        
        .login-prompt {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            border: 2px dashed #cbd5e1;
            margin-bottom: 30px;
        }
        
        .login-prompt-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .char-counter {
            font-size: 0.85rem;
            display: block;
            text-align: right;
            margin-top: 8px;
            font-weight: 500;
        }
        
        .char-counter.text-danger {
            color: #dc2626 !important;
            font-weight: 700;
        }
        
        @media (max-width: 992px) {
            .result-header {
                padding: 80px 0 40px;
            }
            
            .result-image-container {
                margin-top: -40px;
            }
            
            .result-image {
                height: 350px;
            }
            
            .comment-section {
                padding: 30px;
            }
            
            .reply-form,
            .reply-list {
                padding-left: 20px;
            }
            
            .floating-actions {
                bottom: 25px;
                right: 25px;
            }
            
            .action-btn {
                width: 55px;
                height: 55px;
                font-size: 1.4rem;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .gallery-item {
                height: 150px;
            }
        }
        
        @media (max-width: 768px) {
            .result-header {
                padding: 60px 0 30px;
            }
            
            .result-image {
                height: 280px;
            }
            
            .info-card {
                padding: 24px;
            }
            
            .organizer-card {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .reaction-buttons {
                flex-direction: column;
                gap: 12px;
            }
            
            .reaction-btn {
                width: 100%;
                justify-content: center;
            }
            
            .comment-card {
                padding: 20px;
            }
            
            .comment-header {
                flex-wrap: wrap;
            }
            
            .floating-actions {
                bottom: 20px;
                right: 20px;
            }
            
            .action-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 12px;
            }
            
            .gallery-item {
                height: 120px;
            }
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-organizer {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-volunteer {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .empty-comments {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
        }
        
        .empty-comments-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        
        .empty-gallery {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
            margin-top: 20px;
        }
        
        .empty-gallery-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>


    <!-- Result Header -->
    <div class="result-header">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb" style="background: transparent; padding: 0;">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="past_events_main.php" class="text-white-50 text-decoration-none">Past Events</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Event Results</li>
                </ol>
            </nav>
            
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3"><?= htmlspecialchars($result['resultTitle']) ?></h1>
                    <p class="lead mb-4 opacity-75">Event: <?= htmlspecialchars($result['eventName'] ?? 'N/A') ?></p>
                    
                    <div class="d-flex flex-wrap gap-3">
                        <span class="badge-custom date-badge">
                            <i class="ri-calendar-line"></i>
                            <?= date('F j, Y', strtotime($result['resultDate'])) ?>
                        </span>
                        
                        <?php if (!empty($result['category'])): ?>
                            <span class="badge-custom category-badge">
                                <i class="ri-price-tag-line"></i>
                                <?= htmlspecialchars($result['category']) ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if (!empty($result['skillName'])): ?>
                            <span class="badge-custom skill-badge">
                                <i class="ri-tools-line"></i>
                                <?= htmlspecialchars($result['skillName']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="stats-card d-inline-block">
                        <div class="stats-number"><?= $commentCount ?></div>
                        <div class="stats-label">Total Feedback</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="container py-5">
        <?php if ($message): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="ri-information-line me-2"></i>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Left Column - Main Content -->
            <div class="col-lg-8">
                <!-- Main Image Gallery -->
                <div class="result-image-container mb-5 gallery-main" id="mainImageGallery">
                    <?php if (!empty($resultImages)): ?>
                        <?php 
                        $mainImage = $resultImages[0];
                        if (!str_starts_with($mainImage, '../') && !str_starts_with($mainImage, 'http')) {
                            $mainImage = '../' . $mainImage;
                        }
                        ?>
                        <img src="<?= htmlspecialchars($mainImage) ?>" 
                             alt="<?= htmlspecialchars($result['resultTitle']) ?>"
                             class="result-image"
                             data-lg-src="<?= htmlspecialchars($mainImage) ?>"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1200&h=600&fit=crop';">
                        <span class="gallery-main-badge">
                            <i class="ri-star-fill me-1"></i> Main Image
                        </span>
                        <?php if (count($resultImages) > 1): ?>
                            <span class="image-count-badge">
                                <i class="ri-image-line me-1"></i> +<?= count($resultImages) - 1 ?> more
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="result-image bg-light d-flex align-items-center justify-content-center">
                            <i class="ri-image-line" style="font-size: 5rem; color: #d1d5db;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Additional Images Gallery -->
                <?php if (count($resultImages) > 1): ?>
                    <div class="gallery-container">
                        <h3 class="gallery-title">
                            <i class="ri-gallery-line me-2"></i> Event Gallery
                            <span class="badge bg-primary ms-2"><?= count($resultImages) - 1 ?> photos</span>
                        </h3>
                        
                        <div class="gallery-grid" id="additionalGallery">
                            <?php for ($i = 1; $i < count($resultImages); $i++): ?>
                                <?php 
                                $imagePath = $resultImages[$i];
                                if (!str_starts_with($imagePath, '../') && !str_starts_with($imagePath, 'http')) {
                                    $imagePath = '../' . $imagePath;
                                }
                                ?>
                                <div class="gallery-item" 
                                     data-lg-src="<?= htmlspecialchars($imagePath) ?>"
                                     data-lg-download-url="<?= htmlspecialchars($imagePath) ?>">
                                    <img src="<?= htmlspecialchars($imagePath) ?>" 
                                         alt="Event Image <?= $i + 1 ?>" 
                                         class="gallery-img"
                                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542744095-fcf48d80b0fd?w=400&h=300&fit=crop&crop=entropy';">
                                    <div class="gallery-overlay">
                                        <div class="gallery-number">
                                            <i class="ri-image-line me-1"></i> Image <?= $i + 1 ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="gallery-actions">

                            <button class="gallery-btn gallery-btn-download" onclick="downloadAllImages()">
                                <i class="ri-download-line"></i> Download All
                            </button>
                        </div>
                    </div>
                <?php elseif (empty($resultImages)): ?>
                    <div class="empty-gallery">
                        <div class="empty-gallery-icon">
                            <i class="ri-image-line"></i>
                        </div>
                        <h4 class="mb-3">No Images Available</h4>
                        <p class="text-muted mb-4">No images were uploaded for this event result.</p>
                    </div>
                <?php endif; ?>

                <!-- Organizer Information -->
                <?php if (!empty($result['organizerName'])): ?>
                    <div class="organizer-card info-card mt-4">
                        <div class="organizer-icon">
                            <i class="ri-user-star-fill"></i>
                        </div>
                        <div>
                            <h4 class="mb-2">Organized by</h4>
                            <h3 class="fw-bold mb-2"><?= htmlspecialchars($result['organizerName']) ?></h3>
                            <?php if (!empty($result['organizerEmail'])): ?>
                                <p class="mb-0 text-dark opacity-75">
                                    <i class="ri-mail-line me-2"></i>
                                    <?= htmlspecialchars($result['organizerEmail']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Event Results Description -->
                <div class="info-card mb-4">
                    <h3><i class="ri-file-text-line me-2"></i> Event Results</h3>
                    <div class="description-content fs-5 lh-lg" style="color: #374151;">
                        <?= nl2br(htmlspecialchars($result['description'])) ?>
                    </div>
                </div>

                <!-- Event Details -->
                <div class="info-card mb-4">
                    <h3><i class="ri-information-line me-2"></i> Event Details</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong class="d-block mb-1"><i class="ri-calendar-line me-2"></i>Result Date:</strong>
                                <span class="text-dark"><?= date('F j, Y', strtotime($result['resultDate'])) ?></span>
                            </div>
                            
                            <?php if (!empty($result['eventStartDate'])): ?>
                                <div class="mb-3">
                                    <strong class="d-block mb-1"><i class="ri-calendar-event-line me-2"></i>Event Date:</strong>
                                    <span class="text-dark"><?= date('F j, Y', strtotime($result['eventStartDate'])) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($result['location'])): ?>
                                <div class="mb-3">
                                    <strong class="d-block mb-1"><i class="ri-map-pin-line me-2"></i>Location:</strong>
                                    <span class="text-dark"><?= htmlspecialchars($result['location']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?php if (!empty($result['category'])): ?>
                                <div class="mb-3">
                                    <strong class="d-block mb-1"><i class="ri-price-tag-line me-2"></i>Category:</strong>
                                    <span class="text-dark"><?= htmlspecialchars($result['category']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($result['skillName'])): ?>
                                <div class="mb-3">
                                    <strong class="d-block mb-1"><i class="ri-tools-line me-2"></i>Primary Skill:</strong>
                                    <span class="text-dark"><?= htmlspecialchars($result['skillName']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Submission Details -->
                <div class="info-card">
                    <h3><i class="ri-history-line me-2"></i> Submission Details</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong class="d-block mb-1"><i class="ri-user-line me-2"></i>Submitted by:</strong>
                                <span class="text-dark">
                                    <?= htmlspecialchars($result['addedByName']) ?> 
                                    <span class="badge bg-secondary ms-2"><?= $result['addedByRole'] ?></span>
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong class="d-block mb-1"><i class="ri-time-line me-2"></i>Submitted on:</strong>
                                <span class="text-dark"><?= date('F j, Y, g:i A', strtotime($result['createdDate'])) ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($result['approvedByName'])): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong class="d-block mb-1"><i class="ri-check-double-line me-2"></i>Approved by:</strong>
                                    <span class="text-dark"><?= htmlspecialchars($result['approvedByName']) ?></span>
                                </div>
                                <div class="mb-3">
                                    <strong class="d-block mb-1"><i class="ri-time-line me-2"></i>Approved on:</strong>
                                    <span class="text-dark"><?= date('F j, Y, g:i A', strtotime($result['approvedDate'])) ?></span>
                                </div>
                                <?php if (!empty($result['approvalNotes'])): ?>
                                    <div class="mb-3">
                                        <strong class="d-block mb-1"><i class="ri-sticky-note-line me-2"></i>Approval Notes:</strong>
                                        <span class="text-dark"><?= htmlspecialchars($result['approvalNotes']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reaction Buttons -->
                <div class="info-card mt-4">
                    <h3><i class="ri-heart-line me-2"></i> Reactions</h3>
                    <div class="reaction-buttons">
                        <button class="reaction-btn <?= $userReaction === 'like' ? 'active-like' : '' ?>" 
                                onclick="reactToResult('like')" 
                                id="likeBtn">
                            <i class="ri-thumb-up-line"></i>
                            <span id="likeCount"><?= $reactionCounts['likes'] ?? 0 ?></span>
                        </button>
                        
                        <button class="reaction-btn <?= $userReaction === 'dislike' ? 'active-dislike' : '' ?>" 
                                onclick="reactToResult('dislike')" 
                                id="dislikeBtn">
                            <i class="ri-thumb-down-line"></i>
                            <span id="dislikeCount"><?= $reactionCounts['dislikes'] ?? 0 ?></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Stats -->
                <div class="info-card mb-4">
                    <h3><i class="ri-bar-chart-line me-2"></i> Quick Stats</h3>
                    <div class="row text-center">
                        <div class="col-6 mb-4">
                            <div class="mb-2">
                                <i class="ri-chat-3-line fs-1" style="color: #3b82f6;"></i>
                            </div>
                            <div class="fs-3 fw-bold"><?= $commentCount ?></div>
                            <small class="text-muted">Feedback</small>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="mb-2">
                                <i class="ri-thumb-up-line fs-1" style="color: var(--primary-color);"></i>
                            </div>
                            <div class="fs-3 fw-bold" id="totalLikes"><?= $reactionCounts['likes'] ?? 0 ?></div>
                            <small class="text-muted">Likes</small>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <i class="ri-thumb-down-line fs-1" style="color: #dc2626;"></i>
                            </div>
                            <div class="fs-3 fw-bold" id="totalDislikes"><?= $reactionCounts['dislikes'] ?? 0 ?></div>
                            <small class="text-muted">Dislikes</small>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <i class="ri-image-line fs-1" style="color: #8b5cf6;"></i>
                            </div>
                            <div class="fs-3 fw-bold"><?= count($resultImages) ?></div>
                            <small class="text-muted">Images</small>
                        </div>
                    </div>
                </div>

                <!-- Share Options -->
                <div class="info-card">
                    <h3><i class="ri-share-line me-2"></i> Share This Result</h3>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary flex-fill" onclick="shareResult('facebook')">
                            <i class="ri-facebook-fill"></i>
                        </button>
                        <button class="btn btn-outline-info flex-fill" onclick="shareResult('twitter')">
                            <i class="ri-twitter-fill"></i>
                        </button>
                        <button class="btn btn-outline-success flex-fill" onclick="shareResult('whatsapp')">
                            <i class="ri-whatsapp-line"></i>
                        </button>
                        <button class="btn btn-outline-danger flex-fill" onclick="shareResult('email')">
                            <i class="ri-mail-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback & Discussion Section -->
        <div class="comment-section mt-5">
            <h2 class="mb-4">
                <i class="ri-chat-3-line me-2"></i> 
                Feedback & Discussion
                <span class="badge bg-primary ms-2 fs-6"><?= $commentCount ?></span>
            </h2>

            <!-- Add Feedback Form - Only for logged in users -->
            <?php if ($isLoggedIn): ?>
                <div class="info-card mb-4">
                    <h4><i class="ri-edit-line me-2"></i> Share Your Feedback</h4>
                    <form method="POST" id="commentForm" onsubmit="return validateComment()">
                        <input type="hidden" name="action" value="add_comment">
                        <input type="hidden" name="resultId" value="<?= $resultId ?>">
                        <input type="hidden" name="parentCommentId" id="parentCommentId" value="">
                        
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="5" 
                                      placeholder="Share your thoughts, suggestions, or feedback about this event..."
                                      required id="commentText" maxlength="1000"
                                      style="resize: none; border-radius: 12px; padding: 16px;"></textarea>
                            <small class="char-counter text-muted">0/1000</small>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="ri-information-line me-1"></i>
                                Your feedback helps improve future events
                            </small>
                            <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 12px;">
                                <i class="ri-send-plane-line me-2"></i> Post Feedback
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- For non-logged in users -->
                <div class="login-prompt mb-4">
                    <div class="login-prompt-icon">
                        <i class="ri-feedback-line"></i>
                    </div>
                    <h4 class="mb-3">Want to share your feedback?</h4>
                    <p class="text-muted mb-4">Join the discussion by signing in to share your thoughts and suggestions</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="sign_in.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                           class="btn btn-primary px-4 py-2">
                            <i class="ri-login-circle-line me-2"></i> Sign In
                        </a>
                        <a href="sign_up.php" class="btn btn-outline-primary px-4 py-2">
                            <i class="ri-user-add-line me-2"></i> Sign Up
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comments List -->
            <div id="commentsList">
                <?php if (empty($comments)): ?>
                    <div class="empty-comments">
                        <div class="empty-comments-icon">
                            <i class="ri-chat-1-line"></i>
                        </div>
                        <h4 class="mb-3">No feedback yet</h4>
                        <p class="text-muted mb-4">Be the first to share your thoughts about this event!</p>
                        
                        <?php if (!$isLoggedIn): ?>
                            <a href="sign_in.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                               class="btn btn-outline-primary">
                                <i class="ri-login-circle-line me-2"></i> Sign in to comment
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-card" id="comment-<?= $comment['commentId'] ?>">
                            <div class="comment-header">
                                <div class="comment-avatar">
                                    <?= strtoupper(substr($comment['userName'], 0, 2)) ?>
                                </div>
                                <div class="comment-user">
                                    <h6 class="mb-1 d-flex align-items-center">
                                        <?= htmlspecialchars($comment['userName']) ?>
                                        <span class="ms-2 <?= $comment['userRole'] === 'Admin' ? 'badge-admin' : 
                                                           ($comment['userRole'] === 'Organizer' ? 'badge-organizer' : 'badge-volunteer') ?>">
                                            <?= $comment['userRole'] ?>
                                        </span>
                                    </h6>
                                    <small class="text-muted d-flex align-items-center">
                                        <i class="ri-time-line me-1"></i>
                                        <?= date('M j, Y g:i A', strtotime($comment['createdAt'])) ?>
                                        <?php if ($comment['updatedAt'] != $comment['createdAt']): ?>
                                            <span class="ms-2 d-flex align-items-center">
                                                <i class="ri-edit-line me-1"></i>Edited
                                            </span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <!-- Delete Button -->
                                <?php 
                                $isCommentOwner = $isLoggedIn && ($comment['userId'] == $currentUserId);
                                $canDelete = $isCommentOwner || in_array($userRole, ['Admin', 'Organizer']);
                                ?>
                                
                                <?php if ($canDelete): ?>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteComment(<?= $comment['commentId'] ?>)"
                                            title="Delete <?= $isCommentOwner ? 'your comment' : 'comment' ?>">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="comment-body">
                                <p class="mb-0 fs-5"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                            </div>
                            
                            <div class="comment-actions">
                                <!-- Reply Button -->
                                <?php if ($isLoggedIn): ?>
                                    <button class="comment-action-btn" 
                                            onclick="showReplyForm(<?= $comment['commentId'] ?>)">
                                        <i class="ri-reply-line me-1"></i> Reply
                                    </button>
                                <?php endif; ?>
                                
                                <!-- View Replies Button -->
                                <?php if ($comment['replyCount'] > 0): ?>
                                    <button class="comment-action-btn" 
                                            onclick="toggleReplies(<?= $comment['commentId'] ?>)"
                                            id="toggleRepliesBtn-<?= $comment['commentId'] ?>">
                                        <i class="ri-chat-1-line me-1"></i> 
                                        <span id="replyCount-<?= $comment['commentId'] ?>">
                                            <?= $comment['replyCount'] ?>
                                        </span>
                                        <?= $comment['replyCount'] == 1 ? 'Reply' : 'Replies' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Reply Form -->
                            <?php if ($isLoggedIn): ?>
                                <div class="reply-form" id="replyForm-<?= $comment['commentId'] ?>" style="display: none;">
                                    <form method="POST" class="reply-form-inner" onsubmit="return validateReply(this)">
                                        <input type="hidden" name="action" value="add_comment">
                                        <input type="hidden" name="resultId" value="<?= $resultId ?>">
                                        <input type="hidden" name="parentCommentId" value="<?= $comment['commentId'] ?>">
                                        
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="comment-avatar" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                                <?= isset($_SESSION['name']) ? strtoupper(substr($_SESSION['name'], 0, 2)) : 'ME' ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="text" name="comment" class="form-control" 
                                                       placeholder="Write a reply..." required maxlength="500"
                                                       style="border-radius: 12px; padding: 12px;">
                                                <small class="char-counter text-muted">0/500</small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-outline-secondary px-3" type="button"
                                                        onclick="hideReplyForm(<?= $comment['commentId'] ?>)">
                                                    Cancel
                                                </button>
                                                <button class="btn btn-primary px-3" type="submit">
                                                    <i class="ri-send-plane-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Replies List -->
                            <?php if (!empty($comment['replies'])): ?>
                                <div class="reply-list" id="replies-<?= $comment['commentId'] ?>" style="display: none;">
                                    <h6 class="mb-3" style="color: var(--primary-color);">
                                        <i class="ri-reply-line me-1"></i> Replies
                                    </h6>
                                    
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="reply-card" id="reply-<?= $reply['commentId'] ?>">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="comment-avatar me-3" 
                                                     style="width: 40px; height: 40px; font-size: 0.9rem;">
                                                    <?= strtoupper(substr($reply['userName'], 0, 2)) ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0 d-flex align-items-center" style="font-size: 1rem;">
                                                        <?= htmlspecialchars($reply['userName']) ?>
                                                        <?php if ($reply['userRole'] === 'Admin'): ?>
                                                            <span class="badge-admin ms-2" style="font-size: 0.7rem;">Admin</span>
                                                        <?php elseif ($reply['userRole'] === 'Organizer'): ?>
                                                            <span class="badge-organizer ms-2" style="font-size: 0.7rem;">Organizer</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <small class="text-muted d-flex align-items-center">
                                                        <i class="ri-time-line me-1"></i>
                                                        <?= date('M j, Y g:i A', strtotime($reply['createdAt'])) ?>
                                                    </small>
                                                </div>
                                                
                                                <!-- Delete Button for Replies -->
                                                <?php 
                                                $isReplyOwner = $isLoggedIn && ($reply['userId'] == $currentUserId);
                                                $canDeleteReply = $isReplyOwner || in_array($userRole, ['Admin', 'Organizer']);
                                                ?>
                                                
                                                <?php if ($canDeleteReply): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteComment(<?= $reply['commentId'] ?>)"
                                                            title="Delete <?= $isReplyOwner ? 'your reply' : 'reply' ?>"
                                                            style="padding: 4px 10px; font-size: 0.85rem;">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-0" style="font-size: 1rem; color: #4b5563;">
                                                <?= nl2br(htmlspecialchars($reply['comment'])) ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Floating Action Buttons -->
    <div class="floating-actions">
        <button class="action-btn back-btn" onclick="window.history.back()" title="Go Back">
            <i class="ri-arrow-left-line"></i>
        </button>
        
        <button class="action-btn comment-btn" onclick="scrollToComments()" title="Go to Feedback">
            <i class="ri-chat-3-line"></i>
            <?php if ($commentCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                      style="font-size: 0.65rem; padding: 3px 6px;">
                    <?= $commentCount ?>
                </span>
            <?php endif; ?>
        </button>
        
        <button class="action-btn share-btn" onclick="shareResult()" title="Share">
            <i class="ri-share-line"></i>
        </button>
    </div>

    <!-- Simple Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <img src="../assets/images/logo.png" alt="Logo" style="height: 40px;" class="mb-3">
                    <p class="mb-0">Unity Volunteers Trust &copy; <?= date('Y') ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="ri-phone-line me-2"></i> 077 235 3565
                        <br>
                        <i class="ri-mail-line me-2"></i> infocontact256@gmail.com
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Lightgallery Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/zoom/lg-zoom.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/fullscreen/lg-fullscreen.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/download/lg-download.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/thumbnail/lg-thumbnail.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize lightgallery
        let gallery;
        
        function initGallery() {
            const galleryItems = [];
            
            <?php foreach ($resultImages as $index => $imagePath): ?>
                <?php 
                if (!str_starts_with($imagePath, '../') && !str_starts_with($imagePath, 'http')) {
                    $imagePath = '../' . $imagePath;
                }
                ?>
                galleryItems.push({
                    src: '<?= htmlspecialchars($imagePath) ?>',
                    thumb: '<?= htmlspecialchars($imagePath) ?>',
                    subHtml: `<h4>Image ${<?= $index + 1 ?>} - <?= htmlspecialchars($result['resultTitle']) ?></h4>`,
                    downloadUrl: '<?= htmlspecialchars($imagePath) ?>',
                    downloadFilename: 'event_image_<?= $index + 1 ?>_<?= preg_replace('/[^a-z0-9]/i', '_', $result['resultTitle']) ?>.jpg'
                });
            <?php endforeach; ?>
            
            if (galleryItems.length > 0) {
                gallery = lightGallery(document.getElementById('mainImageGallery'), {
                    plugins: [lgZoom, lgFullscreen, lgDownload, lgThumbnail],
                    speed: 500,
                    download: true,
                    counter: true,
                    getCaptionFromTitleOrAlt: false,
                    dynamic: true,
                    dynamicEl: galleryItems,
                    index: 0,
                    thumbWidth: 80,
                    thumbHeight: '60px',
                    thumbMargin: 5,
                    hideBarsDelay: 3000
                });
            }
        }
        
        // Open gallery
        function openGallery() {
            if (gallery) {
                gallery.openGallery();
            }
        }
        
        // Download all images
        function downloadAllImages() {
            <?php if (!empty($resultImages)): ?>
                const images = [];
                <?php foreach ($resultImages as $index => $imagePath): ?>
                    <?php 
                    if (!str_starts_with($imagePath, '../') && !str_starts_with($imagePath, 'http')) {
                        $imagePath = '../' . $imagePath;
                    }
                    ?>
                    images.push({
                        url: '<?= htmlspecialchars($imagePath) ?>',
                        filename: 'event_image_<?= $index + 1 ?>_<?= preg_replace('/[^a-z0-9]/i', '_', $result['resultTitle']) ?>.jpg'
                    });
                <?php endforeach; ?>
                
                if (confirm(`Download all ${images.length} images?`)) {
                    images.forEach((image, index) => {
                        setTimeout(() => {
                            const link = document.createElement('a');
                            link.href = image.url;
                            link.download = image.filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }, index * 500);
                    });
                    
                    showToast(`Downloading ${images.length} images...`, 'info');
                }
            <?php endif; ?>
        }
        
        // Initialize gallery on page load
        document.addEventListener('DOMContentLoaded', function() {
            initGallery();
            
            // Character counters
            const commentText = document.getElementById('commentText');
            if (commentText) {
                const counter = commentText.nextElementSibling;
                commentText.addEventListener('input', function() {
                    counter.textContent = this.value.length + '/1000';
                    if (this.value.length > 1000) {
                        counter.classList.add('text-danger');
                    } else {
                        counter.classList.remove('text-danger');
                    }
                });
            }
            
            // Reply inputs
            document.querySelectorAll('.reply-form input[type="text"]').forEach(input => {
                const counter = input.nextElementSibling;
                input.addEventListener('input', function() {
                    counter.textContent = this.value.length + '/500';
                    if (this.value.length > 500) {
                        counter.classList.add('text-danger');
                    } else {
                        counter.classList.remove('text-danger');
                    }
                });
            });
        });
        

        function scrollToComments() {
            const commentSection = document.querySelector('.comment-section');
            if (commentSection) {
                commentSection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
                
                <?php if ($isLoggedIn): ?>
                    const commentText = document.getElementById('commentText');
                    if (commentText) {
                        setTimeout(() => {
                            commentText.focus();
                        }, 600);
                    }
                <?php endif; ?>
            }
        }
        
        // Share function
        function shareResult(platform = '') {
            const url = window.location.href;
            const title = '<?= addslashes($result['resultTitle']) ?>';
            const text = 'Check out this event result from Unity Volunteers Trust';
            
            let shareUrl = '';
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
                    break;
                case 'email':
                    shareUrl = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(text + '\n\n' + url)}`;
                    break;
                default:
                    if (navigator.share) {
                        navigator.share({
                            title: title,
                            text: text,
                            url: url
                        });
                        return;
                    }
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        }
        
        // Reaction handling
        function reactToResult(reactionType) {
            <?php if (!$isLoggedIn): ?>
                alert('Please login to react');
                window.location.href = 'sign_in.php?redirect=' + encodeURIComponent(window.location.href);
                return;
            <?php endif; ?>
            
            const formData = new FormData();
            formData.append('action', 'react');
            formData.append('resultId', '<?= $resultId ?>');
            formData.append('reactionType', reactionType);
            
            fetch('view_result.php?resultId=<?= $resultId ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    const likeBtn = document.getElementById('likeBtn');
                    const dislikeBtn = document.getElementById('dislikeBtn');
                    const likeCount = document.getElementById('likeCount');
                    const dislikeCount = document.getElementById('dislikeCount');
                    const totalLikes = document.getElementById('totalLikes');
                    const totalDislikes = document.getElementById('totalDislikes');
                    
                    
                    likeCount.textContent = data.counts.likes || 0;
                    dislikeCount.textContent = data.counts.dislikes || 0;
                    totalLikes.textContent = data.counts.likes || 0;
                    totalDislikes.textContent = data.counts.dislikes || 0;
                    
                    
                    likeBtn.classList.remove('active-like');
                    dislikeBtn.classList.remove('active-dislike');
                    
                    if (data.userReaction === 'like') {
                        likeBtn.classList.add('active-like');
                    } else if (data.userReaction === 'dislike') {
                        dislikeBtn.classList.add('active-dislike');
                    }
                    
                    
                    showToast(`Reaction ${data.action} successfully`, 'success');
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Comment reply functions
        function showReplyForm(commentId) {
            const replyForm = document.getElementById('replyForm-' + commentId);
            const allReplyForms = document.querySelectorAll('.reply-form');
            
            allReplyForms.forEach(form => {
                if (form.id !== 'replyForm-' + commentId) {
                    form.style.display = 'none';
                }
            });
            
            if (replyForm.style.display === 'none') {
                replyForm.style.display = 'block';
                replyForm.querySelector('input[type="text"]').focus();
            } else {
                replyForm.style.display = 'none';
            }
        }
        
        function hideReplyForm(commentId) {
            const replyForm = document.getElementById('replyForm-' + commentId);
            if (replyForm) {
                replyForm.style.display = 'none';
            }
        }
        
        function toggleReplies(commentId) {
            const repliesDiv = document.getElementById('replies-' + commentId);
            const toggleBtn = document.getElementById('toggleRepliesBtn-' + commentId);
            
            if (repliesDiv) {
                if (repliesDiv.style.display === 'none') {
                    repliesDiv.style.display = 'block';
                    if (toggleBtn) {
                        toggleBtn.innerHTML = '<i class="ri-chat-1-line me-1"></i> Hide Replies';
                    }
                } else {
                    repliesDiv.style.display = 'none';
                    if (toggleBtn) {
                        const replyCount = document.getElementById('replyCount-' + commentId).textContent;
                        toggleBtn.innerHTML = `<i class="ri-chat-1-line me-1"></i> <span id="replyCount-${commentId}">${replyCount}</span> ${replyCount == 1 ? 'Reply' : 'Replies'}`;
                    }
                }
            }
        }
        
        // Delete comment
        function deleteComment(commentId) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete_comment');
            formData.append('commentId', commentId);
            
            fetch('view_result.php?resultId=<?= $resultId ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    const commentElement = document.getElementById('comment-' + commentId);
                    const replyElement = document.getElementById('reply-' + commentId);
                    
                    if (commentElement) {
                        commentElement.style.opacity = '0.5';
                        commentElement.style.transition = 'opacity 0.3s';
                        
                        setTimeout(() => {
                            commentElement.remove();
                            updateCommentCounts(-1);
                            showToast('Comment deleted successfully', 'success');
                        }, 300);
                    } else if (replyElement) {
                        replyElement.style.opacity = '0.5';
                        replyElement.style.transition = 'opacity 0.3s';
                        
                        setTimeout(() => {
                            replyElement.remove();
                            
                            
                            const parentComment = replyElement.closest('.comment-card');
                            if (parentComment) {
                                const replyCountSpan = parentComment.querySelector('#replyCount-' + parentComment.id.split('-')[1]);
                                if (replyCountSpan) {
                                    const currentCount = parseInt(replyCountSpan.textContent);
                                    replyCountSpan.textContent = currentCount - 1;
                                    
                                    
                                    const toggleBtn = parentComment.querySelector('.comment-action-btn:last-child');
                                    if (toggleBtn) {
                                        const newCount = currentCount - 1;
                                        toggleBtn.innerHTML = `<i class="ri-chat-1-line me-1"></i> <span id="replyCount-${parentComment.id.split('-')[1]}">${newCount}</span> ${newCount === 1 ? 'Reply' : 'Replies'}`;
                                    }
                                }
                            }
                            showToast('Reply deleted successfully', 'success');
                        }, 300);
                    }
                } else {
                    showToast(data.message || 'Failed to delete', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
            });
        }
        
        
        function updateCommentCounts(change) {
           
            const commentCountBadge = document.querySelector('.comment-section h2 .badge');
            if (commentCountBadge) {
                const currentCount = parseInt(commentCountBadge.textContent);
                commentCountBadge.textContent = Math.max(0, currentCount + change);
            }
            
            // Update stats card
            const statsNumber = document.querySelector('.stats-card .stats-number');
            if (statsNumber) {
                const currentCount = parseInt(statsNumber.textContent);
                statsNumber.textContent = Math.max(0, currentCount + change);
            }
            
            
            const floatingBadge = document.querySelector('.comment-btn .badge');
            if (floatingBadge) {
                const currentCount = parseInt(floatingBadge.textContent);
                const newCount = Math.max(0, currentCount + change);
                floatingBadge.textContent = newCount;
                
                if (newCount === 0) {
                    floatingBadge.remove();
                }
            }
        }
        
        // Validate comment
        function validateComment() {
            const commentText = document.getElementById('commentText');
            if (!commentText || commentText.value.trim().length < 3) {
                showToast('Comment must be at least 3 characters long', 'error');
                return false;
            }
            
            if (commentText.value.trim().length > 1000) {
                showToast('Comment is too long (max 1000 characters)', 'error');
                return false;
            }
            
            return true;
        }
        
        // Validate reply
        function validateReply(form) {
            const input = form.querySelector('input[name="comment"]');
            if (!input || input.value.trim().length < 3) {
                showToast('Reply must be at least 3 characters long', 'error');
                return false;
            }
            
            if (input.value.trim().length > 500) {
                showToast('Reply is too long (max 500 characters)', 'error');
                return false;
            }
            
            return true;
        }
        
        // Toast notification
        function showToast(message, type = 'info') {
            const existingToasts = document.querySelectorAll('.custom-toast');
            existingToasts.forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `custom-toast alert alert-${type} alert-dismissible fade show`;
            toast.style.cssText = `
                position: fixed;
                top: 25px;
                right: 25px;
                z-index: 9999;
                min-width: 300px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                border-radius: 12px;
                border: none;
                animation: slideInRight 0.3s ease-out;
            `;
            
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="ri-${type === 'success' ? 'check-line' : type === 'error' ? 'close-line' : 'information-line'} 
                       me-2 fs-5"></i>
                    <div class="flex-grow-1">${message}</div>
                    <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }
        
        
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>