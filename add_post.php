<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id']; // $user_id=$_POST['user_id']; can also be used
    $description = $_POST['description'];
    $imagePath = '';

    if (!empty($_FILES['post_image']['name'])) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($_FILES["post_image"]["name"]);
        move_uploaded_file($_FILES["post_image"]["tmp_name"], $imagePath); //post ko store karne k liye
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, description, image, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$user_id, $description, $imagePath]);

    $newPostId = $conn->lastInsertId(); // last inserted post ID nikal lo jo help karegi vapas se fetch krne me post ke data ko

    // jo post database me dala hai usko fetch kro
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$newPostId]);
    $newPost = $stmt->fetch(PDO::FETCH_ASSOC);

    // user ki profile photo fetch kro 
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Add the profile picture to the post data
    $newPost['profile_picture'] = $user['profile_picture'];

    echo json_encode($newPost); 
    exit();
}
?>