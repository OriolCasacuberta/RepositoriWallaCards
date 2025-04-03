<?php
// toggle_like.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Require database connection
require_once '../web/connecta_db_persistent.php';

$username = $_SESSION['user']['username'];
$post_id = $_POST['post_id'];

try {
    // First, check if user has already liked the post
    $checkLikeStmt = $db->prepare("SELECT * FROM likes WHERE post_id = :post_id AND username = :username");
    $checkLikeStmt->bindParam(':post_id', $post_id);
    $checkLikeStmt->bindParam(':username', $username);
    $checkLikeStmt->execute();
    
    $existing_like = $checkLikeStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_like) {
        // Remove like
        $deleteLikeStmt = $db->prepare("DELETE FROM likes WHERE post_id = :post_id AND username = :username");
        $deleteLikeStmt->bindParam(':post_id', $post_id);
        $deleteLikeStmt->bindParam(':username', $username);
        $deleteLikeStmt->execute();
        
        // Decrease likes count in posts table
        $updatePostStmt = $db->prepare("UPDATE posts SET num_likes = num_likes - 1 WHERE id = :post_id");
        $updatePostStmt->bindParam(':post_id', $post_id);
        $updatePostStmt->execute();
        
        $liked = false;
    } else {
        // Add like
        $addLikeStmt = $db->prepare("INSERT INTO likes (post_id, username, fecha) VALUES (:post_id, :username, NOW())");
        $addLikeStmt->bindParam(':post_id', $post_id);
        $addLikeStmt->bindParam(':username', $username);
        $addLikeStmt->execute();
        
        // Increase likes count in posts table
        $updatePostStmt = $db->prepare("UPDATE posts SET num_likes = num_likes + 1 WHERE id = :post_id");
        $updatePostStmt->bindParam(':post_id', $post_id);
        $updatePostStmt->execute();
        
        $liked = true;
    }
    
    // Get current likes count
    $getLikesStmt = $db->prepare("SELECT num_likes FROM posts WHERE id = :post_id");
    $getLikesStmt->bindParam(':post_id', $post_id);
    $getLikesStmt->execute();
    $post = $getLikesStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'liked' => $liked, 
        'likes' => $post['num_likes']
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}