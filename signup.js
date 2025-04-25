$(document).ready(function () {
  // Display Selected Image
  $("#profilePicture").change(function (event) {
    const file = event.target.files[0]; // Get the selected file
    if (file) {
      $("#profile-img").attr("src", URL.createObjectURL(file)); // Show image
    }
  });
});
