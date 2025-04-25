<?php
session_start();
include 'database.php';

//if(isset($_POST['login'])){}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM Users WHERE email = ?";
        $result = $conn->prepare($sql);
        $result->execute([$email]);

        // Checking that no user has same email in DB
        if ($result->rowCount() > 0) {
            $user = $result->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];

                echo "<script>alert('Login successful!'); window.location.href = 'profilepage.php';</script>";
                exit();
            } else {
                echo "<script>alert('Invalid password.');</script>";
            }
        } else {
            echo "<script>alert('User not found.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="login.css" />
</head>

<body>
    <div class="heading">
        <h2>Employee Login</h2>
    </div>
    <div class="login-container">
        <form action="login.php" method="post">
            <!-- Email -->
            <div id="text">Email Address</div>
            <input type="email" name="email" required>
            <!-- Password -->
            <div id="text">Password</div>
            <input type="password" name="password" required>
            <!-- Login Button -->
            <button type="submit" name="login">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Create Account</a></p>
    </div>
</body>

</html>