<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the post ID from the request
    $postId = $_POST['post_id'];

    // Delete the post from the database
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$postId]);

    // Check if the post was deleted successfully
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 1, 'message' => 'Post deleted successfully']);
    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to delete post']);
    }
    exit();
}
?>