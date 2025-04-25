<?php
// header('Content-Type: application/json');

session_start();

include 'database.php';

if (!isset($_SESSION['id'])) {
    die("Unauthorized");
}

// $action = $_POST['action'];

$user_id = $_SESSION['id'];
$post_id = $_POST['post_id'];
$type = $_POST['type'];

try {
    if ($type === 'like') {
        $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);

        if ($stmt->rowCount() > 0) {
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?"); 
            $stmt->execute([$user_id, $post_id]);
            $response['type'] = 'unliked';  //already liked pe like krna
        } else {

            $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $post_id]);
            $response['type'] = 'liked';

            $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
            $response['removedDislike'] = true; //dislike agar h usko htake like krdo
        }
    } elseif ($type === 'dislike') {
        // Check if the user already disliked the post
        $stmt = $conn->prepare("SELECT * FROM dislikes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);

        if ($stmt->rowCount() > 0) {
            // User already disliked the post, so undislike it
            $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
            $response['type'] = 'undisliked'; // already disliked pe dislike krna
        } else {
            // User has not disliked the post, so dislike it
            $stmt = $conn->prepare("INSERT INTO dislikes (user_id, post_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $post_id]);
            $response['type'] = 'disliked';

            // Remove like if it exists
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
            $response['removedLike'] = true;
        }
    }

    $response['success'] = 1;
} catch (PDOException $e) {
    $response['error'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);//array to json
?>