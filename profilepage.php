<?php
// profile.php

session_start();
require 'database.php';

if (!isset($_SESSION['id'])) {
  header("Location: login.php");
  exit();
}

$id = $_SESSION['id'];

// Fetch user data in a variable
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute(params: [$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// echo "<pre>";
// print_r($user);
// echo "</pre>";


// Fetch user posts with likes and dislikes count
$stmt = $conn->prepare("
    SELECT posts.*, 
           COUNT(likes.like_id) AS like_count, 
           COUNT(dislikes.dislike_id) AS dislike_count 
    FROM posts 
    LEFT JOIN likes ON posts.id = likes.post_id 
    LEFT JOIN dislikes ON posts.id = dislikes.post_id 
    WHERE posts.user_id = ? 
    GROUP BY posts.id 
    ORDER BY posts.created_at DESC
");

//LEFT JOIN ensures that even if a post has no likes, it will still appear in the result
$stmt->execute([$id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// echo "<pre>";
// print_r($posts);
// echo "</pre>";

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Social Network</title>
  <link rel="stylesheet" href="./fontawesome/css/all.min.css" />
  <link rel="stylesheet" href="profilepage.css" />
</head>

<body>
  <div class="below">
    <div id="text">
      <h2>Social Network</h2>
    </div>
    <div class="form-side">
      <div class="container">
        <div class="profile-header">
          <form id="form-edit" method="POST" enctype="multipart/form-data">
            <!-- Profile Picture -->
            <div class="form-below">
              <img id="profile-picture" src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" />

              <input type="file" name="profile_picture" id="profile-photo-upload" accept="image/*"
                style="display: none" />
              <i class="fa-solid fa-pen-to-square edit-icon" id="imageclick"></i>
            </div>

            <!-- Full Name -->
            <div id="center">
              <input type="text" class="inputx editable-field" name="full_name" id="heading"
                value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly required />
              <i class="fa-solid fa-pen-to-square edit-icon"></i>
            </div>

            <!-- Email (Non-Editable) -->
            <p><?php echo $user['email']; ?></p>

            <!-- DOB -->
            <div id="center">
              <label for="date">DOB:</label>
              <input type="date" class="inputx editable-field" name="dob" id="dob"
                value="<?php echo htmlspecialchars($user['dob']); ?>" readonly required />
              <i class="fa-solid fa-pen-to-square edit-icon"></i>
            </div>
            <a href="#" class="share-profile">Share Profile</a>
          </form>
        </div>
      </div>

      <div class="form-below">
        <!-- Add Post Section -->
        <div class="post-container">
          <h3>Add Post</h3>

          <form id="add-post-form" enctype="multipart/form-data">

            <textarea name="description" placeholder="What's on your mind?" required></textarea>
            <div>
              <!-- Image Preview -->
              <div id="image-preview" style="display: none">
                <img id="preview-image" src="" alt="Preview Image" width="100%" height="300px" />
                <button type="button" class="remove-btn">X</button>
              </div>

              <div class="post-actions">
                <button type="submit" class="post-btn">Post</button>
                <input type="file" id="post-image-input" name="post_image" accept="image/*" hidden />
                <button type="button" id="add-image-btn">Add Image</button>
              </div>

            </div>
          </form>
        </div>

        <!-- Display Posts -->
        <div class="posts">
          <?php foreach ($posts as $post): ?>
            <div class="post-container">
              <div class="post-side">
                <div class="post-header">
                  <img class="pic" src="<?php echo $user['profile_picture']; ?>" alt=" User Profile" />
                  <div class="post-details">
                    <span class="post-content">
                      <?php echo htmlspecialchars($post['description']); ?>
                    </span>
                    <p class="post-date">
                      Posted on -
                      <?php echo $post['created_at'] ?>
                    </p>
                  </div>
                </div>
                <button class="remove" data-post-id="<?php echo $post['id']; ?>">
                  X
                </button>
              </div>
              <?php if ($post['image']): ?>
                <img src="<?php echo $post['image']; ?>" alt="Post Image" width="100%" height="300px" />
              <?php endif; ?>
              <div class="post-actions">
                <button class="like-btn" data-post-id="<?php echo $post['id']; ?>">
                  👍 <span class="like-count"><?php echo $post['like_count']; ?></span>
                </button>
                <button class="dislike-btn" data-post-id="<?php echo $post['id']; ?>">
                  👎 <span class="dislike-count"><?php echo $post['dislike_count']; ?></span>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="jquery.js"></script>
  <script src="profilepage.js"></script>
</body>

</html>