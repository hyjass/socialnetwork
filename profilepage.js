$(document).ready(function () {
  //  save icon aur edit icon ko toggle kiya h(using event delegation)
  $(document).on("click", ".edit-icon", function () {
    let inputField = $(this).prev("input");

    if (inputField.prop("readonly")) {
      // Enable editing
      inputField.prop("readonly", false).focus();
      $(this).removeClass("fa-pen-to-square").addClass("fa-save");
    } else {
      // Disable editing
      inputField.prop("readonly", true);
      $(this).removeClass("fa-save").addClass("fa-pen-to-square");

      // Send updated data to the server via AJAX
      updateField(inputField);
    }
  });

  // Function to update name and dob in the database
  function updateField(inputField) {
    const fieldName = inputField.attr("name");
    let fieldValue;
    if (fieldName !== "profile_picture") {
      fieldValue = inputField.val(); // values jaise name ya dob
    } else {
      return; //for image
    }

    // Send AJAX request to update the field
    $.ajax({
      url: "update_profile.php",
      type: "POST",
      data: {
        field: fieldName,
        value: fieldValue,
      },
      success: function (response) {
        console.log("Server Response:", response);
        if (response.status === 1) {
          alert(response.message || "Field updated successfully.");
        } else {
          alert(response.message || "Can't update field");
        }
      },
      error: function () {
        alert("An error occurred while updating the field. Please try again.");
      },
    });
  }

  // image ko trigger kiya h
  $("#imageclick").on("click", function () {
    if ($("#imageclick").hasClass("fa-pen-to-square")) {
      $("#profile-photo-upload").click();
    }
  });

  // photo update ki h UI me and DB me by ajax
  $("#profile-photo-upload").change(function (event) {
    const file = event.target.files[0];
    // Jab user ek image choose karega to uski details event.target.files[0] se milengi.
    // console.log(file);
    if (file) {
      const formData = new FormData();
      //ek built-in JavaScript object hai jo form data ko dynamically store aur send karne ke liye use hota hai.
      //Iska use hum AJAX requests ke saath karte hain bina page reload kiye data send karne ke liye.
      formData.append("profile_picture", file);

      $.ajax({
        url: "update_photo.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          console.log("Server Response:", response);
          response = JSON.parse(response);
          if (response.status === 1) {
            $("#profile-picture").attr("src", response.new_image_path); // Show image in profile

            // console.log($(".pic")); //array like object
            $(".pic").each(function () {
              $(this).attr("src", response.new_image_path); //AJAX request asynchronous hoti hai
            });
            setTimeout(function () {
              alert("Profile Image Updated."); //synchronous hota hai
            }, 500);
          } else {
            alert(response.message);
          }
        },
        error: function () {
          alert(
            "An error occurred while updating the profile picture. Please try again."
          );
        },
      });
    }
  });

  // jab x pe click ho to image hat jaiye post wale section me
  $(".remove-btn").click(function () {
    $("#preview-image").attr("src", "");
    $("#image-preview").hide();
    $("#post-image-upload").val("");
  });

  // AJAX for adding posts by form submission
  $("#add-post-form").submit(function (e) {
    e.preventDefault(); //Prevents the default form submission (which would refresh the page).
    const formData = new FormData(this); //this refers to current form

    // formData.forEach((value, key) => {
    //   console.log(`${key}:`, value);
    // });

    $.ajax({
      url: "add_post.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        let post = JSON.parse(response);
        console.log(post);
        let newPost = `
                <div class="post-container">
                    <div class="post-side">
                        <div class="post-header">
                            <img class="pic" src="${
                              post.profile_picture
                            }" alt="User Profile">
                            <div class="post-details">
                                <span class="post-content">${
                                  post.description
                                }</span>
                                <p class="post-date">Posted on - ${
                                  post.created_at
                                }</p>
                            </div>
                        </div>
                        <button class="remove" data-post-id="${
                          post.id
                        }">X</button>
                    </div>
                    ${
                      post.image
                        ? `<img src="${post.image}" alt="Post Image" width="100%" height="300px">`
                        : ""
                    }
                    <div class="post-actions">
                        <button class="like-btn" data-post-id="${
                          post.id
                        }">👍 <span class="like-count">0</span> </button>
                        <button class="dislike-btn" data-post-id="${
                          post.id
                        }">👎 <span class="dislike-count">0</span> </button>
                    </div>
                </div>
            `;
        $(".posts").prepend(newPost);
        $("#add-post-form")[0].reset(); // form ko reset krdiya post section wale ko
        $("#image-preview").hide(); //image htadi post section se
      },
      error: function () {
        alert("Failed to add post. Please try again.");
      },
    });
  });

  //like
  $(document).on("click", ".like-btn", function () {
    const postId = $(this).data("post-id");
    const likeBtn = $(this);
    const dislikeBtn = likeBtn.siblings(".dislike-btn");

    $.ajax({
      url: "like_dislike.php",
      type: "POST",
      data: { post_id: postId, type: "like" }, //object h
      success: function (response) {
        // console.log(typeof response); string
        response = JSON.parse(response); //json to object
        // console.log(typeof response); object
        if (response.success) {
          let likeCountElement = likeBtn.find(".like-count"); //reference of span tag
          let dislikeCountElement = dislikeBtn.find(".dislike-count");

          let likeCount = parseInt(likeCountElement.text());
          let dislikeCount = parseInt(dislikeCountElement.text());

          if (response.type === "liked") {
            likeCount += 1;
            likeCountElement.text(likeCount);
          } else if (response.type === "unliked") {
            if (likeCount > 0) {
              likeCount -= 1;
              likeCountElement.text(likeCount);
            }
          }

          if (response.removedDislike) {
            if (dislikeCount > 0) {
              dislikeCount -= 1;
              dislikeCountElement.text(dislikeCount);
            }
          }
        } else {
          alert("Error processing like.");
        }
      },
    });
  });

  //dislike btn
  $(document).on("click", ".dislike-btn", function () {
    const postId = $(this).data("post-id");
    const dislikeBtn = $(this);
    const likeBtn = dislikeBtn.siblings(".like-btn");

    $.ajax({
      url: "like_dislike.php",
      type: "POST",
      data: { post_id: postId, type: "dislike" },
      success: function (response) {
        response = JSON.parse(response);
        console.log(response);

        if (response.success) {
          let likeCountElement = likeBtn.find(".like-count");
          let dislikeCountElement = dislikeBtn.find(".dislike-count");

          let likeCount = parseInt(likeCountElement.text());
          let dislikeCount = parseInt(dislikeCountElement.text());

          if (response.type === "disliked") {
            dislikeCount += 1;
            dislikeCountElement.text(dislikeCount);
            // dislikeBtn.addClass("active");
            // likeBtn.removeClass("active");
          } else if (response.type === "undisliked") {
            if (dislikeCount > 0) {
              dislikeCount -= 1;
              dislikeCountElement.text(dislikeCount);
            }
            // dislikeBtn.removeClass("active");
          }

          if (response.removedLike) {
            if (likeCount > 0) {
              likeCount -= 1;
              likeCountElement.text(likeCount);
            }
            // likeBtn.removeClass("active");
          }
        } else {
          alert("Error processing dislike.");
        }
      },
    });
  });

  // When "Add Image" button is clicked, trigger file input
  $("#add-image-btn").click(function () {
    $("#post-image-input").click();
  });

  // image display ki h post section me jab choose krta h user post krne k liye
  $("#post-image-input").change(function (event) {
    //Jab bhi user ek naya image file select karega
    const file = event.target.files[0]; // Get the selected file
    if (file) {
      $("#preview-image").attr("src", URL.createObjectURL(file));
      //Yeh function ek temporary URL banata hai jo browser ke andar hi kaam karta hai
      //is URL se image ko bina kisi server pe upload kiye direct preview kiya ja sakta hai
      //file ka ek in-memory representation store karta hai
      $("#image-preview").show();
    }
  });

  // Handle remove button click
  $(document).on("click", ".remove", function () {
    //Ensures that the event works for dynamically added .remove buttons (new posts).
    const postId = $(this).data("post-id");
    const postElement = $(this).closest(".post-container");

    if (confirm("Are you sure you want to delete this post?")) {
      $.ajax({
        url: "delete_post.php",
        type: "POST",
        data: { post_id: postId },
        success: function (response) {
          let result = JSON.parse(response); //datatype: json  fir ===or alternative
          if (result.status === 1) {
            postElement.remove(); // Remove post from page
          } else {
            alert(result.message);
          }
        },
        error: function () {
          alert("Could not delete post. Try again.");
        },
      });
    }
  });
});
