<?php
// get_comments.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Require database connection
require_once '../web/connecta_db_persistent.php';

$post_id = $_GET['post_id'];

try {
    // Fetch comments for the specific post
    $stmt = $db->prepare("SELECT * FROM comentarios WHERE post_id = :post_id ORDER BY fecha DESC");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'comments' => $comments,
        'total_comments' => count($comments)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}