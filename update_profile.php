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
        if (isset($_POST['field']) && isset($_POST['value'])) {
            $field = $_POST['field'];
            $value = $_POST['value'];
            $sql = "UPDATE Users SET $field = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$value, $id])) {
                $response = ['status' => 1, 'message' => 'Field updated successfully'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to update field'];
            }
        }
    } catch (PDOException $e) {
        $response = ['status' => 0, 'message' => 'Database error: ' . $e->getMessage()];
    }

    echo json_encode($response);
}
?>