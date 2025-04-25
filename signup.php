<?php
// Include the database connection file
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data

    $full_name = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['repassword'];
    $dob = $_POST['dateOfBirth'];
    $profile_picture = null;

    // Check if passwords match 
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
        exit();
    }

    // Check if email already exists in the database
    try {
        $sql = "SELECT email FROM Users WHERE email = ?";
        $result = $conn->prepare($sql);
        $result->execute([$email]);

        if ($result->rowCount() > 0) {
            echo "<script>alert('Email already exists. Please use a different email.'); window.location.href = 'signup.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error checking email: " . $e->getMessage() . "');</script>";
        exit();
    }

    // Handle profile picture upload
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === 0) {
        // echo $_FILES['profilePicture'];
        $target_folder = "uploads/"; // Folder to store uploaded files
        $target_file = $target_folder . basename($_FILES['profilePicture']['name']); // Full file path

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file; // Update the profile picture with the uploaded file path
        } else {
            echo "<script>alert('Error uploading profile picture.');</script>";
            exit();
        }
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the Users table
    try {

        $sql = "INSERT INTO Users (
                    full_name, email, password, dob, profile_picture
                    ) VALUES (
                    :full_name, :email, :password, :dob, :profile_picture
                    )";

        $result = $conn->prepare($sql);

        // Bind parameters using bindParam
        $result->bindParam(':full_name', $full_name);
        $result->bindParam(':email', $email);
        $result->bindParam(':password', $hashed_password);
        $result->bindParam(':dob', $dob);
        $result->bindParam(':profile_picture', $profile_picture);

        // Execute the statement
        $result->execute();

        echo "<script>alert('User registered successfully'); window.location.href = 'login.php'; </script>";
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css" />
</head>

<body>
    <div class="heading">
        <h2>Join Social Network</h2>
    </div>
    <div class="signup-container">
        <form action="signup.php" id="signupForm" method="post" enctype="multipart/form-data">
            <!-- Profile Picture -->
            <div class="form-below">

                <img src="./uploads/userprofile.jpg" alt="Profile Picture" id="profile-img" width="100" height="100" />

                <input type="file" id="profilePicture" name="profilePicture" accept="image/*" hidden required />
                <label for="profilePicture" id="upload-btn">Upload Profile Pic</label>

            </div>

            <!-- Full Name and Date of Birth (Side by Side) -->
            <div class="form-ro">

                <div class="form-row">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="fullName" placeholder="John Doe" required />
                    </div>
                    <div class="form-group">
                        <label for="dateOfBirth">Date of Birth</label>
                        <input type="date" id="dateOfBirth" name="dateOfBirth" placeholder="dd/mm/yyyy" />
                    </div>
                </div>

            </div>

            <!-- Email and Password (Side by Side) -->
            <div class="form-row">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="john@doe.com" required />
                </div>

                <div class="form-half">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required />
                        <div id="text">Use A-Z, a-z, 0-9, !@#$%^&* in password</div>
                    </div>
                    <div class="form-group">
                        <label for="repassword">Re-Password</label>
                        <input type="password" id="repassword" name="repassword" required />
                    </div>
                </div>

            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button" name="submit">Sign Up</button>
        </form>

    </div>
    <script src="jquery.js"></script>
    <script src="signup.js"></script>

</body>

</html>