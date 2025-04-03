<?php
// add_comment.php
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
$contenido = trim($_POST['contenido']);

// Validate input
if (empty($contenido)) {
    echo json_encode(['success' => false, 'message' => 'El comentario no puede estar vacío']);
    exit;
}

try {
    // Insert comment
    $insertStmt = $db->prepare("INSERT INTO comentarios (post_id, username, contenido, fecha) VALUES (:post_id, :username, :contenido, NOW())");
    $insertStmt->bindParam(':post_id', $post_id);
    $insertStmt->bindParam(':username', $username);
    $insertStmt->bindParam(':contenido', $contenido);
    $insertStmt->execute();
    
    // Update comments count in posts table
    $updatePostStmt = $db->prepare("UPDATE posts SET num_comentarios = num_comentarios + 1 WHERE id = :post_id");
    $updatePostStmt->bindParam(':post_id', $post_id);
    $updatePostStmt->execute();
    
    // Fetch updated comments
    $getCommentsStmt = $db->prepare("SELECT * FROM comentarios WHERE post_id = :post_id ORDER BY fecha DESC");
    $getCommentsStmt->bindParam(':post_id', $post_id);
    $getCommentsStmt->execute();
    $comments = $getCommentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
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