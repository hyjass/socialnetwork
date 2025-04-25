<?php
session_start();
include 'database.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 0, 'message' => 'User not logged in']);
    exit();
}

$id = $_SESSION['id']; // user id fetch krlo session se

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [];

    try {

        if (!empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === 0) {
            $target_folder = "uploads/"; // Folder to store uploaded files
            $newFileName = basename($_FILES['profile_picture']['name']); // Unique filename
            $target_file = $target_folder . $newFileName;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                // Update the profile picture in the database
                $sql = "UPDATE Users SET profile_picture = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$target_file, $id])) {
                    $response = [
                        'status' => 1,
                        'message' => 'Profile picture updated',
                        'new_image_path' => $target_file
                    ];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to update database'];
                }
            } else {
                $response = ['status' => 0, 'message' => 'Failed to upload file'];
            }
        }
    } catch (PDOException $e) {
        $response = ['status' => 0, 'message' => 'Database error: ' . $e->getMessage()];
    }

    echo json_encode($response);
}
?>